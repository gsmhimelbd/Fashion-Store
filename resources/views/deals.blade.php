@extends('layouts.app')

@section('title', 'Exclusive Flash Deals & Offers - ' . \App\Models\Setting::get('store_name', 'OnlineBdMart'))

@section('content')

<!-- Deals Header -->
<section class="bg-gradient-to-r from-rose-950 via-slate-950 to-indigo-950 text-white py-12 border-b border-slate-800"
         x-data="{ hours: 12, mins: 48, secs: 26, init() { setInterval(() => { if (this.secs > 0) this.secs--; else { this.secs = 59; if (this.mins > 0) this.mins--; else { this.mins = 59; if (this.hours > 0) this.hours--; } } }, 1000); } }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <span class="px-3 py-1 bg-rose-500/20 text-rose-400 text-xs font-black uppercase rounded-full">🔥 FLASH PROMOTIONS</span>
            <h1 class="text-2xl sm:text-4xl font-extrabold font-serif tracking-tight mt-2">Hot Deals & Limited Offers</h1>
            <p class="text-xs sm:text-sm text-slate-300 mt-1">Up to 40% OFF on selected luxury items with Nationwide Cash On Delivery.</p>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-2xl p-4 flex items-center gap-3 text-center text-xs">
            <div><div class="text-2xl font-black font-mono text-primary-400" x-text="hours">12</div><span class="text-[10px] text-slate-400 font-bold uppercase">Hours</span></div>
            <span class="text-xl font-bold text-slate-500">:</span>
            <div><div class="text-2xl font-black font-mono text-emerald-400" x-text="mins">48</div><span class="text-[10px] text-slate-400 font-bold uppercase">Mins</span></div>
            <span class="text-xl font-bold text-slate-500">:</span>
            <div><div class="text-2xl font-black font-mono text-amber-400" x-text="secs">26</div><span class="text-[10px] text-slate-400 font-bold uppercase">Secs</span></div>
        </div>
    </div>
</section>

<!-- Active Coupons Grid -->
@if($coupons->count() > 0)
<section class="py-8 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h3 class="text-xs font-extrabold uppercase text-slate-400 tracking-wider mb-4">Available Promo Coupons</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($coupons as $coupon)
            <div class="p-4 rounded-2xl bg-slate-50 border-2 border-dashed border-primary-200 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-400">Coupon Code</span>
                    <h4 class="text-base font-black font-mono text-primary-600">{{ $coupon->code }}</h4>
                    <p class="text-[11px] text-slate-500 font-semibold">{{ $coupon->type === 'percentage' ? $coupon->value . '% OFF' : '৳' . $coupon->value . ' FLAT OFF' }} (Min: ৳{{ number_format($coupon->min_spend ?: 0) }})</p>
                </div>
                <button type="button" onclick="navigator.clipboard.writeText('{{ $coupon->code }}'); showToast('Copied: {{ $coupon->code }}', 'success');" class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow">
                    Copy Code
                </button>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Deals Products -->
<section class="py-12 bg-slate-50 min-h-[60vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($deals as $product)
            <div class="group bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl transition flex flex-col justify-between overflow-hidden relative">
                <span class="absolute top-3 left-3 z-10 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white shadow-sm">
                    -{{ $product->discount_percentage }}% OFF
                </span>

                <a href="{{ route('product.show', $product->slug) }}" class="relative block aspect-square bg-slate-100 overflow-hidden">
                    <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                </a>

                <div class="p-4 flex flex-col justify-between flex-1">
                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400">{{ $product->category->name ?? 'Accessories' }}</span>
                        <a href="{{ route('product.show', $product->slug) }}" class="text-xs sm:text-sm font-bold text-slate-900 group-hover:text-primary-600 transition line-clamp-2 block mt-0.5">{{ $product->name }}</a>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-sm sm:text-base font-black text-slate-900">৳{{ number_format($product->effective_price, 2) }}</span>
                            <span class="text-[11px] text-slate-400 line-through block">৳{{ number_format($product->price, 2) }}</span>
                        </div>
                        <button type="button" @click="addToCart({{ $product->id }})" class="px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow">
                            <i class="fas fa-bag-shopping"></i> Add
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-8 flex justify-center">
            {{ $deals->links() }}
        </div>
    </div>
</section>

@endsection
