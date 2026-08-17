<?php
$pageTitle = 'Home - OnlineBdMart • Online Shopping Bangladesh';
require_once 'includes/header.php';

try {
    $db = getDB();
    $banners = $db->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY display_order ASC")->fetchAll();
    $featuredProducts = $db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 AND p.is_featured = 1 ORDER BY p.id DESC LIMIT 8")->fetchAll();
} catch (Throwable $e) {
    $banners = [];
    $featuredProducts = [];
}

// Fallback banners if database is empty
if (empty($banners)) {
    $banners = [
        [
            'title' => 'Exclusive Luxury Watches & Fashion Accessories',
            'subtitle' => 'Genuine Chronograph watches, full-grain leather wallets and designer sunglasses with 100% authentic quality guarantee.',
            'badge_text' => '✨ NEW ARRIVALS 2026',
            'button_text' => 'Shop Collection',
            'button_url' => 'shop.php',
            'image_path' => 'images/hero/hero-1.jpg'
        ],
        [
            'title' => 'B2B Wholesale Hub — Direct Factory Rates in Bangladesh',
            'subtitle' => 'Low MOQ from 5 pieces. Dedicated support for retail shops, Facebook page sellers, and drop-shippers.',
            'badge_text' => '📦 B2B WHOLESALE',
            'button_text' => 'View Wholesale Rates',
            'button_url' => 'wholesale.php',
            'image_path' => 'images/hero/hero-2.jpg'
        ]
    ];
}

// Fallback products if database is empty
if (empty($featuredProducts)) {
    $featuredProducts = [
        [
            'id' => 1,
            'name' => 'Naviforce Luxury Chronograph Quartz Watch',
            'slug' => 'naviforce-luxury-chronograph',
            'price' => 3200.00,
            'sale_price' => 2450.00,
            'category_name' => 'Watches',
            'image_path' => 'images/products/watch-1.jpg',
            'rating' => 4.9,
            'reviews_count' => 48
        ],
        [
            'id' => 2,
            'name' => 'Automatic Mechanical Skeleton Steel Watch',
            'slug' => 'automatic-mechanical-skeleton-steel-watch',
            'price' => 4500.00,
            'sale_price' => 3650.00,
            'category_name' => 'Watches',
            'image_path' => 'images/products/watch-2.jpg',
            'rating' => 4.8,
            'reviews_count' => 36
        ],
        [
            'id' => 3,
            'name' => 'Full Grain Cowhide Leather Long Bifold Wallet',
            'slug' => 'full-grain-cowhide-leather-wallet',
            'price' => 1200.00,
            'sale_price' => 750.00,
            'category_name' => 'Leather Wallets',
            'image_path' => 'images/products/wallet-1.jpg',
            'rating' => 5.0,
            'reviews_count' => 52
        ],
        [
            'id' => 4,
            'name' => 'Executive Leather Handbag with Shoulder Strap',
            'slug' => 'executive-leather-handbag',
            'price' => 3800.00,
            'sale_price' => 2890.00,
            'category_name' => 'Luxury Bags',
            'image_path' => 'images/products/bag-1.jpg',
            'rating' => 4.9,
            'reviews_count' => 29
        ],
        [
            'id' => 5,
            'name' => 'Polarized UV400 Retro Aviator Sunglasses',
            'slug' => 'polarized-uv400-aviator-sunglasses',
            'price' => 1500.00,
            'sale_price' => 990.00,
            'category_name' => 'Sunglasses',
            'image_path' => 'images/products/sunglasses-1.jpg',
            'rating' => 4.7,
            'reviews_count' => 41
        ],
        [
            'id' => 6,
            'name' => 'Magnetic Wireless Fast Power Bank 10000mAh',
            'slug' => 'magnetic-wireless-powerbank-10000mah',
            'price' => 2400.00,
            'sale_price' => 1850.00,
            'category_name' => 'Smart Gadgets',
            'image_path' => 'images/products/gadget-1.jpg',
            'rating' => 4.9,
            'reviews_count' => 64
        ],
        [
            'id' => 7,
            'name' => 'Automatic Buckle Genuine Cow Leather Belt',
            'slug' => 'automatic-buckle-cow-leather-belt',
            'price' => 1400.00,
            'sale_price' => 890.00,
            'category_name' => 'Accessories & Belts',
            'image_path' => 'images/products/belt-1.jpg',
            'rating' => 4.8,
            'reviews_count' => 33
        ],
        [
            'id' => 8,
            'name' => 'True Wireless ANC Active Noise Cancelling Earbuds',
            'slug' => 'true-wireless-anc-earbuds',
            'price' => 2800.00,
            'sale_price' => 1950.00,
            'category_name' => 'Smart Gadgets',
            'image_path' => 'images/products/gadget-2.jpg',
            'rating' => 4.9,
            'reviews_count' => 72
        ]
    ];
}
?>

