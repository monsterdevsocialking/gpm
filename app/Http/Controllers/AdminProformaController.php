<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CompanySetting;
use App\Models\Order;
use App\Models\Proforma;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminProformaController extends Controller
{
    public function storeForOrder(Request $request, int $order): RedirectResponse
    {
        $validated = $request->validate([
            'valid_until' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $proforma = DB::transaction(function () use ($order, $validated, $request): Proforma {
            $record = Order::query()
                ->with('customer')
                ->lockForUpdate()
                ->findOrFail($order);

            if (! in_array($record->status, ['confirmed', 'preparing', 'shipped', 'completed'], true)) {
                throw ValidationException::withMessages([
                    'proforma' => 'Só pode emitir uma proforma para uma encomenda confirmada.',
                ]);
            }

            $company = CompanySetting::query()->first();

            $proforma = Proforma::query()->create([
                'order_id' => $record->id,
                'proforma_number' => 'TMP-'.Str::uuid(),
                'company_name' => $company?->company_name ?: 'GPM Conecta',
                'company_nif' => $company?->nif,
                'company_address' => $company?->address,
                'company_phone' => $company?->phone,
                'company_email' => $company?->email,
                'customer_name' => $record->customer_name,
                'customer_nif' => $record->customer_nif,
                'customer_phone' => $record->customer_phone,
                'customer_email' => $record->customer_email,
                'customer_address' => $record->delivery_address,
                'subtotal' => $record->subtotal,
                'delivery_fee' => $record->delivery_fee,
                'discount' => $record->discount,
                'total' => $record->total,
                'currency' => $record->currency ?: 'AOA',
                'status' => 'issued',
                'issue_date' => today(),
                'valid_until' => $validated['valid_until'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::guard('web')->id(),
            ]);

            $proforma->update([
                'proforma_number' => 'PF-'.now()->format('Y').'-'.str_pad((string) $proforma->id, 6, '0', STR_PAD_LEFT),
            ]);

            ActivityLog::query()->create([
                'user_id' => Auth::guard('web')->id(),
                'action' => 'proforma_issued',
                'entity_type' => 'proforma',
                'entity_id' => $proforma->id,
                'description' => 'Proforma '.$proforma->proforma_number.' emitida para a encomenda '.$record->order_number.'.',
                'ip_address' => $request->ip(),
            ]);

            return $proforma;
        });

        return redirect()->route('admin.proformas.show', $proforma)
            ->with('success', 'Proforma emitida e associada à encomenda.');
    }

    public function show(int $proforma): View
    {
        $proforma = Proforma::query()
            ->with(['order.items', 'creator'])
            ->findOrFail($proforma);

        return view('admin.proformas.show', compact('proforma'));
    }
}
