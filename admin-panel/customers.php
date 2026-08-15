<?php
$adminTitle = 'Customer Directory';
require_once __DIR__ . '/header.php';

try {
    $db = getDB();
    $customers = $db->query("SELECT customer_name, customer_phone, customer_email, district_name, COUNT(*) as total_orders, SUM(total_amount) as total_spent, MAX(created_at) as last_order_date FROM orders GROUP BY customer_phone, customer_name ORDER BY total_spent DESC")->fetchAll();
} catch (Exception $e) {
    $customers = [];
}
?>

<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase text-indigo-400">Buyer CRM</span>
            <h2 class="text-lg font-black text-white mt-1">Customer Accounts & Lifetime Value</h2>
        </div>
        <span class="px-3 py-1 bg-indigo-500/20 text-indigo-400 rounded-xl text-xs font-bold"><?= count($customers) ?> Unique Buyers</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase text-[10px]">
                    <th class="py-3">Customer Name</th>
                    <th class="py-3">Phone</th>
                    <th class="py-3">District</th>
                    <th class="py-3">Total Orders</th>
                    <th class="py-3">Lifetime Spent</th>
                    <th class="py-3">Last Order Date</th>
                    <th class="py-3 text-right">WhatsApp</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                <?php if (!empty($customers)): ?>
                    <?php foreach ($customers as $c): 
                        $wa = preg_replace('/[^0-9]/', '', $c['customer_phone'] ?? '');
                        if (str_starts_with($wa, '0')) $wa = '88' . $wa;
                    ?>
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3 font-bold text-white"><?= htmlspecialchars($c['customer_name']) ?></td>
                        <td class="py-3 font-mono text-slate-300"><?= htmlspecialchars($c['customer_phone']) ?></td>
                        <td class="py-3 text-slate-400"><?= htmlspecialchars($c['district_name'] ?? 'Dhaka') ?></td>
                        <td class="py-3 text-indigo-400 font-bold"><?= $c['total_orders'] ?></td>
                        <td class="py-3 font-black text-emerald-400">৳<?= number_format($c['total_spent'], 2) ?></td>
                        <td class="py-3 text-slate-400"><?= date('d M Y', strtotime($c['last_order_date'])) ?></td>
                        <td class="py-3 text-right">
                            <a href="https://wa.me/<?= $wa ?>" target="_blank" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 rounded-lg text-white font-bold text-[11px]"><i class="fab fa-whatsapp"></i> Chat</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="py-6 text-center text-slate-500">No customer history recorded.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
