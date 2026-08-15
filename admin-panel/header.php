<?php
require_once __DIR__ . '/auth.php';
checkAdminAuth();

$activePage = basename($_SERVER['PHP_SELF']);

try {
    $db = getDB();
    $pendingOrdersCount = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
    $unreadMessagesCount = (int)$db->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();
} catch (Exception $e) {
    $pendingOrdersCount = 0;
    $unreadMessagesCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($adminTitle ?? 'Admin Portal') ?> - OnlineBdMart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4f46e5',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased min-h-screen flex">
    <!-- Left Sidebar -->
    <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col justify-between shrink-0 hidden md:flex sticky top-0 h-screen overflow-y-auto">
        <div class="p-6 space-y-6">
            <!-- Brand Logo -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white text-lg font-black shadow-lg shadow-indigo-600/30">
                    O
                </div>
                <div>
                    <h2 class="font-extrabold text-sm text-white tracking-wide">OnlineBdMart</h2>
                    <span class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider">Admin Engine</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-1 text-xs font-semibold">
                <a href="index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'index.php' ? 'bg-indigo-600 text-white font-bold shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                    <i class="fas fa-chart-pie w-4"></i> <span>Dashboard</span>
                </a>
                <a href="products.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'products.php' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                    <i class="fas fa-boxes-stacked w-4"></i> <span>Products</span>
                </a>
                <a href="wholesale.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'wholesale.php' ? 'bg-amber-500 text-slate-950 font-black' : 'text-amber-400 hover:bg-slate-800/60' ?>">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-boxes-packing w-4"></i> <span>Wholesale / B2B</span>
                    </div>
                    <span class="text-[9px] bg-amber-400/20 px-2 py-0.5 rounded-full font-black text-amber-300">MOQ</span>
                </a>
                <a href="orders.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'orders.php' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-receipt w-4"></i> <span>Orders</span>
                    </div>
                    <?php if ($pendingOrdersCount > 0): ?>
                    <span class="bg-rose-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full"><?= $pendingOrdersCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="banners.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'banners.php' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                    <i class="fas fa-images w-4"></i> <span>Banners / Slider</span>
                </a>
                <a href="payments.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'payments.php' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                    <i class="fas fa-credit-card w-4"></i> <span>Payments</span>
                </a>
                <a href="delivery.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'delivery.php' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                    <i class="fas fa-truck-fast w-4 text-emerald-400"></i> <span>64 Districts Delivery</span>
                </a>
                <a href="customers.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'customers.php' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                    <i class="fas fa-users w-4"></i> <span>Customers</span>
                </a>
                <a href="suppliers.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'suppliers.php' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                    <i class="fas fa-truck-ramp-box w-4"></i> <span>Suppliers</span>
                </a>
                <a href="messages.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'messages.php' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-envelope w-4"></i> <span>Messages</span>
                    </div>
                    <?php if ($unreadMessagesCount > 0): ?>
                    <span class="bg-indigo-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full"><?= $unreadMessagesCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="analytics.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'analytics.php' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                    <i class="fas fa-chart-line w-4 text-pink-400"></i> <span>Analytics & Funnel</span>
                </a>
                <a href="categories.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'categories.php' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                    <i class="fas fa-folder-tree w-4"></i> <span>Categories</span>
                </a>
                <a href="blog.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'blog.php' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                    <i class="fas fa-newspaper w-4"></i> <span>Blog & Guides</span>
                </a>
                <a href="reviews.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'reviews.php' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                    <i class="fas fa-star w-4 text-amber-400"></i> <span>Reviews</span>
                </a>
                <a href="whatsapp.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'whatsapp.php' ? 'bg-emerald-600 text-white font-bold' : 'text-emerald-400 hover:bg-slate-800/60' ?>">
                    <i class="fab fa-whatsapp w-4"></i> <span>WhatsApp Setup</span>
                </a>
                <a href="telegram.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'telegram.php' ? 'bg-indigo-600 text-white font-bold' : 'text-sky-400 hover:bg-slate-800/60' ?>">
                    <i class="fab fa-telegram w-4"></i> <span>Telegram Bot</span>
                </a>
                <a href="facebook-pixel.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'facebook-pixel.php' ? 'bg-indigo-600 text-white font-bold' : 'text-blue-400 hover:bg-slate-800/60' ?>">
                    <i class="fab fa-facebook w-4"></i> <span>Facebook Pixel</span>
                </a>
                <a href="colors.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'colors.php' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                    <i class="fas fa-palette w-4"></i> <span>Colors & Theme</span>
                </a>
                <a href="smtp.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'smtp.php' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                    <i class="fas fa-at w-4 text-indigo-400"></i> <span>SMTP Emailer</span>
                </a>
                <a href="settings.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'settings.php' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                    <i class="fas fa-gear w-4"></i> <span>Settings</span>
                </a>
                <a href="otp-system.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'otp-system.php' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                    <i class="fas fa-key w-4"></i> <span>OTP System</span>
                </a>
                <a href="profile.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'profile.php' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                    <i class="fas fa-user-shield w-4"></i> <span>Admin Profile</span>
                </a>
                <a href="seo.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= $activePage === 'seo.php' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                    <i class="fas fa-magnifying-glass-chart w-4"></i> <span>SEO Optimization</span>
                </a>
            </nav>
        </div>

        <div class="p-6 border-t border-slate-800 space-y-3">
            <a href="../index.php" target="_blank" class="flex items-center justify-between text-xs text-slate-400 hover:text-white py-1">
                <span>View Live Store</span> <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
            </a>
            <a href="logout.php" class="flex items-center gap-2 text-xs text-rose-400 hover:text-rose-300 font-bold py-1">
                <i class="fas fa-arrow-right-from-bracket"></i> <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- Main Workspace Container -->
    <div class="flex-1 flex flex-col min-w-0 bg-slate-950">
        <!-- Top App Bar -->
        <header class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-4">
                <h1 class="text-base font-extrabold text-white"><?= htmlspecialchars($adminTitle ?? 'Admin Dashboard') ?></h1>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <a href="../index.php" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold flex items-center gap-2 border border-slate-700">
                    <i class="fas fa-globe"></i> <span>Open Store</span>
                </a>
                <div class="flex items-center gap-2 pl-2 border-l border-slate-800">
                    <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center font-bold text-white">
                        <?= strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)) ?>
                    </div>
                    <span class="font-bold text-slate-300 hidden sm:inline"><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></span>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="flex-1 p-6 sm:p-8 max-w-7xl w-full mx-auto space-y-6">
