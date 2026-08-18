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

$subtotal = 0.0;
foreach ($cart as $item) {
    $subtotal += (float)$item['price'] * (int)$item['quantity'];
}

// Handle AJAX Coupon Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['coupon_action'])) {
    header('Content-Type: application/json');
    $cAction = $_POST['coupon_action'];

    if ($cAction === 'apply') {
        $code = strtoupper(trim(preg_replace('/[^A-Za-z0-9_-]/', '', $_POST['coupon_code'] ?? '')));
        if (empty($code)) {
            echo json_encode(['success' => false, 'message' => 'অনুগ্রহ করে কুপন কোড লিখুন।']);
            exit;
        }

        try {
            $db = getDB();

            // Auto-heal coupons table if not exists or missing columns
            try { $db->exec("CREATE TABLE IF NOT EXISTS `coupons` (`id` int(11) NOT NULL AUTO_INCREMENT, `code` varchar(50) NOT NULL, `discount_type` varchar(20) NOT NULL DEFAULT 'fixed', `discount_value` decimal(10,2) NOT NULL DEFAULT 0.00, `min_spend` decimal(10,2) NOT NULL DEFAULT 0.00, `product_id` int(11) DEFAULT NULL, `show_in_header` tinyint(1) DEFAULT 1, `header_banner_text` varchar(255) DEFAULT NULL, `first_order_only` tinyint(1) DEFAULT 0, `expiry_date` date DEFAULT NULL, `used_count` int(11) DEFAULT 0, `is_active` tinyint(1) DEFAULT 1, `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `code` (`code`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch (Exception $e) {}

            $stmt = $db->prepare("SELECT * FROM coupons WHERE UPPER(TRIM(code)) = ? AND (is_active = 1 OR is_active IS NULL) AND (expiry_date IS NULL OR expiry_date = '' OR expiry_date = '0000-00-00' OR expiry_date >= CURDATE()) LIMIT 1");
            $stmt->execute([$code]);
            $coupon = $stmt->fetch();

            if (!$coupon) {
                echo json_encode(['success' => false, 'message' => 'দুঃখিত! কুপন কোডটি সঠিক নয় অথবা মেয়াদ শেষ হয়ে গেছে।']);
                exit;
            }

            // Check Minimum Spend
            if ($subtotal < (float)($coupon['min_spend'] ?? 0)) {
                echo json_encode(['success' => false, 'message' => "এই কুপনটি ব্যবহার করতে সর্বনিম্ন ৳" . number_format((float)$coupon['min_spend'], 0) . " টাকার অর্ডার করতে হবে।"]);
                exit;
            }

            // Check First Order Only Rule
            if (!empty($coupon['first_order_only'])) {
                $custPhone = trim($_POST['customer_phone'] ?? ($_SESSION['user_phone'] ?? ''));
                $custEmail = trim($_POST['customer_email'] ?? ($_SESSION['user_email'] ?? ''));
                
                if ($custPhone || $custEmail) {
                    $prevOrderCount = 0;
                    try {
                        $prevStmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE (customer_phone = ? OR phone = ?) OR (customer_email != '' AND customer_email = ?)");
                        $prevStmt->execute([$custPhone, $custPhone, $custEmail]);
                        $prevOrderCount = (int)$prevStmt->fetchColumn();
                    } catch (Exception $e) {
                        try {
                            $prevStmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE customer_phone = ? OR phone = ?");
                            $prevStmt->execute([$custPhone, $custPhone]);
                            $prevOrderCount = (int)$prevStmt->fetchColumn();
                        } catch (Exception $ex2) {}
                    }

                    if ($prevOrderCount > 0) {
                        echo json_encode(['success' => false, 'message' => 'দুঃখিত! এই কুপন কোডটি শুধুমাত্র নতুন গ্রাহকদের ১ম অর্ডারের জন্য প্রযোজ্য (First Order Only)।']);
                        exit;
                    }
                }
            }

            // Check Product Scope
            if (!empty($coupon['product_id'])) {
                $hasMatchingProduct = false;
                foreach ($cart as $it) {
                    if ((int)$it['id'] === (int)$coupon['product_id']) {
                        $hasMatchingProduct = true;
                        break;
                    }
                }
                if (!$hasMatchingProduct) {
                    echo json_encode(['success' => false, 'message' => 'এই কুপনটি আপনার কার্টের প্রোডাক্টের জন্য প্রযোজ্য নয়।']);
                    exit;
                }
            }

            // Calculate Discount Amount
            $discount = 0.0;
            $discType = $coupon['discount_type'] ?? 'fixed';
            $discVal = (float)($coupon['discount_value'] ?? 0);

            if ($discType === 'percent') {
                $discount = round(($subtotal * $discVal) / 100, 2);
            } else {
                $discount = min($subtotal, $discVal);
            }

            $_SESSION['applied_coupon'] = [
                'code' => $coupon['code'],
                'discount' => $discount,
                'type' => $discType,
                'value' => $discVal,
                'min_spend' => (float)($coupon['min_spend'] ?? 0)
            ];

            echo json_encode([
                'success' => true,
                'message' => "✓ কুপন \"{$coupon['code']}\" সফলভাবে যুক্ত হয়েছে! (-৳" . number_format($discount, 2) . ")",
                'code' => $coupon['code'],
                'discount' => $discount,
                'new_subtotal' => max(0, $subtotal - $discount)
            ]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'কুপন ভ্যালিডেট করতে সমস্যা হয়েছে: ' . $e->getMessage()]);
            exit;
        }
    } elseif ($cAction === 'remove') {
        unset($_SESSION['applied_coupon']);
        echo json_encode([
            'success' => true,
            'message' => 'কুপনটি রিমুভ করা হয়েছে।',
            'new_subtotal' => $subtotal
        ]);
        exit;
    }
}

