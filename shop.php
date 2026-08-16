<?php
$pageTitle = 'Shop All Products - OnlineBdMart • Online Shopping BD';
require_once 'includes/header.php';

$catSlug = trim($_GET['category'] ?? '');
$search = trim($_GET['search'] ?? '');
$sort = trim($_GET['sort'] ?? 'latest');

function getCategoryEmojiFallback($cat) {
    $emoji = trim((string)($cat['emoji'] ?? ''));
    if (!empty($emoji)) return $emoji;
    $slug = strtolower($cat['slug'] ?? ($cat['name'] ?? ''));
    if (str_contains($slug, 'watch') || str_contains($slug, 'clock')) return '⌚';
    if (str_contains($slug, 'gadget') || str_contains($slug, 'phone') || str_contains($slug, 'mobile')) return '📱';
    if (str_contains($slug, 'wallet') || str_contains($slug, 'leather')) return '👛';
    if (str_contains($slug, 'bag') || str_contains($slug, 'backpack')) return '👜';
    if (str_contains($slug, 'glass') || str_contains($slug, 'sunglass')) return '🕶️';
    if (str_contains($slug, 'men') || str_contains($slug, 'shirt') || str_contains($slug, 'panjabi')) return '👔';
    if (str_contains($slug, 'women') || str_contains($slug, 'dress') || str_contains($slug, 'saree')) return '👗';
    if (str_contains($slug, 'shoe') || str_contains($slug, 'sneaker') || str_contains($slug, 'footwear')) return '👟';
    if (str_contains($slug, 'ring') || str_contains($slug, 'jewelry') || str_contains($slug, 'necklace')) return '💍';
    if (str_contains($slug, 'perfume') || str_contains($slug, 'fragrance') || str_contains($slug, 'attar')) return '🧴';
    return '🛍️';
}

$parentCategories = [];
$activeCategoryInfo = null;
$activeParentId = null;

try {
    $db = getDB();
    $allCats = $db->query("SELECT * FROM categories ORDER BY display_order ASC, id ASC")->fetchAll();

    $prodCounts = [];
    $subProdCounts = [];
    try {
        $cStmt = $db->query("SELECT category_id, COUNT(*) as cnt FROM products WHERE is_active = 1 GROUP BY category_id");
        while ($r = $cStmt->fetch()) {
            if ($r['category_id']) $prodCounts[(int)$r['category_id']] = (int)$r['cnt'];
        }
        $scStmt = $db->query("SELECT subcategory_id, COUNT(*) as cnt FROM products WHERE is_active = 1 AND subcategory_id IS NOT NULL GROUP BY subcategory_id");
        while ($r = $scStmt->fetch()) {
            if ($r['subcategory_id']) $subProdCounts[(int)$r['subcategory_id']] = (int)$r['cnt'];
        }
    } catch (Exception $e) {}

    $parentMap = [];
    $subMap = [];

    foreach ($allCats as $cat) {
        $cid = (int)$cat['id'];
        $cat['products_count'] = ($prodCounts[$cid] ?? 0) + ($subProdCounts[$cid] ?? 0);
        $cat['subcategories'] = [];
        
        if (empty($cat['parent_id']) || $cat['parent_id'] == 0) {
            $parentMap[$cid] = $cat;
        } else {
            $subMap[(int)$cat['parent_id']][] = $cat;
        }
    }

    foreach ($parentMap as $pid => &$pCat) {
        if (isset($subMap[$pid])) {
            $pCat['subcategories'] = $subMap[$pid];
            $totalSub = 0;
            foreach ($pCat['subcategories'] as $sc) {
                $totalSub += ($sc['products_count'] ?? 0);
            }
            $pCat['total_products_count'] = $pCat['products_count'] + $totalSub;
        } else {
            $pCat['total_products_count'] = $pCat['products_count'];
        }
    }
    unset($pCat);

    $parentCategories = array_values($parentMap);

} catch (Exception $ex) {
    $parentCategories = [];
}

