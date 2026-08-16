<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::with(['category', 'images', 'reviews' => function ($q) {
            $q->where('is_approved', true)->latest();
        }])->where('slug', $slug)->firstOrFail();

        $relatedProducts = Product::with(['category', 'images'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->inRandomOrder()
            ->take(4)
            ->get();

        $whatsappNumber = Setting::get('whatsapp_number', '01775153740');
        $storeUrl = url("/product/{$product->slug}");
        $whatsappMessage = urlencode("Hello, I want to order this product: *{$product->name}* - Price: ৳" . number_format($product->effective_price, 2) . "\nLink: {$storeUrl}");

        return view('shop.show', compact('product', 'relatedProducts', 'whatsappNumber', 'whatsappMessage'));
    }

    public function quickView(int $id)
    {
        $product = Product::with(['category', 'images'])->findOrFail($id);
        return response()->json([
            'html' => view('shop.partials.quick_view_modal', compact('product'))->render()
        ]);
    }

    public function storeReview(Request $request, int $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_email' => 'nullable|email|max:100',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'required|string|max:1000',
        ]);

        $review = $product->reviews()->create($validated);

        // Update product average rating
        $avg = $product->reviews()->where('is_approved', true)->avg('rating') ?: 5.0;
        $count = $product->reviews()->where('is_approved', true)->count();
        $product->update([
            'rating' => round($avg, 1),
            'reviews_count' => $count,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you! Your review has been submitted.',
            ]);
        }

        return back()->with('success', 'Thank you! Your review has been submitted.');
    }
}
