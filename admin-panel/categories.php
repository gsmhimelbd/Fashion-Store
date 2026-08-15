<?php
$adminTitle = 'Categories & Subcategories';
require_once __DIR__ . '/header.php';

$msg = '';
$error = '';

try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'create' || $action === 'update') {
            $name = trim($_POST['name'] ?? '');
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            if (!$slug) $slug = 'cat-' . time();
            $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
            $emoji = trim($_POST['emoji'] ?? '🛍️');
            $icon = trim($_POST['icon'] ?? 'fa-tag');
            $desc = trim($_POST['description'] ?? '');
            $displayOrder = (int)($_POST['display_order'] ?? 1);

            if ($action === 'create') {
                $stmt = $db->prepare("INSERT INTO categories (name, slug, parent_id, emoji, icon, description, display_order, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())");
                $stmt->execute([$name, $slug, $parentId, $emoji, $icon, $desc, $displayOrder]);
                $msg = $parentId ? 'Subcategory created successfully!' : 'Category created successfully!';
            } else {
                $id = (int)$_POST['category_id'];
                $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ?, parent_id = ?, emoji = ?, icon = ?, description = ?, display_order = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $parentId, $emoji, $icon, $desc, $displayOrder, $id]);
                $msg = 'Category updated successfully!';
            }
        } elseif ($action === 'delete') {
            $id = (int)$_POST['category_id'];
            $db->prepare("DELETE FROM categories WHERE id = ? OR parent_id = ?")->execute([$id, $id]);
            $msg = 'Category and associated subcategories deleted!';
        }
    }

    $parentCategories = $db->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as products_count FROM categories c WHERE c.parent_id IS NULL OR c.parent_id = 0 ORDER BY c.display_order ASC, c.name ASC")->fetchAll();
    $subCategories = $db->query("SELECT sc.*, p.name as parent_name, (SELECT COUNT(*) FROM products WHERE subcategory_id = sc.id) as products_count FROM categories sc LEFT JOIN categories p ON sc.parent_id = p.id WHERE sc.parent_id IS NOT NULL AND sc.parent_id > 0 ORDER BY sc.name ASC")->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
    $parentCategories = [];
    $subCategories = [];
}
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Category & Subcategory Add Form -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div>
            <span class="text-xs font-bold uppercase text-indigo-400">Navigation Taxonomy</span>
            <h3 class="text-base font-extrabold text-white mt-1">Add Category / Subcategory</h3>
        </div>

        <form method="POST" action="categories.php" class="space-y-4 text-xs">
            <input type="hidden" name="action" value="create">

            <div>
                <label class="block text-slate-300 font-bold mb-1">Parent Category (Select for Subcategory)</label>
                <select name="parent_id" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    <option value="">None (Create Main Category)</option>
                    <?php foreach ($parentCategories as $pc): ?>
                    <option value="<?= $pc['id'] ?>">&rsaquo; <?= htmlspecialchars($pc['emoji'] ?? '🛍️') ?> <?= htmlspecialchars($pc['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-slate-300 font-bold mb-1">Category Name *</label>
                <input type="text" name="name" required placeholder="e.g. Chronograph Watches" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>

            <!-- Emoji Selector with Copy-Paste Button Bar -->
            <div>
                <label class="block text-slate-300 font-bold mb-1">Category Emoji Icon</label>
                <input type="text" name="emoji" id="emojiInput" value="⌚" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-base outline-none font-sans">
                
                <p class="text-[11px] text-slate-400 mt-2 mb-1 font-bold">Quick Emoji Picker (Click to insert):</p>
                <div class="flex flex-wrap gap-1.5 p-2.5 bg-slate-950 rounded-xl border border-slate-800 text-base">
                    <?php 
                    $emojis = ['⌚', '📱', '👛', '👜', '🕶️', '👔', '👠', '💍', '🎁', '💎', '🎧', '👟', '👑', '👗', '💼', '👝', '⏱️', '⚙️', '🧸', '🧼'];
                    foreach ($emojis as $em): ?>
                    <button type="button" onclick="document.getElementById('emojiInput').value='<?= $em ?>';" class="w-8 h-8 rounded-lg hover:bg-slate-800 flex items-center justify-center transition hover:scale-125"><?= $em ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">FontAwesome Icon</label>
                    <input type="text" name="icon" value="fa-clock" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Display Order</label>
                    <input type="number" name="display_order" value="1" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-bold outline-none">
                </div>
            </div>

            <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg transition">
                + Save Category
            </button>
        </form>
    </div>

    <!-- Main Categories & Subcategories List -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Parent Categories -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-extrabold text-white">Main Product Categories (<?= count($parentCategories) ?>)</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach ($parentCategories as $cat): ?>
                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl"><?= htmlspecialchars($cat['emoji'] ?? '🛍️') ?></span>
                        <div>
                            <h4 class="font-extrabold text-white"><?= htmlspecialchars($cat['name']) ?></h4>
                            <span class="text-indigo-400 text-[11px]"><?= $cat['products_count'] ?> products • order: #<?= $cat['display_order'] ?></span>
                        </div>
                    </div>
                    <form method="POST" action="categories.php" onsubmit="return confirm('Delete this category and its subcategories?');" class="inline">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                        <button type="submit" class="p-2 bg-rose-500/20 text-rose-400 hover:bg-rose-500/40 rounded-xl"><i class="fas fa-trash-can"></i></button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Subcategories -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-extrabold text-white">Subcategories (<?= count($subCategories) ?>)</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach ($subCategories as $sc): ?>
                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-3">
                        <span class="text-xl"><?= htmlspecialchars($sc['emoji'] ?? '🏷️') ?></span>
                        <div>
                            <h4 class="font-bold text-white"><?= htmlspecialchars($sc['name']) ?></h4>
                            <span class="text-slate-400 text-[10px]">Under: <strong class="text-indigo-300"><?= htmlspecialchars($sc['parent_name'] ?? '') ?></strong></span>
                        </div>
                    </div>
                    <form method="POST" action="categories.php" onsubmit="return confirm('Delete subcategory?');" class="inline">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="category_id" value="<?= $sc['id'] ?>">
                        <button type="submit" class="p-1.5 bg-rose-500/20 text-rose-400 hover:bg-rose-500/40 rounded-lg text-xs"><i class="fas fa-trash-can"></i></button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
