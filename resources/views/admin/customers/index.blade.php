@extends('layouts.admin')

@section('page_title', 'Customers Management')

@section('content')

<div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4 text-xs">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-900 uppercase">Registered Customers & Buyers</h3>
            <p class="text-slate-500">View customer contact details and order statistics.</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-50 font-bold uppercase text-[10px]">
                <tr>
                    <th class="p-3">Customer</th>
                    <th class="p-3">Phone</th>
                    <th class="p-3">Email</th>
                    <th class="p-3 text-center">Orders Placed</th>
                    <th class="p-3">Joined Date</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <tr>
                    <td class="p-3 font-bold text-slate-900">Tanvir Ahmed</td>
                    <td class="p-3 font-mono text-indigo-600">01711223344</td>
                    <td class="p-3 text-slate-500">tanvir@example.com</td>
                    <td class="p-3 text-center font-bold">2 orders</td>
                    <td class="p-3 text-slate-400">Aug 10, 2026</td>
                </tr>
                <tr>
                    <td class="p-3 font-bold text-slate-900">Nusrat Jahan</td>
                    <td class="p-3 font-mono text-indigo-600">01899887766</td>
                    <td class="p-3 text-slate-500">nusrat@example.com</td>
                    <td class="p-3 text-center font-bold">1 order</td>
                    <td class="p-3 text-slate-400">Aug 12, 2026</td>
                </tr>
                <tr>
                    <td class="p-3 font-bold text-slate-900">Sabbir Hossain</td>
                    <td class="p-3 font-mono text-indigo-600">01912345678</td>
                    <td class="p-3 text-slate-500">sabbir@example.com</td>
                    <td class="p-3 text-center font-bold">1 order</td>
                    <td class="p-3 text-slate-400">Aug 14, 2026</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection
