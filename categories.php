<?php
$pageTitle = 'Product Categories - OnlineBdMart';
require_once 'includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <span class="text-xs font-bold uppercase text-indigo-600">Categories</span>
        <h1 class="text-3xl font-extrabold font-serif text-slate-900 mt-1">Browse All Categories</h1>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($categories as $cat): ?>
        <a href="shop.php?category=<?= htmlspecialchars($cat['slug']) ?>" class="group bg-white p-6 rounded-3xl border border-slate-200 hover:border-indigo-500 hover:shadow-xl transition flex flex-col items-center text-center space-y-4">
            <div class="w-20 h-20 rounded-2xl bg-indigo-50 group-hover:bg-indigo-600 text-indigo-600 group-hover:text-white flex items-center justify-center text-3xl transition">
                <i class="fas <?= htmlspecialchars($cat['icon'] ?: 'fa-tag') ?>"></i>
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-indigo-600"><?= htmlspecialchars($cat['name']) ?></h3>
                <p class="text-xs text-slate-400 mt-1"><?= $cat['products_count'] ?> Products Listed</p>
            </div>
            <span class="text-xs font-bold text-indigo-600 group-hover:underline">Explore Category &rarr;</span>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