try {
    $db = getDB();

    // Auto-heal orders & coupons schema
    try {
        @$db->exec("ALTER TABLE `orders` ADD COLUMN `coupon_code` varchar(50) DEFAULT NULL");
        @$db->exec("ALTER TABLE `orders` ADD COLUMN `discount_amount` decimal(10,2) DEFAULT 0.00");
    } catch (Exception $ex) {}

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
    $isLoggedIn = !empty($_SESSION['user_id']) || !empty($_SESSION['user_logged_in']) || !empty($_SESSION['customer_id']);

    if ($isLoggedIn) {
        $uId = $_SESSION['user_id'] ?? ($_SESSION['customer_id'] ?? 0);
        $uEmail = $_SESSION['user_email'] ?? '';
        $uPhone = $_SESSION['user_phone'] ?? '';

        $uStmt = $db->prepare("SELECT * FROM users WHERE id = ? OR (email != '' AND email = ?) OR (phone != '' AND phone = ?) LIMIT 1");
        $uStmt->execute([$uId, $uEmail, $uPhone]);
        $loggedUser = $uStmt->fetch();

        if (!$loggedUser && $uPhone) {
            $lastOrd = $db->prepare("SELECT customer_name as name, COALESCE(customer_phone, phone) as phone, customer_email as email, COALESCE(district_name, district) as district, upazila, post_office, COALESCE(delivery_address, address) as address FROM orders WHERE customer_phone = ? OR phone = ? ORDER BY id DESC LIMIT 1");
            $lastOrd->execute([$uPhone, $uPhone]);
            $loggedUser = $lastOrd->fetch();
        }

        if ($loggedUser) {
            $custName = $loggedUser['name'] ?? ($_SESSION['user_name'] ?? '');
            $custPhone = $loggedUser['phone'] ?? ($_SESSION['user_phone'] ?? '');
            $custEmail = $loggedUser['email'] ?? ($_SESSION['user_email'] ?? '');
            $custDistrict = !empty($loggedUser['district']) ? $loggedUser['district'] : 'Dhaka';
            $custUpazila = $loggedUser['upazila'] ?? '';
            $custPostOffice = $loggedUser['post_office'] ?? '';
            $custAddress = $loggedUser['address'] ?? '';
        } else {
            $custName = $_SESSION['user_name'] ?? '';
            $custEmail = $_SESSION['user_email'] ?? '';
            $custPhone = $_SESSION['user_phone'] ?? '';
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

// Calculate Applied Coupon Discount
$appliedCoupon = $_SESSION['applied_coupon'] ?? null;
$discountAmount = 0.0;
$couponCode = null;

if (!empty($appliedCoupon)) {
    $couponCode = $appliedCoupon['code'];
    $discountAmount = (float)($appliedCoupon['discount'] ?? 0.0);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['coupon_action'])) {
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

            $total = max(0, ($subtotal - $discountAmount)) + $deliveryCost;
            $orderNumber = 'OBM-' . strtoupper(bin2hex(random_bytes(4)));

            $orderStmt = $db->prepare("INSERT INTO orders (
                order_number, customer_name, customer_email, customer_phone, phone, whatsapp, 
                delivery_address, address, district_name, district, upazila, post_office, country,
                subtotal, delivery_cost, delivery_charge, discount_amount, coupon_code, total_amount, grand_total, 
                payment_method, payment_number, transaction_id, status, notes, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, 'Bangladesh',
                ?, ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, 'pending', ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
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
                $discountAmount,
                $couponCode,
                $total,
                $total,
                $paymentMethod,
                $payNumber,
                $trxId,
                $notes
            ]);

            $orderId = $db->lastInsertId();

            // Update Coupon usage count
            if (!empty($couponCode)) {
                try {
                    $db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?")->execute([$couponCode]);
                } catch (Exception $exCp) {}
            }

            // Auto-heal missing order_items columns for legacy MySQL tables
            try { @$db->exec("ALTER TABLE `order_items` ADD COLUMN `total_price` decimal(10,2) DEFAULT NULL"); } catch (Exception $ex) {}
            try { @$db->exec("ALTER TABLE `order_items` ADD `total_price` decimal(10,2) DEFAULT NULL"); } catch (Exception $ex) {}
            try { @$db->exec("ALTER TABLE `order_items` ADD COLUMN `product_image` varchar(255) DEFAULT NULL"); } catch (Exception $ex) {}
            try { @$db->exec("ALTER TABLE `order_items` ADD `product_image` varchar(255) DEFAULT NULL"); } catch (Exception $ex) {}
            try { @$db->exec("ALTER TABLE `order_items` ADD COLUMN `is_wholesale` tinyint(1) DEFAULT 0"); } catch (Exception $ex) {}
            try { @$db->exec("ALTER TABLE `order_items` ADD `is_wholesale` tinyint(1) DEFAULT 0"); } catch (Exception $ex) {}
            try { @$db->exec("ALTER TABLE `order_items` ADD COLUMN `color` varchar(100) DEFAULT NULL"); } catch (Exception $ex) {}
            try { @$db->exec("ALTER TABLE `order_items` ADD `color` varchar(100) DEFAULT NULL"); } catch (Exception $ex) {}
            try { @$db->exec("ALTER TABLE `order_items` ADD COLUMN `size` varchar(100) DEFAULT NULL"); } catch (Exception $ex) {}
            try { @$db->exec("ALTER TABLE `order_items` ADD `size` varchar(100) DEFAULT NULL"); } catch (Exception $ex) {}

            // Insert items safely preserving color and size
            foreach ($cart as $item) {
                $itemTotal = (float)($item['price'] * $item['quantity']);
                $isWholesale = !empty($item['is_wholesale']) ? 1 : 0;
                $img = $item['image'] ?? '';
                $itemColor = !empty($item['color']) ? trim($item['color']) : null;
                $itemSize = !empty($item['size']) ? trim($item['size']) : null;

                // Build guaranteed variant name string so color and size are NEVER lost in any system/view
                $variantParts = [];
                if (!empty($itemColor)) $variantParts[] = 'Color: ' . $itemColor;
                if (!empty($itemSize)) $variantParts[] = 'Size/Option: ' . $itemSize;
                $variantSuffix = !empty($variantParts) ? ' (' . implode(' • ', $variantParts) . ')' : '';
                $productNameWithVariant = $item['name'] . $variantSuffix;

                try {
                    $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity, total_price, is_wholesale, color, size, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
                    $itemStmt->execute([$orderId, $item['id'], $productNameWithVariant, $img, $item['price'], $item['quantity'], $itemTotal, $isWholesale, $itemColor, $itemSize]);
                } catch (Exception $e1) {
                    try {
                        $itemStmt2 = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity, total_price, is_wholesale, color, size) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $itemStmt2->execute([$orderId, $item['id'], $productNameWithVariant, $img, $item['price'], $item['quantity'], $itemTotal, $isWholesale, $itemColor, $itemSize]);
                    } catch (Exception $e2) {
                        try {
                            $itemStmt3 = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total_price, color, size) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                            $itemStmt3->execute([$orderId, $item['id'], $productNameWithVariant, $item['price'], $item['quantity'], $itemTotal, $itemColor, $itemSize]);
                        } catch (Exception $e3) {
                            try {
                                $itemStmt4 = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, color, size) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                $itemStmt4->execute([$orderId, $item['id'], $productNameWithVariant, $item['price'], $item['quantity'], $itemColor, $itemSize]);
                            } catch (Exception $e4) {
                                try {
                                    $itemStmt5 = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
                                    $itemStmt5->execute([$orderId, $item['id'], $productNameWithVariant, $item['price'], $item['quantity']]);
                                } catch (Exception $e5) {}
                            }
                        }
                    }
                }
            }

            // Mark cart abandonment session as recovered
            try {
                $db->prepare("UPDATE cart_abandonments SET recovered = 1 WHERE session_id = ? OR customer_phone = ?")->execute([session_id(), $phone]);
            } catch (Exception $ex) {}

            // Trigger Realtime Telegram Push Notification
            require_once __DIR__ . '/includes/telegram_bot.php';
            @sendTelegramOrderAlert($orderId);

            // Trigger Automated SMTP Email Confirmation
            require_once __DIR__ . '/includes/smtp_mailer.php';
            @sendOrderEmailNotifications($orderId);

            // Server-side Meta Conversions API (CAPI) Purchase Event Dispatch with Deduplicated Event ID
            if ((getSetting('track_purchase', '1') === '1') && (getSetting('meta_capi_enabled', '1') === '1')) {
                require_once __DIR__ . '/includes/meta_capi.php';
                $purchaseEventId = 'purchase_' . $orderNumber;
                
                $contentIds = [];
                $contents = [];
                foreach ($cart as $ci) {
                    $contentIds[] = (string)$ci['id'];
                    $contents[] = [
                        'id' => (string)$ci['id'],
                        'quantity' => (int)$ci['quantity'],
                        'item_price' => (float)$ci['price']
                    ];
                }

                $cData = [
                    'currency' => 'BDT',
                    'value' => (float)$total,
                    'content_type' => 'product',
                    'content_ids' => $contentIds,
                    'contents' => $contents,
                    'num_items' => count($cart),
                    'order_id' => $orderNumber
                ];

                $uData = [
                    'email' => $email,
                    'phone' => $phone,
                    'name' => $name,
                    'city' => $districtName,
                    'district' => $districtName,
                    'country' => 'bd'
                ];

                @MetaConversionsAPI::trackServerEvent('Purchase', $purchaseEventId, $cData, $uData);
            }

            // Clear session cart and applied coupon
            unset($_SESSION['cart']);
            unset($_SESSION['applied_coupon']);

            header('Location: order-success.php?order=' . $orderNumber);
            exit;
        } catch (Exception $e) {
            $error = 'Failed to process order: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Express Checkout - OnlineBdMart';

// Server-side Meta CAPI InitiateCheckout Dispatch
require_once __DIR__ . '/includes/meta_capi.php';
$initiateCheckoutEventId = 'initiate_checkout_' . (session_id() ?: rand(1000, 9999)) . '_' . time();
if ((getSetting('track_initiate_checkout', '1') === '1') && (getSetting('meta_capi_enabled', '1') === '1')) {
    $cIds = array_map(fn($it) => (string)$it['id'], $cart);
    $cData = [
        'currency' => 'BDT',
        'value' => (float)$subtotal,
        'content_type' => 'product',
        'content_ids' => $cIds,
        'num_items' => count($cart)
    ];
    $uData = [];
    if (!empty($custEmail)) $uData['email'] = $custEmail;
    if (!empty($custPhone)) $uData['phone'] = $custPhone;
    if (!empty($custName)) $uData['name'] = $custName;
    if (!empty($custDistrict)) $uData['city'] = $custDistrict;
    @MetaConversionsAPI::trackServerEvent('InitiateCheckout', $initiateCheckoutEventId, $cData, $uData);
}

require_once 'includes/header.php';

// Payment gateway numbers
$bkashNum = $settings['payment_bkash_number'] ?? ($settings['bkash_number'] ?? '01775153740');
$nagadNum = $settings['payment_nagad_number'] ?? ($settings['nagad_number'] ?? '01775153740');
$rocketNum = $settings['payment_rocket_number'] ?? ($settings['rocket_number'] ?? '01775153740');
$bankName = $settings['payment_bank_name'] ?? ($settings['bank_name'] ?? 'City Bank Ltd');
$bankAcc = $settings['payment_bank_acc_no'] ?? ($settings['bank_account_number'] ?? '1102938481001');
$advanceCodEnabled = ($settings['payment_cod_advance_delivery_charge'] ?? '1') === '1';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold font-serif text-slate-900">Complete Your Order</h1>
        <p class="text-xs text-slate-500 mt-1">Cash on delivery available across all 64 districts with digital coupon & bank payment options.</p>
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

                <!-- 2 Options for Logged in Customers -->
                <?php if ($isLoggedIn): ?>
                <div class="p-4 bg-indigo-50/70 border border-indigo-100 rounded-2xl space-y-3 text-xs">
                    <span class="font-extrabold text-indigo-950 uppercase tracking-wider text-[10px] block">Choose Delivery Address Option:</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border bg-white cursor-pointer border-indigo-600 shadow-sm ring-1 ring-indigo-600/20" id="optSavedLabel">
                            <input type="radio" name="address_choice" value="saved" checked onchange="toggleAddressChoice('saved')" class="text-indigo-600">
                            <div>
                                <span class="font-bold text-slate-900 block">✓ Use Saved Account Profile</span>
                                <span class="text-[11px] text-slate-500"><?= htmlspecialchars($custDistrict) ?> • <?= htmlspecialchars($custPhone ?: 'Auto-filled') ?></span>
                            </div>
                        </label>
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border bg-white cursor-pointer border-slate-200 hover:border-indigo-400" id="optNewLabel">
                            <input type="radio" name="address_choice" value="new" onchange="toggleAddressChoice('new')" class="text-indigo-600">
                            <div>
                                <span class="font-bold text-slate-900 block">+ Ship to Different Address</span>
                                <span class="text-[11px] text-slate-500">Enter a new receiver address</span>
                            </div>
                        </label>
                    </div>
                </div>
                <?php else: ?>
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2.5">
                        <i class="fas fa-user-circle text-indigo-600 text-lg"></i>
                        <span>Already have an account? <a href="login.php" class="text-indigo-600 font-bold hover:underline">Log in</a> for 1-click address auto-fill.</span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Your Full Name (আপনার নাম) *</label>
                        <input type="text" name="customer_name" id="inpCustName" value="<?= htmlspecialchars($custName) ?>" required placeholder="e.g. Arif Hossain" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Phone Number (সচল মোবাইল নাম্বার) *</label>
                        <input type="tel" name="customer_phone" id="inpCustPhone" value="<?= htmlspecialchars($custPhone) ?>" required placeholder="017xxxxxxxx" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 outline-none font-bold text-indigo-600">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Email Address (ইমেইল - Optional)</label>
                        <input type="email" name="customer_email" id="inpCustEmail" value="<?= htmlspecialchars($custEmail) ?>" placeholder="arif@example.com" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">District / City (জেলা) *</label>
                        <select name="district" id="inpCustDistrict" onchange="updateDeliveryCharge(this.value)" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 outline-none font-bold text-slate-800 bg-white">
                            <?php foreach ($districts as $d): ?>
                            <option value="<?= htmlspecialchars($d['name']) ?>" data-fee="<?= $d['delivery_fee'] ?>" data-days="<?= htmlspecialchars($d['estimated_days']) ?>" <?= $d['name'] === $custDistrict ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['name']) ?> (<?= htmlspecialchars($d['division_name']) ?>) - ৳<?= number_format($d['delivery_fee'], 0) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Thana / Upazila (থানা / উপজেলা)</label>
                        <input type="text" name="upazila" id="inpCustUpazila" value="<?= htmlspecialchars($custUpazila) ?>" placeholder="e.g. Mirpur, Dhanmondi, Savar" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Post Office / Area (পোস্ট অফিস / এলাকা)</label>
                        <input type="text" name="post_office" id="inpCustPostOffice" value="<?= htmlspecialchars($custPostOffice) ?>" placeholder="e.g. Mirpur-10" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 outline-none">
                    </div>
                </div>

                <div class="text-xs">
                    <label class="block font-bold text-slate-700 mb-1">Full Detailed Delivery Address (সম্পূর্ণ ঠিকানা - বাসা নং, রোড নং, এলাকা) *</label>
                    <textarea name="delivery_address" id="inpCustAddress" rows="2" required placeholder="House # 12, Road # 4, Block # B, Mirpur-10, Dhaka" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 outline-none"><?= htmlspecialchars($custAddress) ?></textarea>
                </div>
            </div>

            <!-- Payment Method Selection -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 space-y-6 shadow-sm">
                <div class="border-b pb-3 flex items-center justify-between">
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs">2</span>
                        Payment Method (পেমেন্ট পদ্ধতি)
                    </h2>
                </div>

                <div class="space-y-3 text-xs">
                    <!-- COD -->
                    <label class="flex items-start gap-3 p-4 rounded-2xl border-2 border-indigo-600 bg-indigo-50/50 cursor-pointer">
                        <input type="radio" name="payment_method" value="cod" checked onchange="togglePaymentInputs('cod')" class="text-indigo-600 focus:ring-indigo-500 mt-1">
                        <div class="flex-1 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-slate-900 block text-sm">Cash on Delivery (ক্যাশ অন ডেলিভারি)</span>
                                <i class="fas fa-hand-holding-dollar text-lg text-indigo-600"></i>
                            </div>

                            <?php if ($advanceCodEnabled): ?>
                            <!-- Advance Delivery Charge Alert Box -->
                            <div class="p-3 bg-amber-500/15 border border-amber-500/30 rounded-xl space-y-2 text-xs">
                                <p class="font-extrabold text-amber-950 leading-snug">
                                    ⚠️ ক্যাশ অন ডেলিভারিতে অর্ডার কনফার্ম করতে ডেলিভারি চার্জ <strong class="text-indigo-700 font-mono text-sm" id="codAdvanceFeeDisplay">৳120</strong> অগ্রিম বিকাশ/নগদ করুন। পণ্যের মূল্য ডেলিভারির সময় রাইডারকে ক্যাশ পরিশোধ করবেন।
                                </p>
                                <div class="flex flex-wrap items-center gap-3 font-mono font-black text-xs bg-white p-2.5 rounded-lg border border-amber-200">
                                    <span>bKash (Send Money): <strong class="text-pink-600"><?= htmlspecialchars($bkashNum) ?></strong></span>
                                    <span>•</span>
                                    <span>Nagad: <strong class="text-orange-600"><?= htmlspecialchars($nagadNum) ?></strong></span>
                                </div>
                            </div>
                            <?php else: ?>
                            <p class="text-slate-500 text-[11px]">Pay with cash when the delivery rider arrives at your doorstep. Open parcel inspection available.</p>
                            <?php endif; ?>
                        </div>
                    </label>

                    <!-- bKash Full Payment -->
                    <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 hover:border-pink-500 cursor-pointer">
                        <input type="radio" name="payment_method" value="bkash" onchange="togglePaymentInputs('bkash')" class="text-pink-600 focus:ring-pink-500">
                        <div class="flex-1">
                            <span class="font-extrabold text-pink-600 block">bKash (সম্পূর্ণ মূল্য অগ্রিম পরিশোধ)</span>
                            <span class="text-slate-500 text-[11px]">Send money to <strong><?= htmlspecialchars($bkashNum) ?></strong> and enter TrxID below.</span>
                        </div>
                        <span class="font-black text-pink-600 text-base">bKash</span>
                    </label>

                    <!-- Nagad Full Payment -->
                    <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 hover:border-orange-500 cursor-pointer">
                        <input type="radio" name="payment_method" value="nagad" onchange="togglePaymentInputs('nagad')" class="text-orange-600 focus:ring-orange-500">
                        <div class="flex-1">
                            <span class="font-extrabold text-orange-600 block">Nagad (সম্পূর্ণ মূল্য অগ্রিম পরিশোধ)</span>
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

                    <!-- TrxID input box -->
                    <div id="trxIdContainer" class="<?= $advanceCodEnabled ? '' : 'hidden' ?> p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                        <p class="text-xs font-bold text-slate-800" id="trxHelpText">
                            <?= $advanceCodEnabled ? 'অগ্রিম ডেলিভারি চার্জ পরিশোধের তথ্য দিন:' : 'পেমেন্ট ট্রানজেকশনের তথ্য দিন:' ?>
                        </p>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Sender Mobile Number (যে নাম্বার থেকে টাকা পাঠিয়েছেন) *</label>
                            <input type="tel" name="payment_number" placeholder="017xxxxxxxx" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-mono font-bold outline-none bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Enter Transaction ID (TrxID) / Reference *</label>
                            <input type="text" name="transaction_id" placeholder="e.g. 9J8A7D6F5E" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-mono font-bold outline-none uppercase bg-white">
                            <p class="text-[10px] text-slate-500 mt-1">আমরা ট্রানজেকশন ভেরিফাই করে পার্সেল কুরিয়ারে বুকিং করব।</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Order Summary, Coupon Box & Checkout Button -->
        <div class="space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-5 shadow-sm">
                <h3 class="text-base font-extrabold text-slate-900 border-b pb-3">Your Items (<?= count($cart) ?>)</h3>
                
                <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
                    <?php foreach ($cart as $it): ?>
                    <div class="flex items-center justify-between text-xs gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <img src="/<?= ltrim($it['image'], '/') ?>" class="w-12 h-12 object-cover rounded-xl bg-slate-50 border shrink-0">
                            <div class="min-w-0">
                                <p class="font-bold text-slate-800 truncate"><?= htmlspecialchars($it['name']) ?></p>
                                <?php if (!empty($it['color']) || !empty($it['size'])): ?>
                                <div class="flex items-center gap-1.5 my-0.5">
                                    <?php if (!empty($it['color'])): ?>
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-indigo-50 text-indigo-700">Color: <?= htmlspecialchars($it['color']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($it['size'])): ?>
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-50 text-amber-800">Size: <?= htmlspecialchars($it['size']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                <span class="text-slate-500 text-[11px]"><?= $it['quantity'] ?> × ৳<?= number_format($it['price'], 2) ?></span>
                            </div>
                        </div>
                        <span class="font-black text-slate-900 shrink-0">৳<?= number_format($it['price'] * $it['quantity'], 2) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Interactive Coupon Discount Box -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                    <label class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <i class="fas fa-ticket text-indigo-600"></i> Have a Coupon Code? (কুপন কোড)
                    </label>
                    
                    <div id="couponAppliedBox" class="<?= !empty($appliedCoupon) ? '' : 'hidden' ?> flex items-center justify-between p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <i class="fas fa-circle-check text-emerald-600"></i>
                            <span class="font-mono font-black text-emerald-800" id="appliedCouponCodeDisplay"><?= htmlspecialchars($appliedCoupon['code'] ?? '') ?></span>
                            <span class="text-emerald-700 font-bold" id="appliedCouponDiscountDisplay">(-৳<?= number_format($discountAmount, 2) ?>)</span>
                        </div>
                        <button type="button" onclick="removeCouponAjax()" class="text-rose-600 hover:text-rose-800 font-bold text-xs p-1">✕ Remove</button>
                    </div>

                    <div id="couponInputBox" class="<?= empty($appliedCoupon) ? '' : 'hidden' ?> flex items-center gap-2">
                        <input type="text" id="couponCodeInput" placeholder="Enter Code (e.g. SPECIAL100)" class="flex-1 px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono font-bold uppercase outline-none focus:border-indigo-500 bg-white">
                        <button type="button" onclick="applyCouponAjax()" id="applyCouponBtn" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow transition shrink-0">
                            Apply
                        </button>
                    </div>
                    <div id="couponFeedbackMsg" class="hidden text-[11px] font-bold"></div>
                </div>

                <!-- Price Calculations Breakdown -->
                <div class="pt-4 border-t border-slate-100 space-y-2.5 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Items Subtotal</span>
                        <span class="font-bold text-slate-900" id="subtotalDisplay">৳<?= number_format($subtotal, 2) ?></span>
                    </div>

                    <div id="discountRow" class="<?= $discountAmount > 0 ? '' : 'hidden' ?> flex justify-between text-emerald-600 font-bold">
                        <span>Coupon Discount</span>
                        <span id="discountDisplay">-৳<?= number_format($discountAmount, 2) ?></span>
                    </div>

                    <div class="flex justify-between text-slate-600">
                        <span>Delivery Charge</span>
                        <span id="checkoutDeliveryFee" class="font-bold text-slate-900">৳120.00</span>
                    </div>
                    
                    <div class="flex justify-between text-slate-600">
                        <span>Estimated Arrival</span>
                        <span id="checkoutEstDays" class="font-bold text-emerald-600">1-2 days</span>
                    </div>

                    <div class="pt-3 border-t space-y-2">
                        <?php if ($advanceCodEnabled): ?>
                        <div class="flex justify-between text-xs font-bold text-amber-950 bg-amber-50 p-2.5 rounded-xl border border-amber-200" id="advancePayableRow">
                            <span>অগ্রিম প্রদেয় ডেলিভারি চার্জ:</span>
                            <span id="advancePayableDisplay" class="font-black font-mono text-indigo-700">৳120.00</span>
                        </div>
                        <div class="flex justify-between text-xs font-bold text-slate-800 bg-slate-100 p-2.5 rounded-xl border border-slate-200" id="dueOnDeliveryRow">
                            <span>ডেলিভারির সময় প্রদেয় ক্যাশ (COD):</span>
                            <span id="dueOnDeliveryDisplay" class="font-black font-mono text-slate-950">৳<?= number_format(max(0, $subtotal - $discountAmount), 2) ?></span>
                        </div>
                        <?php endif; ?>

                        <div class="flex justify-between text-base font-black text-slate-900 pt-1">
                            <span>Total Order Value</span>
                            <span class="text-indigo-600 font-black text-lg font-mono" id="checkoutTotalPayable">৳<?= number_format(max(0, $subtotal - $discountAmount) + 120.0, 2) ?></span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-black text-sm rounded-2xl shadow-xl shadow-indigo-600/25 transition active:scale-98 flex items-center justify-center gap-2">
                    <i class="fas fa-lock text-xs"></i> <span>Confirm & Place Order</span>
                </button>

                <p class="text-center text-[10px] text-slate-400">By placing order, you agree to our 3-Day replacement policy & delivery terms.</p>
            </div>
        </div>
    </form>
