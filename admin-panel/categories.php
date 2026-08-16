<?php
$adminTitle = 'Manage Categories & Home Top Categories';
require_once __DIR__ . '/header.php';

$msg = '';
$error = '';

try {
    $db = getDB();

    // Auto-heal categories schema if missing show_on_homepage or emoji columns
    try {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            @$db->exec("ALTER TABLE `categories` ADD COLUMN `show_on_homepage` tinyint(1) DEFAULT 1");
            @$db->exec("ALTER TABLE `categories` ADD COLUMN `is_featured` tinyint(1) DEFAULT 1");
            @$db->exec("ALTER TABLE `categories` ADD COLUMN `emoji` varchar(50) DEFAULT '🛍️'");
            @$db->exec("ALTER TABLE `categories` ADD COLUMN `parent_id` int(11) DEFAULT NULL");
            @$db->exec("ALTER TABLE `categories` ADD COLUMN `display_order` int(11) DEFAULT 1");
        }
    } catch (Exception $ex) {}

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'create' || $action === 'update') {
            $name = trim($_POST['name'] ?? '');
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            if (!$slug) $slug = 'cat-' . time();
            $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
            $emoji = trim($_POST['emoji'] ?? '🛍️');
            if (empty($emoji)) $emoji = '🛍️';
            $desc = trim($_POST['description'] ?? '');
            $displayOrder = (int)($_POST['display_order'] ?? 1);
            $showOnHome = isset($_POST['show_on_homepage']) ? 1 : 0;
            $isActive = 1;

            if ($action === 'create') {
                try {
                    $stmt = $db->prepare("INSERT INTO categories (name, slug, parent_id, emoji, description, display_order, show_on_homepage, is_featured, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
                    $stmt->execute([$name, $slug, $parentId, $emoji, $desc, $displayOrder, $showOnHome, $showOnHome, $isActive]);
                } catch (Exception $e1) {
                    try {
                        @$db->exec("ALTER TABLE `categories` ADD COLUMN `show_on_homepage` tinyint(1) DEFAULT 1");
                        $stmt = $db->prepare("INSERT INTO categories (name, slug, parent_id, emoji, description, display_order, show_on_homepage, is_featured, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$name, $slug, $parentId, $emoji, $desc, $displayOrder, $showOnHome, $showOnHome, $isActive]);
                    } catch (Exception $e2) {
                        $stmt = $db->prepare("INSERT INTO categories (name, slug, parent_id, emoji, description, display_order) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$name, $slug, $parentId, $emoji, $desc, $displayOrder]);
                    }
                }
                $msg = $parentId ? '✓ Subcategory created successfully!' : '✓ Category created and saved!';
            } else {
                $id = (int)$_POST['category_id'];
                try {
                    $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ?, parent_id = ?, emoji = ?, description = ?, display_order = ?, show_on_homepage = ?, is_featured = ? WHERE id = ?");
                    $stmt->execute([$name, $slug, $parentId, $emoji, $desc, $displayOrder, $showOnHome, $showOnHome, $id]);
                } catch (Exception $e1) {
                    try {
                        @$db->exec("ALTER TABLE `categories` ADD COLUMN `show_on_homepage` tinyint(1) DEFAULT 1");
                        $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ?, parent_id = ?, emoji = ?, description = ?, display_order = ?, show_on_homepage = ? WHERE id = ?");
                        $stmt->execute([$name, $slug, $parentId, $emoji, $desc, $displayOrder, $showOnHome, $id]);
                    } catch (Exception $e2) {
                        $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ?, parent_id = ?, emoji = ?, description = ?, display_order = ? WHERE id = ?");
                        $stmt->execute([$name, $slug, $parentId, $emoji, $desc, $displayOrder, $id]);
                    }
                }
                $msg = '✓ Category updated successfully!';
            }
        } elseif ($action === 'toggle_home') {
            $id = (int)$_POST['category_id'];
            $current = (int)$_POST['current_status'];
            $newStatus = $current === 1 ? 0 : 1;
            try {
                @$db->exec("ALTER TABLE `categories` ADD COLUMN `show_on_homepage` tinyint(1) DEFAULT 1");
                $db->prepare("UPDATE categories SET show_on_homepage = ?, is_featured = ? WHERE id = ?")->execute([$newStatus, $newStatus, $id]);
            } catch (Exception $ex) {
                try {
                    $db->prepare("UPDATE categories SET show_on_homepage = ? WHERE id = ?")->execute([$newStatus, $id]);
                } catch (Exception $ex2) {}
            }
            $msg = $newStatus === 1 ? '✓ Category enabled on Home Screen!' : '✓ Category hidden from Home Screen.';
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
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold flex items-center gap-2 mb-6">
    <i class="fas fa-circle-check text-base"></i> <span><?= htmlspecialchars($msg) ?></span>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="p-4 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-400 text-xs font-bold flex items-center gap-2 mb-6">
    <i class="fas fa-circle-exclamation text-base"></i> <span><?= htmlspecialchars($error) ?></span>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Category Add & Edit Form Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-5 h-fit">
        <div class="border-b border-slate-800 pb-3">
            <span class="text-xs font-bold uppercase text-indigo-400">Home Screen & Catalog</span>
            <h3 class="text-lg font-black text-white mt-1" id="formHeaderTitle">Add Category</h3>
            <p class="text-xs text-slate-400">Manage categories and choose exactly which ones to show in the <strong>"Browse Top Categories"</strong> section on the Home Page.</p>
        </div>

        <form method="POST" action="categories.php" id="categoryForm" class="space-y-4 text-xs">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="category_id" id="formCategoryId" value="">

            <div>
                <label class="block text-slate-300 font-bold mb-1">Parent Category (মেইন নাকি সাব-ক্যাটাগরি)</label>
                <select name="parent_id" id="formParentId" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
                    <option value="">None (Top Category for Home Screen)</option>
                    <?php foreach ($parentCategories as $pc): ?>
                    <option value="<?= $pc['id'] ?>">&rsaquo; <?= htmlspecialchars($pc['emoji'] ?? '🛍️') ?> <?= htmlspecialchars($pc['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-slate-300 font-bold mb-1">Category Name (ক্যাটাগরির নাম) *</label>
                <input type="text" name="name" id="formName" required placeholder="e.g. Watches, Leather Wallets, Sunglasses" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500 font-medium">
            </div>

            <!-- Emoji Selection & Palette -->
            <div>
                <label class="block text-slate-300 font-bold mb-1">Category Logo / Emoji Icon (ইমোজি আইকন) *</label>
                <div class="flex items-center gap-2">
                    <input type="text" name="emoji" id="formEmoji" value="⌚" required class="w-20 px-3 py-2 bg-slate-950 border-2 border-indigo-500 rounded-xl text-white text-center text-2xl outline-none font-sans font-bold shadow-inner">
                    <span class="text-slate-400 text-[11px]">Click an emoji below to select:</span>
                </div>
                
                <div class="flex flex-wrap gap-1.5 p-3 bg-slate-950 rounded-2xl border border-slate-800 text-xl mt-2">
                    <?php 
                    $emojis = [
                        '⌚', '📱', '👛', '👜', '🕶️', '👔', '👠', '💍', '🎁', '💎', 
                        '🎧', '👟', '👑', '👗', '💼', '👝', '⏱️', '⚙️', '🧸', '🧼', 
                        '🛍️', '📦', '🏷️', '🔥', '✨', '⭐', '👕', '👖', '🧢', '🍫'
                    ];
                    foreach ($emojis as $em): ?>
                    <button type="button" onclick="document.getElementById('formEmoji').value='<?= $em ?>';" class="w-8 h-8 rounded-lg hover:bg-slate-800 flex items-center justify-center transition hover:scale-125 select-none"><?= $em ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <label class="block text-slate-300 font-bold mb-1">Display Order (হোম পেজে সিরিয়াল নং) *</label>
                <input type="number" name="display_order" id="formOrder" value="1" min="1" max="99" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-bold outline-none focus:border-indigo-500">
                <p class="text-[10px] text-slate-500 mt-1">1 = First, 2 = Second, 3 = Third in the Home Screen grid.</p>
            </div>

            <!-- Home Screen Inclusion Checkbox -->
            <div class="p-3.5 bg-slate-950 border border-slate-800 rounded-2xl">
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="show_on_homepage" id="formShowOnHome" value="1" checked class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <span class="font-bold text-white block text-xs">Show in Home Screen "Browse Top Categories"</span>
                        <span class="text-[10px] text-slate-400 block">Check to feature this category on the Homepage.</span>
                    </div>
                </label>
            </div>

            <div class="pt-2 flex items-center gap-2">
                <button type="submit" id="formSubmitBtn" class="flex-1 py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg transition">
                    + Save Category
                </button>
                <button type="button" id="formCancelBtn" onclick="resetCatForm()" class="hidden px-4 py-3.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl text-xs">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    <!-- Main Categories List & Homepage Feature Manager -->
    <div class="lg:col-span-2 space-y-6">
        
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
                <div>
                    <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                        <i class="fas fa-layer-group text-indigo-400"></i> Main Product Categories (<?= count($parentCategories) ?>)
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Toggle the <b>"Home Screen"</b> switch to choose which categories appear on the homepage.</p>
                </div>
                <a href="../index.php" target="_blank" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-xl text-indigo-400 font-bold text-xs shrink-0 flex items-center gap-1.5 border border-slate-700">
                    <span>View Home Screen</span> <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach ($parentCategories as $cat): 
                    $emoji = !empty($cat['emoji']) ? $cat['emoji'] : '🛍️';
                    $isOnHome = isset($cat['show_on_homepage']) ? ($cat['show_on_homepage'] == 1) : true;
                ?>
                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex flex-col justify-between text-xs hover:border-indigo-500/50 transition space-y-3">
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Category Emoji Logo Box -->
                            <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center text-2xl shrink-0 shadow-inner">
                                <span class="select-none"><?= htmlspecialchars($emoji) ?></span>
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-extrabold text-white truncate text-sm"><?= htmlspecialchars($cat['name']) ?></h4>
                                <span class="text-slate-400 text-[11px]"><?= $cat['products_count'] ?> Products • Order: #<?= $cat['display_order'] ?></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
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

                    <!-- Quick Home Screen Toggle Bar -->
                    <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between">
                        <span class="text-[11px] text-slate-400">Home Screen Display:</span>
                        <form method="POST" action="categories.php" class="inline">
                            <input type="hidden" name="action" value="toggle_home">
                            <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                            <input type="hidden" name="current_status" value="<?= $isOnHome ? 1 : 0 ?>">
                            <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase flex items-center gap-1.5 <?= $isOnHome ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700' ?>">
                                <i class="fas <?= $isOnHome ? 'fa-circle-check text-emerald-400' : 'fa-circle-xmark text-slate-500' ?>"></i>
                                <span><?= $isOnHome ? 'Visible on Home' : 'Hidden on Home' ?></span>
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
    document.getElementById('formOrder').value = cat.display_order || 1;
    document.getElementById('formParentId').value = cat.parent_id || '';
    document.getElementById('formShowOnHome').checked = (cat.show_on_homepage === 1 || cat.show_on_homepage === '1' || cat.show_on_homepage === undefined);
    document.getElementById('formHeaderTitle').textContent = 'Edit Category #' + cat.id;
    document.getElementById('formSubmitBtn').innerHTML = '✓ Update Category #' + cat.id;
    document.getElementById('formCancelBtn').classList.remove('hidden');
    document.getElementById('categoryForm').scrollIntoView({ behavior: 'smooth' });
}

function resetCatForm() {
    document.getElementById('formAction').value = 'create';
    document.getElementById('formCategoryId').value = '';
    document.getElementById('formName').value = '';
    document.getElementById('formEmoji').value = '⌚';
    document.getElementById('formOrder').value = 1;
    document.getElementById('formParentId').value = '';
    document.getElementById('formShowOnHome').checked = true;
    document.getElementById('formHeaderTitle').textContent = 'Add Category';
    document.getElementById('formSubmitBtn').innerHTML = '+ Save Category';
    document.getElementById('formCancelBtn').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
