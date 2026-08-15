<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'images'])->where('is_active', true);

        // Filter by category slug
        if ($request->filled('category')) {
            $categorySlug = $request->query('category');
            $category = Category::where('slug', $categorySlug)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // Search by keyword
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', floatval($request->query('min_price')));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', floatval($request->query('max_price')));
        }

        // Filter by in-stock only
        if ($request->boolean('in_stock')) {
            $query->where('stock', '>', 0);
        }

        // Sorting
        $sort = $request->query('sort', 'latest');
        match ($sort) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'popular' => $query->orderBy('reviews_count', 'desc'),
            'rating' => $query->orderBy('rating', 'desc'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::withCount('products')->get();
        $activeCategory = $request->filled('category') ? Category::where('slug', $request->query('category'))->first() : null;

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('shop.partials.product_grid', compact('products'))->render(),
                'pagination' => view('shop.partials.pagination', compact('products'))->render(),
                'total' => $products->total(),
            ]);
        }

        return view('shop.index', compact('products', 'categories', 'activeCategory'));
    }

    public function searchSuggestions(Request $request)
    {
        $term = trim($request->query('q', ''));
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $results = Product::where('is_active', true)
            ->where('name', 'like', "%{$term}%")
            ->take(6)
            ->get(['id', 'name', 'slug', 'price', 'sale_price'])
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'price' => $p->effective_price,
                    'formatted_price' => '৳' . number_format($p->effective_price, 2),
                    'image' => $p->primary_image_url,
                    'url' => route('product.show', $p->slug),
                ];
            });

        return response()->json($results);
    }
}
