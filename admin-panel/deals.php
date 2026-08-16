<?php
$adminTitle = 'Flash Deals & Discounts Manager';
require_once __DIR__ . '/header.php';

$msg = '';
$error = '';

try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'save_timer_settings') {
            $keys = [
                'deals_banner_title' => $_POST['deals_banner_title'] ?? 'Big Deals on Top Fashion Gadgets',
                'deals_banner_subtitle' => $_POST['deals_banner_subtitle'] ?? 'Grab luxury chronograph watches and leather wallets at unbeatable discount prices.',
                'deals_badge_text' => $_POST['deals_badge_text'] ?? '% LIMITED TIME FLASH SALE',
                'deals_end_time' => $_POST['deals_end_time'] ?? date('Y-m-d H:i:s', strtotime('+3 days')),
                'deals_enabled' => isset($_POST['deals_enabled']) ? '1' : '0',
            ];

            foreach ($keys as $k => $v) {
                saveSetting($k, $v);
            }
            $msg = 'Flash Deals timer and banner settings saved!';
        } elseif ($action === 'add_deal_product') {
            $productId = (int)$_POST['product_id'];
            $dealPrice = (float)$_POST['deal_price'];

            if ($productId > 0 && $dealPrice > 0) {
                $stmt = $db->prepare("UPDATE products SET sale_price = ? WHERE id = ?");
                $stmt->execute([$dealPrice, $productId]);
                $msg = 'Product added to Flash Deals with special discounted price!';
            }
        } elseif ($action === 'remove_deal_product') {
            $productId = (int)$_POST['product_id'];
            $db->prepare("UPDATE products SET sale_price = NULL WHERE id = ?")->execute([$productId]);
            $msg = 'Product removed from Flash Deals!';
        }
    }

    $dealProducts = $db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 AND p.sale_price IS NOT NULL AND p.sale_price > 0 AND p.sale_price < p.price ORDER BY p.id DESC")->fetchAll();
    $allProducts = $db->query("SELECT id, name, price, sale_price FROM products WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
    $settings = getAllSettings();
} catch (Exception $e) {
    $error = $e->getMessage();
    $dealProducts = [];
    $allProducts = [];
    $settings = [];
}

