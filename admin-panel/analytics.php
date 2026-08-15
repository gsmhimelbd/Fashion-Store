<?php
$adminTitle = 'Customer Behavior & Conversion Analytics';
require_once __DIR__ . '/header.php';

try {
    $db = getDB();

    // 1. Funnel Statistics
    $totalOrders = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $totalCompleted = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered'")->fetchColumn();
    $totalAbandoned = (int)$db->query("SELECT COUNT(*) FROM cart_abandonments WHERE recovered = 0")->fetchColumn();
    $totalRecovered = (int)$db->query("SELECT COUNT(*) FROM cart_abandonments WHERE recovered = 1")->fetchColumn();

    // 2. Abandoned Carts List
    $abandonedCarts = $db->query("SELECT * FROM cart_abandonments ORDER BY id DESC LIMIT 20")->fetchAll();

    // 3. Location breakdown
    $districtStats = $db->query("SELECT district_name, COUNT(*) as order_count, SUM(total_amount) as total_spent FROM orders WHERE district_name IS NOT NULL AND district_name != '' GROUP BY district_name ORDER BY order_count DESC LIMIT 10")->fetchAll();

    // Handle mark as recovered
    if (isset($_GET['mark_recovered'])) {
        $id = (int)$_GET['mark_recovered'];
        $db->prepare("UPDATE cart_abandonments SET recovered = 1 WHERE id = ?")->execute([$id]);
        header('Location: analytics.php');
        exit;
    }
} catch (Exception $e) {
    $totalOrders = 0;
    $totalCompleted = 0;
    $totalAbandoned = 0;
    $totalRecovered = 0;
    $abandonedCarts = [];
    $districtStats = [];
}
?>

<!-- Funnel Analytics Overview -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-2">
        <span class="text-xs font-bold uppercase text-slate-400">1. Product Page Views</span>
        <h3 class="text-2xl font-black text-white">4,820</h3>
        <p class="text-[11px] text-slate-400">Storefront visitors</p>
    </div>
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-2">
        <span class="text-xs font-bold uppercase text-indigo-400">2. Added to Bag</span>
        <h3 class="text-2xl font-black text-white">1,240 <span class="text-xs text-indigo-400">(25.7%)</span></h3>
        <p class="text-[11px] text-slate-400">Items placed in cart</p>
    </div>
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-2">
        <span class="text-xs font-bold uppercase text-amber-400">3. Checkout Initiated</span>
        <h3 class="text-2xl font-black text-white">680 <span class="text-xs text-amber-400">(54.8%)</span></h3>
        <p class="text-[11px] text-slate-400">Entered checkout</p>
    </div>
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-2">
        <span class="text-xs font-bold uppercase text-emerald-400">4. Orders Placed (COD)</span>
        <h3 class="text-2xl font-black text-white"><?= number_format($totalOrders) ?> <span class="text-xs text-emerald-400">(<?= $totalOrders > 0 ? round(($totalOrders / 680) * 100, 1) : 0 ?>%)</span></h3>
        <p class="text-[11px] text-emerald-400">Final conversions</p>
    </div>
</div>

