<?php
$pageTitle = 'Track Order - OnlineBdMart';
require_once 'includes/header.php';

$orderNumber = trim($_GET['order_number'] ?? '');
$phone = trim($_GET['phone'] ?? '');

$order = null;
$items = [];
$searched = false;

if ($orderNumber || $phone) {
    $searched = true;
    try {
        $db = getDB();
        if ($orderNumber && $phone) {
            $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ? AND customer_phone = ? LIMIT 1");
            $stmt->execute([$orderNumber, $phone]);
        } elseif ($orderNumber) {
            $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ? LIMIT 1");
            $stmt->execute([$orderNumber]);
        } else {
            $stmt = $db->prepare("SELECT * FROM orders WHERE customer_phone = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$phone]);
        }
        $order = $stmt->fetch();

        if ($order) {
            $itemStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $itemStmt->execute([$order['id']]);
            $items = $itemStmt->fetchAll();
        }
    } catch (Exception $e) {}
}

$statusSteps = [
    'pending' => 1,
    'processing' => 2,
    'shipped' => 3,
    'delivered' => 4,
    'cancelled' => 0
];
$currentStep = $order ? ($statusSteps[$order['status']] ?? 1) : 1;
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8 text-center max-w-xl mx-auto space-y-2">
        <span class="text-xs font-bold uppercase text-emerald-600 tracking-wider">Real-time Logistics</span>
        <h1 class="text-3xl font-extrabold font-serif text-slate-900">Track Your Order</h1>
        <p class="text-xs text-slate-500">Enter your Order Tracking ID or Mobile Number to check real-time dispatch progress.</p>
    </div>

    <!-- Search Form -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm mb-10">
        <form method="GET" action="track-order.php" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Order Tracking ID</label>
                <input type="text" name="order_number" value="<?= htmlspecialchars($orderNumber) ?>" placeholder="e.g. OBM-XXXXXXXX" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-mono font-medium focus:ring-2 focus:ring-indigo-500 outline-none uppercase">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Customer Phone Number</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="017xxxxxxxx" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                    <i class="fas fa-search"></i> Track Status
                </button>
            </div>
        </form>
    </div>

    <!-- Results Area -->
    <?php if ($searched): ?>
        <?php if ($order): ?>
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-10 shadow-sm space-y-8">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b pb-6">
                    <div>
                        <span class="text-xs font-bold text-slate-400 block uppercase">Order ID</span>
                        <h2 class="text-xl font-black font-mono text-slate-900"><?= htmlspecialchars($order['order_number']) ?></h2>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-400 block uppercase">Current Status</span>
                        <span class="inline-block px-3 py-1 bg-indigo-50 text-indigo-700 font-extrabold rounded-full text-xs uppercase"><?= htmlspecialchars($order['status']) ?></span>
                    </div>
                </div>

                <!-- 4-step progress tracker -->
                <div class="relative py-4">
                    <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden mb-6">
                        <div class="h-full bg-emerald-500 transition-all duration-500" style="width: <?= ($currentStep / 4) * 100 ?>%;"></div>
                    </div>
                    <div class="grid grid-cols-4 text-center text-xs">
                        <div class="<?= $currentStep >= 1 ? 'text-emerald-600 font-bold' : 'text-slate-400' ?>">
                            <div class="w-8 h-8 mx-auto mb-1 rounded-full flex items-center justify-center <?= $currentStep >= 1 ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100' ?>"><i class="fas fa-clipboard-check"></i></div>
                            <span>Placed</span>
                        </div>
                        <div class="<?= $currentStep >= 2 ? 'text-emerald-600 font-bold' : 'text-slate-400' ?>">
                            <div class="w-8 h-8 mx-auto mb-1 rounded-full flex items-center justify-center <?= $currentStep >= 2 ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100' ?>"><i class="fas fa-box-open"></i></div>
                            <span>Processing</span>
                        </div>
                        <div class="<?= $currentStep >= 3 ? 'text-emerald-600 font-bold' : 'text-slate-400' ?>">
                            <div class="w-8 h-8 mx-auto mb-1 rounded-full flex items-center justify-center <?= $currentStep >= 3 ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100' ?>"><i class="fas fa-truck-fast"></i></div>
                            <span>Dispatched</span>
                        </div>
                        <div class="<?= $currentStep >= 4 ? 'text-emerald-600 font-bold' : 'text-slate-400' ?>">
                            <div class="w-8 h-8 mx-auto mb-1 rounded-full flex items-center justify-center <?= $currentStep >= 4 ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100' ?>"><i class="fas fa-circle-check"></i></div>
                            <span>Delivered</span>
                        </div>
                    </div>
                </div>

                <!-- Order Info Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50 p-6 rounded-2xl border text-xs">
                    <div>
                        <span class="text-slate-400 uppercase font-bold text-[10px] block mb-1">Customer Info</span>
                        <p class="font-bold text-slate-900"><?= htmlspecialchars($order['customer_name']) ?></p>
                        <p class="text-slate-600"><?= htmlspecialchars($order['customer_phone']) ?></p>
                        <p class="text-slate-600 mt-1"><?= htmlspecialchars($order['delivery_address']) ?></p>
                        <p class="text-slate-900 font-bold mt-1">District: <?= htmlspecialchars($order['district_name'] ?? 'Dhaka') ?></p>
                    </div>
                    <div>
                        <span class="text-slate-400 uppercase font-bold text-[10px] block mb-1">Payment & Amount</span>
                        <p class="text-slate-600">Method: <strong class="uppercase text-slate-900"><?= htmlspecialchars($order['payment_method']) ?></strong></p>
                        <p class="text-slate-600">Items: <strong><?= count($items) ?> pcs</strong></p>
                        <p class="text-slate-900 font-black text-sm mt-2">Total: ৳<?= number_format($order['total_amount'], 2) ?></p>
                    </div>
                </div>

                <div class="text-center pt-2">
                    <a href="invoice.php?id=<?= $order['id'] ?>" target="_blank" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow inline-flex items-center gap-2">
                        <i class="fas fa-file-invoice"></i> Download Invoice
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center text-slate-400 text-xs space-y-3">
                <i class="fas fa-circle-exclamation text-3xl text-rose-400"></i>
                <h3 class="font-bold text-slate-800 text-sm">No Order Found</h3>
                <p>Please double check the Order ID or phone number and try again.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
