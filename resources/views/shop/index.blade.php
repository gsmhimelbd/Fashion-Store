@extends('layouts.app')

@section('title', 'Shop All Fashion Accessories - ' . \App\Models\Setting::get('store_name', 'OnlineBdMart'))

@section('content')

<!-- Shop Page Header Breadcrumb -->
<section class="bg-slate-900 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs text-slate-400 mb-2">
                    <a href="{{ route('home') }}" class="hover:text-white transition">Home</a>
                    <i class="fas fa-chevron-right text-[10px]"></i>
                    <span class="text-white font-medium">{{ $activeCategory ? $activeCategory->name : 'Shop Products' }}</span>
                </nav>
                <h1 class="text-2xl sm:text-4xl font-extrabold font-serif tracking-tight">
                    {{ $activeCategory ? $activeCategory->name : 'Explore All Accessories' }}
                </h1>
                <p class="text-slate-400 text-xs sm:text-sm mt-1 max-w-xl">
                    {{ $activeCategory ? $activeCategory->description : 'Discover our full range of luxury chronograph watches, genuine leather wallets, designer bags, and fashion essentials.' }}
                </p>
            </div>
            <div class="flex items-center gap-2 text-xs text-slate-300">
                <span>Showing <strong class="text-white">{{ $products->total() }}</strong> Products</span>
            </div>
        </div>
    </div>
</section>

