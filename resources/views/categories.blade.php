@extends('layouts.app')

@section('title', 'All Categories - ' . \App\Models\Setting::get('store_name', 'OnlineBdMart'))

@section('content')

<!-- Header -->
<section class="bg-slate-900 text-white py-10 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl sm:text-4xl font-extrabold font-serif tracking-tight">Browse All Categories</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">Explore all curated product collections with premium quality.</p>
    </div>
</section>

<!-- Categories Grid -->
<section class="py-14 bg-slate-50 min-h-[60vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @foreach($categories as $cat)
            <a href="{{ route('shop', ['category' => $cat->slug]) }}" class="group bg-white rounded-3xl border border-slate-200 p-6 shadow-sm hover:shadow-xl transition flex flex-col justify-between overflow-hidden">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-14 h-14 rounded-2xl bg-primary-50 group-hover:bg-primary-600 text-primary-600 group-hover:text-white text-2xl flex items-center justify-center transition shadow-sm">
                        <i class="fas {{ $cat->icon ?: 'fa-tag' }}"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 group-hover:text-primary-600 transition">{{ $cat->name }}</h3>
                        <span class="text-xs font-bold text-emerald-600">{{ $cat->products_count }} Products in stock</span>
                    </div>
                </div>

                <p class="text-xs text-slate-500 leading-relaxed mb-4">
                    {{ $cat->description ?: 'Explore our top trending items in this collection with warranty.' }}
                </p>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-primary-600 group-hover:text-primary-800">
                    <span>View Collection</span>
                    <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>

@endsection
