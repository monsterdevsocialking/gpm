<?php

namespace App\Http\Controllers;

use App\Models\QuoteRequest;
use App\Models\Supplier;
use App\Models\SupplierInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminSupplierInquiryController extends Controller
{
    public function storeForRequest(Request $request, int $quoteRequest): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')->where('active', true)],
            'reference' => ['nullable', 'string', 'max:80'],
            'message' => ['nullable', 'string', 'max:5000'],
            'response_due_at' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $inquiry = DB::transaction(function () use ($quoteRequest, $validated): SupplierInquiry {
            $record = QuoteRequest::query()
                ->lockForUpdate()
                ->findOrFail($quoteRequest);

            if (! in_array($record->status, ['under_review', 'supplier_research'], true)) {
                throw ValidationException::withMessages([
                    'supplier_id' => 'Coloque primeiro o pedido na etapa de análise antes de consultar fornecedores.',
                ]);
            }

            $supplierIsActive = Supplier::query()
                ->whereKey($validated['supplier_id'])
                ->where('active', true)
                ->exists();

            if (! $supplierIsActive) {
                throw ValidationException::withMessages([
                    'supplier_id' => 'Este fornecedor está inativo. Escolha um fornecedor ativo.',
                ]);
            }

            $inquiry = $record->supplierInquiries()->create([
                ...$validated,
                'created_by' => Auth::guard('web')->id(),
                'status' => 'draft',
            ]);

            if ($record->status === 'under_review') {
                $record->update(['status' => 'supplier_research']);
                $record->statusHistory()->create([
                    'user_id' => Auth::guard('web')->id(),
                    'old_status' => 'under_review',
                    'new_status' => 'supplier_research',
                    'note' => 'Consulta a fornecedor iniciada.',
                ]);
            }

            return $inquiry;
        });

        return redirect()->route('admin.supplier-inquiries.show', $inquiry)
            ->with('success', 'Consulta ao fornecedor criada. Reveja os dados antes de a marcar como enviada.');
    }

    public function show(int $inquiry): View
    {
        $inquiry = SupplierInquiry::query()
            ->with(['supplier', 'quoteRequest.items', 'creator', 'responses.items.quoteRequestItem'])
            ->findOrFail($inquiry);

        return view('admin.supplier-inquiries.show', compact('inquiry'));
    }

    public function markSent(int $inquiry): RedirectResponse
    {
        $record = SupplierInquiry::query()->findOrFail($inquiry);

        if ($record->status !== 'draft') {
            return back()->withErrors(['status' => 'Só uma consulta em rascunho pode ser marcada como enviada.']);
        }

        $record->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return back()->with('success', 'Consulta marcada como enviada. O envio externo deve ser feito pela equipa.');
    }

    public function storeResponse(Request $request, int $inquiry): RedirectResponse
    {
        $record = SupplierInquiry::query()
            ->with('quoteRequest.items')
            ->findOrFail($inquiry);

        if (! in_array($record->status, ['sent', 'responded'], true)) {
            return back()->withErrors(['status' => 'Marque a consulta como enviada antes de registar uma resposta.']);
        }

        $allowedItemIds = $record->quoteRequest->items->modelKeys();

        $validated = $request->validate([
            'supplier_reference' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'size:3', 'alpha'],
            'lead_time' => ['nullable', 'string', 'max:180'],
            'delivery_terms' => ['nullable', 'string', 'max:5000'],
            'payment_terms' => ['nullable', 'string', 'max:5000'],
            'valid_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.quote_request_item_id' => ['required', 'integer', 'distinct', Rule::in($allowedItemIds)],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'items.*.availability_status' => ['required', Rule::in(['available', 'partial', 'unavailable', 'substitute', 'unknown'])],
            'items.*.available_quantity' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'items.*.lead_time' => ['nullable', 'string', 'max:180'],
            'items.*.conditions' => ['nullable', 'string', 'max:2000'],
            'items.*.notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($record, $validated): void {
            $response = $record->responses()->create([
                'supplier_reference' => $validated['supplier_reference'] ?? null,
                'status' => 'received',
                'currency' => isset($validated['currency']) ? strtoupper($validated['currency']) : null,
                'lead_time' => $validated['lead_time'] ?? null,
                'delivery_terms' => $validated['delivery_terms'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'valid_until' => $validated['valid_until'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'received_at' => now(),
            ]);

            foreach ($validated['items'] as $item) {
                $response->items()->create([
                    'quote_request_item_id' => $item['quote_request_item_id'],
                    'unit_price' => $item['unit_price'] ?? null,
                    'availability_status' => $item['availability_status'],
                    'available_quantity' => $item['available_quantity'] ?? null,
                    'lead_time' => $item['lead_time'] ?? null,
                    'conditions' => $item['conditions'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            $record->update([
                'status' => 'responded',
                'responded_at' => now(),
            ]);
        });

        return redirect()->route('admin.supplier-inquiries.show', $record)
            ->with('success', 'Resposta do fornecedor registada.');
    }
}
