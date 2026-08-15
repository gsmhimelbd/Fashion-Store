<?php
$adminTitle = 'SEO & Meta Tags';
require_once __DIR__ . '/header.php';

$msg = '';
try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $keys = [
            'seo_meta_title' => $_POST['seo_meta_title'] ?? '',
            'seo_meta_description' => $_POST['seo_meta_description'] ?? '',
            'seo_meta_keywords' => $_POST['seo_meta_keywords'] ?? '',
            'seo_og_image' => $_POST['seo_og_image'] ?? '',
        ];

        $upsert = $db->prepare("INSERT INTO settings (`key`, `value`, `updated_at`) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW()");
        foreach ($keys as $k => $v) {
            $upsert->execute([$k, $v]);
        }
        $msg = 'SEO settings saved!';
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
        <span class="text-xs font-bold uppercase text-emerald-400">Search Engine Visibility</span>
        <h2 class="text-lg font-black text-white mt-1">SEO & OpenGraph Social Sharing</h2>
        <p class="text-xs text-slate-400">Configure global search meta titles, descriptions, and OpenGraph thumbnail cards for Facebook, WhatsApp, and Google.</p>
    </div>

    <form method="POST" action="seo.php" class="space-y-4 text-xs">
        <div>
            <label class="block text-slate-300 font-bold mb-1">Global Meta Title</label>
            <input type="text" name="seo_meta_title" value="<?= htmlspecialchars($settings['seo_meta_title'] ?? 'OnlineBdMart • Best Online Shopping & Wholesale in Bangladesh') ?>" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
        </div>

        <div>
            <label class="block text-slate-300 font-bold mb-1">Meta Description</label>
            <textarea name="seo_meta_description" rows="2" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none"><?= htmlspecialchars($settings['seo_meta_description'] ?? 'Buy original accessories, luxury chronograph watches, leather wallets, and smart gadgets at lowest prices across 64 districts in Bangladesh with Cash on Delivery.') ?></textarea>
        </div>

        <div>
            <label class="block text-slate-300 font-bold mb-1">Meta Keywords (Comma separated)</label>
            <input type="text" name="seo_meta_keywords" value="<?= htmlspecialchars($settings['seo_meta_keywords'] ?? 'online shopping bd, onlinebdmart, wholesale bangladesh, watches bd, leather wallet, accessories bangladesh, cash on delivery') ?>" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
        </div>

        <div>
            <label class="block text-slate-300 font-bold mb-1">Social Preview Image (OpenGraph)</label>
            <input type="text" name="seo_og_image" value="<?= htmlspecialchars($settings['seo_og_image'] ?? 'images/hero/hero-1.jpg') ?>" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
        </div>

        <button type="submit" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold rounded-xl shadow-lg transition">Save SEO Meta Configuration</button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