<!-- 1. Animated Hero Carousel Slider (Rounded Corners & Spaced Sides) -->
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 pt-2 sm:pt-4 mb-10 sm:mb-12">
    <section class="relative bg-slate-950 text-white overflow-hidden rounded-3xl sm:rounded-[2.5rem] shadow-2xl border border-slate-800/80" id="heroSliderSection">
        <?php if (!empty($banners)): ?>
            <?php foreach ($banners as $index => $banner): ?>
            <div class="hero-slide relative min-h-[420px] sm:min-h-[480px] lg:min-h-[520px] flex items-center transition-all duration-700 <?= $index === 0 ? '' : 'hidden' ?>" data-slide="<?= $index ?>">
                <img src="/<?= ltrim($banner['image_path'], '/') ?>" alt="<?= htmlspecialchars($banner['title'] ?? '') ?>" class="absolute inset-0 w-full h-full object-cover opacity-60">
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/80 to-transparent"></div>

                <div class="relative max-w-7xl mx-auto px-6 sm:px-12 py-12 sm:py-16 w-full">
                    <div class="max-w-2xl space-y-4 sm:space-y-5">
                        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-black tracking-widest uppercase animate-pulse border border-indigo-500/30">
                            <?= htmlspecialchars($banner['badge_text'] ?: '✨ NEW ARRIVALS 2026') ?>
                        </span>

                        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-tight font-serif">
                            <?= htmlspecialchars($banner['title'] ?? '') ?>
                        </h1>

                        <p class="text-xs sm:text-sm lg:text-base text-slate-300 leading-relaxed font-normal max-w-xl">
                            <?= htmlspecialchars($banner['subtitle'] ?? '') ?>
                        </p>

                        <div class="pt-2 flex flex-wrap items-center gap-3 sm:gap-4">
                            <a href="<?= htmlspecialchars($banner['button_url'] ?: 'shop.php') ?>" class="px-7 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs rounded-xl shadow-xl transition flex items-center gap-2">
                                <?= htmlspecialchars($banner['button_text'] ?: 'Shop Now') ?> &rarr;
                            </a>
                            <a href="wholesale.php" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs rounded-xl shadow transition">
                                Wholesale B2B &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <button type="button" onclick="prevHeroSlide()" class="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-black/40 hover:bg-black/80 text-white flex items-center justify-center backdrop-blur-sm transition z-20">
            <i class="fas fa-chevron-left text-xs sm:text-sm"></i>
        </button>
        <button type="button" onclick="nextHeroSlide()" class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-black/40 hover:bg-black/80 text-white flex items-center justify-center backdrop-blur-sm transition z-20">
            <i class="fas fa-chevron-right text-xs sm:text-sm"></i>
        </button>

        <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex items-center gap-2 z-20">
            <?php foreach ($banners as $index => $b): ?>
            <button type="button" onclick="goToHeroSlide(<?= $index ?>)" class="hero-dot h-2 rounded-full transition-all duration-300 <?= $index === 0 ? 'w-8 bg-indigo-500' : 'w-2 bg-white/40' ?>" data-dot="<?= $index ?>"></button>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<!-- 2. Value Proposition Badges -->
