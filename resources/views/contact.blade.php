@extends('layouts.app')

@section('title', 'Contact Us - ' . \App\Models\Setting::get('store_name', 'OnlineBdMart'))

@section('content')

<!-- Header -->
<section class="bg-slate-900 text-white py-12 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center max-w-xl">
        <h1 class="text-2xl sm:text-4xl font-extrabold font-serif tracking-tight">Get in Touch</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-2">Have a question regarding products, sizing, or existing orders? We are here to help 24/7.</p>
    </div>
</section>

<!-- Contact Body -->
<section class="py-12 bg-slate-50 min-h-[70vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Top Info Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center text-xl mx-auto">
                    <i class="fas fa-phone"></i>
                </div>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Direct Hotline</h3>
                <p class="text-xs font-semibold text-slate-700">{{ $contactPhone }}</p>
                <span class="text-[10px] text-slate-400 block">Mon - Sat (9am - 9pm)</span>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mx-auto">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">WhatsApp Chat</h3>
                <p class="text-xs font-semibold text-slate-700">{{ $whatsapp }}</p>
                <a href="https://wa.me/88{{ $whatsapp }}" target="_blank" class="inline-block text-[11px] font-bold text-emerald-600 hover:underline">
                    Chat with Agent &rarr;
                </a>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl mx-auto">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Email Inquiries</h3>
                <p class="text-xs font-semibold text-slate-700">{{ $contactEmail }}</p>
                <span class="text-[10px] text-slate-400 block">Typical reply within 2 hours</span>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl mx-auto">
                    <i class="fas fa-location-dot"></i>
                </div>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Physical Store</h3>
                <p class="text-xs font-semibold text-slate-700 line-clamp-2">{{ $address }}</p>
                <span class="text-[10px] text-slate-400 block">Tangail, Bangladesh</span>
            </div>
        </div>

        <!-- Contact Form & FAQs -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            
            <!-- Contact Form -->
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm space-y-6">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-primary-600">Send Message</span>
                    <h2 class="text-xl font-extrabold text-slate-900 font-serif mt-1">Leave us a Message</h2>
                </div>

                <form method="POST" action="{{ route('contact.send') }}" class="space-y-4" x-data="{ sent: false, loading: false }">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Your Name *</label>
                            <input type="text" name="name" required placeholder="e.g. Tanvir Ahmed" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                        </div>
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Your Email *</label>
                            <input type="email" name="email" required placeholder="e.g. tanvir@example.com" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Phone Number (optional)</label>
                            <input type="tel" name="phone" placeholder="e.g. 01711223344" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                        </div>
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Subject</label>
                            <input type="text" name="subject" placeholder="e.g. Order Inquiry / Delivery Status" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                        </div>
                    </div>

                    <div class="text-xs">
                        <label class="font-bold text-slate-700 block mb-1">Your Message *</label>
                        <textarea name="message" required rows="4" placeholder="How can we help you today?" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500"></textarea>
                    </div>

                    <button type="submit" class="w-full py-3.5 px-6 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-primary-600/25 transition">
                        Send Message
                    </button>
                </form>
            </div>

            <!-- Frequently Asked Questions -->
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm space-y-6" x-data="{ activeFaq: 1 }">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-primary-600">Helpful Answers</span>
                    <h2 class="text-xl font-extrabold text-slate-900 font-serif mt-1">Frequently Asked Questions</h2>
                </div>

                <div class="space-y-3 text-xs">
                    <!-- FAQ 1 -->
                    <div class="border border-slate-200 rounded-2xl p-4 cursor-pointer" @click="activeFaq = activeFaq === 1 ? null : 1">
                        <div class="flex items-center justify-between font-bold text-slate-900">
                            <span>How long does delivery take?</span>
                            <i class="fas fa-chevron-down text-slate-400 transition-transform" :class="activeFaq === 1 ? 'rotate-180' : ''"></i>
                        </div>
                        <p class="text-slate-600 mt-2 leading-relaxed" x-show="activeFaq === 1" x-cloak>
                            For orders in Tangail District, delivery takes 24 hours. For Dhaka and other divisions across Bangladesh, delivery typically takes 2-3 business days.
                        </p>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="border border-slate-200 rounded-2xl p-4 cursor-pointer" @click="activeFaq = activeFaq === 2 ? null : 2">
                        <div class="flex items-center justify-between font-bold text-slate-900">
                            <span>Can I inspect the product before paying?</span>
                            <i class="fas fa-chevron-down text-slate-400 transition-transform" :class="activeFaq === 2 ? 'rotate-180' : ''"></i>
                        </div>
                        <p class="text-slate-600 mt-2 leading-relaxed" x-show="activeFaq === 2" x-cloak>
                            Yes! With our Cash on Delivery service, you can check the external parcel upon delivery before making payment to the courier rider.
                        </p>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="border border-slate-200 rounded-2xl p-4 cursor-pointer" @click="activeFaq = activeFaq === 3 ? null : 3">
                        <div class="flex items-center justify-between font-bold text-slate-900">
                            <span>What is your return & exchange policy?</span>
                            <i class="fas fa-chevron-down text-slate-400 transition-transform" :class="activeFaq === 3 ? 'rotate-180' : ''"></i>
                        </div>
                        <p class="text-slate-600 mt-2 leading-relaxed" x-show="activeFaq === 3" x-cloak>
                            We offer a 7-day hassle-free exchange policy. If an item arrives defective or damaged, simply message us on WhatsApp with photos and we will replace it free of charge.
                        </p>
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>

@endsection
