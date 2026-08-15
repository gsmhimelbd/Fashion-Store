<?php
$adminTitle = 'General Store & Logo Settings';
require_once __DIR__ . '/header.php';

$msg = '';
$error = '';

function uploadLogoFile($fileArray) {
    if (!empty($fileArray['name']) && $fileArray['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
        if (in_array($ext, $allowed)) {
            $newName = 'logo_' . time() . '.' . $ext;
            $relPath = "uploads/logo/" . $newName;
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
        $logoPath = trim($_POST['existing_logo'] ?? 'images/logo.png');
        if (isset($_FILES['store_logo']) && $_FILES['store_logo']['error'] === UPLOAD_ERR_OK) {
            $up = uploadLogoFile($_FILES['store_logo']);
            if ($up) $logoPath = $up;
        }

        $keys = [
            'store_name' => $_POST['store_name'] ?? 'OnlineBdMart',
            'store_tagline' => $_POST['store_tagline'] ?? 'Online Shopping BD - Wholesale & Retail',
            'store_logo' => $logoPath,
            'store_email' => $_POST['store_email'] ?? 'support@onlinebdmart.com',
            'store_phone' => $_POST['store_phone'] ?? '01775153740',
            'store_address' => $_POST['store_address'] ?? 'Tangail, Dhaka Division, Bangladesh',
            'announcement_bar' => $_POST['announcement_bar'] ?? 'Free Delivery Tangail ৳50 | Others ৳150 • Free Shipping above ৳2000',
            'footer_about_text' => $_POST['footer_about_text'] ?? 'OnlineBdMart is Bangladesh premier wholesale and retail fashion destination offering 100% verified authentic accessories and smart gadgets with Cash on Delivery nationwide.',
            'footer_copyright' => $_POST['footer_copyright'] ?? 'OnlineBdMart • Online Shopping Bangladesh. All rights reserved.',
        ];

        $upsert = $db->prepare("INSERT INTO settings (`key`, `value`, `updated_at`) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW()");
        foreach ($keys as $k => $v) {
            $upsert->execute([$k, $v]);
        }
        $msg = 'Store settings and brand logo updated successfully!';
    }

    $settingsStmt = $db->query("SELECT `key`, `value` FROM settings");
    $settings = $settingsStmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $e) {
    $error = $e->getMessage();
    $settings = [];
}

$logo = $settings['store_logo'] ?? 'images/logo.png';
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
        <span class="text-xs font-bold uppercase text-indigo-400">Store Identity & Branding</span>
        <h2 class="text-lg font-black text-white mt-1">General Store Profile & Global Logo</h2>
        <p class="text-xs text-slate-400">Update store name, upload custom brand logo from computer, top announcement bar, and footer tabs.</p>
    </div>

    <form method="POST" action="settings.php" enctype="multipart/form-data" class="space-y-6 text-xs">
        <input type="hidden" name="existing_logo" value="<?= htmlspecialchars($logo) ?>">

        <!-- Brand Logo Upload Section -->
        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-white text-sm"><i class="fas fa-image text-indigo-400 mr-1.5"></i> Website Global Brand Logo</h3>
                    <p class="text-slate-400 text-[11px]">Upload a horizontal brand logo image (PNG/SVG/WebP) to appear on desktop header, mobile header, invoices, and admin panel.</p>
                </div>
                <div class="p-2 bg-slate-900 rounded-xl border border-slate-800">
                    <img src="/<?= ltrim($logo, '/') ?>" class="h-10 max-w-[140px] object-contain">
                </div>
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Choose Logo File from Local Computer</label>
                <input type="file" name="store_logo" accept="image/*" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-slate-300 file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500">
            </div>
        </div>

        <!-- Store Meta Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Store Name *</label>
                <input type="text" name="store_name" value="<?= htmlspecialchars($settings['store_name'] ?? 'OnlineBdMart') ?>" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Tagline</label>
                <input type="text" name="store_tagline" value="<?= htmlspecialchars($settings['store_tagline'] ?? 'Online Shopping BD - Wholesale & Retail') ?>" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Customer Care Phone *</label>
                <input type="text" name="store_phone" value="<?= htmlspecialchars($settings['store_phone'] ?? '01775153740') ?>" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Support Email</label>
                <input type="email" name="store_email" value="<?= htmlspecialchars($settings['store_email'] ?? 'support@onlinebdmart.com') ?>" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
        </div>

        <div>
            <label class="block text-slate-300 font-bold mb-1">Top Announcement Bar Text</label>
            <input type="text" name="announcement_bar" value="<?= htmlspecialchars($settings['announcement_bar'] ?? 'Free Delivery Tangail ৳50 | Others ৳150 • Free Shipping above ৳2000') ?>" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
        </div>

        <div>
            <label class="block text-slate-300 font-bold mb-1">Physical Warehouse / Store Address</label>
            <textarea name="store_address" rows="2" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none"><?= htmlspecialchars($settings['store_address'] ?? 'Tangail, Dhaka Division, Bangladesh') ?></textarea>
        </div>

        <!-- Footer Customization -->
        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
            <h3 class="font-extrabold text-white text-sm"><i class="fas fa-shoe-prints text-amber-400 mr-1.5"></i> Footer Content & Tab Customization</h3>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Footer About Bio Text</label>
                <textarea name="footer_about_text" rows="2" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white outline-none"><?= htmlspecialchars($settings['footer_about_text'] ?? 'OnlineBdMart is Bangladesh premier wholesale and retail fashion destination offering 100% verified authentic accessories and smart gadgets with Cash on Delivery nationwide.') ?></textarea>
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Footer Copyright Text</label>
                <input type="text" name="footer_copyright" value="<?= htmlspecialchars($settings['footer_copyright'] ?? 'OnlineBdMart • Online Shopping Bangladesh. All rights reserved.') ?>" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white outline-none">
            </div>
        </div>

        <button type="submit" class="px-8 py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg transition">
            Save Store Settings & Logo
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
