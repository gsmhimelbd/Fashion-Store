<?php
$adminTitle = 'OTP & SMS Gateway';
require_once __DIR__ . '/header.php';

$msg = '';
try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $keys = [
            'otp_verification_enabled' => isset($_POST['otp_verification_enabled']) ? '1' : '0',
            'sms_gateway_provider' => $_POST['sms_gateway_provider'] ?? 'greenweb',
            'sms_api_key' => $_POST['sms_api_key'] ?? '',
            'sms_sender_id' => $_POST['sms_sender_id'] ?? 'OnlineBdMart',
        ];

        foreach ($keys as $k => $v) {
            saveSetting($k, $v);
        }
        $msg = 'OTP & SMS Gateway settings saved!';
    }

    $settings = getAllSettings();
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
        <span class="text-xs font-bold uppercase text-amber-400">Order Verification</span>
        <h2 class="text-lg font-black text-white mt-1">SMS Gateway & OTP Phone Verification</h2>
        <p class="text-xs text-slate-400">Send 4-digit SMS OTP to customer mobile numbers to prevent fake COD orders.</p>
    </div>

    <form method="POST" action="otp-system.php" class="space-y-4 text-xs">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-slate-300 font-bold mb-1">SMS Gateway Provider</label>
                <select name="sms_gateway_provider" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    <option value="greenweb" <?= ($settings['sms_gateway_provider'] ?? '') === 'greenweb' ? 'selected' : '' ?>>Greenweb BD SMS</option>
                    <option value="reve" <?= ($settings['sms_gateway_provider'] ?? '') === 'reve' ? 'selected' : '' ?>>Reve SMS Gateway</option>
                    <option value="mim" <?= ($settings['sms_gateway_provider'] ?? '') === 'mim' ? 'selected' : '' ?>>MimSMS</option>
                    <option value="bulksmsbd" <?= ($settings['sms_gateway_provider'] ?? '') === 'bulksmsbd' ? 'selected' : '' ?>>BulkSMS BD</option>
                </select>
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">API Key / Token</label>
                <input type="password" name="sms_api_key" value="<?= htmlspecialchars($settings['sms_api_key'] ?? '') ?>" placeholder="••••••••••••••••" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Approved Sender Masking ID</label>
                <input type="text" name="sms_sender_id" value="<?= htmlspecialchars($settings['sms_sender_id'] ?? 'OnlineBdMart') ?>" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
        </div>

        <div class="pt-2">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="otp_verification_enabled" <?= ($settings['otp_verification_enabled'] ?? '0') === '1' ? 'checked' : '' ?> class="rounded text-amber-500">
                <span class="text-amber-400 font-bold">Require 4-digit SMS OTP verification before checkout confirmation</span>
            </label>
        </div>

        <button type="submit" class="px-8 py-3 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black rounded-xl shadow-lg transition">Save OTP Settings</button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
