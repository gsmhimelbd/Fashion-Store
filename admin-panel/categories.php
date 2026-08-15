<?php
$adminTitle = 'Manage Categories & Home Top Categories';
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
            $isActive = isset($_POST['is_active']) ? 1 : 1;

            if ($action === 'create') {
                $stmt = $db->prepare("INSERT INTO categories (name, slug, parent_id, emoji, icon, description, display_order, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
                $stmt->execute([$name, $slug, $parentId, $emoji, $icon, $desc, $displayOrder, $isActive]);
                $msg = $parentId ? '✓ Subcategory created successfully!' : '✓ Top Category created and added to Home Screen!';
            } else {
                $id = (int)$_POST['category_id'];
                $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ?, parent_id = ?, emoji = ?, icon = ?, description = ?, display_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $parentId, $emoji, $icon, $desc, $displayOrder, $isActive, $id]);
                $msg = '✓ Category updated successfully!';
            }
        } elseif ($action === 'delete') {
            $id = (int)$_POST['category_id'];
            $db->prepare("DELETE FROM categories WHERE id = ? OR parent_id = ?")->execute([$id, $id]);
            $msg = '✓ Category deleted successfully!';
        }
    }

    $parentCategories = $db->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as products_count FROM categories c WHERE c.parent_id IS NULL OR c.parent_id = 0 ORDER BY c.display_order ASC, c.id ASC")->fetchAll();
    $subCategories = $db->query("SELECT sc.*, p.name as parent_name, (SELECT COUNT(*) FROM products WHERE subcategory_id = sc.id) as products_count FROM categories sc LEFT JOIN categories p ON sc.parent_id = p.id WHERE sc.parent_id IS NOT NULL AND sc.parent_id > 0 ORDER BY sc.display_order ASC, sc.name ASC")->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
    $parentCategories = [];
    $subCategories = [];
}
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Category & Subcategory Add / Edit Form -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-5 h-fit">
        <div>
            <span class="text-xs font-bold uppercase text-indigo-400">Home Screen & Catalog</span>
            <h3 class="text-lg font-black text-white mt-1">Add / Manage Category</h3>
            <p class="text-xs text-slate-400">Categories created here automatically display in the Home Screen's <strong>"Browse Top Categories"</strong> section and Header navigation.</p>
        </div>

        <form method="POST" action="categories.php" id="categoryForm" class="space-y-4 text-xs">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="category_id" id="formCategoryId" value="">

            <div>
                <label class="block text-slate-300 font-bold mb-1">Parent Category</label>
                <select name="parent_id" id="formParentId" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
                    <option value="">None (Top Category for Home Screen)</option>
                    <?php foreach ($parentCategories as $pc): ?>
                    <option value="<?= $pc['id'] ?>">&rsaquo; <?= htmlspecialchars($pc['emoji'] ?? '🛍️') ?> <?= htmlspecialchars($pc['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-[10px] text-slate-500 mt-1">Select "None" to show directly on the Home Screen.</p>
            </div>

            <div>
                <label class="block text-slate-300 font-bold mb-1">Category Name *</label>
                <input type="text" name="name" id="formName" required placeholder="e.g. Watches / Leather Wallets" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500 font-medium">
            </div>

            <!-- Clean Emoji Picker -->
            <div>
                <label class="block text-slate-300 font-bold mb-1">Category Emoji Icon</label>
                <div class="flex items-center gap-2">
                    <input type="text" name="emoji" id="formEmoji" value="⌚" class="w-20 px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-center text-xl outline-none font-sans">
                    <span class="text-slate-400 text-[11px]">Click an emoji below to set:</span>
                </div>
                
                <div class="flex flex-wrap gap-1.5 p-3 bg-slate-950 rounded-2xl border border-slate-800 text-lg mt-2">
                    <?php 
                    $emojis = ['⌚', '📱', '👛', '👜', '🕶️', '👔', '👠', '💍', '🎁', '💎', '🎧', '👟', '👑', '👗', '💼', '👝', '⏱️', '⚙️', '🧸', '🧼', '🛍️', '📦'];
                    foreach ($emojis as $em): ?>
                    <button type="button" onclick="document.getElementById('formEmoji').value='<?= $em ?>';" class="w-8 h-8 rounded-lg hover:bg-slate-800 flex items-center justify-center transition hover:scale-125 select-none"><?= $em ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">FontAwesome Icon</label>
                    <input type="text" name="icon" id="formIcon" value="fa-clock" placeholder="fa-clock" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Display Order (ক্রম) *</label>
                    <input type="number" name="display_order" id="formOrder" value="1" min="1" max="99" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-bold outline-none focus:border-indigo-500">
                </div>
            </div>

            <div class="pt-2 flex items-center gap-2">
                <button type="submit" id="formSubmitBtn" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg transition">
                    + Save Category to Home Screen
                </button>
                <button type="button" id="formCancelBtn" onclick="resetCatForm()" class="hidden px-4 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl text-xs">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    <!-- Main Categories & Subcategories List with Live Preview -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Top Main Categories (Home Screen Grid) -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-5">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div>
                    <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                        <i class="fas fa-house text-indigo-400"></i> Home Screen Top Categories (<?= count($parentCategories) ?>)
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">These categories appear directly on the homepage in the exact order specified.</p>
                </div>
                <a href="../index.php" target="_blank" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-xl text-indigo-400 font-bold text-xs">
                    View on Home &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach ($parentCategories as $cat): 
                    $emoji = $cat['emoji'] ?? '🛍️';
                    $icon = $cat['icon'] ?? 'fa-tag';
                ?>
                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs hover:border-indigo-500/50 transition">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="w-12 h-12 rounded-xl bg-slate-900 border border-slate-800 text-indigo-400 flex items-center justify-center text-2xl shrink-0 overflow-hidden shadow-inner">
                            <?php if (!empty($emoji) && mb_strlen($emoji) <= 4 && !str_starts_with($emoji, 'fa-')): ?>
                                <span class="text-2xl select-none"><?= htmlspecialchars($emoji) ?></span>
                            <?php elseif (!empty($icon) && (str_starts_with($icon, 'fa-') || str_starts_with($icon, 'fa '))): ?>
                                <i class="fas <?= htmlspecialchars(ltrim($icon, 'fas ')) ?> text-lg"></i>
                            <?php else: ?>
                                <span class="text-2xl select-none"><?= htmlspecialchars($emoji ?: '🛍️') ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-extrabold text-white truncate text-xs sm:text-sm"><?= htmlspecialchars($cat['name']) ?></h4>
                            <div class="flex items-center gap-2 mt-0.5 text-[10px]">
                                <span class="text-indigo-400 font-bold"><?= $cat['products_count'] ?> Products</span>
                                <span class="text-slate-500">•</span>
                                <span class="text-amber-400 font-mono font-bold">Order #<?= $cat['display_order'] ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0 ml-2">
                        <button type="button" 
                                onclick="editCategory(<?= htmlspecialchars(json_encode($cat)) ?>)" 
                                class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl" title="Edit Category">
                            <i class="fas fa-pen-to-square"></i>
                        </button>
                        <form method="POST" action="categories.php" onsubmit="return confirm('Delete category <?= addslashes($cat['name']) ?>?');" class="inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                            <button type="submit" class="p-2 bg-rose-500/20 text-rose-400 hover:bg-rose-500/40 rounded-xl" title="Delete">
                                <i class="fas fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Subcategories List -->
        <?php if (!empty($subCategories)): ?>
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-folder-tree text-amber-400"></i> Subcategories (<?= count($subCategories) ?>)
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <?php foreach ($subCategories as $sc): ?>
                <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="text-xl shrink-0"><?= htmlspecialchars($sc['emoji'] ?? '🏷️') ?></span>
                        <div class="min-w-0">
                            <h4 class="font-bold text-white truncate"><?= htmlspecialchars($sc['name']) ?></h4>
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
        <?php endif; ?>

    </div>
</div>

<script>
function editCategory(cat) {
    document.getElementById('formAction').value = 'update';
    document.getElementById('formCategoryId').value = cat.id;
    document.getElementById('formName').value = cat.name || '';
    document.getElementById('formEmoji').value = cat.emoji || '🛍️';
    document.getElementById('formIcon').value = cat.icon || 'fa-tag';
    document.getElementById('formOrder').value = cat.display_order || 1;
    document.getElementById('formParentId').value = cat.parent_id || '';
    document.getElementById('formSubmitBtn').innerHTML = '✓ Update Category #' + cat.id;
    document.getElementById('formCancelBtn').classList.remove('hidden');
    document.getElementById('categoryForm').scrollIntoView({ behavior: 'smooth' });
}

function resetCatForm() {
    document.getElementById('formAction').value = 'create';
    document.getElementById('formCategoryId').value = '';
    document.getElementById('formName').value = '';
    document.getElementById('formEmoji').value = '⌚';
    document.getElementById('formIcon').value = 'fa-clock';
    document.getElementById('formOrder').value = 1;
    document.getElementById('formParentId').value = '';
    document.getElementById('formSubmitBtn').innerHTML = '+ Save Category to Home Screen';
    document.getElementById('formCancelBtn').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
