<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSupplierController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $suppliers = Supplier::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('nif', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.suppliers.index', compact('suppliers', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSupplier($request);
        $validated['active'] = $request->boolean('active', true);

        Supplier::query()->create($validated);

        return redirect()->route('admin.suppliers.index')->with('success', 'Fornecedor registado.');
    }

    public function update(Request $request, int $supplier): RedirectResponse
    {
        $record = Supplier::query()->findOrFail($supplier);
        $validated = $this->validateSupplier($request, $record->id);
        $validated['active'] = $request->boolean('active');

        $record->update($validated);

        return redirect()->route('admin.suppliers.index')->with('success', 'Fornecedor atualizado.');
    }

    private function validateSupplier(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'nif' => ['nullable', 'string', 'max:50'],
            'contact_name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
