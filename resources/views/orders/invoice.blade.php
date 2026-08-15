<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->id }} - {{ $storeName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; padding: 0 !important; }
            .shadow-lg { box-shadow: none !important; }
            .border { border-color: #e2e8f0 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 p-4 sm:p-8 antialiased">

    <div class="max-w-3xl mx-auto bg-white rounded-3xl p-8 sm:p-12 shadow-lg border border-slate-200 space-y-8">
        
        <!-- Print Header & Controls -->
        <div class="flex items-center justify-between border-b border-slate-200 pb-6 no-print">
            <a href="{{ route('home') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900">&larr; Back to Store</a>
            <button onclick="window.print()" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Invoice
            </button>
        </div>

        <!-- Invoice Title & Store Logo -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 border-b border-slate-200 pb-8">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 uppercase tracking-tight">{{ $storeName }}</h1>
                <p class="text-xs text-slate-500 mt-1">{{ $storeAddress }}</p>
                <p class="text-xs text-slate-500">Phone: {{ $storePhone }}</p>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-xs font-bold uppercase tracking-widest text-indigo-600">INVOICE RECEIPT</span>
                <p class="text-xl font-extrabold text-slate-900 font-mono mt-0.5">#{{ $order->id }}</p>
                <p class="text-xs text-slate-400 mt-1">Date: {{ $order->created_at->format('d M Y, h:i A') }}</p>
            </div>
        </div>

        <!-- Bill To / Ship To -->
        <div class="grid grid-cols-2 gap-8 text-xs">
            <div>
                <span class="font-bold text-slate-400 uppercase tracking-wider block mb-1">Billed & Shipped To:</span>
                <h3 class="font-bold text-slate-900 text-sm">{{ $order->customer_name }}</h3>
                <p class="text-slate-600 mt-1">Phone: {{ $order->phone }}</p>
                <p class="text-slate-600">Address: {{ $order->address }}, {{ $order->upazila }}, {{ $order->district }}</p>
            </div>
            <div class="text-right">
                <span class="font-bold text-slate-400 uppercase tracking-wider block mb-1">Payment Info:</span>
                <p class="text-slate-700"><strong>Method:</strong> {{ strtoupper($order->payment_method) }}</p>
                @if($order->transaction_id)
                <p class="text-slate-700"><strong>TrxID:</strong> {{ $order->transaction_id }}</p>
                @endif
                <p class="text-slate-700"><strong>Status:</strong> <span class="font-bold uppercase text-emerald-600">{{ $order->status }}</span></p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="border border-slate-200 rounded-2xl overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 font-bold text-slate-700 uppercase">
                    <tr>
                        <th class="py-3 px-4">Item Description</th>
                        <th class="py-3 px-4 text-center">Qty</th>
                        <th class="py-3 px-4 text-right">Unit Price</th>
                        <th class="py-3 px-4 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @foreach($order->items as $item)
                    <tr>
                        <td class="py-3.5 px-4 font-semibold">{{ $item->product_name }}</td>
                        <td class="py-3.5 px-4 text-center">{{ $item->quantity }}</td>
                        <td class="py-3.5 px-4 text-right">৳{{ number_format($item->price, 2) }}</td>
                        <td class="py-3.5 px-4 text-right font-bold">৳{{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary Totals -->
        <div class="flex justify-end pt-2 text-xs">
            <div class="w-64 space-y-2">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-bold text-slate-900">৳{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="flex justify-between text-emerald-600 font-semibold">
                    <span>Discount:</span>
                    <span>-৳{{ number_format($order->discount_amount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-slate-600">
                    <span>Delivery Fee:</span>
                    <span class="font-bold text-slate-900">৳{{ number_format($order->delivery_charge, 2) }}</span>
                </div>
                <div class="pt-2 border-t border-slate-300 flex justify-between text-sm font-extrabold text-slate-900">
                    <span>Grand Total:</span>
                    <span class="text-indigo-600 text-base">৳{{ number_format($order->grand_total, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer Thank You Note -->
        <div class="pt-8 border-t border-slate-200 text-center text-xs text-slate-500 space-y-1">
            <p class="font-bold text-slate-700">Thank you for choosing {{ $storeName }}!</p>
            <p>For any queries or assistance, please contact us on WhatsApp: {{ $storePhone }}</p>
        </div>

    </div>

</body>
</html>