</div>

<script>
// DataLayer & Meta Pixel InitiateCheckout Event with Deduplicated event_id
window.dataLayer = window.dataLayer || [];
window.dataLayer.push({
    event: 'begin_checkout',
    ecommerce: {
        currency: 'BDT',
        value: <?= (float)$subtotal ?>,
        items: <?= json_encode(array_values(array_map(fn($it) => [
            'item_id' => (string)$it['id'],
            'item_name' => $it['name'],
            'price' => (float)$it['price'],
            'quantity' => (int)$it['quantity'],
            'item_variant' => (!empty($it['color']) ? 'Color: ' . $it['color'] : '') . (!empty($it['size']) ? ' Size: ' . $it['size'] : '')
        ], $cart))) ?>
    },
    meta_event: 'InitiateCheckout',
    event_id: '<?= $initiateCheckoutEventId ?>',
    content_ids: <?= json_encode(array_values(array_map(fn($it) => (string)$it['id'], $cart))) ?>,
    content_type: 'product',
    value: <?= (float)$subtotal ?>,
    currency: 'BDT'
});

if (typeof fbq === 'function') {
    fbq('track', 'InitiateCheckout', {
        content_ids: <?= json_encode(array_values(array_map(fn($it) => (string)$it['id'], $cart))) ?>,
        content_type: 'product',
        value: <?= (float)$subtotal ?>,
        currency: 'BDT',
        num_items: <?= count($cart) ?>
    }, { eventID: '<?= $initiateCheckoutEventId ?>' });
}

