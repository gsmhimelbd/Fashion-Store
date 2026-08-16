@extends('layouts.admin')

@section('page_title', 'Admin Profile Settings')

@section('content')

<div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6 text-xs max-w-2xl">
    <div>
        <h3 class="text-base font-bold text-slate-900 uppercase">Administrator Account Profile</h3>
        <p class="text-slate-500 mt-0.5">Update admin username, email address, and security password.</p>
    </div>

    <form method="POST" action="/admin-panel/settings" class="space-y-4">
        @csrf
        <div>
            <label class="block font-bold text-slate-700 mb-1">Admin Username</label>
            <input type="text" name="admin_username" value="admin" class="w-full border rounded-xl px-3.5 py-2.5 outline-none font-mono">
        </div>
        <div>
            <label class="block font-bold text-slate-700 mb-1">Admin Email</label>
            <input type="email" name="admin_email" value="admin@onlinebdmart.com" class="w-full border rounded-xl px-3.5 py-2.5 outline-none">
        </div>
        <div>
            <label class="block font-bold text-slate-700 mb-1">Change Password</label>
            <input type="password" placeholder="Enter new password" class="w-full border rounded-xl px-3.5 py-2.5 outline-none">
        </div>
        <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl shadow">Save Profile Changes</button>
    </form>
</div>

@endsection
