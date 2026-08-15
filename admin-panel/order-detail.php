<?php
$adminTitle = 'Order Details & Management';
require_once __DIR__ . '/header.php';

$orderId = (int)($_GET['id'] ?? 0);
$msg = '';

try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newStatus = trim($_POST['status'] ?? '');
        if ($newStatus) {
            $stmt = $db->prepare("UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$newStatus, $orderId]);
            $msg = "Order #{$orderId} status successfully updated to " . strtoupper($newStatus) . "!";
        }
    }

    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        header('Location: orders.php');
        exit;
    }

    // Join with products table to ensure product picture is always available
    $itemsStmt = $db->prepare("SELECT oi.*, p.image_path as catalog_image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll();
} catch (Exception $e) {
    header('Location: orders.php');
    exit;
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1.5"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
    <!-- Header with Order Reference and Actions -->
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-800 pb-6">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase text-slate-400">Order ID:</span>
                <span class="px-3 py-1 rounded-full text-xs font-black uppercase <?= $order['status'] === 'delivered' ? 'bg-emerald-500/20 text-emerald-400' : ($order['status'] === 'pending' ? 'bg-amber-500/20 text-amber-400' : 'bg-indigo-500/20 text-indigo-400') ?>">
                    <?= htmlspecialchars($order['status']) ?>
                </span>
            </div>
            <h2 class="text-2xl font-black font-mono text-white mt-1">#<?= htmlspecialchars($order['order_number'] ?: $order['id']) ?></h2>
            <p class="text-xs text-slate-400">Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <form method="POST" action="order-detail.php?id=<?= $order['id'] ?>" class="flex items-center gap-2">
                <select name="status" class="px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs font-bold text-white outline-none">
                    <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                    <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>📦 Processing</option>
                    <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>🚚 Dispatched / Shipped</option>
                    <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>✅ Delivered</option>
                    <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>❌ Cancelled</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs shadow transition">Update Status</button>
            </form>

            <a href="../invoice.php?id=<?= $order['id'] ?>" target="_blank" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-xs shadow flex items-center gap-1.5 border border-slate-700">
                <i class="fas fa-print"></i> <span>Print Tax Invoice</span>
            </a>
        </div>
    </div>

    <!-- Customer & Logistics Details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-2.5">
            <span class="text-slate-400 font-bold uppercase text-[10px] block">Customer Information</span>
            <p class="font-extrabold text-white text-base"><?= htmlspecialchars($order['customer_name']) ?></p>
            <p class="text-slate-300"><i class="fas fa-phone text-emerald-400 mr-1"></i> Phone: <strong class="font-mono text-white"><?= htmlspecialchars($order['customer_phone'] ?: $order['phone']) ?></strong></p>
            <?php if (!empty($order['customer_email'])): ?>
            <p class="text-slate-300"><i class="fas fa-envelope text-indigo-400 mr-1"></i> Email: <?= htmlspecialchars($order['customer_email']) ?></p>
            <?php endif; ?>
            <div class="pt-2 border-t border-slate-900">
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Delivery Address:</span>
                <p class="text-slate-200 mt-0.5"><?= htmlspecialchars($order['delivery_address'] ?: $order['address']) ?></p>
                <p class="text-emerald-400 font-bold mt-1">District: <?= htmlspecialchars($order['district_name'] ?: $order['district'] ?: 'Dhaka') ?></p>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-2.5">
            <span class="text-slate-400 font-bold uppercase text-[10px] block">Payment & Invoice Summary</span>
            <p class="text-slate-300">Payment Method: <strong class="uppercase text-white px-2 py-0.5 rounded bg-slate-900 border border-slate-800"><?= htmlspecialchars($order['payment_method'] ?: 'COD') ?></strong></p>
            <?php if (!empty($order['transaction_id'])): ?>
            <p class="text-slate-300">TrxID / Reference: <strong class="font-mono text-indigo-400 text-sm"><?= htmlspecialchars($order['transaction_id']) ?></strong></p>
            <?php endif; ?>
            
            <div class="pt-2 border-t border-slate-900 space-y-1">
                <div class="flex justify-between text-slate-400"><span>Items Subtotal:</span><span class="font-bold text-white">৳<?= number_format($order['subtotal'], 2) ?></span></div>
                <div class="flex justify-between text-slate-400"><span>Delivery Fee:</span><span class="font-bold text-white">৳<?= number_format($order['delivery_cost'] ?: $order['delivery_charge'] ?: 0, 2) ?></span></div>
                <div class="flex justify-between text-sm font-black pt-2 border-t border-slate-900">
                    <span class="text-white">Grand Total:</span>
                    <span class="text-indigo-400 text-base">৳<?= number_format($order['total_amount'] ?: $order['grand_total'], 2) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Ordered Items Table with Complete Picture -->
    <div class="space-y-3">
        <h3 class="text-xs font-bold uppercase text-slate-400 flex items-center gap-2">
            <i class="fas fa-box text-indigo-400"></i> Ordered Items with Pictures (<?= count($items) ?> Items)
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase text-[10px]">
                        <th class="py-2.5">Picture</th>
                        <th class="py-2.5">Product Name</th>
                        <th class="py-2.5">Unit Price</th>
                        <th class="py-2.5 text-center">Quantity</th>
                        <th class="py-2.5 text-right">Total Price</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    <?php foreach ($items as $it): 
                        $img = !empty($it['product_image']) ? $it['product_image'] : (!empty($it['catalog_image']) ? $it['catalog_image'] : 'images/products/watch-1.jpg');
                    ?>
                    <tr>
                        <td class="py-3">
                            <img src="/<?= ltrim($img, '/') ?>" class="w-14 h-14 object-cover rounded-xl bg-slate-950 border border-slate-800 shrink-0">
                        </td>
                        <td class="py-3 font-bold text-white">
                            <p><?= htmlspecialchars($it['product_name']) ?></p>
                            <?php if (!empty($it['is_wholesale'])): ?>
                            <span class="inline-block mt-0.5 text-[9px] bg-amber-500/20 text-amber-300 px-2 py-0.5 rounded font-black">Wholesale Bulk Item</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 text-slate-300">৳<?= number_format($it['price'], 2) ?></td>
                        <td class="py-3 text-center font-black text-white"><?= $it['quantity'] ?></td>
                        <td class="py-3 text-right font-black text-indigo-400 text-sm">৳<?= number_format($it['total_price'] ?: ($it['price'] * $it['quantity']), 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