<!-- Shop Main Content & Filters -->
<section class="py-12 bg-slate-50 min-h-screen" x-data="shopFilter()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Left Sidebar Filters -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Category Filter Box -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                    <h3 class="font-bold text-sm text-slate-900 uppercase tracking-wider">Categories</h3>
                    <div class="space-y-1 text-xs">
                        <a href="{{ route('shop') }}" 
                           class="flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition {{ !request('category') ? 'bg-primary-50 text-primary-600 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                            <span>All Collections</span>
                            <span class="text-slate-400">({{ \App\Models\Product::where('is_active', true)->count() }})</span>
                        </a>
                        @foreach($categories as $cat)
                        <a href="{{ route('shop', ['category' => $cat->slug]) }}" 
                           class="flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition {{ request('category') === $cat->slug ? 'bg-primary-50 text-primary-600 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                            <span class="flex items-center gap-2">
                                <i class="fas {{ $cat->icon ?: 'fa-tag' }} text-[11px] text-slate-400"></i>
                                {{ $cat->name }}
                            </span>
                            <span class="text-slate-400">({{ $cat->products_count }})</span>
                        </a>
                        @endforeach
                    </div>
                </div>

                <!-- Price Range Filter Form -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                    <h3 class="font-bold text-sm text-slate-900 uppercase tracking-wider">Price Filter</h3>
                    <form method="GET" action="{{ route('shop') }}" class="space-y-3">
                        @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif
                        @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <label class="text-[11px] text-slate-500 font-semibold mb-1 block">Min Price (৳)</label>
                                <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="0" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="text-[11px] text-slate-500 font-semibold mb-1 block">Max Price (৳)</label>
                                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="5000" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            </div>
                        </div>

                        <div class="pt-1">
                            <button type="submit" class="w-full py-2.5 bg-slate-900 hover:bg-primary-600 text-white font-bold rounded-xl text-xs transition">
                                Apply Filter
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Free Delivery Notice -->
                <div class="p-5 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white shadow-lg space-y-2">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-truck text-lg"></i>
                        <span class="font-bold text-xs">Fast Shipping</span>
                    </div>
                    <p class="text-xs text-emerald-50 leading-relaxed">
                        Cash on Delivery available all over Bangladesh. Tangail District delivery at only ৳50.
                    </p>
                </div>
            </div>

            <!-- Right Product Grid Area -->
            <div class="lg:col-span-3 space-y-6">
                
                <!-- Sort Bar & Search Summary -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="text-xs text-slate-600">
                        @if(request('search'))
                        <span>Search results for: <strong class="text-slate-900">"{{ request('search') }}"</strong></span>
                        <a href="{{ route('shop') }}" class="text-rose-500 hover:underline ml-2 text-[11px]">Clear Search</a>
                        @else
                        <span>Showing <strong class="text-slate-900">{{ $products->firstItem() ?? 0 }}</strong>- <strong class="text-slate-900">{{ $products->lastItem() ?? 0 }}</strong> of <strong class="text-slate-900">{{ $products->total() }}</strong> products</span>
                        @endif
                    </div>

                    <!-- Sort Dropdown -->
                    <form method="GET" action="{{ route('shop') }}" id="sortForm" class="flex items-center gap-2 text-xs">
                        @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                        @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                        @if(request('min_price'))<input type="hidden" name="min_price" value="{{ request('min_price') }}">@endif
                        @if(request('max_price'))<input type="hidden" name="max_price" value="{{ request('max_price') }}">@endif

                        <label for="sort" class="text-slate-500 font-semibold whitespace-nowrap">Sort By:</label>
                        <select name="sort" id="sort" onchange="document.getElementById('sortForm').submit()" class="border border-slate-200 rounded-xl px-3 py-1.5 text-xs bg-slate-50 font-semibold text-slate-700 outline-none focus:border-primary-500">
                            <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Newest Arrivals</option>
                            <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Most Popular</option>
                            <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>Highest Rated</option>
                        </select>
                    </form>
                </div>

                <!-- Products Grid -->
                @if($products->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                    @foreach($products as $product)
                    <div class="group bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between overflow-hidden relative">
                        
                        <!-- Badges -->
                        <div class="absolute top-3 left-3 z-10 flex flex-col gap-1">
                            @if($product->discount_percentage > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500 text-white shadow-sm">
                                -{{ $product->discount_percentage }}%
                            </span>
                            @endif
                            @if($product->stock <= 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-800 text-white shadow-sm">
                                Out of Stock
                            </span>
                            @endif
                        </div>

                        <!-- Image -->
                        <a href="{{ route('product.show', $product->slug) }}" class="relative block aspect-square bg-slate-100 overflow-hidden">
                            <img src="{{ $product->primary_image_url }}" 
                                 alt="{{ $product->name }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </a>

                        <!-- Details -->
                        <div class="p-4 flex flex-col justify-between flex-1">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                                    {{ $product->category->name ?? 'Accessories' }}
                                </span>
                                <a href="{{ route('product.show', $product->slug) }}" class="text-xs sm:text-sm font-bold text-slate-900 group-hover:text-primary-600 transition line-clamp-2">
                                    {{ $product->name }}
                                </a>

                                <div class="flex items-center gap-1.5 mt-2">
                                    <div class="flex text-amber-400 text-[10px]">
                                        @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star{{ $i <= round($product->rating) ? '' : '-half-alt text-slate-300' }}"></i>
                                        @endfor
                                    </div>
                                    <span class="text-[10px] text-slate-400 font-semibold">({{ $product->reviews_count }})</span>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                                <div>
                                    <span class="text-sm sm:text-base font-extrabold text-primary-600 block">
                                        ৳{{ number_format($product->effective_price, 2) }}
                                    </span>
                                    @if($product->sale_price && $product->sale_price < $product->price)
                                    <span class="text-[11px] text-slate-400 line-through">
                                        ৳{{ number_format($product->price, 2) }}
                                    </span>
                                    @endif
                                </div>

                                <button type="button" 
                                        @click="addToCart({{ $product->id }})" 
                                        class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-primary-600 text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                                    <i class="fas fa-bag-shopping"></i> <span class="hidden sm:inline">Add</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination Links -->
                <div class="pt-6 flex justify-center">
                    {{ $products->links() }}
                </div>
                @else
                <div class="bg-white rounded-2xl border border-slate-200/80 p-12 text-center space-y-4">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center text-slate-300 text-2xl mx-auto">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h3 class="font-bold text-slate-800 text-lg">No Products Found</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">Try clearing your filters or searching for another keyword.</p>
                    <a href="{{ route('shop') }}" class="inline-block px-6 py-2.5 bg-primary-600 text-white font-bold rounded-xl text-xs hover:bg-primary-700 transition">
                        Reset Filters
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

<script>
function shopFilter() {
    return {
        // Alpine logic for shop
    }
}
</script>

@endsection
