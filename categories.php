<?php
$pageTitle = 'Product Categories - OnlineBdMart • Online Shopping BD';
require_once 'includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-950 via-indigo-950 to-slate-900 rounded-3xl p-8 sm:p-12 text-white mb-10 shadow-xl border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 text-center md:text-left">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-black uppercase tracking-widest border border-indigo-500/30">
                <i class="fas fa-tags"></i> Catalog Explorer
            </span>
            <h1 class="text-3xl sm:text-4xl font-extrabold font-serif">Browse All Product Categories</h1>
            <p class="text-xs sm:text-sm text-slate-300 max-w-xl">
                Explore curated fashion gadgets, luxury watches, authentic leather goods, and accessories at official retail and wholesale prices in Bangladesh.
            </p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="shop.php" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs rounded-xl shadow-lg transition">All Products &rarr;</a>
            <a href="wholesale.php" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs rounded-xl shadow transition">Wholesale B2B</a>
        </div>
    </div>

    <!-- Category Grid (Live from Database) -->
    <?php if (!empty($categories)): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($categories as $cat): 
            $pCount = $cat['products_count'] ?? 0;
            $emoji = trim((string)($cat['emoji'] ?? '🛍️'));
            if (empty($emoji)) $emoji = '🛍️';
        ?>
        <a href="shop.php?category=<?= htmlspecialchars($cat['slug']) ?>" class="group bg-white p-6 rounded-3xl border border-slate-200 hover:border-indigo-500 hover:shadow-2xl transition-all duration-300 flex flex-col justify-between space-y-5">
            <div class="flex items-start justify-between gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-50 to-indigo-100 group-hover:from-indigo-600 group-hover:to-cyan-500 text-indigo-600 group-hover:text-white flex items-center justify-center text-3xl shadow-sm transition-all duration-300 shrink-0 overflow-hidden">
                    <span class="select-none inline-block transform group-hover:scale-110 transition-transform"><?= htmlspecialchars($emoji) ?></span>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-indigo-50 text-indigo-700 group-hover:bg-indigo-600 group-hover:text-white transition">
                    <?= $pCount ?> Products
                </span>
            </div>

            <div>
                <h3 class="text-base font-black text-slate-900 group-hover:text-indigo-600 transition">
                    <?= htmlspecialchars($cat['name']) ?>
                </h3>
                <p class="text-xs text-slate-500 mt-1.5 line-clamp-2 leading-relaxed">
                    <?= htmlspecialchars($cat['description'] ?? ('Premium ' . strtolower($cat['name']) . ' collection with warranty and fast delivery across BD.')) ?>
                </p>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-indigo-600 group-hover:text-indigo-700">
                <span>View Products in <?= htmlspecialchars($cat['name']) ?></span>
                <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="p-12 text-center bg-white rounded-3xl border border-slate-200 text-slate-400 space-y-3">
        <i class="fas fa-folder-open text-4xl text-slate-300"></i>
        <h3 class="font-bold text-slate-700 text-base">No Categories Created Yet</h3>
        <p class="text-xs text-slate-400">Add categories in the Admin Panel to display them here.</p>
        <a href="admin-panel/categories.php" class="px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl text-xs inline-block">Go to Admin Categories</a>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
