<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\ContactMessage;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $company = CompanySetting::query()->first();
        $products = Product::query()
            ->active()
            ->orderByDesc('featured')
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();

        return view('home', compact('company', 'products'));
    }

    public function storeContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['nullable', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactMessage::query()->create($validated);

        return redirect()
            ->route('home')
            ->with('contact_success', 'A sua mensagem foi enviada. A equipa GPM entrará em contacto.');
    }
}
