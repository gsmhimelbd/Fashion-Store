<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
checkAdminAuth();

$orderId = (int)($_GET['id'] ?? 0);
if ($orderId <= 0) {
    die("Order ID is required.");
}

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        die("Order not found.");
    }

    $itemsStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll();

    $settings = getAllSettings();
    $storeName = $settings['store_name'] ?? 'OnlineBdMart';
    $storePhone = $settings['store_phone'] ?? '01775153740';
    $storeAddress = $settings['store_address'] ?? 'Dhaka, Bangladesh';
    $storeLogo = $settings['store_logo'] ?? 'images/logo.png';
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$orderNo = $order['order_number'] ?: ('OBM-' . $order['id']);
$grandTotal = (float)($order['total_amount'] ?: ($order['grand_total'] ?? 0));
$subtotal = (float)($order['subtotal'] ?? 0);
$deliveryCost = (float)($order['delivery_cost'] ?? ($order['delivery_charge'] ?? 120));
$discount = (float)($order['discount_amount'] ?? 0);
$isCod = strtolower($order['payment_method'] ?? 'cod') === 'cod';

// Total items count
$totalQty = 0;
foreach ($items as $it) {
    $totalQty += (int)($it['quantity'] ?? 1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Sticker #<?= htmlspecialchars($orderNo) ?> - <?= htmlspecialchars($storeName) ?></title>
    
    <!-- Tailwind CSS for UI -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- JsBarcode for High-Resolution Vector Barcode Rendering -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <style>
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .label-container {
                border: 2px solid #000 !important;
                box-shadow: none !important;
                margin: 0 auto !important;
                page-break-inside: avoid;
            }
            @page {
                size: 4in 6in;
                margin: 0.15in;
            }
        }
        .barcode-svg {
            max-height: 48px;
            width: 100%;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-6 px-4 font-sans text-slate-900">

    <!-- Top Action Toolbar (Hidden during Print) -->
    <div class="max-w-md mx-auto mb-5 no-print flex flex-wrap items-center justify-between gap-3 bg-white p-3.5 rounded-2xl shadow-md border border-slate-200">
        <div class="flex items-center gap-2">
            <a href="order-detail.php?id=<?= $order['id'] ?>" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center gap-1">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <span class="text-xs font-black text-slate-800 font-mono">#<?= htmlspecialchars($orderNo) ?></span>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs rounded-xl shadow transition flex items-center gap-1.5">
                <i class="fas fa-print"></i> <span>Print Sticker</span>
            </button>
            <a href="../invoice.php?id=<?= $order['id'] ?>" target="_blank" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl transition flex items-center gap-1">
                <i class="fas fa-file-invoice"></i> Invoice
            </a>
        </div>
    </div>

    <!-- 4" x 6" Thermal Courier Shipping Sticker Card -->
    <div class="label-container max-w-md mx-auto bg-white rounded-2xl border-2 border-slate-900 shadow-xl overflow-hidden text-black text-xs">
        
        <!-- 1. Header: Store Logo & Barcode Area -->
        <div class="p-3.5 border-b-2 border-slate-900 flex items-center justify-between gap-3 bg-slate-50">
            <div class="min-w-0">
                <h1 class="text-base font-black uppercase tracking-tight text-slate-950 font-serif leading-tight truncate"><?= htmlspecialchars($storeName) ?></h1>
                <p class="text-[10px] font-bold text-slate-700 font-mono">Hotline: <?= htmlspecialchars($storePhone) ?></p>
                <p class="text-[9px] text-slate-500 truncate">www.onlinebdmart.com</p>
            </div>
            <div class="text-right shrink-0">
                <span class="inline-block px-2.5 py-1 text-[11px] font-black uppercase rounded-lg border-2 border-slate-900 <?= $isCod ? 'bg-slate-900 text-white' : 'bg-emerald-600 text-white' ?>">
                    <?= $isCod ? 'CASH ON DELIVERY' : 'PREPAID ORDER' ?>
                </span>
                <p class="text-[10px] font-bold font-mono mt-0.5 text-slate-800"><?= date('d-M-Y h:i A', strtotime($order['created_at'])) ?></p>
            </div>
        </div>

        <!-- 2. Big Vector Code128 Barcode & Order Number -->
        <div class="py-2.5 px-3 border-b-2 border-slate-900 text-center bg-white">
            <svg id="orderBarcode" class="barcode-svg mx-auto"></svg>
            <div class="flex items-center justify-between text-[11px] font-mono font-black mt-0.5 px-2">
                <span>ORDER: <?= htmlspecialchars($orderNo) ?></span>
                <span>ITEMS: <?= $totalQty ?> PCS</span>
                <span>DEST: <?= strtoupper(htmlspecialchars($order['district_name'] ?: ($order['district'] ?: 'DHAKA'))) ?></span>
            </div>
        </div>

        <!-- 3. Consignee / Delivery Address (প্রাপক / কাস্টমার বিস্তারিত) -->
        <div class="p-3.5 border-b-2 border-slate-900 space-y-2 bg-white">
            <div class="flex items-center justify-between">
                <span class="px-2 py-0.5 rounded bg-slate-900 text-white font-black uppercase text-[10px] tracking-wider">
                    SHIP TO (প্রাপক)
                </span>
                <span class="text-[11px] font-black text-slate-900 uppercase">
                    📍 <?= htmlspecialchars($order['district_name'] ?: ($order['district'] ?: 'Dhaka')) ?>
                </span>
            </div>

            <div>
                <h2 class="text-sm font-black text-slate-950 uppercase leading-snug"><?= htmlspecialchars($order['customer_name']) ?></h2>
                <div class="flex flex-wrap items-center gap-2 mt-0.5 text-xs font-black font-mono">
                    <span class="bg-slate-100 px-2 py-0.5 rounded border border-slate-300">📞 <?= htmlspecialchars($order['customer_phone'] ?: ($order['phone'] ?: 'N/A')) ?></span>
                    <?php if (!empty($order['whatsapp']) && $order['whatsapp'] !== $order['customer_phone']): ?>
                    <span class="bg-slate-100 px-2 py-0.5 rounded border border-slate-300">WA: <?= htmlspecialchars($order['whatsapp']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Full Address Box -->
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-300 text-xs font-bold leading-relaxed text-slate-900">
                <p><?= htmlspecialchars($order['delivery_address'] ?: ($order['address'] ?: 'N/A')) ?></p>
                <p class="text-[11px] text-slate-700 mt-1">
                    <?php if (!empty($order['upazila'])): ?><strong>Thana/Upazila:</strong> <?= htmlspecialchars($order['upazila']) ?> • <?php endif; ?>
                    <?php if (!empty($order['post_office'])): ?><strong>Post:</strong> <?= htmlspecialchars($order['post_office']) ?> • <?php endif; ?>
                    <strong>District:</strong> <?= htmlspecialchars($order['district_name'] ?: ($order['district'] ?: 'Dhaka')) ?>
                </p>
            </div>
        </div>

        <!-- 4. Product Details & Variants Breakdown (কালার, সাইজ ও পরিমাণ) -->
        <div class="p-3.5 border-b-2 border-slate-900 space-y-2 bg-white">
            <span class="text-[10px] font-black uppercase text-slate-500 tracking-wider block">
                PRODUCT CONTENTS & VARIANTS (পণ্যের বিবরণ):
            </span>

            <div class="space-y-1.5 divide-y divide-slate-200">
                <?php foreach ($items as $idx => $it): 
                    $pTotal = (float)($it['total_price'] ?: ($it['price'] * $it['quantity']));
                ?>
                <div class="pt-1.5 first:pt-0 flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="font-black text-slate-950 text-xs leading-tight"><?= htmlspecialchars($it['product_name']) ?></p>
                        <?php if (!empty($it['color']) || !empty($it['size'])): ?>
                        <div class="flex flex-wrap items-center gap-1 mt-0.5">
                            <?php if (!empty($it['color'])): ?>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-black bg-indigo-50 text-indigo-900 border border-indigo-200">Color: <?= htmlspecialchars($it['color']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($it['size'])): ?>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-black bg-amber-50 text-amber-900 border border-amber-200">Size: <?= htmlspecialchars($it['size']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="text-right shrink-0 font-mono font-black text-xs">
                        <span><?= $it['quantity'] ?> × ৳<?= number_format($it['price'], 0) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 5. Big Highlights: COD Cash Collection & Instructions -->
        <div class="p-3.5 bg-slate-50 flex items-center justify-between gap-3 border-b-2 border-slate-900">
            <div>
                <span class="text-[10px] font-black uppercase text-slate-500 block">Total Amount Payable:</span>
                <div class="text-xs font-bold text-slate-700">
                    <span>Sub: ৳<?= number_format($subtotal, 0) ?></span>
                    <span>+ Del: ৳<?= number_format($deliveryCost, 0) ?></span>
                    <?php if ($discount > 0): ?><span>- Disc: ৳<?= number_format($discount, 0) ?></span><?php endif; ?>
                </div>
            </div>
            <div class="text-right p-2 rounded-xl bg-slate-900 text-white shrink-0 border border-slate-900">
                <span class="text-[9px] font-extrabold uppercase text-amber-300 block tracking-wider"><?= $isCod ? 'CASH TO COLLECT (COD)' : 'PAID IN FULL' ?></span>
                <span class="text-lg font-black font-mono tracking-tight text-white leading-none">৳<?= number_format($grandTotal, 0) ?></span>
            </div>
        </div>

        <!-- 6. Courier Instructions & Return Address Footer -->
        <div class="p-3 bg-white text-[9px] font-bold text-slate-700 leading-tight space-y-1">
            <div class="flex items-center gap-1.5 text-rose-700">
                <i class="fas fa-triangle-exclamation"></i>
                <span class="uppercase font-black">Instruction: ডেলিভারির পূর্বে কাস্টমারকে কল করুন। পণ্য দেখে চেক করার সুযোগ দিন।</span>
            </div>
            <div class="border-t pt-1 flex justify-between text-slate-500">
                <span>Return to: <strong><?= htmlspecialchars($storeName) ?> Dispatch Hub</strong>, Dhaka</span>
                <span>Helpline: <strong><?= htmlspecialchars($storePhone) ?></strong></span>
            </div>
        </div>

    </div>

    <!-- Render Vector Barcode with JsBarcode -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            JsBarcode("#orderBarcode", "<?= addslashes($orderNo) ?>", {
                format: "CODE128",
                lineColor: "#000000",
                width: 2,
                height: 42,
                displayValue: false,
                margin: 0
            });
        });
    </script>
</body>
</html>
