<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'products' => Product::query()->where('active', true)->count(),
            'customers' => Customer::query()->count(),
            'orders' => Order::query()->count(),
            'pending' => Order::query()->where('status', 'pending')->count(),
            'low_stock' => Product::query()
                ->where('active', true)
                ->whereColumn('stock_quantity', '<=', 'min_stock')
                ->count(),
        ];

        $staff = Auth::guard('web')->user();
        $canReviewRequests = in_array($staff?->role, ['admin', 'seller'], true);
        $stats['quote_requests'] = $canReviewRequests ? QuoteRequest::query()->count() : null;
        $stats['submitted_requests'] = $canReviewRequests ? QuoteRequest::query()->where('status', 'submitted')->count() : null;

        $recentRequests = $canReviewRequests
            ? QuoteRequest::query()->withCount('items')->latest('submitted_at')->limit(6)->get()
            : collect();

        $recentOrders = Order::query()
            ->latest('created_at')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'recentRequests', 'canReviewRequests'));
    }
}
