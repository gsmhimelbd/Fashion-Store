<?php
$adminTitle = 'Payment Gateways & Settings';
require_once __DIR__ . '/header.php';

$msg = '';
try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $keys = [
            'payment_cod_enabled' => isset($_POST['payment_cod_enabled']) ? '1' : '0',
            'payment_bkash_enabled' => isset($_POST['payment_bkash_enabled']) ? '1' : '0',
            'payment_bkash_number' => $_POST['payment_bkash_number'] ?? '01712-345678',
            'payment_nagad_enabled' => isset($_POST['payment_nagad_enabled']) ? '1' : '0',
            'payment_nagad_number' => $_POST['payment_nagad_number'] ?? '01712-345678',
            'payment_rocket_enabled' => isset($_POST['payment_rocket_enabled']) ? '1' : '0',
            'payment_rocket_number' => $_POST['payment_rocket_number'] ?? '01712-345678',
        ];

        $upsert = $db->prepare("INSERT INTO settings (`key`, `value`, `updated_at`) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW()");
        foreach ($keys as $k => $v) {
            $upsert->execute([$k, $v]);
        }
        $msg = 'Payment settings saved!';
    }

    $settingsStmt = $db->query("SELECT `key`, `value` FROM settings");
    $settings = $settingsStmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $e) {
    $settings = [];
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
    <div>
        <span class="text-xs font-bold uppercase text-indigo-400">Payment Accounts</span>
        <h2 class="text-lg font-black text-white mt-1">Configure Bangladesh Payment Methods</h2>
        <p class="text-xs text-slate-400">Enable Cash on Delivery and Mobile Financial Services (bKash, Nagad, Rocket) with your personal or merchant account numbers.</p>
    </div>

    <form method="POST" action="payments.php" class="space-y-6 text-xs">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Cash on Delivery -->
            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-extrabold text-white text-sm">Cash on Delivery (COD)</span>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="payment_cod_enabled" <?= ($settings['payment_cod_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-emerald-500">
                        <span class="text-emerald-400 font-bold">Active</span>
                    </label>
                </div>
                <p class="text-slate-400 text-[11px]">Customers pay cash to the courier delivery rider upon receiving parcel.</p>
            </div>

            <!-- bKash -->
            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-extrabold text-pink-400 text-sm">bKash Personal / Merchant</span>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="payment_bkash_enabled" <?= ($settings['payment_bkash_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-pink-500">
                        <span class="text-pink-400 font-bold">Active</span>
                    </label>
                </div>
                <div>
                    <label class="block text-slate-400 text-[11px] mb-1">bKash Account Number</label>
                    <input type="text" name="payment_bkash_number" value="<?= htmlspecialchars($settings['payment_bkash_number'] ?? '01712-345678') ?>" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white outline-none font-mono">
                </div>
            </div>

            <!-- Nagad -->
            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-extrabold text-orange-400 text-sm">Nagad Personal</span>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="payment_nagad_enabled" <?= ($settings['payment_nagad_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-orange-500">
                        <span class="text-orange-400 font-bold">Active</span>
                    </label>
                </div>
                <div>
                    <label class="block text-slate-400 text-[11px] mb-1">Nagad Account Number</label>
                    <input type="text" name="payment_nagad_number" value="<?= htmlspecialchars($settings['payment_nagad_number'] ?? '01712-345678') ?>" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white outline-none font-mono">
                </div>
            </div>

            <!-- Rocket -->
            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-extrabold text-purple-400 text-sm">Rocket (DBBL)</span>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="payment_rocket_enabled" <?= ($settings['payment_rocket_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-purple-500">
                        <span class="text-purple-400 font-bold">Active</span>
                    </label>
                </div>
                <div>
                    <label class="block text-slate-400 text-[11px] mb-1">Rocket Account Number</label>
                    <input type="text" name="payment_rocket_number" value="<?= htmlspecialchars($settings['payment_rocket_number'] ?? '01712-345678') ?>" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white outline-none font-mono">
                </div>
            </div>
        </div>

        <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg transition">Save Payment Settings</button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
