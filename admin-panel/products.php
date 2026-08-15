<?php
$adminTitle = 'Product Catalog Management';
require_once __DIR__ . '/header.php';

$msg = '';
$error = '';

try {
    $db = getDB();

    // Handle Add/Edit/Delete
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'create' || $action === 'update') {
            $name = trim($_POST['name'] ?? '');
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            $catId = (int)$_POST['category_id'];
            $price = (float)$_POST['price'];
            $salePrice = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
            $isWholesale = isset($_POST['is_wholesale']) ? 1 : 0;
            $wholesalePrice = !empty($_POST['wholesale_price']) ? (float)$_POST['wholesale_price'] : null;
            $wholesaleMoq = !empty($_POST['wholesale_moq']) ? (int)$_POST['wholesale_moq'] : 5;
            $stock = (int)$_POST['stock_quantity'];
            $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $imagePath = trim($_POST['image_path'] ?? 'images/products/watch-1.jpg');
            $desc = trim($_POST['description'] ?? '');

            if ($action === 'create') {
                $stmt = $db->prepare("INSERT INTO products (name, slug, category_id, price, sale_price, is_wholesale, wholesale_price, wholesale_moq, stock_quantity, is_featured, is_active, image_path, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                $stmt->execute([$name, $slug, $catId, $price, $salePrice, $isWholesale, $wholesalePrice, $wholesaleMoq, $stock, $isFeatured, $isActive, $imagePath, $desc]);
                $msg = 'Product added successfully!';
            } else {
                $id = (int)$_POST['product_id'];
                $stmt = $db->prepare("UPDATE products SET name = ?, slug = ?, category_id = ?, price = ?, sale_price = ?, is_wholesale = ?, wholesale_price = ?, wholesale_moq = ?, stock_quantity = ?, is_featured = ?, is_active = ?, image_path = ?, description = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $slug, $catId, $price, $salePrice, $isWholesale, $wholesalePrice, $wholesaleMoq, $stock, $isFeatured, $isActive, $imagePath, $desc, $id]);
                $msg = 'Product updated successfully!';
            }
        } elseif ($action === 'delete') {
            $id = (int)$_POST['product_id'];
            $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $msg = 'Product deleted!';
        }
    }

    $products = $db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();
    $categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
    $products = [];
    $categories = [];
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="p-4 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-400 text-xs font-bold">
    <i class="fas fa-circle-exclamation mr-1"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<!-- Add New Product Form Container -->
<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
    <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
        <i class="fas fa-plus-circle text-indigo-400"></i> Add New Catalog Product
    </h3>

    <form method="POST" action="products.php" class="space-y-4 text-xs">
        <input type="hidden" name="action" value="create">

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Product Title *</label>
                <input type="text" name="name" required placeholder="e.g. Naviforce Chronograph Luxury Watch" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Category *</label>
                <select name="category_id" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Stock Quantity</label>
                <input type="number" name="stock_quantity" value="50" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Regular Price (৳) *</label>
                <input type="number" step="0.01" name="price" required placeholder="2500" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none font-bold">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Sale Discount Price (৳)</label>
                <input type="number" step="0.01" name="sale_price" placeholder="1850" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div>
                <label class="block text-amber-400 font-bold mb-1">Wholesale Price (৳)</label>
                <input type="number" step="0.01" name="wholesale_price" placeholder="1400" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div>
                <label class="block text-amber-400 font-bold mb-1">Wholesale MOQ</label>
                <input type="number" name="wholesale_moq" value="5" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Image Relative Path</label>
                <input type="text" name="image_path" value="images/products/watch-1.jpg" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div class="flex items-center gap-6 pt-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_wholesale" value="1" checked class="rounded text-amber-500">
                    <span class="text-amber-300 font-bold">Enable Wholesale</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" checked class="rounded text-indigo-500">
                    <span class="text-slate-300">Featured</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded text-emerald-500">
                    <span class="text-slate-300">Active</span>
                </label>
            </div>
        </div>

        <div>
            <label class="block text-slate-300 font-bold mb-1">Short Description</label>
            <textarea name="description" rows="2" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none"></textarea>
        </div>

        <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg transition">
            Save Product to Catalog
        </button>
    </form>
</div>

<!-- Products Table -->
<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
    <h3 class="text-sm font-extrabold text-white">All Products (<?= count($products) ?>)</h3>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase text-[10px]">
                    <th class="py-3">Item</th>
                    <th class="py-3">Category</th>
                    <th class="py-3">Retail Price</th>
                    <th class="py-3">Wholesale Rate</th>
                    <th class="py-3">Stock</th>
                    <th class="py-3">Status</th>
                    <th class="py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                <?php foreach ($products as $p): ?>
                <tr class="hover:bg-slate-800/40 transition">
                    <td class="py-3 flex items-center gap-3">
                        <img src="/<?= ltrim($p['image_path'], '/') ?>" class="w-10 h-10 object-cover rounded-xl bg-slate-950 border border-slate-800 shrink-0">
                        <div>
                            <span class="font-bold text-white block truncate max-w-xs"><?= htmlspecialchars($p['name']) ?></span>
                            <span class="text-[10px] text-slate-400"><?= $p['slug'] ?></span>
                        </div>
                    </td>
                    <td class="py-3 text-slate-400"><?= htmlspecialchars($p['category_name'] ?? 'Accessories') ?></td>
                    <td class="py-3 font-bold text-white">
                        ৳<?= number_format($p['price'], 2) ?>
                        <?php if ($p['sale_price']): ?>
                        <span class="text-rose-400 text-[10px] block">Sale: ৳<?= number_format($p['sale_price'], 2) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3 text-amber-400 font-bold">
                        <?php if ($p['is_wholesale'] && $p['wholesale_price']): ?>
                        ৳<?= number_format($p['wholesale_price'], 2) ?> <span class="text-[10px] text-slate-400 block">MOQ: <?= $p['wholesale_moq'] ?> pcs</span>
                        <?php else: ?>
                        <span class="text-slate-500">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3 text-slate-300"><?= $p['stock_quantity'] ?></td>
                    <td class="py-3">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $p['is_active'] ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-800 text-slate-500' ?>">
                            <?= $p['is_active'] ? 'Active' : 'Disabled' ?>
                        </span>
                    </td>
                    <td class="py-3 text-right">
                        <form method="POST" action="products.php" onsubmit="return confirm('Delete this product?');" class="inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="p-1.5 bg-rose-500/20 text-rose-400 hover:bg-rose-500/40 rounded-lg text-xs"><i class="fas fa-trash-can"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
