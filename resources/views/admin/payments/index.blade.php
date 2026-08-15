@extends('layouts.admin')

@section('page_title', 'Payment Gateway Settings')

@section('content')

<div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6 text-xs max-w-3xl">
    <div>
        <h3 class="text-base font-bold text-slate-900 uppercase">Payment Gateways & Mobile Banking</h3>
        <p class="text-slate-500 mt-0.5">Configure bKash, Nagad, Rocket and Cash on Delivery numbers shown at checkout.</p>
    </div>

    <form method="POST" action="/admin-panel/settings" class="space-y-6">
        @csrf
        <!-- bKash -->
        <div class="p-5 rounded-2xl bg-pink-50/50 border border-pink-100 space-y-3">
            <div class="flex items-center gap-2 font-bold text-pink-700 text-sm">
                <span>bKash Payment</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">bKash Account Number</label>
                    <input type="text" name="bkash_number" value="{{ \App\Models\Setting::get('bkash_number', '01775153740') }}" class="w-full border rounded-xl px-3.5 py-2.5 bg-white outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Account Type</label>
                    <select name="bkash_type" class="w-full border rounded-xl px-3.5 py-2.5 bg-white outline-none">
                        <option value="Personal" {{ \App\Models\Setting::get('bkash_type') === 'Personal' ? 'selected' : '' }}>Personal (Send Money)</option>
                        <option value="Merchant" {{ \App\Models\Setting::get('bkash_type') === 'Merchant' ? 'selected' : '' }}>Merchant (Payment)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Nagad -->
        <div class="p-5 rounded-2xl bg-orange-50/50 border border-orange-100 space-y-3">
            <div class="flex items-center gap-2 font-bold text-orange-700 text-sm">
                <span>Nagad Payment</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nagad Account Number</label>
                    <input type="text" name="nagad_number" value="{{ \App\Models\Setting::get('nagad_number', '01775153740') }}" class="w-full border rounded-xl px-3.5 py-2.5 bg-white outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Account Type</label>
                    <select name="nagad_type" class="w-full border rounded-xl px-3.5 py-2.5 bg-white outline-none">
                        <option value="Personal" {{ \App\Models\Setting::get('nagad_type') === 'Personal' ? 'selected' : '' }}>Personal (Send Money)</option>
                        <option value="Merchant" {{ \App\Models\Setting::get('nagad_type') === 'Merchant' ? 'selected' : '' }}>Merchant (Payment)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Rocket -->
        <div class="p-5 rounded-2xl bg-purple-50/50 border border-purple-100 space-y-3">
            <div class="flex items-center gap-2 font-bold text-purple-700 text-sm">
                <span>Rocket Payment</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Rocket Account Number</label>
                    <input type="text" name="rocket_number" value="{{ \App\Models\Setting::get('rocket_number', '01775153740') }}" class="w-full border rounded-xl px-3.5 py-2.5 bg-white outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Account Type</label>
                    <select name="rocket_type" class="w-full border rounded-xl px-3.5 py-2.5 bg-white outline-none">
                        <option value="Personal">Personal (Send Money)</option>
                        <option value="Merchant">Merchant</option>
                    </select>
                </div>
            </div>
        </div>

        <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow">
            Save Payment Settings
        </button>
    </form>
</div>

@endsection