// Detect active category or active subcategory
if (!empty($catSlug) && !empty($parentCategories)) {
    foreach ($parentCategories as $p) {
        if (($p['slug'] ?? '') === $catSlug) {
            $activeCategoryInfo = $p;
            $activeCategoryInfo['is_parent'] = true;
            $activeParentId = $p['id'];
            break;
        }
        if (!empty($p['subcategories'])) {
            foreach ($p['subcategories'] as $sc) {
                if (($sc['slug'] ?? '') === $catSlug) {
                    $activeCategoryInfo = $sc;
                    $activeCategoryInfo['is_parent'] = false;
                    $activeCategoryInfo['parent_name'] = $p['name'];
                    $activeCategoryInfo['parent_slug'] = $p['slug'];
                    $activeParentId = $p['id'];
                    break 2;
                }
            }
        }
    }
}

// Build Products Query
$sql = 'SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1';
$params = [];

if (!empty($activeCategoryInfo)) {
    if (!empty($activeCategoryInfo['is_parent']) && !empty($activeCategoryInfo['subcategories'])) {
        $ids = [(int)$activeCategoryInfo['id']];
        foreach ($activeCategoryInfo['subcategories'] as $sc) {
            $ids[] = (int)$sc['id'];
        }
        $inList = implode(',', array_map('intval', $ids));
        $sql .= " AND (p.category_id IN ($inList) OR p.subcategory_id IN ($inList) OR c.slug = ?)";
        $params[] = $catSlug;
    } else {
        $catId = (int)$activeCategoryInfo['id'];
        $sql .= " AND (p.category_id = ? OR p.subcategory_id = ? OR c.slug = ?)";
        $params[] = $catId;
        $params[] = $catId;
        $params[] = $catSlug;
    }
} elseif ($catSlug) {
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
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (Exception $e) {
    $products = [];
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    <!-- Active Category / Breadcrumb Header (If category selected) -->
    <?php if ($activeCategoryInfo): 
        $cEmoji = getCategoryEmojiFallback($activeCategoryInfo);
    ?>
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-3xl p-6 sm:p-8 mb-8 shadow-lg border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4 text-center sm:text-left">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-indigo-600/30 border border-indigo-500/30 flex items-center justify-center text-3xl shrink-0 shadow-inner">
                <span><?= htmlspecialchars($cEmoji) ?></span>
            </div>
            <div>
                <div class="flex items-center gap-2 justify-center sm:justify-start text-[11px] uppercase tracking-widest text-indigo-400 font-extrabold">
                    <span>Category</span>
                    <?php if (!empty($activeCategoryInfo['parent_name'])): ?>
                        <span>&rsaquo;</span>
                        <a href="shop.php?category=<?= htmlspecialchars($activeCategoryInfo['parent_slug']) ?>" class="hover:underline text-slate-300"><?= htmlspecialchars($activeCategoryInfo['parent_name']) ?></a>
                    <?php endif; ?>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold font-serif"><?= htmlspecialchars($activeCategoryInfo['name']) ?></h1>
                <p class="text-xs text-slate-300 mt-1"><?= htmlspecialchars($activeCategoryInfo['description'] ?? 'Explore all products in this category with official warranty & fast delivery in BD.') ?></p>
            </div>
        </div>
        <a href="shop.php" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl text-xs font-bold transition flex items-center gap-2 border border-slate-700 shrink-0">
            <i class="fas fa-xmark"></i> Clear Filter
        </a>
    </div>
    <?php endif; ?>

    <!-- Mobile Horizontal Category Quick Scroll Bar -->
    <div class="block lg:hidden mb-6 space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-xs font-black uppercase tracking-wider text-slate-500">Categories</span>
            <a href="categories.php" class="text-xs font-bold text-indigo-600 hover:underline">All &rarr;</a>
        </div>
        
        <!-- Main Category Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-thin scrollbar-thumb-indigo-200">
            <a href="shop.php<?= $search ? '?search='.urlencode($search) : '' ?>" class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition shrink-0 <?= empty($catSlug) ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' ?>">
                🛍️ All Products
            </a>
            <?php if (!empty($parentCategories)): ?>
                <?php foreach ($parentCategories as $p): 
                    $pEmoji = getCategoryEmojiFallback($p);
                    $isSelected = ($catSlug === $p['slug'] || $activeParentId == $p['id']);
                ?>
                <a href="shop.php?category=<?= htmlspecialchars($p['slug']) ?><?= $search ? '&search='.urlencode($search) : '' ?>" class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap flex items-center gap-1.5 transition shrink-0 <?= $isSelected ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' ?>">
                    <span><?= htmlspecialchars($pEmoji) ?></span>
                    <span><?= htmlspecialchars($p['name']) ?></span>
                    <span class="text-[10px] opacity-75">(<?= $p['total_products_count'] ?? 0 ?>)</span>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Mobile Subcategory Secondary Pills (If active parent category has subcategories) -->
        <?php 
        $activeParentCatObj = null;
        if ($activeParentId && !empty($parentCategories)) {
            foreach ($parentCategories as $p) {
                if ($p['id'] == $activeParentId) {
                    $activeParentCatObj = $p;
                    break;
                }
            }
        }
        if ($activeParentCatObj && !empty($activeParentCatObj['subcategories'])):
        ?>
        <div class="p-3 bg-indigo-50/80 rounded-2xl border border-indigo-100 space-y-2">
            <div class="text-[11px] font-extrabold text-indigo-900 flex items-center gap-1.5">
                <i class="fas fa-folder-tree text-indigo-600"></i>
                <span>Subcategories of <strong><?= htmlspecialchars($activeParentCatObj['name']) ?></strong>:</span>
            </div>
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-thin">
                <a href="shop.php?category=<?= htmlspecialchars($activeParentCatObj['slug']) ?>" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold whitespace-nowrap transition shrink-0 <?= ($catSlug === $activeParentCatObj['slug']) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200' ?>">
                    All <?= htmlspecialchars($activeParentCatObj['name']) ?>
                </a>
                <?php foreach ($activeParentCatObj['subcategories'] as $sub): 
                    $subEmoji = getCategoryEmojiFallback($sub);
                    $isSubActive = ($catSlug === $sub['slug']);
                ?>
                <a href="shop.php?category=<?= htmlspecialchars($sub['slug']) ?>" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold whitespace-nowrap flex items-center gap-1 transition shrink-0 <?= $isSubActive ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200' ?>">
                    <span><?= htmlspecialchars($subEmoji) ?></span>
                    <span><?= htmlspecialchars($sub['name']) ?></span>
                    <span class="text-[9px] opacity-75">(<?= $sub['products_count'] ?? 0 ?>)</span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Desktop Sidebar with Interactive Subcategory Dropdowns / Accordions -->
        <div class="hidden lg:block lg:col-span-1 space-y-6">
            <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <i class="fas fa-layer-group text-indigo-600"></i> Categories
                    </h3>
                    <a href="categories.php" class="text-[11px] font-bold text-indigo-600 hover:underline">All &rarr;</a>
                </div>

                <!-- All Products Reset Link -->
                <a href="shop.php<?= $search ? '?search='.urlencode($search) : '' ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl transition font-bold text-xs <?= empty($catSlug) ? 'bg-indigo-600 text-white shadow' : 'bg-slate-50 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 border border-slate-100' ?>">
                    <span class="flex items-center gap-2"><span>🛍️</span> <span>All Products</span></span>
                    <i class="fas fa-chevron-right text-[10px] <?= empty($catSlug) ? 'text-white' : 'text-slate-300' ?>"></i>
                </a>

                <!-- Hierarchical Category List with Dropdowns -->
                <div class="space-y-2">
                    <?php if (!empty($parentCategories)): ?>
                        <?php foreach ($parentCategories as $p): 
                            $pEmoji = getCategoryEmojiFallback($p);
                            $hasSubs = !empty($p['subcategories']);
                            $isParentActive = ($catSlug === $p['slug']);
                            $isSubActiveInThis = false;
                            if ($hasSubs) {
                                foreach ($p['subcategories'] as $sc) {
                                    if ($catSlug === $sc['slug']) {
                                        $isSubActiveInThis = true;
                                        break;
                                    }
                                }
                            }
                            $shouldExpand = ($isParentActive || $isSubActiveInThis || $activeParentId == $p['id']);
                        ?>
                        <div class="rounded-2xl border border-slate-100 bg-white overflow-hidden transition-all duration-200 hover:border-slate-200">
                            <!-- Parent Category Row -->
                            <div class="flex items-center justify-between p-2 <?= ($isParentActive && !$isSubActiveInThis) ? 'bg-indigo-50/90 text-indigo-700 font-black' : 'text-slate-700 hover:bg-slate-50' ?>">
                                <a href="shop.php?category=<?= htmlspecialchars($p['slug']) ?><?= $search ? '&search='.urlencode($search) : '' ?>" class="flex items-center gap-2.5 min-w-0 flex-1 py-1 px-1.5">
                                    <span class="text-lg shrink-0 select-none"><?= htmlspecialchars($pEmoji) ?></span>
                                    <span class="truncate text-xs font-bold"><?= htmlspecialchars($p['name']) ?></span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold shrink-0 <?= ($isParentActive && !$isSubActiveInThis) ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-500' ?>">
                                        <?= $p['total_products_count'] ?? 0 ?>
                                    </span>
                                </a>

                                <?php if ($hasSubs): ?>
                                <button type="button" 
                                        onclick="toggleSubmenu(<?= $p['id'] ?>, event)" 
                                        class="p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-100/60 transition ml-1 shrink-0 cursor-pointer" 
                                        title="Toggle subcategories for <?= htmlspecialchars($p['name']) ?>">
                                    <i id="subcat-icon-<?= $p['id'] ?>" class="fas fa-chevron-down text-[11px] transform transition-transform duration-300 <?= $shouldExpand ? 'rotate-180 text-indigo-600' : '' ?>"></i>
                                </button>
                                <?php endif; ?>
                            </div>

                            <!-- Subcategories Dropdown Accordion List -->
                            <?php if ($hasSubs): ?>
                            <div id="subcat-list-<?= $p['id'] ?>" class="space-y-1 py-2 px-2.5 bg-slate-50/80 border-t border-slate-100 <?= $shouldExpand ? '' : 'hidden' ?>">
                                <a href="shop.php?category=<?= htmlspecialchars($p['slug']) ?><?= $search ? '&search='.urlencode($search) : '' ?>" class="flex items-center justify-between px-3 py-1.5 rounded-xl text-[11px] font-bold transition <?= ($catSlug === $p['slug']) ? 'text-indigo-600 bg-indigo-100/60 font-black' : 'text-slate-600 hover:text-slate-900 hover:bg-white' ?>">
                                    <span class="flex items-center gap-1.5">
                                        <i class="fas fa-arrow-right text-[9px] text-indigo-500"></i>
                                        <span>All <?= htmlspecialchars($p['name']) ?></span>
                                    </span>
                                    <span class="text-[10px] text-slate-400">(<?= $p['total_products_count'] ?? 0 ?>)</span>
                                </a>
                                <?php foreach ($p['subcategories'] as $sub): 
                                    $subEmoji = getCategoryEmojiFallback($sub);
                                    $isThisSubActive = ($catSlug === $sub['slug']);
                                ?>
                                <a href="shop.php?category=<?= htmlspecialchars($sub['slug']) ?><?= $search ? '&search='.urlencode($search) : '' ?>" class="flex items-center justify-between px-3 py-1.5 rounded-xl text-[11px] font-bold transition <?= $isThisSubActive ? 'text-indigo-600 bg-indigo-100/80 font-black shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white' ?>">
                                    <span class="flex items-center gap-2 truncate">
                                        <span class="text-xs"><?= htmlspecialchars($subEmoji) ?></span>
                                        <span class="truncate"><?= htmlspecialchars($sub['name']) ?></span>
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold <?= $isThisSubActive ? 'bg-indigo-600 text-white' : 'bg-slate-200/70 text-slate-500' ?>">
                                        <?= $sub['products_count'] ?? 0 ?>
                                    </span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
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
                <span class="text-slate-600">Showing <strong class="text-slate-900"><?= count($products) ?></strong> products <?= $catSlug ? 'in <strong>' . htmlspecialchars($activeCategoryInfo['name'] ?? $catSlug) . '</strong>' : '' ?></span>
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

<script>
function toggleSubmenu(catId, event) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }
    var subEl = document.getElementById('subcat-list-' + catId);
    var iconEl = document.getElementById('subcat-icon-' + catId);
    if (!subEl) return;
    if (subEl.classList.contains('hidden')) {
        subEl.classList.remove('hidden');
        if (iconEl) iconEl.classList.add('rotate-180');
    } else {
        subEl.classList.add('hidden');
        if (iconEl) iconEl.classList.remove('rotate-180');
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
