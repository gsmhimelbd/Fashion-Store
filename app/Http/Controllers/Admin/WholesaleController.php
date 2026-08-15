<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\WholesaleInquiry;
use Illuminate\Http\Request;

class WholesaleController extends Controller
{
    public function index()
    {
        $inquiries = WholesaleInquiry::latest()->paginate(15);
        $wholesaleProducts = Product::where('is_wholesale', true)->latest()->get();
        return view('admin.wholesale.index', compact('inquiries', 'wholesaleProducts'));
    }

    public function toggleProduct(Request $request, int $id)
    {
        $product = Product::findOrFail($id);
        $product->update([
            'is_wholesale' => !$product->is_wholesale,
            'wholesale_price' => $request->input('wholesale_price', $product->wholesale_price ?: ($product->price * 0.75)),
            'wholesale_min_qty' => $request->input('wholesale_min_qty', $product->wholesale_min_qty ?: 5),
        ]);

        return back()->with('success', 'Wholesale product configuration updated.');
    }
}
