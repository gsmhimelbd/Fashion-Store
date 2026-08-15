@extends('layouts.app')

@section('title', \App\Models\Setting::get('store_name', 'OnlineBdMart') . ' - Upgrade Your Style with Premium Accessories')

@section('content')

<!-- 1. Animated Hero Carousel Slider (Auto-slides every 5 seconds) -->
<section class="relative bg-slate-950 text-white overflow-hidden rounded-3xl mb-12 shadow-2xl" id="heroSliderSection">
    @if(count($banners) > 0)
        @foreach($banners as $index => $banner)
        <div class="hero-slide relative min-h-[480px] lg:min-h-[560px] flex items-center transition-all duration-700 {{ $index === 0 ? '' : 'hidden' }}" data-slide="{{ $index }}">
            <img src="{{ asset($banner->image_path) }}" alt="{{ $banner->title }}" class="absolute inset-0 w-full h-full object-cover opacity-60">
            <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/80 to-transparent"></div>

            <div class="relative max-w-7xl mx-auto px-6 sm:px-12 py-16 w-full">
                <div class="max-w-2xl space-y-6">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-primary-500/20 border border-primary-400/40 text-primary-300 text-xs font-black tracking-widest uppercase animate-pulse">
                        {{ $banner->badge_text ?: '✨ NEW ARRIVALS 2026' }}
                    </span>

                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-tight font-serif">
                        {{ $banner->title }}
                    </h1>

                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed font-normal">
                        {{ $banner->subtitle }}
                    </p>

                    <div class="flex flex-wrap items-center gap-4 pt-2">
                        <a href="{{ $banner->button_url ?: route('shop') }}" class="px-8 py-3.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs sm:text-sm font-extrabold shadow-xl shadow-primary-600/30 transition flex items-center gap-2">
                            {{ $banner->button_text ?: 'Shop Now' }} <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                        <a href="{{ route('wholesale') }}" class="px-6 py-3.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs sm:text-sm font-black shadow-lg transition">
                            Wholesale B2B &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    @endif

    <button type="button" onclick="prevHeroSlide()" class="absolute left-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-black/40 hover:bg-black/80 text-white flex items-center justify-center backdrop-blur-sm transition z-20">
        <i class="fas fa-chevron-left text-sm"></i>
    </button>
    <button type="button" onclick="nextHeroSlide()" class="absolute right-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-black/40 hover:bg-black/80 text-white flex items-center justify-center backdrop-blur-sm transition z-20">
        <i class="fas fa-chevron-right text-sm"></i>
    </button>

    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex items-center gap-2 z-20">
        @foreach($banners as $index => $b)
        <button type="button" onclick="goToHeroSlide({{ $index }})" class="hero-dot h-2 rounded-full transition-all duration-300 {{ $index === 0 ? 'w-8 bg-primary-500' : 'w-2 bg-white/40' }}" data-dot="{{ $index }}"></button>
        @endforeach
    </div>
</section>

<!-- 2. Value Proposition Badges -->
<section class="border-b border-slate-200 bg-white py-8 mb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <div class="flex items-center gap-3.5 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center text-xl shrink-0"><i class="fas fa-truck-fast"></i></div>
                <div><h4 class="text-xs font-extrabold text-slate-900">Free Shipping</h4><p class="text-[11px] text-slate-500 mt-0.5">On orders over ৳2000</p></div>
            </div>
            <div class="flex items-center gap-3.5 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0"><i class="fas fa-shield-halved"></i></div>
                <div><h4 class="text-xs font-extrabold text-slate-900">Secure Payment</h4><p class="text-[11px] text-slate-500 mt-0.5">100% safe & COD verified</p></div>
            </div>
            <div class="flex items-center gap-3.5 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0"><i class="fas fa-rotate-left"></i></div>
                <div><h4 class="text-xs font-extrabold text-slate-900">Easy Returns</h4><p class="text-[11px] text-slate-500 mt-0.5">7-day hassle-free policy</p></div>
            </div>
            <div class="flex items-center gap-3.5 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0"><i class="fas fa-headset"></i></div>
                <div><h4 class="text-xs font-extrabold text-slate-900">24/7 Support</h4><p class="text-[11px] text-slate-500 mt-0.5">We're always here to help</p></div>
            </div>
        </div>
    </div>
