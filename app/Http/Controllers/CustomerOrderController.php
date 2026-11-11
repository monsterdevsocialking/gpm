<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Proforma;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    public function show(int $order): View
    {
        $customerId = Auth::guard('customer')->id();

        $order = Order::query()
            ->where('customer_id', $customerId)
            ->with(['items', 'proformas', 'statusHistory', 'conversation.messages', 'proposalVersion.proposal'])
            ->findOrFail($order);

        $proforma = $order->proformas->sortByDesc('id')->first();

        return view('customer.orders.show', compact('order', 'proforma'));
    }

    public function showProforma(int $proforma): View
    {
        $customerId = Auth::guard('customer')->id();

        $proforma = Proforma::query()
            ->whereHas('order', fn ($query) => $query->where('customer_id', $customerId))
            ->with('order.items')
            ->findOrFail($proforma);

        return view('customer.proformas.show', compact('proforma'));
    }
}
