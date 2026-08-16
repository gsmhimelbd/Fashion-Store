@extends('layouts.app')

@section('title', 'My Account - ' . \App\Models\Setting::get('store_name', 'OnlineBdMart'))

@section('content')

<section class="py-12 bg-slate-50 min-h-[70vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- User Welcome Header -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-primary-100 text-primary-700 font-extrabold text-xl flex items-center justify-center">
                    {{ strtoupper(substr(session('customer_name', 'U'), 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900 font-serif">Welcome, {{ session('customer_name', 'Customer') }}</h1>
                    <p class="text-xs text-slate-500">{{ session('customer_phone', '') }} • {{ session('customer_email', '') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3 text-xs font-bold">
                <a href="{{ route('order.track') }}" class="px-4 py-2.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl hover:bg-emerald-100 transition">
                    <i class="fas fa-truck-fast mr-1"></i> Track Order
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 bg-rose-50 text-rose-700 border border-rose-200 rounded-xl hover:bg-rose-100 transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Recent Orders List -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">My Recent Orders</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px]">
                        <tr>
                            <th class="p-3">Order #</th>
                            <th class="p-3">Date</th>
                            <th class="p-3">Payment</th>
                            <th class="p-3">Total</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-right">Invoice</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($orders as $order)
                        <tr>
                            <td class="p-3 font-mono font-bold">#{{ $order->id }}</td>
                            <td class="p-3 text-slate-500">{{ $order->created_at->format('M d, Y') }}</td>
                            <td class="p-3 uppercase font-semibold">{{ $order->payment_method }}</td>
                            <td class="p-3 font-bold text-slate-900">৳{{ number_format($order->grand_total, 2) }}</td>
                            <td class="p-3"><span class="px-2.5 py-1 rounded-full text-[10px] font-bold capitalize {{ $order->status_badge_class }}">{{ $order->status }}</span></td>
                            <td class="p-3 text-right">
                                <a href="{{ route('order.invoice', $order->id) }}" target="_blank" class="px-3 py-1 bg-slate-900 text-white rounded-lg text-[10px] font-bold">Receipt</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">No orders found under this account yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>

@endsection
