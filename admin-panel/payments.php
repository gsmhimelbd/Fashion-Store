<?php
$adminTitle = 'Payment Gateways & Methods';
require_once __DIR__ . '/header.php';

$msg = '';
try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $keys = [
            'payment_cod_enabled' => isset($_POST['payment_cod_enabled']) ? '1' : '0',
            
            // bKash
            'payment_bkash_enabled' => isset($_POST['payment_bkash_enabled']) ? '1' : '0',
            'payment_bkash_number' => $_POST['payment_bkash_number'] ?? '01775153740',
            'payment_bkash_type' => $_POST['payment_bkash_type'] ?? 'merchant',
            'payment_bkash_instructions' => $_POST['payment_bkash_instructions'] ?? 'bKash App এ গিয়ে "Make Payment" অপশন সিলেক্ট করে নাম্বারে টাকা পাঠান এবং TrxID দিন।',

            // Nagad
            'payment_nagad_enabled' => isset($_POST['payment_nagad_enabled']) ? '1' : '0',
            'payment_nagad_number' => $_POST['payment_nagad_number'] ?? '01775153740',
            'payment_nagad_instructions' => $_POST['payment_nagad_instructions'] ?? 'নগদ একাউন্ট থেকে "Send Money" করে TrxID নিচে প্রদান করুন।',

            // Rocket
            'payment_rocket_enabled' => isset($_POST['payment_rocket_enabled']) ? '1' : '0',
            'payment_rocket_number' => $_POST['payment_rocket_number'] ?? '01775153740',
            'payment_rocket_instructions' => $_POST['payment_rocket_instructions'] ?? 'রকেট একাউন্ট থেকে টাকা পাঠিয়ে TrxID প্রদান করুন।',

            // Manual Bank Payment
            'payment_bank_enabled' => isset($_POST['payment_bank_enabled']) ? '1' : '0',
            'payment_bank_name' => $_POST['payment_bank_name'] ?? 'Islami Bank Bangladesh Ltd',
            'payment_bank_acc_name' => $_POST['payment_bank_acc_name'] ?? 'OnlineBdMart Enterprise',
            'payment_bank_acc_no' => $_POST['payment_bank_acc_no'] ?? '2050123456789012',
            'payment_bank_branch' => $_POST['payment_bank_branch'] ?? 'Tangail Branch',
            'payment_bank_routing' => $_POST['payment_bank_routing'] ?? '125272648',
            'payment_bank_instructions' => $_POST['payment_bank_instructions'] ?? 'ব্যাংকে টাকা ডিপোজিট করে ডিপোজিট স্লিপ নম্বর বা ট্রানজেকশন রেফারেন্স প্রদান করুন।',

            // SSLCommerz
            'payment_ssl_enabled' => isset($_POST['payment_ssl_enabled']) ? '1' : '0',
            'payment_ssl_store_id' => $_POST['payment_ssl_store_id'] ?? '',
            'payment_ssl_store_passwd' => $_POST['payment_ssl_store_passwd'] ?? '',
            'payment_ssl_sandbox' => isset($_POST['payment_ssl_sandbox']) ? '1' : '0',
        ];

        foreach ($keys as $k => $v) {
            saveSetting($k, $v);
        }
        $msg = 'All payment methods updated successfully!';
    }

    $settings = getAllSettings();
} catch (Exception $e) {
    $settings = [];
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1.5"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
    <div>
        <span class="text-xs font-bold uppercase text-indigo-400">Checkout Gateways</span>
        <h2 class="text-lg font-black text-white mt-1">Payment Systems & Account Controls</h2>
        <p class="text-xs text-slate-400">Configure Cash on Delivery, bKash Merchant/Personal, Nagad, Rocket, Manual Bank Wire, and SSLCommerz Automated Gateway.</p>
    </div>

    <form method="POST" action="payments.php" class="space-y-6 text-xs">
        
        <!-- 1. Cash On Delivery -->
        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600/20 text-emerald-400 flex items-center justify-center text-lg"><i class="fas fa-hand-holding-dollar"></i></div>
                    <div>
                        <h3 class="font-extrabold text-white text-sm">Cash on Delivery (COD)</h3>
                        <p class="text-slate-400 text-[11px]">Allow customer to pay cash to delivery rider at their doorstep.</p>
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="payment_cod_enabled" <?= ($settings['payment_cod_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-emerald-500">
                    <span class="text-emerald-400 font-bold">Active</span>
                </label>
            </div>
        </div>

        <!-- 2. bKash Merchant & Personal -->
        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-pink-600/20 text-pink-400 flex items-center justify-center text-lg font-black">bK</div>
                    <div>
                        <h3 class="font-extrabold text-pink-400 text-sm">bKash Payment (Merchant / Personal)</h3>
                        <p class="text-slate-400 text-[11px]">Customers send money/payment and provide TrxID at checkout.</p>
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="payment_bkash_enabled" <?= ($settings['payment_bkash_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-pink-500">
                    <span class="text-pink-400 font-bold">Active</span>
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">bKash Account Number</label>
                    <input type="text" name="payment_bkash_number" value="<?= htmlspecialchars($settings['payment_bkash_number'] ?? '01775153740') ?>" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono outline-none">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Account Type</label>
                    <select name="payment_bkash_type" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white outline-none">
                        <option value="merchant" <?= ($settings['payment_bkash_type'] ?? 'merchant') === 'merchant' ? 'selected' : '' ?>>Merchant (Make Payment)</option>
                        <option value="personal" <?= ($settings['payment_bkash_type'] ?? '') === 'personal' ? 'selected' : '' ?>>Personal (Send Money)</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Customer Payment Instructions</label>
                <input type="text" name="payment_bkash_instructions" value="<?= htmlspecialchars($settings['payment_bkash_instructions'] ?? 'bKash App এ গিয়ে "Make Payment" অপশন সিলেক্ট করে নাম্বারে টাকা পাঠান এবং TrxID দিন।') ?>" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white outline-none">
            </div>
        </div>

        <!-- 3. Nagad & Rocket Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nagad -->
            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-extrabold text-orange-400 text-sm">Nagad Manual</h3>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="payment_nagad_enabled" <?= ($settings['payment_nagad_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-orange-500">
                        <span class="text-orange-400 font-bold">Active</span>
                    </label>
                </div>
                <div>
                    <label class="block text-slate-400 text-[11px] mb-1">Nagad Number</label>
                    <input type="text" name="payment_nagad_number" value="<?= htmlspecialchars($settings['payment_nagad_number'] ?? '01775153740') ?>" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono outline-none">
                </div>
            </div>

            <!-- Rocket -->
            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-extrabold text-purple-400 text-sm">Rocket (DBBL) Manual</h3>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="payment_rocket_enabled" <?= ($settings['payment_rocket_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-purple-500">
                        <span class="text-purple-400 font-bold">Active</span>
                    </label>
                </div>
                <div>
                    <label class="block text-slate-400 text-[11px] mb-1">Rocket Number</label>
                    <input type="text" name="payment_rocket_number" value="<?= htmlspecialchars($settings['payment_rocket_number'] ?? '01775153740') ?>" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono outline-none">
                </div>
            </div>
        </div>

        <!-- 4. Manual Bank Deposit -->
        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-cyan-600/20 text-cyan-400 flex items-center justify-center text-lg"><i class="fas fa-building-columns"></i></div>
                    <div>
                        <h3 class="font-extrabold text-cyan-400 text-sm">Manual Bank Transfer / Wire Payment</h3>
                        <p class="text-slate-400 text-[11px]">For wholesale buyers and corporate clients to deposit directly to your bank account.</p>
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="payment_bank_enabled" <?= ($settings['payment_bank_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-cyan-500">
                    <span class="text-cyan-400 font-bold">Active</span>
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Bank Name</label>
                    <input type="text" name="payment_bank_name" value="<?= htmlspecialchars($settings['payment_bank_name'] ?? 'Islami Bank Bangladesh Ltd') ?>" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white outline-none">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Account Title / Name</label>
                    <input type="text" name="payment_bank_acc_name" value="<?= htmlspecialchars($settings['payment_bank_acc_name'] ?? 'OnlineBdMart Enterprise') ?>" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white outline-none">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Account Number</label>
                    <input type="text" name="payment_bank_acc_no" value="<?= htmlspecialchars($settings['payment_bank_acc_no'] ?? '2050123456789012') ?>" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Branch Name</label>
                    <input type="text" name="payment_bank_branch" value="<?= htmlspecialchars($settings['payment_bank_branch'] ?? 'Tangail Branch') ?>" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white outline-none">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Routing Number</label>
                    <input type="text" name="payment_bank_routing" value="<?= htmlspecialchars($settings['payment_bank_routing'] ?? '125272648') ?>" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono outline-none">
                </div>
            </div>
        </div>

        <!-- 5. Bangladesh SSLCommerz Payment Gateway -->
        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600/20 text-blue-400 flex items-center justify-center text-lg"><i class="fas fa-credit-card"></i></div>
                    <div>
                        <h3 class="font-extrabold text-blue-400 text-sm">SSLCommerz Automated Payment Gateway</h3>
                        <p class="text-slate-400 text-[11px]">Accept Visa, MasterCard, bKash, Nagad, Rocket, and Internet Banking online.</p>
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="payment_ssl_enabled" <?= ($settings['payment_ssl_enabled'] ?? '0') === '1' ? 'checked' : '' ?> class="rounded text-blue-500">
                    <span class="text-blue-400 font-bold">Active</span>
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">SSLCommerz Store ID</label>
                    <input type="text" name="payment_ssl_store_id" value="<?= htmlspecialchars($settings['payment_ssl_store_id'] ?? '') ?>" placeholder="e.g. onlinebdmart_live" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono outline-none">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">SSLCommerz Store Password / Secret Key</label>
                    <input type="password" name="payment_ssl_store_passwd" value="<?= htmlspecialchars($settings['payment_ssl_store_passwd'] ?? '') ?>" placeholder="••••••••••••••••" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono outline-none">
                </div>
            </div>

            <div class="pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="payment_ssl_sandbox" <?= ($settings['payment_ssl_sandbox'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-blue-500">
                    <span class="text-slate-300 font-bold">Sandbox / Test Mode (Uncheck when live in production)</span>
                </label>
            </div>
        </div>

        <button type="submit" class="px-8 py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg transition">
            Save All Payment Settings
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
