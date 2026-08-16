<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function success(int $id)
    {
        $order = Order::with('items')->findOrFail($id);
        $whatsappNumber = Setting::get('whatsapp_number', '01775153740');
        $storeName = Setting::get('store_name', 'Fashion Store');

        return view('orders.success', compact('order', 'whatsappNumber', 'storeName'));
    }

    public function track(Request $request)
    {
        $order = null;
        $searched = false;

        if ($request->filled('order_id') && $request->filled('phone')) {
            $searched = true;
            $orderId = intval($request->query('order_id'));
            $phone = trim($request->query('phone'));

            $order = Order::with('items')
                ->where('id', $orderId)
                ->where('phone', 'like', "%{$phone}%")
                ->first();
        }

        return view('orders.track', compact('order', 'searched'));
    }

    public function invoice(int $id)
    {
        $order = Order::with('items')->findOrFail($id);
        $storeName = Setting::get('store_name', 'Fashion Store');
        $storePhone = Setting::get('contact_phone', '01775153740');
        $storeAddress = Setting::get('store_address', 'Tangail, Bangladesh');

        return view('orders.invoice', compact('order', 'storeName', 'storePhone', 'storeAddress'));
    }
}
