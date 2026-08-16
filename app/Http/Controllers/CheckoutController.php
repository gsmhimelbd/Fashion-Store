<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('shop')->with('info', 'Your shopping cart is empty.');
        }

        $items = [];
        $subtotal = 0;
        foreach ($cart as $id => $item) {
            $items[] = $item;
            $subtotal += $item['price'] * $item['quantity'];
        }

        $coupon = session()->get('applied_coupon', null);
        $discount = 0;
        if ($coupon) {
            $couponModel = Coupon::where('code', $coupon['code'])->where('is_active', true)->first();
            if ($couponModel && $couponModel->isValidFor($subtotal)) {
                $discount = $couponModel->calculateDiscount($subtotal);
            }
        }

        $deliveryTangail = floatval(Setting::get('delivery_charge_tangail', 50));
        $deliveryOther = floatval(Setting::get('delivery_charge_other', 150));
        $bkash = Setting::get('bkash_number', '01775153740');
        $nagad = Setting::get('nagad_number', '01775153740');
        $rocket = Setting::get('rocket_number', '01775153740');

        return view('checkout.index', compact(
            'items',
            'subtotal',
            'coupon',
            'discount',
            'deliveryTangail',
            'deliveryOther',
            'bkash',
            'nagad',
            'rocket'
        ));
    }

    public function process(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Cart is empty.'], 422);
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'district' => 'required|string|max:100',
            'upazila' => 'required|string|max:100',
            'address' => 'required|string|max:500',
            'notes' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cod,bkash,nagad,rocket',
            'payment_number' => 'nullable|required_unless:payment_method,cod|string|max:50',
            'transaction_id' => 'nullable|required_unless:payment_method,cod|string|max:100',
        ]);

        $subtotal = 0;
        foreach ($cart as $id => $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        // Calculate delivery charge
        $deliveryCharge = (strtolower(trim($validated['district'])) === 'tangail')
            ? floatval(Setting::get('delivery_charge_tangail', 50))
            : floatval(Setting::get('delivery_charge_other', 150));

        // Calculate coupon discount
        $coupon = session()->get('applied_coupon', null);
        $discount = 0;
        $couponCode = null;
        if ($coupon) {
            $couponModel = Coupon::where('code', $coupon['code'])->where('is_active', true)->first();
            if ($couponModel && $couponModel->isValidFor($subtotal)) {
                $discount = $couponModel->calculateDiscount($subtotal);
                $couponCode = $couponModel->code;
                $couponModel->increment('times_used');
            }
        }

        $grandTotal = max(0, $subtotal + $deliveryCharge - $discount);

        $order = DB::transaction(function () use ($validated, $subtotal, $deliveryCharge, $discount, $couponCode, $grandTotal, $cart) {
            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'phone' => $validated['phone'],
                'whatsapp' => $validated['whatsapp'] ?: $validated['phone'],
                'district' => $validated['district'],
                'upazila' => $validated['upazila'],
                'address' => $validated['address'],
                'notes' => $validated['notes'],
                'payment_method' => $validated['payment_method'],
                'payment_number' => $validated['payment_number'] ?? null,
                'transaction_id' => $validated['transaction_id'] ?? null,
                'subtotal' => $subtotal,
                'delivery_charge' => $deliveryCharge,
                'discount_amount' => $discount,
                'coupon_code' => $couponCode,
                'grand_total' => $grandTotal,
                'status' => 'pending',
            ]);

            foreach ($cart as $productId => $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'product_name' => $item['name'],
                    'product_image' => $item['image'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                ]);

                // Reduce stock
                Product::where('id', $productId)->decrement('stock', $item['quantity']);
            }

            return $order;
        });

        // Clear cart
        session()->forget('cart');
        session()->forget('applied_coupon');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => route('order.success', $order->id),
                'order_id' => $order->id,
            ]);
        }

        return redirect()->route('order.success', $order->id);
    }
}