let currentSubtotal = <?= (float)$subtotal ?>;
let currentDiscount = <?= (float)$discountAmount ?>;
let currentDeliveryFee = 120.0;
const advanceCodEnabled = <?= $advanceCodEnabled ? 'true' : 'false' ?>;

function updateDeliveryCharge(districtName) {
    const sel = document.getElementById('inpCustDistrict');
    const opt = sel.options[sel.selectedIndex];
    let fee = parseFloat(opt.getAttribute('data-fee') || 120);
    const days = opt.getAttribute('data-days') || '1-2 days';

    if (currentSubtotal >= 2000) {
        fee = 0.0;
        document.getElementById('checkoutDeliveryFee').textContent = 'FREE (৳0.00)';
    } else {
        document.getElementById('checkoutDeliveryFee').textContent = '৳' + fee.toFixed(2);
    }

    currentDeliveryFee = fee;
    document.getElementById('checkoutEstDays').textContent = days;
    
    const codAdvEl = document.getElementById('codAdvanceFeeDisplay');
    if (codAdvEl) codAdvEl.textContent = '৳' + fee.toFixed(0);

    const advPayEl = document.getElementById('advancePayableDisplay');
    if (advPayEl) advPayEl.textContent = '৳' + fee.toFixed(2);

    recalculateTotal();
}

