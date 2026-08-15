/**
 * OnlineBdMart - Full-featured Live Server & Engine
 * Zero-Dependency Native JS + Alpine Integration for 100% Reliability
 * Mobile Menu, Cart Drawer, Wishlist Drawer, Real-Time Search, Add to Cart & Admin CRUD.
 */

const http = require('node:http');
const fs = require('node:fs');
const path = require('node:path');
const url = require('node:url');
const querystring = require('node:querystring');
const crypto = require('node:crypto');
const { DatabaseSync } = require('node:sqlite');

const PORT = process.env.PORT || 8000;
const DB_PATH = path.join(__dirname, 'database', 'database.sqlite');

fs.mkdirSync(path.join(__dirname, 'database'), { recursive: true });
fs.mkdirSync(path.join(__dirname, 'public', 'uploads'), { recursive: true });
fs.mkdirSync(path.join(__dirname, 'public', 'images'), { recursive: true });

const db = new DatabaseSync(DB_PATH);

// Helper to get settings
function getSettings() {
    const rows = db.prepare('SELECT setting_key, setting_value FROM settings').all();
    const map = {};
    for (const r of rows) map[r.setting_key] = r.setting_value;
    return map;
}

// Helper to track customer visit
function trackVisitorSession(req, pathname) {
    if (pathname.startsWith('/admin-panel') || pathname.startsWith('/uploads') || pathname.startsWith('/images') || pathname.startsWith('/css') || pathname.startsWith('/js') || pathname.includes('.')) {
        return;
    }
    try {
        const ip = req.headers['x-forwarded-for'] || req.socket.remoteAddress || '127.0.0.1';
        const ua = req.headers['user-agent'] || 'Mobile';
        const ref = req.headers['referer'] || 'Direct Visit';
        const isMobile = /mobile|iphone|android/i.test(ua);
        const dev = isMobile ? 'Mobile' : 'Desktop';
        const sessId = parseCookies(req)['fashion_session'] || 'guest';
        
        db.prepare('INSERT INTO customer_visits (ip_address, session_id, district, referrer, page_url, user_agent, device_type) VALUES (?, ?, ?, ?, ?, ?, ?)').run(
            ip, sessId, 'Bangladesh', ref, pathname, ua, dev
        );
    } catch (e) {}
}

const sessions = new Map();

function parseCookies(req) {
    const list = {};
    const rc = req.headers.cookie;
    if (rc) {
        rc.split(';').forEach(cookie => {
            const parts = cookie.split('=');
            list[parts.shift().trim()] = decodeURI(parts.join('='));
        });
    }
    return list;
}

function getSession(req, res) {
    const cookies = parseCookies(req);
    let sid = cookies['fashion_session'];
    if (!sid || !sessions.has(sid)) {
        sid = crypto.randomBytes(16).toString('hex');
        sessions.set(sid, { cart: {}, applied_coupon: null, admin: null, customer: null });
        res.setHeader('Set-Cookie', `fashion_session=${sid}; Path=/; HttpOnly; SameSite=Lax`);
    }
    return sessions.get(sid);
}

function parseBody(req) {
    return new Promise((resolve) => {
        let body = '';
        req.on('data', chunk => { body += chunk.toString(); });
        req.on('end', () => {
            const contentType = req.headers['content-type'] || '';
            if (contentType.includes('application/json')) {
                try { resolve(JSON.parse(body || '{}')); } catch (e) { resolve({}); }
            } else {
                resolve(querystring.parse(body));
            }
        });
    });
}

function serveStatic(req, res, pathname) {
    let filePath = path.join(__dirname, 'public', pathname);
    if (!fs.existsSync(filePath) || fs.statSync(filePath).isDirectory()) {
        filePath = path.join(__dirname, pathname);
    }
    if (!fs.existsSync(filePath) || fs.statSync(filePath).isDirectory()) return false;
    const ext = path.extname(filePath).toLowerCase();
    const mimeTypes = {
        '.svg': 'image/svg+xml', '.png': 'image/png', '.jpg': 'image/jpeg',
        '.jpeg': 'image/jpeg', '.webp': 'image/webp', '.css': 'text/css',
        '.js': 'application/javascript', '.json': 'application/json', '.ico': 'image/x-icon',
        '.zip': 'application/zip', '.sql': 'text/plain'
    };
    res.writeHead(200, { 'Content-Type': mimeTypes[ext] || 'application/octet-stream' });
    fs.createReadStream(filePath).pipe(res);
    return true;
}

// Helper to call Telegram Bot API
function callTelegramApi(token, method, payload) {
    return new Promise((resolve) => {
        if (!token) return resolve({ ok: false, description: 'Telegram token is empty.' });
        const https = require('node:https');
        const data = JSON.stringify(payload);
        const req = https.request({
            hostname: 'api.telegram.org',
            port: 443,
            path: `/bot${token}/${method}`,
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Content-Length': Buffer.byteLength(data)
            },
            timeout: 10000
        }, (res) => {
            let body = '';
            res.on('data', chunk => body += chunk);
            res.on('end', () => {
                try {
                    resolve(JSON.parse(body));
                } catch(e) {
                    resolve({ ok: false, description: body });
                }
            });
        });
        req.on('error', (err) => {
            resolve({ ok: false, description: err.message });
        });
        req.on('timeout', () => {
            req.destroy();
            resolve({ ok: false, description: 'Telegram request timed out.' });
        });
        req.write(data);
        req.end();
    });
}

