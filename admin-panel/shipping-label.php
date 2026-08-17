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
    
    <!-- Tailwind CSS for UI Controls -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- JsBarcode for Vector Barcode -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <style>
        /* Print Styles */
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
                color: #000 !important;
            }
            .no-print {
                display: none !important;
            }
            
            /* Universal Page Sizing */
            @page {
                size: auto;
                margin: 3mm;
            }
            
            .print-wrapper-80mm {
                width: 76mm !important;
                max-width: 76mm !important;
                padding: 2mm !important;
                margin: 0 auto !important;
                border: 2px solid #000 !important;
                page-break-inside: avoid;
            }

            .print-wrapper-4x6 {
                width: 4in !important;
                max-width: 4in !important;
                margin: 0 auto !important;
                border: 2px solid #000 !important;
                page-break-inside: avoid;
            }

            .print-wrapper-a4 {
                width: 100mm !important;
                max-width: 100mm !important;
                margin: 10mm auto !important;
                border: 2px dashed #000 !important;
                page-break-inside: avoid;
            }
        }

        /* Screen Preview Styles */
        .preview-80mm {
            width: 80mm;
            max-width: 320px;
        }
        .preview-4x6 {
            width: 100mm;
            max-width: 420px;
        }
        .preview-a4 {
            width: 110mm;
            max-width: 460px;
        }
        .barcode-svg {
            max-height: 44px;
            width: 100%;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-6 px-4 font-sans text-slate-900">

    <!-- Top Action Toolbar (Hidden during Print) -->
    <div class="max-w-lg mx-auto mb-6 no-print space-y-3">
        <div class="bg-white p-5 rounded-3xl shadow-xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <a href="order-detail.php?id=<?= $order['id'] ?>" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center gap-1">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <span class="text-xs font-black text-slate-800 font-mono">#<?= htmlspecialchars($orderNo) ?></span>
                </div>

                <div class="flex items-center gap-2">
                    <button onclick="window.print()" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs rounded-xl shadow-lg transition flex items-center gap-1.5 active:scale-95">
                        <i class="fas fa-print"></i> <span>Print Sticker</span>
                    </button>
                    <a href="../invoice.php?id=<?= $order['id'] ?>" target="_blank" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl transition flex items-center gap-1">
                        <i class="fas fa-file-invoice"></i> Invoice
                    </a>
                </div>
            </div>

            <!-- Printer Mode Selector Switch -->
            <div class="pt-3 border-t space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-extrabold text-slate-700">Choose Printer Type (প্রিন্টারের ধরণ বেছে নিন):</span>
                </div>
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <button type="button" id="btnMode80" onclick="setLabelMode('80mm')" class="py-2 px-2 bg-amber-500 text-slate-950 font-black rounded-xl shadow-sm text-center transition">
                        🏷️ 80mm POS Roll
                    </button>
                    <button type="button" id="btnMode4x6" onclick="setLabelMode('4x6')" class="py-2 px-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-center transition">
                        📦 4" × 6" Sticker
                    </button>
                    <button type="button" id="btnModeA4" onclick="setLabelMode('a4')" class="py-2 px-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-center transition">
                        📄 A4 Sheet Paper
                    </button>
                </div>
            </div>

            <!-- Step-by-Step Print Instructions Guide -->
            <div class="p-3 bg-indigo-50/70 rounded-2xl border border-indigo-100 text-[11px] text-slate-700 space-y-1">
                <p class="font-bold text-indigo-950 flex items-center gap-1.5">
                    <i class="fas fa-lightbulb text-amber-500"></i> প্রিন্ট ডায়ালগ সেটিংস গাইড:
                </p>
                <ul class="list-disc list-inside space-y-0.5 text-slate-600">
                    <li><strong>80mm POS থার্মাল প্রিন্টারে:</strong> Destination-এ আপনার থার্মাল প্রিন্টার সিলেক্ট করুন।</li>
                    <li><strong>সাধারণ A4 প্রিন্টারে:</strong> পেপার সাইজ <strong>A4</strong> রেখেই প্রিন্ট করুন, পার্সেল বক্সে লাগাতে স্টিকারটি কেচি দিয়ে কেটে নিন।</li>
                    <li><strong>Margins:</strong> None বা Minimum সিলেক্ট করলে সবচেয়ে ভালো প্রিন্ট আসবে।</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- PRINTABLE SHIPPING STICKER CARD -->
    <div id="stickerContainer" class="label-container preview-80mm mx-auto bg-white rounded-2xl border-2 border-black shadow-2xl overflow-hidden text-black text-xs font-sans transition-all duration-200">
        
        <!-- 1. Header: Store Name, Hotline & COD Status -->
        <div class="p-2.5 border-b-2 border-black text-center bg-white space-y-1">
            <h1 class="text-base font-black uppercase tracking-tight text-black font-serif leading-tight truncate"><?= htmlspecialchars($storeName) ?></h1>
            <div class="flex items-center justify-between text-[10px] font-bold font-mono px-1">
                <span>📞 <?= htmlspecialchars($storePhone) ?></span>
                <span class="px-2 py-0.5 rounded bg-black text-white font-black uppercase text-[9px]"><?= $isCod ? 'CASH ON DELIVERY' : 'PAID ORDER' ?></span>
            </div>
        </div>

        <!-- 2. Scannable Code128 Vector Barcode & Order Number -->
        <div class="py-2 px-2 border-b-2 border-black text-center bg-white">
            <svg id="orderBarcode" class="barcode-svg mx-auto"></svg>
            <div class="flex items-center justify-between text-[10px] font-mono font-black mt-0.5 px-1">
                <span>ORDER: <?= htmlspecialchars($orderNo) ?></span>
                <span>DEST: <?= strtoupper(htmlspecialchars($order['district_name'] ?: ($order['district'] ?: 'DHAKA'))) ?></span>
            </div>
        </div>

        <!-- 3. Consignee / Delivery Address (প্রাপক / কাস্টমার বিস্তারিত) -->
        <div class="p-2.5 border-b-2 border-black space-y-1.5 bg-white">
            <div class="flex items-center justify-between">
                <span class="px-1.5 py-0.2 rounded bg-black text-white font-black uppercase text-[9px]">
                    SHIP TO (প্রাপক)
                </span>
                <span class="text-[10px] font-black text-black uppercase">
                    📍 <?= htmlspecialchars($order['district_name'] ?: ($order['district'] ?: 'Dhaka')) ?>
                </span>
            </div>

            <div>
                <h2 class="text-xs font-black text-black uppercase leading-tight"><?= htmlspecialchars($order['customer_name']) ?></h2>
                <p class="text-xs font-black font-mono mt-0.5">📞 <?= htmlspecialchars($order['customer_phone'] ?: ($order['phone'] ?: 'N/A')) ?></p>
            </div>

            <!-- Full Address Box -->
            <div class="p-2 rounded-lg bg-slate-50 border border-black/80 text-[11px] font-bold leading-snug text-black">
                <p><?= htmlspecialchars($order['delivery_address'] ?: ($order['address'] ?: 'N/A')) ?></p>
                <p class="text-[10px] text-slate-800 mt-1 border-t border-slate-300 pt-0.5">
                    <?php if (!empty($order['upazila'])): ?><strong>Thana:</strong> <?= htmlspecialchars($order['upazila']) ?> • <?php endif; ?>
                    <?php if (!empty($order['post_office'])): ?><strong>Post:</strong> <?= htmlspecialchars($order['post_office']) ?> • <?php endif; ?>
                    <strong>District:</strong> <?= htmlspecialchars($order['district_name'] ?: ($order['district'] ?: 'Dhaka')) ?>
                </p>
            </div>
        </div>

        <!-- 4. Product Details & Variants Breakdown (কালার, সাইজ ও পরিমাণ) -->
        <div class="p-2.5 border-b-2 border-black space-y-1 bg-white">
            <div class="flex items-center justify-between text-[9px] font-black uppercase text-slate-700">
                <span>ITEMS (পণ্যের বিবরণ):</span>
                <span>QTY: <?= $totalQty ?> PCS</span>
            </div>

            <div class="space-y-1.5 divide-y divide-slate-200">
                <?php foreach ($items as $idx => $it): ?>
                <div class="pt-1 first:pt-0 flex items-start justify-between gap-1 text-[11px]">
                    <div class="min-w-0 flex-1">
                        <p class="font-black text-black leading-tight"><?= htmlspecialchars($it['product_name']) ?></p>
                        <?php if (!empty($it['color']) || !empty($it['size'])): ?>
                        <div class="flex flex-wrap items-center gap-1 mt-0.5 text-[9px] font-bold text-slate-800">
                            <?php if (!empty($it['color'])): ?><span>Color: <?= htmlspecialchars($it['color']) ?></span><?php endif; ?>
                            <?php if (!empty($it['color']) && !empty($it['size'])): ?><span>•</span><?php endif; ?>
                            <?php if (!empty($it['size'])): ?><span>Size: <?= htmlspecialchars($it['size']) ?></span><?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="text-right shrink-0 font-mono font-black text-xs pl-1">
                        <span><?= $it['quantity'] ?> × ৳<?= number_format($it['price'], 0) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 5. Big Highlights: COD Cash Collection (কুরিয়ার কত টাকা ক্যাশ সংগ্রহ করবে) -->
        <div class="p-2.5 bg-slate-50 flex items-center justify-between gap-2 border-b-2 border-black">
            <div>
                <span class="text-[9px] font-black uppercase text-slate-600 block">BILLING BREAKDOWN:</span>
                <p class="text-[10px] font-bold text-slate-800">
                    ৳<?= number_format($subtotal, 0) ?> + Del: ৳<?= number_format($deliveryCost, 0) ?>
                    <?php if ($discount > 0): ?> - Disc: ৳<?= number_format($discount, 0) ?><?php endif; ?>
                </p>
            </div>
            <div class="text-right p-1.5 rounded-lg bg-black text-white shrink-0 border border-black">
                <span class="text-[8px] font-black uppercase text-amber-300 block tracking-wider"><?= $isCod ? 'CASH TO COLLECT (COD)' : 'PAID IN FULL' ?></span>
                <span class="text-base font-black font-mono tracking-tight text-white leading-none">৳<?= number_format($grandTotal, 0) ?></span>
            </div>
        </div>

        <!-- 6. Delivery Instruction for Rider -->
        <div class="p-2 bg-white text-[8px] font-bold text-black leading-tight space-y-0.5">
            <p class="text-rose-900 font-black uppercase">⚠️ কুরিয়ার রাইডার ডেলিভারির পূর্বে কল করুন ও পার্সেল চেক করার সুযোগ দিন।</p>
            <div class="border-t border-slate-300 pt-0.5 flex justify-between text-slate-600 text-[8px]">
                <span>Hub: <strong><?= htmlspecialchars($storeName) ?></strong></span>
                <span>Date: <?= date('d/m/Y') ?></span>
            </div>
        </div>

    </div>

    <!-- Render Vector Barcode with JsBarcode -->
    <script>
        function renderBarcode() {
            JsBarcode("#orderBarcode", "<?= addslashes($orderNo) ?>", {
                format: "CODE128",
                lineColor: "#000000",
                width: 2,
                height: 38,
                displayValue: false,
                margin: 0
            });
        }

        function setLabelMode(mode) {
            const container = document.getElementById('stickerContainer');
            const btn80 = document.getElementById('btnMode80');
            const btn4x6 = document.getElementById('btnMode4x6');
            const btnA4 = document.getElementById('btnModeA4');

            btn80.className = 'py-2 px-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-center transition';
            btn4x6.className = 'py-2 px-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-center transition';
            btnA4.className = 'py-2 px-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-center transition';

            if (mode === '4x6') {
                container.className = 'label-container preview-4x6 print-wrapper-4x6 mx-auto bg-white rounded-2xl border-2 border-black shadow-2xl overflow-hidden text-black text-xs font-sans transition-all duration-200';
                btn4x6.className = 'py-2 px-2 bg-amber-500 text-slate-950 font-black rounded-xl shadow-sm text-center transition';
            } else if (mode === 'a4') {
                container.className = 'label-container preview-a4 print-wrapper-a4 mx-auto bg-white rounded-2xl border-2 border-black shadow-2xl overflow-hidden text-black text-xs font-sans transition-all duration-200';
                btnA4.className = 'py-2 px-2 bg-amber-500 text-slate-950 font-black rounded-xl shadow-sm text-center transition';
            } else {
                container.className = 'label-container preview-80mm print-wrapper-80mm mx-auto bg-white rounded-2xl border-2 border-black shadow-2xl overflow-hidden text-black text-xs font-sans transition-all duration-200';
                btn80.className = 'py-2 px-2 bg-amber-500 text-slate-950 font-black rounded-xl shadow-sm text-center transition';
            }
            renderBarcode();
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderBarcode();
        });
    </script>
</body>
</html>
