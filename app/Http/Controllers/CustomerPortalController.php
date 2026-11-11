<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\QuoteRequest;
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

        $requests = QuoteRequest::query()
            ->where('customer_id', $customer->getKey())
            ->withCount('items')
            ->latest('submitted_at')
            ->limit(10)
            ->get();

        $totalRequests = QuoteRequest::query()
            ->where('customer_id', $customer->getKey())
            ->count();

        $totalOrders = Order::query()
            ->where('customer_id', $customer->getKey())
            ->count();

        return view('customer.dashboard', compact(
            'customer',
            'orders',
            'totalOrders',
            'requests',
            'totalRequests'
        ));
    }
}
