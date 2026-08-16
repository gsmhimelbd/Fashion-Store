@extends('layouts.app')

@section('title', 'Shopping Cart - ' . \App\Models\Setting::get('store_name', 'OnlineBdMart'))

@section('content')

<!-- Header -->
<section class="bg-slate-900 text-white py-10 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl sm:text-4xl font-extrabold font-serif tracking-tight">Shopping Bag</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">Review your selected luxury items before proceed to checkout.</p>
    </div>
</section>

<!-- Cart Body -->
<section class="py-12 bg-slate-50 min-h-[60vh]" x-data="cartPage()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <template x-if="items.length === 0">
            <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center max-w-lg mx-auto shadow-sm space-y-4">
                <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center text-slate-300 text-3xl mx-auto">
                    <i class="fas fa-bag-shopping"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-900 font-serif">Your Bag is Empty</h2>
                <p class="text-xs text-slate-500">Looks like you haven't added anything to your cart yet.</p>
                <div class="pt-4">
                    <a href="{{ route('shop') }}" class="inline-flex items-center gap-2 px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-primary-600/25 transition">
                        Explore Collection <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        </template>

        <template x-if="items.length > 0">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Items Table / List -->
                <div class="lg:col-span-2 space-y-4">
                    
                    <!-- Free Shipping Meter -->
                    <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm text-xs">
                        <div class="flex justify-between items-center mb-1.5 font-bold">
                            <span x-text="subtotal >= 2000 ? '🎉 You have qualified for FREE delivery across Bangladesh!' : 'Add ' + formatCurrency(2000 - subtotal) + ' more for Free Delivery'"></span>
                            <span class="text-primary-600" x-text="Math.min(100, Math.round((subtotal / 2000) * 100)) + '%'"></span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-gradient-to-r from-primary-500 to-emerald-400 h-full rounded-full transition-all duration-500" :style="'width: ' + Math.min(100, (subtotal / 2000) * 100) + '%'"></div>
                        </div>
                    </div>

                    <!-- Items Card -->
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden divide-y divide-slate-100">
                        <template x-for="item in items" :key="item.id">
                            <div class="p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <div class="flex items-center gap-4 w-full sm:w-auto">
                                    <img :src="item.image" :alt="item.name" class="w-20 h-20 object-cover rounded-2xl bg-slate-100 border border-slate-200 shrink-0">
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-900" x-text="item.name"></h3>
                                        <p class="text-xs font-bold text-primary-600 mt-1" x-text="formatCurrency(item.price)"></p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto pt-2 sm:pt-0 border-t sm:border-0 border-slate-100">
                                    <!-- Stepper -->
                                    <div class="flex items-center border border-slate-300 rounded-xl bg-white p-1">
                                        <button type="button" @click="updateQty(item.id, item.quantity - 1)" class="w-7 h-7 flex items-center justify-center text-slate-600 hover:bg-slate-100 rounded-lg text-xs font-bold">-</button>
                                        <span class="w-10 text-center text-xs font-bold text-slate-900" x-text="item.quantity"></span>
                                        <button type="button" @click="updateQty(item.id, item.quantity + 1)" class="w-7 h-7 flex items-center justify-center text-slate-600 hover:bg-slate-100 rounded-lg text-xs font-bold">+</button>
                                    </div>

                                    <!-- Total -->
                                    <span class="text-sm font-extrabold text-slate-900 w-24 text-right" x-text="formatCurrency(item.price * item.quantity)"></span>

                                    <!-- Remove -->
                                    <button type="button" @click="removeItem(item.id)" class="text-slate-300 hover:text-rose-500 transition p-1">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="flex justify-between items-center pt-2">
                        <a href="{{ route('shop') }}" class="text-xs font-bold text-slate-600 hover:text-primary-600 flex items-center gap-1.5">
                            <i class="fas fa-arrow-left text-[10px]"></i> Continue Shopping
                        </a>
                        <button type="button" @click="clearCart()" class="text-xs font-bold text-rose-500 hover:underline">
                            Clear Entire Cart
                        </button>
                    </div>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    
                    <!-- Coupon Promo Code Box -->
                    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Have a Coupon?</h3>
                        <div class="flex gap-2">
                            <input type="text" x-model="couponCode" placeholder="e.g. FASHION10" class="flex-1 border border-slate-300 rounded-xl px-3.5 py-2 text-xs uppercase font-mono outline-none focus:border-primary-500">
                            <button type="button" @click="applyCoupon()" class="px-4 py-2 bg-slate-900 hover:bg-primary-600 text-white font-bold text-xs rounded-xl transition">
                                Apply
                            </button>
                        </div>
                        <template x-if="discount > 0">
                            <div class="flex items-center justify-between text-xs bg-emerald-50 text-emerald-800 p-2.5 rounded-xl font-semibold">
                                <span x-text="'Coupon: ' + appliedCouponCode + ' (-' + formatCurrency(discount) + ')'"></span>
                                <button type="button" @click="removeCoupon()" class="text-emerald-900 hover:text-rose-600">&times;</button>
                            </div>
                        </template>
                    </div>

                    <!-- Summary Card -->
                    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                        <h3 class="text-sm font-extrabold text-slate-900 font-serif border-b border-slate-100 pb-3">Order Summary</h3>
                        
                        <div class="space-y-2 text-xs text-slate-600">
                            <div class="flex justify-between">
                                <span>Cart Subtotal:</span>
                                <span class="font-bold text-slate-900" x-text="formatCurrency(subtotal)"></span>
                            </div>

                            <template x-if="discount > 0">
                                <div class="flex justify-between text-emerald-600 font-semibold">
                                    <span>Promo Discount:</span>
                                    <span x-text="'-' + formatCurrency(discount)"></span>
                                </div>
                            </template>

                            <div class="flex justify-between">
                                <span>Delivery (Tangail):</span>
                                <span class="font-medium text-slate-700">৳50.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Delivery (Outside):</span>
                                <span class="font-medium text-slate-700">৳150.00</span>
                            </div>

                            <div class="pt-3 border-t border-slate-200 flex justify-between text-sm font-extrabold text-slate-900">
                                <span>Estimated Total:</span>
                                <span class="text-primary-600 text-lg" x-text="formatCurrency(Math.max(0, subtotal - discount))"></span>
                            </div>
                        </div>

                        <div class="pt-2">
                            <a href="{{ route('checkout.index') }}" class="w-full py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs sm:text-sm rounded-xl text-center shadow-xl shadow-primary-600/25 transition flex items-center justify-center gap-2">
                                Proceed to Checkout <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>

                        <div class="pt-2 text-[11px] text-slate-400 text-center space-y-1">
                            <p><i class="fas fa-lock text-emerald-500 mr-1"></i> Bank-grade 256-bit SSL Checkout</p>
                            <p>Cash on Delivery, bKash, Nagad & Rocket Accepted</p>
                        </div>
                    </div>
                </div>

            </div>
        </template>
    </div>
