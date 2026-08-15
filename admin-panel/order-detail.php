<?php
$adminTitle = 'Order Details';
require_once __DIR__ . '/header.php';

$orderId = (int)($_GET['id'] ?? 0);
$msg = '';

try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newStatus = trim($_POST['status'] ?? '');
        if ($newStatus) {
            $stmt = $db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$newStatus, $orderId]);
            $msg = 'Order status updated!';
        }
    }

    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        header('Location: orders.php');
        exit;
    }

    $itemsStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll();
} catch (Exception $e) {
    header('Location: orders.php');
    exit;
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-800 pb-6">
        <div>
            <span class="text-xs font-bold uppercase text-slate-400">Order Reference</span>
            <h2 class="text-xl font-black font-mono text-white mt-1"><?= htmlspecialchars($order['order_number']) ?></h2>
        </div>
        <div class="flex items-center gap-3">
            <form method="POST" action="order-detail.php?id=<?= $order['id'] ?>" class="flex items-center gap-2">
                <select name="status" class="px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs font-bold text-white outline-none">
                    <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs shadow">Update</button>
            </form>
            <a href="../invoice.php?id=<?= $order['id'] ?>" target="_blank" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-xs shadow flex items-center gap-1.5">
                <i class="fas fa-print"></i> <span>Invoice</span>
            </a>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-2">
            <span class="text-slate-400 font-bold uppercase text-[10px] block">Customer Information</span>
            <p class="font-bold text-white text-sm"><?= htmlspecialchars($order['customer_name']) ?></p>
            <p class="text-slate-300">Phone: <strong><?= htmlspecialchars($order['customer_phone']) ?></strong></p>
            <p class="text-slate-300">Email: <?= htmlspecialchars($order['customer_email'] ?: 'N/A') ?></p>
            <p class="text-slate-300 mt-2">Delivery Address: <?= htmlspecialchars($order['delivery_address']) ?></p>
            <p class="text-slate-300 font-bold">District: <?= htmlspecialchars($order['district_name'] ?? 'Dhaka') ?></p>
        </div>

        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-2">
            <span class="text-slate-400 font-bold uppercase text-[10px] block">Payment & Logistics</span>
            <p class="text-slate-300">Payment Method: <strong class="uppercase text-white"><?= htmlspecialchars($order['payment_method']) ?></strong></p>
            <?php if ($order['transaction_id']): ?>
            <p class="text-slate-300">TrxID: <strong class="font-mono text-indigo-400"><?= htmlspecialchars($order['transaction_id']) ?></strong></p>
            <?php endif; ?>
            <p class="text-slate-300">Ordered At: <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
            <p class="text-slate-300">Subtotal: ৳<?= number_format($order['subtotal'], 2) ?></p>
            <p class="text-slate-300">Delivery Fee: ৳<?= number_format($order['delivery_cost'], 2) ?></p>
            <p class="text-indigo-400 font-black text-base mt-2">Grand Total: ৳<?= number_format($order['total_amount'], 2) ?></p>
        </div>
    </div>

    <!-- Items Table -->
    <div class="space-y-3">
        <h3 class="text-xs font-bold uppercase text-slate-400">Order Items</h3>
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase text-[10px]">
                    <th class="py-2">Product Name</th>
                    <th class="py-2">Unit Price</th>
                    <th class="py-2">Quantity</th>
                    <th class="py-2 text-right">Total Price</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                <?php foreach ($items as $it): ?>
                <tr>
                    <td class="py-3 font-bold text-white"><?= htmlspecialchars($it['product_name']) ?></td>
                    <td class="py-3 text-slate-400">৳<?= number_format($it['price'], 2) ?></td>
                    <td class="py-3 text-slate-200"><?= $it['quantity'] ?></td>
                    <td class="py-3 text-right font-black text-indigo-400">৳<?= number_format($it['total_price'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
