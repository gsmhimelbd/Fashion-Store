<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $items = $this->getCartDetails($cart);
        $subtotal = $items['subtotal'];
        $coupon = session()->get('applied_coupon', null);
        $discount = 0;

        if ($coupon) {
            $couponModel = Coupon::where('code', $coupon['code'])->where('is_active', true)->first();
            if ($couponModel && $couponModel->isValidFor($subtotal)) {
                $discount = $couponModel->calculateDiscount($subtotal);
            } else {
                session()->forget('applied_coupon');
                $coupon = null;
            }
        }

        $freeDeliveryThreshold = floatval(Setting::get('free_delivery_threshold', 2000));
        $deliveryChargeTangail = floatval(Setting::get('delivery_charge_tangail', 50));
        $deliveryChargeOther = floatval(Setting::get('delivery_charge_other', 150));

        return view('cart.index', compact(
            'items',
            'subtotal',
            'coupon',
            'discount',
            'freeDeliveryThreshold',
            'deliveryChargeTangail',
            'deliveryChargeOther'
        ));
    }

    public function add(Request $request)
    {
        $productId = intval($request->input('product_id', 0));
        $isWholesale = $request->boolean('is_wholesale', false);
        $product = Product::with('images')->find($productId);

        if (!$product || !$product->is_active) {
            return response()->json(['success' => false, 'message' => 'Product not available.'], 404);
        }

        $minQty = ($isWholesale || $request->input('type') === 'wholesale') ? ($product->wholesale_min_qty ?: 5) : 1;
        $quantity = max($minQty, intval($request->input('quantity', $minQty)));

        // Determine price: if wholesale requested or quantity >= wholesale_min_qty, use wholesale price
        $price = $product->effective_price;
        if ($isWholesale || $request->input('type') === 'wholesale' || ($product->is_wholesale && $quantity >= ($product->wholesale_min_qty ?: 5))) {
            $price = $product->wholesale_price ?: ($product->price * 0.75);
            $isWholesale = true;
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
            if ($cart[$productId]['quantity'] >= ($product->wholesale_min_qty ?: 5) && $product->wholesale_price) {
                $cart[$productId]['price'] = $product->wholesale_price;
                $cart[$productId]['is_wholesale'] = true;
            }
        } else {
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $price,
                'retail_price' => $product->price,
                'image' => $product->primary_image_url,
                'quantity' => $quantity,
                'is_wholesale' => $isWholesale,
                'wholesale_min_qty' => $product->wholesale_min_qty ?: 5,
            ];
        }

        session()->put('cart', $cart);

        $cartDetails = $this->getCartDetails($cart);

        return response()->json([
            'success' => true,
            'message' => $isWholesale ? "Added {$quantity} pcs to cart at wholesale rate!" : 'Added to cart successfully!',
            'count' => $cartDetails['total_count'],
            'subtotal' => $cartDetails['subtotal'],
            'formatted_subtotal' => '৳' . number_format($cartDetails['subtotal'], 2),
            'items' => $cartDetails['items'],
        ]);
    }

    public function update(Request $request)
    {
        $productId = intval($request->input('product_id', 0));
        $quantity = intval($request->input('quantity', 0));

        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            if ($quantity > 0) {
                $cart[$productId]['quantity'] = $quantity;
                $product = Product::find($productId);
                if ($product && $product->wholesale_price && $quantity >= ($product->wholesale_min_qty ?: 5)) {
                    $cart[$productId]['price'] = $product->wholesale_price;
                    $cart[$productId]['is_wholesale'] = true;
                } elseif ($product) {
                    $cart[$productId]['price'] = $product->effective_price;
                    $cart[$productId]['is_wholesale'] = false;
                }
            } else {
                unset($cart[$productId]);
            }
            session()->put('cart', $cart);
        }

        $cartDetails = $this->getCartDetails($cart);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated.',
            'count' => $cartDetails['total_count'],
            'subtotal' => $cartDetails['subtotal'],
            'formatted_subtotal' => '৳' . number_format($cartDetails['subtotal'], 2),
            'item_total' => isset($cart[$productId]) ? '৳' . number_format($cart[$productId]['price'] * $cart[$productId]['quantity'], 2) : 0,
        ]);
    }

    public function remove(Request $request)
    {
        $productId = intval($request->input('product_id', 0));
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
        }

        $cartDetails = $this->getCartDetails($cart);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.',
            'count' => $cartDetails['total_count'],
            'subtotal' => $cartDetails['subtotal'],
            'formatted_subtotal' => '৳' . number_format($cartDetails['subtotal'], 2),
        ]);
    }

    public function clear()
    {
        session()->forget('cart');
        session()->forget('applied_coupon');
        return response()->json(['success' => true, 'count' => 0, 'subtotal' => 0]);
    }

    public function applyCoupon(Request $request)
    {
        $code = strtoupper(trim($request->input('code', '')));
        $cart = session()->get('cart', []);
        $cartDetails = $this->getCartDetails($cart);
        $subtotal = $cartDetails['subtotal'];

        $coupon = Coupon::where('code', $code)->where('is_active', true)->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired coupon code.'], 422);
        }

        if (!$coupon->isValidFor($subtotal)) {
            $min = $coupon->min_spend ? ' Minimum spend required: ৳' . number_format($coupon->min_spend, 2) : '';
            return response()->json(['success' => false, 'message' => 'Coupon conditions not met.' . $min], 422);
        }

        $discount = $coupon->calculateDiscount($subtotal);
        session()->put('applied_coupon', [
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'discount' => $discount,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Coupon '{$coupon->code}' applied successfully!",
            'discount' => $discount,
            'formatted_discount' => '৳' . number_format($discount, 2),
            'new_total' => $subtotal - $discount,
        ]);
    }

    public function removeCoupon()
    {
        session()->forget('applied_coupon');
        return response()->json(['success' => true, 'message' => 'Coupon removed.']);
    }

    private function getCartDetails(array $cart): array
    {
        $items = [];
        $subtotal = 0;
        $totalCount = 0;

        foreach ($cart as $id => $item) {
            $total = $item['price'] * $item['quantity'];
            $subtotal += $total;
            $totalCount += $item['quantity'];
            $items[] = array_merge($item, ['total' => $total]);
        }

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'total_count' => $totalCount,
        ];
    }
}