<section class="border-b border-slate-200 bg-white py-8 mb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <div class="flex items-center gap-3.5 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0"><i class="fas fa-truck-fast"></i></div>
                <div><h4 class="text-xs font-extrabold text-slate-900">Free Shipping</h4><p class="text-[11px] text-slate-500 mt-0.5">On orders over ৳2000</p></div>
            </div>
            <div class="flex items-center gap-3.5 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0"><i class="fas fa-shield-halved"></i></div>
                <div><h4 class="text-xs font-extrabold text-slate-900">Secure Payment</h4><p class="text-[11px] text-slate-500 mt-0.5">100% safe & COD verified</p></div>
            </div>
            <div class="flex items-center gap-3.5 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0"><i class="fas fa-truck-fast"></i></div>
                <div><h4 class="text-xs font-extrabold text-slate-900">Fast Dispatch</h4><p class="text-[11px] text-slate-500 mt-0.5">Quick doorstep service</p></div>
            </div>
            <div class="flex items-center gap-3.5 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0"><i class="fas fa-headset"></i></div>
                <div><h4 class="text-xs font-extrabold text-slate-900">24/7 Support</h4><p class="text-[11px] text-slate-500 mt-0.5">We're always here to help</p></div>
            </div>
        </div>
    </div>
</section>

<!-- 3. WHOLESALE / B2B SHOWCASE CARD -->
<section class="py-4 bg-slate-50 mb-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950 rounded-3xl p-8 sm:p-10 text-white shadow-xl border border-slate-800 flex flex-col lg:flex-row items-center justify-between gap-8">
            <div class="space-y-3 max-w-xl text-center lg:text-left">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-400 text-xs font-black uppercase tracking-wider border border-amber-500/30">
                    <i class="fas fa-boxes-stacked"></i> Wholesale / B2B Portal
                </span>
                <h3 class="text-2xl sm:text-3xl font-extrabold font-serif">Bulk Prices for Retailers & Resellers</h3>
                <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                    OnlineBdMart wholesale — factory rates with low 5 pcs minimum quantity. Controlled directly from Admin. Add to cart in bulk or order via WhatsApp!
                </p>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-4">
                <a href="wholesale.php" class="px-8 py-3.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs sm:text-sm rounded-xl shadow-lg transition flex items-center gap-2">
                    <span>Open Wholesale Catalog</span> <i class="fas fa-arrow-right text-xs"></i>
                </a>
                <a href="https://wa.me/88<?= htmlspecialchars($whatsapp) ?>?text=<?= urlencode('Hello, I am interested in wholesale / B2B bulk purchases at OnlineBdMart.') ?>" target="_blank" class="px-6 py-3.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs sm:text-sm rounded-xl shadow transition flex items-center gap-2">
                    <i class="fab fa-whatsapp text-base"></i> WhatsApp B2B Agent
                </a>
            </div>
        </div>
    </div>
</section>

