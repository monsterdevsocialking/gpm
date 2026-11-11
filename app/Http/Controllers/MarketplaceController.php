<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $categoryId = $request->integer('categoria');

        $categories = Category::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->active()
            ->with('category')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($categoryId > 0, fn ($query) => $query->where('category_id', $categoryId))
            ->orderByDesc('featured')
            ->orderByDesc('created_at')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('marketplace.index', compact('categories', 'products', 'search', 'categoryId'));
    }

    public function show(int $product): View
    {
        $product = Product::query()
            ->active()
            ->with(['category', 'images'])
            ->findOrFail($product);

        return view('marketplace.show', compact('product'));
    }
}
