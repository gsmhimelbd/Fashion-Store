@extends('layouts.app')

@section('title', $product->name . ' - ' . \App\Models\Setting::get('store_name', 'OnlineBdMart'))

@section('content')

<!-- Breadcrumb -->
<section class="bg-slate-900 text-white py-6 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('home') }}" class="hover:text-white transition">Home</a>
            <i class="fas fa-chevron-right text-[10px]"></i>
            <a href="{{ route('shop') }}" class="hover:text-white transition">Shop</a>
            <i class="fas fa-chevron-right text-[10px]"></i>
            @if($product->category)
            <a href="{{ route('shop', ['category' => $product->category->slug]) }}" class="hover:text-white transition">{{ $product->category->name }}</a>
            <i class="fas fa-chevron-right text-[10px]"></i>
            @endif
            <span class="text-white font-medium truncate max-w-xs">{{ $product->name }}</span>
        </nav>
    </div>
</section>

<!-- Product Detail Showcase -->
<section class="py-12 bg-white" x-data="{ 
    selectedImage: '{{ $product->primary_image_url }}',
    qty: 1,
    activeTab: 'desc'
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            
            <!-- Left: Multi-Image Gallery -->
            <div class="space-y-4">
                <!-- Large Image Preview -->
                <div class="aspect-square bg-slate-100 rounded-3xl overflow-hidden border border-slate-200 shadow-sm relative group">
                    <img :src="selectedImage" 
                         alt="{{ $product->name }}" 
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    
                    @if($product->discount_percentage > 0)
                    <span class="absolute top-4 left-4 px-3 py-1 rounded-full text-xs font-extrabold bg-rose-500 text-white shadow-md">
                        SAVE {{ $product->discount_percentage }}%
                    </span>
                    @endif
                </div>

                <!-- Thumbnails Carousel -->
                @if($product->images->count() > 1)
                <div class="flex items-center gap-3 overflow-x-auto pb-2">
                    @foreach($product->images as $img)
                    <button type="button" 
                            @click="selectedImage = '{{ asset($img->image_path) }}'" 
                            class="w-20 h-20 rounded-2xl overflow-hidden border-2 transition-all shrink-0 bg-slate-50"
                            :class="selectedImage === '{{ asset($img->image_path) }}' ? 'border-primary-600 ring-2 ring-primary-500/30' : 'border-slate-200 hover:border-slate-400'">
                        <img src="{{ asset($img->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Right: Product Information & Buy Box -->
            <div class="space-y-6">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full bg-primary-50 text-primary-600 font-bold text-[11px] uppercase tracking-wider mb-2">
                        {{ $product->category->name ?? 'Accessories' }}
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-serif leading-tight">
                        {{ $product->name }}
                    </h1>
                    
                    <!-- Rating and Stock Status -->
                    <div class="flex flex-wrap items-center gap-4 mt-3 text-xs">
                        <div class="flex items-center gap-1 text-amber-400">
                            @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star{{ $i <= round($product->rating) ? '' : '-half-alt text-slate-300' }}"></i>
                            @endfor
                            <span class="text-slate-600 font-bold ml-1">{{ $product->rating }}</span>
                            <span class="text-slate-400">({{ $product->reviews_count }} verified reviews)</span>
                        </div>

                        <span class="text-slate-300">|</span>

                        <div>
                            @if($product->stock > 0)
                            <span class="inline-flex items-center gap-1.5 text-emerald-600 font-bold">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> In Stock ({{ $product->stock }} available)
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 text-rose-600 font-bold">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span> Out of Stock
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Price Box -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                    <div>
                        <div class="flex items-baseline gap-3">
                            <span class="text-3xl font-extrabold text-primary-600">
                                ৳{{ number_format($product->effective_price, 2) }}
                            </span>
                            @if($product->sale_price && $product->sale_price < $product->price)
                            <span class="text-sm text-slate-400 line-through">
                                ৳{{ number_format($product->price, 2) }}
                            </span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Cash on Delivery available nationwide in Bangladesh</p>
                    </div>

                    @if($product->sku)
                    <div class="text-right">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">SKU Code</span>
                        <span class="text-xs font-mono font-bold text-slate-700">{{ $product->sku }}</span>
                    </div>
                    @endif
                </div>

                <!-- Short Description -->
                @if($product->short_description)
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    {{ $product->short_description }}
                </p>
                @endif

                <!-- Quantity & Add to Cart Controls -->
                <div class="space-y-3 pt-2">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center border border-slate-300 rounded-xl bg-white p-1 shadow-sm">
                            <button type="button" @click="if (qty > 1) qty--" class="w-8 h-8 flex items-center justify-center text-slate-600 hover:bg-slate-100 rounded-lg text-sm font-bold">-</button>
                            <input type="number" x-model="qty" min="1" max="{{ $product->stock ?: 99 }}" class="w-12 text-center text-xs font-extrabold text-slate-900 outline-none border-none">
                            <button type="button" @click="qty++" class="w-8 h-8 flex items-center justify-center text-slate-600 hover:bg-slate-100 rounded-lg text-sm font-bold">+</button>
                        </div>

                        <button type="button" 
                                @click="addToCart({{ $product->id }}, qty)" 
                                class="flex-1 py-3.5 px-6 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs sm:text-sm shadow-xl shadow-primary-600/25 transition flex items-center justify-center gap-2">
                            <i class="fas fa-bag-shopping"></i> Add to Shopping Bag
                        </button>
                    </div>

                    <!-- Instant WhatsApp Order Button -->
                    <a href="https://wa.me/88{{ $whatsappNumber }}?text={{ $whatsappMessage }}" 
                       target="_blank" 
                       class="w-full py-3.5 px-6 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs sm:text-sm shadow-lg shadow-emerald-500/20 transition flex items-center justify-center gap-2">
                        <i class="fab fa-whatsapp text-lg"></i> Direct 1-Click Order via WhatsApp
                    </a>
                </div>

                <!-- Shipping & Delivery Highlights -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-3 text-xs">
                    <div class="flex items-center gap-3 text-slate-700">
                        <i class="fas fa-truck-fast text-primary-600 text-base shrink-0"></i>
                        <span><strong>Delivery Charge:</strong> Tangail District ৳50, Rest of Bangladesh ৳150</span>
                    </div>
                    <div class="flex items-center gap-3 text-slate-700">
                        <i class="fas fa-money-bill-wave text-emerald-600 text-base shrink-0"></i>
                        <span><strong>Payment:</strong> Cash on Delivery, bKash, Nagad, or Rocket</span>
                    </div>
                    <div class="flex items-center gap-3 text-slate-700">
                        <i class="fas fa-arrow-rotate-left text-indigo-600 text-base shrink-0"></i>
                        <span><strong>Return Policy:</strong> 7 Days hassle-free exchange on damaged/defective items</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Product Tabs (Description & Reviews) -->
        <div class="mt-16 border-t border-slate-200 pt-10">
            <div class="flex border-b border-slate-200 gap-8 text-sm font-bold">
                <button type="button" 
                        @click="activeTab = 'desc'" 
                        class="pb-4 transition border-b-2"
                        :class="activeTab === 'desc' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-400 hover:text-slate-700'">
                    Product Description
                </button>
                <button type="button" 
                        @click="activeTab = 'reviews'" 
                        class="pb-4 transition border-b-2"
                        :class="activeTab === 'reviews' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-400 hover:text-slate-700'">
                    Customer Reviews ({{ $product->reviews->count() }})
                </button>
            </div>

            <!-- Description Tab Content -->
            <div x-show="activeTab === 'desc'" class="py-8 text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line max-w-3xl">
                {{ $product->description ?: 'No detailed description provided for this product.' }}
            </div>

            <!-- Reviews Tab Content -->
            <div x-show="activeTab === 'reviews'" class="py-8 space-y-8 max-w-4xl" x-cloak>
                
                <!-- Review Submission Form -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80">
                    <h3 class="text-sm font-bold text-slate-900 mb-4">Write a Verified Review</h3>
                    <form method="POST" action="{{ route('product.review', $product->id) }}" class="space-y-4" x-data="{ r: 5 }">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-semibold text-slate-700 block mb-1">Your Name *</label>
                                <input type="text" name="customer_name" required placeholder="e.g. Tanvir Ahmed" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs outline-none focus:border-primary-500">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-700 block mb-1">Your Email (optional)</label>
                                <input type="email" name="customer_email" placeholder="e.g. tanvir@example.com" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs outline-none focus:border-primary-500">
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-700 block mb-1">Rating *</label>
                            <div class="flex items-center gap-2">
                                <input type="hidden" name="rating" :value="r">
                                <template x-for="i in 5">
                                    <button type="button" @click="r = i" class="text-xl" :class="i <= r ? 'text-amber-400' : 'text-slate-300'">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </template>
                                <span class="text-xs text-slate-500 font-bold ml-2" x-text="r + ' / 5 Stars'"></span>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-700 block mb-1">Your Review *</label>
                            <textarea name="comment" required rows="3" placeholder="Share your experience with this item..." class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs outline-none focus:border-primary-500"></textarea>
                        </div>

                        <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl transition shadow-md shadow-primary-600/20">
                            Submit Review
                        </button>
                    </form>
                </div>

                <!-- Existing Reviews List -->
                <div class="space-y-4">
                    @forelse($product->reviews as $review)
                    <div class="p-5 rounded-2xl bg-white border border-slate-100 shadow-sm space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 font-bold flex items-center justify-center text-xs">
                                    {{ strtoupper(substr($review->customer_name, 0, 2)) }}
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900">{{ $review->customer_name }}</h4>
                                    <span class="text-[10px] text-slate-400">{{ $review->created_at->diffForHumans() }}</span>
                                </div>
                            </div>

                            <div class="flex text-amber-400 text-xs">
                                @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star{{ $i <= $review->rating ? '' : ' text-slate-200' }}"></i>
                                @endfor
                            </div>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed pt-1">
                            {{ $review->comment }}
                        </p>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 italic">No reviews yet for this product. Be the first to leave a review!</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Related Products Section -->
        @if($relatedProducts->count() > 0)
        <div class="mt-20 border-t border-slate-200 pt-12">
            <h2 class="text-2xl font-extrabold text-slate-900 font-serif mb-8">You May Also Like</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                @foreach($relatedProducts as $rel)
                <div class="group bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between overflow-hidden relative">
                    <a href="{{ route('product.show', $rel->slug) }}" class="relative block aspect-square bg-slate-100 overflow-hidden">
                        <img src="{{ $rel->primary_image_url }}" alt="{{ $rel->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </a>
                    <div class="p-4 flex flex-col justify-between flex-1">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                                {{ $rel->category->name ?? 'Accessories' }}
                            </span>
                            <a href="{{ route('product.show', $rel->slug) }}" class="text-xs font-bold text-slate-900 group-hover:text-primary-600 transition line-clamp-2">
                                {{ $rel->name }}
                            </a>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                            <span class="text-sm font-extrabold text-primary-600 block">
                                ৳{{ number_format($rel->effective_price, 2) }}
                            </span>
                            <button type="button" @click="addToCart({{ $rel->id }})" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-primary-600 text-white text-xs font-bold transition">
                                <i class="fas fa-bag-shopping"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</section>

@endsection
