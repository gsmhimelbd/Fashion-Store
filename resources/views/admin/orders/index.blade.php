@extends('layouts.admin')

@section('page_title', 'Orders Management')

@section('content')

<div x-data="orderManager()">
    
    <!-- Status Filter Tabs & CSV Export -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        
        <!-- Status Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-semibold">
            @foreach(['all' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'] as $k => $label)
            <a href="{{ route('admin.orders.index', ['status' => $k]) }}" 
               class="px-3.5 py-2 rounded-xl whitespace-nowrap transition {{ $statusFilter === $k ? 'bg-primary-600 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                {{ $label }} ({{ $counts[$k] }})
            </a>
            @endforeach
        </div>

        <!-- Export CSV Form -->
        <form method="POST" action="{{ route('admin.orders.export_csv', ['status' => $statusFilter]) }}">
            @csrf
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-2">
                <i class="fas fa-file-csv text-sm"></i> Export CSV
            </button>
        </form>
    </div>

    <!-- Search Input -->
    <div class="mt-4 flex items-center justify-between">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="w-full sm:w-80">
            @if($statusFilter !== 'all')
            <input type="hidden" name="status" value="{{ $statusFilter }}">
            @endif
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by ID, customer, phone, TrxID..." class="w-full pl-9 pr-4 py-2 text-xs bg-white border border-slate-200 rounded-xl outline-none focus:border-primary-500">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fas fa-search text-xs"></i>
                </span>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mt-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4">Order #</th>
                        <th class="py-3.5 px-4">Customer Info</th>
                        <th class="py-3.5 px-4">Location</th>
                        <th class="py-3.5 px-4">Payment</th>
                        <th class="py-3.5 px-4">Grand Total</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @forelse($orders as $order)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-900">#{{ $order->id }}</td>
                        <td class="py-3.5 px-4">
                            <p class="font-bold text-slate-900">{{ $order->customer_name }}</p>
                            <span class="text-[11px] text-slate-500 block">{{ $order->phone }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <p class="font-semibold text-slate-800">{{ $order->district }}</p>
                            <span class="text-[10px] text-slate-400 block">{{ $order->upazila }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $order->payment_method === 'cod' ? 'bg-emerald-50 text-emerald-700' : 'bg-indigo-50 text-indigo-700' }}">
                                {{ $order->payment_method }}
                            </span>
                            @if($order->transaction_id)
                            <span class="text-[10px] font-mono text-slate-500 block mt-0.5">{{ $order->transaction_id }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            ৳{{ number_format($order->grand_total, 2) }}
                        </td>
                        <td class="py-3.5 px-4">
                            <form method="POST" action="{{ route('admin.orders.update_status', $order->id) }}" class="inline">
                                @csrf
                                <select name="status" onchange="this.form.submit()" class="px-2 py-1 rounded-lg text-[10px] font-extrabold capitalize border {{ $order->status_badge_class }} outline-none cursor-pointer">
                                    @foreach(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'] as $st)
                                    <option value="{{ $st }}" {{ $order->status === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="py-3.5 px-4 text-slate-400 text-[11px]">
                            {{ $order->created_at->format('d M Y') }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" @click="viewOrder({{ json_encode($order) }})" class="p-1.5 text-primary-600 hover:text-primary-800" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="{{ route('order.invoice', $order->id) }}" target="_blank" class="p-1.5 text-slate-500 hover:text-slate-800" title="Print Invoice">
                                    <i class="fas fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">No orders found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100 flex justify-center">
            {{ $orders->links() }}
        </div>
    </div>

    <!-- Order View Modal -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalOpen = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl p-6 sm:p-8 space-y-6" x-show="activeOrder">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 font-serif" x-text="'Order #' + activeOrder?.id + ' Details'"></h3>
                        <p class="text-xs text-slate-400" x-text="'Placed on ' + activeOrder?.created_at"></p>
                    </div>
                    <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="space-y-1.5 p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                        <h4 class="font-bold text-slate-900 uppercase tracking-wider text-[10px]">Customer Information</h4>
                        <p><strong class="text-slate-500">Name:</strong> <span class="font-bold text-slate-900" x-text="activeOrder?.customer_name"></span></p>
                        <p><strong class="text-slate-500">Phone:</strong> <span class="font-semibold text-slate-800" x-text="activeOrder?.phone"></span></p>
                        <p><strong class="text-slate-500">WhatsApp:</strong> <span class="text-slate-700" x-text="activeOrder?.whatsapp || 'N/A'"></span></p>
                        <p><strong class="text-slate-500">Address:</strong> <span class="text-slate-700" x-text="activeOrder?.address + ', ' + activeOrder?.upazila + ', ' + activeOrder?.district"></span></p>
                        <template x-if="activeOrder?.notes">
                            <p><strong class="text-slate-500">Notes:</strong> <span class="italic text-slate-700" x-text="activeOrder?.notes"></span></p>
                        </template>
                    </div>

                    <div class="space-y-1.5 p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                        <h4 class="font-bold text-slate-900 uppercase tracking-wider text-[10px]">Payment Details</h4>
                        <p><strong class="text-slate-500">Method:</strong> <span class="font-bold uppercase text-primary-600" x-text="activeOrder?.payment_method"></span></p>
                        <p><strong class="text-slate-500">Payment Number:</strong> <span class="text-slate-700" x-text="activeOrder?.payment_number || 'N/A'"></span></p>
                        <p><strong class="text-slate-500">Transaction ID:</strong> <span class="font-mono font-bold text-slate-800" x-text="activeOrder?.transaction_id || 'N/A'"></span></p>
                        <p><strong class="text-slate-500">Subtotal:</strong> <span class="font-bold text-slate-900" x-text="'৳' + Number(activeOrder?.subtotal || 0).toFixed(2)"></span></p>
                        <p><strong class="text-slate-500">Delivery Fee:</strong> <span class="text-slate-700" x-text="'৳' + Number(activeOrder?.delivery_charge || 0).toFixed(2)"></span></p>
                        <p class="pt-1 border-t border-slate-200 font-extrabold text-sm text-primary-600"><strong class="text-slate-900">Grand Total:</strong> <span x-text="'৳' + Number(activeOrder?.grand_total || 0).toFixed(2)"></span></p>
                    </div>
                </div>

                <!-- Order Items List -->
                <div class="space-y-2 text-xs">
                    <h4 class="font-bold text-slate-900 uppercase tracking-wider text-[10px]">Ordered Items</h4>
                    <div class="border border-slate-200 rounded-2xl overflow-hidden divide-y divide-slate-100">
                        <template x-for="item in activeOrder?.items" :key="item.id">
                            <div class="p-3 flex items-center justify-between">
                                <div>
                                    <p class="font-bold text-slate-900" x-text="item.product_name"></p>
                                    <span class="text-[11px] text-slate-400" x-text="'Qty: ' + item.quantity + ' × ৳' + Number(item.price).toFixed(2)"></span>
                                </div>
                                <span class="font-bold text-slate-900" x-text="'৳' + (item.price * item.quantity).toFixed(2)"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Footer Status Update & Print -->
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <a :href="'/order/' + activeOrder?.id + '/invoice'" target="_blank" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition flex items-center gap-2">
                        <i class="fas fa-print"></i> Print Invoice
                    </a>
                    <button type="button" @click="modalOpen = false" class="px-5 py-2 border border-slate-300 font-bold text-xs text-slate-700 rounded-xl hover:bg-slate-50 transition">
                        Close
                    </button>
                </div>

            </div>
        </div>
    </div>

</div>

<script>
function orderManager() {
    return {
        modalOpen: false,
        activeOrder: null,

        viewOrder(order) {
            this.activeOrder = order;
            this.modalOpen = true;
        }
    }
}
</script>

@endsection
