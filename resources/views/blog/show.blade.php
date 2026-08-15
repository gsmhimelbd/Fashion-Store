@extends('layouts.app')

@section('title', $post->title . ' - ' . \App\Models\Setting::get('store_name', 'OnlineBdMart'))

@section('content')

<!-- Blog Header Breadcrumb -->
<section class="bg-slate-900 text-white py-12 border-b border-slate-800">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        <nav class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('home') }}" class="hover:text-white transition">Home</a>
            <i class="fas fa-chevron-right text-[10px]"></i>
            <a href="{{ route('blog.index') }}" class="hover:text-white transition">Blog</a>
            <i class="fas fa-chevron-right text-[10px]"></i>
            <span class="text-white font-medium truncate max-w-xs">{{ $post->title }}</span>
        </nav>

        <span class="inline-block px-3 py-1 bg-primary-600/30 text-primary-300 font-extrabold text-[11px] uppercase tracking-wider rounded-full border border-primary-500/30">
            {{ $post->category }}
        </span>

        <h1 class="text-2xl sm:text-4xl font-extrabold font-serif leading-tight text-white">
            {{ $post->title }}
        </h1>

        <div class="flex items-center gap-4 text-xs text-slate-400 font-semibold pt-1">
            <span>✍️ {{ $post->author ?: 'OnlineBdMart Team' }}</span>
            <span>•</span>
            <span>📅 {{ $post->created_at->format('M d, Y') }}</span>
        </div>
    </div>
</section>

<!-- Single Article Body -->
<section class="py-12 bg-slate-50 min-h-[70vh]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Large Cover Photo -->
        <div class="aspect-video w-full rounded-3xl overflow-hidden shadow-xl border border-slate-200 bg-slate-900">
            <img src="{{ asset($post->image_path ?: 'uploads/hero-banner-1.svg') }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
        </div>

        <!-- Article Summary Box -->
        @if($post->summary)
        <div class="p-6 rounded-2xl bg-indigo-50 border border-indigo-100 text-xs sm:text-sm font-semibold text-indigo-900 leading-relaxed italic">
            "{{ $post->summary }}"
        </div>
        @endif

        <!-- Main Content -->
        <div class="bg-white p-8 sm:p-10 rounded-3xl border border-slate-200 shadow-sm text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line space-y-4 font-sans">
            {{ $post->content }}
        </div>

        <!-- Social Share & Back -->
        <div class="flex items-center justify-between pt-4 border-t border-slate-200">
            <a href="{{ route('blog.index') }}" class="text-xs font-bold text-primary-600 hover:underline flex items-center gap-1.5">
                <i class="fas fa-arrow-left text-[10px]"></i> Back to all guides
            </a>
            <div class="flex items-center gap-2 text-xs">
                <span class="text-slate-400 font-bold">Share:</span>
                <a href="https://wa.me/?text={{ urlencode($post->title . ' - ' . url()->current()) }}" target="_blank" class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow"><i class="fab fa-whatsapp"></i></a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center shadow"><i class="fab fa-facebook-f"></i></a>
            </div>
        </div>

    </div>
</section>

@endsection
