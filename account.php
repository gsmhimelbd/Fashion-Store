<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';

// Redirect to login if not logged in
if (empty($_SESSION['user_logged_in'])) {
    header('Location: login.php?redirect=account.php');
    exit;
}

$userId = $_SESSION['user_id'] ?? 0;
$userEmail = $_SESSION['user_email'] ?? '';
$userPhone = $_SESSION['user_phone'] ?? '';
$msg = '';
$error = '';

try {
    $db = getDB();

    // Fetch user details
    $uStmt = $db->prepare("SELECT * FROM users WHERE id = ? OR email = ? LIMIT 1");
    $uStmt->execute([$userId, $userEmail]);
    $user = $uStmt->fetch();

    if (!$user) {
        $user = [
            'id' => $userId,
            'name' => $_SESSION['user_name'] ?? 'Customer',
            'email' => $userEmail,
            'phone' => $userPhone,
            'district' => 'Dhaka',
            'address' => '',
        ];
    }

    // Handle Profile & Password Updates
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_profile') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $district = trim($_POST['district'] ?? 'Dhaka');
            $address = trim($_POST['address'] ?? '');

            if ($name && $email) {
                $upStmt = $db->prepare("UPDATE users SET name = ?, phone = ?, email = ?, district = ?, address = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $upStmt->execute([$name, $phone, $email, $district, $address, $user['id']]);

                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_phone'] = $phone;

                $user['name'] = $name;
                $user['phone'] = $phone;
                $user['email'] = $email;
                $user['district'] = $district;
                $user['address'] = $address;

                $msg = 'Your profile and default delivery address have been updated!';
            }
        } elseif ($action === 'update_password') {
            $currentPass = trim($_POST['current_password'] ?? '');
            $newPass = trim($_POST['new_password'] ?? '');
            $confirmPass = trim($_POST['confirm_password'] ?? '');

            if (empty($newPass) || strlen($newPass) < 6) {
                $error = 'New password must be at least 6 characters long.';
            } elseif ($newPass !== $confirmPass) {
                $error = 'New password and confirm password do not match.';
            } else {
                $hashed = password_hash($newPass, PASSWORD_BCRYPT);
                $pStmt = $db->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $pStmt->execute([$hashed, $user['id']]);
                $msg = 'Password changed successfully! Please use your new password next time.';
            }
        }
    }

    // Fetch user orders with items
    $orderStmt = $db->prepare("SELECT * FROM orders WHERE customer_email = ? OR customer_phone = ? OR phone = ? ORDER BY id DESC");
    $orderStmt->execute([$user['email'], $user['phone'], $user['phone']]);
    $userOrders = $orderStmt->fetchAll();

    // Order Stats
    $totalOrdersCount = count($userOrders);
    $pendingOrdersCount = 0;
    $deliveredOrdersCount = 0;
    $totalSpentAmount = 0.0;

    foreach ($userOrders as $uo) {
        $total = floatval($uo['total_amount'] ?: ($uo['grand_total'] ?? 0));
        $totalSpentAmount += $total;
        if ($uo['status'] === 'pending' || $uo['status'] === 'processing') $pendingOrdersCount++;
        if ($uo['status'] === 'delivered') $deliveredOrdersCount++;
    }

    $districts = $db->query("SELECT DISTINCT name FROM districts ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $userOrders = [];
    $totalOrdersCount = 0;
    $pendingOrdersCount = 0;
    $deliveredOrdersCount = 0;
    $totalSpentAmount = 0.0;
    $districts = ['Dhaka', 'Tangail', 'Chittagong', 'Sylhet', 'Rajshahi', 'Khulna', 'Barisal', 'Rangpur'];
}

