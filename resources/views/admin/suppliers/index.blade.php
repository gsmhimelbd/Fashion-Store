@extends('layouts.admin')

@section('page_title', 'Suppliers Management')

@section('content')

<div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4 text-xs">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-900 uppercase">Product Suppliers & Manufacturers</h3>
            <p class="text-slate-500">Track vendor contact details, supplied product lines, and wholesale pricing.</p>
        </div>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-xl font-bold shadow">+ Add Supplier</button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-50 font-bold uppercase text-[10px]">
                <tr>
                    <th class="p-3">Company / Factory</th>
                    <th class="p-3">Contact Person</th>
                    <th class="p-3">Phone</th>
                    <th class="p-3">Products Supplied</th>
                    <th class="p-3">Location</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <tr>
                    <td class="p-3 font-bold text-slate-900">Apex Leather Crafts Ltd</td>
                    <td class="p-3">Mohammad Ali</td>
                    <td class="p-3 font-mono text-indigo-600">01712000000</td>
                    <td class="p-3">Full-grain Wallets, Belts, Handbags</td>
                    <td class="p-3">Hazaribagh, Dhaka</td>
                </tr>
                <tr>
                    <td class="p-3 font-bold text-slate-900">Orient Timepieces & Tech</td>
                    <td class="p-3">Kabir Hossain</td>
                    <td class="p-3 font-mono text-indigo-600">01815000000</td>
                    <td class="p-3">Chronograph Watches, AMOLED Smartwatches</td>
                    <td class="p-3">Chittagong Port Zone</td>
                </tr>
                <tr>
                    <td class="p-3 font-bold text-slate-900">Silk Wave Handloom</td>
                    <td class="p-3">Fazlur Rahman</td>
                    <td class="p-3 font-mono text-indigo-600">01918000000</td>
                    <td class="p-3">Jacquard Silk Ties & Cufflinks</td>
                    <td class="p-3">Tangail Sadar</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection
