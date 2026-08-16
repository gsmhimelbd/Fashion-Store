<?php
$adminTitle = 'Colors & Theme Customization';
require_once __DIR__ . '/header.php';

$msg = '';
try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $keys = [
            'theme_primary_color' => $_POST['theme_primary_color'] ?? '#4f46e5',
            'theme_accent_color' => $_POST['theme_accent_color'] ?? '#f59e0b',
            'theme_nav_bg' => $_POST['theme_nav_bg'] ?? '#020617',
        ];

        foreach ($keys as $k => $v) {
            saveSetting($k, $v);
        }
        $msg = 'Theme colors updated!';
    }

    $settings = getAllSettings();
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
        <span class="text-xs font-bold uppercase text-purple-400">Design & UI</span>
        <h2 class="text-lg font-black text-white mt-1">Storefront Colors & Theme Palette</h2>
        <p class="text-xs text-slate-400">Select branding colors, buttons, and wholesale badge highlights.</p>
    </div>

    <form method="POST" action="colors.php" class="space-y-6 text-xs">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                <label class="block font-bold text-white">Primary Brand Color</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="theme_primary_color" value="<?= htmlspecialchars($settings['theme_primary_color'] ?? '#4f46e5') ?>" class="w-12 h-10 rounded-xl bg-transparent border border-slate-800 cursor-pointer">
                    <span class="font-mono text-slate-300"><?= htmlspecialchars($settings['theme_primary_color'] ?? '#4f46e5') ?></span>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                <label class="block font-bold text-white">Wholesale / Accent Highlight</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="theme_accent_color" value="<?= htmlspecialchars($settings['theme_accent_color'] ?? '#f59e0b') ?>" class="w-12 h-10 rounded-xl bg-transparent border border-slate-800 cursor-pointer">
                    <span class="font-mono text-slate-300"><?= htmlspecialchars($settings['theme_accent_color'] ?? '#f59e0b') ?></span>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                <label class="block font-bold text-white">Header & Top Bar Theme</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="theme_nav_bg" value="<?= htmlspecialchars($settings['theme_nav_bg'] ?? '#020617') ?>" class="w-12 h-10 rounded-xl bg-transparent border border-slate-800 cursor-pointer">
                    <span class="font-mono text-slate-300"><?= htmlspecialchars($settings['theme_nav_bg'] ?? '#020617') ?></span>
                </div>
            </div>
        </div>

        <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg transition">Apply Theme Palette</button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
