<?php
$adminTitle = 'Customer Orders Management';
require_once __DIR__ . '/header.php';

$msg = '';
try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($action === 'update_status') {
            $orderId = (int)$_POST['order_id'];
            $newStatus = trim($_POST['status']);

            $stmt = $db->prepare("UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$newStatus, $orderId]);
            $msg = "Order #{$orderId} updated to {$newStatus}!";

            // Send automated email on Delivered or Shipped
            require_once __DIR__ . '/../includes/smtp_mailer.php';
            if ($newStatus === 'delivered') {
                @sendOrderDeliveredEmailNotification($orderId);
            } elseif ($newStatus === 'shipped') {
                @sendOrderShippedEmailNotification($orderId);
            }
        }
    }

    $statusFilter = trim($_GET['status'] ?? '');
    $sql = "SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count, (SELECT product_image FROM order_items WHERE order_id = o.id LIMIT 1) as first_item_image FROM orders o";
    $params = [];
    if ($statusFilter) {
        $sql .= " WHERE o.status = ?";
        $params[] = $statusFilter;
    }
    $sql .= " ORDER BY o.id DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
} catch (Exception $e) {
    $orders = [];
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1.5"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase text-indigo-400">Transactions & Logistics</span>
            <h2 class="text-lg font-black text-white mt-1">Orders List (<?= count($orders) ?>)</h2>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="orders.php" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= empty($statusFilter) ? 'bg-indigo-600 text-white' : 'bg-slate-950 text-slate-400 hover:text-white' ?>">All (<?= count($orders) ?>)</a>
            <a href="orders.php?status=pending" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $statusFilter === 'pending' ? 'bg-amber-500 text-slate-950' : 'bg-slate-950 text-amber-400 hover:text-white' ?>">Pending</a>
            <a href="orders.php?status=processing" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $statusFilter === 'processing' ? 'bg-blue-600 text-white' : 'bg-slate-950 text-slate-400 hover:text-white' ?>">Processing</a>
            <a href="orders.php?status=shipped" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $statusFilter === 'shipped' ? 'bg-purple-600 text-white' : 'bg-slate-950 text-slate-400 hover:text-white' ?>">Shipped</a>
            <a href="orders.php?status=delivered" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $statusFilter === 'delivered' ? 'bg-emerald-600 text-white' : 'bg-slate-950 text-emerald-400 hover:text-white' ?>">Delivered</a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase text-[10px]">
                    <th class="py-3">Order ID</th>
                    <th class="py-3">Photo</th>
                    <th class="py-3">Customer Info</th>
                    <th class="py-3">District</th>
                    <th class="py-3">Method</th>
                    <th class="py-3">Amount</th>
                    <th class="py-3">Status</th>
                    <th class="py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $o): 
                        $img = !empty($o['first_item_image']) ? $o['first_item_image'] : 'images/products/watch-1.jpg';
                    ?>
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3.5 font-mono font-bold text-white">#<?= htmlspecialchars($o['order_number'] ?: $o['id']) ?></td>
                        <td class="py-3.5">
                            <img src="/<?= ltrim($img, '/') ?>" class="w-10 h-10 object-cover rounded-xl bg-slate-950 border border-slate-800 shrink-0">
                        </td>
                        <td class="py-3.5">
                            <p class="font-bold text-slate-200"><?= htmlspecialchars($o['customer_name']) ?></p>
                            <span class="text-[11px] text-slate-400 font-mono"><?= htmlspecialchars($o['customer_phone'] ?: $o['phone']) ?></span>
                        </td>
                        <td class="py-3.5 text-slate-300"><?= htmlspecialchars($o['district_name'] ?: $o['district'] ?: 'Dhaka') ?></td>
                        <td class="py-3.5">
                            <span class="uppercase font-bold text-[10px] px-2 py-0.5 rounded bg-slate-950 border border-slate-800 text-indigo-300"><?= htmlspecialchars($o['payment_method']) ?></span>
                        </td>
                        <td class="py-3.5 font-black text-indigo-400">৳<?= number_format($o['total_amount'] ?: $o['grand_total'], 2) ?></td>
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
                        <td class="py-3.5 text-right space-x-1.5">
                            <a href="order-detail.php?id=<?= $o['id'] ?>" class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-xl text-white font-bold text-[11px] inline-flex items-center gap-1" title="View Order">
                                <i class="fas fa-eye"></i> <span class="hidden sm:inline">View</span>
                            </a>
                            <a href="shipping-label.php?id=<?= $o['id'] ?>" target="_blank" class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black rounded-xl text-[11px] inline-flex items-center gap-1 shadow" title="Print Parcel Barcode Shipping Sticker">
                                <i class="fas fa-barcode"></i> <span class="hidden sm:inline">Sticker</span>
                            </a>
                            <a href="../invoice.php?id=<?= $o['id'] ?>" target="_blank" class="px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-500 rounded-xl text-white font-bold text-[11px] inline-flex items-center gap-1" title="Print Invoice">
                                <i class="fas fa-print"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="py-6 text-center text-slate-500">No orders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