$pageTitle = 'Customer Portal & Dashboard - OnlineBdMart';
require_once 'includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Welcome Header Banner -->
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl mb-8 flex flex-col sm:flex-row items-center justify-between gap-6 border border-slate-800">
        <div class="flex items-center gap-4 text-center sm:text-left">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 to-cyan-400 flex items-center justify-center text-2xl font-black shadow-lg text-white shrink-0">
                <?= strtoupper(substr($user['name'] ?? 'C', 0, 1)) ?>
            </div>
            <div>
                <div class="flex items-center gap-2 justify-center sm:justify-start">
                    <h1 class="text-xl sm:text-2xl font-black font-serif"><?= htmlspecialchars($user['name']) ?></h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Verified Member</span>
                </div>
                <p class="text-xs text-slate-400 mt-1"><?= htmlspecialchars($user['email']) ?> • <?= htmlspecialchars($user['phone'] ?? 'No Phone') ?></p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="shop.php" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                <i class="fas fa-bag-shopping"></i> <span>Continue Shopping</span>
            </a>
            <a href="logout.php" class="px-4 py-2.5 bg-slate-800 hover:bg-rose-600 text-slate-300 hover:text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                <i class="fas fa-arrow-right-from-bracket"></i> <span>Sign Out</span>
            </a>
        </div>
    </div>

    <?php if ($msg): ?>
    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
        <i class="fas fa-circle-check text-emerald-600 text-base"></i> <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-2">
        <i class="fas fa-circle-exclamation text-rose-600 text-base"></i> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <!-- Customer Dashboard Overview Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8 text-xs">
        <div class="p-5 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-1">
            <span class="text-[10px] font-bold uppercase text-slate-400">Total Orders Placed</span>
            <p class="text-2xl font-black text-slate-900"><?= $totalOrdersCount ?></p>
            <span class="text-[10px] text-indigo-600 font-semibold">Lifetime purchases</span>
        </div>
        <div class="p-5 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-1">
            <span class="text-[10px] font-bold uppercase text-amber-500">In Progress / Pending</span>
            <p class="text-2xl font-black text-amber-600"><?= $pendingOrdersCount ?></p>
            <span class="text-[10px] text-slate-400 font-semibold">Processing & Transit</span>
        </div>
        <div class="p-5 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-1">
            <span class="text-[10px] font-bold uppercase text-emerald-500">Delivered Orders</span>
            <p class="text-2xl font-black text-emerald-600"><?= $deliveredOrdersCount ?></p>
            <span class="text-[10px] text-emerald-700 font-semibold">Successfully received</span>
        </div>
        <div class="p-5 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-1">
            <span class="text-[10px] font-bold uppercase text-slate-400">Total Amount Spent</span>
            <p class="text-2xl font-black text-indigo-600">৳<?= number_format($totalSpentAmount, 2) ?></p>
            <span class="text-[10px] text-slate-400 font-semibold">COD & Digital Paid</span>
        </div>
    </div>

    <!-- Dashboard Tabs Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left Tab Controls -->
        <div class="lg:col-span-4 space-y-4">
            <div class="bg-white rounded-3xl border border-slate-200 p-4 shadow-sm space-y-1 text-xs font-bold">
                <button type="button" onclick="showTab('ordersTab', this)" class="tab-btn w-full px-4 py-3 rounded-2xl transition text-left flex items-center justify-between bg-indigo-600 text-white shadow-md">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-receipt text-base"></i> <span>My Orders History</span>
                    </div>
                    <span class="bg-white/20 px-2 py-0.5 rounded-full text-[10px] font-black"><?= $totalOrdersCount ?></span>
                </button>

                <button type="button" onclick="showTab('profileTab', this)" class="tab-btn w-full px-4 py-3 rounded-2xl transition text-left flex items-center gap-3 text-slate-600 hover:bg-slate-50">
                    <i class="fas fa-user-gear text-base text-indigo-600"></i> <span>Profile & Address Settings</span>
                </button>

                <button type="button" onclick="showTab('securityTab', this)" class="tab-btn w-full px-4 py-3 rounded-2xl transition text-left flex items-center gap-3 text-slate-600 hover:bg-slate-50">
                    <i class="fas fa-lock text-base text-amber-500"></i> <span>Change Password</span>
                </button>

                <a href="track-order.php" class="w-full px-4 py-3 rounded-2xl transition text-left flex items-center justify-between text-emerald-700 bg-emerald-50 hover:bg-emerald-100 block">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-truck-fast text-base"></i> <span>Live Order Tracker</span>
                    </div>
                    <i class="fas fa-arrow-right text-[10px]"></i>
                </a>

                <a href="logout.php" class="w-full px-4 py-3 rounded-2xl transition text-left flex items-center gap-3 text-rose-600 hover:bg-rose-50 block">
                    <i class="fas fa-arrow-right-from-bracket text-base"></i> <span>Log Out</span>
                </a>
            </div>

            <!-- Customer Care Help Box -->
            <div class="bg-indigo-50 border border-indigo-100 rounded-3xl p-6 space-y-3 text-xs">
                <div class="flex items-center gap-2 text-indigo-900 font-extrabold">
                    <i class="fab fa-whatsapp text-lg text-emerald-600"></i>
                    <span>Need Help with an Order?</span>
                </div>
                <p class="text-indigo-800 leading-relaxed text-[11px]">Our Tangail & Dhaka fulfillment team is available 24/7 on WhatsApp for order changes or delivery inquiries.</p>
                <a href="https://wa.me/88<?= htmlspecialchars($s['whatsapp_number'] ?? '01775153740') ?>?text=<?= urlencode('Hello OnlineBdMart support, I need help with my customer account: ' . ($user['email'] ?? '')) ?>" target="_blank" class="w-full py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl text-center block shadow transition">
                    <i class="fab fa-whatsapp mr-1"></i> WhatsApp Support Agent
                </a>
            </div>
        </div>

        <!-- Right Tab Panels -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- 1. Orders Tab -->
            <div id="ordersTab" class="tab-panel bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 space-y-6 shadow-sm">
                <div class="flex items-center justify-between border-b pb-4">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">My Orders History</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Review current parcel status, items, and download tax invoices.</p>
                    </div>
                    <span class="text-xs font-bold text-indigo-600"><?= count($userOrders) ?> Orders</span>
                </div>

                <?php if (!empty($userOrders)): ?>
                    <div class="space-y-4">
                        <?php foreach ($userOrders as $ord): 
                            $orderId = $ord['id'];
                            $items = $db->query("SELECT * FROM order_items WHERE order_id = {$orderId}")->fetchAll();
                            $status = $ord['status'] ?? 'pending';
                        ?>
                        <div class="p-5 rounded-3xl bg-slate-50 border border-slate-200 space-y-4 text-xs">
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400">Order ID:</span>
                                    <span class="font-mono font-extrabold text-slate-900 text-sm ml-1">#<?= htmlspecialchars($ord['order_number'] ?: $ord['id']) ?></span>
                                    <span class="text-[11px] text-slate-500 ml-2">(<?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?>)</span>
                                </div>
                                <div>
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider <?= $status === 'delivered' ? 'bg-emerald-100 text-emerald-700' : ($status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-700') ?>">
                                        <?= htmlspecialchars($status) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Ordered Items List -->
                            <div class="space-y-2.5">
                                <?php foreach ($items as $item): ?>
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <img src="/<?= ltrim($item['product_image'] ?: 'images/products/watch-1.jpg', '/') ?>" class="w-12 h-12 object-cover rounded-xl bg-white border shrink-0">
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-900 truncate"><?= htmlspecialchars($item['product_name']) ?></p>
                                            <span class="text-slate-500 text-[11px]">Qty: <?= $item['quantity'] ?> × ৳<?= number_format($item['price'], 2) ?></span>
                                        </div>
                                    </div>
                                    <span class="font-extrabold text-slate-900 shrink-0">৳<?= number_format($item['total_price'] ?: ($item['price'] * $item['quantity']), 2) ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Totals & Actions -->
                            <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-200">
                                <div>
                                    <span class="text-slate-500">Delivery to: <strong><?= htmlspecialchars($ord['district_name'] ?: ($ord['district'] ?? 'Dhaka')) ?></strong> (<?= strtoupper(htmlspecialchars($ord['payment_method'] ?? 'COD')) ?>)</span>
                                    <p class="text-slate-900 font-black text-sm mt-0.5">Grand Total: <span class="text-indigo-600">৳<?= number_format($ord['total_amount'] ?: ($ord['grand_total'] ?? 0), 2) ?></span></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="track-order.php?order_number=<?= urlencode($ord['order_number'] ?: $ord['id']) ?>&phone=<?= urlencode($ord['customer_phone'] ?: ($ord['phone'] ?? '')) ?>" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold text-[11px] shadow transition flex items-center gap-1.5">
                                        <i class="fas fa-truck-fast"></i> <span>Track Order</span>
                                    </a>
                                    <a href="invoice.php?id=<?= $ord['id'] ?>" target="_blank" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold text-[11px] shadow transition flex items-center gap-1.5">
                                        <i class="fas fa-file-invoice"></i> <span>Invoice</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-12 text-center text-slate-400 space-y-3 bg-slate-50 rounded-3xl border border-slate-200">
                        <i class="fas fa-box-open text-4xl text-slate-300"></i>
                        <h3 class="font-bold text-slate-800 text-sm">No Orders Placed Yet</h3>
                        <p class="text-xs text-slate-500">Explore original accessories and smart gadgets with Cash on Delivery.</p>
                        <a href="shop.php" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow inline-block mt-2">Start Shopping</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 2. Profile Settings Tab -->
            <div id="profileTab" class="tab-panel bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 space-y-6 shadow-sm hidden">
                <div class="border-b pb-4">
                    <h2 class="text-base font-extrabold text-slate-900">Profile & Default Delivery Address</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Keep your active phone number and street address up-to-date for fast delivery dispatch.</p>
                </div>

                <form method="POST" action="account.php" class="space-y-4 text-xs">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-700 font-bold mb-1">Full Name *</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                        </div>
                        <div>
                            <label class="block text-slate-700 font-bold mb-1">Active Mobile Phone *</label>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required placeholder="017xxxxxxxx" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500 font-mono font-medium">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-700 font-bold mb-1">Email Address *</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                        </div>
                        <div>
                            <label class="block text-slate-700 font-bold mb-1">Default Delivery District *</label>
                            <select name="district" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50 font-bold">
                                <?php foreach ($districts as $dName): ?>
                                <option value="<?= htmlspecialchars($dName) ?>" <?= ($user['district'] === $dName) ? 'selected' : '' ?>><?= htmlspecialchars($dName) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Default Street Address / Landmark *</label>
                        <textarea name="address" rows="3" placeholder="House No, Road Name, Area/Thana..." required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500 font-medium"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl shadow-lg transition">
                        Save Profile Details
                    </button>
                </form>
            </div>

            <!-- 3. Security & Password Tab -->
            <div id="securityTab" class="tab-panel bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 space-y-6 shadow-sm hidden">
                <div class="border-b pb-4">
                    <h2 class="text-base font-extrabold text-slate-900">Change Account Password</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Ensure your account uses a secure password of at least 6 characters.</p>
                </div>

                <form method="POST" action="account.php" class="space-y-4 text-xs max-w-md">
                    <input type="hidden" name="action" value="update_password">

                    <div>
                        <label class="block text-slate-700 font-bold mb-1">New Password *</label>
                        <input type="password" name="new_password" required placeholder="Minimum 6 characters" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                    </div>

                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Confirm New Password *</label>
                        <input type="password" name="confirm_password" required placeholder="Repeat new password" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                    </div>

                    <button type="submit" class="px-8 py-3 bg-slate-900 hover:bg-slate-800 text-white font-extrabold rounded-xl shadow-lg transition">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabId, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    const target = document.getElementById(tabId);
    if (target) target.classList.remove('hidden');

    document.querySelectorAll('.tab-btn').forEach(b => {
        b.className = 'tab-btn w-full px-4 py-3 rounded-2xl transition text-left flex items-center justify-between text-slate-600 hover:bg-slate-50';
    });
    btn.className = 'tab-btn w-full px-4 py-3 rounded-2xl transition text-left flex items-center justify-between bg-indigo-600 text-white shadow-md';
}
</script>

<?php require_once 'includes/footer.php'; ?>
