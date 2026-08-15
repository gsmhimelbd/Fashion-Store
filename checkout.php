<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/database.php';

$cart = getCartItems();
if (empty($cart)) {
    header('Location: shop.php');
    exit;
}

$s = getAllSettings();
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += ($item['price'] * $item['quantity']);
}

$error = '';
$custDistrict = 'Dhaka';
$custName = '';
$custPhone = '';
$custEmail = '';
$custUpazila = '';
$custPostOffice = '';
$custAddress = '';

try {
    $db = getDB();
    $districts = $db->query("SELECT * FROM districts WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
    
    // Auto-fill logged-in customer info
    if (!empty($_SESSION['user_id']) || !empty($_SESSION['customer_id'])) {
        $uid = $_SESSION['user_id'] ?? $_SESSION['customer_id'];
        $uStmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $uStmt->execute([$uid]);
        $u = $uStmt->fetch();
        if ($u) {
            $custName = $u['name'] ?? '';
            $custPhone = $u['phone'] ?? '';
            $custEmail = $u['email'] ?? '';
            $custDistrict = $u['district'] ?? 'Dhaka';
            $custUpazila = $u['upazila'] ?? '';
            $custPostOffice = $u['post_office'] ?? '';
            $custAddress = $u['address'] ?? '';
        }
    }
} catch (Exception $e) {
    $districts = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['customer_phone'] ?? '');
    $email = trim($_POST['customer_email'] ?? '');
    $districtName = trim($_POST['district'] ?? 'Dhaka');
    $upazila = trim($_POST['upazila'] ?? '');
    if (empty($upazila)) $upazila = $districtName;
    $postOffice = trim($_POST['post_office'] ?? '');
    $address = trim($_POST['delivery_address'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? 'cod');
    $payNumber = trim($_POST['payment_number'] ?? '');
    $trxId = trim($_POST['transaction_id'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($name) || empty($phone) || empty($address)) {
        $error = 'Please fill out all required fields (Name, Phone, and Delivery Address).';
    } else {
        try {
            $deliveryCost = 120.0;
            try {
                $distStmt = $db->prepare("SELECT * FROM districts WHERE name = ? LIMIT 1");
                $distStmt->execute([$districtName]);
                $distRow = $distStmt->fetch();
                if ($distRow) {
                    $deliveryCost = (float)$distRow['delivery_fee'];
                }
            } catch (Exception $ex) {}

            if ($subtotal >= 2000) {
                $deliveryCost = 0.0;
            }

            $total = $subtotal + $deliveryCost;
            $orderNumber = 'OBM-' . strtoupper(bin2hex(random_bytes(4)));

            $orderStmt = $db->prepare("INSERT INTO orders (
                order_number, customer_name, customer_email, customer_phone, phone, whatsapp, 
                delivery_address, address, district_name, district, upazila, post_office, country,
                subtotal, delivery_cost, delivery_charge, total_amount, grand_total, 
                payment_method, payment_number, transaction_id, status, notes, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, 'Bangladesh',
                ?, ?, ?, ?, ?, 
                ?, ?, ?, 'pending', ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            )");
            
            $orderStmt->execute([
                $orderNumber, $name, $email, $phone, $phone, $phone,
                $address, $address, $districtName, $districtName, $upazila, $postOffice,
                $subtotal, $deliveryCost, $deliveryCost, $total, $total,
                $paymentMethod, $payNumber, $trxId, $notes
            ]);

            $orderId = $db->lastInsertId();

            // Auto-heal order_items schema if columns were missing
            try {
                $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
                if ($driver === 'mysql') {
                    @$db->exec("ALTER TABLE `order_items` ADD COLUMN `product_image` varchar(255) DEFAULT NULL");
                    @$db->exec("ALTER TABLE `order_items` ADD COLUMN `total_price` decimal(10,2) DEFAULT NULL");
                    @$db->exec("ALTER TABLE `order_items` ADD COLUMN `is_wholesale` tinyint(1) DEFAULT 0");
                }
            } catch (Exception $ex) {}

            foreach ($cart as $item) {
                $itemTotal = (float)($item['price'] * $item['quantity']);
                $isWholesale = !empty($item['is_wholesale']) ? 1 : 0;
                $img = $item['image'] ?? '';

                try {
                    $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity, total_price, is_wholesale, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
                    $itemStmt->execute([$orderId, $item['id'], $item['name'], $img, $item['price'], $item['quantity'], $itemTotal, $isWholesale]);
                } catch (Exception $e1) {
                    try {
                        $itemStmt2 = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
                        $itemStmt2->execute([$orderId, $item['id'], $item['name'], $item['price'], $item['quantity']]);
                    } catch (Exception $e2) {}
                }
            }

            try {
                $db->prepare("UPDATE cart_abandonments SET recovered = 1 WHERE session_id = ? OR customer_phone = ?")->execute([session_id(), $phone]);
            } catch (Exception $ex) {}

            // Send Telegram Push Notification & Email confirmation
            require_once __DIR__ . '/includes/telegram_bot.php';
            @sendTelegramOrderAlert($orderId);

            require_once __DIR__ . '/includes/smtp_mailer.php';
            @sendOrderEmailNotifications($orderId);

            // Clear Cart
            unset($_SESSION['cart']);

            header('Location: order-success.php?id=' . $orderId . '&order_number=' . urlencode($orderNumber));
            exit;
        } catch (Exception $e) {
            $error = 'Failed to create order: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Express Checkout - OnlineBdMart';
require_once 'includes/header.php';

$bkashNum = $s['payment_bkash_number'] ?? '01775153740';
$nagadNum = $s['payment_nagad_number'] ?? '01775153740';
$rocketNum = $s['payment_rocket_number'] ?? '01775153740';
$bankName = $s['payment_bank_name'] ?? 'Islami Bank Bangladesh Ltd';
$bankAcc = $s['payment_bank_acc_no'] ?? '2050123456789012';
$bankTitle = $s['payment_bank_acc_name'] ?? 'OnlineBdMart Enterprise';
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold font-serif text-slate-900">Complete Your Order</h1>
        <p class="text-xs text-slate-500 mt-1">Cash on delivery available across all 64 districts with digital payment options.</p>
    </div>

    <?php if ($error): ?>
    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-2">
        <i class="fas fa-circle-exclamation text-base"></i> <span><?= htmlspecialchars($error) ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" action="checkout.php" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left: Customer Details & Shipping Form (7 cols) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 space-y-6 shadow-sm">
                <div class="border-b pb-3">
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs">1</span>
                        Customer & Delivery Information
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Full Name (গ্রাহকের নাম) *</label>
                        <input type="text" name="customer_name" value="<?= htmlspecialchars($custName) ?>" required placeholder="e.g. Arif Hossain" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Mobile Phone (মোবাইল নাম্বার) *</label>
                        <input type="tel" name="customer_phone" value="<?= htmlspecialchars($custPhone) ?>" required placeholder="017xxxxxxxx" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Address (ঐচ্ছিক)</label>
                        <input type="email" name="customer_email" value="<?= htmlspecialchars($custEmail) ?>" placeholder="yourname@gmail.com" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Delivery District (জেলা) *</label>
                        <select name="district" id="districtSelect" onchange="updateDeliveryFee()" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-indigo-500 outline-none bg-slate-50">
                            <?php foreach ($districts as $d): ?>
                            <option value="<?= htmlspecialchars($d['name']) ?>" data-fee="<?= $d['delivery_fee'] ?>" data-time="<?= htmlspecialchars($d['estimated_days'] ?? '2-4 days') ?>" <?= ($d['name'] === $custDistrict) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['name']) ?> (<?= htmlspecialchars($d['division_name']) ?>) - ৳<?= number_format($d['delivery_fee'], 0) ?> [<?= htmlspecialchars($d['estimated_days'] ?? '2-4 days') ?>]
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Upazila / Thana (উপজেলা / থানা) *</label>
                        <input type="text" name="upazila" value="<?= htmlspecialchars($custUpazila) ?>" placeholder="e.g. Tangail Sadar / Mirpur" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Post Office / Zip Code (ডাকঘর)</label>
                        <input type="text" name="post_office" value="<?= htmlspecialchars($custPostOffice) ?>" placeholder="e.g. 1900" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Full Street Address (বাড়ি / গ্রাম / রোড নং) *</label>
                    <textarea name="delivery_address" rows="2" required placeholder="House/Flat No, Road Name, Area/Thana, Landmark..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none"><?= htmlspecialchars($custAddress) ?></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Delivery Notes (ঐচ্ছিক)</label>
                    <input type="text" name="notes" placeholder="e.g. Call before delivery, deliver after 2 PM" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 space-y-4 shadow-sm">
                <h2 class="text-base font-extrabold text-slate-900 border-b pb-3 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs">2</span>
                    Select Payment Method
                </h2>

                <div class="space-y-3 text-xs">
                    <!-- COD -->
                    <label class="flex items-center gap-3 p-4 rounded-2xl border-2 border-indigo-600 bg-indigo-50/40 cursor-pointer">
                        <input type="radio" name="payment_method" value="cod" checked onchange="togglePaymentInputs('cod')" class="text-indigo-600 focus:ring-indigo-500">
                        <div class="flex-1">
                            <span class="font-extrabold text-slate-900 block">Cash on Delivery (COD)</span>
                            <span class="text-slate-500 text-[11px]">Pay cash to courier upon doorstep delivery.</span>
                        </div>
                        <i class="fas fa-hand-holding-dollar text-2xl text-emerald-600"></i>
                    </label>

                    <!-- bKash -->
                    <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 hover:border-pink-500 cursor-pointer">
                        <input type="radio" name="payment_method" value="bkash" onchange="togglePaymentInputs('bkash')" class="text-pink-600 focus:ring-pink-500">
                        <div class="flex-1">
                            <span class="font-extrabold text-pink-600 block">bKash Online / Personal</span>
                            <span class="text-slate-500 text-[11px]">Send money to <strong><?= htmlspecialchars($bkashNum) ?></strong></span>
                        </div>
                        <span class="font-black text-pink-600 text-base">bKash</span>
                    </label>

                    <!-- Nagad -->
                    <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 hover:border-orange-500 cursor-pointer">
                        <input type="radio" name="payment_method" value="nagad" onchange="togglePaymentInputs('nagad')" class="text-orange-600 focus:ring-orange-500">
                        <div class="flex-1">
                            <span class="font-extrabold text-orange-600 block">Nagad Personal</span>
                            <span class="text-slate-500 text-[11px]">Send money to <strong><?= htmlspecialchars($nagadNum) ?></strong></span>
                        </div>
                        <span class="font-black text-orange-600 text-base">Nagad</span>
                    </label>

                    <!-- Rocket -->
                    <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 hover:border-purple-500 cursor-pointer">
                        <input type="radio" name="payment_method" value="rocket" onchange="togglePaymentInputs('rocket')" class="text-purple-600 focus:ring-purple-500">
                        <div class="flex-1">
                            <span class="font-extrabold text-purple-600 block">Rocket (DBBL)</span>
                            <span class="text-slate-500 text-[11px]">Send money to <strong><?= htmlspecialchars($rocketNum) ?></strong></span>
                        </div>
                        <span class="font-black text-purple-600 text-base">Rocket</span>
                    </label>

                    <!-- Bank Wire -->
                    <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 hover:border-cyan-500 cursor-pointer">
                        <input type="radio" name="payment_method" value="bank" onchange="togglePaymentInputs('bank')" class="text-cyan-600 focus:ring-cyan-500">
                        <div class="flex-1">
                            <span class="font-extrabold text-cyan-700 block">Bank Deposit</span>
                            <span class="text-slate-500 text-[11px]"><?= htmlspecialchars($bankName) ?> (Acc: <?= htmlspecialchars($bankAcc) ?>)</span>
                        </div>
                        <i class="fas fa-building-columns text-xl text-cyan-600"></i>
                    </label>

                    <!-- TrxID Container -->
                    <div id="trxIdContainer" class="hidden p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Sender Mobile Number (যে নাম্বার থেকে টাকা পাঠিয়েছেন) *</label>
                            <input type="tel" name="payment_number" placeholder="017xxxxxxxx" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-mono font-bold outline-none bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Transaction ID (TrxID) / Deposit Ref *</label>
                            <input type="text" name="transaction_id" placeholder="e.g. 9J8A7D6F5E" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-mono font-bold outline-none uppercase bg-white">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Order Summary & Place Order Action Button (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-6 shadow-sm sticky top-24">
                <div class="border-b pb-3 flex items-center justify-between">
                    <h3 class="text-base font-extrabold text-slate-900">Your Items (<?= count($cart) ?>)</h3>
                    <a href="cart.php" class="text-xs text-indigo-600 font-bold hover:underline">Edit Bag</a>
                </div>
                
                <div class="space-y-3 max-h-64 overflow-y-auto pr-1 divide-y divide-slate-100">
                    <?php foreach ($cart as $it): ?>
                    <div class="pt-2 flex items-center justify-between text-xs gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <img src="/<?= ltrim($it['image'], '/') ?>" class="w-12 h-12 object-cover rounded-xl bg-slate-50 border shrink-0">
                            <div class="min-w-0">
                                <p class="font-bold text-slate-800 truncate"><?= htmlspecialchars($it['name']) ?></p>
                                <span class="text-slate-500 text-[11px]"><?= $it['quantity'] ?> × ৳<?= number_format($it['price'], 2) ?></span>
                            </div>
                        </div>
                        <span class="font-black text-slate-900 shrink-0">৳<?= number_format($it['price'] * $it['quantity'], 2) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="pt-4 border-t border-slate-100 space-y-2.5 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Items Subtotal</span>
                        <span class="font-bold text-slate-900">৳<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Delivery Charge</span>
                        <span id="checkoutDeliveryFee" class="font-bold text-slate-900">৳80.00</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Estimated Arrival</span>
                        <span id="checkoutEstDays" class="font-bold text-emerald-600">1-2 days</span>
                    </div>
                    <div class="pt-3 border-t flex justify-between text-sm font-black text-slate-900">
                        <span>Total Payable</span>
                        <span id="checkoutTotalAmount" class="text-indigo-600 text-base">৳<?= number_format($subtotal + 80, 2) ?></span>
                    </div>
                </div>

                <!-- Big Confirm & Place Order Button -->
                <button type="submit" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-sm rounded-2xl shadow-xl shadow-indigo-600/30 transition flex items-center justify-center gap-2">
                    <i class="fas fa-lock"></i> <span>Confirm & Place Order</span>
                </button>

                <p class="text-[10px] text-center text-slate-400">100% Secure Checkout with Free Nationwide Replacement Guarantee.</p>
            </div>
        </div>

    </form>
</div>

<script>
    const subtotal = <?= (float)$subtotal ?>;

    function updateDeliveryFee() {
        const select = document.getElementById('districtSelect');
        if (!select || select.selectedIndex < 0) return;
        const selected = select.options[select.selectedIndex];
        let fee = parseFloat(selected.getAttribute('data-fee')) || 80;
        const estTime = selected.getAttribute('data-time') || '2-4 days';

        if (subtotal >= 2000) {
            fee = 0;
            document.getElementById('checkoutDeliveryFee').innerHTML = '<span class="text-emerald-600 font-extrabold">FREE</span>';
        } else {
            document.getElementById('checkoutDeliveryFee').textContent = '৳' + fee.toFixed(2);
        }

        document.getElementById('checkoutEstDays').textContent = estTime;
        document.getElementById('checkoutTotalAmount').textContent = '৳' + (subtotal + fee).toFixed(2);
    }

    function togglePaymentInputs(method) {
        const c = document.getElementById('trxIdContainer');
        if (!c) return;
        if (['bkash', 'nagad', 'rocket', 'bank'].includes(method)) {
            c.classList.remove('hidden');
        } else {
            c.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', updateDeliveryFee);
</script>

<?php require_once 'includes/footer.php'; ?>