<!-- 4. Shop By Category Grid (Always Visible with Dynamic / Seed Categories) -->
<section class="py-10 bg-slate-50 mb-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
            <div>
                <span class="text-[11px] font-extrabold uppercase tracking-widest text-indigo-600">Shop By Category</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold font-serif text-slate-900 mt-1">Browse Top Categories</h2>
            </div>
            <a href="categories.php" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
                <span>View All Categories</span> &rarr;
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <?php 
            if (empty($categories)) {
                try {
                    $catDirect = $db->query("SELECT * FROM categories")->fetchAll();
                    if (!empty($catDirect)) $categories = $catDirect;
                } catch (Exception $ex) {}
            }

            // Filter Top Categories selected from Admin Panel to show on Homepage
            $displayCats = array_filter($categories, function($c) {
                $isHome = isset($c['show_on_homepage']) ? ($c['show_on_homepage'] == 1) : (isset($c['is_featured']) ? ($c['is_featured'] == 1) : true);
                $isParent = empty($c['parent_id']) || $c['parent_id'] == 0;
                return $isHome && $isParent;
            });

            if (empty($displayCats)) {
                $displayCats = array_filter($categories, function($c) {
                    return empty($c['parent_id']) || $c['parent_id'] == 0;
                });
            }
            if (empty($displayCats)) {
                $displayCats = $categories;
            }
            $displayCats = array_slice($displayCats, 0, 12);

            foreach ($displayCats as $cat): 
                $pCount = $cat['products_count'] ?? 0;
                $emoji = trim((string)($cat['emoji'] ?? ''));
                if (empty($emoji)) {
                    $slug = strtolower($cat['slug'] ?? ($cat['name'] ?? ''));
                    if (str_contains($slug, 'watch') || str_contains($slug, 'clock')) $emoji = '⌚';
                    elseif (str_contains($slug, 'gadget') || str_contains($slug, 'phone')) $emoji = '📱';
                    elseif (str_contains($slug, 'wallet') || str_contains($slug, 'leather')) $emoji = '👛';
                    elseif (str_contains($slug, 'bag')) $emoji = '👜';
                    elseif (str_contains($slug, 'glass') || str_contains($slug, 'sunglass')) $emoji = '🕶️';
                    elseif (str_contains($slug, 'men')) $emoji = '👔';
                    elseif (str_contains($slug, 'women')) $emoji = '👗';
                    else $emoji = '🛍️';
                }
            ?>
            <a href="shop.php?category=<?= htmlspecialchars($cat['slug']) ?>" class="group bg-white p-5 rounded-2xl border border-slate-200/80 hover:border-indigo-500 hover:shadow-xl transition-all duration-300 flex flex-col items-center text-center">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-50 to-indigo-100 group-hover:from-indigo-600 group-hover:to-cyan-500 text-indigo-600 group-hover:text-white flex items-center justify-center text-3xl transition-all duration-300 mb-3 shadow-sm overflow-hidden shrink-0">
                    <span class="select-none inline-block transform group-hover:scale-110 transition-transform"><?= htmlspecialchars($emoji) ?></span>
                </div>
                <h3 class="text-xs font-extrabold text-slate-900 group-hover:text-indigo-600 transition truncate w-full"><?= htmlspecialchars($cat['name']) ?></h3>
                <span class="text-[10px] text-slate-400 font-semibold mt-0.5"><?= $pCount ?> Items</span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 5. Flash Deals with Live Countdown (ROUNDED WITH SPACED SIDES) -->
<?php if (($s['deals_enabled'] ?? '1') === '1'): 
    $dealEndTime = $s['deals_end_time'] ?? date('Y-m-d 23:59:59', strtotime('+3 days'));
?>
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 my-10 sm:my-14">
    <section class="relative bg-gradient-to-r from-slate-950 via-indigo-950 to-slate-900 text-white overflow-hidden rounded-3xl sm:rounded-[2.5rem] shadow-2xl border border-slate-800/80 p-6 sm:p-10 lg:p-12">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div class="space-y-4 max-w-xl">
                <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-rose-500/20 text-rose-400 text-xs font-black uppercase tracking-widest border border-rose-500/30 animate-pulse">
                    <?= htmlspecialchars($s['deals_badge_text'] ?? '% Limited Time Flash Sale') ?>
                </span>
                <h2 class="text-2xl sm:text-4xl font-extrabold font-serif leading-tight">
                    <?= htmlspecialchars($s['deals_banner_title'] ?? 'Big Deals on Top Fashion Gadgets') ?>
                </h2>
                <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                    <?= htmlspecialchars($s['deals_banner_subtitle'] ?? 'Grab luxury chronograph watches and leather wallets at unbeatable discount prices.') ?>
                </p>
                <div class="pt-2 flex flex-wrap items-center gap-3">
                    <a href="deals.php" class="px-7 py-3 bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white font-extrabold text-xs rounded-xl shadow-lg transition flex items-center gap-2">
                        <span>Shop Flash Deals</span> <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>

            <!-- Countdown Timer Box -->
            <div class="bg-slate-900/90 backdrop-blur-md border border-slate-700/60 rounded-3xl p-6 sm:p-8 flex items-center justify-center gap-3 sm:gap-5 text-center shadow-xl">
                <div>
                    <div id="liveHours" class="w-16 sm:w-20 py-3 sm:py-4 bg-slate-950/80 border border-slate-800 rounded-2xl text-2xl sm:text-3xl font-black text-indigo-400 font-mono shadow-inner">12</div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mt-1 block">Hours</span>
                </div>
                <span class="text-2xl font-bold text-slate-600 -mt-4">:</span>
                <div>
                    <div id="liveMins" class="w-16 sm:w-20 py-3 sm:py-4 bg-slate-950/80 border border-slate-800 rounded-2xl text-2xl sm:text-3xl font-black text-emerald-400 font-mono shadow-inner">48</div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mt-1 block">Mins</span>
                </div>
                <span class="text-2xl font-bold text-slate-600 -mt-4">:</span>
                <div>
                    <div id="liveSecs" class="w-16 sm:w-20 py-3 sm:py-4 bg-slate-950/80 border border-slate-800 rounded-2xl text-2xl sm:text-3xl font-black text-amber-400 font-mono shadow-inner">26</div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mt-1 block">Secs</span>
                </div>
            </div>
        </div>
    </section>