</section>

<!-- 3. WHOLESALE / B2B SECTION (Added to Home Page) -->
<section class="py-12 bg-slate-900 text-white rounded-3xl p-8 sm:p-10 mb-14 border border-slate-800 shadow-2xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
            <div class="space-y-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/20 text-amber-400 text-xs font-black uppercase"><i class="fas fa-boxes-stacked"></i> Wholesale / B2B Section</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold font-serif text-white mt-1">Factory Bulk Rates for Retailers & Resellers</h2>
                <p class="text-slate-300 text-xs sm:text-sm">Low 5 Pcs minimum order quantity. Add directly to cart in bulk or order via WhatsApp!</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('wholesale') }}" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs rounded-xl shadow transition flex items-center gap-1.5">
                    <span>Open Wholesale Catalog</span> <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            @foreach($featuredProducts->take(4) as $product)
            @php
                $wsPrice = $product->wholesale_price ?: ($product->price * 0.75);
                $minQty = $product->wholesale_min_qty ?: 5;
            @endphp
            <div class="bg-slate-950 rounded-2xl border border-slate-800 p-4 flex flex-col justify-between relative shadow">
                <span class="absolute top-3 left-3 z-10 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-slate-950 shadow">
                    Min: {{ $minQty }} Pcs
                </span>
                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="w-full aspect-square object-cover rounded-xl mb-3 bg-slate-900">
                <div>
                    <h4 class="font-bold text-xs text-white line-clamp-1">{{ $product->name }}</h4>
                    <div class="mt-2 text-xs">
                        <span class="text-[10px] text-slate-400 block font-bold">Wholesale (Per Unit):</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-base font-black text-emerald-400">৳{{ number_format($wsPrice, 2) }}</span>
                            <span class="text-xs text-slate-500 line-through">৳{{ number_format($product->price, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-800 mt-3 space-y-2">
                    <button type="button" onclick="addToCart({{ $product->id }}, {{ $minQty }}, true)" class="w-full py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold text-center block shadow">
                        <i class="fas fa-bag-shopping mr-1"></i> Add to Cart (Min {{ $minQty }} pcs)
                    </button>
                    <a href="https://wa.me/88{{ \App\Models\Setting::get('whatsapp_number', '01775153740') }}?text={{ urlencode('Hello, I want wholesale order for: ' . $product->name . ' (Min ' . $minQty . ' pcs @ ৳' . number_format($wsPrice, 2) . ' each)') }}" target="_blank" class="w-full py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-[11px] font-bold text-center block shadow">
                        <i class="fab fa-whatsapp mr-1"></i> Order on WhatsApp
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 4. Shop By Category Grid -->
<section class="py-12 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
            <div>
                <span class="text-[11px] font-extrabold uppercase tracking-widest text-primary-600">Shop By Category</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-serif mt-1">Browse Top Categories</h2>
            </div>
            <a href="{{ route('categories') }}" class="text-xs font-bold text-primary-600 hover:text-primary-800 flex items-center gap-1.5 group">
                View All Categories <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($categories as $category)
            <a href="{{ route('shop', ['category' => $category->slug]) }}" 
               class="group relative bg-white p-5 rounded-2xl border border-slate-200/80 hover:border-primary-500 hover:shadow-xl transition-all duration-300 flex flex-col items-center text-center">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-primary-50 to-indigo-100 group-hover:from-primary-600 group-hover:to-cyan-500 flex items-center justify-center text-2xl text-primary-600 group-hover:text-white transition-all duration-300 shadow-sm group-hover:scale-110 mb-3">
                    <i class="fas {{ $category->icon ?: 'fa-tag' }}"></i>
                </div>
                <h3 class="text-xs font-extrabold text-slate-900 group-hover:text-primary-600 transition">{{ $category->name }}</h3>
                <span class="text-[10px] text-slate-400 font-semibold mt-0.5">{{ $category->products_count }} Items</span>
            </a>
            @endforeach
        </div>
    </div>
</section>

<!-- 5. Flash Deals with Live Countdown -->
<section class="py-12 bg-slate-950 text-white relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-r from-primary-950/60 via-purple-950/40 to-slate-950"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
        <div class="space-y-4 max-w-xl">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/20 text-rose-400 text-xs font-bold uppercase tracking-widest">% Limited Time Offer</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold font-serif leading-tight">Big Deals on Top Fashion Gadgets</h2>
            <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">Grab luxury chronograph watches and leather wallets at unbeatable discount prices.</p>
            <div class="pt-2">
                <a href="{{ route('deals') }}" class="px-7 py-3 rounded-xl bg-white hover:bg-slate-100 text-slate-950 font-black text-xs shadow-xl transition inline-flex items-center gap-2">
                    Shop Deals <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- Live Clock -->
        <div class="bg-white/5 border border-white/10 backdrop-blur-md rounded-3xl p-6 sm:p-8 flex items-center justify-center gap-4 text-center">
            <div><div id="liveHours" class="w-16 sm:w-20 py-3 bg-slate-900/90 rounded-2xl border border-white/10 text-2xl sm:text-3xl font-black text-primary-400 font-mono">12</div><span class="text-[10px] text-slate-400 uppercase font-bold">Hours</span></div>
            <span class="text-2xl font-bold text-slate-600">:</span>
            <div><div id="liveMins" class="w-16 sm:w-20 py-3 bg-slate-900/90 rounded-2xl border border-white/10 text-2xl sm:text-3xl font-black text-emerald-400 font-mono">48</div><span class="text-[10px] text-slate-400 uppercase font-bold">Mins</span></div>
            <span class="text-2xl font-bold text-slate-600">:</span>
            <div><div id="liveSecs" class="w-16 sm:w-20 py-3 bg-slate-900/90 rounded-2xl border border-white/10 text-2xl sm:text-3xl font-black text-amber-400 font-mono">26</div><span class="text-[10px] text-slate-400 uppercase font-bold">Secs</span></div>
        </div>
    </div>
</section>

<!-- 6. Best Sellers & Top Picks -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
            <div>
                <span class="text-[11px] font-extrabold uppercase tracking-widest text-primary-600">Best Sellers</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-serif mt-1">Top Picks for You</h2>
            </div>
            <a href="{{ route('shop') }}" class="text-xs font-bold text-primary-600 hover:text-primary-800 flex items-center gap-1.5 group">
                View All Products <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($featuredProducts as $product)
            <div class="group bg-white rounded-2xl border border-slate-200/90 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between overflow-hidden relative">
                
                <div class="absolute top-3 left-3 z-10">
                    @if($product->discount_percentage > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white shadow-sm">Sale</span>
                    @endif
                </div>

                <div class="absolute top-3 right-3 z-10">
                    <button type="button" onclick="toggleWishlist({ id: {{ $product->id }}, name: '{{ addslashes($product->name) }}', price: {{ $product->effective_price }}, image: '{{ $product->primary_image_url }}' })" class="w-8 h-8 rounded-full bg-white/90 shadow text-slate-400 hover:text-rose-500 flex items-center justify-center transition">
                        <i class="fas fa-heart text-xs"></i>
                    </button>
                </div>

                <a href="{{ route('product.show', $product->slug) }}" class="relative block aspect-square bg-slate-100 overflow-hidden">
                    <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                </a>

                <div class="p-4 flex flex-col justify-between flex-1">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">{{ $product->category->name ?? 'Accessories' }}</span>
                        <a href="{{ route('product.show', $product->slug) }}" class="text-xs sm:text-sm font-bold text-slate-900 group-hover:text-primary-600 transition line-clamp-2">{{ $product->name }}</a>
                        <div class="flex items-center gap-1.5 mt-2">
                            <div class="flex text-amber-400 text-[10px]">
                                @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star{{ $i <= round($product->rating) ? '' : '-half-alt text-slate-300' }}"></i>
                                @endfor
                            </div>
                            <span class="text-[10px] text-slate-400 font-semibold">({{ $product->reviews_count }})</span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <div class="flex items-baseline gap-2 mb-3">
                            <span class="text-sm sm:text-base font-black text-slate-900">৳{{ number_format($product->effective_price, 2) }}</span>
                            @if($product->sale_price && $product->sale_price < $product->price)
                            <span class="text-[11px] text-slate-400 line-through">৳{{ number_format($product->price, 2) }}</span>
                            @endif
                        </div>
                        <button type="button" onclick="addToCart({{ $product->id }})" class="w-full py-2 px-3 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                            <i class="fas fa-bag-shopping"></i> <span>Add to Cart</span>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 7. BLOG & BUYING GUIDES SECTION -->
<section class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
            <div>
                <span class="text-[11px] font-extrabold uppercase tracking-widest text-primary-600">Our Blog & Buying Guides</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold font-serif text-slate-900 mt-1">Latest Tech & Fashion Guides</h2>
            </div>
            <a href="{{ route('blog.index') }}" class="text-xs font-bold text-primary-600 hover:text-primary-800 flex items-center gap-1.5 group">
                View All Guides <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl transition overflow-hidden p-6 flex flex-col justify-between">
                <div class="space-y-3">
                    <span class="text-[10px] font-black uppercase text-primary-600 bg-primary-50 px-2.5 py-1 rounded-full">Buying Guide</span>
                    <h3 class="text-base font-extrabold text-slate-900 hover:text-primary-600 leading-snug">
                        <a href="{{ route('blog.index') }}">Top 5 Luxury Watches & Accessories Under ৳5000 in 2026</a>
                    </h3>
                    <p class="text-xs text-slate-500 leading-relaxed line-clamp-3">We tested 15+ premium Japanese quartz watches and full-grain cowhide leather wallets to find the best value for money in Bangladesh.</p>
                </div>
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400 mt-4">
                    <span>📅 Aug 14, 2026</span>
                    <a href="{{ route('blog.index') }}" class="font-bold text-primary-600 hover:underline">Read Article &rarr;</a>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl transition overflow-hidden p-6 flex flex-col justify-between">
                <div class="space-y-3">
                    <span class="text-[10px] font-black uppercase text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full">Tips & Tricks</span>
                    <h3 class="text-base font-extrabold text-slate-900 hover:text-primary-600 leading-snug">
                        <a href="{{ route('blog.index') }}">How to Spot Original vs Copy Accessories Before Paying</a>
                    </h3>
                    <p class="text-xs text-slate-500 leading-relaxed line-clamp-3">Avoid cheap replicas with these 5 quick verification checks before making payment to courier riders.</p>
                </div>
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400 mt-4">
                    <span>📅 Aug 12, 2026</span>
                    <a href="{{ route('blog.index') }}" class="font-bold text-primary-600 hover:underline">Read Article &rarr;</a>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl transition overflow-hidden p-6 flex flex-col justify-between">
                <div class="space-y-3">
                    <span class="text-[10px] font-black uppercase text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">B2B & Wholesale</span>
                    <h3 class="text-base font-extrabold text-slate-900 hover:text-primary-600 leading-snug">
                        <a href="{{ route('blog.index') }}">Wholesale & Reselling Guide for Beginners in Bangladesh</a>
                    </h3>
                    <p class="text-xs text-slate-500 leading-relaxed line-clamp-3">How to start your online or offline accessory business with low MOQ and factory pricing directly from Tangail & Dhaka.</p>
                </div>
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400 mt-4">
                    <span>📅 Aug 10, 2026</span>
                    <a href="{{ route('blog.index') }}" class="font-bold text-primary-600 hover:underline">Read Article &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 8. CUSTOMER LOVE / VERIFIED REVIEWS SECTION -->
<section class="py-16 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12 space-y-2">
            <span class="text-xs font-extrabold uppercase tracking-widest text-primary-600">Customer Love</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-serif">What Our Buyers Say</h2>
            <p class="text-xs text-slate-500">Read verified reviews from customers across Bangladesh.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-6 rounded-3xl bg-slate-50 border border-slate-200 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 font-bold flex items-center justify-center text-xs">AH</div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Arif Hossain</h4>
                        <span class="text-[10px] text-emerald-600 font-bold">✓ Verified Buyer</span>
                    </div>
                </div>
                <div class="flex text-amber-400 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <p class="text-xs text-slate-600 leading-relaxed italic">"Luxury watch quality is outstanding! Heavy steel weight and sapphire glass looks premium. Delivery was completed in 2 days."</p>
            </div>

            <div class="p-6 rounded-3xl bg-slate-50 border border-slate-200 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-pink-100 text-pink-700 font-bold flex items-center justify-center text-xs">NJ</div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Nusrat Jahan</h4>
                        <span class="text-[10px] text-emerald-600 font-bold">✓ Verified Buyer</span>
                    </div>
                </div>
                <div class="flex text-amber-400 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <p class="text-xs text-slate-600 leading-relaxed italic">"Ordered genuine leather handbag and pearl necklace. Best price in Bangladesh and cash on delivery was super smooth."</p>
            </div>

            <div class="p-6 rounded-3xl bg-slate-50 border border-slate-200 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center text-xs">TA</div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Tanvir Ahmed</h4>
                        <span class="text-[10px] text-emerald-600 font-bold">✓ Verified Buyer</span>
                    </div>
                </div>
                <div class="flex text-amber-400 text-xs"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <p class="text-xs text-slate-600 leading-relaxed italic">"Polarized sunglasses and leather wallet are authentic. Real-time order tracking updated every step until delivered."</p>
            </div>
        </div>
    </div>
</section>

<script>
    let currentHeroIdx = 0;
    const totalHeroSlides = {{ count($banners) }};
    function showHeroSlide(idx) {
        currentHeroIdx = (idx + totalHeroSlides) % totalHeroSlides;
        document.querySelectorAll('.hero-slide').forEach(s => s.classList.add('hidden'));
        const active = document.querySelector('.hero-slide[data-slide="' + currentHeroIdx + '"]');
        if (active) active.classList.remove('hidden');

        document.querySelectorAll('.hero-dot').forEach(d => {
            const dotIdx = parseInt(d.getAttribute('data-dot'), 10);
            if (dotIdx === currentHeroIdx) {
                d.className = 'hero-dot h-2 rounded-full transition-all duration-300 w-8 bg-primary-500';
            } else {
                d.className = 'hero-dot h-2 rounded-full transition-all duration-300 w-2 bg-white/40';
            }
        });
    }
    function nextHeroSlide() { showHeroSlide(currentHeroIdx + 1); }
    function prevHeroSlide() { showHeroSlide(currentHeroIdx - 1); }
    function goToHeroSlide(idx) { showHeroSlide(idx); }
    setInterval(nextHeroSlide, 5000);

    let h = 12, m = 48, sec = 26;
    setInterval(() => {
        if (sec > 0) sec--;
        else {
            sec = 59;
            if (m > 0) m--;
            else { m = 59; if (h > 0) h--; }
        }
        const elH = document.getElementById('liveHours');
        const elM = document.getElementById('liveMins');
        const elS = document.getElementById('liveSecs');
        if (elH) elH.textContent = String(h).padStart(2, '0');
        if (elM) elM.textContent = String(m).padStart(2, '0');
        if (elS) elS.textContent = String(sec).padStart(2, '0');
    }, 1000);
</script>

@endsection
