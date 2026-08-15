@extends('layouts.admin')

@section('page_title', 'Brand Colors & Theme Accent')

@section('content')

<div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6 text-xs max-w-2xl">
    <div>
        <h3 class="text-base font-bold text-slate-900 uppercase">Theme Colors & Visual Accents</h3>
        <p class="text-slate-500 mt-0.5">Customize primary brand color scheme, buttons and highlight tints.</p>
    </div>

    <form method="POST" action="/admin-panel/settings" class="space-y-4">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Primary Brand Color</label>
                <div class="flex items-center gap-2">
                    <input type="color" value="#4f46e5" class="w-10 h-10 rounded-xl cursor-pointer">
                    <input type="text" value="#4f46e5" class="flex-1 border rounded-xl px-3 py-2 font-mono">
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Accent / Sale Color</label>
                <div class="flex items-center gap-2">
                    <input type="color" value="#f59e0b" class="w-10 h-10 rounded-xl cursor-pointer">
                    <input type="text" value="#f59e0b" class="flex-1 border rounded-xl px-3 py-2 font-mono">
                </div>
            </div>
        </div>
        <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl shadow">Save Color Palette</button>
    </form>
</div>

@endsection
