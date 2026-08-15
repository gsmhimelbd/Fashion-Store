<?php
$adminTitle = 'Order Management';
require_once __DIR__ . '/header.php';

$msg = '';
try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($action === 'update_status') {
            $orderId = (int)$_POST['order_id'];
            $newStatus = trim($_POST['status']);

            $stmt = $db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$newStatus, $orderId]);
            $msg = "Order status updated to {$newStatus}!";
        }
    }

    $statusFilter = trim($_GET['status'] ?? '');
    $sql = "SELECT * FROM orders";
    $params = [];
    if ($statusFilter) {
        $sql .= " WHERE status = ?";
        $params[] = $statusFilter;
    }
    $sql .= " ORDER BY id DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
} catch (Exception $e) {
    $orders = [];
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <a href="orders.php" class="px-3.5 py-1.5 rounded-xl text-xs font-bold <?= empty($statusFilter) ? 'bg-indigo-600 text-white' : 'bg-slate-950 text-slate-400 hover:text-white' ?>">All Orders</a>
            <a href="orders.php?status=pending" class="px-3.5 py-1.5 rounded-xl text-xs font-bold <?= $statusFilter === 'pending' ? 'bg-amber-500 text-slate-950' : 'bg-slate-950 text-slate-400 hover:text-white' ?>">Pending</a>
            <a href="orders.php?status=processing" class="px-3.5 py-1.5 rounded-xl text-xs font-bold <?= $statusFilter === 'processing' ? 'bg-blue-600 text-white' : 'bg-slate-950 text-slate-400 hover:text-white' ?>">Processing</a>
            <a href="orders.php?status=shipped" class="px-3.5 py-1.5 rounded-xl text-xs font-bold <?= $statusFilter === 'shipped' ? 'bg-purple-600 text-white' : 'bg-slate-950 text-slate-400 hover:text-white' ?>">Shipped</a>
            <a href="orders.php?status=delivered" class="px-3.5 py-1.5 rounded-xl text-xs font-bold <?= $statusFilter === 'delivered' ? 'bg-emerald-600 text-white' : 'bg-slate-950 text-slate-400 hover:text-white' ?>">Delivered</a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase text-[10px]">
                    <th class="py-3">Tracking ID</th>
                    <th class="py-3">Customer Info</th>
                    <th class="py-3">District</th>
                    <th class="py-3">Payment</th>
                    <th class="py-3">Amount</th>
                    <th class="py-3">Status</th>
                    <th class="py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $o): ?>
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3.5 font-mono font-bold text-white"><?= htmlspecialchars($o['order_number']) ?></td>
                        <td class="py-3.5">
                            <p class="font-bold text-slate-200"><?= htmlspecialchars($o['customer_name']) ?></p>
                            <span class="text-[11px] text-slate-400"><?= htmlspecialchars($o['customer_phone']) ?></span>
                        </td>
                        <td class="py-3.5 text-slate-300"><?= htmlspecialchars($o['district_name'] ?? 'Dhaka') ?></td>
                        <td class="py-3.5">
                            <span class="uppercase font-bold text-[10px] px-2 py-0.5 rounded bg-slate-950 border border-slate-800 text-indigo-300"><?= htmlspecialchars($o['payment_method']) ?></span>
                        </td>
                        <td class="py-3.5 font-black text-indigo-400">৳<?= number_format($o['total_amount'], 2) ?></td>
                        <td class="py-3.5">
                            <form method="POST" action="orders.php" class="inline">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                <select name="status" onchange="this.form.submit()" class="px-2.5 py-1 rounded-lg bg-slate-950 border border-slate-800 text-xs font-bold outline-none <?= $o['status'] === 'delivered' ? 'text-emerald-400' : ($o['status'] === 'pending' ? 'text-amber-400' : 'text-indigo-400') ?>">
                                    <option value="pending" <?= $o['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="processing" <?= $o['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="shipped" <?= $o['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                    <option value="delivered" <?= $o['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                    <option value="cancelled" <?= $o['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </form>
                        </td>
                        <td class="py-3.5 text-right space-x-2">
                            <a href="order-detail.php?id=<?= $o['id'] ?>" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-xl text-white font-bold text-[11px]">View Details</a>
                            <a href="../invoice.php?id=<?= $o['id'] ?>" target="_blank" class="px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-500 rounded-xl text-white font-bold text-[11px]"><i class="fas fa-print"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="py-6 text-center text-slate-500">No orders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
