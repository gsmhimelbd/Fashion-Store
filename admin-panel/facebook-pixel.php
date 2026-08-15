<?php
$adminTitle = 'Facebook Pixel & Conversions API';
require_once __DIR__ . '/header.php';

$msg = '';
try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $keys = [
            'facebook_pixel_id' => $_POST['facebook_pixel_id'] ?? '',
            'facebook_pixel_enabled' => isset($_POST['facebook_pixel_enabled']) ? '1' : '0',
            'track_pageviews' => isset($_POST['track_pageviews']) ? '1' : '0',
            'track_add_to_cart' => isset($_POST['track_add_to_cart']) ? '1' : '0',
            'track_purchase' => isset($_POST['track_purchase']) ? '1' : '0',
        ];

        $upsert = $db->prepare("INSERT INTO settings (`key`, `value`, `updated_at`) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW()");
        foreach ($keys as $k => $v) {
            $upsert->execute([$k, $v]);
        }
        $msg = 'Facebook Pixel settings saved!';
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
        <span class="text-xs font-bold uppercase text-blue-400">Ad Tracking</span>
        <h2 class="text-lg font-black text-white mt-1">Meta Facebook Pixel Integration</h2>
        <p class="text-xs text-slate-400">Track AddToCart, InitiateCheckout, and Purchase standard events for Meta / Facebook advertising campaigns.</p>
    </div>

    <form method="POST" action="facebook-pixel.php" class="space-y-4 text-xs">
        <div>
            <label class="block text-slate-300 font-bold mb-1">Facebook Pixel ID</label>
            <input type="text" name="facebook_pixel_id" value="<?= htmlspecialchars($settings['facebook_pixel_id'] ?? '123456789012345') ?>" placeholder="e.g. 123456789012345" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none">
        </div>

        <div class="space-y-2 pt-2">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="facebook_pixel_enabled" <?= ($settings['facebook_pixel_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-blue-500">
                <span class="text-blue-400 font-bold">Enable Facebook Pixel on All Pages</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="track_add_to_cart" <?= ($settings['track_add_to_cart'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-indigo-500">
                <span class="text-slate-300">Track <code>AddToCart</code> Events</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="track_purchase" <?= ($settings['track_purchase'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-indigo-500">
                <span class="text-slate-300">Track <code>Purchase</code> & Order Revenue Conversions</span>
            </label>
        </div>

        <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-500 text-white font-extrabold rounded-xl shadow-lg transition">Save Pixel Settings</button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