$dealEndTime = $settings['deals_end_time'] ?? date('Y-m-d 23:59:59', strtotime('+3 days'));
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1.5"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="p-4 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-400 text-xs font-bold">
    <i class="fas fa-circle-exclamation mr-1.5"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: Deals Countdown Timer & Banner Controls -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
        <div>
            <span class="text-xs font-bold uppercase text-rose-400">Promotions Engine</span>
            <h2 class="text-base font-black text-white mt-1">Flash Deals Timer & Banner</h2>
            <p class="text-xs text-slate-400">Set countdown expiration target date & time and customize banner text.</p>
        </div>

        <form method="POST" action="deals.php" class="space-y-4 text-xs">
            <input type="hidden" name="action" value="save_timer_settings">

            <div>
                <label class="block text-slate-300 font-bold mb-1">Deals Section Title</label>
                <input type="text" name="deals_banner_title" value="<?= htmlspecialchars($settings['deals_banner_title'] ?? 'Big Deals on Top Fashion Gadgets') ?>" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>

            <div>
                <label class="block text-slate-300 font-bold mb-1">Subtitle Description</label>
                <input type="text" name="deals_banner_subtitle" value="<?= htmlspecialchars($settings['deals_banner_subtitle'] ?? 'Grab luxury chronograph watches and leather wallets at unbeatable discount prices.') ?>" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>

            <div>
                <label class="block text-slate-300 font-bold mb-1">Badge Text</label>
                <input type="text" name="deals_badge_text" value="<?= htmlspecialchars($settings['deals_badge_text'] ?? '% LIMITED TIME FLASH SALE') ?>" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-rose-400 font-bold outline-none">
            </div>

            <div>
                <label class="block text-slate-300 font-bold mb-1">Countdown Expiration Date & Time</label>
                <input type="datetime-local" name="deals_end_time" value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($dealEndTime))) ?>" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none">
            </div>

            <!-- Live Timer Preview Box -->
            <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-2">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Live Countdown Clock:</span>
                <div class="flex items-center justify-center gap-3 text-center">
                    <div><span id="admHours" class="text-xl font-black text-indigo-400 font-mono">12</span><span class="block text-[9px] text-slate-500 uppercase">Hours</span></div>
                    <span class="text-slate-600 font-bold">:</span>
                    <div><span id="admMins" class="text-xl font-black text-emerald-400 font-mono">48</span><span class="block text-[9px] text-slate-500 uppercase">Mins</span></div>
                    <span class="text-slate-600 font-bold">:</span>
                    <div><span id="admSecs" class="text-xl font-black text-amber-400 font-mono">26</span><span class="block text-[9px] text-slate-500 uppercase">Secs</span></div>
                </div>
            </div>

            <div class="pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="deals_enabled" <?= ($settings['deals_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-rose-500">
                    <span class="text-rose-400 font-bold">Show Flash Deals Section on Home Page</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-500 text-white font-extrabold rounded-xl shadow-lg transition">
                Save Deals & Countdown Timer
            </button>
        </form>
    </div>

    <!-- Right Column: Add Product to Deal & Active Deals List -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Add Product to Deals Box -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-plus-circle text-indigo-400"></i> Put Product into Flash Deals
            </h3>

            <form method="POST" action="deals.php" class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <input type="hidden" name="action" value="add_deal_product">

                <div class="sm:col-span-2">
                    <label class="block text-slate-300 font-bold mb-1">Select Catalog Product *</label>
                    <select name="product_id" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                        <option value="">Choose product...</option>
                        <?php foreach ($allProducts as $ap): ?>
                        <option value="<?= $ap['id'] ?>"><?= htmlspecialchars($ap['name']) ?> (Regular: ৳<?= number_format($ap['price'], 0) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Special Deal Price (৳) *</label>
                    <input type="number" step="0.01" name="deal_price" required placeholder="e.g. 1850" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-rose-400 font-bold outline-none">
                </div>

                <div class="sm:col-span-3">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow">
                        + Add to Active Deals
                    </button>
                </div>
            </form>
        </div>

        <!-- Active Flash Deals Table -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-white">Active Flash Deal Products (<?= count($dealProducts) ?>)</h3>
                <a href="../deals.php" target="_blank" class="text-xs font-bold text-rose-400 hover:underline">
                    View Live Deals Page &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase text-[10px]">
                            <th class="py-3">Photo</th>
                            <th class="py-3">Product Name</th>
                            <th class="py-3">Regular Price</th>
                            <th class="py-3">Deal Price</th>
                            <th class="py-3">Discount</th>
                            <th class="py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <?php if (!empty($dealProducts)): ?>
                            <?php foreach ($dealProducts as $dp): 
                                $discountPct = round((($dp['price'] - $dp['sale_price']) / $dp['price']) * 100);
                            ?>
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="py-3">
                                    <img src="/<?= ltrim($dp['image_path'], '/') ?>" class="w-12 h-12 object-cover rounded-xl bg-slate-950 border border-slate-800 shrink-0">
                                </td>
                                <td class="py-3 font-bold text-white max-w-xs truncate">
                                    <p class="truncate"><?= htmlspecialchars($dp['name']) ?></p>
                                    <span class="text-[10px] text-slate-400"><?= htmlspecialchars($dp['category_name'] ?? 'Accessories') ?></span>
                                </td>
                                <td class="py-3 text-slate-400 line-through">৳<?= number_format($dp['price'], 2) ?></td>
                                <td class="py-3 font-black text-rose-400 text-sm">৳<?= number_format($dp['sale_price'], 2) ?></td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500/20 text-rose-400">
                                        -<?= $discountPct ?>% OFF
                                    </span>
                                </td>
                                <td class="py-3 text-right">
                                    <form method="POST" action="deals.php" onsubmit="return confirm('Remove product from Flash Deals?');" class="inline">
                                        <input type="hidden" name="action" value="remove_deal_product">
                                        <input type="hidden" name="product_id" value="<?= $dp['id'] ?>">
                                        <button type="submit" class="px-3 py-1.5 bg-rose-500/20 hover:bg-rose-500 text-rose-400 hover:text-white rounded-xl text-[11px] font-bold transition">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="py-6 text-center text-slate-500">No active products in Flash Deals. Select a product above to add.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Admin live timer countdown
const targetDate = new Date("<?= addslashes($dealEndTime) ?>").getTime();

function updateCountdown() {
    const now = new Date().getTime();
    const dist = targetDate - now;

    if (dist <= 0) {
        document.getElementById('admHours').textContent = '00';
        document.getElementById('admMins').textContent = '00';
        document.getElementById('admSecs').textContent = '00';
        return;
    }

    const h = Math.floor((dist % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const m = Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60));
    const s = Math.floor((dist % (1000 * 60)) / 1000);

    document.getElementById('admHours').textContent = String(h).padStart(2, '0');
    document.getElementById('admMins').textContent = String(m).padStart(2, '0');
    document.getElementById('admSecs').textContent = String(s).padStart(2, '0');
}

setInterval(updateCountdown, 1000);
updateCountdown();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
