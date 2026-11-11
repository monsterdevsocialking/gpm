<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\QuoteRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QuoteCartController extends Controller
{
    private const SESSION_KEY = 'gpm_quote_cart';

    public function index(Request $request): View
    {
        $cart = $request->session()->get(self::SESSION_KEY, []);
        $products = Product::query()
            ->active()
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        $items = collect($cart)->map(function (array $line, string|int $id) use ($products): ?array {
            $product = $products->get((int) $id);

            if (! $product) {
                return null;
            }

            return [
                'product' => $product,
                'quantity' => (float) ($line['quantity'] ?? 1),
                'notes' => (string) ($line['notes'] ?? ''),
            ];
        })->filter()->values();

        return view('marketplace.cart', compact('items'));
    }

    public function store(Request $request, int $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.001', 'max:999999999'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $item = Product::query()->active()->findOrFail($product);
        $cart = $request->session()->get(self::SESSION_KEY, []);
        $current = (float) ($cart[$item->id]['quantity'] ?? 0);

        $cart[$item->id] = [
            'quantity' => round($current + (float) $validated['quantity'], 3),
            'notes' => trim((string) ($validated['notes'] ?? '')),
        ];

        $request->session()->put(self::SESSION_KEY, $cart);

        return redirect()->route('cart.index')->with('success', 'Produto adicionado ao pedido de fornecimento.');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001', 'max:999999999'],
            'items.*.notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $cart = $request->session()->get(self::SESSION_KEY, []);

        foreach ($validated['items'] as $id => $line) {
            $id = (int) $id;

            if (! array_key_exists($id, $cart)) {
                continue;
            }

            $cart[$id] = [
                'quantity' => round((float) $line['quantity'], 3),
                'notes' => trim((string) ($line['notes'] ?? '')),
            ];
        }

        $request->session()->put(self::SESSION_KEY, $cart);

        return redirect()->route('cart.index')->with('success', 'Pedido atualizado.');
    }

    public function destroy(Request $request, int $product): RedirectResponse
    {
        $cart = $request->session()->get(self::SESSION_KEY, []);
        unset($cart[$product]);
        $request->session()->put(self::SESSION_KEY, $cart);

        return redirect()->route('cart.index')->with('success', 'Produto removido do pedido.');
    }

    public function submit(Request $request): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        if (! $customer || ! $customer->active) {
            return redirect()->route('customer.login')
                ->with('status', 'Entre na sua conta para enviar o pedido de fornecimento.');
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $cart = $request->session()->get(self::SESSION_KEY, []);

        if ($cart === []) {
            return redirect()->route('cart.index')->withErrors([
                'cart' => 'Adicione pelo menos um produto antes de enviar o pedido.',
            ]);
        }

        $products = Product::query()
            ->active()
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        if ($products->count() !== count($cart)) {
            return redirect()->route('cart.index')->withErrors([
                'cart' => 'Um ou mais produtos já não estão disponíveis. Reveja o pedido.',
            ]);
        }

        $quoteRequest = DB::transaction(function () use ($cart, $products, $customer, $validated): QuoteRequest {
            $requestNumber = 'GPM-REQ-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));

            $quoteRequest = QuoteRequest::query()->create([
                'request_number' => $requestNumber,
                'customer_id' => $customer->getKey(),
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'status' => 'submitted',
                'notes' => trim((string) ($validated['notes'] ?? '')) ?: null,
                'submitted_at' => now(),
            ]);

            $quoteRequest->statusHistory()->create([
                'user_id' => null,
                'old_status' => null,
                'new_status' => 'submitted',
                'note' => 'Pedido enviado pelo cliente.',
            ]);

            foreach ($cart as $id => $line) {
                $product = $products->get((int) $id);

                $quoteRequest->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit' => $product->unit,
                    'quantity' => round((float) ($line['quantity'] ?? 1), 3),
                    'notes' => trim((string) ($line['notes'] ?? '')) ?: null,
                ]);
            }

            return $quoteRequest;
        });

        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('customer.requests.show', $quoteRequest)
            ->with('success', 'Pedido enviado à equipa GPM. A referência foi criada com sucesso.');
    }
}
