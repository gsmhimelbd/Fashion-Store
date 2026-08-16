<?php
$adminTitle = 'SEO & Social Media Optimization';
require_once __DIR__ . '/header.php';

$msg = '';
$error = '';

function uploadSeoOgFile($fileArray) {
    if (!empty($fileArray['name']) && $fileArray['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $newName = 'og_' . time() . '.' . $ext;
            $relPath = "uploads/seo/" . $newName;
            $dest1 = __DIR__ . '/../' . $relPath;
            $dest2 = __DIR__ . '/../public/' . $relPath;
            @mkdir(dirname($dest1), 0777, true);
            @mkdir(dirname($dest2), 0777, true);
            if (@move_uploaded_file($fileArray['tmp_name'], $dest1)) {
                @copy($dest1, $dest2);
                return $relPath;
            }
        }
    }
    return null;
}

try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ogPath = trim($_POST['existing_og'] ?? 'images/hero/hero-1.jpg');
        if (isset($_FILES['og_image']) && $_FILES['og_image']['error'] === UPLOAD_ERR_OK) {
            $up = uploadSeoOgFile($_FILES['og_image']);
            if ($up) $ogPath = $up;
        }

        $keys = [
            'seo_meta_title' => $_POST['seo_meta_title'] ?? 'OnlineBdMart • Best Online Shopping & Wholesale in Bangladesh',
            'seo_meta_description' => $_POST['seo_meta_description'] ?? 'Buy original accessories, luxury chronograph watches, leather wallets, and smart gadgets at lowest prices across 64 districts in Bangladesh with Cash on Delivery.',
            'seo_meta_keywords' => $_POST['seo_meta_keywords'] ?? 'online shopping bd, onlinebdmart, wholesale bangladesh, watches bd, leather wallet, accessories bangladesh, cash on delivery',
            'seo_og_image' => $ogPath,
            'seo_google_analytics' => $_POST['seo_google_analytics'] ?? '',
            'seo_header_tags' => $_POST['seo_header_tags'] ?? '',
        ];

        foreach ($keys as $k => $v) {
            saveSetting($k, $v);
        }
        $msg = 'SEO & OpenGraph Social metadata saved successfully!';
    }

    $settings = getAllSettings();
} catch (Exception $e) {
    $error = $e->getMessage();
    $settings = [];
}

$ogImage = $settings['seo_og_image'] ?? 'images/hero/hero-1.jpg';
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1.5"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="p-4 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-400 text-xs font-bold">
    <i class="fas fa-circle-exclamation mr-1.5"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
    <div>
        <span class="text-xs font-bold uppercase text-emerald-400">Search Engine Visibility</span>
        <h2 class="text-lg font-black text-white mt-1">SEO & Social Media OpenGraph Setup</h2>
        <p class="text-xs text-slate-400">Upload social share preview image from computer and configure meta tags for Facebook, WhatsApp, and Google.</p>
    </div>

    <form method="POST" action="seo.php" enctype="multipart/form-data" class="space-y-6 text-xs">
        <input type="hidden" name="existing_og" value="<?= htmlspecialchars($ogImage) ?>">

        <!-- Social Share Preview Image -->
        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-white text-sm"><i class="fas fa-share-nodes text-emerald-400 mr-1.5"></i> Social Share Preview Photo (Facebook & WhatsApp Link Card)</h3>
                    <p class="text-slate-400 text-[11px]">This high-resolution thumbnail appears automatically when sharing website links on WhatsApp, Messenger, and Facebook.</p>
                </div>
                <div class="p-2 bg-slate-900 rounded-xl border border-slate-800">
                    <img src="/<?= ltrim($ogImage, '/') ?>" class="h-14 w-24 object-cover rounded-lg">
                </div>
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Choose Social Thumbnail from Local Computer (1200x630 recommended)</label>
                <input type="file" name="og_image" accept="image/*" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-slate-300 file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500">
            </div>
        </div>

        <div>
            <label class="block text-slate-300 font-bold mb-1">Global Meta Title (Google Search Title)</label>
            <input type="text" name="seo_meta_title" value="<?= htmlspecialchars($settings['seo_meta_title'] ?? 'OnlineBdMart • Best Online Shopping & Wholesale in Bangladesh') ?>" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
        </div>

        <div>
            <label class="block text-slate-300 font-bold mb-1">Meta Description (160 characters snippet)</label>
            <textarea name="seo_meta_description" rows="2" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none"><?= htmlspecialchars($settings['seo_meta_description'] ?? 'Buy original accessories, luxury chronograph watches, leather wallets, and smart gadgets at lowest prices across 64 districts in Bangladesh with Cash on Delivery.') ?></textarea>
        </div>

        <div>
            <label class="block text-slate-300 font-bold mb-1">Meta Keywords (Comma separated tags)</label>
            <input type="text" name="seo_meta_keywords" value="<?= htmlspecialchars($settings['seo_meta_keywords'] ?? 'online shopping bd, onlinebdmart, wholesale bangladesh, watches bd, leather wallet, accessories bangladesh, cash on delivery') ?>" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Google Analytics Measurement ID</label>
                <input type="text" name="seo_google_analytics" value="<?= htmlspecialchars($settings['seo_google_analytics'] ?? 'G-XXXXXXXXXX') ?>" placeholder="G-XXXXXXXXXX" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Custom Header Tracking Scripts</label>
                <input type="text" name="seo_header_tags" value="<?= htmlspecialchars($settings['seo_header_tags'] ?? '') ?>" placeholder="<meta ...>" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none">
            </div>
        </div>

        <button type="submit" class="px-8 py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold rounded-xl shadow-lg transition">
            Save SEO & Social Settings
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