function recalculateTotal() {
    const itemsPayable = Math.max(0, currentSubtotal - currentDiscount);
    const totalOrderVal = itemsPayable + currentDeliveryFee;
    
    document.getElementById('checkoutTotalPayable').textContent = '৳' + totalOrderVal.toFixed(2);
    
    const dueEl = document.getElementById('dueOnDeliveryDisplay');
    if (dueEl) dueEl.textContent = '৳' + itemsPayable.toFixed(2);
}

function togglePaymentInputs(method) {
    const trxBox = document.getElementById('trxIdContainer');
    const trxHelp = document.getElementById('trxHelpText');
    const advRow = document.getElementById('advancePayableRow');
    const dueRow = document.getElementById('dueOnDeliveryRow');

    if (method === 'cod') {
        if (advanceCodEnabled) {
            trxBox.classList.remove('hidden');
            if (trxHelp) trxHelp.textContent = 'অগ্রিম ডেলিভারি চার্জ পাঠানোর পর আপনার বিকাশ/নগদ নম্বর এবং TrxID দিন:';
            if (advRow) advRow.classList.remove('hidden');
            if (dueRow) dueRow.classList.remove('hidden');
        } else {
            trxBox.classList.add('hidden');
        }
    } else {
        trxBox.classList.remove('hidden');
        if (trxHelp) trxHelp.textContent = 'সম্পূর্ণ মূল্য পরিশোধের বিকাশ/নগদ নম্বর ও TrxID দিন:';
        if (advRow) advRow.classList.add('hidden');
        if (dueRow) dueRow.classList.add('hidden');
    }
}

