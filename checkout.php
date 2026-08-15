<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header('Location: shop.php');
    exit;
}

try {
    $db = getDB();
    $districts = $db->query("SELECT * FROM districts ORDER BY division_name ASC, name ASC")->fetchAll();
} catch (Exception $e) {
    $districts = [];
}

$subtotal = 0.0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['customer_phone'] ?? '');
    $email = trim($_POST['customer_email'] ?? '');
    $address = trim($_POST['delivery_address'] ?? '');
    $districtName = trim($_POST['district'] ?? 'Dhaka');
    $paymentMethod = trim($_POST['payment_method'] ?? 'cod');
    $trxId = trim($_POST['transaction_id'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($name) || empty($phone) || empty($address)) {
        $error = 'Please fill out all required fields (Name, Phone, and Delivery Address).';
    } else {
        try {
            $db = getDB();
            
            // Get delivery cost from district
            $deliveryCost = 120.0;
            $districtId = null;
            $distStmt = $db->prepare("SELECT * FROM districts WHERE name = ? LIMIT 1");
            $distStmt->execute([$districtName]);
            $distRow = $distStmt->fetch();
            if ($distRow) {
                $deliveryCost = (float)$distRow['delivery_fee'];
                $districtId = $distRow['id'];
            }

            if ($subtotal >= 2000) {
                $deliveryCost = 0.0;
            }

            $total = $subtotal + $deliveryCost;
            $orderNumber = 'OBM-' . strtoupper(bin2hex(random_bytes(4)));

            $orderStmt = $db->prepare("INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, delivery_address, district_name, subtotal, delivery_cost, total_amount, payment_method, transaction_id, status, notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW(), NOW())");
            
            $orderStmt->execute([
                $orderNumber,
                $name,
                $email,
                $phone,
                $address,
                $districtName,
                $subtotal,
                $deliveryCost,
                $total,
                $paymentMethod,
                $trxId,
                $notes
            ]);

            $orderId = $db->lastInsertId();

            // Insert items
            $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total_price, is_wholesale, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            foreach ($cart as $item) {
                $itemStmt->execute([
                    $orderId,
                    $item['id'],
                    $item['name'],
                    $item['price'],
                    $item['quantity'],
                    $item['price'] * $item['quantity'],
                    !empty($item['is_wholesale']) ? 1 : 0
                ]);
            }

            // Clear Cart
            unset($_SESSION['cart']);

            header('Location: order-success.php?id=' . $orderId . '&order_number=' . urlencode($orderNumber));
            exit;
        } catch (Exception $e) {
            $error = 'Failed to create order: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Checkout - OnlineBdMart';
require_once 'includes/header.php';
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold font-serif text-slate-900">Complete Your Order</h1>
        <p class="text-xs text-slate-500 mt-1">Cash on delivery available across all 64 districts of Bangladesh.</p>
    </div>

    <?php if ($error): ?>
    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold">
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="checkout.php" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Customer Info & Shipping Address -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 space-y-6 shadow-sm">
                <h2 class="text-base font-extrabold text-slate-900 border-b pb-3">1. Shipping & Customer Details</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Full Name *</label>
                        <input type="text" name="customer_name" required placeholder="e.g. Arif Hossain" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Mobile Number (Active) *</label>
                        <input type="tel" name="customer_phone" required placeholder="017xxxxxxxx" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email (Optional - for tracking & invoice)</label>
                        <input type="email" name="customer_email" placeholder="arif@example.com" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Select Delivery District *</label>
                        <select name="district" id="districtSelect" onchange="updateDeliveryFee()" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-indigo-500 outline-none bg-slate-50">
                            <?php foreach ($districts as $d): ?>
                            <option value="<?= htmlspecialchars($d['name']) ?>" data-fee="<?= $d['delivery_fee'] ?>" data-time="<?= htmlspecialchars($d['estimated_days'] ?? '2-4 days') ?>" <?= $d['name'] === 'Dhaka' ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['name']) ?> (<?= htmlspecialchars($d['division_name']) ?>) - ৳<?= number_format($d['delivery_fee'], 0) ?> [<?= htmlspecialchars($d['estimated_days'] ?? '2-4 days') ?>]
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Complete Delivery Address *</label>
                    <textarea name="delivery_address" rows="2" required placeholder="House/Flat No, Road Name, Area/Thana, Landmark..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Order Notes / Delivery Instructions (Optional)</label>
                    <input type="text" name="notes" placeholder="e.g. Call before delivery, deliver after 2 PM" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>

            <!-- Payment Method Selection -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 space-y-4 shadow-sm">
                <h2 class="text-base font-extrabold text-slate-900 border-b pb-3">2. Payment Method</h2>

                <div class="space-y-3 text-xs">
                    <label class="flex items-center gap-3 p-4 rounded-2xl border-2 border-indigo-600 bg-indigo-50/40 cursor-pointer">
                        <input type="radio" name="payment_method" value="cod" checked onchange="toggleTxId(false)" class="text-indigo-600 focus:ring-indigo-500">
                        <div class="flex-1">
                            <span class="font-bold text-slate-900 block">Cash on Delivery (COD)</span>
                            <span class="text-slate-500 text-[11px]">Pay with cash upon receiving your package at your doorstep.</span>
                        </div>
                        <i class="fas fa-hand-holding-dollar text-xl text-emerald-600"></i>
                    </label>

                    <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 hover:border-pink-500 cursor-pointer">
                        <input type="radio" name="payment_method" value="bkash" onchange="toggleTxId(true)" class="text-pink-600 focus:ring-pink-500">
                        <div class="flex-1">
                            <span class="font-bold text-slate-900 block">bKash Personal / Merchant</span>
                            <span class="text-slate-500 text-[11px]">Send money to <strong>01712-345678</strong> and provide TrxID below.</span>
                        </div>
                        <span class="font-black text-pink-600">bKash</span>
                    </label>

                    <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 hover:border-orange-500 cursor-pointer">
                        <input type="radio" name="payment_method" value="nagad" onchange="toggleTxId(true)" class="text-orange-600 focus:ring-orange-500">
                        <div class="flex-1">
                            <span class="font-bold text-slate-900 block">Nagad Personal</span>
                            <span class="text-slate-500 text-[11px]">Send money to <strong>01712-345678</strong> and provide TrxID below.</span>
                        </div>
                        <span class="font-black text-orange-600">Nagad</span>
                    </label>

                    <div id="trxIdContainer" class="hidden p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                        <label class="block text-xs font-bold text-slate-700">Enter TrxID / Transaction Reference</label>
                        <input type="text" name="transaction_id" placeholder="e.g. 9J8A7D6F5E" class="w-full px-4 py-2 rounded-xl border border-slate-200 text-xs font-mono outline-none">
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary & Place Button -->
        <div class="space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-6 shadow-sm">
                <h3 class="text-base font-extrabold text-slate-900 border-b pb-3">Your Items (<?= count($cart) ?>)</h3>
                
                <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                    <?php foreach ($cart as $it): ?>
                    <div class="flex items-center justify-between text-xs gap-3">
                        <div class="flex items-center gap-2 min-w-0">
                            <img src="/<?= ltrim($it['image'], '/') ?>" class="w-10 h-10 object-cover rounded-lg bg-slate-50 border shrink-0">
                            <div class="min-w-0">
                                <p class="font-bold text-slate-800 truncate"><?= htmlspecialchars($it['name']) ?></p>
                                <span class="text-slate-500 text-[11px]">Qty: <?= $it['quantity'] ?> × ৳<?= number_format($it['price'], 2) ?></span>
                            </div>
                        </div>
                        <span class="font-extrabold text-slate-900 shrink-0">৳<?= number_format($it['price'] * $it['quantity'], 2) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="pt-4 border-t border-slate-100 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal</span>
                        <span class="font-bold text-slate-900">৳<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Delivery Fee</span>
                        <span id="checkoutDeliveryFee" class="font-bold text-slate-900">৳60.00</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Estimated Arrival</span>
                        <span id="checkoutEstDays" class="font-bold text-emerald-600">1-2 days</span>
                    </div>
                    <div class="pt-3 border-t flex justify-between text-sm font-extrabold text-slate-900">
                        <span>Total Payable</span>
                        <span id="checkoutTotalAmount" class="text-indigo-600 text-base">৳<?= number_format($subtotal + 60, 2) ?></span>
                    </div>
                </div>

                <button type="submit" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs uppercase tracking-wider rounded-2xl shadow-xl transition flex items-center justify-center gap-2">
                    <i class="fas fa-lock"></i> Place Order (Cash on Delivery)
                </button>

                <p class="text-[10px] text-center text-slate-400">By placing this order, you agree to OnlineBdMart's terms & conditions.</p>
            </div>
        </div>
    </form>
</div>

<script>
    const subtotal = <?= $subtotal ?>;

    function updateDeliveryFee() {
        const sel = document.getElementById('districtSelect');
        const opt = sel.options[sel.selectedIndex];
        let fee = parseFloat(opt.getAttribute('data-fee') || 120);
        const days = opt.getAttribute('data-time') || '2-4 days';

        if (subtotal >= 2000) {
            fee = 0;
            document.getElementById('checkoutDeliveryFee').textContent = 'FREE';
        } else {
            document.getElementById('checkoutDeliveryFee').textContent = '৳' + fee.toFixed(2);
        }

        document.getElementById('checkoutEstDays').textContent = days;
        document.getElementById('checkoutTotalAmount').textContent = '৳' + (subtotal + fee).toFixed(2);
    }

    function toggleTxId(show) {
        const el = document.getElementById('trxIdContainer');
        if (el) {
            if (show) el.classList.remove('hidden');
            else el.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', updateDeliveryFee);
</script>

<?php require_once 'includes/footer.php'; ?>
