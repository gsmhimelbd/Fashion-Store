<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use App\Models\WholesaleInquiry;
use Illuminate\Http\Request;

class WholesaleController extends Controller
{
    public function index()
    {
        $wholesaleProducts = Product::with(['category', 'images'])
            ->where('is_active', true)
            ->where('is_wholesale', true)
            ->latest()
            ->paginate(12);

        // Fallback: If no products marked as wholesale, get latest products with discounted wholesale rate
        if ($wholesaleProducts->isEmpty()) {
            $wholesaleProducts = Product::with(['category', 'images'])
                ->where('is_active', true)
                ->latest()
                ->paginate(12);
        }

        $whatsappNumber = Setting::get('whatsapp_number', '01775153740');
        $contactPhone = Setting::get('contact_phone', '01775153740');

        return view('wholesale', compact('wholesaleProducts', 'whatsappNumber', 'contactPhone'));
    }

    public function submitInquiry(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:150',
            'contact_person' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'district' => 'required|string|max:100',
            'products_interested' => 'nullable|string|max:255',
            'estimated_monthly_quantity' => 'nullable|string|max:100',
            'message' => 'nullable|string|max:1000',
        ]);

        WholesaleInquiry::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Wholesale inquiry received! Our B2B manager will contact you within 2 business hours.',
            ]);
        }

        return back()->with('success', 'Wholesale inquiry received! Our B2B manager will contact you within 2 business hours.');
    }
}
