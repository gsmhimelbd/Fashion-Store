<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'Admin Dashboard') - {{ \App\Models\Setting::get('store_name', 'OnlineBdMart') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 9999px; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased min-h-screen" x-data="{ sidebarOpen: false }">

    @php
        $pendingOrdersCount = \App\Models\Order::where('status', 'pending')->count();
        $unreadMessagesCount = \App\Models\WholesaleInquiry::count();
        $storeName = \App\Models\Setting::get('store_name', 'OnlineBdMart');
    @endphp

    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar Backdrop for Mobile -->
        <div x-show="sidebarOpen" 
             x-cloak 
             @click="sidebarOpen = false" 
             class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"></div>

        <!-- Sidebar Navigation (Full 21 Feature Suite matching onlinebdmart.com/admin.html) -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-950 text-slate-300 flex flex-col justify-between lg:static lg:translate-x-0 transition-transform duration-300 ease-in-out border-r border-slate-800"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="flex-1 overflow-y-auto">
                
                <!-- Brand Header -->
                <div class="h-20 flex items-center justify-between px-6 border-b border-slate-800/80 sticky top-0 bg-slate-950/95 backdrop-blur z-10">
                    <a href="/admin-panel/dashboard" class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-500 to-cyan-400 flex items-center justify-center text-white font-bold shadow-md shadow-indigo-500/20">
                            <i class="fas fa-crown text-sm"></i>
                        </div>
                        <div>
                            <span class="text-sm font-extrabold text-white tracking-tight uppercase block leading-none">{{ $storeName }}</span>
                            <span class="text-[10px] text-indigo-400 font-bold tracking-widest uppercase">Admin Panel</span>
                        </div>
                    </a>
                    <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Complete 21-Item Menu -->
                <nav class="p-3 space-y-1 text-xs font-semibold">
                    
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 pt-2 pb-1.5">Core E-Commerce</p>

                    <!-- 1. Dashboard -->
                    <a href="/admin-panel/dashboard" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*dashboard*') || request()->path() === 'admin-panel' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">📊</span>
                        <span>Dashboard</span>
                    </a>

                    <!-- 2. Products -->
                    <a href="/admin-panel/products" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*products*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">📦</span>
                        <span>Products</span>
                    </a>

                    <!-- 3. Wholesale -->
                    <a href="/admin-panel/wholesale" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*wholesale*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">🏭</span>
                        <span>Wholesale / B2B</span>
                    </a>

                    <!-- 4. Orders -->
                    <a href="/admin-panel/orders" class="flex items-center justify-between px-3 py-2 rounded-xl transition {{ request()->is('*orders*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <span class="text-sm">🧾</span>
                            <span>Orders</span>
                        </div>
                        @if($pendingOrdersCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-400 text-slate-950">{{ $pendingOrdersCount }}</span>
                        @endif
                    </a>

                    <!-- 5. Banners / Slider -->
                    <a href="/admin-panel/banners" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*banners*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">🖼️</span>
                        <span>Banners / Slider</span>
                    </a>

                    <!-- 6. Categories -->
                    <a href="/admin-panel/categories" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*categories*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">📂</span>
                        <span>Categories</span>
                    </a>

                    <!-- 7. Coupons -->
                    <a href="/admin-panel/coupons" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*coupons*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">🎟️</span>
                        <span>Coupons / Deals</span>
                    </a>

                    <!-- 8. Blog -->
                    <a href="/admin-panel/blogs" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*blogs*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">📝</span>
                        <span>Blog & Guides</span>
                    </a>

                    <!-- 9. Reviews -->
                    <a href="/admin-panel/reviews" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*reviews*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">⭐</span>
                        <span>Customer Reviews</span>
                    </a>

                    <!-- 10. Messages -->
                    <a href="/admin-panel/messages" class="flex items-center justify-between px-3 py-2 rounded-xl transition {{ request()->is('*messages*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <span class="text-sm">💬</span>
                            <span>Messages</span>
                        </div>
                        @if($unreadMessagesCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-400 text-slate-950">{{ $unreadMessagesCount }}</span>
                        @endif
                    </a>

                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 pt-4 pb-1.5">Business & Operations</p>

                    <!-- 11. Payments -->
                    <a href="/admin-panel/payments" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*payments*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">💳</span>
                        <span>Payments Setup</span>
                    </a>

                    <!-- 12. Delivery -->
                    <a href="/admin-panel/delivery" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*delivery*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">🚚</span>
                        <span>Delivery & Courier</span>
                    </a>

                    <!-- 13. Customers -->
                    <a href="/admin-panel/customers" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*customers*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">👥</span>
                        <span>Customers</span>
                    </a>

                    <!-- 14. Suppliers -->
                    <a href="/admin-panel/suppliers" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*suppliers*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">🏭</span>
                        <span>Suppliers</span>
                    </a>

                    <!-- 15. Analytics -->
                    <a href="/admin-panel/analytics" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*analytics*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">📈</span>
                        <span>Sales Analytics</span>
                    </a>

                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 pt-4 pb-1.5">Integrations & Tech</p>

                    <!-- 16. WhatsApp -->
                    <a href="/admin-panel/whatsapp" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*whatsapp*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">🟢</span>
                        <span>WhatsApp Config</span>
                    </a>

                    <!-- 17. Telegram -->
                    <a href="/admin-panel/telegram" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*telegram*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">📨</span>
                        <span>Telegram Alerts</span>
                    </a>

                    <!-- 18. Facebook Pixel -->
                    <a href="/admin-panel/pixel" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*pixel*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">📘</span>
                        <span>Facebook Pixel</span>
                    </a>

                    <!-- 19. Colors & Theme -->
                    <a href="/admin-panel/colors" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*colors*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">🎨</span>
                        <span>Colors & Branding</span>
                    </a>

                    <!-- 20. OTP System -->
                    <a href="/admin-panel/otp" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*otp*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">🔑</span>
                        <span>OTP System</span>
                    </a>

                    <!-- 21. SEO Setup -->
                    <a href="/admin-panel/seo" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*seo*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">🔍</span>
                        <span>SEO & Meta</span>
                    </a>

                    <!-- 22. Site Settings -->
                    <a href="/admin-panel/settings" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*settings*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">⚙️</span>
                        <span>Site Settings</span>
                    </a>

                    <!-- 23. Admin Profile -->
                    <a href="/admin-panel/profile" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->is('*profile*') ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900 hover:text-white' }}">
                        <span class="text-sm">👤</span>
                        <span>Admin Profile</span>
                    </a>

                </nav>
            </div>

            <!-- Sidebar User Profile & Site Link -->
            <div class="p-4 border-t border-slate-800 space-y-2 bg-slate-950">
                <a href="/" target="_blank" class="flex items-center justify-center gap-2 w-full py-2 bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white font-semibold rounded-xl text-xs transition">
                    <i class="fas fa-arrow-up-right-from-square text-[11px]"></i> Visit Storefront
                </a>

                <div class="flex items-center justify-between pt-2 px-2">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-indigo-600/30 text-indigo-400 font-bold flex items-center justify-center text-xs border border-indigo-500/30">
                            A
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white leading-none">Admin</p>
                            <p class="text-[10px] text-emerald-400 mt-0.5">● Superadmin</p>
                        </div>
                    </div>
                    <a href="/admin-panel/logout" title="Logout" class="p-2 text-slate-400 hover:text-rose-400 transition">
                        <i class="fas fa-right-from-bracket text-sm"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Body -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <header class="h-20 bg-white border-b border-slate-200 px-4 sm:px-8 flex items-center justify-between shrink-0 shadow-sm">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100">
                        <i class="fas fa-bars-staggered text-lg"></i>
                    </button>
                    <h1 class="text-lg font-extrabold text-slate-900 tracking-tight">@yield('page_title', 'Admin Dashboard')</h1>
                </div>

                <div class="flex items-center gap-3">
                    <a href="/" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                        <i class="fas fa-store text-indigo-500"></i> View Store
                    </a>
                    <a href="/admin-panel/products" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-500/20 transition">
                        <i class="fas fa-plus"></i> Add Product
                    </a>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 sm:p-8 space-y-6">
                @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500">&times;</button>
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
