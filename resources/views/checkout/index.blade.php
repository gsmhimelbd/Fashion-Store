@extends('layouts.app')

@section('title', 'Fast Express Checkout - ' . \App\Models\Setting::get('store_name', 'OnlineBdMart'))

@section('content')

<!-- Header -->
<section class="bg-slate-900 text-white py-8 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold font-serif tracking-tight">Express Checkout</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">Complete your delivery address and preferred payment method for 64 districts in Bangladesh.</p>
    </div>
</section>

<!-- Checkout Form Section -->
<section class="py-12 bg-slate-50 min-h-screen" x-data="checkoutForm()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form @submit.prevent="submitOrder()" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left: Shipping & Payment Details -->
            <div class="lg:col-span-7 space-y-6">
                
                <!-- Step 1: Customer Contact -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                        <div class="w-7 h-7 rounded-full bg-primary-100 text-primary-600 font-bold flex items-center justify-center text-xs">1</div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Contact & Customer Information</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="sm:col-span-2">
                            <label class="font-bold text-slate-700 block mb-1">Full Name *</label>
                            <input type="text" x-model="form.customer_name" required placeholder="e.g. Tanvir Ahmed" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Phone Number (Required for Courier) *</label>
                            <input type="tel" x-model="form.phone" required placeholder="e.g. 01711223344" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Email Address (For Invoice Receipt & Tracking)</label>
                            <input type="email" x-model="form.email" placeholder="e.g. user@example.com" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                        </div>
                    </div>
                </div>

                <!-- Step 2: Bangladesh All 64 Districts Delivery Location -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-primary-100 text-primary-600 font-bold flex items-center justify-center text-xs">2</div>
                            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Bangladesh Delivery Location</h2>
                        </div>
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full" x-text="deliveryNote">Tangail (Within 24h)</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Select District (64 Districts) *</label>
                            <select x-model="form.district" @change="updateDeliveryRate()" required class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500 bg-white cursor-pointer font-semibold">
                                <optgroup label="⭐ Tangail Home Zone (৳50 - 24 Hours Delivery)">
                                    <option value="Tangail">Tangail (৳50.00 - Fast 24h)</option>
                                </optgroup>
                                <optgroup label="🏙️ Dhaka Division (৳80 - 1-2 Days)">
                                    <option value="Dhaka">Dhaka City (৳80.00)</option>
                                    <option value="Gazipur">Gazipur (৳80.00)</option>
                                    <option value="Narayanganj">Narayanganj (৳80.00)</option>
                                    <option value="Narsingdi">Narsingdi (৳80.00)</option>
                                    <option value="Manikganj">Manikganj (৳80.00)</option>
                                    <option value="Munshiganj">Munshiganj (৳80.00)</option>
                                </optgroup>
                                <optgroup label="🌊 Chittagong & Sylhet Division (৳130 - 2-3 Days)">
                                    <option value="Chittagong">Chittagong (৳130.00)</option>
                                    <option value="Sylhet">Sylhet (৳130.00)</option>
                                    <option value="Comilla">Comilla (৳130.00)</option>
                                    <option value="Cox's Bazar">Cox's Bazar (৳130.00)</option>
                                    <option value="Moulvibazar">Moulvibazar (৳130.00)</option>
                                    <option value="Habiganj">Habiganj (৳130.00)</option>
                                    <option value="Sunamganj">Sunamganj (৳130.00)</option>
                                    <option value="Brahmanbaria">Brahmanbaria (৳130.00)</option>
                                    <option value="Feni">Feni (৳130.00)</option>
                                    <option value="Noakhali">Noakhali (৳130.00)</option>
                                </optgroup>
                                <optgroup label="🌾 Rajshahi, Khulna, Barisal, Rangpur & Others (৳150 - 2-3 Days)">
                                    <option value="Rajshahi">Rajshahi (৳150.00)</option>
                                    <option value="Khulna">Khulna (৳150.00)</option>
                                    <option value="Bogra">Bogra (৳150.00)</option>
                                    <option value="Pabna">Pabna (৳150.00)</option>
                                    <option value="Rangpur">Rangpur (৳150.00)</option>
                                    <option value="Dinajpur">Dinajpur (৳150.00)</option>
                                    <option value="Barisal">Barisal (৳150.00)</option>
                                    <option value="Jessore">Jessore (৳150.00)</option>
                                    <option value="Kushtia">Kushtia (৳150.00)</option>
                                    <option value="Mymensingh">Mymensingh (৳150.00)</option>
                                    <option value="Other">All Other Bangladesh Districts (৳150.00)</option>
                                </optgroup>
                            </select>
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Upazila / Thana / Area *</label>
                            <input type="text" x-model="form.upazila" required placeholder="e.g. Tangail Sadar, Dhanmondi, etc." class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="font-bold text-slate-700 block mb-1">Full Street Address / House / Road *</label>
                            <textarea x-model="form.address" required rows="2" placeholder="e.g. House 24, Road 4, Area, Ward..." class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Payment Selection -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                        <div class="w-7 h-7 rounded-full bg-primary-100 text-primary-600 font-bold flex items-center justify-center text-xs">3</div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Select Payment Method</h2>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <label class="p-4 rounded-2xl border-2 cursor-pointer flex flex-col items-center text-center transition"
                               :class="form.payment_method === 'cod' ? 'border-emerald-500 bg-emerald-50 text-emerald-900 font-bold' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" x-model="form.payment_method" value="cod" class="hidden">
                            <i class="fas fa-hand-holding-dollar text-2xl text-emerald-600 mb-2"></i>
                            <span>Cash on Delivery</span>
                        </label>

                        <label class="p-4 rounded-2xl border-2 cursor-pointer flex flex-col items-center text-center transition"
                               :class="form.payment_method === 'bkash' ? 'border-pink-500 bg-pink-50 text-pink-900 font-bold' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" x-model="form.payment_method" value="bkash" class="hidden">
                            <span class="text-2xl font-black text-pink-600 mb-2">bKash</span>
                            <span>bKash</span>
                        </label>

                        <label class="p-4 rounded-2xl border-2 cursor-pointer flex flex-col items-center text-center transition"
                               :class="form.payment_method === 'nagad' ? 'border-orange-500 bg-orange-50 text-orange-900 font-bold' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" x-model="form.payment_method" value="nagad" class="hidden">
                            <span class="text-2xl font-black text-orange-600 mb-2">Nagad</span>
                            <span>Nagad</span>
                        </label>

                        <label class="p-4 rounded-2xl border-2 cursor-pointer flex flex-col items-center text-center transition"
                               :class="form.payment_method === 'rocket' ? 'border-purple-500 bg-purple-50 text-purple-900 font-bold' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" x-model="form.payment_method" value="rocket" class="hidden">
                            <span class="text-2xl font-black text-purple-600 mb-2">Rocket</span>
                            <span>Rocket</span>
                        </label>
                    </div>

                    <div x-show="form.payment_method !== 'cod'" x-cloak class="p-5 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-3">
                        <div class="flex items-center justify-between bg-white p-3 rounded-xl border">
                            <div>
                                <span class="font-bold text-slate-500 uppercase text-[10px]" x-text="form.payment_method.toUpperCase() + ' Number:'"></span>
                                <p class="text-sm font-extrabold text-slate-900 font-mono" x-text="getPaymentNumber()"></p>
                            </div>
                            <button type="button" @click="copyPaymentNumber()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-lg transition">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <input type="tel" x-model="form.payment_number" placeholder="Your Sender Number" class="w-full border rounded-xl px-3 py-2 bg-white outline-none">
                            <input type="text" x-model="form.transaction_id" placeholder="Transaction ID (TrxID)" class="w-full border rounded-xl px-3 py-2 bg-white uppercase font-mono outline-none">
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right: Order Summary Sidebar -->
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-5 sticky top-24">
                    <h3 class="text-sm font-extrabold text-slate-900 font-serif border-b border-slate-100 pb-3">Your Order Summary</h3>

                    <div class="max-h-60 overflow-y-auto space-y-3 divide-y divide-slate-100 pr-1">
                        @foreach($items as $item)
                        <div class="pt-3 first:pt-0 flex items-center gap-3">
                            <img src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}" class="w-12 h-12 object-cover rounded-xl bg-slate-100 border shrink-0">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-bold text-slate-900 truncate">{{ $item['name'] }}</h4>
                                <span class="text-[11px] text-slate-500">Qty: {{ $item['quantity'] }} &times; ৳{{ number_format($item['price'], 2) }}</span>
                            </div>
                            <span class="text-xs font-extrabold text-slate-900 shrink-0">৳{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="pt-3 border-t border-slate-100 space-y-2 text-xs text-slate-600">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span class="font-bold text-slate-900" x-text="formatCurrency(subtotal)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Delivery Fee (<span x-text="form.district"></span>):</span>
                            <span class="font-bold text-slate-900" x-text="formatCurrency(deliveryCharge)"></span>
                        </div>
                        <div class="pt-3 border-t border-slate-200 flex justify-between text-base font-extrabold text-slate-900">
                            <span>Grand Total:</span>
                            <span class="text-primary-600 text-xl" x-text="formatCurrency(grandTotal)"></span>
                        </div>
                    </div>

                    <button type="submit" 
                            :disabled="loading" 
                            class="w-full py-4 px-6 bg-primary-600 hover:bg-primary-700 text-white font-extrabold text-sm rounded-2xl shadow-xl transition flex items-center justify-center gap-2">
                        <span x-show="!loading">Confirm & Place Order <i class="fas fa-check-circle ml-1"></i></span>
                        <span x-show="loading"><i class="fas fa-spinner fa-spin"></i> Processing & Sending Email Receipt...</span>
                    </button>
                </div>
            </div>

        </form>
    </div>
</section>

<script>
function checkoutForm() {
    return {
        subtotal: {{ $subtotal }},
        discount: {{ $discount }},
        deliveryCharge: 50,
        deliveryNote: 'Tangail (Within 24h)',
        loading: false,

        form: {
            customer_name: '{{ session('customer_name', '') }}',
            phone: '{{ session('customer_phone', '') }}',
            email: '{{ session('customer_email', '') }}',
            district: 'Tangail',
            upazila: '',
            address: '',
            payment_method: 'cod',
            payment_number: '',
            transaction_id: ''
        },

        bkashNum: '{{ $bkash }}',
        nagadNum: '{{ $nagad }}',
        rocketNum: '{{ $rocket }}',

        get grandTotal() {
            return Math.max(0, this.subtotal + this.deliveryCharge - this.discount);
        },

        formatCurrency(amount) {
            return '৳' + Number(amount || 0).toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        updateDeliveryRate() {
            const d = this.form.district.toLowerCase();
            if (d === 'tangail') {
                this.deliveryCharge = 50;
                this.deliveryNote = 'Tangail (Within 24 Hours)';
            } else if (['dhaka', 'gazipur', 'narayanganj', 'narsingdi', 'manikganj', 'munshiganj'].includes(d)) {
                this.deliveryCharge = 80;
                this.deliveryNote = 'Dhaka Metro (1-2 Days)';
            } else if (['chittagong', 'sylhet', 'comilla', "cox's bazar", 'moulvibazar', 'habiganj', 'sunamganj', 'feni', 'noakhali', 'brahmanbaria'].includes(d)) {
                this.deliveryCharge = 130;
                this.deliveryNote = 'Chittagong & Sylhet (2-3 Days)';
            } else {
                this.deliveryCharge = 150;
                this.deliveryNote = 'National Express (2-3 Days)';
            }
        },

        getPaymentNumber() {
            if (this.form.payment_method === 'bkash') return this.bkashNum;
            if (this.form.payment_method === 'nagad') return this.nagadNum;
            if (this.form.payment_method === 'rocket') return this.rocketNum;
            return '';
        },

        copyPaymentNumber() {
            const num = this.getPaymentNumber();
            navigator.clipboard.writeText(num);
            showToast('Copied: ' + num, 'success');
        },

        submitOrder() {
            this.loading = true;
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch('{{ route('checkout.process') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.form)
            })
            .then(r => r.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    showToast(data.message || 'Please check your inputs.', 'error');
                }
            })
            .catch(e => {
                this.loading = false;
                showToast('Failed to place order.', 'error');
            });
        }
    }
}
</script>

@endsection
