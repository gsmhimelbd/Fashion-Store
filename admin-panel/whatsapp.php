<?php
$adminTitle = 'WhatsApp Integration Settings';
require_once __DIR__ . '/header.php';

$msg = '';
try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $keys = [
            'whatsapp_phone' => $_POST['whatsapp_phone'] ?? '01712345678',
            'whatsapp_floating_enabled' => isset($_POST['whatsapp_floating_enabled']) ? '1' : '0',
            'whatsapp_default_message' => $_POST['whatsapp_default_message'] ?? 'Hello OnlineBdMart, I need help with an order.',
            'whatsapp_b2b_phone' => $_POST['whatsapp_b2b_phone'] ?? '01712345678',
        ];

        $upsert = $db->prepare("INSERT INTO settings (`key`, `value`, `updated_at`) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW()");
        foreach ($keys as $k => $v) {
            $upsert->execute([$k, $v]);
        }
        $msg = 'WhatsApp settings saved!';
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
        <span class="text-xs font-bold uppercase text-emerald-400">Direct Chat & Orders</span>
        <h2 class="text-lg font-black text-white mt-1">WhatsApp Support & B2B Hotline</h2>
        <p class="text-xs text-slate-400">Configure floating chat widget numbers and WhatsApp 1-click ordering across all product pages.</p>
    </div>

    <form method="POST" action="whatsapp.php" class="space-y-4 text-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Customer Support WhatsApp Number</label>
                <input type="text" name="whatsapp_phone" value="<?= htmlspecialchars($settings['whatsapp_phone'] ?? '01712345678') ?>" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">B2B / Wholesale WhatsApp Number</label>
                <input type="text" name="whatsapp_b2b_phone" value="<?= htmlspecialchars($settings['whatsapp_b2b_phone'] ?? '01712345678') ?>" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
        </div>

        <div>
            <label class="block text-slate-300 font-bold mb-1">Default Pre-filled Message</label>
            <input type="text" name="whatsapp_default_message" value="<?= htmlspecialchars($settings['whatsapp_default_message'] ?? 'Hello OnlineBdMart, I want to order a product.') ?>" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
        </div>

        <div class="pt-2">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="whatsapp_floating_enabled" <?= ($settings['whatsapp_floating_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-emerald-500">
                <span class="text-emerald-400 font-bold">Show Floating WhatsApp Button on Storefront</span>
            </label>
        </div>

        <button type="submit" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold rounded-xl shadow-lg transition">Save WhatsApp Configuration</button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
