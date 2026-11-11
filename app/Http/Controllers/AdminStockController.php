<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminStockController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'stock_quantity', 'unit', 'min_stock']);

        $movements = StockMovement::query()
            ->with(['product:id,name,sku,unit', 'user:id,name'])
            ->latest('created_at')
            ->paginate(50);

        return view('admin.stock.index', compact('products', 'movements'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'movement_type' => ['required', 'in:entry,exit,adjustment'],
            'quantity' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $result = DB::transaction(function () use ($validated): string {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($validated['product_id']);

            if (! $product->active) {
                throw ValidationException::withMessages([
                    'product_id' => 'Não é possível movimentar um produto inativo.',
                ]);
            }

            $before = round((float) $product->stock_quantity, 3);
            $inputQuantity = round((float) $validated['quantity'], 3);

            if ($validated['movement_type'] === 'adjustment') {
                $after = $inputQuantity;
                $movementType = $after >= $before ? 'adjustment_in' : 'adjustment_out';
                $delta = abs($after - $before);
            } elseif ($validated['movement_type'] === 'entry') {
                if ($inputQuantity <= 0) {
                    throw ValidationException::withMessages(['quantity' => 'A quantidade de entrada deve ser superior a zero.']);
                }

                $after = round($before + $inputQuantity, 3);
                $movementType = 'entry';
                $delta = $inputQuantity;
            } else {
                if ($inputQuantity <= 0) {
                    throw ValidationException::withMessages(['quantity' => 'A quantidade de saída deve ser superior a zero.']);
                }

                if ($inputQuantity > $before) {
                    throw ValidationException::withMessages(['quantity' => 'Stock insuficiente para esta saída.']);
                }

                $after = round($before - $inputQuantity, 3);
                $movementType = 'exit';
                $delta = $inputQuantity;
            }

            if ($delta <= 0) {
                return 'unchanged';
            }

            $product->update(['stock_quantity' => $after]);
            $product->stockMovements()->create([
                'user_id' => Auth::guard('web')->id(),
                'movement_type' => $movementType,
                'quantity' => $delta,
                'stock_before' => $before,
                'stock_after' => $after,
                'reason' => trim((string) ($validated['reason'] ?? '')) ?: null,
            ]);

            return 'saved';
        });

        return redirect()->route('admin.stock.index')
            ->with('success', $result === 'saved'
                ? 'Movimento de stock registado.'
                : 'A quantidade indicada é igual ao stock atual. Não foi criado um movimento.');
    }
}
