@extends('layouts.app')

@section('title', 'Create Account - ' . \App\Models\Setting::get('store_name', 'OnlineBdMart'))

@section('content')

<section class="py-16 bg-slate-50 min-h-[75vh] flex items-center">
    <div class="max-w-md mx-auto px-4 w-full">
        <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-xl space-y-6">
            
            <div class="text-center space-y-1">
                <h1 class="text-2xl font-extrabold font-serif text-slate-900">Create New Account</h1>
                <p class="text-xs text-slate-500">Sign up for faster express checkout & order tracking.</p>
            </div>

            @if($errors->any())
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('register.submit') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Full Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Tanvir Ahmed" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Phone Number *</label>
                    <input type="tel" name="phone" required placeholder="e.g. 01711223344" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Email Address *</label>
                    <input type="email" name="email" required placeholder="e.g. tanvir@example.com" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Password *</label>
                    <input type="password" name="password" required placeholder="At least 6 characters" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Confirm Password *</label>
                    <input type="password" name="password_confirmation" required placeholder="Re-enter password" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 outline-none focus:border-primary-500">
                </div>

                <button type="submit" class="w-full py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-extrabold text-xs rounded-xl shadow-lg transition">
                    Create Account &rarr;
                </button>
            </form>

            <div class="text-center pt-2 text-xs text-slate-500">
                Already have an account? 
                <a href="{{ route('login') }}" class="font-bold text-primary-600 hover:underline">Sign In</a>
            </div>

        </div>
    </div>
</section>

@endsection
