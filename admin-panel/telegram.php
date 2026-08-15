<?php
$adminTitle = 'Telegram Bot Order Management & Alerts';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../includes/telegram_bot.php';

$msg = '';
$errorMsg = '';
$testOutput = null;
$botInfo = null;
$webhookInfo = null;

try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? 'save';

        if ($action === 'save') {
            $keys = [
                'telegram_bot_token' => trim($_POST['telegram_bot_token'] ?? ''),
                'telegram_chat_id' => trim($_POST['telegram_chat_id'] ?? ''),
                'telegram_alerts_enabled' => isset($_POST['telegram_alerts_enabled']) ? '1' : '0',
            ];

            foreach ($keys as $k => $v) {
                saveSetting($k, $v);
            }
            $msg = 'Telegram settings saved successfully!';
        } elseif ($action === 'test_alert') {
            $testRes = sendTelegramTestNotification();
            if (!empty($testRes['ok'])) {
                $msg = '✓ Live test message sent to Telegram successfully! Check your Telegram app for the sample order notification.';
            } else {
                $errorMsg = 'Failed to send Telegram test message: ' . ($testRes['description'] ?? 'Unknown API error');
            }
        } elseif ($action === 'set_webhook') {
            $siteUrl = rtrim($_POST['custom_site_url'] ?? '', '/');
            if (!$siteUrl) {
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? 'https://' : 'http://';
                $siteUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'onlinebdmart.com');
            }
            $webhookUrl = $siteUrl . '/telegram-webhook.php';
            $res = setTelegramBotWebhook($webhookUrl);
            if (!empty($res['ok'])) {
                $msg = "✓ Telegram Webhook connected successfully to: {$webhookUrl}";
            } else {
                $errorMsg = 'Webhook registration failed: ' . ($res['description'] ?? 'API error');
            }
        } elseif ($action === 'delete_webhook') {
            $res = deleteTelegramBotWebhook();
            if (!empty($res['ok'])) {
                $msg = '✓ Telegram Webhook disconnected successfully.';
            } else {
                $errorMsg = 'Failed to delete webhook: ' . ($res['description'] ?? 'API error');
            }
        }
    }

    $settings = getAllSettings();
    $token = trim($settings['telegram_bot_token'] ?? '');
    $chatId = trim($settings['telegram_chat_id'] ?? '');

    if ($token) {
        $botInfo = getTelegramBotMe($token);
        $webhookInfo = getTelegramBotWebhookInfo($token);
    }
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
    
    <!-- Main Telegram Bot Configuration -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div>
                    <span class="text-xs font-bold uppercase text-sky-400 tracking-wider">Automated Store Alerts</span>
                    <h2 class="text-lg font-black text-white mt-1">Telegram Bot & Order Management</h2>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-sky-500/20 text-sky-400 flex items-center justify-center text-2xl">
                    <i class="fab fa-telegram"></i>
                </div>
            </div>

            <form method="POST" action="telegram.php" class="space-y-4 text-xs">
                <input type="hidden" name="action" value="save">

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Telegram Bot Token (বট টোকেন) *</label>
                    <input type="text" name="telegram_bot_token" value="<?= htmlspecialchars($settings['telegram_bot_token'] ?? '') ?>" placeholder="e.g. 7123456789:AAHk3_xyz987AbcDefGhIjKlMnOpQrStUv" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none focus:border-sky-500">
                    <p class="text-[11px] text-slate-400 mt-1">Get your Bot Token from <a href="https://t.me/BotFather" target="_blank" class="text-sky-400 underline font-bold">@BotFather</a> on Telegram.</p>
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Telegram Chat ID / Admin ID (চ্যাট আইডি) *</label>
                    <input type="text" name="telegram_chat_id" value="<?= htmlspecialchars($settings['telegram_chat_id'] ?? '') ?>" placeholder="e.g. 123456789 or -100123456789 for group" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none focus:border-sky-500">
                    <p class="text-[11px] text-slate-400 mt-1">Get your numerical User ID from <a href="https://t.me/userinfobot" target="_blank" class="text-sky-400 underline font-bold">@userinfobot</a> on Telegram.</p>
                </div>

                <div class="pt-3 border-t border-slate-800 space-y-2">
                    <label class="flex items-center gap-2 p-3 bg-slate-950 border border-slate-800 rounded-xl cursor-pointer">
                        <input type="checkbox" name="telegram_alerts_enabled" <?= ($settings['telegram_alerts_enabled'] ?? '0') === '1' ? 'checked' : '' ?> class="rounded text-sky-500 w-4 h-4">
                        <span class="text-white font-bold">Enable Instant Telegram Alerts for Every New Order</span>
                    </label>
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <button type="submit" class="px-7 py-3 bg-sky-600 hover:bg-sky-500 text-white font-extrabold rounded-xl shadow-lg transition flex items-center gap-2">
                        <i class="fas fa-floppy-disk"></i> <span>Save Telegram Settings</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Live Actions & Webhook Management -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
            <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-network-wired text-sky-400"></i> Bot Webhook & Interactive Buttons
            </h3>
            <p class="text-xs text-slate-400 leading-relaxed">
                Connect the webhook so when you click <b>[✅ Confirm Order]</b>, <b>[🚚 Mark Shipped]</b>, or <b>[❌ Cancel Order]</b> inside Telegram, the order updates in your store database instantly!
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Test Dispatch Form -->
                <form method="POST" action="telegram.php" class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                    <input type="hidden" name="action" value="test_alert">
                    <div class="flex items-center gap-2 text-sky-400 text-xs font-bold">
                        <i class="fas fa-paper-plane"></i> <span>1. Test Connection</span>
                    </div>
                    <p class="text-[11px] text-slate-400">Sends a full test order notification to your Telegram with sample customer and interactive buttons.</p>
                    <button type="submit" class="w-full py-2.5 bg-sky-600/80 hover:bg-sky-600 text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5">
                        <i class="fas fa-bell"></i> <span>Send Test Notification</span>
                    </button>
                </form>

                <!-- Set Webhook Form -->
                <form method="POST" action="telegram.php" class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                    <input type="hidden" name="action" value="set_webhook">
                    <div class="flex items-center gap-2 text-emerald-400 text-xs font-bold">
                        <i class="fas fa-link"></i> <span>2. Connect Webhook</span>
                    </div>
                    <p class="text-[11px] text-slate-400">Enables Telegram inline action buttons and bot commands (<code>/orders</code>, <code>/confirm</code>, <code>/cancel</code>).</p>
                    <button type="submit" class="w-full py-2.5 bg-emerald-600/80 hover:bg-emerald-600 text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5">
                        <i class="fas fa-plug"></i> <span>Connect Webhook Now</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Sidebar: Diagnostics & Guide -->
    <div class="space-y-6">
        
        <!-- Live Diagnostic Status Box -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4 text-xs">
            <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-circle-nodes text-indigo-400"></i> Bot Connection Status
            </h3>

            <?php if (!empty($botInfo['ok'])): ?>
            <div class="p-4 rounded-2xl bg-emerald-950/40 border border-emerald-500/30 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-emerald-400 font-bold">● Telegram Bot Connected</span>
                    <span class="text-[10px] bg-emerald-500/20 text-emerald-300 px-2 py-0.5 rounded-full font-bold">ONLINE</span>
                </div>
                <div class="text-slate-300 space-y-1 text-[11px]">
                    <p><strong>Bot Name:</strong> <?= htmlspecialchars($botInfo['result']['first_name'] ?? '') ?></p>
                    <p><strong>Bot Username:</strong> <a href="https://t.me/<?= htmlspecialchars($botInfo['result']['username'] ?? '') ?>" target="_blank" class="text-sky-400 underline font-mono">@<?= htmlspecialchars($botInfo['result']['username'] ?? '') ?></a></p>
                    <p><strong>Bot ID:</strong> <code class="text-slate-400"><?= $botInfo['result']['id'] ?? '' ?></code></p>
                </div>
            </div>
            <?php else: ?>
            <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 text-slate-400 space-y-1">
                <span class="text-amber-400 font-bold block"><i class="fas fa-circle-exclamation mr-1"></i> Bot Not Connected</span>
                <p class="text-[11px]">Please enter your Telegram Bot Token and Chat ID to verify connectivity.</p>
            </div>
            <?php endif; ?>

            <?php if (!empty($webhookInfo['ok']) && !empty($webhookInfo['result']['url'])): ?>
            <div class="p-4 rounded-2xl bg-indigo-950/40 border border-indigo-500/30 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-indigo-300 font-bold">Webhook Registered</span>
                    <span class="text-[10px] bg-indigo-500/20 text-indigo-200 px-2 py-0.5 rounded-full font-bold">ACTIVE</span>
                </div>
                <p class="text-[10px] text-slate-400 font-mono break-all"><?= htmlspecialchars($webhookInfo['result']['url']) ?></p>
                <form method="POST" action="telegram.php" class="pt-1">
                    <input type="hidden" name="action" value="delete_webhook">
                    <button type="submit" class="text-[11px] text-rose-400 hover:underline"><i class="fas fa-trash text-[10px] mr-1"></i> Disconnect Webhook</button>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <!-- 3-Step Setup Tutorial -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4 text-xs text-slate-300">
            <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-circle-question text-sky-400"></i> Easy 3-Step Setup Guide
            </h3>

            <div class="space-y-3 text-[11px] leading-relaxed">
                <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800 space-y-1">
                    <span class="font-bold text-sky-400">১. টেলিগ্রাম বট তৈরি করুন:</span>
                    <p>টেলিগ্রামে <a href="https://t.me/BotFather" target="_blank" class="text-sky-400 underline font-bold">@BotFather</a> সার্চ করুন, <code>/newbot</code> লিখে সেন্ড করুন এবং বটের নাম দিন। শেষে আপনি একটি <b>HTTP API Token</b> পাবেন।</p>
                </div>

                <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800 space-y-1">
                    <span class="font-bold text-sky-400">২. আপনার Chat ID সংগ্রহ করুন:</span>
                    <p>টেলিগ্রামে <a href="https://t.me/userinfobot" target="_blank" class="text-sky-400 underline font-bold">@userinfobot</a> এ <code>/start</code> সেন্ড করুন। আপনার সংখ্যাযুক্ত <b>Id</b> (যেমন: <code>123456789</code>) কপি করুন।</p>
                </div>

                <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800 space-y-1">
                    <span class="font-bold text-sky-400">৩. বট চালু ও টেস্ট করুন:</span>
                    <p>টোকেন ও চ্যাট আইডি পেস্ট করে <b>Save</b> করুন, এরপর <b>Send Test Notification</b> বাটনে চাপ দিন এবং আপনার বট ওপেন করে <code>/start</code> লিখুন!</p>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
