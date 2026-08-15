@extends('layouts.admin')

@section('page_title', 'Search Engine Optimization (SEO)')

@section('content')

<div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6 text-xs max-w-2xl">
    <div>
        <h3 class="text-base font-bold text-slate-900 uppercase">Store SEO & Meta Configuration</h3>
        <p class="text-slate-500 mt-0.5">Optimize your website ranking on Google and social media share previews.</p>
    </div>

    <form method="POST" action="/admin-panel/settings" class="space-y-4">
        @csrf
        <div>
            <label class="block font-bold text-slate-700 mb-1">Default Meta Title</label>
            <input type="text" value="OnlineBdMart - Online Shopping Bangladesh | Best Deals & Fast Delivery" class="w-full border rounded-xl px-3.5 py-2.5 outline-none">
        </div>
        <div>
            <label class="block font-bold text-slate-700 mb-1">Meta Description</label>
            <textarea rows="3" class="w-full border rounded-xl px-3.5 py-2.5 outline-none">Shop top-quality luxury chronograph watches, genuine leather wallets, sunglasses and gadgets at factory wholesale & retail rates in Bangladesh.</textarea>
        </div>
        <div>
            <label class="block font-bold text-slate-700 mb-1">Google Analytics Measurement ID</label>
            <input type="text" placeholder="G-XXXXXXXXXX" class="w-full border rounded-xl px-3.5 py-2.5 outline-none font-mono">
        </div>
        <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl shadow">Save SEO Meta</button>
    </form>
</div>

@endsection