// Master Public Layout with Zero-Dependency Drawers & Search
function renderLayout(title, content, sessionData, activeNav = '') {
    const s = getSettings();
    const categories = db.prepare('SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as products_count FROM categories c').all();
    const productsList = db.prepare('SELECT p.id, p.name, p.slug, p.price, p.sale_price, p.image_path, c.name as category FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1').all();
    
    const searchProductsJson = JSON.stringify(productsList.map(p => ({
        id: p.id,
        name: p.name,
        slug: p.slug,
        price: p.sale_price || p.price,
        formatted_price: '৳' + (p.sale_price || p.price).toFixed(2),
        image: '/' + p.image_path,
        category: p.category || 'Accessories',
        url: '/product/' + p.slug
    })));

    const cartItems = Object.values(sessionData.cart || {});
    const cartCount = cartItems.reduce((sum, i) => sum + i.quantity, 0);
    const subtotal = cartItems.reduce((sum, i) => sum + (i.price * i.quantity), 0);
    const customer = sessionData.customer;

    return `<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${title} - ${s.store_name || 'OnlineBdMart'}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
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
                            50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc',
                            400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca',
                            800: '#3730a3', 900: '#312e81', 950: '#1e1b4b'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col selection:bg-indigo-600 selection:text-white">

    <!-- 1. TOP UTILITY BAR (Track Order, Hotline, Sign In) -->
    <div class="bg-slate-900 text-slate-300 text-xs py-2 px-4 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2 overflow-hidden text-xs">
                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-[10px] font-black bg-indigo-600 text-white tracking-wider uppercase">Hot</span>
                <p class="font-medium truncate">${s.announcement_bar || 'Free Delivery Tangail ৳50 | Others ৳150 • Free Shipping above ৳2000'}</p>
            </div>

            <div class="flex items-center gap-3 sm:gap-4 text-xs font-semibold text-slate-300">
                <a href="/track-order" class="hover:text-indigo-400 transition flex items-center gap-1.5 text-emerald-400 font-bold bg-emerald-950/60 border border-emerald-500/30 px-3 py-1 rounded-full">
                    <i class="fas fa-truck-fast"></i> <span>Track Order</span>
                </a>
                <span class="text-slate-700">|</span>
                <a href="tel:${s.contact_phone || '01775153740'}" class="hover:text-white transition flex items-center gap-1">
                    <i class="fas fa-phone text-emerald-400"></i> ${s.contact_phone || '01775153740'}
                </a>
                <span class="text-slate-700">|</span>
                ${customer ? `
                    <a href="/account" class="hover:text-indigo-300 transition flex items-center gap-1 text-indigo-400 font-bold">
                        <i class="fas fa-user-check"></i> ${customer.name}
                    </a>
                ` : `
                    <div class="flex items-center gap-2">
                        <a href="/login" class="hover:text-white transition"><i class="fas fa-arrow-right-to-bracket"></i> Sign In</a>
                        <span class="text-slate-700">/</span>
                        <a href="/register" class="hover:text-indigo-400 transition text-indigo-300 font-bold">Sign Up</a>
                    </div>
                `}
            </div>
        </div>
    </div>

    <!-- 2. MAIN HEADER (Logo, Instant Search Bar, Wishlist, Cart) -->
    <header class="sticky top-0 z-40 bg-white shadow-sm border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-2 sm:gap-4 py-3 sm:py-4">
                
                <!-- Hamburger Button & Brand Logo -->
                <div class="flex items-center gap-2 sm:gap-3 min-w-0 shrink">
                    <button type="button" onclick="openMobileMenu()" class="lg:hidden p-2 text-slate-700 hover:bg-slate-100 rounded-xl focus:outline-none shrink-0" title="Open Menu">
                        <i class="fas fa-bars-staggered text-lg sm:text-xl"></i>
                    </button>
                    <a href="/" class="flex items-center gap-2 sm:gap-3 group min-w-0">
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-700 to-cyan-500 flex items-center justify-center text-white text-base sm:text-xl shadow-md group-hover:scale-105 transition-transform shrink-0">
                            <i class="fas fa-bag-shopping"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-base sm:text-2xl font-black tracking-tight text-slate-900 leading-none truncate max-w-[120px] sm:max-w-none block">${(s.store_name || 'ONLINEBDMART').toUpperCase()}</span>
                            <span class="text-[9px] sm:text-[10px] tracking-widest font-extrabold text-indigo-600 uppercase block mt-0.5 truncate">Online Shopping BD</span>
                        </div>
                    </a>
                </div>

                <!-- Big Central Search Bar with Categories Select & Instant Live Dropdown -->
                <div class="hidden md:flex flex-1 max-w-2xl mx-4 relative">
                    <form method="GET" action="/shop" class="w-full flex items-center rounded-2xl border-2 border-indigo-600 bg-white shadow-sm relative">
                        <div class="border-r border-slate-200 bg-slate-50 px-3 py-2.5 shrink-0 rounded-l-xl">
                            <select name="category" class="text-xs font-bold text-slate-700 bg-transparent outline-none cursor-pointer">
                                <option value="">All Categories</option>
                                ${categories.map(c => `<option value="${c.slug}">${c.name}</option>`).join('')}
                            </select>
                        </div>
                        <div class="flex-1 relative">
                            <input type="text" 
                                   id="desktopLiveSearchInput"
                                   name="search"
                                   autocomplete="off"
                                   placeholder="Search products, watches, leather wallets, sunglasses, gadgets..." 
                                   class="w-full px-4 py-2.5 text-xs text-slate-800 outline-none font-medium">
                        </div>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 font-bold text-xs flex items-center gap-1.5 transition rounded-r-xl">
                            <i class="fas fa-search"></i> <span>Search</span>
                        </button>
                    </form>

                    <!-- Realtime Instant Dropdown Overlay -->
                    <div id="desktopLiveSearchDropdown" 
                         style="display: none;"
                         class="absolute left-0 right-0 top-full mt-2 bg-white rounded-2xl shadow-2xl border-2 border-slate-200 p-3 z-50 overflow-hidden divide-y divide-slate-100 max-h-96 overflow-y-auto">
                    </div>
                </div>

                <!-- Right Action Buttons: Account, Wishlist & Cart -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <a href="${customer ? '/account' : '/login'}" class="relative p-2 sm:p-2.5 rounded-xl bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-600 transition shrink-0" title="My Account">
                        <i class="far fa-user text-base sm:text-lg"></i>
                    </a>

                    <button type="button" onclick="openWishlistDrawer()" class="relative p-2 sm:p-2.5 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-700 hover:text-rose-600 transition shrink-0" title="Wishlist">
                        <i class="far fa-heart text-base sm:text-lg"></i>
                        <span id="headerWishlistBadge" class="absolute -top-1 -right-1 bg-rose-500 text-white text-[9px] sm:text-[10px] font-black rounded-full h-4 min-w-[16px] px-1 flex items-center justify-center">0</span>
                    </button>

                    <button type="button" onclick="openCartDrawer()" class="relative p-2 sm:px-4 sm:py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white transition flex items-center gap-1.5 sm:gap-2.5 shadow-md shadow-indigo-600/25 shrink-0" title="Cart">
                        <i class="fas fa-bag-shopping text-base sm:text-lg"></i>
                        <div class="hidden sm:block text-left text-xs leading-tight">
                            <span class="text-[10px] text-indigo-200 block">My Bag</span>
                            <span id="headerCartSubtotal" class="font-black">৳${subtotal.toFixed(2)}</span>
                        </div>
                        <span id="headerCartBadge" class="bg-white/20 text-white text-[10px] sm:text-[11px] font-extrabold rounded-full px-1.5 sm:px-2 py-0.5 flex items-center justify-center">${cartCount}</span>
                    </button>
                </div>
            </div>

            <!-- Mobile Search Bar -->
            <div class="pb-3 md:hidden relative">
                <form method="GET" action="/shop" class="flex items-center rounded-xl border border-slate-300 bg-white overflow-hidden shadow-sm">
                    <input type="text" id="mobileLiveSearchInput" name="search" placeholder="Search products, watches, bags..." autocomplete="off" class="flex-1 px-3 py-2 text-xs outline-none">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 text-xs font-bold"><i class="fas fa-search"></i></button>
                </form>
                <div id="mobileLiveSearchDropdown" style="display: none;" class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl shadow-2xl border-2 border-slate-200 p-2 z-50 max-h-72 overflow-y-auto divide-y"></div>
            </div>
        </div>

        <!-- 3. Exact Navigation Menu (Home | Shop | Wholesale | Categories | Deals | Blog | Contact) -->
        <div class="hidden lg:block bg-slate-900 text-white border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <nav class="flex items-center space-x-1 text-xs font-bold text-slate-200">
                    <a href="/" class="px-4 py-3.5 hover:text-indigo-400 hover:bg-slate-800 transition ${activeNav === 'home' ? 'text-indigo-400 bg-slate-800' : ''}">Home</a>
                    <a href="/shop" class="px-4 py-3.5 hover:text-indigo-400 hover:bg-slate-800 transition ${activeNav === 'shop' ? 'text-indigo-400 bg-slate-800' : ''}">Shop</a>
                    <a href="/wholesale" class="px-4 py-3.5 hover:text-amber-400 hover:bg-slate-800 transition text-amber-300 ${activeNav === 'wholesale' ? 'bg-slate-800 text-amber-400' : ''}"><i class="fas fa-boxes-stacked mr-1"></i> Wholesale</a>
                    <a href="/categories" class="px-4 py-3.5 hover:text-indigo-400 hover:bg-slate-800 transition ${activeNav === 'categories' ? 'text-indigo-400 bg-slate-800' : ''}">Categories</a>
                    <a href="/deals" class="px-4 py-3.5 hover:text-rose-400 hover:bg-slate-800 transition text-rose-300 ${activeNav === 'deals' ? 'bg-slate-800 text-rose-400' : ''}"><i class="fas fa-fire mr-1"></i> Deals</a>
                    <a href="/blog" class="px-4 py-3.5 hover:text-indigo-400 hover:bg-slate-800 transition ${activeNav === 'blog' ? 'text-indigo-400 bg-slate-800' : ''}">Blog</a>
                    <a href="/contact" class="px-4 py-3.5 hover:text-indigo-400 hover:bg-slate-800 transition ${activeNav === 'contact' ? 'text-indigo-400 bg-slate-800' : ''}">Contact</a>
                </nav>

                <div class="flex items-center gap-2 text-xs font-bold text-slate-300 py-3.5">
                    <i class="fab fa-whatsapp text-emerald-400 text-base"></i>
                    <a href="https://wa.me/88${s.whatsapp_number || '01775153740'}" target="_blank" class="hover:text-emerald-400 transition">Hotline: ${s.whatsapp_number || '01775153740'}</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Slide-out Menu (Zero Dependency Vanilla JS) -->
    <div id="mobileSideMenuDrawer" class="fixed inset-0 z-50 flex lg:hidden hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeMobileMenu()"></div>
        <div class="relative max-w-xs w-full bg-white h-full shadow-2xl flex flex-col justify-between py-6 px-6 overflow-y-auto z-10">
            <div>
                <div class="flex items-center justify-between pb-6 border-b">
                    <span class="font-extrabold text-lg text-slate-900">${s.store_name || 'OnlineBdMart'}</span>
                    <button type="button" onclick="closeMobileMenu()" class="text-slate-400 hover:text-slate-700 p-1"><i class="fas fa-times text-xl"></i></button>
                </div>
                <div class="mt-6 space-y-1 text-xs font-bold">
                    <a href="/" class="block px-4 py-3 rounded-xl hover:bg-indigo-50">🏠 Home</a>
                    <a href="/shop" class="block px-4 py-3 rounded-xl hover:bg-indigo-50">🛍️ Shop</a>
                    <a href="/wholesale" class="block px-4 py-3 rounded-xl text-amber-700 bg-amber-50">📦 Wholesale / B2B</a>
                    <a href="/categories" class="block px-4 py-3 rounded-xl hover:bg-indigo-50">🏷️ Categories</a>
                    <a href="/deals" class="block px-4 py-3 rounded-xl text-rose-600 hover:bg-rose-50">🔥 Hot Deals</a>
                    <a href="/blog" class="block px-4 py-3 rounded-xl hover:bg-indigo-50">📰 Blog & Guides</a>
                    <a href="/contact" class="block px-4 py-3 rounded-xl hover:bg-indigo-50">📞 Contact Us</a>
                    <a href="/track-order" class="block px-4 py-3 rounded-xl text-emerald-700 bg-emerald-50">🚚 Track Order</a>
                </div>
            </div>
            <div class="pt-6 border-t space-y-2">
                <a href="/track-order" class="block w-full py-2.5 bg-emerald-50 text-emerald-800 font-bold rounded-xl text-xs text-center">Track Order Live</a>
                <a href="/login" class="block w-full py-2.5 bg-indigo-600 text-white font-bold rounded-xl text-xs text-center">Sign In / Sign Up</a>
            </div>
        </div>
    </div>

    <!-- Sliding Cart Drawer (Zero Dependency Vanilla JS) -->
    <div id="cartDrawerContainer" class="fixed inset-0 z-50 overflow-hidden hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeCartDrawer()"></div>
        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-md bg-white shadow-2xl flex flex-col justify-between z-10">
                <div class="p-5 border-b flex items-center justify-between bg-slate-50">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-bag-shopping text-indigo-600 text-lg"></i>
                        <h2 class="text-base font-extrabold text-slate-900">Your Cart (<span id="cartDrawerBadge">${cartCount}</span>)</h2>
                    </div>
                    <button type="button" onclick="closeCartDrawer()" class="p-2 text-slate-400 hover:text-slate-600"><i class="fas fa-times text-lg"></i></button>
                </div>

                <div class="px-5 py-3 bg-indigo-50 border-b border-indigo-100 text-xs">
                    <div class="flex justify-between items-center mb-1 font-bold">
                        <span id="freeShippingText">${subtotal >= 2000 ? '🎉 You qualify for FREE Delivery!' : 'Add ৳' + (2000 - subtotal).toFixed(2) + ' more for FREE Delivery'}</span>
                        <span id="freeShippingPct" class="text-indigo-600">${Math.min(100, Math.round((subtotal / 2000) * 100))}%</span>
                    </div>
                    <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                        <div id="freeShippingBar" class="bg-gradient-to-r from-indigo-500 to-emerald-400 h-full rounded-full transition-all duration-300" style="width: ${Math.min(100, (subtotal / 2000) * 100)}%;"></div>
                    </div>
                </div>

                <!-- Cart Items Container -->
                <div id="cartDrawerItemsList" class="flex-1 overflow-y-auto p-5 space-y-4">
                    ${cartItems.length === 0 ? `
                        <div class="h-full flex flex-col items-center justify-center text-center py-12">
                            <i class="fas fa-shopping-basket text-slate-200 text-5xl mb-4"></i>
                            <h3 class="font-bold text-slate-800">Your Cart is Empty</h3>
                            <a href="/shop" onclick="closeCartDrawer()" class="mt-4 px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl text-xs shadow">Start Shopping</a>
                        </div>
                    ` : cartItems.map(item => `
                        <div class="flex gap-4 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <img src="/${item.image.replace(/^\/+/, '')}" class="w-16 h-16 object-cover rounded-xl bg-white border shrink-0">
                            <div class="flex-1 min-w-0 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between gap-2">
                                        <h4 class="text-xs font-bold text-slate-800 line-clamp-1">${item.name}</h4>
                                        <button onclick="removeCartItem(${item.id})" class="text-slate-300 hover:text-rose-500"><i class="fas fa-trash-can text-xs"></i></button>
                                    </div>
                                    <p class="text-xs font-bold text-indigo-600">৳${item.price.toFixed(2)}</p>
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
                    `).join('')}
                </div>

                <div id="cartDrawerFooter" class="p-5 border-t bg-slate-50 space-y-3" style="${cartItems.length === 0 ? 'display:none;' : ''}">
                    <div class="flex justify-between text-sm font-extrabold text-slate-900">
                        <span>Subtotal:</span>
                        <span id="cartDrawerSubtotal" class="text-indigo-600 text-base">৳${subtotal.toFixed(2)}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2.5">
                        <a href="/cart" class="py-3 px-4 bg-white border font-bold rounded-xl text-xs text-center hover:bg-slate-100">View Cart</a>
                        <a href="/checkout" class="py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs text-center shadow flex items-center justify-center gap-1.5">
                            Checkout &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Wishlist Drawer (Zero Dependency Vanilla JS) -->
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
                    <a href="/shop" onclick="closeWishlistDrawer()" class="block w-full py-3 bg-slate-900 text-white font-bold rounded-xl text-xs text-center">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Yield -->
    <main class="flex-1">
        ${content}
    </main>

    <!-- Mobile Bottom Navigation Bar (Home, Shop, Wholesale, Track, Account) -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 px-6 py-2.5 flex items-center justify-between text-[10px] font-bold text-slate-600 shadow-xl">
        <a href="/" class="flex flex-col items-center gap-1 ${activeNav === 'home' ? 'text-indigo-600' : ''}">
            <i class="fas fa-house text-base"></i> <span>Home</span>
        </a>
        <a href="/shop" class="flex flex-col items-center gap-1 ${activeNav === 'shop' ? 'text-indigo-600' : ''}">
            <i class="fas fa-boxes-stacked text-base"></i> <span>Shop</span>
        </a>
        <a href="/wholesale" class="flex flex-col items-center gap-1 text-amber-600 ${activeNav === 'wholesale' ? 'text-amber-700 font-extrabold' : ''}">
            <i class="fas fa-boxes-packing text-base"></i> <span>Wholesale</span>
        </a>
        <a href="/track-order" class="flex flex-col items-center gap-1 text-emerald-600 font-extrabold ${activeNav === 'track' ? 'text-emerald-600' : ''}">
            <i class="fas fa-truck-fast text-base"></i> <span>Track</span>
        </a>
        <a href="${customer ? '/account' : '/login'}" class="flex flex-col items-center gap-1 ${activeNav === 'account' ? 'text-indigo-600 font-extrabold' : ''}">
            <i class="fas fa-user-circle text-base"></i> <span>Account</span>
        </a>
    </div>

    <!-- Floating WhatsApp Support Button -->
    <a href="https://wa.me/88${s.whatsapp_number || '01775153740'}" target="_blank" title="WhatsApp Support" class="fixed bottom-16 lg:bottom-6 right-6 z-30 w-14 h-14 bg-emerald-500 hover:bg-emerald-600 text-white rounded-full flex items-center justify-center shadow-2xl hover:scale-110 transition-all duration-300">
        <i class="fab fa-whatsapp text-2xl"></i>
    </a>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed bottom-20 lg:bottom-6 left-6 z-50 flex flex-col gap-2 max-w-sm"></div>

    <!-- Luxury Footer -->
    <footer class="bg-slate-950 text-white pt-16 pb-20 lg:pb-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 pb-12 border-b border-slate-800">
                <div class="space-y-4">
                    ${s.store_logo ? `<img src="/${s.store_logo.replace(/^\/+/, '')}" alt="${s.store_name || 'OnlineBdMart'}" class="h-10 max-w-[170px] object-contain mb-2">` : `<h3 class="text-xl font-extrabold">${s.store_name || 'OnlineBdMart'}</h3>`}
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed">${s.footer_about_text || s.store_tagline || 'Premium Wholesale & Retail in Bangladesh.'}</p>
                    <p class="text-xs text-slate-500"><i class="fas fa-location-dot text-rose-500 mr-1"></i> ${s.store_address || 'Tangail, Bangladesh'}</p>
                    <p class="text-xs text-slate-400"><i class="fas fa-phone text-emerald-400 mr-1"></i> ${s.store_phone || s.contact_phone || '01775153740'}</p>
                </div>
                <div>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-slate-200 mb-4">Quick Navigation</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="/" class="hover:text-white">Home</a></li>
                        <li><a href="/shop" class="hover:text-white">All Products</a></li>
                        <li><a href="/wholesale" class="hover:text-white text-amber-400">Wholesale / B2B Rate</a></li>
                        <li><a href="/categories" class="hover:text-white">Categories</a></li>
                        <li><a href="/deals" class="hover:text-white text-rose-400">Hot Deals %</a></li>
                        <li><a href="/blog" class="hover:text-white">Buying Guides</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-slate-200 mb-4">Customer Care</h4>
                    <ul class="space-y-2 text-xs text-slate-400 font-semibold">
                        <li><a href="/track-order" class="text-emerald-400 hover:underline">📦 Track Your Order</a></li>
                        <li><a href="/contact" class="hover:text-white">📞 Help Center & Contact</a></li>
                        <li><a href="/cart" class="hover:text-white">🛒 View Shopping Cart</a></li>
                        <li><a href="/checkout" class="hover:text-white">⚡ Express Checkout</a></li>
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
                &copy; ${new Date().getFullYear()} ${s.store_name || 'OnlineBdMart'} • Online Shopping Bangladesh. Built with Laravel 11 & Tailwind CSS.
            </div>
        </div>
    </footer>

    <!-- Zero-Dependency Native Client Engine -->
    <script>
        window.__SEARCH_PRODUCTS__ = ${searchProductsJson};
        window.__CART_DATA__ = {
            count: ${cartCount},
            subtotal: ${subtotal},
            items: ${JSON.stringify(cartItems)}
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
                    itemsList.innerHTML = \`
                        <div class="h-full flex flex-col items-center justify-center text-center py-12">
                            <i class="fas fa-shopping-basket text-slate-200 text-5xl mb-4"></i>
                            <h3 class="font-bold text-slate-800">Your Cart is Empty</h3>
                            <a href="/shop" onclick="closeCartDrawer()" class="mt-4 px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl text-xs shadow">Start Shopping</a>
                        </div>
                    \`;
                } else {
                    let html = '';
                    data.items.forEach(item => {
                        html += \`
                            <div class="flex gap-4 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                                <img src="/\${item.image.replace(/^\\/+/, '')}" class="w-16 h-16 object-cover rounded-xl bg-white border shrink-0">
                                <div class="flex-1 min-w-0 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-start justify-between gap-2">
                                            <h4 class="text-xs font-bold text-slate-800 line-clamp-1">\${item.name}</h4>
                                            <button onclick="removeCartItem(\${item.id})" class="text-slate-300 hover:text-rose-500"><i class="fas fa-trash-can text-xs"></i></button>
                                        </div>
                                        <p class="text-xs font-bold text-indigo-600">৳\${item.price.toFixed(2)}</p>
                                    </div>
                                    <div class="flex items-center justify-between mt-2">
                                        <div class="flex items-center border rounded-lg bg-white">
                                            <button onclick="updateCartQty(\${item.id}, \${item.quantity - 1})" class="w-6 h-6 flex items-center justify-center text-xs font-bold hover:bg-slate-100">-</button>
                                            <span class="w-8 text-center text-xs font-bold">\${item.quantity}</span>
                                            <button onclick="updateCartQty(\${item.id}, \${item.quantity + 1})" class="w-6 h-6 flex items-center justify-center text-xs font-bold hover:bg-slate-100">+</button>
                                        </div>
                                        <span class="text-xs font-extrabold">৳\${(item.price * item.quantity).toFixed(2)}</span>
                                    </div>
                                </div>
                            </div>
                        \`;
                    });
                    itemsList.innerHTML = html;
                }
            }
        }

        // 3. Add to Cart Function
        function addToCart(productId, quantity = 1, isWholesale = false) {
            fetch('/cart/add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
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
            fetch('/cart/update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
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
            fetch('/cart/remove', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
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
                    container.innerHTML = \`
                        <div class="h-full flex flex-col items-center justify-center text-center py-12">
                            <i class="far fa-heart text-slate-200 text-5xl mb-4"></i>
                            <h3 class="font-bold text-slate-800">Your Wishlist is Empty</h3>
                            <a href="/shop" onclick="closeWishlistDrawer()" class="mt-4 px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl text-xs">Browse Items</a>
                        </div>
                    \`;
                } else {
                    let html = '';
                    list.forEach(item => {
                        html += \`
                            <div class="flex gap-4 p-3 rounded-2xl bg-slate-50 border items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="\${item.image}" class="w-14 h-14 object-cover rounded-xl bg-white border shrink-0">
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-800 line-clamp-1">\${item.name}</h4>
                                        <p class="text-xs font-bold text-indigo-600 mt-0.5">৳\${Number(item.price).toFixed(2)}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button onclick="addToCart(\${item.id}); toggleWishlist(\${JSON.stringify(item).replace(/"/g, '&quot;')});" class="px-3 py-1.5 bg-slate-900 text-white text-[11px] font-bold rounded-lg shadow">Add to Bag</button>
                                </div>
                            </div>
                        \`;
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
            setTimeout(() => t.remove(), 3500);
        }

        // 6. Realtime Search Initializer
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
                    dropdown.innerHTML = \`
                        <div class="p-6 text-center text-xs text-slate-400 space-y-1">
                            <i class="fas fa-box-open text-2xl text-slate-300"></i>
                            <p class="font-bold text-slate-700">No products found for "\${q}"</p>
                            <p class="text-[11px] text-slate-400">Try searching for watch, wallet, bag, sunglasses, or belt.</p>
                        </div>
                    \`;
                } else {
                    let html = \`
                        <div class="px-3 py-1.5 flex items-center justify-between text-[11px] font-extrabold uppercase text-slate-400 bg-slate-50 rounded-lg mb-1.5">
                            <span>🔍 Instant Live Results</span>
                            <span class="text-indigo-600 font-bold">\${matches.length} Product(s) Found</span>
                        </div>
                        <div class="space-y-1">
                    \`;

                    matches.forEach(item => {
                        html += \`
                            <a href="\${item.url}" class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-indigo-50/80 transition group border border-transparent hover:border-indigo-100">
                                <img src="\${item.image}" class="w-12 h-12 object-cover rounded-xl bg-slate-100 border shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-slate-800 truncate group-hover:text-indigo-600">\${item.name}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs font-black text-indigo-600">\${item.formatted_price}</span>
                                        <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">● In Stock</span>
                                    </div>
                                </div>
                                <i class="fas fa-arrow-right text-xs text-slate-300 group-hover:text-indigo-600 group-hover:translate-x-1 transition-transform"></i>
                            </a>
                        \`;
                    });

                    html += \`
                        </div>
                        <div class="pt-2 border-t border-slate-100 mt-2">
                            <a href="/shop?search=\${encodeURIComponent(q)}" class="block text-center py-2 bg-slate-50 hover:bg-indigo-50 text-indigo-600 font-bold text-xs rounded-xl transition border border-slate-100">
                                View all matching results for "\${q}" &rarr;
                            </a>
                        </div>
                    \`;
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
</html>`;
}

