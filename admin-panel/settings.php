<?php
$adminTitle = 'General Store, Logo & Favicon Settings';
require_once __DIR__ . '/header.php';

$msg = '';
$error = '';

function uploadBrandFile($fileArray, $prefix = 'logo') {
    if (!empty($fileArray['name']) && $fileArray['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'ico'];
        if (in_array($ext, $allowed)) {
            $newName = $prefix . '_' . time() . '.' . $ext;
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
            $up = uploadBrandFile($_FILES['store_logo'], 'logo');
            if ($up) $logoPath = $up;
        }

        $faviconPath = trim($_POST['existing_favicon'] ?? 'images/logo.png');
        if (isset($_FILES['store_favicon']) && $_FILES['store_favicon']['error'] === UPLOAD_ERR_OK) {
            $upFav = uploadBrandFile($_FILES['store_favicon'], 'favicon');
            if ($upFav) $faviconPath = $upFav;
        }

        $keys = [
            'store_name' => $_POST['store_name'] ?? 'OnlineBdMart',
            'store_tagline' => $_POST['store_tagline'] ?? 'Online Shopping BD - Wholesale & Retail',
            'store_logo' => $logoPath,
            'store_favicon' => $faviconPath,
            'store_email' => $_POST['store_email'] ?? 'support@onlinebdmart.com',
            'store_phone' => $_POST['store_phone'] ?? '01775153740',
            'store_address' => $_POST['store_address'] ?? 'Tangail, Dhaka Division, Bangladesh',
            'announcement_bar' => $_POST['announcement_bar'] ?? 'Free Delivery Tangail ৳50 | Others ৳150 • Free Shipping above ৳2000',
            'footer_about_text' => $_POST['footer_about_text'] ?? 'OnlineBdMart is Bangladesh premier wholesale and retail fashion destination offering 100% verified authentic accessories and smart gadgets with Cash on Delivery nationwide.',
            'footer_copyright' => $_POST['footer_copyright'] ?? 'OnlineBdMart • Online Shopping Bangladesh. All rights reserved.',
            
            // 2-Factor Authentication
            'admin_2fa_enabled' => isset($_POST['admin_2fa_enabled']) ? '1' : '0',
            'admin_2fa_master_pin' => $_POST['admin_2fa_master_pin'] ?? '123456',
        ];

        foreach ($keys as $k => $v) {
            saveSetting($k, $v);
        }
        $msg = 'Store settings, brand logo, and website favicon icon updated successfully!';
    }

    $settings = getAllSettings();
} catch (Exception $e) {
    $error = $e->getMessage();
    $settings = [];
}

$logo = $settings['store_logo'] ?? 'images/logo.png';
$favicon = $settings['store_favicon'] ?? 'images/logo.png';
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold flex items-center gap-2">
    <i class="fas fa-circle-check text-base"></i> <span><?= htmlspecialchars($msg) ?></span>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="p-4 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-400 text-xs font-bold flex items-center gap-2">
    <i class="fas fa-circle-exclamation text-base"></i> <span><?= htmlspecialchars($error) ?></span>
</div>
<?php endif; ?>

<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-800 pb-4">
        <div>
            <span class="text-xs font-bold uppercase text-indigo-400 tracking-wider">Store Identity & Branding</span>
            <h2 class="text-lg font-black text-white mt-1">General Store Profile, Logo & Website Icon</h2>
            <p class="text-xs text-slate-400">Update store name, upload custom brand logo, browser tab favicon icon, and contact details.</p>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center justify-center text-2xl">
            <i class="fas fa-sliders"></i>
        </div>
    </div>

    <form method="POST" action="settings.php" enctype="multipart/form-data" class="space-y-6 text-xs">
        <input type="hidden" name="existing_logo" value="<?= htmlspecialchars($logo) ?>">
        <input type="hidden" name="existing_favicon" value="<?= htmlspecialchars($favicon) ?>">

        <!-- Brand Logo & Website Icon (Favicon) Side-by-Side Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- 1. Header & Brand Logo -->
            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <h3 class="font-extrabold text-white text-sm flex items-center gap-2">
                            <i class="fas fa-image text-indigo-400"></i> Website Main Brand Logo
                        </h3>
                        <div class="p-2 bg-slate-900 rounded-xl border border-slate-800 shrink-0">
                            <img src="/<?= ltrim($logo, '/') ?>" class="h-8 max-w-[120px] object-contain">
                        </div>
                    </div>
                    <p class="text-slate-400 text-[11px] leading-relaxed">
                        Appears on desktop navbar, mobile drawer, email invoices, and admin portal (Horizontal PNG / SVG / WebP).
                    </p>
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5">Upload Logo Image (লোগো ফাইল)</label>
                    <input type="file" name="store_logo" accept="image/*" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-slate-300 file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer">
                </div>
            </div>

            <!-- 2. Website Favicon / Browser Tab Icon -->
            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <h3 class="font-extrabold text-white text-sm flex items-center gap-2">
                            <i class="fas fa-globe text-cyan-400"></i> Website Icon (Favicon)
                        </h3>
                        <div class="w-10 h-10 bg-slate-900 rounded-xl border border-slate-800 flex items-center justify-center p-1.5 shrink-0">
                            <img src="/<?= ltrim($favicon, '/') ?>" class="w-7 h-7 object-contain rounded">
                        </div>
                    </div>
                    <p class="text-slate-400 text-[11px] leading-relaxed">
                        Appears on the browser tab, bookmarks bar, and phone home screen shortcut (Square PNG / ICO / SVG).
                    </p>
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5">Upload Website Icon (ট্যাব আইকন ফাইল)</label>
                    <input type="file" name="store_favicon" accept="image/*,.ico" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-slate-300 file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-cyan-600 file:text-white hover:file:bg-cyan-500 cursor-pointer">
                </div>
            </div>

        </div>

        <!-- Store Meta Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Store Name *</label>
                <input type="text" name="store_name" value="<?= htmlspecialchars($settings['store_name'] ?? 'OnlineBdMart') ?>" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Tagline</label>
                <input type="text" name="store_tagline" value="<?= htmlspecialchars($settings['store_tagline'] ?? 'Online Shopping BD - Wholesale & Retail') ?>" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Customer Care Phone *</label>
                <input type="text" name="store_phone" value="<?= htmlspecialchars($settings['store_phone'] ?? '01775153740') ?>" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500 font-mono">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Support Email</label>
                <input type="email" name="store_email" value="<?= htmlspecialchars($settings['store_email'] ?? 'support@onlinebdmart.com') ?>" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
            </div>
        </div>

        <div>
            <label class="block text-slate-300 font-bold mb-1">Top Announcement Bar Text</label>
            <input type="text" name="announcement_bar" value="<?= htmlspecialchars($settings['announcement_bar'] ?? 'Free Delivery Tangail ৳50 | Others ৳150 • Free Shipping above ৳2000') ?>" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
        </div>

        <div>
            <label class="block text-slate-300 font-bold mb-1">Physical Warehouse / Store Address</label>
            <textarea name="store_address" rows="2" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500"><?= htmlspecialchars($settings['store_address'] ?? 'Tangail, Dhaka Division, Bangladesh') ?></textarea>
        </div>

        <!-- Footer Customization -->
        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
            <h3 class="font-extrabold text-white text-sm"><i class="fas fa-shoe-prints text-amber-400 mr-1.5"></i> Footer Content & Tab Customization</h3>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Footer About Bio Text</label>
                <textarea name="footer_about_text" rows="2" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500"><?= htmlspecialchars($settings['footer_about_text'] ?? 'OnlineBdMart is Bangladesh premier wholesale and retail fashion destination offering 100% verified authentic accessories and smart gadgets with Cash on Delivery nationwide.') ?></textarea>
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Footer Copyright Text</label>
                <input type="text" name="footer_copyright" value="<?= htmlspecialchars($settings['footer_copyright'] ?? 'OnlineBdMart • Online Shopping Bangladesh. All rights reserved.') ?>" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
            </div>
        </div>

        <!-- 2-Factor Authentication Security Settings -->
        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-white text-sm"><i class="fas fa-shield-halved text-amber-400 mr-1.5"></i> Admin & Staff 2-Factor Authentication (2FA)</h3>
                    <p class="text-slate-400 text-[11px]">Require a 6-digit security code or PIN during login for maximum protection.</p>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="admin_2fa_enabled" <?= ($settings['admin_2fa_enabled'] ?? '0') === '1' ? 'checked' : '' ?> class="rounded text-amber-500 w-4 h-4">
                    <span class="text-amber-400 font-bold">Enforce 2FA on Login</span>
                </label>
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Backup Master PIN Code</label>
                <input type="password" name="admin_2fa_master_pin" value="<?= htmlspecialchars($settings['admin_2fa_master_pin'] ?? '123456') ?>" placeholder="e.g. 123456" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono outline-none focus:border-indigo-500">
            </div>
        </div>

        <button type="submit" class="px-8 py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg transition flex items-center gap-2">
            <i class="fas fa-floppy-disk"></i> <span>Save Store Settings, Logo & Favicon</span>
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
