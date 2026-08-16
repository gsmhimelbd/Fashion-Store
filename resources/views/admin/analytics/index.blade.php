@extends('layouts.admin')

@section('page_title', 'Customer Behavior & Location Analytics')

@section('content')

<div class="space-y-8 text-xs" x-data="analyticsManager()">
    
    <!-- Top KPI Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-5">
        <div class="bg-white p-6 rounded-3xl border shadow-sm space-y-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Conversion Rate</span>
            <p class="text-2xl font-black text-indigo-600">4.8%</p>
            <span class="text-[10px] text-emerald-600 font-bold">↑ High conversion</span>
        </div>

        <div class="bg-white p-6 rounded-3xl border shadow-sm space-y-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Average Order Value</span>
            <p class="text-2xl font-black text-slate-900">৳2,850.00</p>
            <span class="text-[10px] text-slate-400">BDT per order</span>
        </div>

        <div class="bg-white p-6 rounded-3xl border shadow-sm space-y-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Abandoned Carts (Lost Sales)</span>
            <p class="text-2xl font-black text-rose-500">3 Potential</p>
            <span class="text-[10px] text-rose-400 font-bold">Recoverable via WhatsApp</span>
        </div>

        <div class="bg-white p-6 rounded-3xl border shadow-sm space-y-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Top Order Location</span>
            <p class="text-2xl font-black text-emerald-600">Dhaka & Tangail</p>
            <span class="text-[10px] text-slate-400">72% of total orders</span>
        </div>
    </div>

    <!-- 1. Location Analytics: Where orders are coming from (District & Division Heatmap) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <div>
                    <span class="text-[10px] font-bold text-indigo-600 uppercase">📍 Customer Geographic Behavior</span>
                    <h3 class="text-sm font-extrabold text-slate-900 font-serif mt-0.5">Order Volume by Location & District</h3>
                </div>
            </div>

            <div class="space-y-3">
                <!-- Location 1 -->
                <div>
                    <div class="flex justify-between font-bold mb-1">
                        <span>Dhaka Division (Metro, Dhanmondi, Gulshan, Uttara)</span>
                        <span class="text-indigo-600">45% (18 Orders)</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-indigo-600 h-full rounded-full" style="width: 45%;"></div>
                    </div>
                </div>

                <!-- Location 2 -->
                <div>
                    <div class="flex justify-between font-bold mb-1">
                        <span>Tangail District (Home Delivery Hub)</span>
                        <span class="text-emerald-600">28% (11 Orders)</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-emerald-500 h-full rounded-full" style="width: 28%;"></div>
                    </div>
                </div>

                <!-- Location 3 -->
                <div>
                    <div class="flex justify-between font-bold mb-1">
                        <span>Chittagong & Comilla Zone</span>
                        <span class="text-cyan-600">14% (6 Orders)</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-cyan-500 h-full rounded-full" style="width: 14%;"></div>
                    </div>
                </div>

                <!-- Location 4 -->
                <div>
                    <div class="flex justify-between font-bold mb-1">
                        <span>Sylhet & Moulvibazar</span>
                        <span class="text-amber-600">8% (3 Orders)</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-amber-500 h-full rounded-full" style="width: 8%;"></div>
                    </div>
                </div>

                <!-- Location 5 -->
                <div>
                    <div class="flex justify-between font-bold mb-1">
                        <span>Rajshahi, Khulna & Other Districts</span>
                        <span class="text-purple-600">5% (2 Orders)</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-purple-500 h-full rounded-full" style="width: 5%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Conversion Funnel Analysis -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <div>
                    <span class="text-[10px] font-bold text-indigo-600 uppercase">📊 E-Commerce Funnel</span>
                    <h3 class="text-sm font-extrabold text-slate-900 font-serif mt-0.5">Visitor to Purchase Conversion Funnel</h3>
                </div>
            </div>

            <div class="space-y-3 pt-2">
                <div class="p-3 rounded-2xl bg-indigo-50/50 border flex items-center justify-between font-bold">
                    <span class="flex items-center gap-2"><span>1. Product Page Views</span></span>
                    <span class="text-indigo-700">1,420 Visitors (100%)</span>
                </div>

                <div class="p-3 rounded-2xl bg-indigo-50 border flex items-center justify-between font-bold">
                    <span class="flex items-center gap-2"><span>2. Added to Cart / Bag</span></span>
                    <span class="text-indigo-800">312 Users (22%)</span>
                </div>

                <div class="p-3 rounded-2xl bg-indigo-100/70 border flex items-center justify-between font-bold">
                    <span class="flex items-center gap-2"><span>3. Reached Single-Page Checkout</span></span>
                    <span class="text-indigo-900">128 Users (9%)</span>
                </div>

                <div class="p-3 rounded-2xl bg-emerald-500 text-white flex items-center justify-between font-extrabold shadow-md">
                    <span class="flex items-center gap-2"><span>4. Confirmed & Ordered Successfully</span></span>
                    <span>68 Completed (4.8%)</span>
                </div>
            </div>
        </div>

    </div>

    <!-- 3. ABANDONED CARTS / DROP-OFF TRACKING ("Kon package order korte gele korlo na") -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b pb-4">
            <div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-rose-50 text-rose-700">🛒 Cart Abandonment Tracking</span>
                <h3 class="text-base font-extrabold text-slate-900 font-serif mt-1">Customers Who Dropped Off Before Placing Order</h3>
                <p class="text-slate-500 mt-0.5">Recover lost sales by contacting customers on WhatsApp with a direct 10% discount promo link.</p>
            </div>
            <span class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 font-bold border border-rose-200">
                Potential Recoverable Value: ৳10,400.00
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="p-3">Customer / Phone</th>
                        <th class="p-3">Item / Package in Cart</th>
                        <th class="p-3">Cart Value</th>
                        <th class="p-3">Drop-Off Step</th>
                        <th class="p-3">Time Ago</th>
                        <th class="p-3 text-right">Recovery Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y text-slate-800">
                    <tr class="hover:bg-slate-50">
                        <td class="p-3">
                            <p class="font-bold text-slate-900">Mahmudul Hasan</p>
                            <span class="font-mono text-indigo-600 text-[11px]">01799882211</span>
                        </td>
                        <td class="p-3 font-semibold text-slate-700">
                            Luxury Chronograph Sapphire Watch (Qty: 1)
                        </td>
                        <td class="p-3 font-bold text-slate-900">৳3,250.00</td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">Left at Checkout Step</span></td>
                        <td class="p-3 text-slate-400">15 mins ago</td>
                        <td class="p-3 text-right">
                            <a href="https://wa.me/8801799882211?text={{ urlencode('Hello Mahmudul! You left the Luxury Chronograph Watch in your bag at OnlineBdMart. Complete your order today & get 10% OFF with code FASHION10! Link: ' . url('/checkout')) }}" target="_blank" class="px-3.5 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold shadow flex items-center gap-1.5 inline-flex">
                                <i class="fab fa-whatsapp"></i> Recover WhatsApp
                            </a>
                        </td>
                    </tr>

                    <tr class="hover:bg-slate-50">
                        <td class="p-3">
                            <p class="font-bold text-slate-900">Sadia Afrin</p>
                            <span class="font-mono text-indigo-600 text-[11px]">01822334455</span>
                        </td>
                        <td class="p-3 font-semibold text-slate-700">
                            Minimalist Luxury Leather Handbag + Pearl Necklace
                        </td>
                        <td class="p-3 font-bold text-slate-900">৳5,000.00</td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200">Left in Cart Drawer</span></td>
                        <td class="p-3 text-slate-400">45 mins ago</td>
                        <td class="p-3 text-right">
                            <a href="https://wa.me/8801822334455?text={{ urlencode('Hello Sadia! You left your favorite handbag in your bag at OnlineBdMart. Complete checkout now with Free Delivery across Bangladesh! Link: ' . url('/checkout')) }}" target="_blank" class="px-3.5 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold shadow flex items-center gap-1.5 inline-flex">
                                <i class="fab fa-whatsapp"></i> Recover WhatsApp
                            </a>
                        </td>
                    </tr>

                    <tr class="hover:bg-slate-50">
                        <td class="p-3">
                            <p class="font-bold text-slate-900">Kamrul Islam</p>
                            <span class="font-mono text-indigo-600 text-[11px]">01977665544</span>
                        </td>
                        <td class="p-3 font-semibold text-slate-700">
                            Handcrafted Full-Grain Leather Wallet + Belt Set
                        </td>
                        <td class="p-3 font-bold text-slate-900">৳2,150.00</td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">Payment Selection</span></td>
                        <td class="p-3 text-slate-400">2 hours ago</td>
                        <td class="p-3 text-right">
                            <a href="https://wa.me/8801977665544?text={{ urlencode('Hello Kamrul! Cash on Delivery is available for your leather wallet order at OnlineBdMart. Finish your order in 30 seconds: ' . url('/checkout')) }}" target="_blank" class="px-3.5 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold shadow flex items-center gap-1.5 inline-flex">
                                <i class="fab fa-whatsapp"></i> Recover WhatsApp
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function analyticsManager() {
    return {
        // Analytics logic
    }
}
</script>

@endsection