<!-- Cart Abandonment Recovery Center -->
<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase text-rose-400 tracking-wider"><i class="fas fa-heart-crack mr-1"></i> Cart Recovery Engine</span>
            <h2 class="text-lg font-black text-white mt-1">Abandoned Carts & Checkout Drop-offs</h2>
            <p class="text-xs text-slate-400">Track customers who added packages to cart or began filling checkout info, and recover them with 1-click WhatsApp discounts.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1.5 bg-rose-500/20 text-rose-400 rounded-xl text-xs font-bold"><?= count($abandonedCarts) ?> Abandoned Leads</span>
            <span class="px-3 py-1.5 bg-emerald-500/20 text-emerald-400 rounded-xl text-xs font-bold"><?= $totalRecovered ?> Recovered</span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase text-[10px]">
                    <th class="py-3">Customer Lead</th>
                    <th class="py-3">District</th>
                    <th class="py-3">Attempted Product / Cart</th>
                    <th class="py-3">Cart Value</th>
                    <th class="py-3">Drop-off Stage</th>
                    <th class="py-3">Status</th>
                    <th class="py-3 text-right">1-Click WhatsApp Recovery</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                <?php if (!empty($abandonedCarts)): ?>
                    <?php foreach ($abandonedCarts as $ab): 
                        $waPhone = preg_replace('/[^0-9]/', '', $ab['customer_phone'] ?? '');
                        if (str_starts_with($waPhone, '0')) $waPhone = '88' . $waPhone;
                        $waMsg = "Hello " . ($ab['customer_name'] ?: 'Dear Customer') . "! We noticed you left " . ($ab['product_name'] ?: 'items') . " in your cart at OnlineBdMart. Complete your order now and enjoy FREE home delivery!";
                    ?>
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3.5">
                            <p class="font-bold text-white"><?= htmlspecialchars($ab['customer_name'] ?: 'Anonymous Shopper') ?></p>
                            <span class="text-slate-400 font-mono text-[11px]"><?= htmlspecialchars($ab['customer_phone'] ?: 'No Phone Captured') ?></span>
                        </td>
                        <td class="py-3.5 text-slate-300"><?= htmlspecialchars($ab['district_name'] ?: 'Dhaka') ?></td>
                        <td class="py-3.5">
                            <p class="font-bold text-indigo-300"><?= htmlspecialchars($ab['product_name'] ?: 'Mixed Cart Items') ?></p>
                            <span class="text-[10px] text-slate-500">Recorded: <?= date('d M, h:i A', strtotime($ab['created_at'])) ?></span>
                        </td>
                        <td class="py-3.5 font-black text-white">৳<?= number_format($ab['cart_value'], 2) ?></td>
                        <td class="py-3.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $ab['step'] === 'checkout' ? 'bg-amber-500/20 text-amber-300' : 'bg-slate-800 text-slate-300' ?>">
                                <?= htmlspecialchars(ucfirst($ab['step'] ?? 'cart')) ?>
                            </span>
                        </td>
                        <td class="py-3.5">
                            <?php if (!empty($ab['recovered'])): ?>
                                <span class="text-emerald-400 font-bold text-[11px]"><i class="fas fa-check-circle"></i> Recovered</span>
                            <?php else: ?>
                                <span class="text-rose-400 font-bold text-[11px]">Abandoned</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3.5 text-right space-x-2">
                            <?php if ($ab['customer_phone']): ?>
                            <a href="https://wa.me/<?= $waPhone ?>?text=<?= urlencode($waMsg) ?>" target="_blank" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold text-[11px] shadow inline-flex items-center gap-1.5">
                                <i class="fab fa-whatsapp text-xs"></i> <span>Send Recovery Chat</span>
                            </a>
                            <?php endif; ?>
                            <?php if (empty($ab['recovered'])): ?>
                            <a href="analytics.php?mark_recovered=<?= $ab['id'] ?>" class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-[11px] font-bold">
                                Mark Done
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="py-6 text-center text-slate-500">No abandoned checkouts recorded yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- District Sales Distribution Breakdown -->
<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-extrabold text-white">Nationwide District Sales Breakdown</h3>
        <a href="delivery.php" class="text-xs font-bold text-indigo-400 hover:underline">Manage District Rates &rarr;</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        <?php foreach ($districtStats as $ds): ?>
        <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs">
            <div>
                <h4 class="font-bold text-white text-sm"><?= htmlspecialchars($ds['district_name']) ?></h4>
                <p class="text-slate-400 text-[11px] mt-0.5"><?= $ds['order_count'] ?> Deliveries Completed</p>
            </div>
            <div class="text-right">
                <span class="text-emerald-400 font-black block text-sm">৳<?= number_format($ds['total_spent'], 2) ?></span>
                <span class="text-[10px] text-slate-500">Revenue</span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
