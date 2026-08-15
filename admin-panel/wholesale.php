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

            $stmt = $db->prepare("UPDATE products SET is_wholesale = ?, wholesale_price = ?, wholesale_moq = ? WHERE id = ?");
            $stmt->execute([$isWholesale, $wPrice, $moq, $id]);
            $msg = 'Wholesale settings updated!';
        }
    }

    $products = $db->query("SELECT * FROM products ORDER BY is_wholesale DESC, id DESC")->fetchAll();
} catch (Exception $e) {
    $products = [];
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase text-amber-400 tracking-wider">Bulk Pricing Portal</span>
            <h2 class="text-lg font-black text-white mt-1">Wholesale & B2B Inventory Controls</h2>
            <p class="text-xs text-slate-400">Enable or disable bulk purchase tiers, custom factory rates, and Minimum Order Quantities (MOQ) for retailers.</p>
        </div>
        <a href="../wholesale.php" target="_blank" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black rounded-xl text-xs shadow flex items-center gap-2">
            <span>View Wholesale Catalog</span> <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase text-[10px]">
                    <th class="py-3">Product Name</th>
                    <th class="py-3">Retail Price</th>
                    <th class="py-3">Wholesale Rate (৳)</th>
                    <th class="py-3">Min Qty (MOQ)</th>
                    <th class="py-3">B2B Enabled</th>
                    <th class="py-3 text-right">Update</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                <?php foreach ($products as $p): ?>
                <tr class="hover:bg-slate-800/40 transition">
                    <form method="POST" action="wholesale.php">
                        <input type="hidden" name="action" value="quick_update_wholesale">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <td class="py-3 font-bold text-white"><?= htmlspecialchars($p['name']) ?></td>
                        <td class="py-3 text-slate-400">৳<?= number_format($p['price'], 2) ?></td>
                        <td class="py-3">
                            <input type="number" step="0.01" name="wholesale_price" value="<?= $p['wholesale_price'] ?: round($p['price'] * 0.7) ?>" class="w-24 px-2.5 py-1 bg-slate-950 border border-slate-800 rounded-lg text-amber-400 font-bold outline-none">
                        </td>
                        <td class="py-3">
                            <input type="number" name="wholesale_moq" value="<?= $p['wholesale_moq'] ?: 5 ?>" class="w-16 px-2.5 py-1 bg-slate-950 border border-slate-800 rounded-lg text-white font-bold outline-none">
                        </td>
                        <td class="py-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_wholesale" <?= $p['is_wholesale'] ? 'checked' : '' ?> class="rounded text-amber-500">
                                <span class="text-[11px] <?= $p['is_wholesale'] ? 'text-amber-400 font-bold' : 'text-slate-500' ?>"><?= $p['is_wholesale'] ? 'Enabled' : 'Disabled' ?></span>
                            </label>
                        </td>
                        <td class="py-3 text-right">
                            <button type="submit" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg text-[11px] shadow">Save</button>
                        </td>
                    </form>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
