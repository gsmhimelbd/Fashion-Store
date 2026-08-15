<?php
$adminTitle = 'Categories Management';
require_once __DIR__ . '/header.php';

$msg = '';
try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            $name = trim($_POST['name']);
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            $icon = trim($_POST['icon'] ?? 'fa-tag');

            $stmt = $db->prepare("INSERT INTO categories (name, slug, icon, is_active, display_order) VALUES (?, ?, ?, 1, 1)");
            $stmt->execute([$name, $slug, $icon]);
            $msg = 'Category added!';
        } elseif ($action === 'delete') {
            $id = (int)$_POST['category_id'];
            $db->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
            $msg = 'Category deleted!';
        }
    }

    $categories = $db->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as products_count FROM categories c ORDER BY display_order ASC, name ASC")->fetchAll();
} catch (Exception $e) {
    $categories = [];
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-extrabold text-white">Add Category</h3>
        <form method="POST" action="categories.php" class="space-y-3 text-xs">
            <input type="hidden" name="action" value="create">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Category Name</label>
                <input type="text" name="name" required placeholder="e.g. Smart Watches" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">FontAwesome Icon Class</label>
                <input type="text" name="icon" value="fa-clock" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow">Save Category</button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-extrabold text-white">All Categories (<?= count($categories) ?>)</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php foreach ($categories as $cat): ?>
            <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center text-base"><i class="fas <?= htmlspecialchars($cat['icon'] ?: 'fa-tag') ?>"></i></div>
                    <div>
                        <h4 class="font-bold text-white"><?= htmlspecialchars($cat['name']) ?></h4>
                        <span class="text-slate-400 text-[11px]"><?= $cat['products_count'] ?> products</span>
                    </div>
                </div>
                <form method="POST" action="categories.php" onsubmit="return confirm('Delete category?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                    <button type="submit" class="p-2 bg-rose-500/20 text-rose-400 hover:bg-rose-500/40 rounded-xl"><i class="fas fa-trash-can"></i></button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
