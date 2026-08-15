@extends('layouts.admin')

@section('page_title', 'Customer Reviews Moderation')

@section('content')

<div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4 text-xs">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-900 uppercase">Customer Reviews & Ratings</h3>
            <p class="text-slate-500">Approve, moderate, or remove product reviews.</p>
        </div>
    </div>

    <div class="space-y-3">
        <div class="p-4 rounded-2xl bg-slate-50 border space-y-2">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="font-bold text-slate-900">Arif Hossain <span class="text-[10px] text-emerald-600 font-bold ml-2">✓ Verified Buyer</span></h4>
                    <span class="text-slate-400 text-[10px]">Product: Luxury Chronograph Sapphire Watch</span>
                </div>
                <div class="flex text-amber-400"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
            </div>
            <p class="text-slate-700">"Headphone quality outstanding! Bass heavy and ANC works perfectly. Delivery was completed in 2 days."</p>
            <div class="pt-2 flex justify-end gap-2">
                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-lg text-[10px] font-bold">Approved</span>
            </div>
        </div>
    </div>
</div>

@endsection
