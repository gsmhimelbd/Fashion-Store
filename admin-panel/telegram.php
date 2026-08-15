<?php
$adminTitle = 'Telegram Bot Order Alerts';
require_once __DIR__ . '/header.php';

$msg = '';
try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $keys = [
            'telegram_bot_token' => $_POST['telegram_bot_token'] ?? '',
            'telegram_chat_id' => $_POST['telegram_chat_id'] ?? '',
            'telegram_alerts_enabled' => isset($_POST['telegram_alerts_enabled']) ? '1' : '0',
        ];

        $upsert = $db->prepare("INSERT INTO settings (`key`, `value`, `updated_at`) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW()");
        foreach ($keys as $k => $v) {
            $upsert->execute([$k, $v]);
        }
        $msg = 'Telegram settings saved!';
    }

    $settings = $db->query("SELECT `key`, `value` FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
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
        <span class="text-xs font-bold uppercase text-sky-400">Instant Admin Notifications</span>
        <h2 class="text-lg font-black text-white mt-1">Telegram Bot Order Notification</h2>
        <p class="text-xs text-slate-400">Receive instant push notifications on your Telegram app the exact second a customer places a COD order.</p>
    </div>

    <form method="POST" action="telegram.php" class="space-y-4 text-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Telegram Bot Token</label>
                <input type="text" name="telegram_bot_token" value="<?= htmlspecialchars($settings['telegram_bot_token'] ?? '') ?>" placeholder="e.g. 123456789:ABCDefghIJKlmnoPQRstuv" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Telegram Chat ID / Group ID</label>
                <input type="text" name="telegram_chat_id" value="<?= htmlspecialchars($settings['telegram_chat_id'] ?? '') ?>" placeholder="e.g. 987654321 or -100123456789" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none">
            </div>
        </div>

        <div class="pt-2">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="telegram_alerts_enabled" <?= ($settings['telegram_alerts_enabled'] ?? '0') === '1' ? 'checked' : '' ?> class="rounded text-sky-500">
                <span class="text-sky-400 font-bold">Enable Telegram Realtime Alerts on New Order</span>
            </label>
        </div>

        <button type="submit" class="px-8 py-3 bg-sky-600 hover:bg-sky-500 text-white font-extrabold rounded-xl shadow-lg transition">Save Telegram Bot Settings</button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
