    </main>

    <!-- Mobile Bottom Navigation Bar -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 px-6 py-2.5 flex items-center justify-between text-[10px] font-bold text-slate-600 shadow-xl">
        <a href="index.php" class="flex flex-col items-center gap-1 <?= $currentPage === 'index.php' ? 'text-indigo-600' : '' ?>">
            <i class="fas fa-house text-base"></i> <span>Home</span>
        </a>
        <a href="shop.php" class="flex flex-col items-center gap-1 <?= $currentPage === 'shop.php' ? 'text-indigo-600' : '' ?>">
            <i class="fas fa-boxes-stacked text-base"></i> <span>Shop</span>
        </a>
        <a href="wholesale.php" class="flex flex-col items-center gap-1 text-amber-600 <?= $currentPage === 'wholesale.php' ? 'text-amber-700 font-extrabold' : '' ?>">
            <i class="fas fa-boxes-packing text-base"></i> <span>Wholesale</span>
        </a>
        <a href="track-order.php" class="flex flex-col items-center gap-1 text-emerald-600 font-extrabold">
            <i class="fas fa-truck-fast text-base"></i> <span>Track</span>
        </a>
        <button type="button" onclick="openCartDrawer()" class="flex flex-col items-center gap-1 relative text-indigo-600">
            <i class="fas fa-bag-shopping text-base"></i> <span>Bag</span>
            <span id="mobileBottomCartBadge" class="absolute -top-1.5 right-1 bg-amber-400 text-slate-950 text-[9px] font-black rounded-full h-3.5 min-w-[14px] px-0.5 flex items-center justify-center"><?= $cartCount ?></span>
        </button>
    </div>

    <!-- Floating WhatsApp Support Button -->
    <a href="https://wa.me/88<?= htmlspecialchars($whatsapp) ?>?text=<?= urlencode('Hello! I have a question about ' . $storeName) ?>" target="_blank" title="WhatsApp Support" class="fixed bottom-16 lg:bottom-6 right-6 z-30 w-14 h-14 bg-emerald-500 hover:bg-emerald-600 text-white rounded-full flex items-center justify-center shadow-2xl hover:scale-110 transition-all duration-300">
        <i class="fab fa-whatsapp text-2xl"></i>
    </a>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed bottom-20 lg:bottom-6 left-6 z-50 flex flex-col gap-2 max-w-sm"></div>

    <!-- Luxury Footer -->
    <footer class="bg-slate-950 text-white pt-16 pb-20 lg:pb-12 border-t border-slate-800 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 pb-12 border-b border-slate-800">
                <div class="space-y-4">
                    <?php if (!empty($s['store_logo']) && file_exists(__DIR__ . '/../' . ltrim($s['store_logo'], '/'))): ?>
                        <img src="/<?= ltrim($s['store_logo'], '/') ?>" alt="<?= htmlspecialchars($storeName) ?>" class="h-10 max-w-[170px] object-contain mb-2">
                    <?php else: ?>
                        <h3 class="text-xl font-extrabold"><?= htmlspecialchars($storeName) ?></h3>
                    <?php endif; ?>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed"><?= htmlspecialchars($s['footer_about_text'] ?? ($s['store_tagline'] ?? 'Premium Wholesale & Retail in Bangladesh.')) ?></p>
                    <p class="text-xs text-slate-500"><i class="fas fa-location-dot text-rose-500 mr-1"></i> <?= htmlspecialchars($s['store_address'] ?? 'Tangail, Bangladesh') ?></p>
                    <p class="text-xs text-slate-400"><i class="fas fa-phone text-emerald-400 mr-1"></i> <?= htmlspecialchars($s['store_phone'] ?? '01775153740') ?></p>
                </div>
                <div>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-slate-200 mb-4">Quick Navigation</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="index.php" class="hover:text-white">Home</a></li>
                        <li><a href="shop.php" class="hover:text-white">All Products</a></li>
                        <li><a href="wholesale.php" class="hover:text-white text-amber-400 font-bold">Wholesale / B2B Rate</a></li>
                        <li><a href="categories.php" class="hover:text-white">Categories</a></li>
                        <li><a href="deals.php" class="hover:text-white text-rose-400">Hot Deals %</a></li>
                        <li><a href="blog.php" class="hover:text-white">Buying Guides</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-slate-200 mb-4">Customer Care</h4>
                    <ul class="space-y-2 text-xs text-slate-400 font-semibold">
                        <li><a href="track-order.php" class="text-emerald-400 hover:underline">📦 Track Your Order</a></li>
                        <li><a href="contact.php" class="hover:text-white">📞 Help Center & Contact</a></li>
                        <li><a href="cart.php" class="hover:text-white">🛒 View Shopping Cart</a></li>
                        <li><a href="checkout.php" class="hover:text-white">⚡ Express Checkout</a></li>
                        <li><a href="admin-panel/login.php" class="hover:text-white text-slate-500">🔐 Admin Portal</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-slate-200 mb-4">Accepted Payments</h4>
                    <div class="flex flex-wrap gap-2 text-xs text-slate-300">
                        <span class="px-2.5 py-1 bg-slate-900 rounded font-bold text-emerald-400">Cash on Delivery</span>
                        <span class="px-2.5 py-1 bg-slate-900 rounded font-bold text-pink-400">bKash</span>
                        <span class="px-2.5 py-1 bg-slate-900 rounded font-bold text-orange-400">Nagad</span>
                        <span class="px-2.5 py-1 bg-slate-900 rounded font-bold text-purple-400">Rocket</span>
                        <span class="px-2.5 py-1 bg-slate-900 rounded font-bold text-cyan-400">Bank Transfer</span>
                    </div>
                </div>
            </div>
            <div class="pt-8 text-center text-xs text-slate-500">
                &copy; <?= date('Y') ?> <?= htmlspecialchars($s['footer_copyright'] ?? ($storeName . ' • Online Shopping Bangladesh. All rights reserved.')) ?>
            </div>
        </div>
    </footer>

    <!-- Zero-Dependency Native Client Engine -->
    <script>
        window.__SEARCH_PRODUCTS__ = <?= json_encode(array_map(function($p) {
            $price = ($p['sale_price'] && $p['sale_price'] > 0 && $p['sale_price'] < $p['price']) ? $p['sale_price'] : $p['price'];
            return [
                'id' => $p['id'],
                'name' => $p['name'],
                'slug' => $p['slug'],
                'price' => $price,
                'formatted_price' => '৳' . number_format($price, 2),
                'image' => '/' . ltrim($p['image_path'], '/'),
                'category' => $p['category'] ?? 'Accessories',
                'url' => 'product.php?slug=' . $p['slug']
            ];
        }, $allProducts)) ?>;

        window.__CART_DATA__ = {
            count: <?= $cartCount ?>,
            subtotal: <?= $subtotal ?>,
            items: <?= json_encode($cartItems) ?>
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
                            <a href="shop.php" onclick="closeCartDrawer()" class="mt-4 px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl text-xs shadow">Start Shopping</a>
                        </div>
                    `;
                } else {
                    let html = '';
                    data.items.forEach(item => {
                        html += `
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
                        `;
                    });
                    itemsList.innerHTML = html;
                }
            }
        }

        // 3. Add to Cart Function
        function addToCart(productId, quantity = 1, isWholesale = false) {
            fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'add', product_id: productId, quantity: quantity, is_wholesale: isWholesale })
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
            fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update', product_id: productId, quantity: qty })
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
            fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'remove', product_id: productId })
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
                            <a href="shop.php" onclick="closeWishlistDrawer()" class="mt-4 px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl text-xs">Browse Items</a>
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
            setTimeout(() => t.remove(), 3500);
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
                            <a href="shop.php?search=${encodeURIComponent(q)}" class="block text-center py-2 bg-slate-50 hover:bg-indigo-50 text-indigo-600 font-bold text-xs rounded-xl transition border border-slate-100">
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
