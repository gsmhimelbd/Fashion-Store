@extends('layouts.admin')

@section('page_title', 'OTP Verification System')

@section('content')

<div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6 text-xs max-w-2xl">
    <div>
        <h3 class="text-base font-bold text-slate-900 uppercase">SMS Gateway & OTP Verification</h3>
        <p class="text-slate-500 mt-0.5">Enable OTP verification on customer checkout and registration in Bangladesh.</p>
    </div>

    <form method="POST" action="/admin-panel/settings" class="space-y-4">
        @csrf
        <div>
            <label class="block font-bold text-slate-700 mb-1">SMS Gateway Provider</label>
            <select class="w-full border rounded-xl px-3.5 py-2.5 bg-white outline-none">
                <option value="greenweb">Greenweb SMS Gateway</option>
                <option value="mimsms">MiMSMS Bangladesh</option>
                <option value="bulksmsbd">BulkSMSBD</option>
            </select>
        </div>
        <div>
            <label class="block font-bold text-slate-700 mb-1">SMS API Token / Key</label>
            <input type="text" placeholder="sms_token_xxxxxxxx" class="w-full border rounded-xl px-3.5 py-2.5 outline-none font-mono">
        </div>
        <div>
            <label class="block font-bold text-slate-700 mb-1">Approved Sender ID</label>
            <input type="text" placeholder="OnlineBdMart" class="w-full border rounded-xl px-3.5 py-2.5 outline-none">
        </div>
        <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl shadow">Save OTP Config</button>
    </form>
</div>

@endsection
