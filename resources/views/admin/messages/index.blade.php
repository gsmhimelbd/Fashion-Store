@extends('layouts.admin')

@section('page_title', 'Customer Inquiries & Messages')

@section('content')

<div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4 text-xs">
    <div>
        <h3 class="text-sm font-bold text-slate-900 uppercase">Incoming Support Messages & Inquiries</h3>
        <p class="text-slate-500">Contact form submissions and wholesale requests.</p>
    </div>

    <div class="space-y-3">
        <div class="p-4 rounded-2xl bg-slate-50 border space-y-2">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="font-bold text-slate-900">Tanvir Ahmed</h4>
                    <span class="text-[10px] text-slate-400">tanvir@example.com • 01711223344</span>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700">General Inquiry</span>
            </div>
            <p class="text-slate-700">"Is delivery available in Tangail district within 24 hours?"</p>
            <div class="pt-2 flex justify-end gap-2">
                <a href="https://wa.me/8801711223344" target="_blank" class="px-3 py-1 bg-emerald-500 text-white rounded-lg font-bold flex items-center gap-1"><i class="fab fa-whatsapp"></i> Reply on WhatsApp</a>
            </div>
        </div>
    </div>
</div>

@endsection