</section>

<script>
function cartPage() {
    return {
        items: @json(array_values(session('cart', []))),
        subtotal: {{ array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], session('cart', []))) }},
        discount: {{ session('applied_coupon.discount', 0) }},
        couponCode: '',
        appliedCouponCode: '{{ session('applied_coupon.code', '') }}',

        formatCurrency(amount) {
            return '৳' + Number(amount || 0).toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        updateQty(productId, newQty) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch('{{ route('cart.update') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ product_id: productId, quantity: newQty })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.subtotal = data.subtotal;
                    if (newQty <= 0) {
                        this.items = this.items.filter(i => i.id !== productId);
                    } else {
                        const found = this.items.find(i => i.id === productId);
                        if (found) found.quantity = newQty;
                    }
                }
            });
        },

        removeItem(productId) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch('{{ route('cart.remove') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ product_id: productId })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.subtotal = data.subtotal;
                    this.items = this.items.filter(i => i.id !== productId);
                    showToast('Item removed.', 'info');
                }
            });
        },

        clearCart() {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch('{{ route('cart.clear') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                this.items = [];
                this.subtotal = 0;
                this.discount = 0;
            });
        },

        applyCoupon() {
            if (!this.couponCode) return;
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch('{{ route('cart.apply_coupon') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ code: this.couponCode })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.discount = data.discount;
                    this.appliedCouponCode = this.couponCode;
                    this.couponCode = '';
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message || 'Invalid coupon.', 'error');
                }
            });
        },

        removeCoupon() {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch('{{ route('cart.remove_coupon') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                this.discount = 0;
                this.appliedCouponCode = '';
                showToast('Coupon removed.', 'info');
            });
        }
    }
}
</script>

@endsection
