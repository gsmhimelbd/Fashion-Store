@extends('layouts.admin')

@section('page_title', 'Wholesale & B2B Manager')

@section('content')

<div class="space-y-8">
    
    <!-- Wholesale Product Management Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Active Wholesale Products</h3>
                <p class="text-xs text-slate-500">Products currently listed on the /wholesale catalog at bulk rates.</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="px-4 py-2 bg-primary-600 text-white font-bold text-xs rounded-xl shadow">
                Configure Products &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="p-3">Item</th>
                        <th class="p-3">Product Name</th>
                        <th class="p-3">Retail Price</th>
                        <th class="p-3">Wholesale Rate</th>
                        <th class="p-3 text-center">Min Order (MOQ)</th>
                        <th class="p-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($wholesaleProducts as $wp)
                    <tr>
                        <td class="p-3"><img src="{{ $wp->primary_image_url }}" class="w-10 h-10 object-cover rounded-lg border"></td>
                        <td class="p-3 font-bold text-slate-900">{{ $wp->name }}</td>
                        <td class="p-3 text-slate-500">৳{{ number_format($wp->price, 2) }}</td>
                        <td class="p-3 font-extrabold text-emerald-600">৳{{ number_format($wp->wholesale_price ?: ($wp->price * 0.75), 2) }}</td>
                        <td class="p-3 text-center font-bold">{{ $wp->wholesale_min_qty ?: 5 }} pcs</td>
                        <td class="p-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">Active on B2B</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">No wholesale items configured yet. All catalog items appear as available.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Wholesale Inquiries Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Retailer / Reseller Inquiries</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="p-3">Business</th>
                        <th class="p-3">Contact</th>
                        <th class="p-3">Phone / WhatsApp</th>
                        <th class="p-3">Location</th>
                        <th class="p-3">Monthly Vol</th>
                        <th class="p-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($inquiries as $inq)
                    <tr>
                        <td class="p-3 font-bold text-slate-900">{{ $inq->business_name }}</td>
                        <td class="p-3 font-semibold">{{ $inq->contact_person }}</td>
                        <td class="p-3 text-primary-600 font-mono">{{ $inq->phone }}</td>
                        <td class="p-3 text-slate-600">{{ $inq->district }}</td>
                        <td class="p-3 font-bold text-emerald-600">{{ $inq->estimated_monthly_quantity ?: 'N/A' }}</td>
                        <td class="p-3 text-slate-400 text-[10px]">{{ $inq->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">No B2B wholesale inquiries submitted yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-2 flex justify-center">
            {{ $inquiries->links() }}
        </div>
    </div>

</div>

@endsection
