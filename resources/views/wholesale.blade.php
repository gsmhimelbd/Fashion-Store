@extends('layouts.app')

@section('title', 'Wholesale & B2B Bulk Order - ' . \App\Models\Setting::get('store_name', 'OnlineBdMart'))

@section('content')

<!-- Wholesale Hero Banner (like onlinebdmart.com) -->
<section class="bg-gradient-to-r from-slate-950 via-indigo-950 to-slate-900 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
            <div class="space-y-4">
                <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-amber-500/20 border border-amber-500/30 text-amber-400 text-xs font-black tracking-widest uppercase">
                    <i class="fas fa-boxes-stacked"></i> Wholesale / B2B Rate
                </span>
                <h1 class="text-3xl sm:text-5xl font-extrabold font-serif leading-tight">
                    Bulk Prices for Retailers & Resellers
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                    OnlineBdMart Wholesale provides factory rates and minimum order quantities directly for shop owners, online resellers, and corporate gift orders. Add products to cart in bulk or order via WhatsApp!
                </p>
                <div class="pt-2 flex flex-wrap items-center gap-4 text-xs font-bold">
                    <a href="#wholesale-products" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-xl shadow-lg transition font-black">
                        Browse Wholesale Items &rarr;
                    </a>
                    <a href="https://wa.me/88{{ $whatsappNumber }}?text={{ urlencode('Hello, I am interested in bulk wholesale purchases at OnlineBdMart.') }}" target="_blank" class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl shadow-lg transition flex items-center gap-2">
                        <i class="fab fa-whatsapp text-sm"></i> WhatsApp B2B Agent
                    </a>
                </div>
            </div>

            <!-- Wholesale Badges -->
            <div class="grid grid-cols-2 gap-4 text-center text-xs">
                <div class="p-5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md space-y-1">
                    <span class="text-2xl font-black text-amber-400">25% - 40%</span>
                    <h4 class="font-bold text-white">Below Retail Price</h4>
                    <p class="text-[10px] text-slate-400">Direct factory bulk rate</p>
                </div>
                <div class="p-5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md space-y-1">
                    <span class="text-2xl font-black text-emerald-400">5 Pcs</span>
                    <h4 class="font-bold text-white">Low Minimum Quantity</h4>
                    <p class="text-[10px] text-slate-400">Add to Cart in bulk</p>
                </div>
                <div class="p-5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md space-y-1">
                    <span class="text-2xl font-black text-primary-400">64 Districts</span>
                    <h4 class="font-bold text-white">Nationwide Courier</h4>
                    <p class="text-[10px] text-slate-400">Cash on Delivery available</p>
                </div>
                <div class="p-5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md space-y-1">
                    <span class="text-2xl font-black text-indigo-400">100% Verified</span>
                    <h4 class="font-bold text-white">Original Quality</h4>
                    <p class="text-[10px] text-slate-400">Replacement warranty</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Wholesale Products Grid with Add to Cart & WhatsApp Order -->
<section id="wholesale-products" class="py-14 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
            <div>
                <span class="text-xs font-extrabold uppercase tracking-widest text-primary-600">Available For Bulk Order</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-serif mt-1">Wholesale Products Catalog</h2>
            </div>
            <span class="text-xs text-slate-500 font-semibold">Select quantity and Add to Bag or Order via WhatsApp</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($wholesaleProducts as $product)
            @php
                $wholesalePrice = $product->wholesale_price ?: ($product->price * 0.75);
                $minQty = $product->wholesale_min_qty ?: 5;
            @endphp
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl transition flex flex-col justify-between overflow-hidden relative p-4" x-data="{ bulkQty: {{ $minQty }} }">
                
                <!-- MOQ Badge -->
                <div class="absolute top-4 left-4 z-10">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-500 text-slate-950 shadow uppercase tracking-wider">
                        MOQ: {{ $minQty }} Pcs
                    </span>
                </div>

                <!-- Image -->
                <a href="{{ route('product.show', $product->slug) }}" class="relative block aspect-square bg-slate-100 rounded-2xl overflow-hidden mb-3">
                    <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                </a>

                <!-- Product Info -->
                <div class="flex flex-col justify-between flex-1 space-y-3">
                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">{{ $product->category->name ?? 'Accessories' }}</span>
                        <a href="{{ route('product.show', $product->slug) }}" class="text-xs sm:text-sm font-bold text-slate-900 hover:text-primary-600 transition line-clamp-2 block mt-0.5">{{ $product->name }}</a>
                    </div>

                    <!-- Pricing Info -->
                    <div class="bg-slate-50 p-3 rounded-2xl border text-xs">
                        <div class="flex justify-between items-baseline">
                            <span class="text-[10px] text-slate-500 font-bold">Wholesale (Per Pc):</span>
                            <span class="text-base font-black text-emerald-600">৳{{ number_format($wholesalePrice, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-baseline mt-0.5">
                            <span class="text-[10px] text-slate-400">Regular Retail:</span>
                            <span class="text-xs text-slate-400 line-through">৳{{ number_format($product->price, 2) }}</span>
                        </div>
                        <div class="pt-1.5 border-t border-slate-200 mt-1.5 flex justify-between font-bold text-slate-800 text-[11px]">
                            <span>Min Bulk Total:</span>
                            <span class="text-indigo-600" x-text="'৳' + ({{ $wholesalePrice }} * bulkQty).toFixed(2)">৳{{ number_format($wholesalePrice * $minQty, 2) }}</span>
                        </div>
                    </div>

                    <!-- Quantity Stepper for Bulk -->
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-bold text-slate-600">Quantity:</span>
                        <div class="flex items-center border border-slate-300 rounded-xl bg-white p-0.5 shadow-sm">
                            <button type="button" @click="if (bulkQty > {{ $minQty }}) bulkQty--" class="w-7 h-7 flex items-center justify-center font-bold text-xs hover:bg-slate-100 rounded">-</button>
                            <input type="number" x-model="bulkQty" min="{{ $minQty }}" class="w-12 text-center text-xs font-extrabold border-none outline-none">
                            <button type="button" @click="bulkQty++" class="w-7 h-7 flex items-center justify-center font-bold text-xs hover:bg-slate-100 rounded">+</button>
                        </div>
                    </div>

                    <!-- Dual Action Buttons: Add to Cart & WhatsApp -->
                    <div class="space-y-2 pt-1">
                        <button type="button" 
                                @click="addToCart({{ $product->id }}, bulkQty, true)" 
                                class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-extrabold text-xs rounded-xl shadow transition flex items-center justify-center gap-1.5">
                            <i class="fas fa-bag-shopping"></i> Add to Cart (Bulk Rate)
                        </button>

                        <a :href="'https://wa.me/88{{ $whatsappNumber }}?text=' + encodeURIComponent('Hello! I want to order bulk wholesale for: {{ addslashes($product->name) }} (Qty: ' + bulkQty + ' pcs @ ৳{{ number_format($wholesalePrice, 2) }} each)')" 
                           target="_blank" 
                           class="w-full py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 shadow">
                            <i class="fab fa-whatsapp"></i> Order on WhatsApp
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-8 flex justify-center">
            {{ $wholesaleProducts->links() }}
        </div>
    </div>
</section>

<!-- Wholesale Custom Quote Form -->
<section id="wholesale-inquiry" class="py-16 bg-white border-t border-slate-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-xl mx-auto mb-10 space-y-2">
            <span class="text-xs font-extrabold uppercase tracking-widest text-primary-600">B2B Partnership</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold font-serif text-slate-900">Request a Custom Wholesale Quote</h2>
            <p class="text-xs text-slate-500">Fill out your business details below. We will provide our complete dealer price list.</p>
        </div>

        <div class="bg-slate-50 p-8 rounded-3xl border border-slate-200 shadow-sm">
            <form method="POST" action="{{ route('wholesale.inquiry') }}" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Business / Shop Name *</label>
                        <input type="text" name="business_name" required placeholder="e.g. Dhaka Gadget Zone" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500 bg-white">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Contact Person Name *</label>
                        <input type="text" name="contact_person" required placeholder="e.g. Tanvir Ahmed" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500 bg-white">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Phone Number *</label>
                        <input type="tel" name="phone" required placeholder="e.g. 01711223344" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500 bg-white">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">WhatsApp Number</label>
                        <input type="tel" name="whatsapp" placeholder="e.g. 01711223344" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500 bg-white">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">District / Location *</label>
                        <input type="text" name="district" required placeholder="e.g. Tangail / Dhaka" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500 bg-white">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Estimated Monthly Quantity</label>
                        <input type="text" name="estimated_monthly_quantity" placeholder="e.g. 20 - 50 pcs" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500 bg-white">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="font-bold text-slate-700 block mb-1">Products Interested In & Questions</label>
                        <textarea name="message" rows="3" placeholder="Watches, Leather bags, Wallets, etc..." class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500 bg-white"></textarea>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-extrabold text-xs rounded-xl shadow-lg transition">
                        Submit Wholesale Application &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

@endsection
