@extends('layouts.app')

@section('title', 'Order Confirmed #' . $order->id . ' - ' . $storeName)

@section('content')

<section class="py-16 bg-slate-50 min-h-[80vh] flex items-center">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        
        <!-- Success Card -->
        <div class="bg-white rounded-3xl border border-slate-200 p-8 sm:p-10 shadow-xl space-y-8 text-center">
            
            <!-- Animated Green Checkmark -->
            <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-4xl mx-auto shadow-lg shadow-emerald-500/20 animate-bounce">
                <i class="fas fa-check"></i>
            </div>

            <div>
                <span class="text-xs font-extrabold uppercase tracking-widest text-emerald-600">ORDER RECEIVED</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-serif mt-1">Thank You For Your Order!</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-2">
                    Your order <strong class="text-primary-600 font-mono text-sm">#{{ $order->id }}</strong> has been successfully placed. We will contact you soon to confirm delivery dispatch.
                </p>
            </div>

            <!-- Order Summary Details -->
            <div class="bg-slate-50 rounded-2xl p-6 text-left border border-slate-200/80 space-y-4 text-xs">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <span class="text-slate-500">Order ID:</span>
                    <span class="font-mono font-bold text-slate-900">#{{ $order->id }}</span>
                </div>

                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <span class="text-slate-500">Customer Name:</span>
                    <span class="font-bold text-slate-900">{{ $order->customer_name }}</span>
                </div>

                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <span class="text-slate-500">Delivery Address:</span>
                    <span class="font-medium text-slate-900 text-right">{{ $order->address }}, {{ $order->upazila }}, {{ $order->district }}</span>
                </div>

                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <span class="text-slate-500">Payment Method:</span>
                    <span class="font-bold uppercase text-primary-600">{{ $order->payment_method }}</span>
                </div>

                <!-- Items Breakdown -->
                <div class="space-y-2 pt-1">
                    <p class="font-bold text-slate-700 uppercase tracking-wider text-[10px]">Purchased Items:</p>
                    @foreach($order->items as $item)
                    <div class="flex justify-between text-slate-600">
                        <span>{{ $item->product_name }} &times; {{ $item->quantity }}</span>
                        <span class="font-bold text-slate-900">৳{{ number_format($item->price * $item->quantity, 2) }}</span>
                    </div>
                    @endforeach
                </div>

                <div class="pt-3 border-t border-slate-200 flex justify-between text-sm font-extrabold text-slate-900">
                    <span>Grand Total:</span>
                    <span class="text-primary-600 text-base">৳{{ number_format($order->grand_total, 2) }}</span>
                </div>
            </div>

            <!-- Actions Button Group -->
            <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                <a href="{{ route('order.invoice', $order->id) }}" target="_blank" class="px-6 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
                    <i class="fas fa-print"></i> Print Invoice Receipt
                </a>

                <a href="https://wa.me/88{{ $whatsappNumber }}?text={{ urlencode('Hello, I have placed order #' . $order->id . '. Please confirm my delivery.') }}" 
                   target="_blank" 
                   class="px-6 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
                    <i class="fab fa-whatsapp text-sm"></i> WhatsApp Support
                </a>

                <a href="{{ route('shop') }}" class="px-6 py-3 rounded-xl bg-primary-50 text-primary-700 hover:bg-primary-100 font-bold text-xs transition">
                    Continue Shopping
                </a>
            </div>

        </div>

    </div>
</section>

@endsection
