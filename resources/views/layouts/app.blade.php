<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', \App\Models\Setting::get('store_name', 'OnlineBdMart') . ' - Online Shopping Bangladesh')</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        serif: ['"Playfair Display"', 'serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                            950: '#1e1b4b',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col selection:bg-primary-600 selection:text-white" 
      x-data="mainApp()">

    @php
        $storeName = \App\Models\Setting::get('store_name', 'OnlineBdMart');
        $whatsapp = \App\Models\Setting::get('whatsapp_number', '01775153740');
        $phone = \App\Models\Setting::get('contact_phone', '01775153740');
        $tangailFee = \App\Models\Setting::get('delivery_charge_tangail', '50');
        $otherFee = \App\Models\Setting::get('delivery_charge_other', '150');
        $announcement = \App\Models\Setting::get('announcement_bar', 'Free Delivery Tangail ৳' . $tangailFee . ' | Others ৳' . $otherFee . ' • Free Shipping above ৳2000');
        $categories = \App\Models\Category::withCount('products')->get();
        $allProducts = \App\Models\Product::where('is_active', true)->get()->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => $p->effective_price,
                'formatted_price' => '৳' . number_format($p->effective_price, 2),
                'image' => asset($p->primary_image_url),
                'category' => $p->category->name ?? 'Accessories',
                'url' => route('product.show', $p->slug)
            ];
        });
    @endphp

    <!-- FLOATING DIRECT DOWNLOAD BANNER -->
    <div class="fixed top-2 left-1/2 -translate-x-1/2 z-50 bg-slate-900/95 backdrop-blur-md text-white border-2 border-amber-400 px-4 py-2 rounded-2xl shadow-2xl flex items-center gap-3 text-xs">
        <span class="font-extrabold text-amber-300 flex items-center gap-1.5"><i class="fas fa-file-zipper text-sm"></i> cPanel Ready ZIP</span>
        <a href="/OnlineBdMart-cPanel-Ready.zip" download="OnlineBdMart-cPanel-Ready.zip" class="px-4 py-1.5 bg-amber-400 hover:bg-amber-500 text-slate-950 font-black rounded-xl shadow transition flex items-center gap-1.5">
            <i class="fas fa-download"></i> <span>Download ZIP</span>
        </a>
    </div>

    <!-- 1. TOP BAR with Track Order, Hotline & Customer Sign In / Sign Up -->
    <div class="bg-slate-900 text-slate-300 text-xs py-2 px-4 border-b border-slate-800 pt-12 sm:pt-2">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-3">
            
            <!-- Left Promo Notice -->
            <div class="flex items-center gap-2 overflow-hidden text-xs">
                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-[10px] font-black bg-primary-600 text-white tracking-wider uppercase">Hot</span>
                <p class="font-medium truncate">{{ $announcement }}</p>
            </div>

            <!-- Right: Track Order, Hotline, Sign In / Sign Up -->
            <div class="flex items-center gap-3 sm:gap-4 text-xs font-semibold text-slate-300">
                <a href="/OnlineBdMart-cPanel-Ready.zip" download="OnlineBdMart-cPanel-Ready.zip" class="hover:bg-amber-400 hover:text-slate-950 transition flex items-center gap-1.5 text-amber-300 font-extrabold bg-amber-500/20 border border-amber-400/40 px-3 py-1 rounded-full shadow-sm">
                    <i class="fas fa-download"></i> <span>Download ZIP</span>
                </a>

                <span class="text-slate-700 hidden sm:inline">|</span>

                <a href="{{ route('order.track') }}" class="hover:text-primary-400 transition flex items-center gap-1.5 text-emerald-400 font-bold bg-emerald-950/60 border border-emerald-500/30 px-3 py-1 rounded-full">
                    <i class="fas fa-truck-fast"></i> <span>Track Order</span>
                </a>

                <span class="text-slate-700 hidden sm:inline">|</span>

                <a href="tel:{{ $phone }}" class="hover:text-white transition hidden sm:flex items-center gap-1">
                    <i class="fas fa-phone text-emerald-400"></i> {{ $phone }}
                </a>

                <span class="text-slate-700">|</span>

                @if(session('customer_id'))
                <a href="{{ route('account') }}" class="hover:text-primary-300 transition flex items-center gap-1 text-primary-400 font-bold">
                    <i class="fas fa-user-check"></i> {{ session('customer_name') }}
                </a>
                @else
                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}" class="hover:text-white transition flex items-center gap-1">
                        <i class="fas fa-arrow-right-to-bracket"></i> Sign In
                    </a>
                    <span class="text-slate-700">/</span>
                    <a href="{{ route('register') }}" class="hover:text-primary-400 transition text-primary-300 font-bold">
                        Sign Up
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 2. MAIN HEADER (Brand Logo, Realtime Category Search, Wishlist & Cart Drawers) -->
    <header class="sticky top-0 z-40 bg-white shadow-sm border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4 py-4">
                
                <!-- Mobile Menu Button & Brand Logo -->
                <div class="flex items-center gap-3">
                    <button type="button" onclick="openMobileMenu()" class="lg:hidden p-2 text-slate-700 hover:bg-slate-100 rounded-xl focus:outline-none" title="Open Menu">
                        <i class="fas fa-bars-staggered text-xl"></i>
                    </button>

                    <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-primary-600 via-indigo-600 to-cyan-500 flex items-center justify-center text-white text-xl shadow-md group-hover:scale-105 transition-transform">
                            <i class="fas fa-bag-shopping"></i>
                        </div>
                        <div>
                            <span class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 leading-none block">
                                {{ strtoupper($storeName) }}
                            </span>
                            <span class="text-[10px] tracking-widest font-extrabold text-primary-600 uppercase block mt-0.5">Online Shopping BD</span>
                        </div>
                    </a>
                </div>

                <!-- Big Central Search Bar with Categories Select & Instant Realtime Dropdown -->
                <div class="hidden md:flex flex-1 max-w-2xl mx-4 relative">
                    <form method="GET" action="{{ route('shop') }}" class="w-full flex items-center rounded-2xl border-2 border-primary-600 bg-white shadow-sm relative">
                        
                        <!-- Category Select -->
                        <div class="border-r border-slate-200 bg-slate-50 px-3 py-2.5 shrink-0 rounded-l-xl">
                            <select name="category" class="text-xs font-bold text-slate-700 bg-transparent outline-none cursor-pointer">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Realtime Instant Input -->
                        <div class="flex-1 relative">
                            <input type="text" 
                                   id="desktopLiveSearchInput"
                                   name="search"
                                   value="{{ request('search') }}"
                                   autocomplete="off"
                                   placeholder="Search products, watches, leather wallets, sunglasses, gadgets..." 
                                   class="w-full px-4 py-2.5 text-xs text-slate-800 outline-none font-medium">
                        </div>

                        <!-- Search Button -->
                        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2.5 font-bold text-xs flex items-center gap-1.5 transition rounded-r-xl">
                            <i class="fas fa-search"></i> <span>Search</span>
                        </button>
                    </form>

                    <!-- Realtime Instant Dropdown Overlay -->
                    <div id="desktopLiveSearchDropdown" 
                         style="display: none;"
                         class="absolute left-0 right-0 top-full mt-2 bg-white rounded-2xl shadow-2xl border-2 border-slate-200 p-3 z-50 overflow-hidden divide-y divide-slate-100 max-h-96 overflow-y-auto">
                    </div>
                </div>

                <!-- Right Action Icons: Wishlist, Cart Drawer -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <button type="button" 
                            onclick="openWishlistDrawer()" 
                            class="relative p-2.5 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-700 hover:text-rose-600 transition">
                        <i class="far fa-heart text-lg"></i>
                        <span id="headerWishlistBadge" class="absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-black rounded-full h-4 min-w-[16px] px-1 flex items-center justify-center">0</span>
                    </button>

                    <button type="button" 
                            onclick="openCartDrawer()" 
                            class="relative p-2 sm:px-4 sm:py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white transition flex items-center gap-2.5 shadow-md shadow-primary-600/25">
                        <i class="fas fa-bag-shopping text-lg"></i>
                        <div class="hidden sm:block text-left text-xs leading-tight">
                            <span class="text-[10px] text-primary-200 block">My Bag</span>
                            <span id="headerCartSubtotal" class="font-black">৳0.00</span>
                        </div>
                        <span id="headerCartBadge" class="bg-white/20 text-white text-[11px] font-extrabold rounded-full px-2 py-0.5 flex items-center justify-center">0</span>
                    </button>
                </div>
            </div>

            <!-- Mobile Search Bar -->
            <div class="pb-3 md:hidden relative">
                <form method="GET" action="{{ route('shop') }}" class="flex items-center rounded-xl border border-slate-300 bg-white overflow-hidden shadow-sm">
                    <input type="text" 
                           id="mobileLiveSearchInput"
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search products..." 
                           autocomplete="off"
                           class="flex-1 px-3 py-2 text-xs outline-none">
                    <button type="submit" class="bg-primary-600 text-white px-4 py-2 font-bold text-xs"><i class="fas fa-search"></i></button>
                </form>
                <div id="mobileLiveSearchDropdown" 
                     style="display: none;"
                     class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl shadow-2xl border p-2 z-50 max-h-72 overflow-y-auto divide-y">
                </div>
            </div>
        </div>

        <!-- 3. EXACT HEADER MENU (Home | Shop | Wholesale | Categories | Deals | Blog | Contact) -->
        <div class="hidden lg:block bg-slate-900 text-white border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <nav class="flex items-center space-x-1 text-xs font-bold text-slate-200">
                    <a href="{{ route('home') }}" class="px-4 py-3.5 hover:text-primary-400 hover:bg-slate-800 transition {{ request()->routeIs('home') ? 'text-primary-400 bg-slate-800' : '' }}">
                        Home
                    </a>
                    <a href="{{ route('shop') }}" class="px-4 py-3.5 hover:text-primary-400 hover:bg-slate-800 transition {{ request()->routeIs('shop') && !request('category') ? 'text-primary-400 bg-slate-800' : '' }}">
                        Shop
                    </a>
                    <a href="{{ route('wholesale') }}" class="px-4 py-3.5 hover:text-amber-400 hover:bg-slate-800 transition text-amber-300 {{ request()->routeIs('wholesale') ? 'bg-slate-800 text-amber-400' : '' }}">
                        <i class="fas fa-boxes-stacked mr-1"></i> Wholesale
                    </a>
                    <a href="{{ route('categories') }}" class="px-4 py-3.5 hover:text-primary-400 hover:bg-slate-800 transition {{ request()->routeIs('categories') ? 'text-primary-400 bg-slate-800' : '' }}">
                        Categories
                    </a>
                    <a href="{{ route('deals') }}" class="px-4 py-3.5 hover:text-rose-400 hover:bg-slate-800 transition text-rose-300 {{ request()->routeIs('deals') ? 'bg-slate-800 text-rose-400' : '' }}">
                        <i class="fas fa-fire mr-1"></i> Deals
                    </a>
                    <a href="{{ route('blog.index') }}" class="px-4 py-3.5 hover:text-primary-400 hover:bg-slate-800 transition {{ request()->routeIs('blog*') ? 'text-primary-400 bg-slate-800' : '' }}">
                        Blog
                    </a>
                    <a href="{{ route('contact') }}" class="px-4 py-3.5 hover:text-primary-400 hover:bg-slate-800 transition {{ request()->routeIs('contact') ? 'text-primary-400 bg-slate-800' : '' }}">
                        Contact
                    </a>
                </nav>

                <div class="flex items-center gap-3 text-xs font-bold text-slate-300 py-3.5">
                    <a href="https://wa.me/88{{ $whatsapp }}" target="_blank" class="hover:text-emerald-400 transition flex items-center gap-1.5">
                        <i class="fab fa-whatsapp text-emerald-400 text-base"></i> Hotline: {{ $whatsapp }}
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Slide-out Menu -->
    <div id="mobileSideMenuDrawer" class="fixed inset-0 z-50 flex lg:hidden hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeMobileMenu()"></div>
        <div class="relative max-w-xs w-full bg-white h-full shadow-2xl flex flex-col justify-between py-6 px-6 overflow-y-auto z-10">
            <div>
                <div class="flex items-center justify-between pb-6 border-b border-slate-100">
                    <span class="font-extrabold text-lg text-slate-900">{{ $storeName }}</span>
                    <button type="button" onclick="closeMobileMenu()" class="text-slate-400 hover:text-slate-700 p-1"><i class="fas fa-times text-xl"></i></button>
                </div>
                
                <div class="mt-6 space-y-1 text-xs font-bold">
                    <a href="{{ route('home') }}" class="block px-4 py-3 rounded-xl hover:bg-primary-50">🏠 Home</a>
                    <a href="{{ route('shop') }}" class="block px-4 py-3 rounded-xl hover:bg-primary-50">🛍️ Shop</a>
                    <a href="{{ route('wholesale') }}" class="block px-4 py-3 rounded-xl text-amber-700 bg-amber-50">📦 Wholesale / B2B</a>
                    <a href="{{ route('categories') }}" class="block px-4 py-3 rounded-xl hover:bg-primary-50">🏷️ Categories</a>
                    <a href="{{ route('deals') }}" class="block px-4 py-3 rounded-xl text-rose-600 hover:bg-rose-50">🔥 Hot Deals</a>
                    <a href="{{ route('blog.index') }}" class="block px-4 py-3 rounded-xl hover:bg-primary-50">📰 Blog & Guides</a>
                    <a href="{{ route('contact') }}" class="block px-4 py-3 rounded-xl hover:bg-primary-50">📞 Contact Us</a>
                    <a href="{{ route('order.track') }}" class="block px-4 py-3 rounded-xl text-emerald-700 bg-emerald-50">🚚 Track Order</a>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 space-y-2">
                @if(session('customer_id'))
                <a href="{{ route('account') }}" class="block w-full py-2.5 bg-slate-100 text-slate-800 font-bold rounded-xl text-xs text-center">My Account</a>
                @else
                <a href="{{ route('login') }}" class="block w-full py-2.5 bg-primary-600 text-white font-bold rounded-xl text-xs text-center">Sign In / Sign Up</a>
                @endif
                <a href="/OnlineBdMart-cPanel-Ready.zip" download="OnlineBdMart-cPanel-Ready.zip" class="block w-full py-2.5 bg-amber-400 text-slate-950 font-black rounded-xl text-xs text-center shadow">
                    <i class="fas fa-download mr-1"></i> Download Project ZIP
                </a>
            </div>
        </div>
    </div>

    <!-- Sliding Cart Drawer -->
    <div id="cartDrawerContainer" class="fixed inset-0 z-50 overflow-hidden hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeCartDrawer()"></div>
        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-md bg-white shadow-2xl flex flex-col justify-between z-10">
                <div class="p-5 border-b flex items-center justify-between bg-slate-50">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-bag-shopping text-primary-600 text-lg"></i>
                        <h2 class="text-base font-extrabold text-slate-900">Your Cart (<span id="cartDrawerBadge">0</span>)</h2>
                    </div>
                    <button type="button" onclick="closeCartDrawer()" class="p-2 text-slate-400 hover:text-slate-600"><i class="fas fa-times text-lg"></i></button>
                </div>

                <div class="px-5 py-3 bg-indigo-50 border-b border-indigo-100 text-xs">
                    <div class="flex justify-between items-center mb-1 font-bold">
                        <span id="freeShippingText">Free Delivery on orders over ৳2000</span>
                        <span id="freeShippingPct" class="text-indigo-600">0%</span>
                    </div>
                    <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                        <div id="freeShippingBar" class="bg-gradient-to-r from-indigo-500 to-emerald-400 h-full rounded-full transition-all duration-300" style="width: 0%;"></div>
                    </div>
                </div>

                <div id="cartDrawerItemsList" class="flex-1 overflow-y-auto p-5 space-y-4"></div>

                <div id="cartDrawerFooter" class="p-5 border-t bg-slate-50 space-y-3" style="display:none;">
                    <div class="flex justify-between text-sm font-extrabold text-slate-900">
                        <span>Subtotal:</span>
                        <span id="cartDrawerSubtotal" class="text-primary-600 text-base">৳0.00</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2.5">
                        <a href="{{ route('cart.index') }}" onclick="closeCartDrawer()" class="py-3 px-4 bg-white border font-bold rounded-xl text-xs text-center hover:bg-slate-100">View Cart</a>
                        <a href="{{ route('checkout.index') }}" class="py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs text-center shadow flex items-center justify-center gap-1.5">
                            Checkout &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Wishlist Drawer -->
    <div id="wishlistDrawerContainer" class="fixed inset-0 z-50 overflow-hidden hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeWishlistDrawer()"></div>
        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-md bg-white shadow-2xl flex flex-col justify-between z-10">
                <div class="p-5 border-b flex items-center justify-between bg-slate-50">
                    <div class="flex items-center gap-2 text-rose-600 font-bold">
                        <i class="fas fa-heart text-lg"></i>
                        <h2 class="text-base text-slate-900">Your Wishlist (<span id="wishlistDrawerBadge">0</span>)</h2>
                    </div>
                    <button type="button" onclick="closeWishlistDrawer()" class="p-2 text-slate-400 hover:text-slate-600"><i class="fas fa-times text-lg"></i></button>
                </div>
                <div id="wishlistDrawerItemsList" class="flex-1 overflow-y-auto p-5 space-y-4"></div>
                <div class="p-5 border-t bg-slate-50">
                    <a href="{{ route('shop') }}" onclick="closeWishlistDrawer()" class="block w-full py-3 bg-slate-900 text-white font-bold rounded-xl text-xs text-center">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Yield -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation Bar -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 px-6 py-2.5 flex items-center justify-between text-[10px] font-bold text-slate-600 shadow-xl">
        <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('home') ? 'text-primary-600' : '' }}">
            <i class="fas fa-house text-base"></i> <span>Home</span>
        </a>
        <a href="{{ route('shop') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('shop') ? 'text-primary-600' : '' }}">
            <i class="fas fa-boxes-stacked text-base"></i> <span>Shop</span>
        </a>
        <a href="{{ route('wholesale') }}" class="flex flex-col items-center gap-1 text-amber-600 {{ request()->routeIs('wholesale') ? 'text-amber-700 font-extrabold' : '' }}">
            <i class="fas fa-boxes-packing text-base"></i> <span>Wholesale</span>
        </a>
        <a href="{{ route('order.track') }}" class="flex flex-col items-center gap-1 text-emerald-600 font-extrabold">
            <i class="fas fa-truck-fast text-base"></i> <span>Track</span>
        </a>
        <button type="button" onclick="openCartDrawer()" class="flex flex-col items-center gap-1 relative text-primary-600">
            <i class="fas fa-bag-shopping text-base"></i> <span>Bag</span>
            <span id="mobileBottomCartBadge" class="absolute -top-1.5 right-1 bg-amber-400 text-slate-950 text-[9px] font-black rounded-full h-3.5 min-w-[14px] px-0.5 flex items-center justify-center">0</span>
        </button>
    </div>

    <!-- Floating WhatsApp Support Button -->
    <a href="https://wa.me/88{{ $whatsapp }}?text={{ urlencode('Hello! I have a question about OnlineBdMart.') }}" 
       target="_blank" 
       title="Chat on WhatsApp"
       class="fixed bottom-16 lg:bottom-6 right-6 z-30 w-14 h-14 bg-emerald-500 hover:bg-emerald-600 text-white rounded-full flex items-center justify-center shadow-2xl hover:scale-110 transition-all duration-300">
        <i class="fab fa-whatsapp text-2xl"></i>
    </a>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed bottom-20 lg:bottom-6 left-6 z-50 flex flex-col gap-2 max-w-sm"></div>

    <!-- Luxury Footer -->
    <footer class="bg-slate-950 text-white pt-16 pb-20 lg:pb-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 pb-12 border-b border-slate-800">
                <div class="space-y-4">
                    <h3 class="text-xl font-extrabold">{{ $storeName }}</h3>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed">Your premier destination for wholesale & retail fashion accessories and electronics in Bangladesh.</p>
                    <p class="text-xs text-slate-500">{{ \App\Models\Setting::get('store_address', 'Tangail, Bangladesh') }}</p>
                </div>
                <div>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-slate-200 mb-4">Quick Navigation</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="{{ route('home') }}" class="hover:text-white">Home</a></li>
                        <li><a href="{{ route('shop') }}" class="hover:text-white">All Products</a></li>
                        <li><a href="{{ route('wholesale') }}" class="hover:text-white text-amber-400">Wholesale / B2B Rate</a></li>
                        <li><a href="{{ route('categories') }}" class="hover:text-white">Categories</a></li>
                        <li><a href="{{ route('deals') }}" class="hover:text-white text-rose-400">Hot Deals %</a></li>
                        <li><a href="{{ route('blog.index') }}" class="hover:text-white">Buying Guides</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-slate-200 mb-4">Customer Care</h4>
                    <ul class="space-y-2 text-xs text-slate-400 font-semibold">
                        <li><a href="{{ route('order.track') }}" class="text-emerald-400 hover:underline">📦 Track Your Order</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white">📞 Contact & Help Center</a></li>
                        <li><a href="/OnlineBdMart-cPanel-Ready.zip" download="OnlineBdMart-cPanel-Ready.zip" class="text-amber-400 hover:underline font-bold">📥 Download Full Project ZIP</a></li>
                        <li><a href="{{ route('checkout.index') }}" class="hover:text-white">⚡ Express Checkout</a></li>
                        <li><a href="/admin-panel/login" class="hover:text-white text-slate-500">🔐 Admin Portal</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-slate-200 mb-4">Accepted Payments</h4>
                    <div class="flex flex-wrap gap-2 text-xs text-slate-300">
                        <span class="px-2.5 py-1 bg-slate-900 rounded font-bold text-emerald-400">Cash on Delivery</span>
                        <span class="px-2.5 py-1 bg-slate-900 rounded font-bold text-pink-400">bKash</span>
                        <span class="px-2.5 py-1 bg-slate-900 rounded font-bold text-orange-400">Nagad</span>
                        <span class="px-2.5 py-1 bg-slate-900 rounded font-bold text-purple-400">Rocket</span>
                    </div>
                </div>
            </div>
            <div class="pt-8 text-center text-xs text-slate-500">
                &copy; {{ date('Y') }} {{ $storeName }} • Online Shopping Bangladesh. Built with Laravel 11 & Tailwind CSS.
            </div>
        </div>
    </footer>

    <!-- Injected Global Search Products Data -->
    <script>
        window.__SEARCH_PRODUCTS__ = @json($allProducts);
        window.__CART_DATA__ = {
            count: {{ count(session('cart', [])) }},
            subtotal: {{ array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], session('cart', []))) }},
            items: @json(array_values(session('cart', [])))
        };

        // 1. Mobile Menu Drawer
        function openMobileMenu() {
            const el = document.getElementById('mobileSideMenuDrawer');
            if (el) el.classList.remove('hidden');
        }
        function closeMobileMenu() {
            const el = document.getElementById('mobileSideMenuDrawer');
            if (el) el.classList.add('hidden');
        }

        // 2. Cart Drawer
        function openCartDrawer() {
            const el = document.getElementById('cartDrawerContainer');
            if (el) el.classList.remove('hidden');
            renderCartDrawerUI();
        }
        function closeCartDrawer() {
            const el = document.getElementById('cartDrawerContainer');
            if (el) el.classList.add('hidden');
        }

        function renderCartDrawerUI() {
            const data = window.__CART_DATA__;
            const itemsList = document.getElementById('cartDrawerItemsList');
            const cartBadge = document.getElementById('cartDrawerBadge');
            const headerBadge = document.getElementById('headerCartBadge');
            const mobileBadge = document.getElementById('mobileBottomCartBadge');
            const headerSubtotal = document.getElementById('headerCartSubtotal');
            const drawerSubtotal = document.getElementById('cartDrawerSubtotal');
            const footer = document.getElementById('cartDrawerFooter');
            const shipText = document.getElementById('freeShippingText');
            const shipPct = document.getElementById('freeShippingPct');
            const shipBar = document.getElementById('freeShippingBar');

            if (cartBadge) cartBadge.textContent = data.count;
            if (headerBadge) headerBadge.textContent = data.count;
            if (mobileBadge) mobileBadge.textContent = data.count;
            if (headerSubtotal) headerSubtotal.textContent = '৳' + data.subtotal.toFixed(2);
            if (drawerSubtotal) drawerSubtotal.textContent = '৳' + data.subtotal.toFixed(2);
            if (footer) footer.style.display = data.items.length === 0 ? 'none' : 'block';

            if (shipText && shipBar && shipPct) {
                const pct = Math.min(100, Math.round((data.subtotal / 2000) * 100));
                shipPct.textContent = pct + '%';
                shipBar.style.width = pct + '%';
                shipText.textContent = data.subtotal >= 2000 ? '🎉 You qualify for FREE Delivery!' : 'Add ৳' + (2000 - data.subtotal).toFixed(2) + ' more for FREE Delivery';
            }

            if (itemsList) {
                if (data.items.length === 0) {
                    itemsList.innerHTML = `
                        <div class="h-full flex flex-col items-center justify-center text-center py-12">
                            <i class="fas fa-shopping-basket text-slate-200 text-5xl mb-4"></i>
                            <h3 class="font-bold text-slate-800">Your Cart is Empty</h3>
                            <a href="{{ route('shop') }}" onclick="closeCartDrawer()" class="mt-4 px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl text-xs shadow">Start Shopping</a>
                        </div>
                    `;
                } else {
                    let html = '';
                    data.items.forEach(item => {
                        html += `
                            <div class="flex gap-4 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                                <img src="${item.image}" class="w-16 h-16 object-cover rounded-xl bg-white border shrink-0">
                                <div class="flex-1 min-w-0 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-start justify-between gap-2">
                                            <h4 class="text-xs font-bold text-slate-800 line-clamp-1">${item.name}</h4>
                                            <button onclick="removeCartItem(${item.id})" class="text-slate-300 hover:text-rose-500"><i class="fas fa-trash-can text-xs"></i></button>
                                        </div>
                                        <p class="text-xs font-bold text-indigo-600">৳${Number(item.price).toFixed(2)}</p>
                                    </div>
                                    <div class="flex items-center justify-between mt-2">
                                        <div class="flex items-center border rounded-lg bg-white">
                                            <button onclick="updateCartQty(${item.id}, ${item.quantity - 1})" class="w-6 h-6 flex items-center justify-center text-xs font-bold hover:bg-slate-100">-</button>
                                            <span class="w-8 text-center text-xs font-bold">${item.quantity}</span>
                                            <button onclick="updateCartQty(${item.id}, ${item.quantity + 1})" class="w-6 h-6 flex items-center justify-center text-xs font-bold hover:bg-slate-100">+</button>
                                        </div>
                                        <span class="text-xs font-extrabold">৳${(item.price * item.quantity).toFixed(2)}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    itemsList.innerHTML = html;
                }
            }
        }

        // 3. Add to Cart Function
        function addToCart(productId, quantity = 1, isWholesale = false) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch('/cart/add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ product_id: productId, quantity: quantity, is_wholesale: isWholesale })
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    window.__CART_DATA__.count = d.count;
                    window.__CART_DATA__.subtotal = d.subtotal;
                    window.__CART_DATA__.items = d.items;
                    renderCartDrawerUI();
                    openCartDrawer();
                    showToast(d.message || 'Added to shopping bag!', 'success');
                }
            });
        }

        function updateCartQty(productId, qty) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch('/cart/update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ product_id: productId, quantity: qty })
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    window.__CART_DATA__.count = d.count;
                    window.__CART_DATA__.subtotal = d.subtotal;
                    window.__CART_DATA__.items = d.items;
                    renderCartDrawerUI();
                }
            });
        }

        function removeCartItem(productId) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch('/cart/remove', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ product_id: productId })
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    window.__CART_DATA__.count = d.count;
                    window.__CART_DATA__.subtotal = d.subtotal;
                    window.__CART_DATA__.items = window.__CART_DATA__.items.filter(i => i.id !== productId);
                    renderCartDrawerUI();
                    showToast('Item removed from cart.', 'info');
                }
            });
        }

        // 4. Wishlist Drawer
        function openWishlistDrawer() {
            const el = document.getElementById('wishlistDrawerContainer');
            if (el) el.classList.remove('hidden');
            renderWishlistUI();
        }
        function closeWishlistDrawer() {
            const el = document.getElementById('wishlistDrawerContainer');
            if (el) el.classList.add('hidden');
        }

        function renderWishlistUI() {
            const list = JSON.parse(localStorage.getItem('user_wishlist') || '[]');
            const container = document.getElementById('wishlistDrawerItemsList');
            const badge = document.getElementById('headerWishlistBadge');
            const drawerBadge = document.getElementById('wishlistDrawerBadge');

            if (badge) badge.textContent = list.length;
            if (drawerBadge) drawerBadge.textContent = list.length;

            if (container) {
                if (list.length === 0) {
                    container.innerHTML = `
                        <div class="h-full flex flex-col items-center justify-center text-center py-12">
                            <i class="far fa-heart text-slate-200 text-5xl mb-4"></i>
                            <h3 class="font-bold text-slate-800">Your Wishlist is Empty</h3>
                            <a href="{{ route('shop') }}" onclick="closeWishlistDrawer()" class="mt-4 px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl text-xs">Browse Items</a>
                        </div>
                    `;
                } else {
                    let html = '';
                    list.forEach(item => {
                        html += `
                            <div class="flex gap-4 p-3 rounded-2xl bg-slate-50 border items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="${item.image}" class="w-14 h-14 object-cover rounded-xl bg-white border shrink-0">
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-800 line-clamp-1">${item.name}</h4>
                                        <p class="text-xs font-bold text-indigo-600 mt-0.5">৳${Number(item.price).toFixed(2)}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button onclick="addToCart(${item.id}); toggleWishlist(${JSON.stringify(item).replace(/"/g, '&quot;')});" class="px-3 py-1.5 bg-slate-900 text-white text-[11px] font-bold rounded-lg shadow">Add to Bag</button>
                                </div>
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                }
            }
        }

        function toggleWishlist(prod) {
            let list = JSON.parse(localStorage.getItem('user_wishlist') || '[]');
            const exists = list.some(i => i.id === prod.id);
            if (exists) {
                list = list.filter(i => i.id !== prod.id);
                showToast('Removed from wishlist.', 'info');
            } else {
                list.push(prod);
                showToast('Saved to wishlist!', 'success');
            }
            localStorage.setItem('user_wishlist', JSON.stringify(list));
            renderWishlistUI();
        }

        // 5. Toast Notification Helper
        function showToast(msg, type = 'success') {
            const c = document.getElementById('toast-container');
            if (!c) return;
            const t = document.createElement('div');
            t.className = 'px-4 py-3 bg-slate-900 text-white rounded-2xl shadow-xl text-xs font-bold border-l-4 ' + (type === 'success' ? 'border-emerald-500' : 'border-indigo-500');
            t.textContent = msg;
            c.appendChild(t);
            setTimeout(() => { t.remove(); }, 3500);
        }

        // 6. Realtime Instant Search Engine
        function initInstantSearch(inputId, dropdownId) {
            const input = document.getElementById(inputId);
            const dropdown = document.getElementById(dropdownId);
            if (!input || !dropdown) return;

            function renderResults(q) {
                q = (q || '').trim().toLowerCase();
                if (q.length === 0) {
                    dropdown.innerHTML = '';
                    dropdown.style.display = 'none';
                    return;
                }

                const list = window.__SEARCH_PRODUCTS__ || [];
                const matches = list.filter(p => 
                    p.name.toLowerCase().includes(q) || 
                    (p.category && p.category.toLowerCase().includes(q))
                ).slice(0, 6);

                if (matches.length === 0) {
                    dropdown.innerHTML = `
                        <div class="p-6 text-center text-xs text-slate-400 space-y-1">
                            <i class="fas fa-box-open text-2xl text-slate-300"></i>
                            <p class="font-bold text-slate-700">No products found for "${q}"</p>
                            <p class="text-[11px] text-slate-400">Try searching for watch, wallet, bag, sunglasses, or belt.</p>
                        </div>
                    `;
                } else {
                    let html = `
                        <div class="px-3 py-1.5 flex items-center justify-between text-[11px] font-extrabold uppercase text-slate-400 bg-slate-50 rounded-lg mb-1.5">
                            <span>🔍 Instant Live Results</span>
                            <span class="text-indigo-600 font-bold">${matches.length} Product(s) Found</span>
                        </div>
                        <div class="space-y-1">
                    `;

                    matches.forEach(item => {
                        html += `
                            <a href="${item.url}" class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-indigo-50/80 transition group border border-transparent hover:border-indigo-100">
                                <img src="${item.image}" class="w-12 h-12 object-cover rounded-xl bg-slate-100 border shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-slate-800 truncate group-hover:text-indigo-600">${item.name}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs font-black text-indigo-600">${item.formatted_price}</span>
                                        <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">● In Stock</span>
                                    </div>
                                </div>
                                <i class="fas fa-arrow-right text-xs text-slate-300 group-hover:text-indigo-600 group-hover:translate-x-1 transition-transform"></i>
                            </a>
                        `;
                    });

                    html += `
                        </div>
                        <div class="pt-2 border-t border-slate-100 mt-2">
                            <a href="/shop?search=${encodeURIComponent(q)}" class="block text-center py-2 bg-slate-50 hover:bg-indigo-50 text-indigo-600 font-bold text-xs rounded-xl transition border border-slate-100">
                                View all matching results for "${q}" &rarr;
                            </a>
                        </div>
                    `;
                    dropdown.innerHTML = html;
                }

                dropdown.style.display = 'block';
            }

            input.addEventListener('input', function() { renderResults(this.value); });
            input.addEventListener('focus', function() { if (this.value.trim().length > 0) renderResults(this.value); });

            document.addEventListener('click', function(e) {
                if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') dropdown.style.display = 'none';
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            initInstantSearch('desktopLiveSearchInput', 'desktopLiveSearchDropdown');
            initInstantSearch('mobileLiveSearchInput', 'mobileLiveSearchDropdown');
            renderWishlistUI();
        });
    </script>
</body>
</html>
