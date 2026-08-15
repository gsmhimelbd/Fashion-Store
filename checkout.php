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
    $settings = getAllSettings();

    // Auto-fill logged-in customer info
    $custName = '';
    $custPhone = '';
    $custEmail = '';
    $custDistrict = 'Dhaka';
    $custUpazila = '';
    $custPostOffice = '';
    $custAddress = '';
    $isLoggedIn = !empty($_SESSION['user_id']) || !empty($_SESSION['user_logged_in']);

    if ($isLoggedIn) {
        $uId = $_SESSION['user_id'] ?? 0;
        $uEmail = $_SESSION['user_email'] ?? '';
        $uStmt = $db->prepare("SELECT * FROM users WHERE id = ? OR email = ? LIMIT 1");
        $uStmt->execute([$uId, $uEmail]);
        $loggedUser = $uStmt->fetch();
        if ($loggedUser) {
            $custName = $loggedUser['name'] ?? '';
            $custPhone = $loggedUser['phone'] ?? '';
            $custEmail = $loggedUser['email'] ?? '';
            $custDistrict = !empty($loggedUser['district']) ? $loggedUser['district'] : 'Dhaka';
            $custUpazila = $loggedUser['upazila'] ?? '';
            $custPostOffice = $loggedUser['post_office'] ?? '';
            $custAddress = $loggedUser['address'] ?? '';
        }
    }

    // Record visitor behavior in customer_visits
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $page = $_SERVER['REQUEST_URI'] ?? '/checkout.php';
    $ref = $_SERVER['HTTP_REFERER'] ?? 'Direct';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Mobile';
    $dev = (str_contains(strtolower($ua), 'mobile') || str_contains(strtolower($ua), 'android') || str_contains(strtolower($ua), 'iphone')) ? 'Mobile' : 'Desktop';
    $db->prepare("INSERT INTO customer_visits (ip_address, session_id, district, referrer, page_url, user_agent, device_type, visited_at) VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)")->execute([$ip, session_id(), $custDistrict, $ref, $page, $ua, $dev]);
} catch (Exception $e) {
    $districts = [];
    $settings = [];
    $custName = '';
    $custPhone = '';
    $custEmail = '';
    $custDistrict = 'Dhaka';
    $custUpazila = '';
    $custPostOffice = '';
    $custAddress = '';
    $isLoggedIn = false;
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
    $districtName = trim($_POST['district'] ?? 'Dhaka');
    $upazila = trim($_POST['upazila'] ?? '');
    if (empty($upazila)) $upazila = $districtName;
    $postOffice = trim($_POST['post_office'] ?? '');
    $address = trim($_POST['delivery_address'] ?? '');
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
            $distStmt = $db->prepare("SELECT * FROM districts WHERE name = ? LIMIT 1");
            $distStmt->execute([$districtName]);
            $distRow = $distStmt->fetch();
            if ($distRow) {
                $deliveryCost = (float)$distRow['delivery_fee'];
            }

            if ($subtotal >= 2000) {
                $deliveryCost = 0.0;
            }

            $total = $subtotal + $deliveryCost;
            $orderNumber = 'OBM-' . strtoupper(bin2hex(random_bytes(4)));

            $orderStmt = $db->prepare("INSERT INTO orders (
                order_number, customer_name, customer_email, customer_phone, phone, whatsapp, 
                delivery_address, address, district_name, district, upazila, post_office, country,
                subtotal, delivery_cost, delivery_charge, total_amount, grand_total, 
                payment_method, transaction_id, status, notes, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, 'Bangladesh',
                ?, ?, ?, ?, ?, 
                ?, ?, 'pending', ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            )");
            
            $orderStmt->execute([
                $orderNumber,
                $name,
                $email,
                $phone,
                $phone,
                $phone,
                $address,
                $address,
                $districtName,
                $districtName,
                $upazila,
                $postOffice,
                $subtotal,
                $deliveryCost,
                $deliveryCost,
                $total,
                $total,
                $paymentMethod,
                $trxId,
                $notes
            ]);

            $orderId = $db->lastInsertId();

            // Insert items with picture
            $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity, total_price, is_wholesale, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
            foreach ($cart as $item) {
                $itemStmt->execute([
                    $orderId,
                    $item['id'],
                    $item['name'],
                    $item['image'],
                    $item['price'],
                    $item['quantity'],
                    $item['price'] * $item['quantity'],
                    !empty($item['is_wholesale']) ? 1 : 0
                ]);
            }

            // Mark any cart abandonment session as recovered
            $db->prepare("UPDATE cart_abandonments SET recovered = 1 WHERE session_id = ? OR customer_phone = ?")->execute([session_id(), $phone]);

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

$bkashNum = $settings['payment_bkash_number'] ?? '01775153740';
$nagadNum = $settings['payment_nagad_number'] ?? '01775153740';
$rocketNum = $settings['payment_rocket_number'] ?? '01775153740';
$bankName = $settings['payment_bank_name'] ?? 'Islami Bank Bangladesh Ltd';
$bankAcc = $settings['payment_bank_acc_no'] ?? '2050123456789012';
$bankTitle = $settings['payment_bank_acc_name'] ?? 'OnlineBdMart Enterprise';
$bankBranch = $settings['payment_bank_branch'] ?? 'Tangail Branch';
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold font-serif text-slate-900">Complete Your Order</h1>
        <p class="text-xs text-slate-500 mt-1">Cash on delivery available across all 64 districts with digital & bank payment options.</p>
    </div>

    <?php if ($error): ?>
    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold">
        <i class="fas fa-circle-exclamation mr-1"></i> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="checkout.php" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Customer Info & Shipping Address -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 space-y-6 shadow-sm">
                <div class="border-b pb-3 flex items-center justify-between">
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs">1</span>
                        Shipping & Customer Details
                    </h2>
                </div>

                <!-- 2 Options for Logged in Customers vs Sign in Banner for Guests -->
                <?php if ($isLoggedIn): ?>
                <div class="p-4 bg-indigo-50/70 border border-indigo-100 rounded-2xl space-y-3 text-xs">
                    <span class="font-extrabold text-indigo-950 uppercase tracking-wider text-[10px] block">Choose Delivery Address:</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border bg-white cursor-pointer border-indigo-600 shadow-sm" id="optSavedLabel">
                            <input type="radio" name="address_choice" value="saved" checked onchange="toggleAddressChoice('saved')" class="text-indigo-600">
                            <div>
                                <span class="font-bold text-slate-900 block">Use Saved Profile Address</span>
                                <span class="text-[11px] text-slate-500"><?= htmlspecialchars($custDistrict) ?> • <?= htmlspecialchars($custPhone) ?></span>
                            </div>
                        </label>
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border bg-white cursor-pointer border-slate-200 hover:border-indigo-400" id="optNewLabel">
                            <input type="radio" name="address_choice" value="new" onchange="toggleAddressChoice('new')" class="text-indigo-600">
                            <div>
                                <span class="font-bold text-slate-900 block">Ship to a Different Address</span>
                                <span class="text-[11px] text-slate-500">For friend, office or gift</span>
                            </div>
                        </label>
                    </div>
                </div>
                <?php else: ?>
                <div class="p-3.5 bg-indigo-50/80 border border-indigo-100 rounded-2xl flex items-center justify-between text-xs text-indigo-900">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-user-circle text-indigo-600 text-base"></i>
                        <span>Have an account? <a href="login.php?redirect=checkout.php" class="font-extrabold text-indigo-600 underline">Sign In</a> to auto-fill your saved address.</span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Form Fields (Auto-filled when logged in) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Full Name (গ্রাহকের নাম) *</label>
                        <input type="text" name="customer_name" id="inCustomerName" value="<?= htmlspecialchars($custName) ?>" required placeholder="e.g. Arif Hossain" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Mobile Phone Number (সচল মোবাইল নাম্বার) *</label>
                        <input type="tel" name="customer_phone" id="inCustomerPhone" value="<?= htmlspecialchars($custPhone) ?>" required placeholder="017xxxxxxxx" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Address (ইমেইল - optional)</label>
                        <input type="email" name="customer_email" id="inCustomerEmail" value="<?= htmlspecialchars($custEmail) ?>" placeholder="arif@example.com" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Select Delivery District (জেলা) *</label>
                        <select name="district" id="districtSelect" onchange="updateDeliveryFee()" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-indigo-500 outline-none bg-slate-50">
                            <?php foreach ($districts as $d): ?>
                            <option value="<?= htmlspecialchars($d['name']) ?>" data-fee="<?= $d['delivery_fee'] ?>" data-time="<?= htmlspecialchars($d['estimated_days'] ?? '2-4 days') ?>" <?= ($d['name'] === $custDistrict) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['name']) ?> (<?= htmlspecialchars($d['division_name']) ?>) - ৳<?= number_format($d['delivery_fee'], 0) ?> [<?= htmlspecialchars($d['estimated_days'] ?? '2-4 days') ?>]
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Upazila, Post Office, Country -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Upazila / Thana (উপজেলা / থানা) *</label>
                        <input type="text" name="upazila" id="inCustomerUpazila" value="<?= htmlspecialchars($custUpazila) ?>" placeholder="e.g. Tangail Sadar / Mirpur" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Post Office / Zip Code (ডাকঘর)</label>
                        <input type="text" name="post_office" id="inCustomerPostOffice" value="<?= htmlspecialchars($custPostOffice) ?>" placeholder="e.g. Tangail 1900" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Country (দেশ)</label>
                        <input type="text" name="country" value="Bangladesh" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold bg-slate-50 outline-none" readonly>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Full Street Address / Village / Landmark (বাড়ি / গ্রাম / রোড নং) *</label>
                    <textarea name="delivery_address" id="inCustomerAddress" rows="2" required placeholder="House/Flat No, Road Name, Area/Thana, Landmark..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none"><?= htmlspecialchars($custAddress) ?></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Delivery Instructions / Notes (Optional)</label>
                    <input type="text" name="notes" placeholder="e.g. Call before delivery, deliver after 2 PM" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>

            <!-- Payment Method Selection -->
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
                            <span class="text-slate-500 text-[11px]">Pay cash to the courier rider upon delivery at your doorstep.</span>
                        </div>
                        <i class="fas fa-hand-holding-dollar text-2xl text-emerald-600"></i>
                    </label>

                    <!-- bKash -->
                    <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 hover:border-pink-500 cursor-pointer">
                        <input type="radio" name="payment_method" value="bkash" onchange="togglePaymentInputs('bkash')" class="text-pink-600 focus:ring-pink-500">
                        <div class="flex-1">
                            <span class="font-extrabold text-pink-600 block">bKash (Merchant / Personal)</span>
                            <span class="text-slate-500 text-[11px]">Send money to <strong><?= htmlspecialchars($bkashNum) ?></strong> and enter TrxID below.</span>
                        </div>
                        <span class="font-black text-pink-600 text-base">bKash</span>
                    </label>

                    <!-- Nagad -->
                    <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 hover:border-orange-500 cursor-pointer">
                        <input type="radio" name="payment_method" value="nagad" onchange="togglePaymentInputs('nagad')" class="text-orange-600 focus:ring-orange-500">
                        <div class="flex-1">
                            <span class="font-extrabold text-orange-600 block">Nagad Personal</span>
                            <span class="text-slate-500 text-[11px]">Send money to <strong><?= htmlspecialchars($nagadNum) ?></strong> and enter TrxID below.</span>
                        </div>
                        <span class="font-black text-orange-600 text-base">Nagad</span>
                    </label>

                    <!-- Rocket -->
                    <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 hover:border-purple-500 cursor-pointer">
                        <input type="radio" name="payment_method" value="rocket" onchange="togglePaymentInputs('rocket')" class="text-purple-600 focus:ring-purple-500">
                        <div class="flex-1">
                            <span class="font-extrabold text-purple-600 block">Rocket (DBBL)</span>
                            <span class="text-slate-500 text-[11px]">Send money to <strong><?= htmlspecialchars($rocketNum) ?></strong> and enter TrxID below.</span>
                        </div>
                        <span class="font-black text-purple-600 text-base">Rocket</span>
                    </label>

                    <!-- Bank Wire -->
                    <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 hover:border-cyan-500 cursor-pointer">
                        <input type="radio" name="payment_method" value="bank" onchange="togglePaymentInputs('bank')" class="text-cyan-600 focus:ring-cyan-500">
                        <div class="flex-1">
                            <span class="font-extrabold text-cyan-700 block">Manual Bank Deposit</span>
                            <span class="text-slate-500 text-[11px]"><?= htmlspecialchars($bankName) ?> (Acc: <?= htmlspecialchars($bankAcc) ?>).</span>
                        </div>
                        <i class="fas fa-building-columns text-xl text-cyan-600"></i>
                    </label>

                    <!-- TrxID / Deposit reference input box -->
                    <div id="trxIdContainer" class="hidden p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                        <label class="block text-xs font-bold text-slate-700" id="trxLabel">Enter Transaction ID (TrxID) / Deposit Slip Reference *</label>
                        <input type="text" name="transaction_id" id="trxInput" placeholder="e.g. 9J8A7D6F5E" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-mono font-bold outline-none uppercase bg-white">
                        <p class="text-[10px] text-slate-500" id="trxHelp">We will verify the transaction reference before dispatching your parcel.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary & Place Button -->
        <div class="space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-6 shadow-sm">
                <h3 class="text-base font-extrabold text-slate-900 border-b pb-3">Your Items (<?= count($cart) ?>)</h3>
                
                <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
                    <?php foreach ($cart as $it): ?>
                    <div class="flex items-center justify-between text-xs gap-3">
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

                <button type="submit" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs uppercase tracking-wider rounded-2xl shadow-xl transition flex items-center justify-center gap-2">
                    <i class="fas fa-lock"></i> Confirm & Place Order
                </button>

                <p class="text-[10px] text-center text-slate-400">By placing this order, you agree to OnlineBdMart's terms & return policy.</p>
            </div>
        </div>
    </form>
