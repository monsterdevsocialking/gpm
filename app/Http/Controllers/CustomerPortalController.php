<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerPortalController extends Controller
{
    public function index(): View
    {
        $customer = Auth::guard('customer')->user();

        $orders = Order::query()
            ->where('customer_id', $customer->getKey())
            ->with(['items', 'proformas'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        $totalOrders = Order::query()
            ->where('customer_id', $customer->getKey())
            ->count();

        return view('customer.dashboard', compact('customer', 'orders', 'totalOrders'));
    }
}
