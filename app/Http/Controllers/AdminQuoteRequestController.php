<?php

namespace App\Http\Controllers;

use App\Models\QuoteRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminQuoteRequestController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $requests = QuoteRequest::query()
            ->withCount('items')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('request_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest('submitted_at')
            ->paginate(20)
            ->withQueryString();

        $statuses = QuoteRequest::query()
            ->select('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        return view('admin.requests.index', compact('requests', 'search', 'status', 'statuses'));
    }

    public function show(int $quoteRequest): View
    {
        $quoteRequest = QuoteRequest::query()
            ->with('customer', 'items.product')
            ->findOrFail($quoteRequest);

        return view('admin.requests.show', compact('quoteRequest'));
    }
}
