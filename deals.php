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
    <div class="bg-gradient-to-r from-rose-950 via-slate-900 to-indigo-950 rounded-3xl p-8 sm:p-12 text-white shadow-xl mb-10 flex flex-col lg:flex-row items-center justify-between gap-8 border border-slate-800">
        <div class="space-y-3 max-w-xl text-center lg:text-left">
            <span class="px-3.5 py-1 bg-rose-500/20 text-rose-400 rounded-full text-xs font-black uppercase tracking-wider">🔥 Limited-Time Flash Sale</span>
            <h1 class="text-3xl sm:text-4xl font-extrabold font-serif"><?= htmlspecialchars($s['deals_banner_title'] ?? 'Super Deals & Discount Offers') ?></h1>
            <p class="text-rose-100 text-xs sm:text-sm"><?= htmlspecialchars($s['deals_banner_subtitle'] ?? 'Enjoy special discounts on original accessories, watches, and smart gadgets in Bangladesh.') ?></p>
        </div>

        <div class="bg-white/5 border border-white/10 backdrop-blur-md rounded-3xl p-6 flex items-center justify-center gap-4 text-center shrink-0">
            <div><div id="liveDealHours" class="w-16 sm:w-20 py-3 bg-slate-900 rounded-2xl text-2xl sm:text-3xl font-black text-indigo-400 font-mono">12</div><span class="text-[10px] text-slate-400 uppercase font-bold">Hours</span></div>
            <span class="text-2xl font-bold text-slate-600">:</span>
            <div><div id="liveDealMins" class="w-16 sm:w-20 py-3 bg-slate-900 rounded-2xl text-2xl sm:text-3xl font-black text-emerald-400 font-mono">48</div><span class="text-[10px] text-slate-400 uppercase font-bold">Mins</span></div>
            <span class="text-2xl font-bold text-slate-600">:</span>
            <div><div id="liveDealSecs" class="w-16 sm:w-20 py-3 bg-slate-900 rounded-2xl text-2xl sm:text-3xl font-black text-amber-400 font-mono">26</div><span class="text-[10px] text-slate-400 uppercase font-bold">Secs</span></div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-extrabold font-serif text-slate-900">Active Flash Deals (<?= count($deals) ?> Items)</h2>
        </div>

        <?php if (!empty($deals)): ?>
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                <?php foreach ($deals as $p): 
                    $discountPct = round((($p['price'] - $p['sale_price']) / $p['price']) * 100);
                ?>
                <div class="group bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl transition flex flex-col justify-between overflow-hidden relative p-4">
                    <span class="absolute top-3 left-3 z-10 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white shadow">-<?= $discountPct ?>% OFF</span>

                    <a href="product.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="relative block aspect-square bg-slate-100 rounded-xl overflow-hidden mb-3">
                        <img src="/<?= ltrim($p['image_path'], '/') ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    </a>

                    <div class="flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            <span class="text-[10px] font-bold uppercase text-slate-400"><?= htmlspecialchars($p['category_name'] ?? 'Accessories') ?></span>
                            <a href="product.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition line-clamp-2 block mt-0.5"><?= htmlspecialchars($p['name']) ?></a>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <span class="text-sm sm:text-base font-black text-rose-600 block">৳<?= number_format($p['sale_price'], 2) ?></span>
                                <span class="text-[10px] text-slate-400 line-through">৳<?= number_format($p['price'], 2) ?></span>
                            </div>
                            <button type="button" onclick="addToCart(<?= $p['id'] ?>)" class="w-full sm:w-auto px-3 py-2 bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-white text-xs font-black rounded-xl shadow transition flex items-center justify-center gap-1.5 active:scale-95">
                                <i class="fas fa-bag-shopping text-xs"></i> <span>Add to Bag</span>
                            </button>
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

<script>
const targetDateDeals = new Date("<?= addslashes($s['deals_end_time'] ?? date('Y-m-d 23:59:59', strtotime('+3 days'))) ?>").getTime();

function updateDealsPageCountdown() {
    const now = new Date().getTime();
    const dist = targetDateDeals - now;

    const elH = document.getElementById('liveDealHours');
    const elM = document.getElementById('liveDealMins');
    const elS = document.getElementById('liveDealSecs');
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

setInterval(updateDealsPageCountdown, 1000);
updateDealsPageCountdown();
</script>

<?php require_once 'includes/footer.php'; ?>