function applyCouponAjax() {
    const input = document.getElementById('couponCodeInput');
    const code = input.value.trim();
    const msg = document.getElementById('couponFeedbackMsg');
    const btn = document.getElementById('applyCouponBtn');

    if (!code) {
        msg.textContent = 'অনুগ্রহ করে কুপন কোড লিখুন।';
        msg.className = 'text-[11px] font-bold text-rose-500 block mt-1';
        return;
    }

    btn.disabled = true;
    btn.textContent = '...';

    const formData = new FormData();
    formData.append('coupon_action', 'apply');
    formData.append('coupon_code', code);

    fetch('checkout.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = 'Apply';

        if (data.success) {
            currentDiscount = parseFloat(data.discount);
            document.getElementById('appliedCouponCodeDisplay').textContent = data.code;
            document.getElementById('appliedCouponDiscountDisplay').textContent = '(-৳' + currentDiscount.toFixed(2) + ')';
            document.getElementById('discountDisplay').textContent = '-৳' + currentDiscount.toFixed(2);
            document.getElementById('discountRow').classList.remove('hidden');

            document.getElementById('couponInputBox').classList.add('hidden');
            document.getElementById('couponAppliedBox').classList.remove('hidden');
            
            msg.textContent = data.message;
            msg.className = 'text-[11px] font-bold text-emerald-600 block mt-1';

            recalculateTotal();
        } else {
            msg.textContent = data.message;
            msg.className = 'text-[11px] font-bold text-rose-500 block mt-1';
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.textContent = 'Apply';
        msg.textContent = 'কুপন প্রয়োগ করতে ত্রুটি হয়েছে।';
        msg.className = 'text-[11px] font-bold text-rose-500 block mt-1';
    });
}