</div>
<?php endif; ?>

<!-- 6. Best Sellers & Top Picks -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <span class="text-[11px] font-extrabold uppercase text-indigo-600">Best Sellers</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold font-serif text-slate-900 mt-1">Top Picks for You</h2>
            </div>
            <a href="shop.php" class="text-xs font-bold text-indigo-600 hover:underline">View All &rarr;</a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <?php foreach ($featuredProducts as $product): 
                $price = ($product['sale_price'] && $product['sale_price'] > 0 && $product['sale_price'] < $product['price']) ? $product['sale_price'] : $product['price'];
            ?>
            <div class="group bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between overflow-hidden relative">
                <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                <span class="absolute top-3 left-3 z-10 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white">Sale</span>
                <?php endif; ?>

                <div class="absolute top-3 right-3 z-10">
                    <button type="button" onclick="toggleWishlist({ id: <?= $product['id'] ?>, name: '<?= addslashes($product['name']) ?>', price: <?= $price ?>, image: '/<?= ltrim($product['image_path'], '/') ?>' })" class="w-8 h-8 rounded-full bg-white/90 shadow text-slate-400 hover:text-rose-500 flex items-center justify-center">
                        <i class="fas fa-heart text-xs"></i>
                    </button>
                </div>

                <a href="product.php?slug=<?= htmlspecialchars($product['slug']) ?>" class="relative block aspect-square bg-slate-100 overflow-hidden">
                    <img src="/<?= ltrim($product['image_path'], '/') ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                </a>

                <div class="p-4 flex flex-col justify-between flex-1">
                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400"><?= htmlspecialchars($product['category_name'] ?? 'Accessories') ?></span>
                        <a href="product.php?slug=<?= htmlspecialchars($product['slug']) ?>" class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition line-clamp-2 block mt-1"><?= htmlspecialchars($product['name']) ?></a>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <div class="flex items-baseline gap-2 mb-3">
                            <span class="text-sm sm:text-base font-black text-slate-900">৳<?= number_format($price, 2) ?></span>
                            <?php if ($product['sale_price']): ?>
                            <span class="text-[11px] text-slate-400 line-through">৳<?= number_format($product['price'], 2) ?></span>
                            <?php endif; ?>
                        </div>
                        <button type="button" onclick="addToCart(<?= $product['id'] ?>)" class="w-full py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white text-xs font-extrabold rounded-xl shadow-md shadow-indigo-600/25 hover:shadow-indigo-600/40 transition-all flex items-center justify-center gap-2 group-hover:scale-[1.02]">
                            <i class="fas fa-bag-shopping text-xs"></i> <span>Add to Bag</span>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 7. BLOG & BUYING GUIDES SECTION -->
