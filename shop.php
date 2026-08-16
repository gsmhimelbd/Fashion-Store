<?php
$pageTitle = 'Shop All Products - OnlineBdMart • Online Shopping BD';
require_once 'includes/header.php';

$catSlug = trim($_GET['category'] ?? '');
$search = trim($_GET['search'] ?? '');
$sort = trim($_GET['sort'] ?? 'latest');

// Ensure categories are loaded directly from database
if (empty($categories)) {
    try {
        $db = getDB();
        $catDirect = $db->query("SELECT * FROM categories")->fetchAll();
        if (!empty($catDirect)) {
            $categories = $catDirect;
            $counts = [];
            try {
                $pStmt = $db->query("SELECT category_id, COUNT(*) as c FROM products WHERE is_active = 1 GROUP BY category_id");
                while ($r = $pStmt->fetch()) {
                    $counts[$r['category_id']] = (int)$r['c'];
                }
            } catch (Exception $e) {}

            foreach ($categories as &$cd) {
                $cid = $cd['id'] ?? 0;
                $cd['products_count'] = $counts[$cid] ?? 0;
            }
            unset($cd);
        }
    } catch (Exception $ex) {}
}

// Safely sort categories by display_order
if (!empty($categories) && is_array($categories)) {
    usort($categories, function($a, $b) {
        $o1 = (int)($a['display_order'] ?? 1);
        $o2 = (int)($b['display_order'] ?? 1);
        if ($o1 === $o2) return ((int)($a['id'] ?? 0)) <=> ((int)($b['id'] ?? 0));
        return $o1 <=> $o2;
    });
}

$sql = 'SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1';
$params = [];

