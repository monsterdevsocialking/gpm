<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
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
            ->with(['customer', 'items', 'proformas', 'statusHistory.user', 'conversation.messages'])
            ->findOrFail($order);

        return view('admin.orders.show', compact('order'));
    }
}
