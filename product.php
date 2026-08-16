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

    // Smart Related Products: Fetch from same category or subcategory, with trending fallback
    $relatedProducts = [];
    try {
        if (!empty($product['category_id'])) {
            $rStmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1 ORDER BY p.id DESC LIMIT 4");
            $rStmt->execute([(int)$product['category_id'], (int)$product['id']]);
            $relatedProducts = $rStmt->fetchAll();
        }

        if (count($relatedProducts) < 4) {
            $excludeIds = [(int)$product['id']];
            foreach ($relatedProducts as $rp) {
                $excludeIds[] = (int)$rp['id'];
            }
            $inList = implode(',', $excludeIds);
            $limit = 4 - count($relatedProducts);
            $fStmt = $db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id NOT IN ($inList) AND p.is_active = 1 ORDER BY p.id DESC LIMIT $limit");
            $fallbackRows = $fStmt->fetchAll();
            foreach ($fallbackRows as $fr) {
                $relatedProducts[] = $fr;
            }
        }
    } catch (Exception $re) {
        try {
            $fStmt = $db->prepare("SELECT * FROM products WHERE id != ? AND is_active = 1 ORDER BY id DESC LIMIT 4");
            $fStmt->execute([(int)$product['id']]);
            $relatedProducts = $fStmt->fetchAll();
        } catch (Exception $re2) {
            $relatedProducts = [];
        }
    }

} catch (Exception $e) {
    header('Location: shop.php');
    exit;
}

$pageTitle = !empty($product['meta_title']) ? $product['meta_title'] : ($product['name'] . ' - OnlineBdMart • Online Shopping BD');
$metaDescription = !empty($product['meta_description']) ? $product['meta_description'] : (!empty($product['short_description']) ? $product['short_description'] : substr(strip_tags($product['description'] ?? ''), 0, 160));
$metaKeywords = !empty($product['meta_keywords']) ? $product['meta_keywords'] : (!empty($product['focus_keyword']) ? $product['focus_keyword'] : ($product['name'] . ', price in bangladesh, buy online bd'));

require_once 'includes/header.php';

$price = ($product['sale_price'] && $product['sale_price'] > 0 && $product['sale_price'] < $product['price']) ? $product['sale_price'] : $product['price'];

