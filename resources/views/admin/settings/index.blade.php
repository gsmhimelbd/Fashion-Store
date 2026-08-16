@extends('layouts.admin')

@section('page_title', 'Store & System Settings')

@section('content')

<div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-8">
    
    <div>
        <h3 class="text-base font-extrabold text-slate-900 font-serif">Store Configuration</h3>
        <p class="text-xs text-slate-500 mt-0.5">Customize your brand details, delivery fee calculation, WhatsApp hotline, and payment accounts.</p>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6 text-xs">
        @csrf

        <!-- General Store Info -->
        <div class="space-y-4">
            <h4 class="font-bold text-slate-900 uppercase tracking-wider text-[10px] border-b border-slate-100 pb-2">1. General Information</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Store Name</label>
                    <input type="text" name="store_name" value="{{ $settings['store_name'] ?? 'OnlineBdMart' }}" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Store Tagline</label>
                    <input type="text" name="store_tagline" value="{{ $settings['store_tagline'] ?? 'Premium Fashion & Accessories' }}" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Store Physical Address</label>
                    <input type="text" name="store_address" value="{{ $settings['store_address'] ?? 'Tangail Sadar, Tangail, Bangladesh' }}" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="font-bold text-slate-700 block mb-1">Top Announcement Bar Notice</label>
                    <input type="text" name="announcement_bar" value="{{ $settings['announcement_bar'] ?? '' }}" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>
            </div>
        </div>

        <!-- Contact & Support -->
        <div class="space-y-4">
            <h4 class="font-bold text-slate-900 uppercase tracking-wider text-[10px] border-b border-slate-100 pb-2">2. Contact & Customer Support</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Hotline Phone Number</label>
                    <input type="text" name="contact_phone" value="{{ $settings['contact_phone'] ?? '01775153740' }}" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">WhatsApp Number (Orders & Support)</label>
                    <input type="text" name="whatsapp_number" value="{{ $settings['whatsapp_number'] ?? '01775153740' }}" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Support Email</label>
                    <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? 'support@onlinebdmart.com' }}" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Facebook Page URL</label>
                    <input type="text" name="facebook_page" value="{{ $settings['facebook_page'] ?? 'https://facebook.com' }}" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Instagram Page URL</label>
                    <input type="text" name="instagram_page" value="{{ $settings['instagram_page'] ?? 'https://instagram.com' }}" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>
            </div>
        </div>

        <!-- Delivery Charge Rates -->
        <div class="space-y-4">
            <h4 class="font-bold text-slate-900 uppercase tracking-wider text-[10px] border-b border-slate-100 pb-2">3. Shipping & Delivery Rates</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Tangail District Delivery Charge (৳)</label>
                    <input type="number" step="0.01" name="delivery_charge_tangail" value="{{ $settings['delivery_charge_tangail'] ?? '50' }}" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Outside Tangail / Rest of BD (৳)</label>
                    <input type="number" step="0.01" name="delivery_charge_other" value="{{ $settings['delivery_charge_other'] ?? '150' }}" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Free Delivery Minimum Cart Value (৳)</label>
                    <input type="number" step="0.01" name="free_delivery_threshold" value="{{ $settings['free_delivery_threshold'] ?? '2000' }}" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>
            </div>
        </div>

        <!-- Payment Gateway Numbers -->
        <div class="space-y-4">
            <h4 class="font-bold text-slate-900 uppercase tracking-wider text-[10px] border-b border-slate-100 pb-2">4. Payment Gateway Accounts</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">bKash Account Number</label>
                    <input type="text" name="bkash_number" value="{{ $settings['bkash_number'] ?? '01775153740' }}" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Nagad Account Number</label>
                    <input type="text" name="nagad_number" value="{{ $settings['nagad_number'] ?? '01775153740' }}" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Rocket Account Number</label>
                    <input type="text" name="rocket_number" value="{{ $settings['rocket_number'] ?? '01775153740' }}" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end pt-4 border-t border-slate-100">
            <button type="submit" class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-600/25 transition flex items-center gap-2">
                <i class="fas fa-floppy-disk"></i> Save Site Settings
            </button>
        </div>

    </form>
</div>

@endsection
