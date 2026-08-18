<?php
$orderId = (int)($_GET['id'] ?? 0);
$orderNum = trim($_GET['order_number'] ?? '');

require_once 'config/database.php';
$order = null;
$items = [];

if ($orderId > 0 || $orderNum) {
    try {
        $db = getDB();
        if ($orderId > 0) {
            $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
            $stmt->execute([$orderId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ? LIMIT 1");
            $stmt->execute([$orderNum]);
        }
        $order = $stmt->fetch();

        if ($order) {
            $itemStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $itemStmt->execute([$order['id']]);
            $items = $itemStmt->fetchAll();
        }
    } catch (Exception $e) {}
}

$pageTitle = 'Order Placed Successfully - OnlineBdMart';

$alreadyTracked = false;
$orderNo = '';
$purchaseEventId = '';
if ($order) {
    $orderNo = $order['order_number'] ?: ('OBM-' . $order['id']);
    $purchaseEventId = 'purchase_' . $orderNo;
    if (isset($_SESSION['tracked_purchases'][$orderNo])) {
        $alreadyTracked = true;
    } else {
        $_SESSION['tracked_purchases'][$orderNo] = time();
    }
}

require_once 'includes/header.php';
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-3xl border border-slate-200 p-8 sm:p-12 shadow-sm text-center space-y-6">
        <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-3xl mx-auto shadow-inner">
            <i class="fas fa-check"></i>
        </div>

        <div>
            <span class="text-xs font-black uppercase text-emerald-600 tracking-wider">Order Confirmed</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-serif text-slate-900 mt-1">Thank you for your order!</h1>
            <p class="text-xs text-slate-500 mt-2">Your order has been recorded in our system. Our delivery team will call you to confirm dispatch.</p>
        </div>

        <?php if ($order): ?>
        <div class="bg-slate-50 rounded-2xl p-6 border text-left text-xs space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b pb-3">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Order Tracking ID</span>
                    <span class="font-extrabold text-slate-900 text-sm font-mono"><?= htmlspecialchars($order['order_number']) ?></span>
                </div>
                <div class="text-right">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Total Amount</span>
                    <span class="font-black text-indigo-600 text-sm">৳<?= number_format($order['total_amount'], 2) ?></span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Customer Name</span>
                    <span class="font-bold text-slate-800"><?= htmlspecialchars($order['customer_name']) ?> (<?= htmlspecialchars($order['customer_phone']) ?>)</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">District / Delivery</span>
                    <span class="font-bold text-slate-800"><?= htmlspecialchars($order['district_name'] ?? 'Dhaka') ?> (COD)</span>
                </div>
            </div>

            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Delivery Address</span>
                <span class="text-slate-700"><?= htmlspecialchars($order['delivery_address']) ?></span>
            </div>

            <div class="pt-2 border-t">
                <span class="text-slate-400 block text-[10px] uppercase font-bold mb-2">Ordered Items</span>
                <div class="space-y-1.5">
                    <?php foreach ($items as $item): ?>
                    <div class="flex justify-between items-center font-medium">
                        <div>
                            <span class="font-bold text-slate-800"><?= htmlspecialchars($item['product_name']) ?> &times; <?= $item['quantity'] ?></span>
                            <?php if (!empty($item['color']) || !empty($item['size'])): ?>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <?php if (!empty($item['color'])): ?>
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-indigo-50 text-indigo-700">Color: <?= htmlspecialchars($item['color']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($item['size'])): ?>
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-50 text-amber-800">Size: <?= htmlspecialchars($item['size']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <span class="font-bold text-slate-900">৳<?= number_format($item['total_price'] ?: ($item['price'] * $item['quantity']), 2) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Financial Calculation Breakdown -->
                <div class="mt-4 pt-3 border-t border-slate-200 space-y-1.5 text-[11px]">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal:</span>
                        <span class="font-bold text-slate-900">৳<?= number_format((float)($order['subtotal'] ?? 0), 2) ?></span>
                    </div>
                    <?php if (!empty($order['discount_amount']) && (float)$order['discount_amount'] > 0): ?>
                    <div class="flex justify-between text-emerald-600 font-bold">
                        <span>Coupon Discount <?= !empty($order['coupon_code']) ? '(' . htmlspecialchars($order['coupon_code']) . ')' : '' ?>:</span>
                        <span>-৳<?= number_format((float)$order['discount_amount'], 2) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between text-slate-600">
                        <span>Delivery Charge:</span>
                        <span class="font-bold text-slate-900">৳<?= number_format((float)($order['delivery_cost'] ?? ($order['delivery_charge'] ?? 120)), 2) ?></span>
                    </div>
                    <div class="flex justify-between text-xs font-black text-slate-900 pt-1.5 border-t border-slate-200">
                        <span>Total Paid / Payable:</span>
                        <span class="text-indigo-600 font-mono">৳<?= number_format((float)($order['total_amount'] ?: $order['grand_total']), 2) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-4 pt-4">
            <a href="invoice.php?id=<?= $order['id'] ?>" target="_blank" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow inline-flex items-center gap-2">
                <i class="fas fa-file-invoice"></i> Print / Download Invoice
            </a>
            <a href="track-order.php?order_number=<?= urlencode($order['order_number']) ?>" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow inline-flex items-center gap-2">
                <i class="fas fa-truck-fast"></i> Live Track Order
            </a>
        </div>

        <?php if ($order && !$alreadyTracked): ?>
        <script>
        // DataLayer & Meta Pixel Purchase Event with Deduplicated event_id
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            event: 'purchase',
            ecommerce: {
                transaction_id: '<?= addslashes($orderNo) ?>',
                value: <?= (float)($order['total_amount'] ?: $order['grand_total']) ?>,
                currency: 'BDT',
                shipping: <?= (float)($order['delivery_cost'] ?? ($order['delivery_charge'] ?? 120)) ?>,
                coupon: '<?= addslashes($order['coupon_code'] ?? '') ?>',
                items: <?= json_encode(array_values(array_map(fn($it) => [
                    'item_id' => (string)($it['product_id'] ?? $it['id']),
                    'item_name' => $it['product_name'],
                    'price' => (float)$it['price'],
                    'quantity' => (int)$it['quantity'],
                    'item_variant' => (!empty($it['color']) ? 'Color: ' . $it['color'] : '') . (!empty($it['size']) ? ' Size: ' . $it['size'] : '')
                ], $items))) ?>
            },
            meta_event: 'Purchase',
            event_id: '<?= $purchaseEventId ?>',
            order_id: '<?= addslashes($orderNo) ?>',
            content_ids: <?= json_encode(array_values(array_map(fn($it) => (string)($it['product_id'] ?? $it['id']), $items))) ?>,
            content_type: 'product',
            value: <?= (float)($order['total_amount'] ?: $order['grand_total']) ?>,
            currency: 'BDT',
            num_items: <?= count($items) ?>
        });

        if (typeof fbq === 'function') {
            fbq('track', 'Purchase', {
                content_ids: <?= json_encode(array_values(array_map(fn($it) => (string)($it['product_id'] ?? $it['id']), $items))) ?>,
                content_type: 'product',
                value: <?= (float)($order['total_amount'] ?: $order['grand_total']) ?>,
                currency: 'BDT',
                num_items: <?= count($items) ?>,
                order_id: '<?= addslashes($orderNo) ?>'
            }, { eventID: '<?= $purchaseEventId ?>' });
        }
        </script>
        <?php endif; ?>
        <?php endif; ?>

        <div class="pt-4">
            <a href="shop.php" class="text-xs font-bold text-slate-500 hover:text-indigo-600">&larr; Return to Shopping</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
