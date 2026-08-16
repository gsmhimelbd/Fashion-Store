<?php
$orderId = (int)($_GET['id'] ?? 0);
require_once 'config/database.php';

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        die('Order not found.');
    }

    $itemsStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $itemsStmt->execute([$order['id']]);
    $items = $itemsStmt->fetchAll();

    $settingsStmt = $db->query("SELECT `key`, `value` FROM settings");
    $settings = $settingsStmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $e) {
    die('Error loading invoice: ' . $e->getMessage());
}

$storeName = $settings['store_name'] ?? 'OnlineBdMart';
$storePhone = $settings['store_phone'] ?? '01712-345678';
$storeEmail = $settings['store_email'] ?? 'support@onlinebdmart.com';
$storeAddress = $settings['store_address'] ?? 'Tangail, Dhaka Division, Bangladesh';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?= htmlspecialchars($order['order_number']) ?> - <?= htmlspecialchars($storeName) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: white; }
        }
    </style>
</head>
<body class="bg-slate-100 p-4 sm:p-8 font-sans text-slate-800 antialiased">
    <div class="max-w-3xl mx-auto mb-4 no-print flex justify-between items-center">
        <a href="order-success.php?id=<?= $order['id'] ?>" class="text-xs font-bold text-indigo-600">&larr; Back to Order</a>
        <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow hover:bg-indigo-700 flex items-center gap-2">
            <i class="fas fa-print"></i> Print Invoice
        </button>
    </div>

    <div class="max-w-3xl mx-auto bg-white p-8 sm:p-12 rounded-3xl shadow-sm border border-slate-200">
        <!-- Invoice Header -->
        <div class="flex justify-between items-start border-b pb-8">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900"><?= htmlspecialchars($storeName) ?></h1>
                <p class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($storeAddress) ?></p>
                <p class="text-xs text-slate-500">Phone: <?= htmlspecialchars($storePhone) ?> | Email: <?= htmlspecialchars($storeEmail) ?></p>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full font-black text-xs uppercase tracking-wider">TAX INVOICE</span>
                <p class="text-xs font-mono font-bold text-slate-900 mt-2">#<?= htmlspecialchars($order['order_number']) ?></p>
                <p class="text-[11px] text-slate-400">Date: <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
            </div>
        </div>

        <!-- Customer & Order Meta -->
        <div class="grid grid-cols-2 gap-8 py-6 border-b text-xs">
            <div>
                <span class="font-extrabold uppercase text-slate-400 text-[10px] block mb-1">Invoice To:</span>
                <p class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($order['customer_name']) ?></p>
                <p class="text-slate-600"><?= htmlspecialchars($order['customer_phone']) ?></p>
                <?php if ($order['customer_email']): ?><p class="text-slate-600"><?= htmlspecialchars($order['customer_email']) ?></p><?php endif; ?>
                <p class="text-slate-600 mt-1"><?= htmlspecialchars($order['delivery_address']) ?></p>
                <p class="text-slate-600 font-bold"><?= htmlspecialchars($order['district_name'] ?? 'Dhaka') ?>, Bangladesh</p>
            </div>
            <div>
                <span class="font-extrabold uppercase text-slate-400 text-[10px] block mb-1">Order Details:</span>
                <p class="text-slate-600">Payment: <strong class="uppercase text-slate-900"><?= htmlspecialchars($order['payment_method']) ?></strong></p>
                <p class="text-slate-600">Status: <strong class="uppercase text-indigo-600"><?= htmlspecialchars($order['status']) ?></strong></p>
                <?php if ($order['transaction_id']): ?>
                <p class="text-slate-600">TrxID: <strong class="font-mono text-slate-900"><?= htmlspecialchars($order['transaction_id']) ?></strong></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="py-6">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b text-slate-400 font-bold text-left uppercase text-[10px]">
                        <th class="py-2">Item Description</th>
                        <th class="py-2 text-center">Unit Price</th>
                        <th class="py-2 text-center">Qty</th>
                        <th class="py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($items as $it): ?>
                    <tr>
                        <td class="py-3 font-bold text-slate-800">
                            <div><?= htmlspecialchars($it['product_name']) ?></div>
                            <?php if (!empty($it['color']) || !empty($it['size'])): ?>
                            <div class="text-[10px] text-slate-500 font-normal">
                                <?php if (!empty($it['color'])): ?><span>Color: <?= htmlspecialchars($it['color']) ?></span><?php endif; ?>
                                <?php if (!empty($it['color']) && !empty($it['size'])): ?> | <?php endif; ?>
                                <?php if (!empty($it['size'])): ?><span>Size: <?= htmlspecialchars($it['size']) ?></span><?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 text-center text-slate-600">৳<?= number_format($it['price'], 2) ?></td>
                        <td class="py-3 text-center font-bold text-slate-900"><?= $it['quantity'] ?></td>
                        <td class="py-3 text-right font-black text-slate-900">৳<?= number_format($it['total_price'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="pt-4 border-t border-slate-200 flex justify-end">
            <div class="w-64 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-bold text-slate-900">৳<?= number_format($order['subtotal'], 2) ?></span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Delivery Fee:</span>
                    <span class="font-bold text-slate-900">৳<?= number_format($order['delivery_cost'], 2) ?></span>
                </div>
                <div class="pt-2 border-t flex justify-between text-sm font-black text-slate-900">
                    <span>Grand Total:</span>
                    <span class="text-indigo-600">৳<?= number_format($order['total_amount'], 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-12 pt-6 border-t text-center text-[11px] text-slate-400">
            Thank you for shopping with <?= htmlspecialchars($storeName) ?>! For order assistance, call <?= htmlspecialchars($storePhone) ?>.
        </div>
    </div>
</body>
</html>
