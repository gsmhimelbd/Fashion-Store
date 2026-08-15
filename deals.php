<?php
$pageTitle = 'Hot Deals & Discounts - OnlineBdMart';
require_once 'includes/header.php';

try {
    $db = getDB();
    $deals = $db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 AND p.sale_price IS NOT NULL AND p.sale_price > 0 AND p.sale_price < p.price ORDER BY p.id DESC")->fetchAll();
} catch (Exception $e) {
    $deals = [];
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-gradient-to-r from-rose-600 to-pink-600 rounded-3xl p-8 sm:p-12 text-white shadow-xl mb-10 flex flex-col md:flex-row items-center justify-between gap-8">
        <div class="space-y-3 max-w-xl text-center md:text-left">
            <span class="px-3 py-1 bg-black/20 rounded-full text-xs font-black uppercase tracking-wider">🔥 Limited-Time Sales</span>
            <h1 class="text-3xl sm:text-4xl font-extrabold font-serif">Super Deals & Discount Offers</h1>
            <p class="text-rose-100 text-xs sm:text-sm">Enjoy up to 50% discount on original accessories, watches, and smart gadgets in Bangladesh.</p>
        </div>
    </div>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-extrabold font-serif text-slate-900">Active Deals (<?= count($deals) ?>)</h2>
        </div>

        <?php if (!empty($deals)): ?>
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                <?php foreach ($deals as $p): 
                    $discountPct = round((($p['price'] - $p['sale_price']) / $p['price']) * 100);
                ?>
                <div class="group bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl transition flex flex-col justify-between overflow-hidden relative p-4">
                    <span class="absolute top-3 left-3 z-10 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white">-<?= $discountPct ?>% OFF</span>

                    <a href="product.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="relative block aspect-square bg-slate-100 rounded-xl overflow-hidden mb-3">
                        <img src="/<?= ltrim($p['image_path'], '/') ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    </a>

                    <div class="flex-1 flex flex-col justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase text-slate-400"><?= htmlspecialchars($p['category_name'] ?? 'Accessories') ?></span>
                            <a href="product.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition line-clamp-2 block mt-0.5"><?= htmlspecialchars($p['name']) ?></a>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-sm font-black text-rose-600 block">৳<?= number_format($p['sale_price'], 2) ?></span>
                                <span class="text-[10px] text-slate-400 line-through">৳<?= number_format($p['price'], 2) ?></span>
                            </div>
                            <button type="button" onclick="addToCart(<?= $p['id'] ?>)" class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow transition">Buy</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white p-12 rounded-3xl border text-center text-slate-400 text-xs">
                No active promotional deals right now. Check back soon!
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
