<?php
require_once __DIR__ . '/auth.php';
checkAdminAuth();

$activePage = basename($_SERVER['PHP_SELF']);

try {
    $db = getDB();
    $pendingOrdersCount = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
    $unreadMessagesCount = (int)$db->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();
    
    $adminId = (int)($_SESSION['admin_id'] ?? 1);
    $stmt = $db->prepare("SELECT * FROM admins WHERE id = ? LIMIT 1");
    $stmt->execute([$adminId]);
    $currAdmin = $stmt->fetch();

    if (!is_array($currAdmin)) {
        $currAdmin = [
            'id' => $adminId,
            'name' => $_SESSION['admin_name'] ?? 'Super Admin',
            'username' => $_SESSION['admin_username'] ?? 'admin',
            'role' => $_SESSION['admin_role'] ?? 'superadmin',
            'profile_photo' => 'images/products/watch-1.jpg'
        ];
    }
    
    $adminAvatar = !empty($currAdmin['profile_photo']) ? $currAdmin['profile_photo'] : 'images/products/watch-1.jpg';
    $adminRole = $currAdmin['role'] ?? ($_SESSION['admin_role'] ?? 'superadmin');
    $storeLogo = getSetting('store_logo', 'images/logo.png');
    $storeFavicon = getSetting('store_favicon', $storeLogo);
    $faviconVer = file_exists(__DIR__ . '/../' . ltrim($storeFavicon, '/')) ? @filemtime(__DIR__ . '/../' . ltrim($storeFavicon, '/')) : time();
} catch (Exception $e) {
    $pendingOrdersCount = 0;
    $unreadMessagesCount = 0;
    $adminAvatar = 'images/products/watch-1.jpg';
    $adminRole = 'superadmin';
    $storeLogo = 'images/logo.png';
    $storeFavicon = 'images/logo.png';
    $faviconVer = time();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($adminTitle ?? 'Admin Portal') ?> - OnlineBdMart</title>
    
    <!-- Dynamic Favicon / Website Tab Icon -->
    <link rel="icon" type="image/png" href="/<?= ltrim($storeFavicon, '/') ?>?v=<?= $faviconVer ?>">
    <link rel="icon" type="image/x-icon" href="/<?= ltrim($storeFavicon, '/') ?>?v=<?= $faviconVer ?>">
    <link rel="shortcut icon" type="image/x-icon" href="/<?= ltrim($storeFavicon, '/') ?>?v=<?= $faviconVer ?>">
    <link rel="apple-touch-icon" href="/<?= ltrim($storeFavicon, '/') ?>?v=<?= $faviconVer ?>">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #020617; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 9999px; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased min-h-screen selection:bg-indigo-600 selection:text-white">

    <!-- Mobile Slide-out Menu Drawer -->
    <div id="adminMobileDrawer" class="fixed inset-0 z-50 flex md:hidden hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="closeAdminMobileMenu()"></div>
        <div class="relative max-w-xs w-full bg-slate-900 h-full shadow-2xl flex flex-col justify-between py-6 px-4 overflow-y-auto z-10 border-r border-slate-800">
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-cyan-500 flex items-center justify-center font-bold text-white shadow-md">O</div>
                        <div>
                            <span class="font-extrabold text-sm text-white block">OnlineBdMart</span>
                            <span class="text-[10px] text-amber-400 uppercase font-black"><?= $adminRole === 'superadmin' ? '👑 Super Admin' : '💼 ' . ucfirst($adminRole) ?></span>
                        </div>
                    </div>
                    <button type="button" onclick="closeAdminMobileMenu()" class="text-slate-400 p-1"><i class="fas fa-times text-lg"></i></button>
                </div>

                <nav class="space-y-1 text-xs font-semibold">
                    <a href="index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'index.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800' ?>"><i class="fas fa-chart-pie w-4"></i> Dashboard</a>
                    <?php if (hasPermission('products')): ?><a href="products.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'products.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800' ?>"><i class="fas fa-boxes-stacked w-4"></i> Products</a><?php endif; ?>
                    <?php if (hasPermission('categories')): ?><a href="categories.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'categories.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800' ?>"><i class="fas fa-folder-tree w-4"></i> Categories & Sub</a><?php endif; ?>
                    <?php if (hasPermission('wholesale')): ?><a href="wholesale.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'wholesale.php' ? 'bg-amber-500 text-slate-950 font-bold' : 'text-amber-400 hover:bg-slate-800' ?>"><i class="fas fa-boxes-packing w-4"></i> Wholesale / B2B</a><?php endif; ?>
                    <?php if (hasPermission('orders')): ?><a href="orders.php" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition <?= $activePage === 'orders.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800' ?>"><div class="flex items-center gap-3"><i class="fas fa-receipt w-4"></i> Orders</div><?php if ($pendingOrdersCount > 0): ?><span class="bg-rose-500 text-white text-[10px] px-2 py-0.5 rounded-full"><?= $pendingOrdersCount ?></span><?php endif; ?></a><?php endif; ?>
                    <?php if (hasPermission('deals')): ?><a href="deals.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'deals.php' ? 'bg-rose-600 text-white' : 'text-rose-400 hover:bg-slate-800' ?>"><i class="fas fa-fire w-4"></i> Flash Deals %</a><?php endif; ?>
                    <a href="coupons.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'coupons.php' ? 'bg-amber-500 text-slate-950 font-black shadow-md' : 'text-amber-400 hover:bg-slate-800' ?>"><i class="fas fa-ticket w-4"></i> Coupons & Banner</a>
                    <?php if (hasPermission('delivery')): ?><a href="delivery.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'delivery.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800' ?>"><i class="fas fa-truck-fast w-4 text-emerald-400"></i> Delivery Zones</a><?php endif; ?>
                    <?php if (hasPermission('customers')): ?><a href="customers.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'customers.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800' ?>"><i class="fas fa-users w-4"></i> Customers</a><?php endif; ?>
                    <?php if (hasPermission('suppliers')): ?><a href="suppliers.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'suppliers.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800' ?>"><i class="fas fa-truck-ramp-box w-4"></i> Suppliers</a><?php endif; ?>
                    <?php if (hasPermission('messages')): ?><a href="messages.php" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition <?= $activePage === 'messages.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800' ?>"><div class="flex items-center gap-3"><i class="fas fa-envelope w-4"></i> Messages</div><?php if ($unreadMessagesCount > 0): ?><span class="bg-indigo-500 text-white text-[10px] px-2 py-0.5 rounded-full"><?= $unreadMessagesCount ?></span><?php endif; ?></a><?php endif; ?>
                    <?php if (hasPermission('analytics')): ?><a href="analytics.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'analytics.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800' ?>"><i class="fas fa-chart-line w-4 text-pink-400"></i> Analytics</a><?php endif; ?>
                    <?php if (hasPermission('banners')): ?><a href="banners.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'banners.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800' ?>"><i class="fas fa-images w-4"></i> Banners / Slider</a><?php endif; ?>
                    <?php if (hasPermission('blog')): ?><a href="blog.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'blog.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800' ?>"><i class="fas fa-newspaper w-4"></i> Blog & Guides</a><?php endif; ?>
                    <?php if (hasPermission('reviews')): ?><a href="reviews.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'reviews.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800' ?>"><i class="fas fa-star w-4 text-amber-400"></i> Reviews</a><?php endif; ?>
                    <?php if (hasPermission('staff')): ?><a href="staff.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'staff.php' ? 'bg-indigo-600 text-white' : 'text-indigo-400 hover:bg-slate-800' ?>"><i class="fas fa-user-shield w-4"></i> Staff & Salesmen</a><?php endif; ?>
                    <?php if (hasPermission('whatsapp')): ?><a href="whatsapp.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'whatsapp.php' ? 'bg-emerald-600 text-white' : 'text-emerald-400 hover:bg-slate-800' ?>"><i class="fab fa-whatsapp w-4"></i> WhatsApp Setup</a><?php endif; ?>
                    <?php if (hasPermission('telegram')): ?><a href="telegram.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'telegram.php' ? 'bg-indigo-600 text-white font-bold' : 'text-sky-400 hover:bg-slate-800' ?>"><i class="fab fa-telegram w-4"></i> Telegram Bot</a><?php endif; ?>
                    <?php if (hasPermission('facebook-pixel')): ?><a href="facebook-pixel.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'facebook-pixel.php' ? 'bg-indigo-600 text-white' : 'text-blue-400 hover:bg-slate-800' ?>"><i class="fab fa-facebook w-4"></i> Facebook Pixel</a><?php endif; ?>
                    <?php if (hasPermission('colors')): ?><a href="colors.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'colors.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800' ?>"><i class="fas fa-palette w-4"></i> Colors & Theme</a><?php endif; ?>
                    <?php if (hasPermission('smtp')): ?><a href="smtp.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'smtp.php' ? 'bg-indigo-600 text-white' : 'text-indigo-400 hover:bg-slate-800' ?>"><i class="fas fa-at w-4"></i> SMTP Mailer</a><?php endif; ?>
                    <?php if (hasPermission('settings')): ?><a href="settings.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'settings.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800' ?>"><i class="fas fa-gear w-4"></i> Settings & Logo</a><?php endif; ?>
                    <?php if (hasPermission('otp-system')): ?><a href="otp-system.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'otp-system.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800' ?>"><i class="fas fa-key w-4"></i> OTP System</a><?php endif; ?>
                    <?php if (hasPermission('seo')): ?><a href="seo.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'seo.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800' ?>"><i class="fas fa-magnifying-glass-chart w-4"></i> SEO Meta</a><?php endif; ?>
                    <a href="profile.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'profile.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800' ?>"><i class="fas fa-user-shield w-4"></i> Admin Profile</a>
                    <a href="google-2fa.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $activePage === 'google-2fa.php' ? 'bg-amber-500 text-slate-950 font-bold' : 'text-amber-400 hover:bg-slate-800' ?>"><i class="fab fa-google w-4"></i> Google 2FA</a>
                </nav>
            </div>

            <div class="pt-4 border-t border-slate-800 space-y-2">
                <a href="../index.php" target="_blank" class="block text-center py-2 bg-slate-800 text-slate-300 font-bold rounded-xl text-xs">View Storefront</a>
                <a href="logout.php" class="block text-center py-2 bg-rose-600 text-white font-bold rounded-xl text-xs">Sign Out</a>
            </div>
        </div>
    </div>

    <!-- Desktop Fixed Sidebar -->
    <aside class="fixed inset-y-0 left-0 w-64 bg-slate-900 border-r border-slate-800 hidden md:flex flex-col justify-between z-40">
        <div class="p-5 space-y-5 overflow-y-auto max-h-[calc(100vh-80px)]">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-800">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 to-cyan-500 flex items-center justify-center font-black text-white text-lg shadow-lg shrink-0">O</div>
                <div class="min-w-0">
                    <span class="font-extrabold text-sm text-white tracking-tight block truncate">OnlineBdMart</span>
                    <span class="text-[10px] text-amber-400 uppercase font-black tracking-wider block truncate"><?= $adminRole === 'superadmin' ? '👑 Super Admin' : '💼 ' . ucfirst($adminRole) ?></span>
                </div>
            </div>

            <nav class="space-y-1 text-xs font-semibold text-slate-300">
                <a href="index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'index.php' ? 'bg-indigo-600 text-white font-bold shadow-md' : 'hover:text-white hover:bg-slate-800/60' ?>"><i class="fas fa-chart-pie w-4"></i> <span>Dashboard</span></a>
                <?php if (hasPermission('products')): ?><a href="products.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'products.php' ? 'bg-indigo-600 text-white font-bold shadow-md' : 'hover:text-white hover:bg-slate-800/60' ?>"><i class="fas fa-boxes-stacked w-4"></i> <span>Products</span></a><?php endif; ?>
                <?php if (hasPermission('categories')): ?><a href="categories.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'categories.php' ? 'bg-indigo-600 text-white font-bold' : 'hover:text-white hover:bg-slate-800/60' ?>"><i class="fas fa-folder-tree w-4"></i> <span>Categories & Sub</span></a><?php endif; ?>
                <?php if (hasPermission('wholesale')): ?><a href="wholesale.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'wholesale.php' ? 'bg-amber-500 text-slate-950 font-extrabold shadow-md' : 'text-amber-400 hover:bg-slate-800/60' ?>"><div class="flex items-center gap-3"><i class="fas fa-boxes-packing w-4"></i> <span>Wholesale / B2B</span></div><span class="text-[9px] bg-amber-400/20 px-2 py-0.5 rounded-full font-black text-amber-300">MOQ</span></a><?php endif; ?>
                <?php if (hasPermission('orders')): ?><a href="orders.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'orders.php' ? 'bg-indigo-600 text-white font-bold' : 'hover:text-white hover:bg-slate-800/60' ?>"><div class="flex items-center gap-3"><i class="fas fa-receipt w-4"></i> <span>Orders</span></div><?php if ($pendingOrdersCount > 0): ?><span class="bg-rose-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full"><?= $pendingOrdersCount ?></span><?php endif; ?></a><?php endif; ?>
                <?php if (hasPermission('deals')): ?><a href="deals.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'deals.php' ? 'bg-rose-600 text-white font-bold' : 'text-rose-400 hover:bg-slate-800/60' ?>"><i class="fas fa-fire w-4"></i> <span>Flash Deals %</span></a><?php endif; ?>
                <a href="coupons.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'coupons.php' ? 'bg-amber-500 text-slate-950 font-black shadow-md' : 'text-amber-400 hover:bg-slate-800/60' ?>"><i class="fas fa-ticket w-4"></i> <span>Coupons & Banner</span></a>
                <?php if (hasPermission('delivery')): ?><a href="delivery.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'delivery.php' ? 'bg-indigo-600 text-white font-bold' : 'hover:text-white hover:bg-slate-800/60' ?>"><i class="fas fa-truck-fast w-4 text-emerald-400"></i> <span>64 Districts Delivery</span></a><?php endif; ?>
                <?php if (hasPermission('customers')): ?><a href="customers.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'customers.php' ? 'bg-indigo-600 text-white font-bold' : 'hover:text-white hover:bg-slate-800/60' ?>"><i class="fas fa-users w-4"></i> <span>Customers</span></a><?php endif; ?>
                <?php if (hasPermission('suppliers')): ?><a href="suppliers.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'suppliers.php' ? 'bg-indigo-600 text-white font-bold' : 'hover:text-white hover:bg-slate-800/60' ?>"><i class="fas fa-truck-ramp-box w-4"></i> <span>Suppliers</span></a><?php endif; ?>
                <?php if (hasPermission('messages')): ?><a href="messages.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'messages.php' ? 'bg-indigo-600 text-white font-bold' : 'hover:text-white hover:bg-slate-800/60' ?>"><div class="flex items-center gap-3"><i class="fas fa-envelope w-4"></i> <span>Messages</span></div><?php if ($unreadMessagesCount > 0): ?><span class="bg-indigo-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full"><?= $unreadMessagesCount ?></span><?php endif; ?></a><?php endif; ?>
                <?php if (hasPermission('analytics')): ?><a href="analytics.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'analytics.php' ? 'bg-indigo-600 text-white font-bold' : 'hover:text-white hover:bg-slate-800/60' ?>"><i class="fas fa-chart-line w-4 text-pink-400"></i> <span>Analytics</span></a><?php endif; ?>
                <?php if (hasPermission('banners')): ?><a href="banners.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'banners.php' ? 'bg-indigo-600 text-white font-bold' : 'hover:text-white hover:bg-slate-800/60' ?>"><i class="fas fa-images w-4"></i> <span>Banners / Slider</span></a><?php endif; ?>
                <?php if (hasPermission('blog')): ?><a href="blog.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'blog.php' ? 'bg-indigo-600 text-white font-bold' : 'hover:text-white hover:bg-slate-800/60' ?>"><i class="fas fa-newspaper w-4"></i> <span>Blog & Guides</span></a><?php endif; ?>
                <?php if (hasPermission('reviews')): ?><a href="reviews.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'reviews.php' ? 'bg-indigo-600 text-white font-bold' : 'hover:text-white hover:bg-slate-800/60' ?>"><i class="fas fa-star w-4 text-amber-400"></i> <span>Reviews</span></a><?php endif; ?>
                <?php if (hasPermission('staff')): ?><a href="staff.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'staff.php' ? 'bg-indigo-600 text-white font-bold' : 'text-indigo-400 hover:bg-slate-800/60' ?>"><i class="fas fa-user-shield w-4"></i> <span>Staff & Salesmen</span></a><?php endif; ?>
                <?php if (hasPermission('whatsapp')): ?><a href="whatsapp.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'whatsapp.php' ? 'bg-emerald-600 text-white font-bold' : 'text-emerald-400 hover:bg-slate-800/60' ?>"><i class="fab fa-whatsapp w-4"></i> <span>WhatsApp Setup</span></a><?php endif; ?>
                <?php if (hasPermission('telegram')): ?><a href="telegram.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'telegram.php' ? 'bg-indigo-600 text-white font-bold' : 'text-sky-400 hover:bg-slate-800/60' ?>"><i class="fab fa-telegram w-4"></i> <span>Telegram Bot</span></a><?php endif; ?>
                <?php if (hasPermission('facebook-pixel')): ?><a href="facebook-pixel.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'facebook-pixel.php' ? 'bg-indigo-600 text-white font-bold' : 'text-blue-400 hover:bg-slate-800/60' ?>"><i class="fab fa-facebook w-4"></i> <span>Facebook Pixel</span></a><?php endif; ?>
                <?php if (hasPermission('colors')): ?><a href="colors.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'colors.php' ? 'bg-indigo-600 text-white font-bold' : 'hover:text-white hover:bg-slate-800/60' ?>"><i class="fas fa-palette w-4"></i> <span>Colors & Theme</span></a><?php endif; ?>
                <?php if (hasPermission('smtp')): ?><a href="smtp.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'smtp.php' ? 'bg-indigo-600 text-white font-bold' : 'text-indigo-400 hover:bg-slate-800/60' ?>"><i class="fas fa-at w-4"></i> <span>SMTP Mailer</span></a><?php endif; ?>
                <?php if (hasPermission('settings')): ?><a href="settings.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'settings.php' ? 'bg-indigo-600 text-white font-bold' : 'hover:text-white hover:bg-slate-800/60' ?>"><i class="fas fa-gear w-4"></i> <span>Settings & Logo</span></a><?php endif; ?>
                <?php if (hasPermission('otp-system')): ?><a href="otp-system.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'otp-system.php' ? 'bg-indigo-600 text-white font-bold' : 'hover:text-white hover:bg-slate-800/60' ?>"><i class="fas fa-key w-4"></i> <span>OTP System</span></a><?php endif; ?>
                <?php if (hasPermission('seo')): ?><a href="seo.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'seo.php' ? 'bg-indigo-600 text-white font-bold' : 'hover:text-white hover:bg-slate-800/60' ?>"><i class="fas fa-magnifying-glass-chart w-4"></i> <span>SEO Meta</span></a><?php endif; ?>
                <a href="profile.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'profile.php' ? 'bg-indigo-600 text-white font-bold' : 'hover:text-white hover:bg-slate-800/60' ?>"><i class="fas fa-user-shield w-4"></i> <span>Admin Profile</span></a>
                <a href="google-2fa.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'google-2fa.php' ? 'bg-amber-500 text-slate-950 font-black shadow-lg' : 'text-amber-400 hover:bg-slate-800/60' ?>"><i class="fab fa-google w-4"></i> <span>Google 2FA Setup</span></a>
            </nav>
        </div>

        <div class="p-4 border-t border-slate-800 space-y-2 bg-slate-900 shrink-0">
            <a href="../index.php" target="_blank" class="flex items-center justify-between text-xs text-slate-400 hover:text-white py-1">
                <span>View Storefront</span> <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
            </a>
            <a href="logout.php" class="flex items-center gap-2 text-xs text-rose-400 hover:text-rose-300 font-bold py-1">
                <i class="fas fa-arrow-right-from-bracket"></i> <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- Main Workspace Container (Pushed right on desktop by md:pl-64) -->
    <div class="md:pl-64 flex flex-col min-h-screen w-full bg-slate-950">
        
        <!-- Top App Bar (STAYS FIRMLY AT TOP OF PAGE ALWAYS) -->
        <header class="sticky top-0 z-30 bg-slate-900 border-b border-slate-800 px-4 sm:px-8 py-3.5 flex items-center justify-between shadow-md">
            <div class="flex items-center gap-3">
                <button type="button" onclick="openAdminMobileMenu()" class="md:hidden p-2 text-slate-400 hover:text-white rounded-xl focus:outline-none" title="Open Menu">
                    <i class="fas fa-bars-staggered text-lg"></i>
                </button>
                <h1 class="text-sm sm:text-base font-extrabold text-white"><?= htmlspecialchars($adminTitle ?? 'Admin Dashboard') ?></h1>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <a href="../index.php" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold flex items-center gap-1.5 border border-slate-700">
                    <i class="fas fa-globe"></i> <span class="hidden sm:inline">Storefront</span>
                </a>
                <div class="flex items-center gap-2.5 pl-2 border-l border-slate-800">
                    <img src="/<?= ltrim($adminAvatar, '/') ?>" class="w-8 h-8 rounded-full object-cover border border-slate-700 bg-slate-800 shrink-0">
                    <div class="hidden sm:block text-left">
                        <span class="font-bold text-slate-200 block leading-tight"><?= htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['admin_username'] ?? 'Admin') ?></span>
                        <span class="text-[9px] text-amber-400 font-black uppercase"><?= $adminRole === 'superadmin' ? 'Super Admin' : ucfirst($adminRole) ?></span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area Starts -->
        <main class="flex-1 p-4 sm:p-8 max-w-7xl w-full mx-auto space-y-6">

<script>
function openAdminMobileMenu() {
    const el = document.getElementById('adminMobileDrawer');
    if (el) el.classList.remove('hidden');
}
function closeAdminMobileMenu() {
    const el = document.getElementById('adminMobileDrawer');
    if (el) el.classList.add('hidden');
}
</script>