// Gallery images array
$gallery = [];
$primaryImg = !empty($product['image_path']) ? $product['image_path'] : ($product['image'] ?? 'images/products/watch-1.jpg');
if (!empty($primaryImg)) {
    $gallery[] = '/' . ltrim($primaryImg, '/');
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

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 mb-8 overflow-x-auto whitespace-nowrap">
        <a href="index.php" class="hover:text-indigo-600 font-medium">Home</a>
        <span>/</span>
        <a href="shop.php" class="hover:text-indigo-600 font-medium">Shop</a>
        <span>/</span>
        <a href="shop.php?category=<?= htmlspecialchars($product['category_slug'] ?? '') ?>" class="hover:text-indigo-600 font-medium"><?= htmlspecialchars($product['category_name'] ?? 'Accessories') ?></a>
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
                    
                    <span class="absolute top-4 left-4 z-10 px-3 py-1 bg-black/60 backdrop-blur-md text-white rounded-full text-[10px] font-bold pointer-events-none flex items-center gap-1.5 shadow">
                        <i class="fas fa-magnifying-glass-plus"></i> Hover to Zoom
                    </span>

                    <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                    <span class="absolute top-4 right-4 z-10 px-3 py-1 bg-rose-500 text-white rounded-full text-xs font-black shadow-lg">
                        <?= round((($product['price'] - $product['sale_price']) / $product['price']) * 100) ?>% OFF
                    </span>
                    <?php endif; ?>
                </div>

                <!-- Gallery Thumbnails Strip -->
                <?php if (count($gallery) > 1): ?>
                <div class="flex items-center gap-3 overflow-x-auto pb-2 scrollbar-thin">
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
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100"><?= htmlspecialchars($product['category_name'] ?? 'Accessories') ?></span>
                        <span class="text-xs text-emerald-600 font-bold bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">● In Stock (<?= $product['stock_quantity'] ?? ($product['stock'] ?? 50) ?> pcs)</span>
                        <?php if (!empty($product['sku'])): ?>
                        <span class="text-[11px] font-mono text-slate-400 bg-slate-50 px-2.5 py-0.5 rounded-lg border border-slate-200">SKU: <?= htmlspecialchars($product['sku']) ?></span>
                        <?php endif; ?>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold font-serif text-slate-900 leading-snug"><?= htmlspecialchars($product['name']) ?></h1>
                    
                    <!-- Rating & Reviews -->
                    <div class="flex items-center gap-2">
                        <div class="flex text-amber-400 text-xs">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-700">4.9</span>
                        <span class="text-xs text-slate-400">(<?= $product['reviews_count'] ?? 48 ?> Verified Customer Reviews)</span>
                    </div>

                    <!-- Price Block -->
                    <div class="flex items-baseline gap-3 pt-2">
                        <span class="text-3xl sm:text-4xl font-black text-indigo-600">৳<?= number_format($price, 2) ?></span>
                        <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                        <span class="text-lg text-slate-400 line-through">৳<?= number_format($product['price'], 2) ?></span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-600">
                            Save ৳<?= number_format($product['price'] - $product['sale_price'], 2) ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Wholesale Bulk Box -->
                    <?php if (!empty($product['is_wholesale']) && !empty($product['wholesale_price']) && $product['wholesale_price'] > 0): 
                        $moq = $product['wholesale_moq'] ?: ($product['wholesale_min_qty'] ?: 5);
                    ?>
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-xs font-black uppercase text-amber-900 flex items-center gap-1.5"><i class="fas fa-boxes-stacked"></i> Wholesale / B2B Rate Available</span>
                            <p class="text-xs text-amber-800 mt-0.5">Order <strong><?= $moq ?>+ pcs</strong> at wholesale price: <strong class="text-emerald-700 text-sm">৳<?= number_format($product['wholesale_price'], 2) ?>/pc</strong></p>
                        </div>
                        <button type="button" onclick="addToCart(<?= $product['id'] ?>, <?= $moq ?>, true)" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-black rounded-xl shadow transition shrink-0">
                            Add <?= $moq ?> pcs Wholesale
                        </button>
                    </div>
                    <?php endif; ?>

                    <!-- Short Highlights / Summary -->
                    <?php if (!empty($product['short_description'])): ?>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-xs sm:text-sm text-slate-700 leading-relaxed">
                        <p class="font-medium"><?= nl2br(htmlspecialchars($product['short_description'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Add to Cart & WhatsApp Buy Buttons -->
                <div class="pt-6 border-t border-slate-200 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="h-12 flex items-center border border-slate-200 rounded-2xl bg-slate-50 px-1 shrink-0">
                            <button type="button" onclick="const q = document.getElementById('productQty'); if (q.value > 1) q.value--;" class="w-9 h-9 flex items-center justify-center font-bold text-slate-700 hover:bg-slate-200 rounded-xl transition text-base">-</button>
                            <input type="number" id="productQty" value="1" min="1" class="w-10 sm:w-12 text-center bg-transparent font-black text-sm outline-none">
                            <button type="button" onclick="const q = document.getElementById('productQty'); q.value++;" class="w-9 h-9 flex items-center justify-center font-bold text-slate-700 hover:bg-slate-200 rounded-xl transition text-base">+</button>
                        </div>
                        <button type="button" onclick="addToCart(<?= $product['id'] ?>, parseInt(document.getElementById('productQty').value))" class="flex-1 h-12 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white font-black text-xs sm:text-sm rounded-2xl shadow-lg shadow-indigo-600/25 hover:shadow-indigo-600/40 transition flex items-center justify-center gap-2 active:scale-98">
                            <i class="fas fa-bag-shopping text-sm"></i> <span>Add to Bag</span>
                        </button>
                    </div>

                    <a href="https://wa.me/88<?= htmlspecialchars($whatsapp ?? '01700000000') ?>?text=<?= urlencode('Hello OnlineBdMart! I want to order product: ' . $product['name'] . ' (৳' . $price . ') - URL: https://onlinebdmart.com/product.php?slug=' . $product['slug']) ?>" target="_blank" class="w-full h-12 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-md transition flex items-center justify-center gap-2">
                        <i class="fab fa-whatsapp text-lg"></i> <span>1-Click Order on WhatsApp</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- FULL DETAILED DESCRIPTION & INFORMATION SECTION -->
        <div class="mt-12 pt-10 border-t border-slate-200 space-y-8">
            
            <!-- Full Description Box -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base">
                        <i class="fas fa-file-lines"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-black text-slate-900">Full Detailed Description & Features (সম্পূর্ণ বিবরণ)</h3>
                </div>

                <div class="text-xs sm:text-sm text-slate-700 leading-relaxed space-y-4 font-normal whitespace-pre-line">
                    <?php if (!empty($product['description'])): ?>
                        <?= nl2br(htmlspecialchars($product['description'])) ?>
                    <?php elseif (!empty($product['short_description'])): ?>
                        <?= nl2br(htmlspecialchars($product['short_description'])) ?>
                    <?php else: ?>
                        <p>Explore the premium <?= htmlspecialchars($product['name']) ?> at OnlineBdMart. Built with high quality materials, official warranty support, and nationwide home delivery with open box inspection before payment across all 64 districts in Bangladesh.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Specifications & Why Order Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Product Specifications -->
                <div class="bg-slate-50 p-6 sm:p-8 rounded-3xl border border-slate-200 space-y-4">
                    <div class="flex items-center gap-2.5 border-b border-slate-200 pb-3">
                        <div class="w-8 h-8 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center text-sm">
                            <i class="fas fa-list-check"></i>
                        </div>
                        <h3 class="text-sm font-extrabold uppercase text-slate-900">Technical Specifications</h3>
                    </div>
                    <div class="text-xs text-slate-700 space-y-2 whitespace-pre-line font-medium leading-relaxed">
                        <?= htmlspecialchars($product['specifications'] ?: "Material: Premium High-Grade Build\nWarranty: 1 Year Official Warranty\nPackage Includes: Original Box, Charging Cable & Manual\nDelivery: Cash On Delivery in All 64 Districts") ?>
                    </div>
                </div>

                <!-- Why Order from OnlineBdMart (Trust & Guarantee Badges) -->
                <div class="bg-gradient-to-tr from-indigo-950 to-slate-900 text-white p-6 sm:p-8 rounded-3xl shadow-md space-y-4">
                    <div class="flex items-center gap-2.5 border-b border-slate-800 pb-3">
                        <div class="w-8 h-8 rounded-xl bg-amber-400/20 text-amber-400 flex items-center justify-center text-sm">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <h3 class="text-sm font-extrabold uppercase text-amber-400">Why Buy from OnlineBdMart?</h3>
                    </div>
                    <div class="text-xs text-slate-200 space-y-3 leading-relaxed whitespace-pre-line">
                        <?= htmlspecialchars($product['why_buy_from_us'] ?: "✓ 100% Original Authentic Quality Guarantee\n✓ 7-Day Easy Return & Replacement Policy\n✓ Open Parcel Before Payment with Delivery Rider\n✓ Fast Nationwide Delivery Across 64 Districts\n✓ 24/7 Dedicated WhatsApp Hotline Support") ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products / Recommended Items (Always Active) -->
    <?php if (!empty($relatedProducts)): ?>
    <div class="space-y-6 mt-12">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Recommendations</span>
                <h2 class="text-xl sm:text-2xl font-extrabold font-serif text-slate-900">You May Also Like (সম্পর্কিত প্রোডাক্ট)</h2>
            </div>
            <a href="shop.php<?= !empty($product['category_slug']) ? '?category=' . htmlspecialchars($product['category_slug']) : '' ?>" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
                <span>View More</span> &rarr;
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <?php foreach ($relatedProducts as $p): 
                $rPrice = ($p['sale_price'] && $p['sale_price'] > 0 && $p['sale_price'] < $p['price']) ? $p['sale_price'] : $p['price'];
                $rImg = !empty($p['image_path']) ? $p['image_path'] : ($p['image'] ?? 'images/products/watch-1.jpg');
            ?>
            <div class="group bg-white rounded-3xl border border-slate-200 p-4 shadow-sm hover:shadow-xl hover:border-indigo-500/50 transition flex flex-col justify-between overflow-hidden relative">
                <?php if ($p['sale_price'] && $p['sale_price'] < $p['price']): ?>
                <span class="absolute top-3 left-3 z-10 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white shadow">Sale</span>
                <?php endif; ?>

                <a href="product.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="aspect-square bg-slate-100 rounded-2xl overflow-hidden mb-3 block">
                    <img src="/<?= ltrim($rImg, '/') ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                </a>

                <div class="flex-1 flex flex-col justify-between space-y-2">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 block truncate"><?= htmlspecialchars($p['category_name'] ?? 'Accessories') ?></span>
                        <a href="product.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition line-clamp-2 block mt-0.5"><?= htmlspecialchars($p['name']) ?></a>
                    </div>
                    <div class="pt-2 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <span class="text-sm font-black text-slate-900">৳<?= number_format($rPrice, 2) ?></span>
                        <button type="button" onclick="addToCart(<?= $p['id'] ?>)" class="w-full sm:w-auto px-3 py-1.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white text-xs font-black rounded-xl shadow transition flex items-center justify-center gap-1.5">
                            <i class="fas fa-bag-shopping text-xs"></i> <span>Add</span>
                        </button>
                    </div>
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