function removeCouponAjax() {
    const formData = new FormData();
    formData.append('coupon_action', 'remove');

    fetch('checkout.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        currentDiscount = 0.0;
        document.getElementById('discountRow').classList.add('hidden');
        document.getElementById('couponAppliedBox').classList.add('hidden');
        document.getElementById('couponInputBox').classList.remove('hidden');
        document.getElementById('couponCodeInput').value = '';
        
        const msg = document.getElementById('couponFeedbackMsg');
        msg.textContent = data.message;
        msg.className = 'text-[11px] font-bold text-slate-500 block mt-1';

        recalculateTotal();
    });
}

function togglePaymentInputs(method) {
    const trxBox = document.getElementById('trxIdContainer');
    if (method === 'cod') {
        trxBox.classList.add('hidden');
    } else {
        trxBox.classList.remove('hidden');
    }
}

function toggleAddressChoice(type) {
    const isSaved = (type === 'saved');
    document.getElementById('inpCustName').value = isSaved ? '<?= addslashes($custName) ?>' : '';
    document.getElementById('inpCustPhone').value = isSaved ? '<?= addslashes($custPhone) ?>' : '';
    document.getElementById('inpCustEmail').value = isSaved ? '<?= addslashes($custEmail) ?>' : '';
    document.getElementById('inpCustAddress').value = isSaved ? '<?= addslashes($custAddress) ?>' : '';
    document.getElementById('inpCustUpazila').value = isSaved ? '<?= addslashes($custUpazila) ?>' : '';
    document.getElementById('inpCustPostOffice').value = isSaved ? '<?= addslashes($custPostOffice) ?>' : '';
    
    if (isSaved) {
        document.getElementById('optSavedLabel').className = 'flex items-center gap-2.5 p-3 rounded-xl border bg-white cursor-pointer border-indigo-600 shadow-sm ring-1 ring-indigo-600/20';
        document.getElementById('optNewLabel').className = 'flex items-center gap-2.5 p-3 rounded-xl border bg-white cursor-pointer border-slate-200 hover:border-indigo-400';
    } else {
        document.getElementById('optSavedLabel').className = 'flex items-center gap-2.5 p-3 rounded-xl border bg-white cursor-pointer border-slate-200 hover:border-indigo-400';
        document.getElementById('optNewLabel').className = 'flex items-center gap-2.5 p-3 rounded-xl border bg-white cursor-pointer border-indigo-600 shadow-sm ring-1 ring-indigo-600/20';
    }
}

// Initial calculation on load
window.addEventListener('DOMContentLoaded', () => {
    updateDeliveryCharge(document.getElementById('inpCustDistrict').value);
});
</script>

<?php require_once 'includes/footer.php'; ?>
