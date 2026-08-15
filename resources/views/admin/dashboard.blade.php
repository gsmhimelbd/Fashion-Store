@extends('layouts.admin')

@section('page_title', 'Admin Overview')

@section('content')

<!-- KPI Stat Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
    
    <!-- Total Revenue -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div class="space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Delivered Revenue</span>
            <p class="text-2xl font-extrabold text-slate-900">৳{{ number_format($totalRevenue, 2) }}</p>
            <span class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
                <i class="fas fa-arrow-trend-up"></i> High performance
            </span>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
            <i class="fas fa-bangladeshi-taka-sign"></i>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div class="space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Orders</span>
            <p class="text-2xl font-extrabold text-slate-900">{{ $totalOrders }}</p>
            <span class="text-[10px] text-primary-600 font-bold">{{ $deliveredOrders }} Delivered successfully</span>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center text-xl shrink-0">
            <i class="fas fa-cart-shopping"></i>
        </div>
    </div>

    <!-- Pending Action Orders -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div class="space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pending Confirmation</span>
            <p class="text-2xl font-extrabold text-amber-600">{{ $pendingOrders }}</p>
            <span class="text-[10px] text-amber-500 font-bold">Needs dispatch attention</span>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
            <i class="fas fa-clock"></i>
        </div>
    </div>

    <!-- Total Products & Stock -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div class="space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Active Products</span>
            <p class="text-2xl font-extrabold text-slate-900">{{ $totalProducts }}</p>
            <span class="text-[10px] {{ $lowStockProducts > 0 ? 'text-rose-500' : 'text-slate-400' }} font-bold">
                {{ $lowStockProducts }} low in stock (&le;5)
            </span>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
            <i class="fas fa-box-open"></i>
        </div>
    </div>

</div>

<!-- Order Status Overview Breakdown -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
    <div class="bg-amber-50/70 border border-amber-200/80 p-3.5 rounded-xl text-center">
        <span class="text-[10px] font-bold uppercase text-amber-700">Pending</span>
        <p class="text-xl font-extrabold text-amber-800 mt-0.5">{{ $pendingOrders }}</p>
    </div>
    <div class="bg-blue-50/70 border border-blue-200/80 p-3.5 rounded-xl text-center">
        <span class="text-[10px] font-bold uppercase text-blue-700">Confirmed</span>
        <p class="text-xl font-extrabold text-blue-800 mt-0.5">{{ $confirmedOrders }}</p>
    </div>
    <div class="bg-indigo-50/70 border border-indigo-200/80 p-3.5 rounded-xl text-center">
        <span class="text-[10px] font-bold uppercase text-indigo-700">Processing</span>
        <p class="text-xl font-extrabold text-indigo-800 mt-0.5">{{ $processingOrders }}</p>
    </div>
    <div class="bg-purple-50/70 border border-purple-200/80 p-3.5 rounded-xl text-center">
        <span class="text-[10px] font-bold uppercase text-purple-700">Shipped</span>
        <p class="text-xl font-extrabold text-purple-800 mt-0.5">{{ \App\Models\Order::where('status', 'shipped')->count() }}</p>
    </div>
    <div class="bg-emerald-50/70 border border-emerald-200/80 p-3.5 rounded-xl text-center">
        <span class="text-[10px] font-bold uppercase text-emerald-700">Delivered</span>
        <p class="text-xl font-extrabold text-emerald-800 mt-0.5">{{ $deliveredOrders }}</p>
    </div>
    <div class="bg-rose-50/70 border border-rose-200/80 p-3.5 rounded-xl text-center">
        <span class="text-[10px] font-bold uppercase text-rose-700">Cancelled</span>
        <p class="text-xl font-extrabold text-rose-800 mt-0.5">{{ $cancelledOrders }}</p>
    </div>
</div>

<!-- Recent Orders Table & Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Recent Orders (2 Columns) -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Recent Customer Orders</h3>
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-primary-600 hover:underline">View All &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-3">Order #</th>
                        <th class="py-3 px-3">Customer</th>
                        <th class="py-3 px-3">District</th>
                        <th class="py-3 px-3">Total</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentOrders as $order)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3 px-3 font-mono font-bold text-slate-900">#{{ $order->id }}</td>
                        <td class="py-3 px-3 font-semibold text-slate-800">{{ $order->customer_name }}</td>
                        <td class="py-3 px-3 text-slate-500">{{ $order->district }}</td>
                        <td class="py-3 px-3 font-bold text-slate-900">৳{{ number_format($order->grand_total, 2) }}</td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold capitalize border {{ $order->status_badge_class }}">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-right">
                            <a href="{{ route('admin.orders.index') }}" class="text-primary-600 hover:text-primary-800 font-bold">
                                Manage
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">No orders received yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Shortcuts Card (1 Column) -->
    <div class="lg:col-span-1 space-y-6">
        
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Quick Shortcuts</h3>
            
            <div class="space-y-2 text-xs font-semibold">
                <a href="{{ route('admin.products.index') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-primary-50 hover:text-primary-600 transition">
                    <span class="flex items-center gap-2.5">
                        <i class="fas fa-plus text-primary-500"></i> Add New Product
                    </span>
                    <i class="fas fa-chevron-right text-slate-300"></i>
                </a>

                <a href="{{ route('admin.banners.index') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-primary-50 hover:text-primary-600 transition">
                    <span class="flex items-center gap-2.5">
                        <i class="fas fa-images text-indigo-500"></i> Update Hero Banners
                    </span>
                    <i class="fas fa-chevron-right text-slate-300"></i>
                </a>

                <a href="{{ route('admin.settings.index') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-primary-50 hover:text-primary-600 transition">
                    <span class="flex items-center gap-2.5">
                        <i class="fas fa-sliders text-emerald-500"></i> Delivery & Payment Setup
                    </span>
                    <i class="fas fa-chevron-right text-slate-300"></i>
                </a>

                <a href="{{ route('admin.coupons.index') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-primary-50 hover:text-primary-600 transition">
                    <span class="flex items-center gap-2.5">
                        <i class="fas fa-ticket text-amber-500"></i> Discount Promo Codes
                    </span>
                    <i class="fas fa-chevron-right text-slate-300"></i>
                </a>
            </div>
        </div>

        <!-- Store Status Card -->
        <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white rounded-2xl p-6 shadow-md space-y-3">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-400">Live & Operating</span>
            <h4 class="text-base font-bold font-serif">{{ \App\Models\Setting::get('store_name', 'Fashion Store') }}</h4>
            <p class="text-xs text-slate-300">Fast Cash on Delivery enabled for all 64 districts in Bangladesh.</p>
            <a href="{{ route('home') }}" target="_blank" class="inline-block pt-2 text-xs font-bold text-cyan-400 hover:underline">
                Open Frontend Store &rarr;
            </a>
        </div>

    </div>

</div>

@endsection
