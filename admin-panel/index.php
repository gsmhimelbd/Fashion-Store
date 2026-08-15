<?php
$adminTitle = 'Dashboard Overview';
require_once __DIR__ . '/header.php';

try {
    $db = getDB();
    $totalOrders = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $totalRevenue = (float)$db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
    $totalProducts = (int)$db->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
    $wholesaleCount = (int)$db->query("SELECT COUNT(*) FROM products WHERE is_wholesale = 1")->fetchColumn();

    $recentOrders = $db->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5")->fetchAll();
    $topDistricts = $db->query("SELECT district_name, COUNT(*) as count, SUM(total_amount) as revenue FROM orders WHERE district_name IS NOT NULL AND district_name != '' GROUP BY district_name ORDER BY count DESC LIMIT 5")->fetchAll();
} catch (Exception $e) {
    $totalOrders = 0;
    $totalRevenue = 0;
    $totalProducts = 0;
    $wholesaleCount = 0;
    $recentOrders = [];
    $topDistricts = [];
}
?>

<!-- Metric Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-sm flex items-center justify-between">
        <div class="space-y-1">
            <span class="text-xs font-bold uppercase text-slate-400">Total Revenue</span>
            <h3 class="text-2xl font-black text-white">৳<?= number_format($totalRevenue, 2) ?></h3>
            <span class="text-[11px] text-emerald-400 font-semibold"><i class="fas fa-arrow-trend-up"></i> All 64 Districts</span>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center justify-center text-xl shrink-0">
            <i class="fas fa-sack-dollar"></i>
        </div>
    </div>

    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-sm flex items-center justify-between">
        <div class="space-y-1">
            <span class="text-xs font-bold uppercase text-slate-400">Total Orders</span>
            <h3 class="text-2xl font-black text-white"><?= number_format($totalOrders) ?></h3>
            <span class="text-[11px] text-indigo-400 font-semibold">COD & Digital Pay</span>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-xl shrink-0">
            <i class="fas fa-receipt"></i>
        </div>
    </div>

    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-sm flex items-center justify-between">
        <div class="space-y-1">
            <span class="text-xs font-bold uppercase text-slate-400">Active Catalog</span>
            <h3 class="text-2xl font-black text-white"><?= number_format($totalProducts) ?></h3>
            <span class="text-[11px] text-slate-400 font-semibold">Live in store</span>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 flex items-center justify-center text-xl shrink-0">
            <i class="fas fa-boxes-stacked"></i>
        </div>
    </div>

    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-sm flex items-center justify-between">
        <div class="space-y-1">
            <span class="text-xs font-bold uppercase text-amber-400">Wholesale B2B</span>
            <h3 class="text-2xl font-black text-white"><?= number_format($wholesaleCount) ?></h3>
            <span class="text-[11px] text-amber-300 font-semibold">Bulk MOQ enabled</span>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center text-xl shrink-0">
            <i class="fas fa-boxes-packing"></i>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Recent Orders Table -->
    <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-white">Recent Store Orders</h3>
            <a href="orders.php" class="text-xs font-bold text-indigo-400 hover:underline">View All Orders &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase text-[10px]">
                        <th class="py-3">Order ID</th>
                        <th class="py-3">Customer</th>
                        <th class="py-3">District</th>
                        <th class="py-3">Amount</th>
                        <th class="py-3">Status</th>
                        <th class="py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    <?php if (!empty($recentOrders)): ?>
                        <?php foreach ($recentOrders as $ro): ?>
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 font-mono font-bold text-white"><?= htmlspecialchars($ro['order_number']) ?></td>
                            <td class="py-3">
                                <p class="font-bold text-slate-200"><?= htmlspecialchars($ro['customer_name']) ?></p>
                                <span class="text-[11px] text-slate-400"><?= htmlspecialchars($ro['customer_phone']) ?></span>
                            </td>
                            <td class="py-3 text-slate-300"><?= htmlspecialchars($ro['district_name'] ?? 'Dhaka') ?></td>
                            <td class="py-3 font-black text-indigo-400">৳<?= number_format($ro['total_amount'], 2) ?></td>
                            <td class="py-3">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase <?= $ro['status'] === 'delivered' ? 'bg-emerald-500/20 text-emerald-400' : ($ro['status'] === 'pending' ? 'bg-amber-500/20 text-amber-400' : 'bg-indigo-500/20 text-indigo-400') ?>">
                                    <?= htmlspecialchars($ro['status']) ?>
                                </span>
                            </td>
                            <td class="py-3 text-right">
                                <a href="order-detail.php?id=<?= $ro['id'] ?>" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 rounded-lg text-white font-bold text-[11px]">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="py-6 text-center text-slate-500">No orders placed yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top District Order Distribution -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-white">Top Sales Districts</h3>
            <a href="delivery.php" class="text-xs font-bold text-indigo-400 hover:underline">Delivery &rarr;</a>
        </div>

        <div class="space-y-3">
            <?php if (!empty($topDistricts)): ?>
                <?php foreach ($topDistricts as $td): ?>
                <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs">
                    <div>
                        <span class="font-bold text-white block"><?= htmlspecialchars($td['district_name']) ?></span>
                        <span class="text-[11px] text-slate-400"><?= $td['count'] ?> Order(s)</span>
                    </div>
                    <span class="font-black text-emerald-400">৳<?= number_format($td['revenue'], 2) ?></span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-xs text-slate-500 py-4 text-center">No location metrics yet.</p>
            <?php endif; ?>
        </div>

        <div class="pt-2 border-t border-slate-800">
            <a href="analytics.php" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 rounded-xl text-white font-bold text-xs flex items-center justify-center gap-2">
                <i class="fas fa-chart-pie"></i> Open Full Analytics & Recovery
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
