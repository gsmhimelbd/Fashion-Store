<?php
$adminTitle = 'Wholesale & B2B Manager';
require_once __DIR__ . '/header.php';

$msg = '';
try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($action === 'quick_update_wholesale') {
            $id = (int)$_POST['product_id'];
            $isWholesale = isset($_POST['is_wholesale']) ? 1 : 0;
            $wPrice = (float)$_POST['wholesale_price'];
            $moq = (int)$_POST['wholesale_moq'];

            $stmt = $db->prepare("UPDATE products SET is_wholesale = ?, wholesale_price = ?, wholesale_moq = ?, wholesale_min_qty = ? WHERE id = ?");
            $stmt->execute([$isWholesale, $wPrice, $moq, $moq, $id]);
            $msg = 'Wholesale bulk settings updated!';
        }
    }

    $products = $db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.is_wholesale DESC, p.id DESC")->fetchAll();
    $inquiries = $db->query("SELECT * FROM wholesale_inquiries ORDER BY id DESC LIMIT 10")->fetchAll();
} catch (Exception $e) {
    $products = [];
    $inquiries = [];
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1.5"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="space-y-6">
    <!-- Wholesale Catalog Manager -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold uppercase text-amber-400 tracking-wider">Bulk Pricing Portal</span>
                <h2 class="text-lg font-black text-white mt-1">Wholesale & B2B Product Catalog</h2>
                <p class="text-xs text-slate-400">Set wholesale factory prices, minimum order quantities (MOQ), and toggle B2B status with pictures.</p>
            </div>
            <a href="../wholesale.php" target="_blank" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black rounded-xl text-xs shadow flex items-center gap-2">
                <span>View Live Wholesale Page</span> <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase text-[10px]">
                        <th class="py-3">Photo</th>
                        <th class="py-3">Product Name</th>
                        <th class="py-3">Retail Price</th>
                        <th class="py-3">Wholesale Rate (৳)</th>
                        <th class="py-3">Min Qty (MOQ)</th>
                        <th class="py-3 text-center">B2B Enabled</th>
                        <th class="py-3 text-right">Quick Save</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    <?php foreach ($products as $p): ?>
                    <tr class="hover:bg-slate-800/40 transition">
                        <form method="POST" action="wholesale.php">
                            <input type="hidden" name="action" value="quick_update_wholesale">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <td class="py-3">
                                <img src="/<?= ltrim($p['image_path'], '/') ?>" class="w-12 h-12 object-cover rounded-xl bg-slate-950 border border-slate-800 shrink-0">
                            </td>
                            <td class="py-3 font-bold text-white max-w-xs truncate">
                                <p class="truncate"><?= htmlspecialchars($p['name']) ?></p>
                                <span class="text-[10px] text-slate-400"><?= htmlspecialchars($p['category_name'] ?? 'General') ?></span>
                            </td>
                            <td class="py-3 text-slate-300 font-semibold">৳<?= number_format($p['price'], 2) ?></td>
                            <td class="py-3">
                                <input type="number" step="0.01" name="wholesale_price" value="<?= $p['wholesale_price'] ?: round($p['price'] * 0.75) ?>" class="w-24 px-2.5 py-1 bg-slate-950 border border-slate-800 rounded-lg text-amber-400 font-bold outline-none">
                            </td>
                            <td class="py-3">
                                <input type="number" name="wholesale_moq" value="<?= $p['wholesale_moq'] ?: $p['wholesale_min_qty'] ?: 5 ?>" class="w-16 px-2.5 py-1 bg-slate-950 border border-slate-800 rounded-lg text-white font-bold outline-none text-center">
                            </td>
                            <td class="py-3 text-center">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_wholesale" <?= $p['is_wholesale'] ? 'checked' : '' ?> class="rounded text-amber-500">
                                    <span class="text-[11px] <?= $p['is_wholesale'] ? 'text-amber-400 font-bold' : 'text-slate-500' ?>"><?= $p['is_wholesale'] ? 'Active' : 'Off' ?></span>
                                </label>
                            </td>
                            <td class="py-3 text-right">
                                <button type="submit" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs shadow transition">Save</button>
                            </td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Wholesale Dealer Applications & Inquiries -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-4 shadow-sm">
        <h3 class="text-sm font-extrabold text-white">Wholesale / Dealer Applications (<?= count($inquiries) ?>)</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase text-[10px]">
                        <th class="py-2.5">Business Name</th>
                        <th class="py-2.5">Contact Person</th>
                        <th class="py-2.5">Phone / WhatsApp</th>
                        <th class="py-2.5">District</th>
                        <th class="py-2.5">Monthly Volume</th>
                        <th class="py-2.5 text-right">1-Click WhatsApp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    <?php if (!empty($inquiries)): ?>
                        <?php foreach ($inquiries as $inq): 
                            $wa = preg_replace('/[^0-9]/', '', $inq['phone'] ?: $inq['whatsapp']);
                            if (str_starts_with($wa, '0')) $wa = '88' . $wa;
                        ?>
                        <tr>
                            <td class="py-3 font-bold text-white"><?= htmlspecialchars($inq['business_name']) ?></td>
                            <td class="py-3 text-slate-300"><?= htmlspecialchars($inq['contact_person']) ?></td>
                            <td class="py-3 font-mono text-indigo-400"><?= htmlspecialchars($inq['phone']) ?></td>
                            <td class="py-3 text-slate-400"><?= htmlspecialchars($inq['district'] ?? 'N/A') ?></td>
                            <td class="py-3 text-amber-400 font-bold"><?= htmlspecialchars($inq['estimated_monthly_quantity'] ?? 'N/A') ?></td>
                            <td class="py-3 text-right">
                                <a href="https://wa.me/<?= $wa ?>?text=<?= urlencode('Hello ' . $inq['contact_person'] . ', thank you for your wholesale inquiry at OnlineBdMart!') ?>" target="_blank" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg font-bold inline-flex items-center gap-1">
                                    <i class="fab fa-whatsapp"></i> Chat
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="py-4 text-center text-slate-500">No wholesale inquiries submitted yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
