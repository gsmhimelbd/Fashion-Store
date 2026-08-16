<?php
$adminTitle = 'Discount Coupons & Header Banner Manager';
require_once __DIR__ . '/header.php';

$msg = '';
$error = '';

try {
    $db = getDB();

    // Auto-heal coupons and orders schema individually
    try { $db->exec("CREATE TABLE IF NOT EXISTS `coupons` (`id` int(11) NOT NULL AUTO_INCREMENT, `code` varchar(50) NOT NULL, `discount_type` varchar(20) NOT NULL DEFAULT 'fixed', `discount_value` decimal(10,2) NOT NULL DEFAULT 0.00, `min_spend` decimal(10,2) NOT NULL DEFAULT 0.00, `product_id` int(11) DEFAULT NULL, `show_in_header` tinyint(1) DEFAULT 1, `header_banner_text` varchar(255) DEFAULT NULL, `expiry_date` date DEFAULT NULL, `used_count` int(11) DEFAULT 0, `is_active` tinyint(1) DEFAULT 1, `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `code` (`code`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE `coupons` ADD COLUMN `product_id` int(11) DEFAULT NULL"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE `coupons` ADD `product_id` int(11) DEFAULT NULL"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE `coupons` ADD COLUMN `show_in_header` tinyint(1) DEFAULT 1"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE `coupons` ADD `show_in_header` tinyint(1) DEFAULT 1"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE `coupons` ADD COLUMN `header_banner_text` varchar(255) DEFAULT NULL"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE `coupons` ADD `header_banner_text` varchar(255) DEFAULT NULL"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE `coupons` ADD COLUMN `discount_type` varchar(20) NOT NULL DEFAULT 'fixed'"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE `coupons` ADD COLUMN `discount_value` decimal(10,2) NOT NULL DEFAULT 0.00"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE `coupons` ADD COLUMN `min_spend` decimal(10,2) NOT NULL DEFAULT 0.00"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE `coupons` ADD COLUMN `expiry_date` date DEFAULT NULL"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE `coupons` ADD COLUMN `used_count` int(11) DEFAULT 0"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE `coupons` ADD COLUMN `is_active` tinyint(1) DEFAULT 1"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE `orders` ADD COLUMN `coupon_code` varchar(50) DEFAULT NULL"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE `orders` ADD COLUMN `discount_amount` decimal(10,2) DEFAULT 0.00"); } catch (Exception $e) {}

    // Handle Actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'create' || $action === 'update') {
            $code = strtoupper(trim(preg_replace('/[^A-Za-z0-9_-]/', '', $_POST['code'] ?? '')));
            $discountType = ($_POST['discount_type'] ?? '') === 'percent' ? 'percent' : 'fixed';
            $discountValue = (float)($_POST['discount_value'] ?? 0);
            $minSpend = (float)($_POST['min_spend'] ?? 0);
            $productId = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;
            $showInHeader = isset($_POST['show_in_header']) ? 1 : 0;
            $headerBannerText = trim($_POST['header_banner_text'] ?? '');
            $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if (empty($headerBannerText)) {
                if ($discountType === 'percent') {
                    $headerBannerText = "🎁 Special Offer: Use Code \"{$code}\" to get {$discountValue}% discount on orders!";
                } else {
                    $headerBannerText = "🎁 Special Offer: Use Code \"{$code}\" to get ৳{$discountValue} OFF on orders!";
                }
            }

            if ($action === 'create') {
                if (empty($code) || $discountValue <= 0) {
                    $error = 'Please enter a valid coupon code and discount value.';
                } else {
                    try {
                        $stmt = $db->prepare("INSERT INTO coupons (`code`, `discount_type`, `discount_value`, `min_spend`, `product_id`, `show_in_header`, `header_banner_text`, `expiry_date`, `is_active`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
                        $stmt->execute([$code, $discountType, $discountValue, $minSpend, $productId, $showInHeader, $headerBannerText, $expiryDate, $isActive]);
                    } catch (Exception $e1) {
                        try {
                            $stmt = $db->prepare("INSERT INTO coupons (`code`, `discount_type`, `discount_value`, `min_spend`, `show_in_header`, `header_banner_text`, `is_active`) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$code, $discountType, $discountValue, $minSpend, $showInHeader, $headerBannerText, $isActive]);
                        } catch (Exception $e2) {
                            $stmt = $db->prepare("INSERT INTO coupons (`code`, `discount_type`, `discount_value`) VALUES (?, ?, ?)");
                            $stmt->execute([$code, $discountType, $discountValue]);
                        }
                    }
                    $msg = "✓ Coupon \"{$code}\" created successfully!";
                }
            } else {
                $id = (int)$_POST['coupon_id'];
                try {
                    $stmt = $db->prepare("UPDATE coupons SET `code` = ?, `discount_type` = ?, `discount_value` = ?, `min_spend` = ?, `product_id` = ?, `show_in_header` = ?, `header_banner_text` = ?, `expiry_date` = ?, `is_active` = ? WHERE `id` = ?");
                    $stmt->execute([$code, $discountType, $discountValue, $minSpend, $productId, $showInHeader, $headerBannerText, $expiryDate, $isActive, $id]);
                } catch (Exception $e1) {
                    try {
                        $stmt = $db->prepare("UPDATE coupons SET `code` = ?, `discount_type` = ?, `discount_value` = ?, `min_spend` = ?, `show_in_header` = ?, `header_banner_text` = ?, `is_active` = ? WHERE `id` = ?");
                        $stmt->execute([$code, $discountType, $discountValue, $minSpend, $showInHeader, $headerBannerText, $isActive, $id]);
                    } catch (Exception $e2) {
                        $stmt = $db->prepare("UPDATE coupons SET `code` = ?, `discount_value` = ? WHERE `id` = ?");
                        $stmt->execute([$code, $discountValue, $id]);
                    }
                }
                $msg = "✓ Coupon \"{$code}\" updated successfully!";
            }
        } elseif ($action === 'toggle_header') {
            $id = (int)$_POST['coupon_id'];
            $current = (int)$_POST['current_status'];
            $newStatus = $current === 1 ? 0 : 1;
            
            if ($newStatus === 1) {
                try { @$db->exec("UPDATE coupons SET show_in_header = 0"); } catch (Exception $ex) {}
            }
            try {
                $db->prepare("UPDATE coupons SET show_in_header = ? WHERE id = ?")->execute([$newStatus, $id]);
            } catch (Exception $ex2) {}
            $msg = $newStatus === 1 ? '✓ Coupon banner enabled on Top Header!' : '✓ Coupon banner hidden from Top Header.';
        } elseif ($action === 'toggle_active') {
            $id = (int)$_POST['coupon_id'];
            $current = (int)$_POST['current_status'];
            $newStatus = $current === 1 ? 0 : 1;
            try {
                $db->prepare("UPDATE coupons SET is_active = ? WHERE id = ?")->execute([$newStatus, $id]);
            } catch (Exception $ex) {}
            $msg = $newStatus === 1 ? '✓ Coupon activated!' : '✓ Coupon disabled.';
        } elseif ($action === 'delete') {
            $id = (int)$_POST['coupon_id'];
            $db->prepare("DELETE FROM coupons WHERE id = ?")->execute([$id]);
            $msg = '✓ Coupon deleted successfully!';
        }
    }

    try {
        $allProducts = $db->query("SELECT id, name, price, image_path FROM products WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
    } catch (Exception $pe) {
        $allProducts = [];
    }

    try {
        $coupons = $db->query("SELECT c.*, p.name as product_name FROM coupons c LEFT JOIN products p ON c.product_id = p.id ORDER BY c.id DESC")->fetchAll();
    } catch (Exception $ce1) {
        try {
            $coupons = $db->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();
        } catch (Exception $ce2) {
            $coupons = [];
        }
    }

} catch (Exception $e) {
    $error = $e->getMessage();
    $allProducts = [];
    $coupons = [];
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold flex items-center gap-2 mb-6">
    <i class="fas fa-circle-check text-base"></i> <span><?= htmlspecialchars($msg) ?></span>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="p-4 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-400 text-xs font-bold flex items-center gap-2 mb-6">
    <i class="fas fa-circle-exclamation text-base"></i> <span><?= htmlspecialchars($error) ?></span>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: Add / Edit Coupon Form -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-5 h-fit shadow-sm">
        <div class="border-b border-slate-800 pb-3">
            <span class="text-xs font-bold uppercase text-indigo-400">Discount Engine</span>
            <h3 class="text-base font-black text-white mt-0.5" id="formHeaderTitle">Create Discount Coupon</h3>
            <p class="text-xs text-slate-400">Create promo codes for all items or specific products, and manage Top Header announcement banner.</p>
        </div>

        <form method="POST" action="coupons.php" id="couponForm" class="space-y-4 text-xs">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="coupon_id" id="formCouponId" value="">

            <div>
                <label class="block text-slate-300 font-bold mb-1">Coupon Code (কুপন কোড) *</label>
                <div class="flex items-center">
                    <input type="text" name="code" id="cCode" required placeholder="e.g. SPECIAL100, EID2026" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-amber-400 font-mono font-black text-sm outline-none focus:border-indigo-500 uppercase">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Discount Type</label>
                    <select name="discount_type" id="cType" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-bold outline-none focus:border-indigo-500">
                        <option value="fixed">Fixed Amount (৳ টাকা ছাড়)</option>
                        <option value="percent">Percentage (% ছাড়)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Discount Value *</label>
                    <input type="number" step="0.01" name="discount_value" id="cValue" required placeholder="100" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-emerald-400 font-black text-sm outline-none focus:border-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-slate-300 font-bold mb-1">Minimum Order Amount (সর্বনিম্ন অর্ডার ৳)</label>
                <input type="number" step="0.01" name="min_spend" id="cMinSpend" value="0" placeholder="0" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
                <p class="text-[10px] text-slate-500 mt-1">Set 0 for no minimum requirement.</p>
            </div>

            <!-- Product Scope Selector -->
            <div>
                <label class="block text-slate-300 font-bold mb-1">Applicable Product (কোন প্রোডাক্টে কাজ করবে) *</label>
                <select name="product_id" id="cProductId" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-medium outline-none focus:border-indigo-500">
                    <option value="">🌟 All Products (স্টোরের সকল প্রোডাক্টে প্রযোজ্য)</option>
                    <?php foreach ($allProducts as $p): ?>
                    <option value="<?= $p['id'] ?>">📦 Only for: <?= htmlspecialchars($p['name']) ?> (৳<?= number_format($p['price'], 0) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-slate-300 font-bold mb-1">Expiry Date (মেয়াদ শেষ হওয়ার তারিখ)</label>
                <input type="date" name="expiry_date" id="cExpiryDate" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
            </div>

            <!-- Top Header Banner Notice Controls -->
            <div class="p-4 rounded-2xl bg-indigo-950/40 border border-indigo-500/30 space-y-3">
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="show_in_header" id="cShowInHeader" value="1" checked class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <span class="font-extrabold text-white text-xs block">Show on Top Header Announcement Bar</span>
                        <span class="text-[10px] text-indigo-300 block">ওয়েবসাইটের একদম উপরে নোটিশ বারে কুপন অফার শো করান</span>
                    </div>
                </label>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Top Header Banner Text (ব্যানারের লেখা)</label>
                    <input type="text" name="header_banner_text" id="cBannerText" placeholder="e.g. 🎁 Use code SPECIAL100 to get ৳100 OFF on your order!" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs outline-none focus:border-indigo-500">
                </div>
            </div>

            <div class="pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" id="cIsActive" value="1" checked class="w-4 h-4 rounded text-emerald-600">
                    <span class="text-slate-300 font-bold">Active & Usable at Checkout</span>
                </label>
            </div>

            <div class="pt-2 flex items-center gap-2">
                <button type="submit" id="formSubmitBtn" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg transition">
                    + Save Coupon
                </button>
                <button type="button" id="formCancelBtn" onclick="resetCouponForm()" class="hidden px-4 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl text-xs">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    <!-- Right Column: Active Coupons List & Header Banner Toggles -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
                <div>
                    <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                        <i class="fas fa-ticket text-amber-400"></i> Active Discount Coupons (<?= count($coupons) ?>)
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Toggle the <b>"Top Header"</b> switch to display or hide coupon announcement banners on the storefront.</p>
                </div>
            </div>

            <?php if (!empty($coupons)): ?>
            <div class="space-y-3.5">
                <?php foreach ($coupons as $cp): 
                    $isHeader = !empty($cp['show_in_header']);
                    $isActive = !empty($cp['is_active']);
                    $cpJson = htmlspecialchars(json_encode($cp), ENT_QUOTES, 'UTF-8');
                ?>
                <div class="p-4 sm:p-5 rounded-2xl bg-slate-950 border <?= $isActive ? 'border-slate-800' : 'border-slate-800/40 opacity-70' ?> space-y-3 text-xs hover:border-slate-700 transition">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500/20 to-indigo-500/20 border border-amber-500/30 flex items-center justify-center text-amber-400 text-xl font-black shrink-0 shadow-inner">
                                <i class="fas fa-ticket"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-black text-amber-400 text-base font-mono tracking-wider"><?= htmlspecialchars($cp['code']) ?></h4>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black <?= ($cp['discount_type'] ?? 'fixed') === 'percent' ? 'bg-indigo-500/20 text-indigo-300' : 'bg-emerald-500/20 text-emerald-300' ?>">
                                        <?= ($cp['discount_type'] ?? 'fixed') === 'percent' ? $cp['discount_value'] . '% OFF' : '৳' . number_format($cp['discount_value'] ?? 0, 2) . ' OFF' ?>
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-400 mt-0.5">
                                    <span>Min Spend: <strong>৳<?= number_format($cp['min_spend'] ?? 0, 0) ?></strong></span>
                                    <span>•</span>
                                    <span>Scope: <strong class="<?= empty($cp['product_id']) ? 'text-indigo-300' : 'text-cyan-300' ?>"><?= empty($cp['product_id']) ? 'All Products' : htmlspecialchars($cp['product_name'] ?? 'Specific Item') ?></strong></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" onclick='editCoupon(<?= $cpJson ?>)' class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl" title="Edit Coupon">
                                <i class="fas fa-pen-to-square"></i>
                            </button>
                            <form method="POST" action="coupons.php" onsubmit="return confirm('Delete coupon <?= addslashes($cp['code']) ?>?');" class="inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="coupon_id" value="<?= $cp['id'] ?>">
                                <button type="submit" class="p-2 bg-rose-500/20 text-rose-400 hover:bg-rose-500/40 rounded-xl" title="Delete">
                                    <i class="fas fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Header Banner Preview & Toggle Switch -->
                    <div class="pt-2.5 border-t border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="text-[11px] text-slate-400 truncate max-w-md">
                            <span class="text-indigo-400 font-bold">Header Text:</span> <?= htmlspecialchars($cp['header_banner_text'] ?? 'Special Offer Available') ?>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <!-- Toggle Top Header Switch -->
                            <form method="POST" action="coupons.php" class="inline">
                                <input type="hidden" name="action" value="toggle_header">
                                <input type="hidden" name="coupon_id" value="<?= $cp['id'] ?>">
                                <input type="hidden" name="current_status" value="<?= $isHeader ? 1 : 0 ?>">
                                <button type="submit" class="px-3 py-1 rounded-full text-[10px] font-black uppercase flex items-center gap-1.5 <?= $isHeader ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-500 border border-slate-700' ?>">
                                    <i class="fas <?= $isHeader ? 'fa-bullhorn text-emerald-400' : 'fa-eye-slash' ?>"></i>
                                    <span><?= $isHeader ? 'Visible in Header' : 'Hidden in Header' ?></span>
                                </button>
                            </form>

                            <!-- Toggle Active Switch -->
                            <form method="POST" action="coupons.php" class="inline">
                                <input type="hidden" name="action" value="toggle_active">
                                <input type="hidden" name="coupon_id" value="<?= $cp['id'] ?>">
                                <input type="hidden" name="current_status" value="<?= $isActive ? 1 : 0 ?>">
                                <button type="submit" class="px-3 py-1 rounded-full text-[10px] font-black uppercase flex items-center gap-1.5 <?= $isActive ? 'bg-indigo-500/20 text-indigo-300' : 'bg-rose-500/20 text-rose-400' ?>">
                                    <span><?= $isActive ? 'Active' : 'Disabled' ?></span>
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="p-10 text-center bg-slate-950 rounded-2xl border border-slate-800 text-slate-400 space-y-2">
                <i class="fas fa-ticket text-3xl text-slate-600"></i>
                <p class="font-bold text-slate-300">No Coupons Created Yet</p>
                <p class="text-xs">Use the form on the left to create your first discount coupon code!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function editCoupon(cp) {
    document.getElementById('formAction').value = 'update';
    document.getElementById('formCouponId').value = cp.id;
    document.getElementById('cCode').value = cp.code || '';
    document.getElementById('cType').value = cp.discount_type || 'fixed';
    document.getElementById('cValue').value = cp.discount_value || '';
    document.getElementById('cMinSpend').value = cp.min_spend || '0';
    document.getElementById('cProductId').value = cp.product_id || '';
    document.getElementById('cExpiryDate').value = cp.expiry_date || '';
    document.getElementById('cShowInHeader').checked = (cp.show_in_header == 1 || cp.show_in_header === '1' || cp.show_in_header === true);
    document.getElementById('cBannerText').value = cp.header_banner_text || '';
    document.getElementById('cIsActive').checked = (cp.is_active == 1 || cp.is_active === '1' || cp.is_active === true);
    
    document.getElementById('formHeaderTitle').textContent = 'Edit Coupon: ' + cp.code;
    document.getElementById('formSubmitBtn').textContent = '✓ Update Coupon';
    document.getElementById('formCancelBtn').classList.remove('hidden');
    document.getElementById('couponForm').scrollIntoView({ behavior: 'smooth' });
}

function resetCouponForm() {
    document.getElementById('formAction').value = 'create';
    document.getElementById('formCouponId').value = '';
    document.getElementById('cCode').value = '';
    document.getElementById('cType').value = 'fixed';
    document.getElementById('cValue').value = '';
    document.getElementById('cMinSpend').value = '0';
    document.getElementById('cProductId').value = '';
    document.getElementById('cExpiryDate').value = '';
    document.getElementById('cShowInHeader').checked = true;
    document.getElementById('cBannerText').value = '';
    document.getElementById('cIsActive').checked = true;

    document.getElementById('formHeaderTitle').textContent = 'Create Discount Coupon';
    document.getElementById('formSubmitBtn').textContent = '+ Save Coupon';
    document.getElementById('formCancelBtn').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
