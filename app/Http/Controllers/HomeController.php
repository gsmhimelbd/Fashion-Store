<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $banners = Banner::where('is_active', true)->orderBy('order')->get();
        $categories = Category::withCount('products')->get();
        $featuredProducts = Product::with(['category', 'images'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest()
            ->take(8)
            ->get();

        $newArrivals = Product::with(['category', 'images'])
            ->where('is_active', true)
            ->latest()
            ->take(8)
            ->get();

        $bestSellers = Product::with(['category', 'images'])
            ->where('is_active', true)
            ->orderBy('reviews_count', 'desc')
            ->take(4)
            ->get();

        return view('home', compact('banners', 'categories', 'featuredProducts', 'newArrivals', 'bestSellers'));
    }
}
