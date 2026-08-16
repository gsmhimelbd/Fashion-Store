<?php
require_once __DIR__ . '/../config/database.php';
trackCustomerVisit();

$s = getAllSettings();
$storeName = $s['store_name'] ?? 'OnlineBdMart';
$storeLogo = !empty($s['store_logo']) ? $s['store_logo'] : 'images/logo.png';
$storeFavicon = !empty($s['store_favicon']) ? $s['store_favicon'] : $storeLogo;
$faviconVer = file_exists(__DIR__ . '/../' . ltrim($storeFavicon, '/')) ? @filemtime(__DIR__ . '/../' . ltrim($storeFavicon, '/')) : time();
$whatsapp = $s['whatsapp_number'] ?? '01775153740';
$phone = $s['contact_phone'] ?? '01775153740';
$tangailFee = $s['delivery_charge_tangail'] ?? '50';
$otherFee = $s['delivery_charge_other'] ?? '150';
$announcement = $s['announcement_bar'] ?? ('Free Delivery Tangail ৳' . $tangailFee . ' | Others ৳' . $otherFee . ' • Free Shipping above ৳2000');

$isCustomerLoggedIn = !empty($_SESSION['user_logged_in']) || !empty($_SESSION['user_id']) || !empty($_SESSION['customer_id']);
$customerName = $_SESSION['user_name'] ?? ($_SESSION['customer_name'] ?? 'Account');

$categories = [];
$allProducts = [];

try {
    $db = getDB();

    // Fetch live categories from database
    $catRows = $db->query("SELECT * FROM categories")->fetchAll();
    if (is_array($catRows) && !empty($catRows)) {
        $counts = [];
        try {
            $pStmt = $db->query("SELECT category_id, COUNT(*) as c FROM products WHERE is_active = 1 GROUP BY category_id");
            while ($r = $pStmt->fetch()) {
                $counts[$r['category_id']] = (int)$r['c'];
            }
        } catch (Exception $ex) {}

        foreach ($catRows as $crow) {
            $crow['products_count'] = $counts[$crow['id']] ?? 0;
            if (empty($crow['emoji'])) {
                $slug = strtolower($crow['slug'] ?? ($crow['name'] ?? ''));
                if (str_contains($slug, 'watch') || str_contains($slug, 'tech') || str_contains($slug, 'clock')) $crow['emoji'] = '⌚';
                elseif (str_contains($slug, 'gadget') || str_contains($slug, 'phone') || str_contains($slug, 'smart')) $crow['emoji'] = '📱';
                elseif (str_contains($slug, 'wallet') || str_contains($slug, 'leather') || str_contains($slug, 'belt')) $crow['emoji'] = '👛';
                elseif (str_contains($slug, 'bag') || str_contains($slug, 'handbag')) $crow['emoji'] = '👜';
                elseif (str_contains($slug, 'glass') || str_contains($slug, 'sunglass')) $crow['emoji'] = '🕶️';
                elseif (str_contains($slug, 'men') || str_contains($slug, 'man')) $crow['emoji'] = '👔';
                elseif (str_contains($slug, 'women') || str_contains($slug, 'lady')) $crow['emoji'] = '👗';
                elseif (str_contains($slug, 'jewel') || str_contains($slug, 'ring') || str_contains($slug, 'diamond') || str_contains($slug, 'fragrance')) $crow['emoji'] = '💍';
                elseif (str_contains($slug, 'new') || str_contains($slug, 'arrival')) $crow['emoji'] = '✨';
                else $crow['emoji'] = '🛍️';
            }
            $categories[] = $crow;
        }

        // Sort in PHP safely
        usort($categories, function($a, $b) {
            $o1 = (int)($a['display_order'] ?? 1);
            $o2 = (int)($b['display_order'] ?? 1);
            if ($o1 === $o2) return ((int)($a['id'] ?? 0)) <=> ((int)($b['id'] ?? 0));
            return $o1 <=> $o2;
        });
    }

    $allProducts = $db->query("SELECT p.id, p.name, p.slug, p.price, p.sale_price, p.image_path, c.name as category FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1")->fetchAll();
} catch (Exception $e) {
    $categories = [];
    $allProducts = [];
}

