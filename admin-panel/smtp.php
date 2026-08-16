<?php
$adminTitle = 'SMTP Mailer & Email Notifications';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../includes/smtp_mailer.php';

$msg = '';
$errorMsg = '';
$testTranscript = '';
$testSuccess = false;

try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'save_smtp') {
            $keys = [
                'smtp_host' => trim($_POST['smtp_host'] ?? ''),
                'smtp_port' => trim($_POST['smtp_port'] ?? '465'),
                'smtp_username' => trim($_POST['smtp_username'] ?? ''),
                'smtp_password' => $_POST['smtp_password'] ?? '',
                'smtp_encryption' => $_POST['smtp_encryption'] ?? 'ssl',
                'smtp_from_address' => trim($_POST['smtp_from_address'] ?? ''),
                'smtp_from_name' => trim($_POST['smtp_from_name'] ?? ''),
                'notify_admin_email' => trim($_POST['notify_admin_email'] ?? ''),
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
            if (!$target || !filter_var($target, FILTER_VALIDATE_EMAIL)) {
                $errorMsg = 'Please provide a valid recipient email address.';
            } else {
                $subject = "🧪 SMTP Test Email from " . (getSetting('store_name') ?: 'OnlineBdMart');
                $htmlBody = "
                <div style='font-family: Arial, sans-serif; max-width: 550px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden;'>
                    <div style='background: linear-gradient(135deg, #0f172a, #4338ca); padding: 25px; text-align: center; color: #ffffff;'>
                        <h2 style='margin: 0; font-size: 22px; font-weight: 800;'>OnlineBdMart SMTP Test</h2>
                        <p style='margin: 5px 0 0 0; font-size: 13px; color: #cbd5e1;'>Socket Mailer Verification</p>
                    </div>
                    <div style='padding: 25px; font-size: 14px; color: #334155; line-height: 1.6;'>
                        <p style='color: #10b981; font-weight: bold; font-size: 16px;'>✓ SMTP Connection Successful!</p>
                        <p>Your mail server settings are working properly. Automated order invoices and courier shipping alerts will now be delivered smoothly to your customers.</p>
                        <div style='background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 10px; font-size: 12px; margin-top: 15px;'>
                            <strong>Sent At:</strong> " . date('d M Y, h:i:s A') . "<br>
                            <strong>Recipient:</strong> {$target}
                        </div>
                    </div>
                    <div style='background: #f1f5f9; padding: 12px; text-align: center; font-size: 11px; color: #64748b;'>
                        Automated Test Message • OnlineBdMart System
                    </div>
                </div>";

                $debugTranscript = '';
                $res = sendSMTPEmail($target, $subject, $htmlBody, $debugTranscript);
                $testTranscript = $debugTranscript;
                if ($res) {
                    $testSuccess = true;
                    $msg = "✓ Test email delivered successfully to {$target}!";
                } else {
                    $errorMsg = "Failed to deliver test email to {$target}. Check the SMTP transcript below for details.";
                }
            }
        }
    }

    $settings = getAllSettings();
} catch (Exception $e) {
    $errorMsg = 'Error: ' . $e->getMessage();
    $settings = [];
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold mb-6 flex items-center gap-2">
    <i class="fas fa-circle-check text-base"></i> <span><?= htmlspecialchars($msg) ?></span>
</div>
<?php endif; ?>

<?php if ($errorMsg): ?>
<div class="p-4 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-300 text-xs font-bold mb-6 flex items-center gap-2">
    <i class="fas fa-triangle-exclamation text-base"></i> <span><?= htmlspecialchars($errorMsg) ?></span>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main SMTP Configuration Form -->
    <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
        <div>
            <span class="text-xs font-bold uppercase text-indigo-400 tracking-wider">Automated Notifications</span>
            <h2 class="text-lg font-black text-white mt-1">SMTP Server & Email Settings</h2>
            <p class="text-xs text-slate-400">Configure your cPanel or custom SMTP mail server to send automated order invoices and delivery tracking emails.</p>
        </div>

        <form method="POST" action="smtp.php" class="space-y-4 text-xs">
            <input type="hidden" name="action" value="save_smtp">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">SMTP Host Server *</label>
                    <input type="text" name="smtp_host" value="<?= htmlspecialchars($settings['smtp_host'] ?? 'mail.onlinebdmart.com') ?>" placeholder="e.g. mail.yourdomain.com or smtp.gmail.com" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500 font-mono">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">SMTP Port *</label>
                    <input type="text" name="smtp_port" value="<?= htmlspecialchars($settings['smtp_port'] ?? '465') ?>" placeholder="465 (SSL) or 587 (TLS)" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">SMTP Username / Email *</label>
                    <input type="text" name="smtp_username" value="<?= htmlspecialchars($settings['smtp_username'] ?? 'info@onlinebdmart.com') ?>" placeholder="e.g. info@yourdomain.com" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">SMTP Password *</label>
                    <input type="password" name="smtp_password" value="<?= htmlspecialchars($settings['smtp_password'] ?? '') ?>" placeholder="Enter email account password" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Encryption Type</label>
                    <select name="smtp_encryption" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
                        <option value="ssl" <?= ($settings['smtp_encryption'] ?? 'ssl') === 'ssl' ? 'selected' : '' ?>>SSL (Port 465)</option>
                        <option value="tls" <?= ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS / STARTTLS (Port 587)</option>
                        <option value="none" <?= ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None (Port 25)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Sender Email Address</label>
                    <input type="email" name="smtp_from_address" value="<?= htmlspecialchars($settings['smtp_from_address'] ?? 'info@onlinebdmart.com') ?>" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Sender Display Name</label>
                    <input type="text" name="smtp_from_name" value="<?= htmlspecialchars($settings['smtp_from_name'] ?? 'OnlineBdMart') ?>" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800 space-y-3">
                <h4 class="font-extrabold text-white text-xs">Automated Notification Triggers</h4>
                
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Admin Alert Recipient Email</label>
                    <input type="email" name="notify_admin_email" value="<?= htmlspecialchars($settings['notify_admin_email'] ?? 'admin@onlinebdmart.com') ?>" placeholder="Receive instant email when a customer places an order" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                    <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-950 border border-slate-800 cursor-pointer">
                        <input type="checkbox" name="notify_order_placed" <?= ($settings['notify_order_placed'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-indigo-600 w-4 h-4">
                        <span class="text-slate-300">Customer Invoice Email</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-950 border border-slate-800 cursor-pointer">
                        <input type="checkbox" name="notify_order_shipped" <?= ($settings['notify_order_shipped'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-indigo-600 w-4 h-4">
                        <span class="text-slate-300">Courier Dispatched Email</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-950 border border-slate-800 cursor-pointer">
                        <input type="checkbox" name="notify_order_delivered" <?= ($settings['notify_order_delivered'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-indigo-600 w-4 h-4">
                        <span class="text-slate-300">Delivered Confirmation</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg transition">Save SMTP Configuration</button>
        </form>
    </div>

    <!-- Test Email Dispatcher & Diagnostics Box -->
    <div class="space-y-6">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-paper-plane text-indigo-400"></i> Live SMTP Test Mailer
            </h3>
            <p class="text-xs text-slate-400">Send an actual live test invoice email to test your host socket connectivity, port, and authentication credentials.</p>

            <form method="POST" action="smtp.php" class="space-y-4 text-xs">
                <input type="hidden" name="action" value="send_test_email">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Target Test Email Address</label>
                    <input type="email" name="test_recipient" required placeholder="yourname@gmail.com" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
                </div>
                <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow transition flex items-center justify-center gap-2">
                    <i class="fas fa-envelope-circle-check"></i> <span>Send Test Email Now</span>
                </button>
            </form>

            <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 text-[11px] text-slate-400 space-y-1.5">
                <p class="font-bold text-slate-300"><i class="fas fa-lightbulb text-amber-400 mr-1"></i> cPanel Mail Settings:</p>
                <p>• <strong>cPanel Host:</strong> <code>mail.yourdomain.com</code></p>
                <p>• <strong>Port:</strong> <code>465</code> with <strong>SSL</strong> encryption</p>
                <p>• <strong>Username:</strong> Your full email address (e.g. <code>info@yourdomain.com</code>)</p>
                <p>• <strong>Gmail:</strong> <code>smtp.gmail.com</code> with Port <code>587 TLS</code> and an <a href="https://myaccount.google.com/apppasswords" target="_blank" class="text-indigo-400 underline">App Password</a>.</p>
            </div>
        </div>

        <?php if ($testTranscript): ?>
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-2">
            <h4 class="text-xs font-bold uppercase text-slate-400">SMTP Debug Log Transcript</h4>
            <pre class="p-3 bg-slate-950 rounded-xl border border-slate-800 text-[10px] font-mono text-slate-300 max-h-48 overflow-y-auto whitespace-pre-wrap leading-relaxed"><?= htmlspecialchars($testTranscript) ?></pre>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