<section class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <span class="text-[11px] font-extrabold uppercase text-indigo-600">Our Blog & Buying Guides</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold font-serif text-slate-900 mt-1">Latest Tech & Fashion Guides</h2>
            </div>
            <a href="blog.php" class="text-xs font-bold text-indigo-600 hover:underline">View All &rarr;</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-3xl border shadow-sm p-6 space-y-3 flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full">Buying Guide</span>
                    <h3 class="text-base font-bold text-slate-900 mt-2"><a href="blog.php" class="hover:text-indigo-600">Top 5 Luxury Watches & Accessories Under ৳5000 in 2026</a></h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">We tested 15+ premium Japanese quartz watches and full-grain cowhide leather wallets in Bangladesh.</p>
                </div>
                <a href="blog.php" class="text-xs font-bold text-indigo-600 hover:underline pt-3 border-t">Read Article &rarr;</a>
            </div>
            <div class="bg-white rounded-3xl border shadow-sm p-6 space-y-3 flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full">Tips & Tricks</span>
                    <h3 class="text-base font-bold text-slate-900 mt-2"><a href="blog.php" class="hover:text-indigo-600">How to Spot Original vs Copy Accessories Before Paying</a></h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">Avoid cheap replicas with these 5 quick verification checks before making payment to courier riders.</p>
                </div>
                <a href="blog.php" class="text-xs font-bold text-indigo-600 hover:underline pt-3 border-t">Read Article &rarr;</a>
            </div>
            <div class="bg-white rounded-3xl border shadow-sm p-6 space-y-3 flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">B2B & Wholesale</span>
                    <h3 class="text-base font-bold text-slate-900 mt-2"><a href="blog.php" class="hover:text-indigo-600">Wholesale & Reselling Guide for Beginners in Bangladesh</a></h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">How to start your online accessory business with low MOQ and direct factory pricing.</p>
                </div>
                <a href="blog.php" class="text-xs font-bold text-indigo-600 hover:underline pt-3 border-t">Read Article &rarr;</a>
            </div>
        </div>
    </div>
</section>

<!-- 8. CUSTOMER LOVE / VERIFIED REVIEWS SECTION -->
<section class="py-16 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-xl mx-auto mb-8 space-y-2">
            <span class="text-xs font-bold uppercase text-indigo-600">Customer Love</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold font-serif">What Our Buyers Say</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">
            <div class="p-6 bg-slate-50 rounded-2xl border space-y-2">
                <div class="flex text-amber-400"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <p class="text-slate-600 italic">"Luxury watch quality outstanding! Heavy steel weight and sapphire glass looks premium. Delivery was completed in 2 days."</p>
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
</section>

<script>
    let currentHeroIdx = 0;
    const totalHeroSlides = <?= count($banners) ?>;
    function showHeroSlide(idx) {
        currentHeroIdx = (idx + totalHeroSlides) % totalHeroSlides;
        document.querySelectorAll('.hero-slide').forEach(s => s.classList.add('hidden'));
        const active = document.querySelector('.hero-slide[data-slide="' + currentHeroIdx + '"]');
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

    // Dynamic Deals Countdown Engine
    const targetDealDate = new Date("<?= addslashes($s['deals_end_time'] ?? date('Y-m-d 23:59:59', strtotime('+3 days'))) ?>").getTime();

    function updateLiveDealsTimer() {
        const now = new Date().getTime();
        const dist = targetDealDate - now;

        const elH = document.getElementById('liveHours');
        const elM = document.getElementById('liveMins');
        const elS = document.getElementById('liveSecs');
        if (!elH || !elM || !elS) return;

        if (dist <= 0) {
            elH.textContent = '00';
            elM.textContent = '00';
            elS.textContent = '00';
            return;
        }

        const h = Math.floor((dist % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const m = Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60));
        const s = Math.floor((dist % (1000 * 60)) / 1000);

        elH.textContent = String(h).padStart(2, '0');
        elM.textContent = String(m).padStart(2, '0');
        elS.textContent = String(s).padStart(2, '0');
    }

    setInterval(updateLiveDealsTimer, 1000);
    updateLiveDealsTimer();
</script>

<?php require_once 'includes/footer.php'; ?>
