@extends('layouts.app')

@section('title', 'Track Your Order - ' . \App\Models\Setting::get('store_name', 'OnlineBdMart'))

@section('content')

<!-- Header -->
<section class="bg-slate-900 text-white py-12 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center max-w-xl">
        <h1 class="text-2xl sm:text-4xl font-extrabold font-serif tracking-tight">Track Your Order</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-2">Enter your Order ID and Phone Number to check real-time order status.</p>
    </div>
</section>

<!-- Tracking Search Box & Results -->
<section class="py-12 bg-slate-50 min-h-[70vh]">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Search Form Card -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm space-y-4">
            <form method="GET" action="{{ route('order.track') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Order ID *</label>
                    <input type="number" name="order_id" required value="{{ request('order_id') }}" placeholder="e.g. 1001" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:border-primary-500 font-mono">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">Phone Number *</label>
                    <input type="tel" name="phone" required value="{{ request('phone') }}" placeholder="e.g. 01711223344" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:border-primary-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2">
                        <i class="fas fa-search"></i> Track Status
                    </button>
                </div>
            </form>
        </div>

        <!-- Tracking Results Details -->
        @if($searched)
            @if($order)
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm space-y-8 animate-fade-in-up">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Order Information</span>
                        <h2 class="text-lg font-extrabold text-slate-900 font-serif">Order #{{ $order->id }}</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
                    </div>

                    <div>
                        <span class="px-3.5 py-1.5 rounded-full text-xs font-extrabold capitalize border {{ $order->status_badge_class }}">
                            Status: {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>

                <!-- Animated Progress Timeline -->
                @php
                    $steps = [
                        'pending' => 'Order Received',
                        'confirmed' => 'Confirmed',
                        'processing' => 'Packaging',
                        'shipped' => 'On the Way',
                        'delivered' => 'Delivered'
                    ];
                    $stepKeys = array_keys($steps);
                    $currentIndex = array_search($order->status, $stepKeys);
                    if ($order->status === 'cancelled') $currentIndex = -1;
                @endphp

                @if($order->status !== 'cancelled')
                <div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-6">Delivery Timeline</h3>
                    <div class="grid grid-cols-5 gap-2 text-center text-[10px] sm:text-xs font-bold">
                        @foreach($steps as $key => $label)
                        @php
                            $stepIdx = array_search($key, $stepKeys);
                            $isPassed = $stepIdx <= $currentIndex;
                            $isCurrent = $stepIdx === $currentIndex;
                        @endphp
                        <div class="space-y-2">
                            <div class="w-10 h-10 mx-auto rounded-full flex items-center justify-center text-sm transition-all duration-300 {{ $isPassed ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30 ring-4 ring-emerald-100' : 'bg-slate-100 text-slate-400' }}">
                                <i class="fas {{ $isPassed ? 'fa-check' : 'fa-circle-dot' }}"></i>
                            </div>
                            <span class="block {{ $isCurrent ? 'text-emerald-700 font-extrabold' : ($isPassed ? 'text-slate-800' : 'text-slate-400') }}">
                                {{ $label }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-3">
                    <i class="fas fa-circle-xmark text-lg text-rose-600"></i>
                    <span>This order has been cancelled. If you believe this is a mistake, please contact our support team.</span>
                </div>
                @endif

                <!-- Items Purchased & Delivery Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100 text-xs">
                    <div class="space-y-2">
                        <h4 class="font-bold text-slate-900 uppercase tracking-wider text-[10px]">Delivery Recipient</h4>
                        <p class="text-slate-700"><strong>Name:</strong> {{ $order->customer_name }}</p>
                        <p class="text-slate-700"><strong>Phone:</strong> {{ $order->phone }}</p>
                        <p class="text-slate-700"><strong>Address:</strong> {{ $order->address }}, {{ $order->upazila }}, {{ $order->district }}</p>
                    </div>

                    <div class="space-y-2">
                        <h4 class="font-bold text-slate-900 uppercase tracking-wider text-[10px]">Payment & Total</h4>
                        <p class="text-slate-700"><strong>Method:</strong> {{ strtoupper($order->payment_method) }}</p>
                        <p class="text-slate-700"><strong>Subtotal:</strong> ৳{{ number_format($order->subtotal, 2) }}</p>
                        <p class="text-slate-700"><strong>Delivery Fee:</strong> ৳{{ number_format($order->delivery_charge, 2) }}</p>
                        <p class="text-primary-600 font-extrabold text-sm pt-1"><strong>Grand Total:</strong> ৳{{ number_format($order->grand_total, 2) }}</p>
                    </div>
                </div>

            </div>
            @else
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm text-center space-y-3">
                <div class="w-14 h-14 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center text-2xl mx-auto">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900 font-serif">Order Not Found</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">Please check your Order ID and Phone Number and try again.</p>
            </div>
            @endif
        @endif

    </div>
</section>

@endsection