</div>

<script>
    const subtotal = <?= $subtotal ?>;
    const savedData = {
        name: "<?= addslashes($custName) ?>",
        phone: "<?= addslashes($custPhone) ?>",
        email: "<?= addslashes($custEmail) ?>",
        district: "<?= addslashes($custDistrict) ?>",
        upazila: "<?= addslashes($custUpazila) ?>",
        post_office: "<?= addslashes($custPostOffice) ?>",
        address: "<?= addslashes($custAddress) ?>"
    };

    function toggleAddressChoice(choice) {
        const inName = document.getElementById('inCustomerName');
        const inPhone = document.getElementById('inCustomerPhone');
        const inEmail = document.getElementById('inCustomerEmail');
        const inUpazila = document.getElementById('inCustomerUpazila');
        const inPost = document.getElementById('inCustomerPostOffice');
        const inAddr = document.getElementById('inCustomerAddress');
        const selDist = document.getElementById('districtSelect');

        if (choice === 'saved') {
            inName.value = savedData.name;
            inPhone.value = savedData.phone;
            inEmail.value = savedData.email;
            inUpazila.value = savedData.upazila;
            inPost.value = savedData.post_office;
            inAddr.value = savedData.address;
            selDist.value = savedData.district || 'Dhaka';
        } else {
            inName.value = '';
            inPhone.value = '';
            inUpazila.value = '';
            inPost.value = '';
            inAddr.value = '';
        }
        updateDeliveryFee();
    }

    function updateDeliveryFee() {
        const sel = document.getElementById('districtSelect');
        if (!sel || sel.selectedIndex < 0) return;
        const opt = sel.options[sel.selectedIndex];
        let fee = parseFloat(opt.getAttribute('data-fee') || 80);
        const days = opt.getAttribute('data-time') || '1-2 days';

        if (subtotal >= 2000) {
            fee = 0;
            document.getElementById('checkoutDeliveryFee').textContent = 'FREE';
        } else {
            document.getElementById('checkoutDeliveryFee').textContent = '৳' + fee.toFixed(2);
        }

        document.getElementById('checkoutEstDays').textContent = days;
        document.getElementById('checkoutTotalAmount').textContent = '৳' + (subtotal + fee).toFixed(2);
    }

    function togglePaymentInputs(method) {
        const box = document.getElementById('trxIdContainer');
        const label = document.getElementById('trxLabel');
        const help = document.getElementById('trxHelp');

        if (method === 'cod') {
            box.classList.add('hidden');
        } else if (method === 'bank') {
            box.classList.remove('hidden');
            label.textContent = 'Enter Bank Deposit Slip Number / Ref No *';
            help.textContent = 'Bank: <?= addslashes($bankName) ?> | Acc No: <?= addslashes($bankAcc) ?> (<?= addslashes($bankTitle) ?>)';
        } else {
            box.classList.remove('hidden');
            label.textContent = 'Enter ' + method.toUpperCase() + ' Transaction ID (TrxID) *';
            help.textContent = 'Enter the 10-character transaction reference code from your SMS.';
        }
    }

    document.addEventListener('DOMContentLoaded', updateDeliveryFee);
</script>

<?php require_once 'includes/footer.php'; ?>
