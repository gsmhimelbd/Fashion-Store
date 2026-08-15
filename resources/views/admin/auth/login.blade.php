<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - {{ \App\Models\Setting::get('store_name', 'OnlineBdMart') }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Background glowing circles -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-cyan-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 shadow-2xl relative z-10 space-y-6">
        
        <!-- Logo & Title -->
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-500 to-cyan-400 flex items-center justify-center text-white text-2xl mx-auto shadow-lg shadow-indigo-500/25">
                <i class="fas fa-crown"></i>
            </div>
            <h1 class="text-xl font-extrabold tracking-tight text-white uppercase">Admin Portal</h1>
            <p class="text-xs text-slate-400">{{ \App\Models\Setting::get('store_name', 'OnlineBdMart') }} Management</p>
        </div>

        <!-- Errors -->
        @if($errors->any())
        <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-semibold flex items-center gap-2">
            <i class="fas fa-exclamation-circle text-rose-400"></i>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-semibold flex items-center gap-2">
            <i class="fas fa-exclamation-circle text-rose-400"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if(session('success'))
        <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-semibold flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-400"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4" id="loginForm">
            @csrf
            <div>
                <label class="text-xs font-bold text-slate-300 block mb-1.5">Username or Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                        <i class="fas fa-user text-xs"></i>
                    </span>
                    <input type="text" name="username" id="usernameInput" required value="admin" class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-9 pr-4 py-2.5 text-xs text-white outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="text-xs font-bold text-slate-300 block mb-1.5">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                        <i class="fas fa-lock text-xs"></i>
                    </span>
                    <input type="password" name="password" id="passwordInput" required value="password" class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-9 pr-4 py-2.5 text-xs text-white outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>
            </div>

            <button type="submit" class="w-full py-3 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center justify-center gap-2">
                <i class="fas fa-right-to-bracket"></i> Sign In to Dashboard
            </button>
        </form>

        <!-- 1-Click Demo Fill Button -->
        <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-center space-y-1">
            <p class="text-[11px] text-slate-400 font-medium">Default Credentials: <strong class="text-indigo-400 font-mono">admin</strong> / <strong class="text-indigo-400 font-mono">password</strong></p>
            <button type="button" onclick="document.getElementById('usernameInput').value='admin';document.getElementById('passwordInput').value='password';" class="text-[11px] text-cyan-400 font-bold hover:underline">
                Auto-fill credentials
            </button>
        </div>

        <div class="text-center pt-2">
            <a href="{{ route('home') }}" class="text-xs text-slate-500 hover:text-slate-300 transition">
                &larr; Return to Customer Store
            </a>
        </div>

    </div>

</body>
</html>