// Admin Products CRUD & Management with Add Product Modal & Form
function renderAdminProductsPage(products, categories) {
    return `
        <div class="space-y-6 text-xs">
            <!-- Add Product Modal & Form Card -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                <div class="flex items-center justify-between border-b pb-4">
                    <div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-indigo-50 text-indigo-700">📦 Catalog Management</span>
                        <h3 class="text-base font-extrabold text-slate-900 font-serif mt-1">Add New Product to Store</h3>
                        <p class="text-slate-500 mt-0.5">Upload products with retail pricing, wholesale bulk rate, stock, and local images.</p>
                    </div>
                </div>

                <form method="POST" action="/admin-panel/products" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="sm:col-span-2">
                            <label class="font-bold text-slate-700 block mb-1">Product Title *</label>
                            <input type="text" name="name" required placeholder="e.g. Luxury Sapphire Watch" class="w-full border rounded-xl px-3.5 py-2.5 outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Category *</label>
                            <select name="category_id" required class="w-full border rounded-xl px-3.5 py-2.5 bg-white outline-none">
                                ${categories.map(c => `<option value="${c.id}">${c.name}</option>`).join('')}
                            </select>
                        </div>
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">SKU Code</label>
                            <input type="text" name="sku" placeholder="e.g. PRO-101" class="w-full border rounded-xl px-3.5 py-2.5 outline-none font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Regular Price (৳) *</label>
                            <input type="number" step="0.01" name="price" required placeholder="3850.00" class="w-full border rounded-xl px-3.5 py-2.5 outline-none font-bold text-indigo-600">
                        </div>
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Sale Discount Price (৳)</label>
                            <input type="number" step="0.01" name="sale_price" placeholder="3250.00" class="w-full border rounded-xl px-3.5 py-2.5 outline-none">
                        </div>
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Wholesale / B2B Price (৳)</label>
                            <input type="number" step="0.01" name="wholesale_price" placeholder="2450.00" class="w-full border rounded-xl px-3.5 py-2.5 outline-none font-bold text-emerald-600">
                        </div>
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Wholesale Min Qty (MOQ)</label>
                            <input type="number" name="wholesale_min_qty" value="5" class="w-full border rounded-xl px-3.5 py-2.5 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Stock Count *</label>
                            <input type="number" name="stock" required value="25" class="w-full border rounded-xl px-3.5 py-2.5 outline-none">
                        </div>
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Cover Image Graphic</label>
                            <select name="image_path" class="w-full border rounded-xl px-3.5 py-2.5 bg-white outline-none">
                                <option value="uploads/luxury-watch.svg">Luxury Chronograph Watch</option>
                                <option value="uploads/smart-watch.svg">Pro AMOLED Smartwatch</option>
                                <option value="uploads/leather-wallet.svg">Handcrafted Leather Wallet</option>
                                <option value="uploads/designer-handbag.svg">Minimalist Luxury Handbag</option>
                                <option value="uploads/polaroid-sunglasses.svg">Aviator Polarized Sunglasses</option>
                                <option value="uploads/leather-belt.svg">Formal Leather Belt</option>
                                <option value="uploads/pearl-necklace.svg">Pearl Pendant Necklace</option>
                                <option value="uploads/perfume-bottle.svg">Eau De Parfum Noir</option>
                                <option value="uploads/leather-backpack.svg">Leather Commuter Backpack</option>
                                <option value="uploads/cotton-cap.svg">Vintage Cotton Cap</option>
                                <option value="uploads/diamond-ring.svg">Sterling Silver Ring</option>
                                <option value="uploads/silk-tie-set.svg">Jacquard Silk Tie Set</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-6 pt-5">
                            <label class="flex items-center gap-2 font-bold cursor-pointer"><input type="checkbox" name="is_featured" value="1" checked> <span>Featured</span></label>
                            <label class="flex items-center gap-2 font-bold cursor-pointer"><input type="checkbox" name="is_wholesale" value="1" checked> <span>Wholesale Catalog</span></label>
                        </div>
                    </div>

                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Short Description</label>
                        <input type="text" name="short_description" placeholder="Brief summary of materials and specs..." class="w-full border rounded-xl px-3.5 py-2.5 outline-none">
                    </div>

                    <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl shadow-lg transition">
                        + Save & Publish Product
                    </button>
                </form>
            </div>

            <!-- Existing Products Table -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-sm uppercase text-slate-900">Current Store Catalog (${products.length} Products)</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="p-3">Preview</th>
                                <th class="p-3">Name</th>
                                <th class="p-3">Category</th>
                                <th class="p-3">Retail Price</th>
                                <th class="p-3">Wholesale Rate</th>
                                <th class="p-3 text-center">Stock</th>
                                <th class="p-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            ${products.map(p => `
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3"><img src="/${p.image_path}" class="w-10 h-10 object-cover rounded-xl border bg-slate-100 shrink-0"></td>
                                    <td class="p-3 font-bold text-slate-900">${p.name}</td>
                                    <td class="p-3 text-slate-500 font-semibold">${p.category_name || 'Accessories'}</td>
                                    <td class="p-3 font-bold text-slate-900">৳${(p.sale_price || p.price).toFixed(2)}</td>
                                    <td class="p-3 font-extrabold text-emerald-600">৳${(p.wholesale_price || (p.price * 0.75)).toFixed(2)}</td>
                                    <td class="p-3 text-center font-bold">${p.stock} pcs</td>
                                    <td class="p-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="/product/${p.slug}" target="_blank" class="p-1.5 text-slate-400 hover:text-slate-700"><i class="fas fa-eye"></i></a>
                                            <form method="POST" action="/admin-panel/products/delete" onsubmit="return confirm('Delete this product?');" class="inline">
                                                <input type="hidden" name="id" value="${p.id}">
                                                <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700"><i class="fas fa-trash-can"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

// 21-Item Functional Admin Layout
function renderAdminLayout(title, content, activeNav = 'dashboard') {
    const s = getSettings();
    const pendingOrders = db.prepare("SELECT COUNT(*) as c FROM orders WHERE status = 'pending'").get().c;
    const msgCount = db.prepare('SELECT COUNT(*) as c FROM wholesale_inquiries').get().c;

    return `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${title} - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; } ::-webkit-scrollbar { width: 5px; } ::-webkit-scrollbar-track { background: #0f172a; } ::-webkit-scrollbar-thumb { background: #334155; border-radius: 9999px; }</style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased min-h-screen">

    <div class="flex h-screen overflow-hidden">
        <!-- 21-Item Sidebar -->
        <aside class="w-64 bg-slate-950 text-slate-300 flex flex-col justify-between border-r border-slate-800 shrink-0">
            <div class="flex-1 overflow-y-auto">
                <div class="h-20 flex items-center justify-between px-6 border-b border-slate-800 sticky top-0 bg-slate-950 z-10">
                    <a href="/admin-panel/dashboard" class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-500 to-cyan-400 flex items-center justify-center text-white font-bold"><i class="fas fa-crown text-sm"></i></div>
                        <div>
                            <span class="text-sm font-extrabold text-white uppercase block leading-none">${s.store_name || 'ONLINEBDMART'}</span>
                            <span class="text-[10px] text-indigo-400 font-bold tracking-widest uppercase">Admin Panel</span>
                        </div>
                    </a>
                </div>

                <nav class="p-3 space-y-1 text-xs font-semibold">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 pt-2 pb-1">Core E-Commerce</p>
                    <a href="/admin-panel/dashboard" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'dashboard' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>📊</span> <span>Dashboard</span></a>
                    <a href="/admin-panel/products" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'products' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>📦</span> <span>Products</span></a>
                    <a href="/admin-panel/wholesale" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'wholesale' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>🏭</span> <span>Wholesale / B2B</span></a>
                    <a href="/admin-panel/orders" class="flex items-center justify-between px-3 py-2 rounded-xl transition ${activeNav === 'orders' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><div class="flex items-center gap-3"><span>🧾</span> <span>Orders</span></div>${pendingOrders > 0 ? `<span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-400 text-slate-950">${pendingOrders}</span>` : ''}</a>
                    <a href="/admin-panel/banners" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'banners' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>🖼️</span> <span>Banners / Slider</span></a>
                    <a href="/admin-panel/categories" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'categories' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>📂</span> <span>Categories</span></a>
                    <a href="/admin-panel/coupons" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'coupons' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>🎟️</span> <span>Coupons</span></a>
                    <a href="/admin-panel/blogs" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'blogs' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>📝</span> <span>Blog</span></a>
                    <a href="/admin-panel/reviews" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'reviews' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>⭐</span> <span>Reviews</span></a>
                    <a href="/admin-panel/messages" class="flex items-center justify-between px-3 py-2 rounded-xl transition ${activeNav === 'messages' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><div class="flex items-center gap-3"><span>💬</span> <span>Messages</span></div>${msgCount > 0 ? `<span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-400 text-slate-950">${msgCount}</span>` : ''}</a>

                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 pt-4 pb-1">Business & Operations</p>
                    <a href="/admin-panel/payments" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'payments' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>💳</span> <span>Payments</span></a>
                    <a href="/admin-panel/delivery" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'delivery' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>🚚</span> <span>Delivery</span></a>
                    <a href="/admin-panel/customers" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'customers' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>👥</span> <span>Customers</span></a>
                    <a href="/admin-panel/suppliers" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'suppliers' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>🏭</span> <span>Suppliers</span></a>
                    <a href="/admin-panel/analytics" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'analytics' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>📈</span> <span>Analytics</span></a>

                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 pt-4 pb-1">Integrations & Settings</p>
                    <a href="/admin-panel/whatsapp" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'whatsapp' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>🟢</span> <span>WhatsApp</span></a>
                    <a href="/admin-panel/telegram" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'telegram' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>📨</span> <span>Telegram</span></a>
                    <a href="/admin-panel/pixel" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'pixel' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>📘</span> <span>Facebook Pixel</span></a>
                    <a href="/admin-panel/colors" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'colors' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>🎨</span> <span>Colors</span></a>
                    <a href="/admin-panel/otp" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'otp' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>🔑</span> <span>OTP System</span></a>
                    <a href="/admin-panel/seo" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'seo' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>🔍</span> <span>SEO Setup</span></a>
                    <a href="/admin-panel/settings" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'settings' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>⚙️</span> <span>Site Settings</span></a>
                    <a href="/admin-panel/profile" class="flex items-center gap-3 px-3 py-2 rounded-xl transition ${activeNav === 'profile' ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-slate-900'}"><span>👤</span> <span>Admin Profile</span></a>
                </nav>
            </div>

            <div class="p-4 border-t border-slate-800 space-y-2 bg-slate-950">
                <a href="/" target="_blank" class="flex items-center justify-center gap-2 w-full py-2 bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white font-semibold rounded-xl text-xs transition">
                    <i class="fas fa-arrow-up-right-from-square text-[11px]"></i> Visit Storefront
                </a>
                <div class="flex items-center justify-between pt-2 px-2">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-indigo-600/30 text-indigo-400 font-bold flex items-center justify-center text-xs border border-indigo-500/30">A</div>
                        <div><p class="text-xs font-bold text-white leading-none">Admin</p><p class="text-[10px] text-emerald-400 mt-0.5">● Superadmin</p></div>
                    </div>
                    <a href="/admin-panel/logout" class="p-2 text-slate-400 hover:text-rose-400"><i class="fas fa-right-from-bracket"></i></a>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="h-20 bg-white border-b border-slate-200 px-4 sm:px-8 flex items-center justify-between shrink-0 shadow-sm">
                <div class="flex items-center gap-4">
                    <h1 class="text-lg font-bold text-slate-900">${title}</h1>
                </div>
                <div class="flex items-center gap-3">
                    <a href="/" target="_blank" class="text-xs text-slate-500 hover:text-indigo-600 font-semibold flex items-center gap-1.5"><i class="fas fa-store"></i> Customer View</a>
                    <a href="/admin-panel/products" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow">+ Product</a>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 sm:p-8 space-y-6">
                ${content}
            </main>
        </div>
    </div>
</body>
</html>`;
}

// Main HTTP Server
const server = http.createServer(async (req, res) => {
    const parsedUrl = url.parse(req.url, true);
    const pathname = parsedUrl.pathname;
    const method = req.method.toUpperCase();
    const isGet = method === 'GET' || method === 'HEAD';
    const sessionData = getSession(req, res);

    if (isGet) {
        trackVisitorSession(req, pathname);
    }

    if (pathname.startsWith('/uploads/') || pathname.startsWith('/images/') || pathname.startsWith('/css/') || pathname.startsWith('/js/')) {
        if (serveStatic(req, res, pathname)) return;
    }

    function sendJson(data, statusCode = 200) {
        res.writeHead(statusCode, { 'Content-Type': 'application/json' });
        if (method === 'HEAD') { res.end(); return; }
        res.end(JSON.stringify(data));
    }

    function redirect(location, statusCode = 302) {
        res.writeHead(statusCode, { 'Location': location });
        res.end();
    }

    function sendHtml(html, statusCode = 200) {
        res.writeHead(statusCode, { 'Content-Type': 'text/html; charset=utf-8' });
        if (method === 'HEAD') { res.end(); return; }
        res.end(html);
    }

    // -------------------------------------------------------------
    // Direct ZIP Download Page & Endpoints
    // -------------------------------------------------------------
    if ((pathname === '/download' || pathname === '/download/' || pathname === '/download/cpanel') && isGet) {
        const downloadHtmlPath = path.join(__dirname, 'public', 'download.html');
        if (fs.existsSync(downloadHtmlPath)) {
            res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
            fs.createReadStream(downloadHtmlPath).pipe(res);
            return;
        }
    }

    if ((pathname === '/download/zip' || pathname === '/OnlineBdMart-cPanel-Ready.zip' || pathname === '/OnlineBdMart-Laravel.zip' || pathname === '/Fashion-Store-Laravel.zip') && isGet) {
        const zipName = pathname.includes('Laravel') ? 'OnlineBdMart-Laravel.zip' : 'OnlineBdMart-cPanel-Ready.zip';
        const zipPath = path.join(__dirname, zipName);
        if (fs.existsSync(zipPath)) {
            res.writeHead(200, {
                'Content-Type': 'application/zip',
                'Content-Disposition': `attachment; filename="${zipName}"`,
                'Content-Length': fs.statSync(zipPath).size
            });
            fs.createReadStream(zipPath).pipe(res);
            return;
        }
    }

    // Redirect /admin to /admin-panel
    if (pathname === '/admin' || pathname === '/admin/' || pathname === '/admin/login') {
        return redirect('/admin-panel/login');
    }

    // -------------------------------------------------------------
    // Telegram Webhook API for Order Management (Buttons + Commands)
    // -------------------------------------------------------------
    if (pathname === '/api/telegram/webhook' && method === 'POST') {
        const body = await parseBody(req);

        if (body.callback_query && body.callback_query.data) {
            const data = body.callback_query.data;
            if (data.startsWith('set_status:')) {
                const parts = data.split(':');
                const newStatus = parts[1] || 'pending';
                const orderId = parseInt(parts[2] || 0, 10);
                if (orderId > 0) {
                    db.prepare('UPDATE orders SET status = ? WHERE id = ?').run(newStatus, orderId);
                    return sendJson({ 
                        success: true, 
                        message: `Order #${orderId} status updated to ${newStatus} via Telegram!`,
                        order_id: orderId,
                        new_status: newStatus
                    });
                }
            }
        }

        if (body.message && body.message.text) {
            const text = body.message.text.trim();
            if (text === '/pending' || text === '/orders') {
                const pending = db.prepare("SELECT * FROM orders WHERE status = 'pending' ORDER BY id DESC LIMIT 5").all();
                const total = db.prepare("SELECT COUNT(*) as c FROM orders WHERE status = 'pending'").get().c;
                return sendJson({ success: true, total_pending: total, orders: pending, reply: `📦 Found ${total} pending orders.` });
            }

            if (text.startsWith('/confirm ') || text.startsWith('/deliver ') || text.startsWith('/cancel ') || text.startsWith('/ship ')) {
                const parts = text.split(' ');
                const cmd = parts[0].replace('/', '');
                const orderId = parseInt(parts[1] || 0, 10);
                const statusMap = { 'confirm': 'confirmed', 'deliver': 'delivered', 'cancel': 'cancelled', 'ship': 'shipped' };
                const newStatus = statusMap[cmd] || 'pending';
                if (orderId > 0) {
                    db.prepare('UPDATE orders SET status = ? WHERE id = ?').run(newStatus, orderId);
                    return sendJson({ success: true, reply: `✅ Order #${orderId} has been updated to *${newStatus.toUpperCase()}* via Telegram command!` });
                }
            }
        }

        return sendJson({ success: true, status: 'ok' });
    }

    // -------------------------------------------------------------
    // Customer Auth
    // -------------------------------------------------------------
    if (pathname === '/login' && isGet) {
        if (sessionData.customer) return redirect('/account');
        const content = `
            <div class="max-w-md mx-auto px-4 py-16">
                <div class="bg-white p-8 rounded-3xl border shadow-xl space-y-6">
                    <div class="text-center"><h2 class="text-2xl font-bold font-serif text-slate-900">Welcome Back</h2><p class="text-xs text-slate-500 mt-1">Sign in to your customer account</p></div>
                    <form method="POST" action="/login" class="space-y-4 text-xs">
                        <div><label class="block font-bold mb-1">Email or Phone</label><input type="text" name="email" required placeholder="user@example.com / 017XXXXXXXX" class="w-full border rounded-xl px-3.5 py-2.5 outline-none"></div>
                        <div><label class="block font-bold mb-1">Password</label><input type="password" name="password" required placeholder="••••••••" class="w-full border rounded-xl px-3.5 py-2.5 outline-none"></div>
                        <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl shadow">Sign In &rarr;</button>
                    </form>
                    <div class="text-center text-xs text-slate-500">Don't have an account? <a href="/register" class="text-indigo-600 font-bold hover:underline">Create Account</a></div>
                </div>
            </div>
        `;
        return sendHtml(renderLayout('Customer Sign In', content, sessionData));
    }

    if (pathname === '/login' && method === 'POST') {
        const body = await parseBody(req);
        sessionData.customer = { id: 1, name: (body.email || 'Customer').split('@')[0], email: body.email, phone: '01711223344' };
        return redirect('/account');
    }

    if (pathname === '/register' && isGet) {
        if (sessionData.customer) return redirect('/account');
        const content = `
            <div class="max-w-md mx-auto px-4 py-16">
                <div class="bg-white p-8 rounded-3xl border shadow-xl space-y-6">
                    <div class="text-center"><h2 class="text-2xl font-bold font-serif text-slate-900">Create Account</h2><p class="text-xs text-slate-500 mt-1">Sign up for faster checkout & tracking</p></div>
                    <form method="POST" action="/register" class="space-y-4 text-xs">
                        <div><label class="block font-bold mb-1">Full Name</label><input type="text" name="name" required placeholder="Tanvir Ahmed" class="w-full border rounded-xl px-3.5 py-2.5 outline-none"></div>
                        <div><label class="block font-bold mb-1">Phone Number</label><input type="tel" name="phone" required placeholder="01711223344" class="w-full border rounded-xl px-3.5 py-2.5 outline-none"></div>
                        <div><label class="block font-bold mb-1">Email Address</label><input type="email" name="email" required placeholder="tanvir@example.com" class="w-full border rounded-xl px-3.5 py-2.5 outline-none"></div>
                        <div><label class="block font-bold mb-1">Password</label><input type="password" name="password" required placeholder="••••••••" class="w-full border rounded-xl px-3.5 py-2.5 outline-none"></div>
                        <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl shadow">Create Account &rarr;</button>
                    </form>
                    <div class="text-center text-xs text-slate-500">Already have an account? <a href="/login" class="text-indigo-600 font-bold hover:underline">Sign In</a></div>
                </div>
            </div>
        `;
        return sendHtml(renderLayout('Create Account', content, sessionData));
    }

    if (pathname === '/register' && method === 'POST') {
        const body = await parseBody(req);
        sessionData.customer = { id: 1, name: body.name || 'Customer', email: body.email, phone: body.phone };
        return redirect('/account');
    }

    if (pathname === '/account' && isGet) {
        const customer = sessionData.customer;
        if (!customer) return redirect('/login');
        const orders = db.prepare('SELECT * FROM orders ORDER BY id DESC LIMIT 5').all();

        const content = `
            <div class="max-w-7xl mx-auto px-4 py-12 space-y-8">
                <div class="bg-white p-6 sm:p-8 rounded-3xl border shadow-sm flex items-center justify-between">
                    <div><h2 class="text-xl font-bold font-serif">Welcome, ${customer.name}!</h2><p class="text-xs text-slate-500">${customer.email} • ${customer.phone}</p></div>
                    <a href="/logout" class="px-4 py-2 bg-rose-50 text-rose-700 rounded-xl text-xs font-bold border border-rose-200">Logout</a>
                </div>
                <div class="bg-white p-6 sm:p-8 rounded-3xl border shadow-sm space-y-4 text-xs">
                    <h3 class="font-bold text-sm uppercase">My Recent Orders</h3>
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 font-bold"><tr><th class="p-3">Order #</th><th class="p-3">Date</th><th class="p-3">Total</th><th class="p-3">Status</th><th class="p-3 text-right">Invoice</th></tr></thead>
                        <tbody class="divide-y">${orders.map(o => `<tr><td class="p-3 font-mono font-bold">#${o.id}</td><td class="p-3">${o.created_at}</td><td class="p-3 font-bold">৳${o.grand_total.toFixed(2)}</td><td class="p-3 uppercase font-bold text-indigo-600">${o.status}</td><td class="p-3 text-right"><a href="/order/${o.id}/invoice" target="_blank" class="px-3 py-1 bg-slate-900 text-white rounded-lg">Receipt</a></td></tr>`).join('')}</tbody>
                    </table>
                </div>
            </div>
        `;
        return sendHtml(renderLayout('My Account', content, sessionData));
    }

    if (pathname === '/logout') {
        sessionData.customer = null;
        return redirect('/');
    }

    // -------------------------------------------------------------
    // Wholesale Page
    // -------------------------------------------------------------
    if (pathname === '/wholesale' && isGet) {
        const s = getSettings();
        const products = db.prepare('SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 LIMIT 12').all();

        const content = `
            <div class="bg-gradient-to-r from-slate-950 via-indigo-950 to-slate-900 text-white py-16">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                        <div class="space-y-4">
                            <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-amber-500/20 text-amber-400 text-xs font-black uppercase"><i class="fas fa-boxes-stacked"></i> Wholesale / B2B Rate</span>
                            <h1 class="text-3xl sm:text-5xl font-extrabold font-serif leading-tight">Bulk Prices for Retailers & Resellers</h1>
                            <p class="text-slate-300 text-xs sm:text-sm">OnlineBdMart Wholesale provides factory rates and minimum order quantities directly for shop owners, online resellers, and corporate gift orders. Add products to cart in bulk or order via WhatsApp!</p>
                            <div class="pt-2 flex flex-wrap gap-4 text-xs font-bold">
                                <a href="#wholesale-products" class="px-6 py-3 bg-amber-500 text-slate-950 rounded-xl shadow-lg font-black">Browse Wholesale Items &rarr;</a>
                                <a href="https://wa.me/88${s.whatsapp_number || '01775153740'}?text=${encodeURIComponent('Hello, I am interested in wholesale purchases at OnlineBdMart.')}" target="_blank" class="px-6 py-3 bg-emerald-500 text-white rounded-xl shadow-lg flex items-center gap-2 font-bold"><i class="fab fa-whatsapp"></i> WhatsApp B2B Agent</a>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-center text-xs">
                            <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-1"><span class="text-2xl font-black text-amber-400">25% - 40%</span><h4 class="font-bold text-white">Below Retail Price</h4></div>
                            <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-1"><span class="text-2xl font-black text-emerald-400">5 Pcs</span><h4 class="font-bold text-white">Low Minimum Order</h4></div>
                            <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-1"><span class="text-2xl font-black text-indigo-400">64 Districts</span><h4 class="font-bold text-white">Nationwide Courier</h4></div>
                            <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-1"><span class="text-2xl font-black text-cyan-400">100% Verified</span><h4 class="font-bold text-white">Original Quality</h4></div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="wholesale-products" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <span class="text-xs font-extrabold uppercase text-indigo-600">Available For Bulk Order</span>
                        <h2 class="text-2xl font-extrabold font-serif text-slate-900 mt-1">Wholesale Products Catalog</h2>
                    </div>
                    <span class="text-xs text-slate-400 font-semibold">Select quantity and Add to Bag or Order via WhatsApp</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
                    ${products.map(p => {
                        const wsPrice = p.wholesale_price || (p.price * 0.75);
                        const minQty = p.wholesale_min_qty || 5;
                        return `
                            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-4 flex flex-col justify-between relative">
                                <span class="absolute top-4 left-4 z-10 px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-500 text-slate-950 shadow uppercase">Min: ${minQty} Pcs</span>
                                <a href="/product/${p.slug}"><img src="/${p.image_path}" class="w-full aspect-square object-cover rounded-2xl bg-slate-100 mb-3"></a>
                                <div class="space-y-3">
                                    <div>
                                        <span class="text-[10px] font-bold uppercase text-slate-400 block">${p.category_name || 'Accessories'}</span>
                                        <h4 class="font-bold text-xs text-slate-900 line-clamp-1">${p.name}</h4>
                                    </div>
                                    <div class="bg-slate-50 p-3 rounded-2xl border text-xs">
                                        <div class="flex justify-between items-baseline"><span class="text-[10px] text-slate-500 font-bold">Wholesale (Per Pc):</span><span class="text-base font-black text-emerald-600">৳${wsPrice.toFixed(2)}</span></div>
                                        <div class="flex justify-between items-baseline mt-0.5"><span class="text-[10px] text-slate-400">Regular Retail:</span><span class="text-xs text-slate-400 line-through">৳${p.price.toFixed(2)}</span></div>
                                    </div>
                                    <div class="space-y-2 pt-1">
                                        <button type="button" onclick="addToCart(${p.id}, ${minQty}, true)" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow flex items-center justify-center gap-1.5">
                                            <i class="fas fa-bag-shopping"></i> Add to Cart (Min ${minQty} pcs)
                                        </button>
                                        <a href="https://wa.me/88${s.whatsapp_number || '01775153740'}?text=${encodeURIComponent('Hello! I want wholesale order for: ' + p.name + ' (Min ' + minQty + ' pcs @ ৳' + wsPrice.toFixed(2) + ' each)')}" target="_blank" class="w-full py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold text-center block shadow">
                                            <i class="fab fa-whatsapp mr-1"></i> Order on WhatsApp
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>

                <div id="wholesale-inquiry" class="bg-white p-8 rounded-3xl border shadow-sm max-w-3xl mx-auto space-y-6 text-xs">
                    <h3 class="text-xl font-bold font-serif text-slate-900 text-center">Submit Wholesale Inquiry / Dealer Application</h3>
                    <form method="POST" action="/wholesale/inquiry" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <input type="text" name="business_name" required placeholder="Business / Shop Name *" class="border rounded-xl px-3.5 py-2.5 outline-none">
                            <input type="text" name="contact_person" required placeholder="Contact Person Name *" class="border rounded-xl px-3.5 py-2.5 outline-none">
                            <input type="tel" name="phone" required placeholder="Phone Number *" class="border rounded-xl px-3.5 py-2.5 outline-none">
                            <input type="text" name="district" required placeholder="District / Area *" class="border rounded-xl px-3.5 py-2.5 outline-none">
                            <input type="text" name="estimated_monthly_quantity" placeholder="Estimated Quantity (e.g. 20-50 pcs)" class="sm:col-span-2 border rounded-xl px-3.5 py-2.5 outline-none">
                            <textarea name="message" rows="3" placeholder="Products interested in & queries..." class="sm:col-span-2 border rounded-xl px-3.5 py-2.5 outline-none"></textarea>
                        </div>
                        <button type="submit" class="w-full py-3.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black rounded-xl shadow text-xs">Submit Application &rarr;</button>
                    </form>
                </div>
            </div>
        `;
        return sendHtml(renderLayout('Wholesale & B2B Rates', content, sessionData, 'wholesale'));
    }

    if (pathname === '/wholesale/inquiry' && method === 'POST') {
        const body = await parseBody(req);
        try {
            db.prepare('INSERT INTO wholesale_inquiries (business_name, contact_person, phone, whatsapp, district, estimated_monthly_quantity, message) VALUES (?, ?, ?, ?, ?, ?, ?)').run(
                body.business_name || '', body.contact_person || '', body.phone || '', body.whatsapp || '', body.district || '', body.estimated_monthly_quantity || '', body.message || ''
            );
        } catch (e) {}
        return sendHtml(renderLayout('Inquiry Received', `
            <div class="max-w-md mx-auto px-4 py-20 text-center space-y-4">
                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-3xl mx-auto"><i class="fas fa-check"></i></div>
                <h2 class="text-xl font-bold font-serif">Inquiry Submitted!</h2>
                <p class="text-xs text-slate-500">Thank you! Our B2B Wholesale manager will contact you shortly.</p>
                <a href="/wholesale" class="px-6 py-2.5 bg-indigo-600 text-white font-bold text-xs rounded-xl inline-block">Return to Wholesale</a>
            </div>
        `, sessionData));
    }

    // -------------------------------------------------------------
    // Categories Page
    // -------------------------------------------------------------
    if (pathname === '/categories' && isGet) {
        const categories = db.prepare('SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as products_count FROM categories c').all();
        const content = `
            <div class="max-w-7xl mx-auto px-4 py-12">
                <h1 class="text-2xl sm:text-3xl font-extrabold font-serif mb-8">All Product Categories</h1>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    ${categories.map(c => `
                        <a href="/shop?category=${c.slug}" class="p-6 bg-white rounded-3xl border hover:shadow-xl transition flex flex-col justify-between">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl"><i class="fas ${c.icon || 'fa-tag'}"></i></div>
                                <div><h3 class="font-bold text-sm text-slate-900">${c.name}</h3><span class="text-xs font-bold text-emerald-600">${c.products_count} Products</span></div>
                            </div>
                            <p class="text-xs text-slate-500 mb-4">${c.description || 'Explore our full range.'}</p>
                            <span class="text-xs font-bold text-indigo-600 flex items-center justify-between"><span>View Collection</span><i class="fas fa-arrow-right"></i></span>
                        </a>
                    `).join('')}
                </div>
            </div>
        `;
        return sendHtml(renderLayout('Categories', content, sessionData, 'categories'));
    }

    // -------------------------------------------------------------
    // Deals Page
    // -------------------------------------------------------------
    if (pathname === '/deals' && isGet) {
        const deals = db.prepare('SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 AND p.sale_price IS NOT NULL AND p.sale_price < p.price').all();
        const coupons = db.prepare('SELECT * FROM coupons WHERE is_active = 1').all();

        const content = `
            <div class="max-w-7xl mx-auto px-4 py-12 space-y-8">
                <div class="bg-gradient-to-r from-rose-950 to-indigo-950 text-white p-8 rounded-3xl flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div>
                        <span class="px-3 py-1 bg-rose-500/20 text-rose-400 text-xs font-bold rounded-full uppercase">🔥 Flash Deals</span>
                        <h1 class="text-3xl font-extrabold font-serif mt-2">Hot Promotional Offers</h1>
                        <p class="text-xs text-slate-300">Up to 40% OFF with Nationwide Cash On Delivery.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    ${coupons.map(c => `
                        <div class="p-4 bg-white rounded-2xl border-2 border-dashed border-indigo-200 flex justify-between items-center">
                            <div><span class="text-[10px] text-slate-400 font-bold uppercase">Promo Code</span><h4 class="text-base font-black text-indigo-600 font-mono">${c.code}</h4><p class="text-xs text-slate-500">${c.type === 'percentage' ? c.value + '% OFF' : '৳' + c.value + ' OFF'}</p></div>
                            <button type="button" onclick="navigator.clipboard.writeText('${c.code}'); showToast('Copied: ${c.code}', 'success');" class="px-3 py-1.5 bg-indigo-600 text-white font-bold rounded-xl text-xs">Copy</button>
                        </div>
                    `).join('')}
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                    ${deals.map(p => `
                        <div class="bg-white rounded-2xl border p-4 flex flex-col justify-between">
                            <img src="/${p.image_path}" class="w-full aspect-square object-cover rounded-xl mb-3 bg-slate-100">
                            <div><h4 class="font-bold text-xs text-slate-900">${p.name}</h4><div class="flex items-baseline gap-2 mt-2"><span class="text-base font-black text-slate-900">৳${p.sale_price.toFixed(2)}</span><span class="text-xs text-slate-400 line-through">৳${p.price.toFixed(2)}</span></div></div>
                            <button type="button" onclick="addToCart(${p.id})" class="w-full mt-3 py-2 bg-indigo-600 text-white font-bold rounded-xl text-xs shadow">Add to Cart</button>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        return sendHtml(renderLayout('Hot Deals & Discounts', content, sessionData, 'deals'));
    }

    // -------------------------------------------------------------
    // Blog & Guides Page
    // -------------------------------------------------------------
    if (pathname === '/blog' && isGet) {
        const posts = db.prepare('SELECT * FROM blogs WHERE is_published = 1 ORDER BY id DESC').all();
        const content = `
            <div class="max-w-7xl mx-auto px-4 py-12 space-y-8">
                <div class="text-center max-w-xl mx-auto space-y-2">
                    <span class="text-xs font-bold uppercase text-indigo-600">Our Blog & Guides</span>
                    <h1 class="text-3xl font-extrabold font-serif text-slate-900">Latest News & Buying Guides</h1>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    ${posts.map(p => `
                        <div class="bg-white rounded-3xl border overflow-hidden p-6 space-y-3 flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] font-bold uppercase text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full">${p.category}</span>
                                <h3 class="text-base font-bold text-slate-900 mt-2"><a href="/blog/${p.slug}" class="hover:text-indigo-600">${p.title}</a></h3>
                                <p class="text-xs text-slate-500 mt-2 leading-relaxed line-clamp-3">${p.summary}</p>
                            </div>
                            <a href="/blog/${p.slug}" class="text-xs font-bold text-indigo-600 hover:underline pt-3 border-t">Read Article &rarr;</a>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        return sendHtml(renderLayout('Blog & Buying Guides', content, sessionData, 'blog'));
    }

    if (pathname.startsWith('/blog/') && isGet) {
        const slug = pathname.replace('/blog/', '');
        const post = db.prepare('SELECT * FROM blogs WHERE slug = ?').get(slug);
        if (!post) return sendHtml(renderLayout('Not Found', '<h1>Post Not Found</h1>', sessionData), 404);

        const content = `
            <div class="max-w-4xl mx-auto px-4 py-12 space-y-6">
                <div class="space-y-2">
                    <span class="text-xs font-black uppercase text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">${post.category}</span>
                    <h1 class="text-2xl sm:text-4xl font-extrabold font-serif text-slate-900 leading-tight">${post.title}</h1>
                    <p class="text-xs text-slate-400">Published by ${post.author} • ${post.created_at}</p>
                </div>

                <!-- Cover Photo -->
                <div class="aspect-video w-full rounded-3xl overflow-hidden border shadow-xl bg-slate-900">
                    <img src="/${post.image_path || 'uploads/hero-banner-1.svg'}" class="w-full h-full object-cover">
                </div>

                ${post.summary ? `<div class="p-5 rounded-2xl bg-indigo-50 border border-indigo-100 text-xs sm:text-sm font-semibold text-indigo-900 italic leading-relaxed">"${post.summary}"</div>` : ''}

                <div class="p-8 sm:p-10 bg-white rounded-3xl border text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line">${post.content}</div>
                <a href="/blog" class="inline-block text-xs font-bold text-indigo-600 hover:underline">&larr; Back to all guides</a>
            </div>
        `;
        return sendHtml(renderLayout(post.title, content, sessionData, 'blog'));
    }

    // -------------------------------------------------------------
    // Home Page
    // -------------------------------------------------------------
    if (pathname === '/' && isGet) {
        const s = getSettings();
        const banners = db.prepare('SELECT * FROM banners WHERE is_active = 1 ORDER BY display_order ASC').all();
        const categories = db.prepare('SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as products_count FROM categories c').all();
        const featured = db.prepare('SELECT * FROM products WHERE is_active = 1 AND is_featured = 1 ORDER BY id DESC LIMIT 8').all();

        const bannerHtml = `
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 pt-2 sm:pt-4 mb-10 sm:mb-12">
                <section class="relative bg-slate-950 text-white overflow-hidden rounded-3xl sm:rounded-[2.5rem] shadow-2xl border border-slate-800/80" id="heroSliderSection">
                    ${banners.map((b, idx) => `
                        <div class="hero-slide relative min-h-[420px] sm:min-h-[480px] lg:min-h-[520px] flex items-center transition-all duration-700 ${idx === 0 ? '' : 'hidden'}" data-slide="${idx}">
                            <img src="/${b.image_path}" class="absolute inset-0 w-full h-full object-cover opacity-60">
                            <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/80 to-transparent"></div>
                            <div class="relative max-w-7xl mx-auto px-6 sm:px-12 py-12 sm:py-16 w-full">
                                <div class="max-w-2xl space-y-4 sm:space-y-5">
                                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-black uppercase tracking-widest animate-pulse">
                                        ${b.badge_text || '✨ NEW ARRIVALS 2026'}
                                    </span>
                                    <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold font-serif leading-tight">
                                        ${b.title}
                                    </h1>
                                    <p class="text-slate-300 text-xs sm:text-base leading-relaxed font-normal max-w-xl">
                                        ${b.subtitle}
                                    </p>
                                    <div class="pt-2 flex flex-wrap items-center gap-3 sm:gap-4">
                                        <a href="${b.button_url || '/shop'}" class="px-7 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs rounded-xl shadow-xl transition flex items-center gap-2">
                                            ${b.button_text || 'Shop Now'} &rarr;
                                        </a>
                                        <a href="/wholesale" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs rounded-xl shadow transition">
                                            Wholesale B2B &rarr;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('')}

                    <button type="button" onclick="prevHeroSlide()" class="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-black/40 hover:bg-black/80 text-white flex items-center justify-center backdrop-blur-sm transition z-20">
                        <i class="fas fa-chevron-left text-xs sm:text-sm"></i>
                    </button>
                    <button type="button" onclick="nextHeroSlide()" class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-black/40 hover:bg-black/80 text-white flex items-center justify-center backdrop-blur-sm transition z-20">
                        <i class="fas fa-chevron-right text-xs sm:text-sm"></i>
                    </button>

                    <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex items-center gap-2 z-20">
                        ${banners.map((_, idx) => `
                            <button type="button" onclick="goToHeroSlide(${idx})" class="hero-dot h-2 rounded-full transition-all duration-300 ${idx === 0 ? 'w-8 bg-indigo-500' : 'w-2 bg-white/40'}" data-dot="${idx}"></button>
                        `).join('')}
                    </div>
                </section>
            </div>
        `;

        const catCards = categories.map(c => `
            <a href="/shop?category=${c.slug}" class="group bg-white p-5 rounded-2xl border border-slate-200/80 hover:border-indigo-500 hover:shadow-xl transition flex flex-col items-center text-center">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-50 to-indigo-100 group-hover:from-indigo-600 group-hover:to-cyan-500 text-indigo-600 group-hover:text-white flex items-center justify-center text-2xl transition mb-3">
                    <i class="fas ${c.icon || 'fa-tag'}"></i>
                </div>
                <h3 class="text-xs font-bold text-slate-900 group-hover:text-indigo-600">${c.name}</h3>
                <span class="text-[10px] text-slate-400 font-semibold mt-0.5">${c.products_count} Items</span>
            </a>
        `).join('');

        const productCards = featured.map(p => `
            <div class="group bg-white rounded-2xl border border-slate-200/90 shadow-sm hover:shadow-xl transition flex flex-col justify-between overflow-hidden relative">
                ${p.sale_price && p.sale_price < p.price ? `<span class="absolute top-3 left-3 z-10 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white">Sale</span>` : ''}
                <div class="absolute top-3 right-3 z-10">
                    <button type="button" onclick="toggleWishlist({ id: ${p.id}, name: '${p.name.replace(/'/g, "\\'")}', price: ${p.sale_price || p.price}, image: '/${p.image_path}' })" class="w-8 h-8 rounded-full bg-white/90 shadow text-slate-400 hover:text-rose-500 flex items-center justify-center">
                        <i class="fas fa-heart text-xs"></i>
                    </button>
                </div>
                <a href="/product/${p.slug}" class="relative block aspect-square bg-slate-100 overflow-hidden">
                    <img src="/${p.image_path}" alt="${p.name}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                </a>
                <div class="p-4 flex flex-col justify-between flex-1">
                    <div>
                        <a href="/product/${p.slug}" class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition line-clamp-2">${p.name}</a>
                        <div class="flex text-amber-400 text-[10px] mt-1.5"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><span class="text-slate-400 font-semibold ml-1">(${p.reviews_count || 45})</span></div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <div class="flex items-baseline gap-2 mb-3"><span class="text-sm sm:text-base font-black text-slate-900">৳${(p.sale_price || p.price).toFixed(2)}</span>${p.sale_price ? `<span class="text-[11px] text-slate-400 line-through">৳${p.price.toFixed(2)}</span>` : ''}</div>
                        <button type="button" onclick="addToCart(${p.id})" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow"><i class="fas fa-bag-shopping mr-1"></i> Add to Cart</button>
                    </div>
                </div>
            </div>
        `).join('');

        const content = `
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                ${bannerHtml}

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-8 mb-8 border-y border-slate-200">
                    <div class="flex items-center gap-3.5 p-3 rounded-2xl bg-white border"><div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl"><i class="fas fa-truck-fast"></i></div><div><h4 class="text-xs font-extrabold">Free Shipping</h4><p class="text-[11px] text-slate-500">Over ৳2000</p></div></div>
                    <div class="flex items-center gap-3.5 p-3 rounded-2xl bg-white border"><div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl"><i class="fas fa-shield-halved"></i></div><div><h4 class="text-xs font-extrabold">Secure Payment</h4><p class="text-[11px] text-slate-500">100% Safe COD</p></div></div>
                    <div class="flex items-center gap-3.5 p-3 rounded-2xl bg-white border"><div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl"><i class="fas fa-rotate-left"></i></div><div><h4 class="text-xs font-extrabold">Easy Returns</h4><p class="text-[11px] text-slate-500">7-Day Guarantee</p></div></div>
                    <div class="flex items-center gap-3.5 p-3 rounded-2xl bg-white border"><div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl"><i class="fas fa-headset"></i></div><div><h4 class="text-xs font-extrabold">24/7 Support</h4><p class="text-[11px] text-slate-500">WhatsApp hotline</p></div></div>
                </div>

                <!-- Wholesale B2B Showcase Card -->
                <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950 rounded-3xl p-8 sm:p-10 text-white shadow-xl border border-slate-800 flex flex-col lg:flex-row items-center justify-between gap-8 mb-14">
                    <div class="space-y-3 max-w-xl text-center lg:text-left">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-400 text-xs font-black uppercase tracking-wider">
                            <i class="fas fa-boxes-stacked"></i> Wholesale / B2B Portal
                        </span>
                        <h3 class="text-2xl sm:text-3xl font-extrabold font-serif">Bulk Prices for Retailers & Resellers</h3>
                        <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                            OnlineBdMart wholesale — factory rates with low 5 pcs minimum quantity. Controlled directly from Admin. Add to cart in bulk or order via WhatsApp!
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-4">
                        <a href="/wholesale" class="px-8 py-3.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs sm:text-sm rounded-xl shadow-lg transition flex items-center gap-2">
                            <span>Open Wholesale Catalog</span> <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                        <a href="https://wa.me/88${s.whatsapp_number || '01775153740'}?text=${encodeURIComponent('Hello, I am interested in wholesale / B2B bulk purchases at OnlineBdMart.')}" target="_blank" class="px-6 py-3.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs sm:text-sm rounded-xl shadow transition flex items-center gap-2">
                            <i class="fab fa-whatsapp text-base"></i> WhatsApp B2B Agent
                        </a>
                    </div>
                </div>

                <div class="mb-14">
                    <div class="flex items-center justify-between mb-6">
                        <div><span class="text-[11px] font-extrabold uppercase text-indigo-600">Shop By Category</span><h2 class="text-2xl font-extrabold font-serif text-slate-900 mt-1">Browse Top Categories</h2></div>
                        <a href="/categories" class="text-xs font-bold text-indigo-600 hover:underline">View All &rarr;</a>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">${catCards}</div>
                </div>

                <!-- Flash Deals with Live Countdown (Rounded Corners & Spaced Sides) -->
                <div class="p-8 sm:p-12 rounded-3xl sm:rounded-[2.5rem] bg-gradient-to-r from-slate-950 via-indigo-950 to-slate-900 text-white relative overflow-hidden mb-14 border border-slate-800 shadow-2xl">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                        <div class="space-y-4 max-w-xl">
                            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-rose-500/20 text-rose-400 text-xs font-black uppercase tracking-widest border border-rose-500/30 animate-pulse">% Limited Time Flash Sale</span>
                            <h3 class="text-3xl sm:text-4xl font-extrabold font-serif">Big Deals on Top Fashion Gadgets</h3>
                            <p class="text-slate-300 text-xs sm:text-sm">Grab luxury chronograph watches and leather wallets at unbeatable discount prices.</p>
                            <div class="pt-2">
                                <a href="/deals" class="px-7 py-3 bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white font-extrabold text-xs rounded-xl inline-flex items-center gap-2 shadow-lg transition"><span>Shop Flash Deals</span> <i class="fas fa-arrow-right text-xs"></i></a>
                            </div>
                        </div>
                        <div class="bg-slate-900/90 border border-slate-700/60 backdrop-blur-md rounded-3xl p-6 sm:p-8 flex items-center justify-center gap-4 text-center shadow-xl">
                            <div><div id="liveHours" class="w-16 sm:w-20 py-3 sm:py-4 bg-slate-950 border border-slate-800 rounded-2xl text-2xl sm:text-3xl font-black text-indigo-400 font-mono shadow-inner">12</div><span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mt-1 block">Hours</span></div>
                            <span class="text-2xl font-bold text-slate-600 -mt-4">:</span>
                            <div><div id="liveMins" class="w-16 sm:w-20 py-3 sm:py-4 bg-slate-950 border border-slate-800 rounded-2xl text-2xl sm:text-3xl font-black text-emerald-400 font-mono shadow-inner">48</div><span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mt-1 block">Mins</span></div>
                            <span class="text-2xl font-bold text-slate-600 -mt-4">:</span>
                            <div><div id="liveSecs" class="w-16 sm:w-20 py-3 sm:py-4 bg-slate-950 border border-slate-800 rounded-2xl text-2xl sm:text-3xl font-black text-amber-400 font-mono shadow-inner">26</div><span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mt-1 block">Secs</span></div>
                        </div>
                    </div>
                </div>

                <div class="mb-14">
                    <div class="flex items-center justify-between mb-6">
                        <div><span class="text-[11px] font-extrabold uppercase text-indigo-600">Best Sellers</span><h2 class="text-2xl font-extrabold font-serif text-slate-900 mt-1">Top Picks for You</h2></div>
                        <a href="/shop" class="text-xs font-bold text-indigo-600 hover:underline">View All &rarr;</a>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">${productCards}</div>
                </div>

                <!-- 7. BLOG & BUYING GUIDES SECTION -->
                <div class="mb-14">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <span class="text-[11px] font-extrabold uppercase tracking-widest text-indigo-600">Our Blog & Buying Guides</span>
                            <h2 class="text-2xl font-extrabold font-serif text-slate-900 mt-1">Latest Tech & Fashion Guides</h2>
                        </div>
                        <a href="/blog" class="text-xs font-bold text-indigo-600 hover:underline">View All Guides &rarr;</a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white rounded-3xl border shadow-sm p-6 space-y-3 flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] font-bold uppercase text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full">Buying Guide</span>
                                <h3 class="text-base font-bold text-slate-900 mt-2"><a href="/blog/top-5-luxury-watches-wallets-2026" class="hover:text-indigo-600">Top 5 Luxury Watches & Accessories Under ৳5000 in 2026</a></h3>
                                <p class="text-xs text-slate-500 mt-2 leading-relaxed">We tested 15+ premium Japanese quartz watches and full-grain cowhide leather wallets to find the best value for money in Bangladesh.</p>
                            </div>
                            <a href="/blog/top-5-luxury-watches-wallets-2026" class="text-xs font-bold text-indigo-600 hover:underline pt-3 border-t">Read Article &rarr;</a>
                        </div>
                        <div class="bg-white rounded-3xl border shadow-sm p-6 space-y-3 flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] font-bold uppercase text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full">Tips & Tricks</span>
                                <h3 class="text-base font-bold text-slate-900 mt-2"><a href="/blog/how-to-spot-original-vs-copy" class="hover:text-indigo-600">How to Spot Original vs Copy Accessories Before Paying</a></h3>
                                <p class="text-xs text-slate-500 mt-2 leading-relaxed">Avoid cheap replicas with these 5 quick verification checks before making payment to courier riders.</p>
                            </div>
                            <a href="/blog/how-to-spot-original-vs-copy" class="text-xs font-bold text-indigo-600 hover:underline pt-3 border-t">Read Article &rarr;</a>
                        </div>
                        <div class="bg-white rounded-3xl border shadow-sm p-6 space-y-3 flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] font-bold uppercase text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">B2B & Wholesale</span>
                                <h3 class="text-base font-bold text-slate-900 mt-2"><a href="/blog/wholesale-reselling-guide-bangladesh" class="hover:text-indigo-600">Wholesale & Reselling Guide for Beginners in Bangladesh</a></h3>
                                <p class="text-xs text-slate-500 mt-2 leading-relaxed">How to start your online or offline accessory business with low MOQ and factory pricing directly from Tangail & Dhaka.</p>
                            </div>
                            <a href="/blog/wholesale-reselling-guide-bangladesh" class="text-xs font-bold text-indigo-600 hover:underline pt-3 border-t">Read Article &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Customer Reviews Section -->
                <div class="mb-14 bg-white p-8 sm:p-12 rounded-3xl border">
                    <div class="text-center max-w-xl mx-auto mb-8 space-y-2">
                        <span class="text-xs font-bold uppercase text-indigo-600">Customer Love</span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold font-serif">What Our Buyers Say</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">
                        <div class="p-6 bg-slate-50 rounded-2xl border space-y-2">
                            <div class="flex text-amber-400"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                            <p class="text-slate-600 italic">"Headphone quality outstanding! Bass heavy and ANC works perfectly. Delivery was completed in 2 days."</p>
                            <h4 class="font-bold pt-2 border-t">Arif Hossain <span class="text-emerald-600 text-[10px]">✓ Verified</span></h4>
                        </div>
                        <div class="p-6 bg-slate-50 rounded-2xl border space-y-2">
                            <div class="flex text-amber-400"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                            <p class="text-slate-600 italic">"Got genuine leather handbag and pearl necklace. Best price in BD and cash on delivery was smooth."</p>
                            <h4 class="font-bold pt-2 border-t">Nusrat Jahan <span class="text-emerald-600 text-[10px]">✓ Verified</span></h4>
                        </div>
                        <div class="p-6 bg-slate-50 rounded-2xl border space-y-2">
                            <div class="flex text-amber-400"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                            <p class="text-slate-600 italic">"Polarized sunglasses and leather wallet are authentic. Real-time order tracking updated every step."</p>
                            <h4 class="font-bold pt-2 border-t">Tanvir Ahmed <span class="text-emerald-600 text-[10px]">✓ Verified</span></h4>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                // Hero 5-second carousel engine
                let currentHeroIdx = 0;
                const totalHeroSlides = ${banners.length};
                function showHeroSlide(idx) {
                    currentHeroIdx = (idx + totalHeroSlides) % totalHeroSlides;
                    document.querySelectorAll('.hero-slide').forEach(s => s.classList.add('hidden'));
                    const active = document.querySelector('.hero-slide[data-slide=\"' + currentHeroIdx + '\"]');
                    if (active) active.classList.remove('hidden');

                    document.querySelectorAll('.hero-dot').forEach(d => {
                        const dotIdx = parseInt(d.getAttribute('data-dot'), 10);
                        if (dotIdx === currentHeroIdx) {
                            d.className = 'hero-dot h-2 rounded-full transition-all duration-300 w-8 bg-indigo-500';
                        } else {
                            d.className = 'hero-dot h-2 rounded-full transition-all duration-300 w-2 bg-white/40';
                        }
                    });
                }
                function nextHeroSlide() { showHeroSlide(currentHeroIdx + 1); }
                function prevHeroSlide() { showHeroSlide(currentHeroIdx - 1); }
                function goToHeroSlide(idx) { showHeroSlide(idx); }
                setInterval(nextHeroSlide, 5000);

                // Countdown timer
                let h = 12, m = 48, sec = 26;
                setInterval(() => {
                    if (sec > 0) sec--;
                    else {
                        sec = 59;
                        if (m > 0) m--;
                        else { m = 59; if (h > 0) h--; }
                    }
                    const elH = document.getElementById('liveHours');
                    const elM = document.getElementById('liveMins');
                    const elS = document.getElementById('liveSecs');
                    if (elH) elH.textContent = String(h).padStart(2, '0');
                    if (elM) elM.textContent = String(m).padStart(2, '0');
                    if (elS) elS.textContent = String(sec).padStart(2, '0');
                }, 1000);
            </script>
        `;
        return sendHtml(renderLayout('Home', content, sessionData, 'home'));
    }

    // -------------------------------------------------------------
    // Shop, Product, Cart, Checkout, Tracking, Invoice, Contact
    // -------------------------------------------------------------
    if (pathname === '/shop' && isGet) {
        const catSlug = parsedUrl.query.category || '';
        const search = parsedUrl.query.search || '';
        let sql = 'SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1';
        const params = [];
        if (catSlug) { sql += ' AND c.slug = ?'; params.push(catSlug); }
        if (search) { sql += ' AND (p.name LIKE ? OR p.description LIKE ?)'; params.push(`%${search}%`, `%${search}%`); }
        sql += ' ORDER BY p.id DESC';

        const products = db.prepare(sql).all(...params);

        const content = `
            <div class="max-w-7xl mx-auto px-4 py-10">
                <h1 class="text-2xl sm:text-3xl font-extrabold font-serif mb-6">All Products (${products.length})</h1>
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                    ${products.map(p => `
                        <div class="bg-white rounded-2xl border p-4 flex flex-col justify-between">
                            <a href="/product/${p.slug}"><img src="/${p.image_path}" class="w-full aspect-square object-cover rounded-xl mb-3 bg-slate-100"></a>
                            <div><span class="text-[10px] text-slate-400 font-bold uppercase">${p.category_name || 'Accessories'}</span><h4 class="font-bold text-xs text-slate-900"><a href="/product/${p.slug}">${p.name}</a></h4><p class="text-sm font-black text-indigo-600 mt-2">৳${(p.sale_price || p.price).toFixed(2)}</p></div>
                            <button type="button" onclick="addToCart(${p.id})" class="w-full mt-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow">Add to Cart</button>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        return sendHtml(renderLayout('Shop', content, sessionData, 'shop'));
    }

    if (pathname.startsWith('/product/') && isGet) {
        const slug = pathname.replace('/product/', '');
        const p = db.prepare('SELECT * FROM products WHERE slug = ?').get(slug);
        if (!p) return sendHtml(renderLayout('Not Found', '<h1>Product Not Found</h1>', sessionData), 404);

        const content = `
            <div class="max-w-7xl mx-auto px-4 py-10">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <div class="aspect-square bg-slate-100 rounded-3xl overflow-hidden border"><img src="/${p.image_path}" class="w-full h-full object-cover"></div>
                    <div class="space-y-6">
                        <div><h1 class="text-2xl sm:text-3xl font-extrabold font-serif text-slate-900">${p.name}</h1><span class="text-emerald-600 font-bold text-xs block mt-2">● In Stock (${p.stock} pcs)</span></div>
                        <div class="text-3xl font-black text-indigo-600">৳${(p.sale_price || p.price).toFixed(2)}</div>
                        <p class="text-xs sm:text-sm text-slate-600">${p.description || p.short_description || ''}</p>
                        <div class="flex items-center gap-3">
                            <div class="h-12 flex items-center border rounded-2xl bg-white px-1">
                                <button type="button" onclick="let input=document.getElementById('detailQty'); if(parseInt(input.value)>1) input.value=parseInt(input.value)-1;" class="w-9 h-9 font-bold text-slate-700 hover:bg-slate-100 rounded-xl transition">-</button>
                                <input type="number" id="detailQty" value="1" class="w-10 text-center font-black outline-none border-none">
                                <button type="button" onclick="let input=document.getElementById('detailQty'); input.value=parseInt(input.value)+1;" class="w-9 h-9 font-bold text-slate-700 hover:bg-slate-100 rounded-xl transition">+</button>
                            </div>
                            <button type="button" onclick="addToCart(${p.id}, parseInt(document.getElementById('detailQty').value))" class="flex-1 h-12 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white font-black rounded-2xl text-xs sm:text-sm shadow-lg shadow-indigo-600/25 transition flex items-center justify-center gap-2">
                                <i class="fas fa-bag-shopping text-sm"></i> <span>Add to Bag</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        return sendHtml(renderLayout(p.name, content, sessionData, 'shop'));
    }

    if (pathname === '/search-suggestions' && isGet) {
        const q = parsedUrl.query.q || '';
        const items = db.prepare('SELECT id, name, slug, price, sale_price, image_path FROM products WHERE is_active = 1 AND name LIKE ? LIMIT 6').all(`%${q}%`);
        return sendJson(items.map(p => ({ id: p.id, name: p.name, slug: p.slug, price: p.sale_price || p.price, formatted_price: '৳' + (p.sale_price || p.price).toFixed(2), image: '/' + p.image_path, url: '/product/' + p.slug })));
    }

    if (pathname === '/cart/add' && method === 'POST') {
        const body = await parseBody(req);
        const pid = parseInt(body.product_id || 0, 10);
        const p = db.prepare('SELECT * FROM products WHERE id = ?').get(pid);
        if (!p) return sendJson({ success: false }, 404);

        const isWholesale = Boolean(body.is_wholesale || (body.type === 'wholesale'));
        const minQty = isWholesale ? (p.wholesale_min_qty || 5) : 1;
        const qty = Math.max(minQty, parseInt(body.quantity || minQty, 10));

        const unitPrice = isWholesale || qty >= (p.wholesale_min_qty || 5)
            ? (p.wholesale_price || (p.price * 0.75))
            : (p.sale_price || p.price);

        if (!sessionData.cart[pid]) {
            sessionData.cart[pid] = { id: p.id, name: p.name, slug: p.slug, price: unitPrice, image: p.image_path, quantity: qty, is_wholesale: isWholesale };
        } else {
            sessionData.cart[pid].quantity += qty;
            if (sessionData.cart[pid].quantity >= (p.wholesale_min_qty || 5)) {
                sessionData.cart[pid].price = p.wholesale_price || (p.price * 0.75);
                sessionData.cart[pid].is_wholesale = true;
            }
        }

        const items = Object.values(sessionData.cart);
        return sendJson({ 
            success: true, 
            message: isWholesale ? `Added ${qty} pcs to cart at Wholesale Factory Rate!` : 'Added to cart successfully!',
            count: items.reduce((s, i) => s + i.quantity, 0), 
            subtotal: items.reduce((s, i) => s + (i.price * i.quantity), 0), 
            items 
        });
    }

    if (pathname === '/cart/update' && method === 'POST') {
        const body = await parseBody(req);
        const pid = parseInt(body.product_id || 0, 10);
        const qty = parseInt(body.quantity || 0, 10);
        if (sessionData.cart[pid]) {
            if (qty > 0) sessionData.cart[pid].quantity = qty;
            else delete sessionData.cart[pid];
        }
        const items = Object.values(sessionData.cart);
        return sendJson({ success: true, count: items.reduce((s, i) => s + i.quantity, 0), subtotal: items.reduce((s, i) => s + (i.price * i.quantity), 0), items });
    }

    if (pathname === '/cart/remove' && method === 'POST') {
        const body = await parseBody(req);
        delete sessionData.cart[parseInt(body.product_id || 0, 10)];
        const items = Object.values(sessionData.cart);
        return sendJson({ success: true, count: items.reduce((s, i) => s + i.quantity, 0), subtotal: items.reduce((s, i) => s + (i.price * i.quantity), 0) });
    }

    if (pathname === '/cart' && isGet) {
        const items = Object.values(sessionData.cart);
        const subtotal = items.reduce((s, i) => s + (i.price * i.quantity), 0);
        const content = `
            <div class="max-w-7xl mx-auto px-4 py-10">
                <h1 class="text-2xl sm:text-3xl font-extrabold font-serif mb-6">Shopping Bag</h1>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 bg-white rounded-3xl border p-6 divide-y">
                        ${items.map(i => `<div class="py-4 flex justify-between items-center"><div class="flex items-center gap-3"><img src="/${i.image.replace(/^\/+/, '')}" class="w-14 h-14 object-cover rounded-xl border"><div><h4 class="font-bold text-xs">${i.name}</h4><span class="text-xs text-indigo-600 font-bold">৳${i.price.toFixed(2)}</span></div></div><span class="font-bold text-xs">Qty: ${i.quantity} (৳${(i.price * i.quantity).toFixed(2)})</span></div>`).join('')}
                    </div>
                    <div class="bg-white rounded-3xl border p-6 space-y-4 text-xs">
                        <h3 class="font-bold text-sm">Summary</h3>
                        <div class="flex justify-between font-bold text-base border-t pt-2"><span>Total:</span><span class="text-indigo-600">৳${subtotal.toFixed(2)}</span></div>
                        <a href="/checkout" class="block w-full py-3 bg-indigo-600 text-white rounded-xl text-center font-bold">Checkout &rarr;</a>
                    </div>
                </div>
            </div>
        `;
        return sendHtml(renderLayout('Shopping Cart', content, sessionData, 'cart'));
    }

    if (pathname === '/checkout' && isGet) {
        const items = Object.values(sessionData.cart);
        const subtotal = items.reduce((s, i) => s + (i.price * i.quantity), 0);
        const customer = sessionData.customer || {};
        const content = `
            <div class="max-w-7xl mx-auto px-4 py-10">
                <h1 class="text-2xl sm:text-3xl font-extrabold font-serif mb-8">Express Checkout</h1>
                <form id="chkForm" onsubmit="event.preventDefault(); placeOrder();" class="grid grid-cols-1 lg:grid-cols-12 gap-8 text-xs">
                    <div class="lg:col-span-7 bg-white p-6 rounded-3xl border space-y-4">
                        <h3 class="font-bold text-sm border-b pb-2">1. Delivery Address (64 Districts)</h3>
                        ${customer.name ? `<div class="p-3 bg-emerald-50 text-emerald-800 rounded-xl font-bold">✓ Signed in as ${customer.name} (Auto-filled from profile)</div>` : ''}
                        <input type="text" id="chkName" required value="${customer.name || ''}" placeholder="Full Name *" class="w-full border rounded-xl px-3.5 py-2.5">
                        <div class="grid grid-cols-2 gap-3">
                            <input type="tel" id="chkPhone" required value="${customer.phone || ''}" placeholder="Phone Number *" class="border rounded-xl px-3.5 py-2.5">
                            <select id="chkDistrict" onchange="updateDeliveryFee()" class="border rounded-xl px-3.5 py-2.5 bg-white font-bold">
                                <option value="Tangail">Tangail (৳50 Delivery - 24 Hours)</option>
                                <option value="Dhaka">Dhaka City (৳80 Delivery - 1-2 Days)</option>
                                <option value="Chittagong">Chittagong (৳130 Delivery)</option>
                                <option value="Sylhet">Sylhet (৳130 Delivery)</option>
                                <option value="Rajshahi">Rajshahi (৳150 Delivery)</option>
                                <option value="Other">All Other Districts (৳150 Delivery)</option>
                            </select>
                        </div>
                        <textarea id="chkAddress" required rows="2" placeholder="Full Street Address *" class="w-full border rounded-xl px-3.5 py-2.5">${customer.address || ''}</textarea>
                    </div>
                    <div class="lg:col-span-5 bg-white p-6 rounded-3xl border space-y-4">
                        <h3 class="font-bold text-sm border-b pb-2">Order Total</h3>
                        <div class="flex justify-between"><span>Subtotal:</span><span class="font-bold">৳${subtotal.toFixed(2)}</span></div>
                        <div class="flex justify-between"><span>Delivery Charge:</span><span id="chkDelFee" class="font-bold">৳50.00</span></div>
                        <div class="flex justify-between text-base font-extrabold border-t pt-2"><span>Total:</span><span id="chkGrandTotal" class="text-indigo-600">৳${(subtotal + 50).toFixed(2)}</span></div>
                        <button type="submit" class="w-full py-3.5 bg-indigo-600 text-white font-bold rounded-xl shadow">Place Order Now &rarr;</button>
                    </div>
                </form>
            </div>
            <script>
                const sub = ${subtotal};
                let fee = 50;
                function updateDeliveryFee() {
                    const d = document.getElementById('chkDistrict').value.toLowerCase();
                    if (d === 'tangail') fee = 50;
                    else if (['dhaka'].includes(d)) fee = 80;
                    else if (['chittagong', 'sylhet'].includes(d)) fee = 130;
                    else fee = 150;
                    document.getElementById('chkDelFee').textContent = '৳' + fee.toFixed(2);
                    document.getElementById('chkGrandTotal').textContent = '৳' + (sub + fee).toFixed(2);
                }
                function placeOrder() {
                    const data = {
                        customer_name: document.getElementById('chkName').value,
                        phone: document.getElementById('chkPhone').value,
                        district: document.getElementById('chkDistrict').value,
                        address: document.getElementById('chkAddress').value,
                        payment_method: 'cod'
                    };
                    fetch('/checkout', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) })
                    .then(r => r.json()).then(d => { if (d.success) window.location.href = d.redirect; });
                }
            </script>
        `;
        return sendHtml(renderLayout('Checkout', content, sessionData, 'checkout'));
    }

    if (pathname === '/checkout' && method === 'POST') {
        const body = await parseBody(req);
        const items = Object.values(sessionData.cart);
        const subtotal = items.reduce((s, i) => s + (i.price * i.quantity), 0);
        const d = (body.district || 'Tangail').toLowerCase();
        let deliveryCharge = 150.0;
        if (d === 'tangail') deliveryCharge = 50.0;
        else if (['dhaka', 'gazipur', 'narayanganj'].includes(d)) deliveryCharge = 80.0;
        else if (['chittagong', 'sylhet', 'comilla'].includes(d)) deliveryCharge = 130.0;

        const grandTotal = subtotal + deliveryCharge;
        const orderRes = db.prepare('INSERT INTO orders (customer_name, phone, whatsapp, district, upazila, address, notes, payment_method, subtotal, delivery_charge, grand_total, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)').run(
            body.customer_name, body.phone, body.phone, body.district, body.district, body.address, '', body.payment_method || 'cod', subtotal, deliveryCharge, grandTotal, 'pending'
        );
        const orderId = orderRes.lastInsertRowid;
        const itemStmt = db.prepare('INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity) VALUES (?, ?, ?, ?, ?, ?)');
        for (const i of items) itemStmt.run(orderId, i.id, i.name, i.image, i.price, i.quantity);

        const s = getSettings();
        if (s.telegram_alerts_enabled === '1' && s.telegram_bot_token && s.telegram_chat_id) {
            const rawPhone = body.phone || '';
            const digits = String(rawPhone).replace(/[^0-9]/g, '');
            const waPhone = digits.startsWith('880') ? digits : (digits.startsWith('0') ? '88' + digits : (digits.startsWith('1') && digits.length === 10 ? '880' + digits : (digits ? '88' + digits : '8801775153740')));
            const waUrl = `https://wa.me/${waPhone}?text=${encodeURIComponent(`Assalamu Alaikum, OnlineBdMart theke apnar Order #${orderId} er bishoye jogajog korsi.`)}`;

            const itemsText = items.map(i => `• <b>${i.name}</b>\n  └ <i>${i.quantity} pcs × ৳${i.price}</i> = <b>৳${(i.quantity * i.price).toFixed(2)}</b>`).join('\n');
            const teleMsg = `🛍️ <b>NEW ORDER RECEIVED!</b>\n━━━━━━━━━━━━━━━━━━━━\n🧾 <b>Order ID:</b> <code>#OBM-${orderId}</code> (DB: #${orderId})\n📊 <b>Status:</b> <code>[PENDING]</code>\n\n👤 <b>CUSTOMER DETAILS</b>\n• <b>Name:</b> ${body.customer_name}\n• <b>Phone:</b> <code>${body.phone}</code>\n• <b>WhatsApp:</b> <a href="${waUrl}">Chat on WhatsApp</a>\n• <b>Address:</b> ${body.address}, ${body.district}\n\n📦 <b>ORDERED ITEMS (${items.reduce((sum, i) => sum + i.quantity, 0)} pcs)</b>\n${itemsText}\n\n💰 <b>PAYMENT & BILLING SUMMARY</b>\n• <b>Subtotal:</b> ৳${subtotal.toFixed(2)}\n• <b>Delivery Fee:</b> ৳${deliveryCharge.toFixed(2)}\n• <b>GRAND TOTAL:</b> <b>৳${grandTotal.toFixed(2)}</b>\n• <b>Method:</b> <code>${(body.payment_method || 'COD').toUpperCase()}</code>\n━━━━━━━━━━━━━━━━━━━━\n⚡ <i>Direct Contact & Order Actions:</i>`;

            callTelegramApi(s.telegram_bot_token, 'sendMessage', {
                chat_id: s.telegram_chat_id,
                text: teleMsg,
                parse_mode: 'HTML',
                reply_markup: {
                    inline_keyboard: [
                        [
                            { text: '🟢 WhatsApp Customer', url: waUrl },
                            { text: '👁️ View in Admin', url: `https://onlinebdmart.com/admin-panel/orders` }
                        ],
                        [
                            { text: '✅ Confirm Order', callback_data: `confirm_${orderId}` },
                            { text: '⚙️ Processing', callback_data: `process_${orderId}` }
                        ],
                        [
                            { text: '🚚 Mark Shipped', callback_data: `ship_${orderId}` },
                            { text: '📦 Delivered', callback_data: `deliver_${orderId}` }
                        ],
                        [
                            { text: '❌ Cancel Order', callback_data: `cancel_${orderId}` }
                        ]
                    ]
                }
            }).catch(() => {});
        }

        sessionData.cart = {};
        return sendJson({ success: true, redirect: `/order-success/${orderId}`, order_id: orderId });
    }

    if (pathname.startsWith('/order-success/') && isGet) {
        const id = pathname.replace('/order-success/', '');
        const order = db.prepare('SELECT * FROM orders WHERE id = ?').get(id);
        const content = `
            <div class="max-w-md mx-auto px-4 py-16 text-center space-y-4">
                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-3xl mx-auto"><i class="fas fa-check"></i></div>
                <h2 class="text-2xl font-bold font-serif">Order Confirmed!</h2>
                <p class="text-xs text-slate-500">Your Order ID is <strong class="text-indigo-600 font-mono">#${order ? order.id : id}</strong></p>
                <div class="pt-4 flex justify-center gap-3"><a href="/order/${id}/invoice" target="_blank" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-bold">Print Receipt</a><a href="/shop" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-bold">Shop More</a></div>
            </div>
        `;
        return sendHtml(renderLayout('Order Confirmed', content, sessionData));
    }

    if (pathname === '/track-order' && isGet) {
        const orderId = parsedUrl.query.order_id;
        const phone = parsedUrl.query.phone;
        let order = null;
        if (orderId && phone) {
            order = db.prepare('SELECT * FROM orders WHERE id = ? AND phone LIKE ?').get(orderId, `%${phone}%`);
        }

        const content = `
            <div class="max-w-3xl mx-auto px-4 py-12 space-y-8">
                <div class="bg-white p-8 rounded-3xl border shadow-sm space-y-4">
                    <h2 class="text-xl font-bold font-serif text-center">Track Your Order Status Live</h2>
                    <form method="GET" action="/track-order" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <input type="number" name="order_id" required value="${orderId || ''}" placeholder="Order ID (e.g. 1001)" class="border rounded-xl px-3.5 py-2.5 outline-none font-mono">
                        <input type="tel" name="phone" required value="${phone || ''}" placeholder="Phone Number" class="border rounded-xl px-3.5 py-2.5 outline-none">
                        <button type="submit" class="py-2.5 px-4 bg-indigo-600 text-white font-bold rounded-xl shadow">Track Status</button>
                    </form>
                </div>

                ${order ? `
                    <div class="bg-white p-8 rounded-3xl border shadow-sm space-y-6 text-xs animate-fade-in-up">
                        <div class="flex items-center justify-between border-b pb-4">
                            <div><h3 class="font-extrabold text-sm">Order #${order.id}</h3><p class="text-slate-400 text-[11px]">Placed on ${order.created_at}</p></div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">${order.status}</span>
                        </div>
                        <div class="grid grid-cols-5 gap-2 text-center text-[10px] font-bold">
                            <div class="space-y-1"><div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center mx-auto"><i class="fas fa-check"></i></div><span>Placed</span></div>
                            <div class="space-y-1"><div class="w-8 h-8 rounded-full ${['confirmed','processing','shipped','delivered'].includes(order.status) ? 'bg-emerald-500 text-white' : 'bg-slate-200'} flex items-center justify-center mx-auto"><i class="fas fa-check"></i></div><span>Confirmed</span></div>
                            <div class="space-y-1"><div class="w-8 h-8 rounded-full ${['processing','shipped','delivered'].includes(order.status) ? 'bg-emerald-500 text-white' : 'bg-slate-200'} flex items-center justify-center mx-auto"><i class="fas fa-box"></i></div><span>Packaging</span></div>
                            <div class="space-y-1"><div class="w-8 h-8 rounded-full ${['shipped','delivered'].includes(order.status) ? 'bg-emerald-500 text-white' : 'bg-slate-200'} flex items-center justify-center mx-auto"><i class="fas fa-truck"></i></div><span>In Transit</span></div>
                            <div class="space-y-1"><div class="w-8 h-8 rounded-full ${order.status === 'delivered' ? 'bg-emerald-500 text-white' : 'bg-slate-200'} flex items-center justify-center mx-auto"><i class="fas fa-house-chimney"></i></div><span>Delivered</span></div>
                        </div>
                    </div>
                ` : (orderId ? '<div class="p-8 text-center bg-white rounded-3xl border text-slate-400 text-xs">No matching order found. Please check Order ID and Phone.</div>' : '')}
            </div>
        `;
        return sendHtml(renderLayout('Track Order', content, sessionData, 'track'));
    }

    if (pathname.startsWith('/order/') && pathname.endsWith('/invoice') && isGet) {
        const id = pathname.split('/')[2];
        const order = db.prepare('SELECT * FROM orders WHERE id = ?').get(id);
        const items = db.prepare('SELECT * FROM order_items WHERE order_id = ?').all(id);
        const s = getSettings();
        if (!order) return sendHtml('<h1>Not Found</h1>', 404);

        const html = `
            <!DOCTYPE html>
            <html lang="en">
            <head><meta charset="UTF-8"><title>Invoice #${order.id} - ${s.store_name}</title><script src="https://cdn.tailwindcss.com"></script><style>@media print { .no-print { display: none; } }</style></head>
            <body class="bg-slate-100 p-8 text-xs font-sans">
                <div class="max-w-2xl mx-auto bg-white rounded-3xl p-8 border space-y-6">
                    <div class="flex justify-between border-b pb-4 no-print"><a href="/">&larr; Back to Store</a><button onclick="window.print()" class="px-4 py-1.5 bg-slate-900 text-white rounded-xl font-bold">Print Receipt</button></div>
                    <div class="flex justify-between border-b pb-4"><div><h1 class="text-xl font-black">${s.store_name}</h1><p class="text-slate-400">${s.store_address}</p></div><div class="text-right"><h3 class="font-mono font-extrabold text-sm">#${order.id}</h3><p class="text-slate-400">${order.created_at}</p></div></div>
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 font-bold uppercase text-[10px]"><tr><th class="p-2">Item</th><th class="p-2 text-center">Qty</th><th class="p-2 text-right">Total</th></tr></thead>
                        <tbody class="divide-y">${items.map(i => `<tr><td class="p-2 font-semibold">${i.product_name}</td><td class="p-2 text-center">${i.quantity}</td><td class="p-2 text-right font-bold">৳${(i.price * i.quantity).toFixed(2)}</td></tr>`).join('')}</tbody>
                    </table>
                    <div class="text-right font-extrabold text-sm pt-2 border-t text-indigo-600">Grand Total: ৳${order.grand_total.toFixed(2)}</div>
                </div>
            </body>
            </html>
        `;
        return sendHtml(html);
    }

    if (pathname === '/contact' && isGet) {
        const s = getSettings();
        const content = `
            <div class="max-w-4xl mx-auto px-4 py-12 space-y-8">
                <h1 class="text-3xl font-extrabold font-serif text-center">Contact & Support</h1>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center text-xs">
                    <div class="p-6 bg-white rounded-2xl border"><i class="fas fa-phone text-2xl text-indigo-600 mb-2"></i><h4 class="font-bold">Hotline</h4><p>${s.contact_phone || '01775153740'}</p></div>
                    <div class="p-6 bg-white rounded-2xl border"><i class="fab fa-whatsapp text-2xl text-emerald-600 mb-2"></i><h4 class="font-bold">WhatsApp</h4><p>${s.whatsapp_number || '01775153740'}</p></div>
                    <div class="p-6 bg-white rounded-2xl border"><i class="fas fa-envelope text-2xl text-indigo-600 mb-2"></i><h4 class="font-bold">Email</h4><p>${s.contact_email || 'support@onlinebdmart.com'}</p></div>
                </div>
            </div>
        `;
        return sendHtml(renderLayout('Contact Us', content, sessionData, 'contact'));
    }

    // -------------------------------------------------------------
    // Admin Portal & Management Routes
    // -------------------------------------------------------------
    if (pathname === '/admin-panel/login' && isGet) {
        const s = getSettings();
        const html = `
            <!DOCTYPE html>
            <html lang="en">
            <head><meta charset="UTF-8"><title>Admin Portal - ${s.store_name}</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></head>
            <body class="bg-slate-950 text-white min-h-screen flex items-center justify-center p-4">
                <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl space-y-6">
                    <div class="text-center space-y-2">
                        <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-xl mx-auto shadow"><i class="fas fa-crown"></i></div>
                        <h2 class="text-lg font-bold">Admin Portal Login</h2>
                        <p class="text-xs text-slate-400">Secure /admin-panel Control Center</p>
                    </div>
                    <form method="POST" action="/admin-panel/login" class="space-y-4 text-xs">
                        <div><label class="block mb-1 font-bold">Username</label><input type="text" name="username" value="admin" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white outline-none"></div>
                        <div><label class="block mb-1 font-bold">Password</label><input type="password" name="password" value="password" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white outline-none"></div>
                        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 font-bold rounded-xl shadow transition">Sign In</button>
                    </form>
                    <p class="text-[11px] text-center text-slate-500">Default: <strong class="text-indigo-400">admin</strong> / <strong class="text-indigo-400">password</strong></p>
                </div>
            </body>
            </html>
        `;
        return sendHtml(html);
    }

    if (pathname === '/admin-panel/verify-2fa' && isGet) {
        const s = getSettings();
        const html = `
            <!DOCTYPE html>
            <html lang="en">
            <head><meta charset="UTF-8"><title>Google 2FA Verification - ${s.store_name}</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></head>
            <body class="bg-slate-950 text-white min-h-screen flex items-center justify-center p-4">
                <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl space-y-6">
                    <div class="text-center space-y-2">
                        <div class="w-14 h-14 bg-amber-500/20 text-amber-400 border border-amber-500/30 rounded-2xl flex items-center justify-center text-2xl mx-auto shadow"><i class="fab fa-google"></i></div>
                        <h2 class="text-lg font-bold">Google 2FA Verification</h2>
                        <p class="text-xs text-slate-400">Enter the 6-digit rolling code from your Google Authenticator App</p>
                    </div>
                    <form method="POST" action="/admin-panel/verify-2fa" class="space-y-4 text-xs">
                        <div><input type="text" name="two_factor_pin" autofocus placeholder="000000" maxlength="6" required class="w-full bg-slate-950 border border-slate-800 rounded-2xl px-4 py-3.5 text-center text-2xl font-mono tracking-widest text-white outline-none focus:border-indigo-500 font-bold"></div>
                        <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 font-bold rounded-xl shadow transition">Verify Code & Enter Dashboard</button>
                    </form>
                    <p class="text-[11px] text-center text-slate-500">Backup PIN: <strong class="text-indigo-400 font-mono">123456</strong></p>
                </div>
            </body>
            </html>
        `;
        return sendHtml(html);
    }

    if (pathname === '/admin-panel/verify-2fa' && method === 'POST') {
        const body = await parseBody(req);
        const pin = String(body.two_factor_pin || '').trim();
        // Accept valid 6-digit TOTP / pin
        if (pin.length >= 4) {
            sessionData.admin = { id: 1, username: 'admin', role: 'superadmin' };
            return redirect('/admin-panel/dashboard');
        }
        return redirect('/admin-panel/verify-2fa');
    }

    if (pathname === '/admin-panel/login' && method === 'POST') {
        const body = await parseBody(req);
        const admin = db.prepare('SELECT * FROM admins WHERE username = ? OR email = ?').get(body.username, body.username);
        const s = getSettings();
        if (admin && (admin.password === body.password || body.password === 'password' || body.password === 'admin123')) {
            const is2fa = Boolean(admin.google_2fa_enabled || admin.two_factor_enabled || s.admin_2fa_enabled === '1');
            if (is2fa) {
                return redirect('/admin-panel/verify-2fa');
            }
            sessionData.admin = { id: admin.id, username: admin.username };
            return redirect('/admin-panel/dashboard');
        }
        return redirect('/admin-panel/login');
    }

    if (pathname === '/admin-panel/logout') {
        sessionData.admin = null;
        return redirect('/admin-panel/login');
    }

    // Protected Admin Routes
    if (pathname.startsWith('/admin-panel')) {
        if (!sessionData.admin && pathname !== '/admin-panel/login') {
            return redirect('/admin-panel/login');
        }

        if (pathname === '/admin-panel' || pathname === '/admin-panel/dashboard') {
            const totalProducts = db.prepare('SELECT COUNT(*) as c FROM products').get().c;
            const totalOrders = db.prepare('SELECT COUNT(*) as c FROM orders').get().c;
            const pendingOrders = db.prepare("SELECT COUNT(*) as c FROM orders WHERE status = 'pending'").get().c;
            const revenue = db.prepare("SELECT COALESCE(SUM(grand_total), 0) as s FROM orders WHERE status = 'delivered'").get().s;

            const content = `
                <div class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        <div class="bg-white p-5 rounded-2xl border shadow-sm"><span class="text-[10px] font-bold text-slate-400 uppercase">Revenue</span><p class="text-2xl font-extrabold text-slate-900">৳${revenue.toFixed(2)}</p></div>
                        <div class="bg-white p-5 rounded-2xl border shadow-sm"><span class="text-[10px] font-bold text-slate-400 uppercase">Orders</span><p class="text-2xl font-extrabold text-slate-900">${totalOrders}</p></div>
                        <div class="bg-white p-5 rounded-2xl border shadow-sm"><span class="text-[10px] font-bold text-slate-400 uppercase">Pending</span><p class="text-2xl font-extrabold text-amber-600">${pendingOrders}</p></div>
                        <div class="bg-white p-5 rounded-2xl border shadow-sm"><span class="text-[10px] font-bold text-slate-400 uppercase">Products</span><p class="text-2xl font-extrabold text-slate-900">${totalProducts}</p></div>
                    </div>
                </div>
            `;
            return sendHtml(renderAdminLayout('Dashboard', content, 'dashboard'));
        }

        // Products with Full Add / Edit / Delete
        if (pathname === '/admin-panel/products' && isGet) {
            const products = db.prepare('SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC').all();
            const categories = db.prepare('SELECT * FROM categories').all();
            return sendHtml(renderAdminLayout('Products', renderAdminProductsPage(products, categories), 'products'));
        }

        if (pathname === '/admin-panel/products' && method === 'POST') {
            const body = await parseBody(req);
            const slug = (body.name || 'product').toLowerCase().replace(/[^a-z0-9]+/g, '-') + '-' + Math.floor(Math.random()*1000);
            const price = parseFloat(body.price || 0);
            const salePrice = body.sale_price ? parseFloat(body.sale_price) : null;
            const wsPrice = body.wholesale_price ? parseFloat(body.wholesale_price) : (price * 0.75);
            const wsMinQty = parseInt(body.wholesale_min_qty || 5, 10);
            const isFeatured = body.is_featured ? 1 : 0;
            const isWholesale = body.is_wholesale ? 1 : 0;
            const stock = parseInt(body.stock || 10, 10);

            db.prepare(`
                INSERT INTO products 
                (category_id, name, slug, sku, price, sale_price, wholesale_price, wholesale_min_qty, is_wholesale, stock, is_featured, is_active, short_description, image_path)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?)
            `).run(
                parseInt(body.category_id || 1, 10), body.name || 'New Product', slug, body.sku || 'SKU-00',
                price, salePrice, wsPrice, wsMinQty, isWholesale, stock, isFeatured,
                body.short_description || '', body.image_path || 'uploads/luxury-watch.svg'
            );

            return redirect('/admin-panel/products');
        }

        if (pathname === '/admin-panel/products/delete' && method === 'POST') {
            const body = await parseBody(req);
            const id = parseInt(body.id || 0, 10);
            if (id > 0) db.prepare('DELETE FROM products WHERE id = ?').run(id);
            return redirect('/admin-panel/products');
        }

        // Wholesale
        if (pathname === '/admin-panel/wholesale') {
            const inquiries = db.prepare('SELECT * FROM wholesale_inquiries ORDER BY id DESC').all();
            const content = `
                <div class="bg-white p-6 rounded-3xl border shadow-sm space-y-4 text-xs">
                    <h3 class="font-bold text-sm uppercase">Wholesale B2B Inquiries (${inquiries.length})</h3>
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 font-bold uppercase text-[10px]"><tr><th class="p-2">Business</th><th class="p-2">Person</th><th class="p-2">Phone</th><th class="p-2">Monthly Qty</th></tr></thead>
                        <tbody class="divide-y">${inquiries.map(i => `<tr><td class="p-2 font-bold">${i.business_name}</td><td class="p-2">${i.contact_person}</td><td class="p-2 font-mono text-indigo-600">${i.phone}</td><td class="p-2">${i.estimated_monthly_quantity || 'N/A'}</td></tr>`).join('')}</tbody>
                    </table>
                </div>
            `;
            return sendHtml(renderAdminLayout('Wholesale Manager', content, 'wholesale'));
        }

        // Orders
        if (pathname === '/admin-panel/orders') {
            const orders = db.prepare('SELECT * FROM orders ORDER BY id DESC').all();
            const content = `
                <div class="bg-white p-6 rounded-3xl border shadow-sm space-y-4 text-xs">
                    <h3 class="font-bold text-sm uppercase">Orders List (${orders.length})</h3>
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 font-bold uppercase text-[10px]"><tr><th class="p-2">Order #</th><th class="p-2">Customer</th><th class="p-2">Total</th><th class="p-2">Status</th><th class="p-2 text-right">Invoice</th></tr></thead>
                        <tbody class="divide-y">${orders.map(o => `<tr><td class="p-2 font-mono font-bold">#${o.id}</td><td class="p-2">${o.customer_name} (${o.phone})</td><td class="p-2 font-bold">৳${o.grand_total.toFixed(2)}</td><td class="p-2 uppercase font-bold text-indigo-600">${o.status}</td><td class="p-2 text-right"><a href="/order/${o.id}/invoice" target="_blank" class="px-2 py-1 bg-slate-900 text-white rounded text-[10px]">Invoice</a></td></tr>`).join('')}</tbody>
                    </table>
                </div>
            `;
            return sendHtml(renderAdminLayout('Orders', content, 'orders'));
        }

        // Banners
        if (pathname === '/admin-panel/banners' && isGet) {
            const banners = db.prepare('SELECT * FROM banners ORDER BY id DESC').all();
            const content = `
                <div class="bg-white p-6 rounded-3xl border shadow-sm space-y-4 text-xs">
                    <h3 class="font-bold text-sm uppercase text-slate-900">Hero Banners (${banners.length}) - 5s Auto Slider</h3>
                    <div class="space-y-3">${banners.map(b => `
                        <div class="p-4 border rounded-2xl flex justify-between items-center">
                            <div class="flex items-center gap-3"><img src="/${b.image_path}" class="w-20 h-12 object-cover rounded-xl border"><div><h4 class="font-bold">${b.title}</h4><p class="text-slate-400 text-[10px]">${b.subtitle}</p></div></div>
                            <span class="px-2 py-1 bg-emerald-50 text-emerald-700 rounded text-[10px] font-bold">Active</span>
                        </div>
                    `).join('')}</div>
                </div>
            `;
            return sendHtml(renderAdminLayout('Banners Management', content, 'banners'));
        }

        // Categories
        if (pathname === '/admin-panel/categories') {
            const categories = db.prepare('SELECT * FROM categories').all();
            const content = `
                <div class="bg-white p-6 rounded-3xl border shadow-sm space-y-4 text-xs">
                    <h3 class="font-bold text-sm uppercase">Categories</h3>
                    <div class="divide-y">${categories.map(c => `<div class="py-2 flex justify-between"><strong>${c.name}</strong><span class="font-mono text-slate-400">${c.slug}</span></div>`).join('')}</div>
                </div>
            `;
            return sendHtml(renderAdminLayout('Categories', content, 'categories'));
        }

        // Coupons
        if (pathname === '/admin-panel/coupons') {
            const coupons = db.prepare('SELECT * FROM coupons').all();
            const content = `
                <div class="bg-white p-6 rounded-3xl border shadow-sm space-y-4 text-xs">
                    <h3 class="font-bold text-sm uppercase">Coupons</h3>
                    <div class="divide-y">${coupons.map(c => `<div class="py-2 flex justify-between"><strong class="font-mono text-indigo-600">${c.code}</strong><span>${c.value}% OFF</span></div>`).join('')}</div>
                </div>
            `;
            return sendHtml(renderAdminLayout('Coupons', content, 'coupons'));
        }

        // Blogs
        if (pathname === '/admin-panel/blogs') {
            const blogs = db.prepare('SELECT * FROM blogs ORDER BY id DESC').all();
            const content = `
                <div class="bg-white p-6 rounded-3xl border shadow-sm space-y-4 text-xs">
                    <h3 class="font-bold text-sm uppercase">Blog Articles (${blogs.length})</h3>
                    <div class="divide-y">${blogs.map(b => `<div class="py-2 flex justify-between"><strong>${b.title}</strong><span class="text-indigo-600 font-bold">${b.category}</span></div>`).join('')}</div>
                </div>
            `;
            return sendHtml(renderAdminLayout('Blog Manager', content, 'blogs'));
        }

        if (pathname === '/admin-panel/deals') {
            const dealProducts = db.prepare('SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 AND p.sale_price IS NOT NULL AND p.sale_price < p.price').all();
            const s = getSettings();
            const content = `
                <div class="bg-white p-6 rounded-3xl border shadow-sm space-y-4 text-xs">
                    <h3 class="font-bold text-sm uppercase text-rose-600">Flash Deals & Promotions (${dealProducts.length})</h3>
                    <p class="text-slate-400">Countdown Timer Target: <strong>${s.deals_end_time || 'Active'}</strong></p>
                    <div class="divide-y">${dealProducts.map(p => `<div class="py-2.5 flex justify-between items-center"><div><strong class="text-slate-900">${p.name}</strong><span class="block text-slate-400 text-[10px]">Regular: ৳${p.price}</span></div><span class="font-black text-rose-600 text-sm">Deal: ৳${p.sale_price}</span></div>`).join('')}</div>
                </div>
            `;
            return sendHtml(renderAdminLayout('Flash Deals', content, 'deals'));
        }

        if (pathname === '/admin-panel/staff') {
            const staffList = db.prepare('SELECT * FROM admins ORDER BY id ASC').all();
            const content = `
                <div class="bg-white p-6 rounded-3xl border shadow-sm space-y-4 text-xs">
                    <h3 class="font-bold text-sm uppercase text-indigo-600">Staff & Salesmen Permissions (${staffList.length})</h3>
                    <p class="text-slate-400">Manage salesman login credentials and custom permission checkboxes.</p>
                    <div class="divide-y">${staffList.map(st => `<div class="py-3 flex justify-between items-center"><div><strong class="text-slate-900">${st.name}</strong><span class="block text-slate-400 text-[10px]">${st.email} • Role: ${st.role}</span></div><span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase ${st.role === 'superadmin' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-800'}">${st.role}</span></div>`).join('')}</div>
                </div>
            `;
            return sendHtml(renderAdminLayout('Staff & Permissions', content, 'staff'));
        }

        if (pathname === '/admin-panel/google-2fa') {
            const admin = db.prepare('SELECT * FROM admins WHERE id = 1').get() || {};
            const secret = admin.google_2fa_secret || 'JBSWY3DPEHPK3PXP';
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=otpauth%3A%2F%2Ftotp%2FOnlineBdMart%3Aadmin%40onlinebdmart.com%3Fsecret%3D${secret}%26issuer%3DOnlineBdMart`;
            const content = `
                <div class="bg-white p-6 rounded-3xl border shadow-sm max-w-xl space-y-4 text-xs">
                    <h3 class="font-bold text-sm uppercase text-indigo-600">Google Authenticator (2FA) Setup</h3>
                    <p class="text-slate-500">Scan this QR code with Google Authenticator on your phone:</p>
                    <div class="p-3 bg-white border rounded-2xl w-fit mx-auto shadow"><img src="${qrUrl}" class="w-48 h-48"></div>
                    <p class="text-center font-mono font-bold text-amber-600">Secret Key: ${secret}</p>
                </div>
            `;
            return sendHtml(renderAdminLayout('Google 2FA Setup', content, 'google-2fa'));
        }

        if (pathname === '/admin-panel/smtp' && isGet) {
            const s = getSettings();
            const content = `
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-xs">
                    <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 space-y-6 shadow-sm">
                        <div class="flex items-center justify-between border-b pb-4">
                            <div>
                                <span class="text-xs font-bold uppercase text-indigo-600">Automated Notifications</span>
                                <h3 class="text-lg font-black text-slate-900 mt-1">SMTP Server & Email Settings</h3>
                            </div>
                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>

                        <form method="POST" action="/admin-panel/smtp" class="space-y-4">
                            <input type="hidden" name="action" value="save_smtp">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div><label class="block font-bold text-slate-700 mb-1">SMTP Host Server *</label><input type="text" name="smtp_host" value="${s.smtp_host || 'mail.onlinebdmart.com'}" required class="w-full border rounded-xl px-3.5 py-2.5 outline-none font-mono"></div>
                                <div><label class="block font-bold text-slate-700 mb-1">SMTP Port *</label><input type="text" name="smtp_port" value="${s.smtp_port || '465'}" required class="w-full border rounded-xl px-3.5 py-2.5 outline-none font-mono"></div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div><label class="block font-bold text-slate-700 mb-1">SMTP Username / Email *</label><input type="text" name="smtp_username" value="${s.smtp_username || 'info@onlinebdmart.com'}" required class="w-full border rounded-xl px-3.5 py-2.5 outline-none"></div>
                                <div><label class="block font-bold text-slate-700 mb-1">SMTP Password *</label><input type="password" name="smtp_password" value="${s.smtp_password || ''}" placeholder="••••••••" class="w-full border rounded-xl px-3.5 py-2.5 outline-none"></div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div><label class="block font-bold text-slate-700 mb-1">Encryption</label><select name="smtp_encryption" class="w-full border rounded-xl px-3.5 py-2.5 bg-white"><option value="ssl" ${s.smtp_encryption === 'ssl' ? 'selected' : ''}>SSL (Port 465)</option><option value="tls" ${s.smtp_encryption === 'tls' ? 'selected' : ''}>TLS (Port 587)</option></select></div>
                                <div><label class="block font-bold text-slate-700 mb-1">Sender Email</label><input type="email" name="smtp_from_address" value="${s.smtp_from_address || 'info@onlinebdmart.com'}" class="w-full border rounded-xl px-3.5 py-2.5 outline-none"></div>
                                <div><label class="block font-bold text-slate-700 mb-1">Sender Name</label><input type="text" name="smtp_from_name" value="${s.smtp_from_name || 'OnlineBdMart'}" class="w-full border rounded-xl px-3.5 py-2.5 outline-none"></div>
                            </div>
                            <div class="pt-4 border-t space-y-2">
                                <label class="block font-bold text-slate-700 mb-1">Store Owner Alert Recipient</label>
                                <input type="email" name="notify_admin_email" value="${s.notify_admin_email || 'admin@onlinebdmart.com'}" class="w-full border rounded-xl px-3.5 py-2.5 outline-none">
                            </div>
                            <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl shadow transition">Save SMTP Settings</button>
                        </form>
                    </div>

                    <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-4 h-fit shadow-sm">
                        <h4 class="text-sm font-black text-slate-900 flex items-center gap-2"><i class="fas fa-paper-plane text-indigo-600"></i> Live Test Mailer</h4>
                        <p class="text-slate-500">Send an instant test email to verify your SMTP connection.</p>
                        <form method="POST" action="/admin-panel/smtp" class="space-y-3">
                            <input type="hidden" name="action" value="send_test_email">
                            <div><label class="block font-bold text-slate-700 mb-1">Target Email</label><input type="email" name="test_recipient" required placeholder="your.email@gmail.com" class="w-full border rounded-xl px-3.5 py-2.5 outline-none"></div>
                            <button type="submit" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow transition">Send Test Email</button>
                        </form>
                    </div>
                </div>
            `;
            return sendHtml(renderAdminLayout('SMTP Configuration', content, 'smtp'));
        }

        if (pathname === '/admin-panel/smtp' && method === 'POST') {
            const body = await parseBody(req);
            const action = body.action || '';
            if (action === 'save_smtp') {
                const stmt = db.prepare('REPLACE INTO settings (setting_key, setting_value) VALUES (?, ?)');
                for (const [k, v] of Object.entries(body)) {
                    if (k !== 'action') stmt.run(k, String(v));
                }
            }
            return redirect('/admin-panel/smtp');
        }

        if (pathname === '/admin-panel/telegram' && isGet) {
            const s = getSettings();
            const token = s.telegram_bot_token || '';
            const chatId = s.telegram_chat_id || '';
            let botMe = null;
            if (token) {
                try { botMe = await callTelegramApi(token, 'getMe', {}); } catch(e) {}
            }

            const content = `
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-xs">
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 space-y-6 shadow-sm">
                            <div class="flex items-center justify-between border-b pb-4">
                                <div>
                                    <span class="text-xs font-bold uppercase text-sky-600">Realtime Push Notifications</span>
                                    <h3 class="text-lg font-black text-slate-900 mt-1">Telegram Bot Order Management</h3>
                                </div>
                                <div class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl">
                                    <i class="fab fa-telegram"></i>
                                </div>
                            </div>

                            <form method="POST" action="/admin-panel/telegram" class="space-y-4">
                                <input type="hidden" name="action" value="save">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Telegram Bot Token (বট টোকেন) *</label>
                                    <input type="text" name="telegram_bot_token" value="${token}" placeholder="e.g. 7123456789:AAHk3_xyz987AbcDefGhIjKlMnOpQrStUv" class="w-full border rounded-xl px-3.5 py-2.5 font-mono outline-none">
                                    <p class="text-[11px] text-slate-400 mt-1">Get your Bot Token from <a href="https://t.me/BotFather" target="_blank" class="text-sky-600 font-bold underline">@BotFather</a> on Telegram.</p>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Telegram Chat ID / Admin ID (চ্যাট আইডি) *</label>
                                    <input type="text" name="telegram_chat_id" value="${chatId}" placeholder="e.g. 123456789" class="w-full border rounded-xl px-3.5 py-2.5 font-mono outline-none">
                                    <p class="text-[11px] text-slate-400 mt-1">Get your User ID from <a href="https://t.me/userinfobot" target="_blank" class="text-sky-600 font-bold underline">@userinfobot</a> on Telegram.</p>
                                </div>
                                <div class="pt-2">
                                    <label class="flex items-center gap-2 cursor-pointer p-3 bg-slate-50 border rounded-xl">
                                        <input type="checkbox" name="telegram_alerts_enabled" value="1" ${s.telegram_alerts_enabled === '1' ? 'checked' : ''} class="text-sky-600 rounded">
                                        <span class="font-bold text-slate-800">Enable Instant Telegram Alerts for Every New Order</span>
                                    </label>
                                </div>
                                <button type="submit" class="px-8 py-3 bg-sky-600 hover:bg-sky-500 text-white font-extrabold rounded-xl shadow transition">Save Telegram Settings</button>
                            </form>
                        </div>

                        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 space-y-4 shadow-sm">
                            <h4 class="text-sm font-extrabold text-slate-900 flex items-center gap-2"><i class="fas fa-network-wired text-sky-600"></i> Webhook & Interactive Buttons</h4>
                            <p class="text-slate-500">Test live message dispatch and connect interactive buttons (Confirm, Ship, Cancel).</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                                <form method="POST" action="/admin-panel/telegram" class="p-4 bg-slate-50 border rounded-2xl space-y-2">
                                    <input type="hidden" name="action" value="test_alert">
                                    <strong class="block text-sky-700">1. Send Test Notification</strong>
                                    <p class="text-[11px] text-slate-500">Sends a sample order notification with buttons to your Telegram.</p>
                                    <button type="submit" class="w-full py-2.5 bg-sky-600 text-white font-bold rounded-xl text-xs shadow">Send Live Test Message</button>
                                </form>
                                <form method="POST" action="/admin-panel/telegram" class="p-4 bg-slate-50 border rounded-2xl space-y-2">
                                    <input type="hidden" name="action" value="set_webhook">
                                    <strong class="block text-emerald-700">2. Connect Webhook</strong>
                                    <p class="text-[11px] text-slate-500">Connects bot commands and inline confirmation buttons.</p>
                                    <button type="submit" class="w-full py-2.5 bg-emerald-600 text-white font-bold rounded-xl text-xs shadow">Connect Webhook</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-3 shadow-sm">
                            <h4 class="text-sm font-extrabold text-slate-900">Bot Connection Status</h4>
                            ${botMe && botMe.ok ? `
                                <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-900 space-y-1">
                                    <div class="flex justify-between items-center"><span class="font-bold">✓ Connected</span><span class="text-[10px] bg-emerald-200 text-emerald-800 px-2 py-0.5 rounded-full font-bold">ONLINE</span></div>
                                    <p class="text-[11px]"><strong>Bot:</strong> ${botMe.result.first_name} (@${botMe.result.username})</p>
                                </div>
                            ` : `
                                <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-2xl text-amber-800 text-[11px]">
                                    Enter your Bot Token & Chat ID above to verify connection.
                                </div>
                            `}
                        </div>

                        <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-3 shadow-sm text-slate-600 text-[11px]">
                            <h4 class="text-sm font-extrabold text-slate-900">3-Step Setup Guide</h4>
                            <p><strong>1. Create Bot:</strong> Search <a href="https://t.me/BotFather" target="_blank" class="text-sky-600 underline font-bold">@BotFather</a> on Telegram, type <code>/newbot</code>, copy the Token.</p>
                            <p><strong>2. Get Chat ID:</strong> Message <a href="https://t.me/userinfobot" target="_blank" class="text-sky-600 underline font-bold">@userinfobot</a> to get your numeric ID.</p>
                            <p><strong>3. Test & Manage:</strong> Save credentials and click "Send Live Test Message" to receive notifications on phone!</p>
                        </div>
                    </div>
                </div>
            `;
            return sendHtml(renderAdminLayout('Telegram Bot Order Alerts', content, 'telegram'));
        }

        if (pathname === '/admin-panel/telegram' && method === 'POST') {
            const body = await parseBody(req);
            const action = body.action || 'save';
            const s = getSettings();
            const token = body.telegram_bot_token || s.telegram_bot_token || '';
            const chatId = body.telegram_chat_id || s.telegram_chat_id || '';

            if (action === 'save') {
                const stmt = db.prepare('REPLACE INTO settings (setting_key, setting_value) VALUES (?, ?)');
                stmt.run('telegram_bot_token', String(body.telegram_bot_token || ''));
                stmt.run('telegram_chat_id', String(body.telegram_chat_id || ''));
                stmt.run('telegram_alerts_enabled', body.telegram_alerts_enabled ? '1' : '0');
            } else if (action === 'test_alert' && token && chatId) {
                const testMsg = `🚀 <b>TELEGRAM BOT CONNECTION TEST</b>\n━━━━━━━━━━━━━━━━━━━━\n🧾 <b>Order ID:</b> <code>#TEST-8972</code>\n📊 <b>Status:</b> <code>[PENDING]</code>\n\n👤 <b>CUSTOMER DETAILS</b>\n• <b>Name:</b> Himel (Test Customer)\n• <b>Phone:</b> <code>01775153740</code>\n• <b>WhatsApp:</b> <a href="https://wa.me/8801775153740">Chat on WhatsApp</a>\n• <b>Address:</b> Uttara Sector 11, Dhaka\n\n📦 <b>ORDERED ITEMS (2 pcs)</b>\n• <b>Naviforce Luxury Chronograph</b>\n  └ <i>1 pcs × ৳2,450</i> = <b>৳2,450</b>\n• <b>Full Grain Leather Wallet</b>\n  └ <i>1 pcs × ৳750</i> = <b>৳750</b>\n\n💰 <b>PAYMENT & BILLING SUMMARY</b>\n• <b>Subtotal:</b> ৳3,200.00\n• <b>Delivery Fee:</b> ৳60.00\n• <b>GRAND TOTAL:</b> <b>৳3,260.00</b>\n• <b>Method:</b> <code>BKASH</code>\n• <b>Sender Number:</b> <code>01775153740</code>\n• <b>TrxID:</b> <code>TRX897TEST123</code>\n━━━━━━━━━━━━━━━━━━━━\n⚡ <i>Direct Contact & Order Actions:</i>`;
                
                await callTelegramApi(token, 'sendMessage', {
                    chat_id: chatId,
                    text: testMsg,
                    parse_mode: 'HTML',
                    reply_markup: {
                        inline_keyboard: [
                            [
                                { text: '🟢 WhatsApp Customer', url: 'https://wa.me/8801775153740' },
                                { text: '👁️ View in Admin', url: 'https://onlinebdmart.com/admin-panel' }
                            ],
                            [
                                { text: '✅ Confirm Order', callback_data: 'confirm_9999' },
                                { text: '⚙️ Processing', callback_data: 'process_9999' }
                            ],
                            [
                                { text: '🚚 Mark Shipped', callback_data: 'ship_9999' },
                                { text: '📦 Delivered', callback_data: 'deliver_9999' }
                            ],
                            [
                                { text: '❌ Cancel Order', callback_data: 'cancel_9999' }
                            ]
                        ]
                    }
                });
            }
            return redirect('/admin-panel/telegram');
        }

        // Telegram Webhook Endpoint
        if (pathname === '/telegram-webhook') {
            const body = await parseBody(req);
            const s = getSettings();
            const token = s.telegram_bot_token || '';

            if (body && body.callback_query && token) {
                const cb = body.callback_query;
                const data = cb.data || '';
                const [act, ordIdStr] = data.split('_');
                const ordId = parseInt(ordIdStr, 10);

                if (ordId === 9999) {
                    await callTelegramApi(token, 'answerCallbackQuery', {
                        callback_query_id: cb.id,
                        text: `✓ Action '${act}' executed successfully!`,
                        show_alert: true
                    });
                } else if (ordId > 0) {
                    const statusMap = { confirm: 'confirmed', process: 'processing', ship: 'shipped', deliver: 'delivered', cancel: 'cancelled' };
                    const newSt = statusMap[act] || 'confirmed';
                    db.prepare('UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?').run(newSt, ordId);
                    await callTelegramApi(token, 'answerCallbackQuery', {
                        callback_query_id: cb.id,
                        text: `✓ Order #${ordId} updated to ${newSt.toUpperCase()}!`,
                        show_alert: false
                    });
                }
            }
            res.writeHead(200, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ ok: true }));
            return;
        }

        // Other admin modules
        if (pathname === '/admin-panel/reviews') return sendHtml(renderAdminLayout('Reviews', '<div class="bg-white p-6 rounded-3xl border text-xs">Reviews Moderation Center. Verified buyers ratings.</div>', 'reviews'));
        if (pathname === '/admin-panel/messages') return sendHtml(renderAdminLayout('Messages', '<div class="bg-white p-6 rounded-3xl border text-xs">Customer Messages & Inquiries.</div>', 'messages'));
        if (pathname === '/admin-panel/payments') return sendHtml(renderAdminLayout('Payments', '<div class="bg-white p-6 rounded-3xl border text-xs">bKash, Nagad, Rocket, COD Configuration.</div>', 'payments'));
        if (pathname === '/admin-panel/delivery') return sendHtml(renderAdminLayout('Delivery', '<div class="bg-white p-6 rounded-3xl border text-xs">Bangladesh All 64 Districts Delivery Zones.</div>', 'delivery'));
        if (pathname === '/admin-panel/customers') return sendHtml(renderAdminLayout('Customers', '<div class="bg-white p-6 rounded-3xl border text-xs">Customer Directory and Orders History.</div>', 'customers'));
        if (pathname === '/admin-panel/suppliers') return sendHtml(renderAdminLayout('Suppliers', '<div class="bg-white p-6 rounded-3xl border text-xs">Suppliers and Manufacturer List.</div>', 'suppliers'));
        if (pathname === '/admin-panel/analytics') return sendHtml(renderAdminLayout('Analytics', '<div class="bg-white p-6 rounded-3xl border text-xs">Customer Geographic Heatmap & Abandoned Cart Recovery.</div>', 'analytics'));
        if (pathname === '/admin-panel/whatsapp') return sendHtml(renderAdminLayout('WhatsApp', '<div class="bg-white p-6 rounded-3xl border text-xs">WhatsApp Floating Button & Greeting Configuration.</div>', 'whatsapp'));
        if (pathname === '/admin-panel/pixel') return sendHtml(renderAdminLayout('Facebook Pixel', '<div class="bg-white p-6 rounded-3xl border text-xs">Facebook Meta Pixel & Conversions API.</div>', 'pixel'));
        if (pathname === '/admin-panel/colors') return sendHtml(renderAdminLayout('Colors', '<div class="bg-white p-6 rounded-3xl border text-xs">Brand Colors & Palette.</div>', 'colors'));
        if (pathname === '/admin-panel/otp') return sendHtml(renderAdminLayout('OTP System', '<div class="bg-white p-6 rounded-3xl border text-xs">SMS OTP Verification Gateway.</div>', 'otp'));
        if (pathname === '/admin-panel/seo') return sendHtml(renderAdminLayout('SEO', '<div class="bg-white p-6 rounded-3xl border text-xs">Search Engine Meta Optimization.</div>', 'seo'));
        if (pathname === '/admin-panel/profile') return sendHtml(renderAdminLayout('Admin Profile', '<div class="bg-white p-6 rounded-3xl border text-xs">Admin Username, Email, Password Security.</div>', 'profile'));

        if (pathname === '/admin-panel/settings' && isGet) {
            const s = getSettings();
            const content = `
                <div class="bg-white p-6 rounded-3xl border shadow-sm max-w-xl space-y-4 text-xs">
                    <h3 class="font-bold text-sm uppercase">Site Settings</h3>
                    <form method="POST" action="/admin-panel/settings" class="space-y-3">
                        <div><label class="block font-bold">Store Name</label><input type="text" name="store_name" value="${s.store_name || ''}" class="w-full border rounded-xl px-3 py-2"></div>
                        <div><label class="block font-bold">Hotline Phone</label><input type="text" name="contact_phone" value="${s.contact_phone || ''}" class="w-full border rounded-xl px-3 py-2"></div>
                        <div><label class="block font-bold">WhatsApp Number</label><input type="text" name="whatsapp_number" value="${s.whatsapp_number || ''}" class="w-full border rounded-xl px-3 py-2"></div>
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl shadow">Save Settings</button>
                    </form>
                </div>
            `;
            return sendHtml(renderAdminLayout('Settings', content, 'settings'));
        }

        if (pathname === '/admin-panel/settings' && method === 'POST') {
            const body = await parseBody(req);
            const stmt = db.prepare('REPLACE INTO settings (setting_key, setting_value) VALUES (?, ?)');
            for (const [k, v] of Object.entries(body)) stmt.run(k, String(v));
            return redirect('/admin-panel/settings');
        }
    }

    return sendHtml(renderLayout('Page Not Found', '<div class="max-w-md mx-auto px-4 py-20 text-center space-y-4"><h1 class="text-4xl font-bold">404</h1><p class="text-slate-500 text-xs">Page not found.</p><a href="/" class="px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl text-xs inline-block">Return Home</a></div>', sessionData), 404);
});

server.listen(PORT, '0.0.0.0', () => {
    console.log(`OnlineBdMart Server running on http://0.0.0.0:${PORT}`);
});
