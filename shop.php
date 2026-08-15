<?php
$pageTitle = 'Shop All Products - OnlineBdMart';
require_once 'includes/header.php';

$catSlug = trim($_GET['category'] ?? '');
$search = trim($_GET['search'] ?? '');
$sort = trim($_GET['sort'] ?? 'latest');

$sql = 'SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1';
$params = [];

if ($catSlug) {
    $sql .= ' AND c.slug = ?';
    $params[] = $catSlug;
}
if ($search) {
    $sql .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($sort === 'price_low') $sql .= ' ORDER BY p.price ASC';
elseif ($sort === 'price_high') $sql .= ' ORDER BY p.price DESC';
else $sql .= ' ORDER BY p.id DESC';

try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (Exception $e) {
    $products = [];
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 space-y-3">
                <h3 class="text-xs font-bold uppercase text-slate-900">Categories</h3>
                <div class="space-y-1 text-xs font-semibold">
                    <a href="shop.php" class="flex justify-between px-3 py-2 rounded-xl <?= empty($catSlug) ? 'bg-indigo-50 text-indigo-600 font-bold' : 'text-slate-600 hover:bg-slate-50' ?>">
                        <span>All Products</span>
                    </a>
                    <?php foreach ($categories as $c): ?>
                        <a href="shop.php?category=<?= htmlspecialchars($c['slug']) ?>" class="flex justify-between px-3 py-2 rounded-xl <?= ($catSlug === $c['slug']) ? 'bg-indigo-50 text-indigo-600 font-bold' : 'text-slate-600 hover:bg-slate-50' ?>">
                            <span><?= htmlspecialchars($c['name']) ?></span>
                            <span class="text-slate-400 font-normal">(<?= $c['products_count'] ?>)</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Catalog -->
        <div class="lg:col-span-3 space-y-6">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 flex items-center justify-between text-xs">
                <span>Showing <strong><?= count($products) ?></strong> items</span>
                <form method="GET" action="shop.php" class="flex items-center gap-2">
                    <?php if ($catSlug): ?><input type="hidden" name="category" value="<?= htmlspecialchars($catSlug) ?>"><?php endif; ?>
                    <?php if ($search): ?><input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>"><?php endif; ?>
                    <label class="text-slate-500 font-bold">Sort:</label>
                    <select name="sort" onchange="this.form.submit()" class="border border-slate-200 rounded-xl px-3 py-1.5 bg-slate-50 font-semibold outline-none">
                        <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Newest</option>
                        <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                    </select>
                </form>
            </div>

            <?php if (!empty($products)): ?>
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                    <?php foreach ($products as $p): 
                        $price = ($p['sale_price'] && $p['sale_price'] > 0 && $p['sale_price'] < $p['price']) ? $p['sale_price'] : $p['price'];
                    ?>
                        <div class="group bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl transition flex flex-col justify-between overflow-hidden relative p-4">
                            <?php if ($p['sale_price'] && $p['sale_price'] < $p['price']): ?>
                            <span class="absolute top-3 left-3 z-10 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white">Sale</span>
                            <?php endif; ?>

                            <a href="product.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="relative block aspect-square bg-slate-100 rounded-xl overflow-hidden mb-3">
                                <img src="/<?= ltrim($p['image_path'], '/') ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            </a>

                            <div class="flex-1 flex flex-col justify-between">
                                <div>
                                    <span class="text-[10px] font-bold uppercase text-slate-400"><?= htmlspecialchars($p['category_name'] ?? 'Accessories') ?></span>
                                    <a href="product.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition line-clamp-2 block mt-0.5"><?= htmlspecialchars($p['name']) ?></a>
                                </div>
                                <div class="mt-3 pt-2.5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <span class="text-sm sm:text-base font-black text-indigo-600">৳<?= number_format($price, 2) ?></span>
                                    <button type="button" onclick="addToCart(<?= $p['id'] ?>)" class="w-full sm:w-auto px-3 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white text-xs font-black rounded-xl shadow-md shadow-indigo-600/20 hover:shadow-indigo-600/40 transition flex items-center justify-center gap-1.5 active:scale-95">
                                        <i class="fas fa-bag-shopping text-xs"></i> <span>Add to Bag</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-white p-12 rounded-3xl border border-slate-200 text-center text-slate-400 text-xs">
                    No products found matching your search.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
