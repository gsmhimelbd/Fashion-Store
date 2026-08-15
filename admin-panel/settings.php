<?php
$adminTitle = 'General Store Settings';
require_once __DIR__ . '/header.php';

$msg = '';
try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $keys = [
            'store_name' => $_POST['store_name'] ?? 'OnlineBdMart',
            'store_tagline' => $_POST['store_tagline'] ?? 'Online Shopping BD - Wholesale & Retail',
            'store_email' => $_POST['store_email'] ?? 'support@onlinebdmart.com',
            'store_phone' => $_POST['store_phone'] ?? '01712-345678',
            'store_address' => $_POST['store_address'] ?? 'Tangail, Dhaka Division, Bangladesh',
            'store_currency' => $_POST['store_currency'] ?? 'BDT (৳)',
        ];

        $upsert = $db->prepare("INSERT INTO settings (`key`, `value`, `updated_at`) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW()");
        foreach ($keys as $k => $v) {
            $upsert->execute([$k, $v]);
        }
        $msg = 'Store settings saved!';
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
        <span class="text-xs font-bold uppercase text-indigo-400">Store Profile</span>
        <h2 class="text-lg font-black text-white mt-1">Store Information & Business Info</h2>
        <p class="text-xs text-slate-400">Update store name, email, phone number, address, and invoice headers.</p>
    </div>

    <form method="POST" action="settings.php" class="space-y-4 text-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Store Name *</label>
                <input type="text" name="store_name" value="<?= htmlspecialchars($settings['store_name'] ?? 'OnlineBdMart') ?>" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Store Tagline</label>
                <input type="text" name="store_tagline" value="<?= htmlspecialchars($settings['store_tagline'] ?? 'Online Shopping BD - Wholesale & Retail') ?>" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Store Email</label>
                <input type="email" name="store_email" value="<?= htmlspecialchars($settings['store_email'] ?? 'support@onlinebdmart.com') ?>" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Customer Care Phone</label>
                <input type="text" name="store_phone" value="<?= htmlspecialchars($settings['store_phone'] ?? '01712-345678') ?>" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Currency Symbol</label>
                <input type="text" name="store_currency" value="<?= htmlspecialchars($settings['store_currency'] ?? 'BDT (৳)') ?>" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
        </div>

        <div>
            <label class="block text-slate-300 font-bold mb-1">Physical Warehouse / Store Address</label>
            <textarea name="store_address" rows="2" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none"><?= htmlspecialchars($settings['store_address'] ?? 'Tangail, Dhaka Division, Bangladesh') ?></textarea>
        </div>

        <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg transition">Save Store Settings</button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
