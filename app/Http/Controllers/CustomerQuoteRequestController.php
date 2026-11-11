<?php

namespace App\Http\Controllers;

use App\Models\QuoteRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerQuoteRequestController extends Controller
{
    public function show(int $quoteRequest): View
    {
        $quoteRequest = QuoteRequest::query()
            ->where('customer_id', Auth::guard('customer')->id())
            ->with('items')
            ->findOrFail($quoteRequest);

        return view('customer.requests.show', compact('quoteRequest'));
    }
}
