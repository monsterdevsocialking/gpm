<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class AdminCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $categoryId = $request->integer('category');

        $products = Product::query()
            ->with('category')
            ->withCount(['images', 'orderItems', 'quoteRequestItems'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($categoryId > 0, fn ($query) => $query->where('category_id', $categoryId))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::query()->orderBy('name')->get();
        $editing = $request->filled('edit')
            ? Product::query()->with('images')->findOrFail($request->integer('edit'))
            : null;

        return view('admin.catalog.index', compact(
            'products',
            'categories',
            'editing',
            'search',
            'categoryId'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);
        $slug = $this->uniqueSlug($validated['slug'] ?: $validated['name']);
        $files = $this->storeUploadedFiles($request);

        try {
            DB::transaction(function () use ($request, $validated, $slug, $files): void {
                $product = Product::query()->create([
                    'category_id' => $validated['category_id'] ?? null,
                    'name' => trim($validated['name']),
                    'slug' => $slug,
                    'sku' => trim((string) ($validated['sku'] ?? '')) ?: null,
                    'description' => $validated['description'] ?? null,
                    'price' => $validated['price'],
                    'cost_price' => $validated['cost_price'] ?? null,
                    'stock_quantity' => 0,
                    'min_stock' => $validated['min_stock'],
                    'unit' => trim($validated['unit']),
                    'main_image' => $files['main'] ?? null,
                    'active' => $request->boolean('active', true),
                    'featured' => $request->boolean('featured'),
                ]);

                $initialStock = round((float) ($validated['initial_stock'] ?? 0), 3);

                if ($initialStock > 0) {
                    $product->update(['stock_quantity' => $initialStock]);
                    $product->stockMovements()->create([
                        'user_id' => Auth::guard('web')->id(),
                        'movement_type' => 'entry',
                        'quantity' => $initialStock,
                        'stock_before' => 0,
                        'stock_after' => $initialStock,
                        'reason' => 'Stock inicial do produto',
                    ]);
                }

                foreach ($files['extra'] as $index => $imagePath) {
                    $product->images()->create([
                        'image_path' => $imagePath,
                        'sort_order' => $index + 1,
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            $this->deleteStoredFiles($files);
            throw $exception;
        }

        return redirect()->route('admin.catalog.index')
            ->with('success', 'Produto criado com sucesso.');
    }

    public function update(Request $request, int $product): RedirectResponse
    {
        $record = Product::query()->with('images')->findOrFail($product);
        $validated = $this->validateProduct($request, $record->id);
        $slug = $this->uniqueSlug($validated['slug'] ?: $validated['name'], $record->id);
        $files = $this->storeUploadedFiles($request);
        $oldMainImage = $record->main_image;

        try {
            DB::transaction(function () use ($request, $validated, $slug, $files, $record): void {
                $record->update([
                    'category_id' => $validated['category_id'] ?? null,
                    'name' => trim($validated['name']),
                    'slug' => $slug,
                    'sku' => trim((string) ($validated['sku'] ?? '')) ?: null,
                    'description' => $validated['description'] ?? null,
                    'price' => $validated['price'],
                    'cost_price' => $validated['cost_price'] ?? null,
                    'min_stock' => $validated['min_stock'],
                    'unit' => trim($validated['unit']),
                    'active' => $request->boolean('active'),
                    'featured' => $request->boolean('featured'),
                    ...($files['main'] ? ['main_image' => $files['main']] : []),
                ]);

                $currentMaxOrder = (int) $record->images()->max('sort_order');

                foreach ($files['extra'] as $index => $imagePath) {
                    $record->images()->create([
                        'image_path' => $imagePath,
                        'sort_order' => $currentMaxOrder + $index + 1,
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            $this->deleteStoredFiles($files);
            throw $exception;
        }

        if ($files['main'] && $oldMainImage !== $files['main']) {
            $this->deleteStoredFile($oldMainImage);
        }

        return redirect()->route('admin.catalog.index')
            ->with('success', 'Produto atualizado. O stock foi preservado; altere-o no módulo de stock.');
    }

    public function destroy(int $product): RedirectResponse
    {
        $record = Product::query()->with('images')->findOrFail($product);
        $mainImage = $record->main_image;
        $extraImages = $record->images->pluck('image_path')->all();

        $hasHistory = $record->orderItems()->exists()
            || $record->quoteRequestItems()->exists()
            || $record->stockMovements()->exists();

        if ($hasHistory) {
            $record->update(['active' => false]);

            return redirect()->route('admin.catalog.index')
                ->with('success', 'O produto tem histórico comercial ou de stock e foi desativado para preservar os registos.');
        }

        $record->delete();

        $this->deleteStoredFile($mainImage);
        foreach ($extraImages as $imagePath) {
            $this->deleteStoredFile($imagePath);
        }

        return redirect()->route('admin.catalog.index')
            ->with('success', 'Produto eliminado.');
    }

    public function destroyImage(int $product, int $image): RedirectResponse
    {
        $record = Product::query()->findOrFail($product);
        $imageRecord = $record->images()->findOrFail($image);
        $path = $imageRecord->image_path;
        $imageRecord->delete();
        $this->deleteStoredFile($path);

        return redirect()->route('admin.catalog.index', ['edit' => $record->id])
            ->with('success', 'Imagem adicional removida.');
    }

    private function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:220', Rule::unique('products', 'slug')->ignore($ignoreId)],
            'sku' => ['nullable', 'string', 'max:80', Rule::unique('products', 'sku')->ignore($ignoreId)],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('active', true)],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'cost_price' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'min_stock' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'initial_stock' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'unit' => ['required', 'string', 'max:40'],
            'main_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'extra_images' => ['nullable', 'array', 'max:20'],
            'extra_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }

    private function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source);

        if ($base === '') {
            throw new RuntimeException('Não foi possível gerar um endereço válido para o produto.');
        }

        $slug = $base;
        $suffix = 2;

        while (Product::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * @return array{main: ?string, extra: array<int, string>}
     */
    private function storeUploadedFiles(Request $request): array
    {
        $stored = ['main' => null, 'extra' => []];

        try {
            if ($request->hasFile('main_image')) {
                $stored['main'] = $this->storeImage($request->file('main_image'));
            }

            foreach ($request->file('extra_images', []) as $file) {
                $stored['extra'][] = $this->storeImage($file);
            }
        } catch (\Throwable $exception) {
            $this->deleteStoredFiles($stored);
            throw $exception;
        }

        return $stored;
    }

    private function storeImage($file): string
    {
        $filename = Str::uuid()->toString().'.'.$file->extension();
        $path = $file->storeAs('uploads/produtos', $filename, 'public');

        if (! $path) {
            throw new RuntimeException('Não foi possível guardar uma imagem do produto.');
        }

        return $path;
    }

    private function deleteStoredFiles(array $files): void
    {
        $this->deleteStoredFile($files['main'] ?? null);

        foreach ($files['extra'] ?? [] as $path) {
            $this->deleteStoredFile($path);
        }
    }

    private function deleteStoredFile(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/produtos/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
