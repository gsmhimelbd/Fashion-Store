<?php
require_once 'config/database.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) {
    header('Location: shop.php');
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug, sc.name as subcategory_name FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN categories sc ON p.subcategory_id = sc.id WHERE p.slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: shop.php');
        exit;
    }

    $relatedStmt = $db->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND is_active = 1 LIMIT 4");
    $relatedStmt->execute([$product['category_id'], $product['id']]);
    $relatedProducts = $relatedStmt->fetchAll();
} catch (Exception $e) {
    header('Location: shop.php');
    exit;
}

$pageTitle = htmlspecialchars($product['name']) . ' - OnlineBdMart';
require_once 'includes/header.php';

$price = ($product['sale_price'] && $product['sale_price'] > 0 && $product['sale_price'] < $product['price']) ? $product['sale_price'] : $product['price'];

// Gallery images array
$gallery = [];
if (!empty($product['image_path'])) {
    $gallery[] = '/' . ltrim($product['image_path'], '/');
}
if (!empty($product['gallery_images'])) {
    foreach (explode(',', $product['gallery_images']) as $g) {
        $g = trim($g);
        if ($g && !in_array('/' . ltrim($g, '/'), $gallery)) {
            $gallery[] = '/' . ltrim($g, '/');
        }
    }
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 mb-8 overflow-x-auto whitespace-nowrap">
        <a href="index.php" class="hover:text-indigo-600">Home</a>
        <span>/</span>
        <a href="shop.php" class="hover:text-indigo-600">Shop</a>
        <span>/</span>
        <a href="shop.php?category=<?= htmlspecialchars($product['category_slug'] ?? '') ?>" class="hover:text-indigo-600"><?= htmlspecialchars($product['category_name'] ?? 'Accessories') ?></a>
        <?php if (!empty($product['subcategory_name'])): ?>
        <span>/</span>
        <span class="text-indigo-600 font-semibold"><?= htmlspecialchars($product['subcategory_name']) ?></span>
        <?php endif; ?>
        <span>/</span>
        <span class="text-slate-900 font-bold truncate max-w-xs"><?= htmlspecialchars($product['name']) ?></span>
    </nav>

    <!-- Main Product Presentation Box -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-10 shadow-sm mb-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            
            <!-- Left: Gallery & Zoomable Photo Viewer -->
            <div class="space-y-4">
                <!-- Big Main Zoom Image Container -->
                <div class="relative aspect-square bg-slate-100 rounded-3xl overflow-hidden border border-slate-200 shadow-sm group cursor-crosshair" id="zoomContainer" onmousemove="handleZoom(event)" onmouseleave="resetZoom()">
                    <img id="mainProductImage" src="<?= htmlspecialchars($gallery[0] ?? '/images/products/watch-1.jpg') ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover transition-transform duration-200 origin-center">
                    
                    <span class="absolute top-4 left-4 z-10 px-3 py-1 bg-black/60 backdrop-blur-md text-white rounded-full text-[10px] font-bold pointer-events-none flex items-center gap-1.5">
                        <i class="fas fa-magnifying-glass-plus"></i> Hover to Zoom
                    </span>
                </div>

                <!-- Gallery Thumbnails Strip -->
                <?php if (count($gallery) > 1): ?>
                <div class="flex items-center gap-3 overflow-x-auto pb-2">
                    <?php foreach ($gallery as $idx => $img): ?>
                    <button type="button" onclick="switchProductImage('<?= htmlspecialchars($img) ?>', this)" class="gallery-thumb w-20 h-20 rounded-2xl overflow-hidden border-2 transition shrink-0 bg-slate-50 <?= $idx === 0 ? 'border-indigo-600 ring-2 ring-indigo-600/30' : 'border-slate-200 hover:border-indigo-400' ?>">
                        <img src="<?= htmlspecialchars($img) ?>" class="w-full h-full object-cover">
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right: Details, Pricing, Wholesale & CTAs -->
            <div class="flex flex-col justify-between space-y-6">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full"><?= htmlspecialchars($product['category_name'] ?? 'Accessories') ?></span>
                        <span class="text-xs text-emerald-600 font-bold bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">● In Stock (<?= $product['stock_quantity'] ?? $product['stock'] ?> pcs)</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold font-serif text-slate-900 mt-3"><?= htmlspecialchars($product['name']) ?></h1>
                    
                    <!-- Rating & Reviews -->
                    <div class="flex items-center gap-2 mt-2">
                        <div class="flex text-amber-400 text-xs">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-700">4.9</span>
                        <span class="text-xs text-slate-400">(<?= $product['reviews_count'] ?? 48 ?> Verified Customer Reviews)</span>
                    </div>

                    <!-- Price Block -->
                    <div class="flex items-baseline gap-3 mt-4">
                        <span class="text-3xl sm:text-4xl font-black text-indigo-600">৳<?= number_format($price, 2) ?></span>
                        <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                        <span class="text-lg text-slate-400 line-through">৳<?= number_format($product['price'], 2) ?></span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-600">
                            <?= round((($product['price'] - $product['sale_price']) / $product['price']) * 100) ?>% OFF
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Wholesale Bulk Box -->
                    <?php if ($product['is_wholesale'] && $product['wholesale_price'] > 0): 
                        $moq = $product['wholesale_moq'] ?: 5;
                    ?>
                    <div class="mt-5 p-4 rounded-2xl bg-amber-50 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-xs font-black uppercase text-amber-900 flex items-center gap-1.5"><i class="fas fa-boxes-stacked"></i> Wholesale / B2B Rate Available</span>
                            <p class="text-xs text-amber-800 mt-0.5">Min Order: <strong><?= $moq ?> pcs</strong> @ <strong class="text-emerald-700 text-sm">৳<?= number_format($product['wholesale_price'], 2) ?>/pc</strong></p>
                        </div>
                        <button type="button" onclick="addToCart(<?= $product['id'] ?>, <?= $moq ?>, true)" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-black rounded-xl shadow transition">
                            Add <?= $moq ?> pcs Wholesale
                        </button>
                    </div>
                    <?php endif; ?>

                    <!-- Short Description -->
                    <div class="mt-4 text-xs sm:text-sm text-slate-600 leading-relaxed">
                        <p><?= nl2br(htmlspecialchars($product['short_description'] ?: $product['description'])) ?></p>
                    </div>
                </div>

                <!-- Add to Cart & Buy Buttons -->
                <div class="pt-6 border-t border-slate-200 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center border border-slate-200 rounded-2xl bg-slate-50 p-1">
                            <button type="button" onclick="const q = document.getElementById('productQty'); if (q.value > 1) q.value--;" class="w-9 h-9 flex items-center justify-center font-bold text-slate-700 hover:bg-slate-200 rounded-xl">-</button>
                            <input type="number" id="productQty" value="1" min="1" class="w-12 text-center bg-transparent font-black text-sm outline-none">
                            <button type="button" onclick="const q = document.getElementById('productQty'); q.value++;" class="w-9 h-9 flex items-center justify-center font-bold text-slate-700 hover:bg-slate-200 rounded-xl">+</button>
                        </div>
                        <button type="button" onclick="addToCart(<?= $product['id'] ?>, parseInt(document.getElementById('productQty').value))" class="flex-1 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs sm:text-sm rounded-2xl shadow-xl shadow-indigo-600/25 transition flex items-center justify-center gap-2">
                            <i class="fas fa-bag-shopping"></i> Add to Shopping Bag
                        </button>
                    </div>

                    <a href="https://wa.me/88<?= htmlspecialchars($whatsapp) ?>?text=<?= urlencode('Hello OnlineBdMart! I want to order product: ' . $product['name'] . ' (৳' . $price . ')') ?>" target="_blank" class="w-full py-3.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-lg transition flex items-center justify-center gap-2">
                        <i class="fab fa-whatsapp text-lg"></i> 1-Click Order on WhatsApp
                    </a>
                </div>
            </div>
        </div>

        <!-- TABS SECTION: Specifications, Full Description & Why Buy from OnlineBdMart -->
        <div class="mt-12 pt-8 border-t border-slate-200 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Product Specifications -->
                <div class="bg-slate-50 p-6 sm:p-8 rounded-3xl border border-slate-200 space-y-4">
                    <h3 class="text-sm font-extrabold uppercase text-slate-900 flex items-center gap-2">
                        <i class="fas fa-list-check text-indigo-600"></i> Product Specifications
                    </h3>
                    <div class="text-xs text-slate-700 space-y-2 whitespace-pre-line font-medium leading-relaxed">
                        <?= htmlspecialchars($product['specifications'] ?: "Material: Premium High-Grade Build\nWarranty: 1 Year Official Warranty\nPackage Includes: Original Box & Warranty Card\nCash On Delivery: All 64 Districts") ?>
                    </div>
                </div>

                <!-- Why Order from OnlineBdMart (Trust & Guarantee Badges) -->
                <div class="bg-gradient-to-tr from-indigo-950 to-slate-900 text-white p-6 sm:p-8 rounded-3xl shadow-md space-y-4">
                    <h3 class="text-sm font-extrabold uppercase text-amber-400 flex items-center gap-2">
                        <i class="fas fa-shield-halved"></i> Why Order from OnlineBdMart?
                    </h3>
                    <div class="text-xs text-slate-200 space-y-3 leading-relaxed">
                        <?= nl2br(htmlspecialchars($product['why_buy_from_us'] ?: "✓ 100% Original Authentic Quality Guarantee\n✓ 7-Day Easy Return & Replacement Policy\n✓ Open Parcel Before Payment with Delivery Rider\n✓ Fast Nationwide Delivery Across 64 Districts\n✓ 24/7 Dedicated WhatsApp Hotline Support")) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
    <div class="space-y-6">
        <h2 class="text-xl font-extrabold font-serif text-slate-900">You May Also Like</h2>
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <?php foreach ($relatedProducts as $p): 
                $rPrice = ($p['sale_price'] && $p['sale_price'] > 0 && $p['sale_price'] < $p['price']) ? $p['sale_price'] : $p['price'];
            ?>
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm hover:shadow-xl transition flex flex-col justify-between">
                <a href="product.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="aspect-square bg-slate-100 rounded-xl overflow-hidden mb-3 block">
                    <img src="/<?= ltrim($p['image_path'], '/') ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-full h-full object-cover">
                </a>
                <a href="product.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="text-xs font-bold text-slate-900 hover:text-indigo-600 line-clamp-1"><?= htmlspecialchars($p['name']) ?></a>
                <div class="mt-3 pt-2 border-t flex items-center justify-between">
                    <span class="text-xs font-extrabold text-indigo-600">৳<?= number_format($rPrice, 2) ?></span>
                    <button type="button" onclick="addToCart(<?= $p['id'] ?>)" class="px-2.5 py-1 bg-slate-900 hover:bg-indigo-600 text-white text-[10px] font-bold rounded-lg"><i class="fas fa-bag-shopping"></i></button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Image Zoom Effect on Hover
function handleZoom(e) {
    const container = document.getElementById('zoomContainer');
    const img = document.getElementById('mainProductImage');
    const rect = container.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    
    img.style.transformOrigin = `${x}% ${y}%`;
    img.style.transform = 'scale(1.8)';
}

function resetZoom() {
    const img = document.getElementById('mainProductImage');
    img.style.transform = 'scale(1)';
    img.style.transformOrigin = 'center center';
}

function switchProductImage(src, btn) {
    document.getElementById('mainProductImage').src = src;
    document.querySelectorAll('.gallery-thumb').forEach(t => {
        t.className = 'gallery-thumb w-20 h-20 rounded-2xl overflow-hidden border-2 border-slate-200 hover:border-indigo-400 transition shrink-0 bg-slate-50';
    });
    btn.className = 'gallery-thumb w-20 h-20 rounded-2xl overflow-hidden border-2 border-indigo-600 ring-2 ring-indigo-600/30 transition shrink-0 bg-slate-50';
}
</script>

<?php require_once 'includes/footer.php'; ?>