$cartItems = getCartItems();
$cartCount = getCartCount();
$subtotal = getCartSubtotal();
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? ($storeName . ' - Online Shopping Bangladesh')) ?></title>
    
    <!-- Dynamic Favicon / Website Tab Icon with Browser Cache Buster -->
    <link rel="icon" type="image/png" href="/<?= ltrim($storeFavicon, '/') ?>?v=<?= $faviconVer ?>">
    <link rel="icon" type="image/x-icon" href="/<?= ltrim($storeFavicon, '/') ?>?v=<?= $faviconVer ?>">
    <link rel="shortcut icon" type="image/x-icon" href="/<?= ltrim($storeFavicon, '/') ?>?v=<?= $faviconVer ?>">
    <link rel="apple-touch-icon" href="/<?= ltrim($storeFavicon, '/') ?>?v=<?= $faviconVer ?>">
    <meta name="msapplication-TileImage" content="/<?= ltrim($storeFavicon, '/') ?>?v=<?= $faviconVer ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col selection:bg-indigo-600 selection:text-white overflow-x-hidden w-full max-w-full">

    <!-- 1. TOP UTILITY BAR -->
    <div class="bg-slate-900 text-slate-300 text-xs py-2 px-3 sm:px-4 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2 sm:gap-3">
            <div class="flex items-center gap-2 overflow-hidden text-xs max-w-full sm:max-w-md">
                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-[10px] font-black bg-indigo-600 text-white tracking-wider uppercase shrink-0">Hot</span>
                <p class="font-medium truncate text-[11px] sm:text-xs"><?= htmlspecialchars($announcement) ?></p>
            </div>

            <div class="flex items-center gap-2 sm:gap-4 text-[11px] sm:text-xs font-semibold text-slate-300">
                <a href="track-order.php" class="hover:text-indigo-400 transition flex items-center gap-1 text-emerald-400 font-bold bg-emerald-950/60 border border-emerald-500/30 px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full">
                    <i class="fas fa-truck-fast text-[10px]"></i> <span>Track</span>
                </a>
                <span class="text-slate-700 hidden sm:inline">|</span>
                <a href="tel:<?= htmlspecialchars($phone) ?>" class="hover:text-white transition hidden sm:flex items-center gap-1">
                    <i class="fas fa-phone text-emerald-400"></i> <?= htmlspecialchars($phone) ?>
                </a>
                <span class="text-slate-700">|</span>
                <?php if ($isCustomerLoggedIn): ?>
                    <a href="account.php" class="hover:text-indigo-300 transition flex items-center gap-1 text-indigo-400 font-bold">
                        <i class="fas fa-user-check text-emerald-400"></i> <?= htmlspecialchars($customerName) ?>
                    </a>
                <?php else: ?>
                    <div class="flex items-center gap-1.5">
                        <a href="login.php" class="hover:text-white transition"><i class="fas fa-arrow-right-to-bracket"></i> Sign In</a>
                        <span class="text-slate-700">/</span>
                        <a href="register.php" class="hover:text-indigo-400 transition text-indigo-300 font-bold">Sign Up</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 2. MAIN HEADER -->
    <header class="sticky top-0 z-40 bg-white shadow-sm border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-2 sm:gap-4 py-3 sm:py-4">
                
                <!-- Hamburger Button & Brand Logo -->
                <div class="flex items-center gap-2 sm:gap-3 min-w-0 shrink">
                    <button type="button" onclick="openMobileMenu()" class="lg:hidden p-2 text-slate-700 hover:bg-slate-100 rounded-xl focus:outline-none shrink-0" title="Open Menu">
                        <i class="fas fa-bars-staggered text-lg sm:text-xl"></i>
                    </button>
                    <a href="index.php" class="flex items-center gap-2 sm:gap-3 group min-w-0">
                        <?php if (!empty($s['store_logo']) && file_exists(__DIR__ . '/../' . ltrim($s['store_logo'], '/'))): ?>
                            <img src="/<?= ltrim($s['store_logo'], '/') ?>" alt="<?= htmlspecialchars($storeName) ?>" class="h-8 sm:h-10 max-w-[120px] sm:max-w-[160px] object-contain group-hover:scale-105 transition-transform shrink-0">
                        <?php else: ?>
                            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-700 to-cyan-500 flex items-center justify-center text-white text-base sm:text-xl shadow-md group-hover:scale-105 transition-transform shrink-0">
                                <i class="fas fa-bag-shopping"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="text-base sm:text-2xl font-black tracking-tight text-slate-900 leading-none truncate max-w-[120px] sm:max-w-none block"><?= strtoupper(htmlspecialchars($storeName)) ?></span>
                                <span class="text-[9px] sm:text-[10px] tracking-widest font-extrabold text-indigo-600 uppercase block mt-0.5 truncate">Online Shopping BD</span>
                            </div>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Big Central Search Bar with Categories Select & Instant Live Dropdown -->
                <div class="hidden md:flex flex-1 max-w-2xl mx-4 relative">
                    <form method="GET" action="shop.php" class="w-full flex items-center rounded-2xl border-2 border-indigo-600 bg-white shadow-sm relative">
                        <div class="border-r border-slate-200 bg-slate-50 px-3 py-2.5 shrink-0 rounded-l-xl">
                            <select name="category" class="text-xs font-bold text-slate-700 bg-transparent outline-none cursor-pointer">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= (($_GET['category'] ?? '') === $cat['slug']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['emoji'] ?? '') ?> <?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex-1 relative">
                            <input type="text" 
                                   id="desktopLiveSearchInput"
                                   name="search"
                                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                                   autocomplete="off"
                                   placeholder="Search products, watches, leather wallets, sunglasses, gadgets..." 
                                   class="w-full px-4 py-2.5 text-xs text-slate-800 outline-none font-medium">
                        </div>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 font-bold text-xs flex items-center gap-1.5 transition rounded-r-xl">
                            <i class="fas fa-search"></i> <span>Search</span>
                        </button>
                    </form>

                    <div id="desktopLiveSearchDropdown" 
                         style="display: none;"
                         class="absolute left-0 right-0 top-full mt-2 bg-white rounded-2xl shadow-2xl border-2 border-slate-200 p-3 z-50 overflow-hidden divide-y divide-slate-100 max-h-96 overflow-y-auto">
                    </div>
                </div>

                <!-- Right Action Buttons: Account, Wishlist & Cart -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <a href="<?= $isCustomerLoggedIn ? 'account.php' : 'login.php' ?>" class="relative p-2 sm:p-2.5 rounded-xl bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-600 transition shrink-0" title="<?= $isCustomerLoggedIn ? htmlspecialchars($customerName) : 'My Account' ?>">
                        <i class="<?= $isCustomerLoggedIn ? 'fas fa-user-circle text-indigo-600' : 'far fa-user' ?> text-base sm:text-lg"></i>
                    </a>

                    <button type="button" onclick="openWishlistDrawer()" class="relative p-2 sm:p-2.5 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-700 hover:text-rose-600 transition shrink-0" title="Wishlist">
                        <i class="far fa-heart text-base sm:text-lg"></i>
                        <span id="headerWishlistBadge" class="absolute -top-1 -right-1 bg-rose-500 text-white text-[9px] sm:text-[10px] font-black rounded-full h-4 min-w-[16px] px-1 flex items-center justify-center">0</span>
                    </button>

                    <button type="button" onclick="openCartDrawer()" class="relative p-2 sm:px-4 sm:py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white transition flex items-center gap-1.5 sm:gap-2.5 shadow-md shadow-indigo-600/25 shrink-0" title="Cart">
                        <i class="fas fa-bag-shopping text-base sm:text-lg"></i>
                        <div class="hidden sm:block text-left text-xs leading-tight">
                            <span class="text-[10px] text-indigo-200 block">My Bag</span>
                            <span id="headerCartSubtotal" class="font-black">৳<?= number_format($subtotal, 2) ?></span>
                        </div>
                        <span id="headerCartBadge" class="bg-white/20 text-white text-[10px] sm:text-[11px] font-extrabold rounded-full px-1.5 sm:px-2 py-0.5 flex items-center justify-center"><?= $cartCount ?></span>
                    </button>
                </div>
            </div>

            <!-- Mobile Search Bar -->
            <div class="pb-3 md:hidden relative">
                <form method="GET" action="shop.php" class="flex items-center rounded-xl border border-slate-300 bg-white overflow-hidden shadow-sm">
                    <input type="text" id="mobileLiveSearchInput" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Search products, watches, bags..." autocomplete="off" class="flex-1 px-3 py-2 text-xs outline-none">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 font-bold text-xs"><i class="fas fa-search"></i></button>
                </form>
                <div id="mobileLiveSearchDropdown" style="display: none;" class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl shadow-2xl border-2 border-slate-200 p-2 z-50 max-h-72 overflow-y-auto divide-y"></div>
            </div>
        </div>

        <!-- 3. Desktop Main Navigation Bar -->
        <div class="hidden lg:block bg-slate-900 text-white border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <nav class="flex items-center space-x-1 text-xs font-bold text-slate-200">
                    <a href="index.php" class="px-4 py-3.5 hover:text-indigo-400 hover:bg-slate-800 transition <?= $currentPage === 'index.php' ? 'text-indigo-400 bg-slate-800' : '' ?>">Home</a>
                    <a href="shop.php" class="px-4 py-3.5 hover:text-indigo-400 hover:bg-slate-800 transition <?= ($currentPage === 'shop.php' && empty($_GET['category'])) ? 'text-indigo-400 bg-slate-800' : '' ?>">Shop</a>
                    <a href="wholesale.php" class="px-4 py-3.5 hover:text-amber-400 hover:bg-slate-800 transition text-amber-300 <?= $currentPage === 'wholesale.php' ? 'bg-slate-800 text-amber-400' : '' ?>"><i class="fas fa-boxes-stacked mr-1"></i> Wholesale</a>
                    <a href="categories.php" class="px-4 py-3.5 hover:text-indigo-400 hover:bg-slate-800 transition <?= $currentPage === 'categories.php' ? 'text-indigo-400 bg-slate-800' : '' ?>">Categories</a>
                    <a href="deals.php" class="px-4 py-3.5 hover:text-rose-400 hover:bg-slate-800 transition text-rose-300 <?= $currentPage === 'deals.php' ? 'bg-slate-800 text-rose-400' : '' ?>"><i class="fas fa-fire mr-1"></i> Deals</a>
                    <a href="blog.php" class="px-4 py-3.5 hover:text-indigo-400 hover:bg-slate-800 transition <?= $currentPage === 'blog.php' ? 'text-indigo-400 bg-slate-800' : '' ?>">Blog</a>
                    <a href="contact.php" class="px-4 py-3.5 hover:text-indigo-400 hover:bg-slate-800 transition <?= $currentPage === 'contact.php' ? 'text-indigo-400 bg-slate-800' : '' ?>">Contact</a>
                </nav>

                <div class="flex items-center gap-2 text-xs font-bold text-slate-300 py-3.5">
                    <i class="fab fa-whatsapp text-emerald-400 text-base"></i>
                    <a href="https://wa.me/88<?= htmlspecialchars($whatsapp) ?>" target="_blank" class="hover:text-emerald-400 transition">Hotline: <?= htmlspecialchars($whatsapp) ?></a>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Slide-out Menu -->
    <div id="mobileSideMenuDrawer" class="fixed inset-0 z-50 flex lg:hidden hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeMobileMenu()"></div>
        <div class="relative max-w-xs w-full bg-white h-full shadow-2xl flex flex-col justify-between py-6 px-6 overflow-y-auto z-10">
            <div>
                <div class="flex items-center justify-between pb-6 border-b">
                    <span class="font-extrabold text-lg text-slate-900"><?= htmlspecialchars($storeName) ?></span>
                    <button type="button" onclick="closeMobileMenu()" class="text-slate-400 hover:text-slate-700 p-1"><i class="fas fa-times text-xl"></i></button>
                </div>
                <div class="mt-6 space-y-1 text-xs font-bold">
                    <a href="index.php" class="block px-4 py-3 rounded-xl hover:bg-indigo-50">🏠 Home</a>
                    <a href="shop.php" class="block px-4 py-3 rounded-xl hover:bg-indigo-50">🛍️ Shop</a>
                    <a href="wholesale.php" class="block px-4 py-3 rounded-xl text-amber-700 bg-amber-50">📦 Wholesale / B2B</a>
                    <a href="categories.php" class="block px-4 py-3 rounded-xl hover:bg-indigo-50">🏷️ Categories</a>
                    <a href="deals.php" class="block px-4 py-3 rounded-xl text-rose-600 hover:bg-rose-50">🔥 Hot Deals</a>
                    <a href="blog.php" class="block px-4 py-3 rounded-xl hover:bg-indigo-50">📰 Blog & Guides</a>
                    <a href="contact.php" class="block px-4 py-3 rounded-xl hover:bg-indigo-50">📞 Contact Us</a>
                    <a href="track-order.php" class="block px-4 py-3 rounded-xl text-emerald-700 bg-emerald-50">🚚 Track Order</a>
                </div>
            </div>
            <div class="pt-6 border-t space-y-2">
                <a href="track-order.php" class="block w-full py-2.5 bg-emerald-50 text-emerald-800 font-bold rounded-xl text-xs text-center">Track Order Live</a>
                <a href="login.php" class="block w-full py-2.5 bg-indigo-600 text-white font-bold rounded-xl text-xs text-center">Sign In / Sign Up</a>
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
                        <i class="fas fa-bag-shopping text-indigo-600 text-lg"></i>
                        <h2 class="text-base font-extrabold text-slate-900">Your Cart (<span id="cartDrawerBadge"><?= $cartCount ?></span>)</h2>
                    </div>
                    <button type="button" onclick="closeCartDrawer()" class="p-2 text-slate-400 hover:text-slate-600"><i class="fas fa-times text-lg"></i></button>
                </div>

                <div class="px-5 py-3 bg-indigo-50 border-b border-indigo-100 text-xs">
                    <div class="flex justify-between items-center mb-1 font-bold">
                        <span id="freeShippingText"><?= $subtotal >= 2000 ? '🎉 You qualify for FREE Delivery!' : 'Add ৳' . number_format(2000 - $subtotal, 2) . ' more for FREE Delivery' ?></span>
                        <span id="freeShippingPct" class="text-indigo-600"><?= min(100, round(($subtotal / 2000) * 100)) ?>%</span>
                    </div>
                    <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                        <div id="freeShippingBar" class="bg-gradient-to-r from-indigo-500 to-emerald-400 h-full rounded-full transition-all duration-300" style="width: <?= min(100, ($subtotal / 2000) * 100) ?>%;"></div>
                    </div>
                </div>

                <div id="cartDrawerItemsList" class="flex-1 overflow-y-auto p-5 space-y-4">
                    <?php if (empty($cartItems)): ?>
                        <div class="h-full flex flex-col items-center justify-center text-center py-12">
                            <i class="fas fa-shopping-basket text-slate-200 text-5xl mb-4"></i>
                            <h3 class="font-bold text-slate-800">Your Cart is Empty</h3>
                            <a href="shop.php" onclick="closeCartDrawer()" class="mt-4 px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl text-xs shadow">Start Shopping</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cartItems as $item): ?>
                        <div class="flex gap-4 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <img src="/<?= ltrim($item['image'], '/') ?>" class="w-16 h-16 object-cover rounded-xl bg-white border shrink-0">
                            <div class="flex-1 min-w-0 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between gap-2">
                                        <h4 class="text-xs font-bold text-slate-800 line-clamp-1"><?= htmlspecialchars($item['name']) ?></h4>
                                        <button onclick="removeCartItem(<?= $item['id'] ?>)" class="text-slate-300 hover:text-rose-500"><i class="fas fa-trash-can text-xs"></i></button>
                                    </div>
                                    <p class="text-xs font-bold text-indigo-600">৳<?= number_format($item['price'], 2) ?></p>
                                </div>
                                <div class="flex items-center justify-between mt-2">
                                    <div class="flex items-center border rounded-lg bg-white">
                                        <button onclick="updateCartQty(<?= $item['id'] ?>, <?= $item['quantity'] - 1 ?>)" class="w-6 h-6 flex items-center justify-center text-xs font-bold hover:bg-slate-100">-</button>
                                        <span class="w-8 text-center text-xs font-bold"><?= $item['quantity'] ?></span>
                                        <button onclick="updateCartQty(<?= $item['id'] ?>, <?= $item['quantity'] + 1 ?>)" class="w-6 h-6 flex items-center justify-center text-xs font-bold hover:bg-slate-100">+</button>
                                    </div>
                                    <span class="text-xs font-extrabold">৳<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div id="cartDrawerFooter" class="p-5 border-t bg-slate-50 space-y-3" style="<?= empty($cartItems) ? 'display:none;' : '' ?>">
                    <div class="flex justify-between text-sm font-extrabold text-slate-900">
                        <span>Subtotal:</span>
                        <span id="cartDrawerSubtotal" class="text-indigo-600 text-base">৳<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="grid grid-cols-2 gap-2.5">
                        <a href="cart.php" class="py-3 px-4 bg-white border font-bold rounded-xl text-xs text-center hover:bg-slate-100">View Cart</a>
                        <a href="checkout.php" class="py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs text-center shadow flex items-center justify-center gap-1.5">
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
                    <a href="shop.php" onclick="closeWishlistDrawer()" class="block w-full py-3 bg-slate-900 text-white font-bold rounded-xl text-xs text-center">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Body Starts -->
    <main class="flex-1">
