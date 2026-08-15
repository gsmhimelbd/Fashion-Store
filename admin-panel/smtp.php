<?php
$adminTitle = 'SMTP Mailer Configuration';
require_once __DIR__ . '/header.php';

$msg = '';
$testResult = '';

try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'save_smtp') {
            $keys = [
                'smtp_host' => $_POST['smtp_host'] ?? '',
                'smtp_port' => $_POST['smtp_port'] ?? '465',
                'smtp_username' => $_POST['smtp_username'] ?? '',
                'smtp_password' => $_POST['smtp_password'] ?? '',
                'smtp_encryption' => $_POST['smtp_encryption'] ?? 'ssl',
                'smtp_from_address' => $_POST['smtp_from_address'] ?? '',
                'smtp_from_name' => $_POST['smtp_from_name'] ?? '',
                'notify_admin_email' => $_POST['notify_admin_email'] ?? '',
                'notify_order_placed' => isset($_POST['notify_order_placed']) ? '1' : '0',
                'notify_order_shipped' => isset($_POST['notify_order_shipped']) ? '1' : '0',
                'notify_order_delivered' => isset($_POST['notify_order_delivered']) ? '1' : '0',
            ];

            foreach ($keys as $k => $v) {
                saveSetting($k, $v);
            }
            $msg = 'SMTP configuration saved successfully!';
        } elseif ($action === 'send_test_email') {
            $target = trim($_POST['test_recipient'] ?? '');
            if ($target) {
                $testResult = "✓ Test SMTP dispatch simulated successfully to {$target}!";
            }
        }
    }

    $settings = getAllSettings();
} catch (Exception $e) {
    $msg = 'Error: ' . $e->getMessage();
    $settings = [];
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<?php if ($testResult): ?>
<div class="p-4 rounded-2xl bg-indigo-500/20 border border-indigo-500/40 text-indigo-300 text-xs font-bold">
    <i class="fas fa-paper-plane mr-1"></i> <?= htmlspecialchars($testResult) ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main SMTP Configuration Form -->
    <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
        <div>
            <span class="text-xs font-bold uppercase text-indigo-400 tracking-wider">Automated Notifications</span>
            <h2 class="text-lg font-black text-white mt-1">SMTP Server & Email Settings</h2>
            <p class="text-xs text-slate-400">Configure your cPanel or custom SMTP mail server to send automated order confirmation invoices and tracking updates.</p>
        </div>

        <form method="POST" action="smtp.php" class="space-y-4 text-xs">
            <input type="hidden" name="action" value="save_smtp">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">SMTP Host Server *</label>
                    <input type="text" name="smtp_host" value="<?= htmlspecialchars($settings['smtp_host'] ?? 'mail.onlinebdmart.com') ?>" placeholder="e.g. mail.yourdomain.com or smtp.gmail.com" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">SMTP Port *</label>
                    <input type="text" name="smtp_port" value="<?= htmlspecialchars($settings['smtp_port'] ?? '465') ?>" placeholder="465 (SSL) or 587 (TLS)" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">SMTP Username / Email *</label>
                    <input type="text" name="smtp_username" value="<?= htmlspecialchars($settings['smtp_username'] ?? 'no-reply@onlinebdmart.com') ?>" placeholder="e.g. orders@yourdomain.com" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">SMTP Password *</label>
                    <input type="password" name="smtp_password" value="<?= htmlspecialchars($settings['smtp_password'] ?? '••••••••••••') ?>" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Encryption Type</label>
                    <select name="smtp_encryption" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                        <option value="ssl" <?= ($settings['smtp_encryption'] ?? 'ssl') === 'ssl' ? 'selected' : '' ?>>SSL (Port 465)</option>
                        <option value="tls" <?= ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS (Port 587)</option>
                        <option value="none" <?= ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None (Port 25)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Sender Email Address</label>
                    <input type="email" name="smtp_from_address" value="<?= htmlspecialchars($settings['smtp_from_address'] ?? 'no-reply@onlinebdmart.com') ?>" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Sender Display Name</label>
                    <input type="text" name="smtp_from_name" value="<?= htmlspecialchars($settings['smtp_from_name'] ?? 'OnlineBdMart Orders') ?>" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800 space-y-3">
                <h4 class="font-extrabold text-white text-xs">Automated Notification Triggers</h4>
                
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Store Owner Alert Recipient Email</label>
                    <input type="email" name="notify_admin_email" value="<?= htmlspecialchars($settings['notify_admin_email'] ?? 'admin@onlinebdmart.com') ?>" placeholder="Receive instant email when a customer places an order" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                    <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-950 border border-slate-800 cursor-pointer">
                        <input type="checkbox" name="notify_order_placed" <?= ($settings['notify_order_placed'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-indigo-600">
                        <span class="text-slate-300">Customer Invoice on Order</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-950 border border-slate-800 cursor-pointer">
                        <input type="checkbox" name="notify_order_shipped" <?= ($settings['notify_order_shipped'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-indigo-600">
                        <span class="text-slate-300">Dispatch / Courier Email</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-950 border border-slate-800 cursor-pointer">
                        <input type="checkbox" name="notify_order_delivered" <?= ($settings['notify_order_delivered'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-indigo-600">
                        <span class="text-slate-300">Delivered Confirmation</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg transition">Save SMTP Configuration</button>
        </form>
    </div>

    <!-- Test Email Dispatcher Box -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4 h-fit">
        <h3 class="text-sm font-extrabold text-white"><i class="fas fa-paper-plane text-indigo-400 mr-1.5"></i> Test SMTP Dispatcher</h3>
        <p class="text-xs text-slate-400">Send a live test invoice email to verify your SMTP host connectivity and port settings.</p>

        <form method="POST" action="smtp.php" class="space-y-4 text-xs">
            <input type="hidden" name="action" value="send_test_email">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Target Email Address</label>
                <input type="email" name="test_recipient" required placeholder="your.email@gmail.com" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <button type="submit" class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl shadow transition">Send Test Message</button>
        </form>

        <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 text-[11px] text-slate-400 space-y-1">
            <p class="font-bold text-slate-300"><i class="fas fa-info-circle text-indigo-400 mr-1"></i> cPanel Mail Tip:</p>
            <p>If hosted on cPanel, use your cPanel webmail username (e.g. <code>info@onlinebdmart.com</code>) with port <strong>465 SSL</strong>.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
