<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $allowedStatuses = ['pending', 'confirmed', 'preparing', 'shipped', 'completed', 'cancelled'];

        $orders = Order::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%")
                        ->orWhere('customer_nif', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, $allowedStatuses, true), fn ($query) => $query->where('status', $status))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'search', 'status', 'allowedStatuses'));
    }

    public function show(int $order): View
    {
        $order = Order::query()
            ->with(['customer', 'items', 'proformas', 'statusHistory.user', 'conversation.messages', 'proposalVersion.proposal'])
            ->findOrFail($order);

        return view('admin.orders.show', compact('order'));
    }
    public function updateStatus(Request $request, int $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,preparing,shipped,completed,cancelled'],
            'note' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($order, $validated): void {
            $record = Order::query()->lockForUpdate()->findOrFail($order);

            $transitions = [
                'pending' => ['confirmed', 'cancelled'],
                'confirmed' => ['preparing', 'cancelled'],
                'preparing' => ['shipped', 'cancelled'],
                'shipped' => ['completed'],
                'completed' => [],
                'cancelled' => [],
            ];

            $oldStatus = $record->status;
            $newStatus = $validated['status'];

            if (! in_array($newStatus, $transitions[$oldStatus] ?? [], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Esta transição de estado não é permitida para a encomenda.',
                ]);
            }

            $changes = ['status' => $newStatus];

            if ($newStatus === 'confirmed') {
                $changes['confirmed_at'] = now();
            } elseif ($newStatus === 'completed') {
                $changes['completed_at'] = now();
            } elseif ($newStatus === 'cancelled') {
                $changes['cancelled_at'] = now();
            }

            $record->update($changes);
            $record->statusHistory()->create([
                'user_id' => Auth::guard('web')->id(),
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'note' => trim((string) ($validated['note'] ?? '')) ?: null,
            ]);
        });

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Estado da encomenda atualizado e registado no histórico.');
    }

}
