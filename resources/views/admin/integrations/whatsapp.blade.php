@extends('layouts.admin')

@section('page_title', 'WhatsApp Integration & Automation')

@section('content')

<div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6 text-xs max-w-2xl">
    <div>
        <h3 class="text-base font-bold text-slate-900 uppercase">WhatsApp Widget & Auto-Message Config</h3>
        <p class="text-slate-500 mt-0.5">Configure floating WhatsApp button and automated order message template.</p>
    </div>

    <form method="POST" action="/admin-panel/settings" class="space-y-4">
        @csrf
        <div>
            <label class="block font-bold text-slate-700 mb-1">WhatsApp Business Number (with country code)</label>
            <input type="text" name="whatsapp_number" value="{{ \App\Models\Setting::get('whatsapp_number', '01775153740') }}" class="w-full border rounded-xl px-3.5 py-2.5 outline-none font-mono">
        </div>
        <div>
            <label class="block font-bold text-slate-700 mb-1">Auto-Greeting Message Template</label>
            <textarea name="whatsapp_greeting" rows="3" class="w-full border rounded-xl px-3.5 py-2.5 outline-none">Hello! I want to inquire about products from OnlineBdMart.</textarea>
        </div>
        <button type="submit" class="px-6 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl shadow">Save WhatsApp Config</button>
    </form>
</div>

@endsection