if ($catSlug) {
    $sql .= ' AND (c.slug = ? OR p.category_id = (SELECT id FROM categories WHERE slug = ? LIMIT 1))';
    $params[] = $catSlug;
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

// Find current active category info
$currentCategory = null;
if (!empty($catSlug) && !empty($categories)) {
    foreach ($categories as $c) {
        if (($c['slug'] ?? '') === $catSlug) {
            $currentCategory = $c;
            break;
        }
    }
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    <!-- Active Category / Breadcrumb Header (If category selected) -->
    <?php if ($currentCategory): 
        $cEmoji = trim((string)($currentCategory['emoji'] ?? ''));
        if (empty($cEmoji)) {
            $slug = strtolower($currentCategory['slug'] ?? ($currentCategory['name'] ?? ''));
            if (str_contains($slug, 'watch') || str_contains($slug, 'clock')) $cEmoji = '⌚';
            elseif (str_contains($slug, 'gadget') || str_contains($slug, 'phone')) $cEmoji = '📱';
            elseif (str_contains($slug, 'wallet') || str_contains($slug, 'leather')) $cEmoji = '👛';
            elseif (str_contains($slug, 'bag')) $cEmoji = '👜';
            elseif (str_contains($slug, 'glass') || str_contains($slug, 'sunglass')) $cEmoji = '🕶️';
            elseif (str_contains($slug, 'men')) $cEmoji = '👔';
            elseif (str_contains($slug, 'women')) $cEmoji = '👗';
            else $cEmoji = '🛍️';
        }
    ?>
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-3xl p-6 sm:p-8 mb-8 shadow-lg border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4 text-center sm:text-left">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-indigo-600/30 border border-indigo-500/30 flex items-center justify-center text-3xl shrink-0 shadow-inner">
                <span><?= htmlspecialchars($cEmoji) ?></span>
            </div>
            <div>
                <span class="text-[11px] uppercase tracking-widest text-indigo-400 font-extrabold block">Category Filter</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold font-serif"><?= htmlspecialchars($currentCategory['name']) ?></h1>
                <p class="text-xs text-slate-300 mt-1"><?= htmlspecialchars($currentCategory['description'] ?? 'Explore all products under this category.') ?></p>
            </div>
        </div>
        <a href="shop.php" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl text-xs font-bold transition flex items-center gap-2 border border-slate-700 shrink-0">
            <i class="fas fa-xmark"></i> Clear Filter
        </a>
    </div>
    <?php endif; ?>

    <!-- Mobile Horizontal Category Quick Scroll Bar -->
    <div class="block lg:hidden mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-black uppercase tracking-wider text-slate-500">Categories</span>
            <a href="categories.php" class="text-xs font-bold text-indigo-600 hover:underline">All &rarr;</a>
        </div>
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-indigo-200">
            <a href="shop.php<?= $search ? '?search='.urlencode($search) : '' ?>" class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition <?= empty($catSlug) ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' ?>">
                🛍️ All Products
            </a>
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $c): 
                    $cEmoji = trim((string)($c['emoji'] ?? ''));
                    if (empty($cEmoji)) {
                        $slug = strtolower($c['slug'] ?? ($c['name'] ?? ''));
                        if (str_contains($slug, 'watch') || str_contains($slug, 'clock')) $cEmoji = '⌚';
                        elseif (str_contains($slug, 'gadget') || str_contains($slug, 'phone')) $cEmoji = '📱';
                        elseif (str_contains($slug, 'wallet') || str_contains($slug, 'leather')) $cEmoji = '👛';
                        elseif (str_contains($slug, 'bag')) $cEmoji = '👜';
                        elseif (str_contains($slug, 'glass') || str_contains($slug, 'sunglass')) $cEmoji = '🕶️';
                        elseif (str_contains($slug, 'men')) $cEmoji = '👔';
                        elseif (str_contains($slug, 'women')) $cEmoji = '👗';
                        else $cEmoji = '🛍️';
                    }
                    $isSelected = ($catSlug === $c['slug']);
                ?>
                <a href="shop.php?category=<?= htmlspecialchars($c['slug']) ?><?= $search ? '&search='.urlencode($search) : '' ?>" class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap flex items-center gap-1.5 transition <?= $isSelected ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' ?>">
                    <span><?= htmlspecialchars($cEmoji) ?></span>
                    <span><?= htmlspecialchars($c['name']) ?></span>
                    <span class="text-[10px] opacity-75">(<?= $c['products_count'] ?? 0 ?>)</span>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Desktop Sidebar -->
        <div class="hidden lg:block lg:col-span-1 space-y-6">
            <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <i class="fas fa-layer-group text-indigo-600"></i> Categories
                    </h3>
                    <a href="categories.php" class="text-[11px] font-bold text-indigo-600 hover:underline">View All</a>
                </div>

                <div class="space-y-1.5 text-xs font-bold">
                    <a href="shop.php<?= $search ? '?search='.urlencode($search) : '' ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition <?= empty($catSlug) ? 'bg-indigo-600 text-white shadow' : 'text-slate-700 hover:bg-slate-50' ?>">
                        <span class="flex items-center gap-2"><span>🛍️</span> <span>All Products</span></span>
                        <i class="fas fa-chevron-right text-[10px] <?= empty($catSlug) ? 'text-white' : 'text-slate-300' ?>"></i>
                    </a>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $c): 
                            $cEmoji = trim((string)($c['emoji'] ?? ''));
                            if (empty($cEmoji)) {
                                $slug = strtolower($c['slug'] ?? ($c['name'] ?? ''));
                                if (str_contains($slug, 'watch') || str_contains($slug, 'clock')) $cEmoji = '⌚';
                                elseif (str_contains($slug, 'gadget') || str_contains($slug, 'phone')) $cEmoji = '📱';
                                elseif (str_contains($slug, 'wallet') || str_contains($slug, 'leather')) $cEmoji = '👛';
                                elseif (str_contains($slug, 'bag')) $cEmoji = '👜';
                                elseif (str_contains($slug, 'glass') || str_contains($slug, 'sunglass')) $cEmoji = '🕶️';
                                elseif (str_contains($slug, 'men')) $cEmoji = '👔';
                                elseif (str_contains($slug, 'women')) $cEmoji = '👗';
                                else $cEmoji = '🛍️';
                            }
                            $isSelected = ($catSlug === $c['slug']);
                        ?>
                            <a href="shop.php?category=<?= htmlspecialchars($c['slug']) ?><?= $search ? '&search='.urlencode($search) : '' ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition <?= $isSelected ? 'bg-indigo-600 text-white shadow' : 'text-slate-700 hover:bg-slate-50' ?>">
                                <span class="flex items-center gap-2 truncate">
                                    <span class="text-base select-none"><?= htmlspecialchars($cEmoji) ?></span>
                                    <span class="truncate"><?= htmlspecialchars($c['name']) ?></span>
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold shrink-0 <?= $isSelected ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' ?>">
                                    <?= $c['products_count'] ?? 0 ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-xs text-slate-400 p-2">No categories found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Wholesale Banner in Sidebar -->
            <div class="p-5 rounded-3xl bg-gradient-to-br from-amber-500 to-amber-600 text-slate-950 space-y-3 shadow-md">
                <span class="px-2.5 py-1 rounded-full bg-slate-950 text-amber-300 text-[10px] font-black uppercase">B2B Wholesale</span>
                <h4 class="font-extrabold text-sm leading-tight">Need Bulk or Reseller Pricing?</h4>
                <p class="text-xs text-slate-900/80 font-medium">Order 5+ items and get exclusive wholesale rates with fast courier delivery across Bangladesh.</p>
                <a href="wholesale.php" class="block w-full py-2 bg-slate-950 hover:bg-slate-900 text-white text-center rounded-xl font-black text-xs transition">
                    Explore Wholesale &rarr;
                </a>
            </div>
        </div>

        <!-- Catalog Section -->
        <div class="lg:col-span-3 space-y-6">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs shadow-sm">
                <span class="text-slate-600">Showing <strong class="text-slate-900"><?= count($products) ?></strong> products <?= $catSlug ? 'in <strong>' . htmlspecialchars($currentCategory['name'] ?? $catSlug) . '</strong>' : '' ?></span>
                <form method="GET" action="shop.php" class="flex items-center gap-2">
                    <?php if ($catSlug): ?><input type="hidden" name="category" value="<?= htmlspecialchars($catSlug) ?>"><?php endif; ?>
                    <?php if ($search): ?><input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>"><?php endif; ?>
                    <label class="text-slate-500 font-bold shrink-0">Sort By:</label>
                    <select name="sort" onchange="this.form.submit()" class="border border-slate-200 rounded-xl px-3 py-1.5 bg-slate-50 font-bold text-slate-700 outline-none cursor-pointer focus:border-indigo-500">
                        <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Newest Arrival</option>
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
                        <div class="group bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:border-indigo-500/50 transition flex flex-col justify-between overflow-hidden relative p-4">
                            <?php if ($p['sale_price'] && $p['sale_price'] < $p['price']): ?>
                            <span class="absolute top-3 left-3 z-10 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white shadow">Sale</span>
                            <?php endif; ?>

                            <a href="product.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="relative block aspect-square bg-slate-100 rounded-2xl overflow-hidden mb-3">
                                <img src="/<?= ltrim($p['image_path'], '/') ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            </a>

                            <div class="flex-1 flex flex-col justify-between space-y-3">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 block"><?= htmlspecialchars($p['category_name'] ?? 'Accessories') ?></span>
                                    <a href="product.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition line-clamp-2 block mt-0.5"><?= htmlspecialchars($p['name']) ?></a>
                                </div>
                                <div class="pt-2.5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <span class="text-sm sm:text-base font-black text-slate-900">৳<?= number_format($price, 2) ?></span>
                                    <button type="button" onclick="addToCart(<?= $p['id'] ?>)" class="w-full sm:w-auto px-3.5 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white text-xs font-black rounded-xl shadow-md shadow-indigo-600/20 hover:shadow-indigo-600/40 transition flex items-center justify-center gap-1.5 active:scale-95">
                                        <i class="fas fa-bag-shopping text-xs"></i> <span>Add to Bag</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-white p-12 rounded-3xl border border-slate-200 text-center text-slate-400 text-xs space-y-3">
                    <i class="fas fa-box-open text-4xl text-slate-300"></i>
                    <p class="font-bold text-slate-700 text-sm">No products found matching your filter.</p>
                    <p class="text-xs text-slate-400">Try selecting a different category or clearing your search filters.</p>
                    <a href="shop.php" class="px-5 py-2.5 bg-indigo-600 text-white font-bold rounded-xl text-xs inline-block">View All Products</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
