@extends('layouts.admin')

@section('page_title', 'Facebook Pixel & CAPI')

@section('content')

<div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6 text-xs max-w-2xl">
    <div>
        <h3 class="text-base font-bold text-slate-900 uppercase">Facebook Meta Pixel & Conversion API</h3>
        <p class="text-slate-500 mt-0.5">Track ViewContent, AddToCart, InitiateCheckout, and Purchase conversion events.</p>
    </div>

    <form method="POST" action="/admin-panel/settings" class="space-y-4">
        @csrf
        <div>
            <label class="block font-bold text-slate-700 mb-1">Facebook Pixel ID</label>
            <input type="text" placeholder="123456789012345" class="w-full border rounded-xl px-3.5 py-2.5 outline-none font-mono">
        </div>
        <div>
            <label class="block font-bold text-slate-700 mb-1">Conversions API Access Token (CAPI)</label>
            <textarea rows="3" placeholder="EAABsb..." class="w-full border rounded-xl px-3.5 py-2.5 outline-none font-mono"></textarea>
        </div>
        <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl shadow">Save Meta Pixel</button>
    </form>
</div>

@endsection
