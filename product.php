<?php
require_once 'config/database.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) {
    header('Location: shop.php');
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.slug = ? LIMIT 1");
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
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 mb-8">
        <a href="index.php" class="hover:text-indigo-600">Home</a>
        <span>/</span>
        <a href="shop.php" class="hover:text-indigo-600">Shop</a>
        <span>/</span>
        <a href="shop.php?category=<?= htmlspecialchars($product['category_slug'] ?? '') ?>" class="hover:text-indigo-600"><?= htmlspecialchars($product['category_name'] ?? 'Accessories') ?></a>
        <span>/</span>
        <span class="text-slate-900 font-bold truncate"><?= htmlspecialchars($product['name']) ?></span>
    </nav>

    <!-- Main Product Details Box -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-10 shadow-sm mb-12">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <!-- Product Image -->
            <div>
                <div class="aspect-square bg-slate-100 rounded-2xl overflow-hidden border">
                    <img src="/<?= ltrim($product['image_path'], '/') ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover">
                </div>
            </div>

            <!-- Product Details -->
            <div class="flex flex-col justify-between space-y-6">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full"><?= htmlspecialchars($product['category_name'] ?? 'Accessories') ?></span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold font-serif text-slate-900 mt-3"><?= htmlspecialchars($product['name']) ?></h1>
                    
                    <div class="flex items-baseline gap-3 mt-4">
                        <span class="text-2xl sm:text-3xl font-black text-indigo-600">৳<?= number_format($price, 2) ?></span>
                        <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                        <span class="text-base text-slate-400 line-through">৳<?= number_format($product['price'], 2) ?></span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-black bg-rose-100 text-rose-600">
                            <?= round((($product['price'] - $product['sale_price']) / $product['price']) * 100) ?>% OFF
                        </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($product['is_wholesale'] && $product['wholesale_price'] > 0): ?>
                    <div class="mt-4 p-4 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-black uppercase text-amber-900"><i class="fas fa-boxes-stacked mr-1"></i> Wholesale Available</span>
                            <p class="text-[11px] text-amber-700 mt-0.5">Min Qty: <strong><?= $product['wholesale_moq'] ?? 5 ?> pcs</strong> @ <strong>৳<?= number_format($product['wholesale_price'], 2) ?>/pc</strong></p>
                        </div>
                        <a href="wholesale.php" class="px-3.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold rounded-xl shadow">Wholesale Page</a>
                    </div>
                    <?php endif; ?>

                    <div class="mt-6 text-xs text-slate-600 leading-relaxed space-y-2">
                        <p class="font-bold text-slate-900 uppercase text-[11px]">Description:</p>
                        <p><?= nl2br(htmlspecialchars($product['description'] ?? 'No description available.')) ?></p>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-200 space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center border border-slate-200 rounded-xl bg-slate-50">
                            <button type="button" onclick="const q = document.getElementById('productQty'); if (q.value > 1) q.value--;" class="w-10 h-10 flex items-center justify-center font-bold text-slate-600 hover:bg-slate-200 rounded-l-xl">-</button>
                            <input type="number" id="productQty" value="1" min="1" class="w-12 text-center bg-transparent font-bold text-xs outline-none">
                            <button type="button" onclick="const q = document.getElementById('productQty'); q.value++;" class="w-10 h-10 flex items-center justify-center font-bold text-slate-600 hover:bg-slate-200 rounded-r-xl">+</button>
                        </div>
                        <button type="button" onclick="addToCart(<?= $product['id'] ?>, parseInt(document.getElementById('productQty').value))" class="flex-1 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                            <i class="fas fa-bag-shopping"></i> Add to Cart
                        </button>
                    </div>

                    <a href="https://wa.me/88<?= htmlspecialchars($whatsapp) ?>?text=<?= urlencode('Hello OnlineBdMart, I want to order product: ' . $product['name'] . ' (৳' . $price . ')') ?>" target="_blank" class="w-full py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl shadow transition flex items-center justify-center gap-2">
                        <i class="fab fa-whatsapp text-base"></i> Order via WhatsApp Instant
                    </a>
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

<?php require_once 'includes/footer.php'; ?>
