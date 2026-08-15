<?php
$pageTitle = 'Wholesale & B2B Bulk Catalog - OnlineBdMart';
require_once 'includes/header.php';

try {
    $db = getDB();
    $wholesaleProducts = $db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 AND p.is_wholesale = 1 ORDER BY p.id DESC")->fetchAll();
} catch (Exception $e) {
    $wholesaleProducts = [];
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Wholesale Hero Header -->
    <div class="bg-gradient-to-r from-amber-600 via-orange-600 to-amber-700 rounded-3xl p-8 sm:p-12 text-white shadow-xl mb-10 flex flex-col md:flex-row items-center justify-between gap-8">
        <div class="space-y-4 max-w-xl text-center md:text-left">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-black/20 text-white text-xs font-black uppercase tracking-wider">
                <i class="fas fa-boxes-packing"></i> B2B & Reseller Supply
            </span>
            <h1 class="text-3xl sm:text-4xl font-extrabold font-serif">OnlineBdMart Wholesale Portal</h1>
            <p class="text-amber-100 text-xs sm:text-sm leading-relaxed">
                Direct factory prices with low minimum order quantities (MOQ starting at 5 pcs). Ideal for Facebook shop owners, retailers, and boutique stores across Bangladesh.
            </p>
        </div>

        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-5 border border-white/20 text-center space-y-2 shrink-0">
            <div class="text-2xl font-black">24/7 Bulk Hotline</div>
            <p class="text-xs text-amber-200">Talk directly to Wholesale Manager</p>
            <a href="https://wa.me/88<?= htmlspecialchars($whatsapp) ?>?text=<?= urlencode('Hello, I want to discuss wholesale/bulk order terms with OnlineBdMart.') ?>" target="_blank" class="px-6 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-lg inline-flex items-center gap-2 mt-2">
                <i class="fab fa-whatsapp text-sm"></i> WhatsApp Bulk Agent
            </a>
        </div>
    </div>

    <!-- Wholesale Product Grid -->
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-extrabold font-serif text-slate-900">Wholesale Verified Catalog</h2>
            <span class="text-xs font-bold text-slate-500"><?= count($wholesaleProducts) ?> Bulk Items Available</span>
        </div>

        <?php if (!empty($wholesaleProducts)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($wholesaleProducts as $wp): 
                    $moq = $wp['wholesale_moq'] ?: 5;
                    $wPrice = $wp['wholesale_price'] ?: ($wp['price'] * 0.7);
                    $regularPrice = ($wp['sale_price'] && $wp['sale_price'] > 0) ? $wp['sale_price'] : $wp['price'];
                ?>
                <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-sm hover:shadow-xl transition flex flex-col justify-between space-y-4">
                    <div class="flex gap-4">
                        <img src="/<?= ltrim($wp['image_path'], '/') ?>" alt="<?= htmlspecialchars($wp['name']) ?>" class="w-24 h-24 object-cover rounded-2xl bg-slate-100 border shrink-0">
                        <div class="flex-1 min-w-0">
                            <span class="text-[10px] font-bold uppercase text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md">MOQ: <?= $moq ?> pcs</span>
                            <h3 class="text-xs font-bold text-slate-900 mt-1 line-clamp-2"><?= htmlspecialchars($wp['name']) ?></h3>
                            <div class="mt-2 text-xs">
                                <span class="font-extrabold text-slate-900 text-base text-amber-600">৳<?= number_format($wPrice, 2) ?></span>
                                <span class="text-slate-400 text-[11px] line-through ml-1">৳<?= number_format($regularPrice, 2) ?></span>
                                <span class="text-[10px] text-slate-500 block">per piece in bulk</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-[11px] text-slate-600 space-y-1">
                        <div class="flex justify-between"><span>Retail Price:</span> <strong>৳<?= number_format($regularPrice, 2) ?></strong></div>
                        <div class="flex justify-between"><span>Wholesale Rate:</span> <strong class="text-amber-600">৳<?= number_format($wPrice, 2) ?></strong></div>
                        <div class="flex justify-between font-bold text-emerald-600"><span>Your Profit / Piece:</span> <span>৳<?= number_format($regularPrice - $wPrice, 2) ?> (<?= round((($regularPrice - $wPrice) / $regularPrice) * 100) ?>%)</span></div>
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t">
                        <button type="button" onclick="addToCart(<?= $wp['id'] ?>, <?= $moq ?>, true)" class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs rounded-xl shadow transition flex items-center justify-center gap-1.5">
                            <i class="fas fa-bag-shopping"></i> Add <?= $moq ?> pcs to Cart
                        </button>
                        <a href="https://wa.me/88<?= htmlspecialchars($whatsapp) ?>?text=<?= urlencode('Hello OnlineBdMart, I want to order bulk: ' . $wp['name'] . ' (MOQ: ' . $moq . ' pcs @ ৳' . $wPrice . ')') ?>" target="_blank" class="p-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl shadow flex items-center justify-center">
                            <i class="fab fa-whatsapp text-base"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white p-12 rounded-3xl border text-center text-slate-400 text-xs">
                No wholesale products currently listed. Please contact WhatsApp support.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
