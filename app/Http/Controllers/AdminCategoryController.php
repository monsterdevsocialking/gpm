<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $editing = $request->filled('edit')
            ? Category::query()->findOrFail($request->integer('edit'))
            : null;

        $categories = Category::query()
            ->withCount('products')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.categories.index', compact('editing', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCategory($request);
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?: $validated['name']);
        $validated['active'] = $request->boolean('active', true);

        $record = Category::query()->create($validated);
        $this->logAction($request, 'category_created', $record->id, 'Categoria criada: '.$record->name.'.');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoria criada com sucesso.');
    }

    public function update(Request $request, int $category): RedirectResponse
    {
        $record = Category::query()->findOrFail($category);
        $validated = $this->validateCategory($request, $record->id);
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?: $validated['name'], $record->id);
        $validated['active'] = $request->boolean('active');

        $record->update($validated);
        $this->logAction($request, 'category_updated', $record->id, 'Categoria atualizada: '.$record->name.'.');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoria atualizada.');
    }

    public function destroy(int $category): RedirectResponse
    {
        $record = Category::query()->withCount('products')->findOrFail($category);

        if ($record->products_count > 0) {
            $record->update(['active' => false]);
            $this->logAction(request(), 'category_deactivated', $record->id, 'Categoria desativada porque ainda contém produtos: '.$record->name.'.');

            return redirect()->route('admin.categories.index')
                ->with('success', 'A categoria contém produtos e foi desativada para preservar o catálogo.');
        }

        $recordId = $record->id;
        $recordName = $record->name;
        $record->delete();
        $this->logAction(request(), 'category_deleted', $recordId, 'Categoria eliminada: '.$recordName.'.');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoria eliminada.');
    }

    private function validateCategory(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:150', Rule::unique('categories', 'slug')->ignore($ignoreId)],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function logAction(Request $request, string $action, int $entityId, string $description): void
    {
        ActivityLog::query()->create([
            'user_id' => auth()->guard('web')->id(),
            'action' => $action,
            'entity_type' => 'category',
            'entity_id' => $entityId,
            'description' => $description,
            'ip_address' => $request->ip(),
        ]);
    }

    private function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source);

        if ($base === '') {
            throw new \RuntimeException('Não foi possível gerar um slug válido para a categoria.');
        }

        $slug = $base;
        $suffix = 2;

        while (Category::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
