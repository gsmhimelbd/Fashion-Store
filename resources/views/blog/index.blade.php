@extends('layouts.app')

@section('title', 'Buying Guides & Tech Blog - ' . \App\Models\Setting::get('store_name', 'OnlineBdMart'))

@section('content')

<!-- Header -->
<section class="bg-slate-900 text-white py-12 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center max-w-xl">
        <span class="text-xs font-bold uppercase tracking-widest text-primary-400">Our Blog & Guides</span>
        <h1 class="text-2xl sm:text-4xl font-extrabold font-serif tracking-tight mt-1">Latest News & Buying Guides</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-2">Expert tips, product comparisons, and styling guides for Bangladesh.</p>
    </div>
</section>

<!-- Blog List -->
<section class="py-14 bg-slate-50 min-h-[60vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @forelse($posts as $post)
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl transition flex flex-col justify-between overflow-hidden group">
                
                <!-- Cover Photo -->
                <a href="{{ route('blog.show', $post->slug) }}" class="aspect-video bg-slate-100 overflow-hidden block relative">
                    <img src="{{ asset($post->image_path ?: 'uploads/hero-banner-1.svg') }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    <span class="absolute top-3 left-3 bg-slate-950/80 backdrop-blur-sm text-white text-[10px] font-black uppercase px-2.5 py-1 rounded-full border border-white/20">
                        {{ $post->category ?: 'Buying Guide' }}
                    </span>
                </a>

                <div class="p-6 flex flex-col justify-between flex-1 space-y-4">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 group-hover:text-primary-600 transition leading-snug">
                            <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                        </h3>
                        <p class="text-xs text-slate-500 leading-relaxed mt-2 line-clamp-3">{{ $post->summary }}</p>
                    </div>
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                        <span>📅 {{ $post->created_at->format('M d, Y') }}</span>
                        <a href="{{ route('blog.show', $post->slug) }}" class="font-bold text-primary-600 hover:underline">Read Article &rarr;</a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-3 bg-white p-12 rounded-3xl border text-center text-slate-400">
                No blog guides published yet.
            </div>
            @endforelse
        </div>

        <div class="mt-8 flex justify-center">
            {{ $posts->links() }}
        </div>
    </div>
</section>

@endsection
