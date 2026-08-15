<?php
$adminTitle = 'Home Slider & Banners';
require_once __DIR__ . '/header.php';

$msg = '';
try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            $title = trim($_POST['title']);
            $sub = trim($_POST['subtitle']);
            $badge = trim($_POST['badge_text']);
            $btnText = trim($_POST['button_text']);
            $btnUrl = trim($_POST['button_url']);
            $img = trim($_POST['image_path']);

            $stmt = $db->prepare("INSERT INTO banners (title, subtitle, badge_text, button_text, button_url, image_path, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, 1, 1)");
            $stmt->execute([$title, $sub, $badge, $btnText, $btnUrl, $img]);
            $msg = 'Banner slide added!';
        } elseif ($action === 'delete') {
            $id = (int)$_POST['banner_id'];
            $db->prepare("DELETE FROM banners WHERE id = ?")->execute([$id]);
            $msg = 'Banner removed!';
        }
    }

    $banners = $db->query("SELECT * FROM banners ORDER BY display_order ASC")->fetchAll();
} catch (Exception $e) {
    $banners = [];
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-extrabold text-white">Add Hero Slide</h3>
        <form method="POST" action="banners.php" class="space-y-3 text-xs">
            <input type="hidden" name="action" value="create">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Banner Title</label>
                <input type="text" name="title" required placeholder="Luxury Quartz & Gadget Collection" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Subtitle</label>
                <input type="text" name="subtitle" placeholder="Direct wholesale and retail delivery in BD." class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Badge Text</label>
                <input type="text" name="badge_text" placeholder="✨ 2026 PREMIUM COLLECTION" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Button Text</label>
                    <input type="text" name="button_text" value="Shop Now" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Button Link</label>
                    <input type="text" name="button_url" value="shop.php" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                </div>
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Image Relative Path</label>
                <input type="text" name="image_path" value="images/hero/hero-1.jpg" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow">Add Slide</button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-extrabold text-white">Active Slides (<?= count($banners) ?>)</h3>
        <div class="space-y-4">
            <?php foreach ($banners as $b): ?>
            <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between gap-4">
                <img src="/<?= ltrim($b['image_path'], '/') ?>" class="w-24 h-16 object-cover rounded-xl border border-slate-800 shrink-0">
                <div class="flex-1 min-w-0 text-xs">
                    <span class="text-[10px] font-bold uppercase text-indigo-400"><?= htmlspecialchars($b['badge_text'] ?? '') ?></span>
                    <h4 class="font-bold text-white truncate"><?= htmlspecialchars($b['title'] ?? '') ?></h4>
                    <p class="text-[11px] text-slate-400 truncate"><?= htmlspecialchars($b['subtitle'] ?? '') ?></p>
                </div>
                <form method="POST" action="banners.php" onsubmit="return confirm('Delete slide?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="banner_id" value="<?= $b['id'] ?>">
                    <button type="submit" class="p-2 bg-rose-500/20 text-rose-400 hover:bg-rose-500/40 rounded-xl text-xs"><i class="fas fa-trash-can"></i></button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
