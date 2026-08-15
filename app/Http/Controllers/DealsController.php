<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\Request;

class DealsController extends Controller
{
    public function index()
    {
        $deals = Product::with(['category', 'images'])
            ->where('is_active', true)
            ->whereNotNull('sale_price')
            ->where('sale_price', '>', 0)
            ->latest()
            ->paginate(12);

        $coupons = Coupon::where('is_active', true)->latest()->get();

        return view('deals', compact('deals', 'coupons'));
    }
}
