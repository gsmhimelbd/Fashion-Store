<?php
$adminTitle = 'Product Catalog, Inventory, Variants & SEO';
require_once __DIR__ . '/header.php';

$msg = '';
$error = '';

function uploadProductFile($fileArray, $subfolder = 'products') {
    if (!empty($fileArray['name']) && $fileArray['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'];
        if (in_array($ext, $allowed)) {
            $newName = uniqid('prod_', true) . '.' . $ext;
            $relPath = "uploads/{$subfolder}/" . $newName;
            $dest1 = __DIR__ . '/../' . $relPath;
            $dest2 = __DIR__ . '/../public/' . $relPath;
            @mkdir(dirname($dest1), 0777, true);
            @mkdir(dirname($dest2), 0777, true);
            if (@move_uploaded_file($fileArray['tmp_name'], $dest1)) {
                @copy($dest1, $dest2);
                return $relPath;
            }
        }
    }
    return null;
}

function getTableColumns($db, $table = 'products') {
    $cols = [];
    try {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $stmt = $db->query("SHOW COLUMNS FROM `$table`");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cols[] = $row['Field'];
            }
        } else {
            $stmt = $db->query("PRAGMA table_info(`$table`)");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cols[] = $row['name'];
            }
        }
    } catch (Exception $e) {}
    return $cols;
}

try {
    $db = getDB();

    // Auto-Heal products schema with all variations
    $alterQueries = [
        "ALTER TABLE `products` ADD COLUMN `colors` text DEFAULT NULL",
        "ALTER TABLE `products` ADD `colors` text DEFAULT NULL",
        "ALTER TABLE `products` ADD COLUMN `sizes` text DEFAULT NULL",
        "ALTER TABLE `products` ADD `sizes` text DEFAULT NULL",
        "ALTER TABLE `products` ADD COLUMN `image_path` varchar(255) DEFAULT 'images/products/watch-1.jpg'",
        "ALTER TABLE `products` ADD COLUMN `image` varchar(255) DEFAULT 'images/products/watch-1.jpg'",
        "ALTER TABLE `products` ADD COLUMN `gallery_images` text DEFAULT NULL",
        "ALTER TABLE `products` ADD COLUMN `sku` varchar(100) DEFAULT NULL",
        "ALTER TABLE `products` ADD COLUMN `category_id` int(11) DEFAULT NULL",
        "ALTER TABLE `products` ADD COLUMN `subcategory_id` int(11) DEFAULT NULL",
        "ALTER TABLE `products` ADD COLUMN `price` decimal(10,2) NOT NULL DEFAULT 0.00",
        "ALTER TABLE `products` ADD COLUMN `sale_price` decimal(10,2) DEFAULT NULL",
        "ALTER TABLE `products` ADD COLUMN `is_wholesale` tinyint(1) DEFAULT 0",
        "ALTER TABLE `products` ADD COLUMN `wholesale_price` decimal(10,2) DEFAULT NULL",
        "ALTER TABLE `products` ADD COLUMN `wholesale_moq` int(11) DEFAULT 5",
        "ALTER TABLE `products` ADD COLUMN `wholesale_min_qty` int(11) DEFAULT 5",
        "ALTER TABLE `products` ADD COLUMN `stock` int(11) DEFAULT 50",
        "ALTER TABLE `products` ADD COLUMN `stock_quantity` int(11) DEFAULT 50",
        "ALTER TABLE `products` ADD COLUMN `is_featured` tinyint(1) DEFAULT 1",
        "ALTER TABLE `products` ADD COLUMN `is_active` tinyint(1) DEFAULT 1",
        "ALTER TABLE `products` ADD COLUMN `short_description` text DEFAULT NULL",
        "ALTER TABLE `products` ADD COLUMN `description` text DEFAULT NULL",
        "ALTER TABLE `products` ADD COLUMN `specifications` text DEFAULT NULL",
        "ALTER TABLE `products` ADD COLUMN `why_buy_from_us` text DEFAULT NULL",
        "ALTER TABLE `products` ADD COLUMN `meta_title` varchar(255) DEFAULT NULL",
        "ALTER TABLE `products` ADD COLUMN `meta_description` text DEFAULT NULL",
        "ALTER TABLE `products` ADD COLUMN `meta_keywords` varchar(255) DEFAULT NULL",
        "ALTER TABLE `products` ADD COLUMN `focus_keyword` varchar(191) DEFAULT NULL"
    ];

    foreach ($alterQueries as $aq) {
        try { @$db->exec($aq); } catch (Exception $e) {}
    }

    // Handle Form Submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'create' || $action === 'update') {
            $name = trim($_POST['name'] ?? '');
            
            $customSlug = trim($_POST['slug'] ?? '');
            if (!empty($customSlug)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $customSlug), '-'));
            } else {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            }
            if (!$slug) $slug = 'product-' . time();

            $metaTitle = trim($_POST['meta_title'] ?? '');
            if (empty($metaTitle)) {
                $metaTitle = $name . ' Price in Bangladesh | OnlineBdMart';
            }
            $metaDescription = trim($_POST['meta_description'] ?? '');
            $focusKeyword = trim($_POST['focus_keyword'] ?? '');
            $metaKeywords = trim($_POST['meta_keywords'] ?? ($focusKeyword ?: $name));

            $catId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $subcatId = !empty($_POST['subcategory_id']) ? (int)$_POST['subcategory_id'] : null;
            $sku = trim($_POST['sku'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $salePrice = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
            $isWholesale = isset($_POST['is_wholesale']) ? 1 : 0;
            $wholesalePrice = !empty($_POST['wholesale_price']) ? (float)$_POST['wholesale_price'] : ($price * 0.75);
            $wholesaleMoq = !empty($_POST['wholesale_moq']) ? (int)$_POST['wholesale_moq'] : 5;
            $stock = !empty($_POST['stock_quantity']) ? (int)$_POST['stock_quantity'] : 50;
            $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $shortDesc = trim($_POST['short_description'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            $specs = trim($_POST['specifications'] ?? '');
            $whyBuy = trim($_POST['why_buy_from_us'] ?? '');
            
            // Colors
            $colors = trim($_POST['colors'] ?? '');

            // Sizes / Liters & Custom Pricing Repeater
            $sizesJson = '';
            if (!empty($_POST['size_name']) && is_array($_POST['size_name'])) {
                $sizeList = [];
                foreach ($_POST['size_name'] as $idx => $sName) {
                    $sName = trim($sName);
                    if ($sName !== '') {
                        $sPrice = !empty($_POST['size_price'][$idx]) ? (float)$_POST['size_price'][$idx] : $price;
                        $sizeList[] = [
                            'size' => $sName,
                            'price' => $sPrice
                        ];
                    }
                }
                if (!empty($sizeList)) {
                    $sizesJson = json_encode($sizeList, JSON_UNESCAPED_UNICODE);
                }
            }
            if (empty($sizesJson) && !empty($_POST['sizes_manual'])) {
                $sizesJson = trim($_POST['sizes_manual']);
            }

            // Primary Image
            $imagePath = trim($_POST['existing_image'] ?? 'images/products/watch-1.jpg');
            if (isset($_FILES['primary_image']) && $_FILES['primary_image']['error'] === UPLOAD_ERR_OK) {
                $uploaded = uploadProductFile($_FILES['primary_image'], 'products');
                if ($uploaded) $imagePath = $uploaded;
            }

            // Gallery Images
            $galleryArr = [];
            if (!empty($_POST['existing_gallery'])) {
                $galleryArr = array_filter(explode(',', $_POST['existing_gallery']));
            }
            if (isset($_FILES['gallery_images'])) {
                $count = count($_FILES['gallery_images']['name']);
                for ($i = 0; $i < $count; $i++) {
                    if ($_FILES['gallery_images']['error'][$i] === UPLOAD_ERR_OK) {
                        $singleFile = [
                            'name' => $_FILES['gallery_images']['name'][$i],
                            'type' => $_FILES['gallery_images']['type'][$i],
                            'tmp_name' => $_FILES['gallery_images']['tmp_name'][$i],
                            'error' => $_FILES['gallery_images']['error'][$i],
                            'size' => $_FILES['gallery_images']['size'][$i],
                        ];
                        $upGal = uploadProductFile($singleFile, 'products');
                        if ($upGal) $galleryArr[] = $upGal;
                    }
                }
            }
            $galleryStr = implode(',', array_unique($galleryArr));

            // Dynamic Schema Mapping
            $existingCols = getTableColumns($db, 'products');
            
            $payload = [
                'name' => $name,
                'slug' => $slug,
                'sku' => $sku,
                'category_id' => $catId,
                'subcategory_id' => $subcatId,
                'price' => $price,
                'sale_price' => $salePrice,
                'is_wholesale' => $isWholesale,
                'wholesale_price' => $wholesalePrice,
                'wholesale_moq' => $wholesaleMoq,
                'wholesale_min_qty' => $wholesaleMoq,
                'stock' => $stock,
                'stock_quantity' => $stock,
                'is_featured' => $isFeatured,
                'is_active' => $isActive,
                'image_path' => $imagePath,
                'image' => $imagePath,
                'gallery_images' => $galleryStr,
                'short_description' => $shortDesc,
                'description' => $desc,
                'specifications' => $specs,
                'why_buy_from_us' => $whyBuy,
                'colors' => $colors,
                'sizes' => $sizesJson,
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'meta_keywords' => $metaKeywords,
                'focus_keyword' => $focusKeyword,
            ];

            if ($action === 'create') {
                $insertCols = [];
                $placeholders = [];
                $values = [];

                foreach ($payload as $col => $val) {
                    if (empty($existingCols) || in_array($col, $existingCols)) {
                        $insertCols[] = "`$col`";
                        $placeholders[] = "?";
                        $values[] = $val;
                    }
                }

                $insertSql = "INSERT INTO `products` (" . implode(', ', $insertCols) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $db->prepare($insertSql);
                $stmt->execute($values);
                $newId = $db->lastInsertId();

                // Direct forced update for colors and sizes
                try {
                    $db->prepare("UPDATE `products` SET `colors` = ?, `sizes` = ? WHERE `id` = ?")->execute([$colors, $sizesJson, $newId]);
                } catch (Exception $eDirect) {}

                $msg = '✓ Product created successfully with colors, sizes/liters, and pricing variants!';
            } else {
                $id = (int)$_POST['product_id'];
                $updateCols = [];
                $values = [];

                foreach ($payload as $col => $val) {
                    if (empty($existingCols) || in_array($col, $existingCols)) {
                        $updateCols[] = "`$col` = ?";
                        $values[] = $val;
                    }
                }
                $values[] = $id;

                $updateSql = "UPDATE `products` SET " . implode(', ', $updateCols) . " WHERE `id` = ?";
                $stmt = $db->prepare($updateSql);
                $stmt->execute($values);

                // Direct forced update for colors and sizes
                try {
                    $db->prepare("UPDATE `products` SET `colors` = ?, `sizes` = ? WHERE `id` = ?")->execute([$colors, $sizesJson, $id]);
                } catch (Exception $eDirect) {
                    try {
                        $db->exec("ALTER TABLE `products` ADD `colors` TEXT NULL");
                        $db->exec("ALTER TABLE `products` ADD `sizes` TEXT NULL");
                        $db->prepare("UPDATE `products` SET `colors` = ?, `sizes` = ? WHERE `id` = ?")->execute([$colors, $sizesJson, $id]);
                    } catch (Exception $eDirect2) {}
                }

                $msg = '✓ Product updated successfully with colors, sizes/liters, and pricing variants!';
            }
        } elseif ($action === 'delete') {
            $id = (int)$_POST['product_id'];
            $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
            $msg = '✓ Product deleted successfully!';
        }
    }

    $products = $db->query("SELECT p.*, c.name as category_name, sc.name as subcategory_name FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN categories sc ON p.subcategory_id = sc.id ORDER BY p.id DESC")->fetchAll();
    $parentCategories = $db->query("SELECT * FROM categories WHERE parent_id IS NULL OR parent_id = 0 ORDER BY display_order ASC, name ASC")->fetchAll();
    $subCategories = $db->query("SELECT * FROM categories WHERE parent_id IS NOT NULL AND parent_id > 0 ORDER BY name ASC")->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
    $products = [];
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

<!-- Products Header & Add Button -->
<div class="flex flex-wrap items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-6 sm:p-8 rounded-3xl shadow-sm mb-6">
    <div>
        <span class="text-xs font-bold uppercase text-indigo-400">Inventory & Catalog</span>
        <h2 class="text-xl font-black text-white mt-1">Product Catalog (<?= count($products) ?> Items)</h2>
        <p class="text-xs text-slate-400">Manage products with color variants, liter/size-based pricing, Google SEO, photo uploads, and wholesale rates.</p>
    </div>
    <button type="button" onclick="openAddProductModal()" class="px-5 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-2xl text-xs shadow-lg transition flex items-center gap-2">
        <i class="fas fa-plus"></i> <span>+ Add New Product</span>
    </button>
</div>

<!-- Products Table List -->
<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase text-[10px]">
                    <th class="py-3">Photo</th>
                    <th class="py-3">Product Name & Variants</th>
                    <th class="py-3">Category</th>
                    <th class="py-3">Retail Price</th>
                    <th class="py-3">Wholesale Rate</th>
                    <th class="py-3 text-center">Stock</th>
                    <th class="py-3 text-center">Status</th>
                    <th class="py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $p): 
                        $prodJson = htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8');
                        $pImg = !empty($p['image_path']) ? $p['image_path'] : ($p['image'] ?? 'images/products/watch-1.jpg');
                        $hasColors = !empty($p['colors']);
                        $hasSizes = !empty($p['sizes']);
                    ?>
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3.5">
                            <img src="/<?= ltrim($pImg, '/') ?>" class="w-12 h-12 object-cover rounded-xl bg-slate-950 border border-slate-800 shrink-0">
                        </td>
                        <td class="py-3.5 max-w-xs">
                            <p class="font-bold text-white truncate"><?= htmlspecialchars($p['name']) ?></p>
                            <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                <?php if ($hasColors): ?>
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 truncate max-w-[120px]">
                                    🎨 <?= htmlspecialchars($p['colors']) ?>
                                </span>
                                <?php endif; ?>
                                <?php if ($hasSizes): ?>
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    📏 Liters/Sizes: <?= htmlspecialchars(substr($p['sizes'], 0, 30)) ?>...
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[10px] text-slate-500 font-mono">/product/<?= htmlspecialchars($p['slug']) ?></span>
                            </div>
                        </td>
                        <td class="py-3.5 text-slate-300">
                            <span class="font-semibold block"><?= htmlspecialchars($p['category_name'] ?? 'General') ?></span>
                            <?php if (!empty($p['subcategory_name'])): ?>
                            <span class="text-[10px] text-indigo-400">&rsaquo; <?= htmlspecialchars($p['subcategory_name']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3.5">
                            <span class="font-black text-white block">৳<?= number_format($p['price'], 2) ?></span>
                            <?php if ($p['sale_price'] && $p['sale_price'] < $p['price']): ?>
                            <span class="text-[10px] text-rose-400 font-bold">Sale: ৳<?= number_format($p['sale_price'], 2) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3.5">
                            <?php if ($p['is_wholesale'] && $p['wholesale_price']): ?>
                            <span class="text-amber-400 font-extrabold block">৳<?= number_format($p['wholesale_price'], 2) ?></span>
                            <span class="text-[10px] text-slate-400">MOQ: <?= $p['wholesale_moq'] ?: 5 ?> pcs</span>
                            <?php else: ?>
                            <span class="text-slate-500">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3.5 text-center font-bold text-slate-200"><?= $p['stock_quantity'] ?? ($p['stock'] ?? 0) ?></td>
                        <td class="py-3.5 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black <?= $p['is_active'] ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-500' ?>">
                                <?= $p['is_active'] ? 'Active' : 'Disabled' ?>
                            </span>
                        </td>
                        <td class="py-3.5 text-right space-x-1.5 whitespace-nowrap">
                            <a href="../product.php?slug=<?= htmlspecialchars($p['slug']) ?>" target="_blank" class="p-2 bg-slate-800 hover:bg-slate-700 rounded-xl text-slate-300 hover:text-white text-xs inline-block" title="View Product on Storefront">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button type="button" onclick='openEditProductModal(<?= $prodJson ?>)' class="p-2 bg-indigo-600/20 hover:bg-indigo-600 text-indigo-400 hover:text-white rounded-xl text-xs" title="Edit Product, Colors, Sizes & SEO">
                                <i class="fas fa-pen-to-square"></i>
                            </button>
                            <form method="POST" action="products.php" onsubmit="return confirm('Delete product <?= addslashes($p['name']) ?>?');" class="inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <button type="submit" class="p-2 bg-rose-500/20 hover:bg-rose-500 text-rose-400 hover:text-white rounded-xl text-xs" title="Delete Product">
                                    <i class="fas fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="py-8 text-center text-slate-500">No products found in store catalog. Click "+ Add New Product" to create one.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ADD / EDIT PRODUCT POPUP MODAL -->
<div id="productModalContainer" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="closeProductModal()"></div>
    <div class="min-h-screen flex items-center justify-center p-4 sm:p-6">
        <div class="relative w-full max-w-4xl bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden z-10">
            
            <!-- Modal Header -->
            <div class="p-6 border-b border-slate-800 flex items-center justify-between bg-slate-950">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center text-lg shadow-inner">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-white" id="modalTitle">Add New Product</h3>
                        <p class="text-xs text-slate-400">Inventory, Colors, Liter/Size-based Pricing & Google SEO</p>
                    </div>
                </div>
                <button type="button" onclick="closeProductModal()" class="p-2 text-slate-400 hover:text-white rounded-xl hover:bg-slate-800 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form method="POST" action="products.php" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6 text-xs max-h-[82vh] overflow-y-auto">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="product_id" id="formProductId" value="">
                <input type="hidden" name="existing_image" id="formExistingImage" value="images/products/watch-1.jpg">
                <input type="hidden" name="existing_gallery" id="formExistingGallery" value="">

                <!-- Section 1: Core Details -->
                <div class="space-y-4">
                    <div class="border-b border-slate-800/80 pb-2">
                        <h4 class="text-xs font-black uppercase tracking-wider text-indigo-400 flex items-center gap-2">
                            <i class="fas fa-info-circle"></i> 1. Basic Product Information
                        </h4>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-slate-300 font-bold mb-1">Product Title (প্রোডাক্টের নাম) *</label>
                            <input type="text" name="name" id="pName" required oninput="onProductTitleChange()" placeholder="e.g. Prestige Electric Multi Cooker 8L Stainless Steel" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500 font-medium">
                        </div>
                        <div>
                            <label class="block text-slate-300 font-bold mb-1">SKU / Model Code</label>
                            <input type="text" name="sku" id="pSku" placeholder="e.g. OBN102" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none">
                        </div>
                        <div>
                            <label class="block text-slate-300 font-bold mb-1">Parent Category *</label>
                            <select name="category_id" id="pCategory" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
                                <option value="">Select Category</option>
                                <?php foreach ($parentCategories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['emoji'] ?? '🛍️') ?> <?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-slate-300 font-bold mb-1">Subcategory (Optional)</label>
                            <select name="subcategory_id" id="pSubcategory" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                                <option value="">None / Top Level</option>
                                <?php foreach ($subCategories as $sc): ?>
                                <option value="<?= $sc['id'] ?>">&rsaquo; <?= htmlspecialchars($sc['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-slate-300 font-bold mb-1">Base Retail Price (মূল দাম ৳) *</label>
                            <input type="number" step="0.01" name="price" id="pPrice" required placeholder="1800" oninput="syncDefaultSizePrice()" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-indigo-400 font-bold outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-slate-300 font-bold mb-1">Sale Discount Price (৳)</label>
                            <input type="number" step="0.01" name="sale_price" id="pSalePrice" placeholder="2200" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-rose-400 font-bold outline-none">
                        </div>
                        <div>
                            <label class="block text-slate-300 font-bold mb-1">Stock Quantity</label>
                            <input type="number" name="stock_quantity" id="pStock" value="50" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Colors & Liter/Size Variants -->
                <div class="p-5 sm:p-6 rounded-3xl bg-slate-950 border-2 border-indigo-500/40 space-y-5 shadow-xl">
                    <div class="border-b border-slate-800 pb-3">
                        <span class="text-xs font-black uppercase tracking-wider text-indigo-400 flex items-center gap-2">
                            <i class="fas fa-palette"></i> 2. Product Color & Liter/Size Variants (কালার ও কত লিটার অনুযায়ী দাম)
                        </span>
                        <p class="text-[11px] text-slate-400 mt-0.5">Add color options and set different prices for different Liters / Volumes (e.g. 6 Liter = ৳1600, 8 Liter = ৳1800, 10 Liter = ৳2200).</p>
                    </div>

                    <!-- Colors Section -->
                    <div class="space-y-2">
                        <label class="block text-slate-300 font-bold">Available Colors (উপলব্ধ কালারসমূহ - কমা দিয়ে লিখুন)</label>
                        <div class="flex items-center gap-2">
                            <input type="text" name="colors" id="pColors" placeholder="e.g. Cream, Stainless Steel, Black, Silver" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500 font-medium">
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5 pt-1">
                            <span class="text-[10px] text-slate-500 font-bold mr-1">Quick Add:</span>
                            <?php 
                            $colorPresets = ['Cream', 'Stainless Steel', 'Silver', 'Black', 'White', 'Gold', 'Red', 'Navy Blue', 'Gray'];
                            foreach ($colorPresets as $cp): ?>
                            <button type="button" onclick="addColorPreset('<?= $cp ?>')" class="px-2 py-0.5 bg-slate-900 hover:bg-indigo-600/40 text-slate-300 hover:text-white rounded-lg text-[10px] font-bold border border-slate-800 transition">
                                + <?= $cp ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Sizes & Liter-based Pricing Table -->
                    <div class="space-y-3 pt-3 border-t border-slate-800">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <label class="block text-slate-300 font-bold">Liters / Volumes / Sizes & Custom Pricing (লিটার, পরিমাপ বা সাইজ অনুযায়ী দাম)</label>
                                <p class="text-[10px] text-slate-400">কত লিটার (যেমনঃ 6 Liter = ৳1600, 8 Liter = ৳1800) নির্ধারণ করুন। কাস্টমার লিটার সিলেক্ট করলে সাথে সাথে ওই দাম পরিবর্তন হবে।</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-1.5 shrink-0">
                                <button type="button" onclick="addSizePreset('liters')" class="px-2.5 py-1 bg-emerald-950/80 hover:bg-emerald-900 text-emerald-300 rounded-lg text-[10px] font-bold border border-emerald-700/50">🧴 6L / 8L / 10L</button>
                                <button type="button" onclick="addSizePreset('volume')" class="px-2.5 py-1 bg-slate-900 hover:bg-slate-800 text-cyan-400 rounded-lg text-[10px] font-bold border border-slate-800">1L / 2L / 5L</button>
                                <button type="button" onclick="addSizePreset('apparel')" class="px-2.5 py-1 bg-slate-900 hover:bg-slate-800 text-indigo-400 rounded-lg text-[10px] font-bold border border-slate-800">S / M / L / XL</button>
                                <button type="button" onclick="addSizeRow()" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-[10px] font-black shadow transition">
                                    + Add Option Row
                                </button>
                            </div>
                        </div>

                        <!-- Dynamic Sizes / Liter Repeater Table -->
                        <div class="overflow-x-auto bg-slate-900 p-3 rounded-2xl border border-slate-800">
                            <table class="w-full text-left text-xs" id="sizesTable">
                                <thead>
                                    <tr class="text-slate-400 font-bold text-[10px] uppercase border-b border-slate-800">
                                        <th class="pb-2">Option / Liter / Size Name (কত লিটার / পরিমাপ)</th>
                                        <th class="pb-2">Price (এই লিটারের দাম ৳)</th>
                                        <th class="pb-2 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="sizesTableBody" class="divide-y divide-slate-800/60">
                                    <!-- Dynamic Rows inserted here by JS -->
                                </tbody>
                            </table>
                            <div id="noSizesNotice" class="py-4 text-center text-slate-500 text-[11px]">
                                No liter/variant options added yet. Click <b>"🧴 6L / 8L / 10L"</b> or <b>"+ Add Option Row"</b> to set prices for different liters.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Complete SEO Suite -->
                <div class="p-5 sm:p-6 rounded-3xl bg-slate-950 border border-slate-800 space-y-4 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-3">
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider text-emerald-400 flex items-center gap-2">
                                <i class="fas fa-magnifying-glass"></i> 3. Product SEO Optimization (গুগল সার্চ এসইও)
                            </span>
                            <p class="text-[11px] text-slate-400 mt-0.5">Customize Google search snippet, focus keyword, meta description, and clean URL slug.</p>
                        </div>
                        <button type="button" onclick="autoGenerateSEO()" class="px-3 py-1.5 bg-indigo-600/30 hover:bg-indigo-600/50 text-indigo-300 hover:text-white rounded-xl text-[11px] font-bold border border-indigo-500/40 transition flex items-center gap-1.5 shrink-0">
                            <i class="fas fa-wand-magic-sparkles"></i> <span>Auto-Fill SEO</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <div>
                            <label class="block text-slate-300 font-bold mb-1">SEO Title (এসইও টাইটেল) *</label>
                            <input type="text" name="meta_title" id="pMetaTitle" placeholder="e.g. Prestige Electric Multi Cooker 8L - Price in BD" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500 font-medium">
                        </div>

                        <div>
                            <label class="block text-slate-300 font-bold mb-1">URL Slug (ক্লিন ইউআরএল) *</label>
                            <div class="flex items-center">
                                <span class="px-3 py-2.5 bg-slate-900 border border-r-0 border-slate-800 rounded-l-xl text-slate-500 font-mono text-[11px]">/product/</span>
                                <input type="text" name="slug" id="pSlug" placeholder="prestige-electric-multi-cooker-8l-stainless-steel" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-r-xl text-indigo-400 font-mono font-bold outline-none focus:border-indigo-500">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-300 font-bold mb-1">Focus Keyword (মেইন ফোকাস কিওয়ার্ড)</label>
                            <input type="text" name="focus_keyword" id="pFocusKeyword" placeholder="e.g. electric cooker 8l, prestige cooker bd" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-slate-300 font-bold mb-1">Meta Keywords (ট্যাগ/কিওয়ার্ড)</label>
                            <input type="text" name="meta_keywords" id="pMetaKeywords" placeholder="electric cooker, 8 liter cooker, buy online, price in bd" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Meta Description (গুগল সার্চ বিবরণী)</label>
                        <textarea name="meta_description" id="pMetaDesc" rows="2" placeholder="Buy original Prestige Electric Multi Cooker 8L in Bangladesh at lowest price. Fast delivery and COD across 64 districts." class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500 leading-relaxed"></textarea>
                    </div>
                </div>

                <!-- Section 4: Wholesale Controls -->
                <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-amber-400 text-xs flex items-center gap-1.5"><i class="fas fa-boxes-stacked"></i> 4. Wholesale & B2B Bulk Settings</span>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_wholesale" id="pIsWholesale" value="1" checked class="rounded text-amber-500">
                            <span class="text-amber-300 font-bold">Enable Wholesale</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-300 font-bold mb-1">Wholesale Factory Rate per Piece (৳)</label>
                            <input type="number" step="0.01" name="wholesale_price" id="pWholesalePrice" placeholder="1650" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-amber-400 font-bold outline-none">
                        </div>
                        <div>
                            <label class="block text-slate-300 font-bold mb-1">Minimum Order Quantity (MOQ)</label>
                            <input type="number" name="wholesale_moq" id="pWholesaleMoq" value="5" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-bold outline-none">
                        </div>
                    </div>
                </div>

                <!-- Section 5: Photo Uploads -->
                <div class="space-y-2">
                    <div class="border-b border-slate-800/80 pb-2">
                        <h4 class="text-xs font-black uppercase tracking-wider text-indigo-400 flex items-center gap-2">
                            <i class="fas fa-images"></i> 5. Product Photos
                        </h4>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-2">
                            <label class="block text-white font-bold"><i class="fas fa-upload text-indigo-400 mr-1"></i> Upload Cover Image</label>
                            <input type="file" name="primary_image" accept="image/*" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-slate-300 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500">
                            <div id="imagePreviewContainer" class="pt-2 hidden">
                                <img id="primaryImagePreview" src="" class="w-20 h-20 object-cover rounded-xl border border-slate-800">
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-2">
                            <label class="block text-white font-bold"><i class="fas fa-images text-emerald-400 mr-1"></i> Upload Multiple Gallery Images</label>
                            <input type="file" name="gallery_images[]" multiple accept="image/*" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-slate-300 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500">
                            <div id="galleryPreviewContainer" class="flex flex-wrap gap-2 pt-2"></div>
                        </div>
                    </div>
                </div>

                <!-- Section 6: Descriptions & Specs -->
                <div class="space-y-4">
                    <div class="border-b border-slate-800/80 pb-2">
                        <h4 class="text-xs font-black uppercase tracking-wider text-indigo-400 flex items-center gap-2">
                            <i class="fas fa-align-left"></i> 6. Details & Specifications
                        </h4>
                    </div>

                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Short Summary (সংক্ষিপ্ত বিবরণ)</label>
                        <input type="text" name="short_description" id="pShortDesc" placeholder="e.g. 8 Liter Large Capacity, Stainless Steel Inner Pot" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    </div>

                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Full Detailed Description (সম্পূর্ণ বিবরণ)</label>
                        <textarea name="description" id="pDesc" rows="3" placeholder="Detailed product story, features, and usage details..." class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-300 font-bold mb-1"><i class="fas fa-list-check text-cyan-400 mr-1"></i> Specifications (স্পেসিফিকেশন)</label>
                            <textarea name="specifications" id="pSpecs" rows="3" placeholder="Capacity: 8 Liter&#10;Inner Pot: Stainless Steel&#10;Power: Electric" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-slate-300 font-bold mb-1"><i class="fas fa-shield-halved text-emerald-400 mr-1"></i> Trust & Guarantee Badges</label>
                            <textarea name="why_buy_from_us" id="pWhyBuy" rows="3" placeholder="✓ 100% Original Product Guarantee&#10;✓ 100% Quality Checked Before Dispatch&#10;✓ Open Parcel Before Payment (COD)&#10;✓ Official Quality Assurance" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 7: Toggles & Save -->
                <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-slate-800">
                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_featured" id="pIsFeatured" value="1" checked class="rounded text-indigo-600">
                            <span class="text-slate-300 font-bold">Featured on Home Page</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" id="pIsActive" value="1" checked class="rounded text-emerald-600">
                            <span class="text-slate-300 font-bold">Active in Catalog</span>
                        </label>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button" onclick="closeProductModal()" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl">Cancel</button>
                        <button type="submit" class="px-8 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg transition" id="modalSubmitBtn">
                            Save Product
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function generateSlug(text) {
    return text.toString().toLowerCase().trim()
        .replace(/[^a-z0-9 -]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
}

function onProductTitleChange() {
    const name = document.getElementById('pName').value;
    const slugInput = document.getElementById('pSlug');
    const formAction = document.getElementById('formAction').value;
    
    if (formAction === 'create' || !slugInput.dataset.manualEdited) {
        slugInput.value = generateSlug(name);
    }
    
    const metaTitleInput = document.getElementById('pMetaTitle');
    if (formAction === 'create' && !metaTitleInput.dataset.manualEdited) {
        metaTitleInput.value = name ? (name + ' Price in Bangladesh | OnlineBdMart') : '';
    }

    const focusInput = document.getElementById('pFocusKeyword');
    if (formAction === 'create' && !focusInput.dataset.manualEdited) {
        focusInput.value = name.toLowerCase();
    }
}

function addColorPreset(cName) {
    const el = document.getElementById('pColors');
    const cur = el.value.trim();
    if (!cur) {
        el.value = cName;
    } else {
        const parts = cur.split(',').map(s => s.trim());
        if (!parts.includes(cName)) {
            el.value = cur + ', ' + cName;
        }
    }
}

function addSizeRow(name = '', price = '') {
    const basePrice = document.getElementById('pPrice').value || '1800';
    const rowPrice = price !== '' ? price : basePrice;
    
    const tbody = document.getElementById('sizesTableBody');
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-slate-800/40 transition';
    tr.innerHTML = `
        <td class="py-2 pr-2">
            <input type="text" name="size_name[]" value="${name}" required placeholder="e.g. 6 Liter, 8 Liter, 10 Liter" class="w-full px-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-white font-bold outline-none focus:border-indigo-500">
        </td>
        <td class="py-2 pr-2">
            <div class="flex items-center">
                <span class="px-2 py-1.5 bg-slate-950 border border-r-0 border-slate-800 rounded-l-lg text-slate-400 font-bold text-[11px]">৳</span>
                <input type="number" step="0.01" name="size_price[]" value="${rowPrice}" required placeholder="Price" class="w-full px-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-r-lg text-indigo-400 font-black outline-none focus:border-indigo-500">
            </div>
        </td>
        <td class="py-2 text-right">
            <button type="button" onclick="this.closest('tr').remove(); checkSizesCount();" class="p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/20 rounded-lg transition" title="Remove Option">
                <i class="fas fa-trash-can"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    checkSizesCount();
}

function addSizePreset(type) {
    const basePrice = parseFloat(document.getElementById('pPrice').value || 1800);
    const tbody = document.getElementById('sizesTableBody');
    tbody.innerHTML = '';
    
    if (type === 'liters') {
        const presets = [
            { name: '6 Liter', price: basePrice - 200 },
            { name: '8 Liter', price: basePrice },
            { name: '10 Liter', price: basePrice + 350 }
        ];
        presets.forEach(p => addSizeRow(p.name, Math.round(p.price)));
    } else if (type === 'volume') {
        const presets = [
            { name: '1 Liter', price: Math.round(basePrice * 0.5) },
            { name: '2 Liter', price: Math.round(basePrice * 0.95) },
            { name: '5 Liter', price: basePrice }
        ];
        presets.forEach(p => addSizeRow(p.name, p.price));
    } else if (type === 'apparel') {
        const presets = [
            { name: 'S', price: basePrice },
            { name: 'M', price: basePrice },
            { name: 'L', price: basePrice + 100 },
            { name: 'XL', price: basePrice + 200 }
        ];
        presets.forEach(p => addSizeRow(p.name, p.price));
    }
    checkSizesCount();
}

function checkSizesCount() {
    const tbody = document.getElementById('sizesTableBody');
    const notice = document.getElementById('noSizesNotice');
    if (tbody.children.length === 0) {
        notice.classList.remove('hidden');
    } else {
        notice.classList.add('hidden');
    }
}

function syncDefaultSizePrice() {
    const basePrice = document.getElementById('pPrice').value;
    const sizePriceInputs = document.querySelectorAll('input[name="size_price[]"]');
    if (sizePriceInputs.length === 1 && !sizePriceInputs[0].value) {
        sizePriceInputs[0].value = basePrice;
    }
}

function autoGenerateSEO() {
    const name = document.getElementById('pName').value.trim() || 'Product Name';
    const shortDesc = document.getElementById('pShortDesc').value.trim() || document.getElementById('pDesc').value.trim();
    
    document.getElementById('pSlug').value = generateSlug(name);
    document.getElementById('pMetaTitle').value = name + ' - Best Price in Bangladesh | OnlineBdMart';
    document.getElementById('pFocusKeyword').value = name.toLowerCase();
    document.getElementById('pMetaKeywords').value = name.toLowerCase() + ', price in bd, buy online, authentic, delivery in bangladesh';
    
    if (shortDesc) {
        document.getElementById('pMetaDesc').value = 'Buy original ' + name + ' in Bangladesh at best price. ' + shortDesc.substring(0, 100) + '... Cash on delivery available.';
    } else {
        document.getElementById('pMetaDesc').value = 'Buy authentic ' + name + ' in Bangladesh at lowest price with official warranty. Fast home delivery and cash on delivery across 64 districts.';
    }
}

function openAddProductModal() {
    document.getElementById('modalTitle').textContent = 'Add New Product & Variants';
    document.getElementById('modalSubmitBtn').textContent = 'Save & Publish Product';
    document.getElementById('formAction').value = 'create';
    document.getElementById('formProductId').value = '';
    document.getElementById('formExistingImage').value = 'images/products/watch-1.jpg';
    document.getElementById('formExistingGallery').value = '';
    
    document.getElementById('pName').value = '';
    document.getElementById('pSlug').value = '';
    document.getElementById('pSlug').dataset.manualEdited = '';
    document.getElementById('pColors').value = '';
    document.getElementById('sizesTableBody').innerHTML = '';
    checkSizesCount();

    document.getElementById('pMetaTitle').value = '';
    document.getElementById('pMetaTitle').dataset.manualEdited = '';
    document.getElementById('pMetaDesc').value = '';
    document.getElementById('pFocusKeyword').value = '';
    document.getElementById('pMetaKeywords').value = '';

    document.getElementById('pSku').value = '';
    document.getElementById('pCategory').value = '';
    document.getElementById('pSubcategory').value = '';
    document.getElementById('pPrice').value = '';
    document.getElementById('pSalePrice').value = '';
    document.getElementById('pWholesalePrice').value = '';
    document.getElementById('pWholesaleMoq').value = '5';
    document.getElementById('pStock').value = '50';
    document.getElementById('pShortDesc').value = '';
    document.getElementById('pDesc').value = '';
    document.getElementById('pSpecs').value = "Capacity: 8 Liter\nInner Pot: Stainless Steel\nPower: Electric";
    document.getElementById('pWhyBuy').value = "✓ 100% Original Product Guarantee\n✓ 100% Quality Checked Before Dispatch\n✓ Cash On Delivery Across 64 Districts\n✓ Official Quality Assurance";
    document.getElementById('pIsWholesale').checked = true;
    document.getElementById('pIsFeatured').checked = true;
    document.getElementById('pIsActive').checked = true;

    document.getElementById('imagePreviewContainer').classList.add('hidden');
    document.getElementById('galleryPreviewContainer').innerHTML = '';

    document.getElementById('productModalContainer').classList.remove('hidden');
}

function openEditProductModal(p) {
    document.getElementById('modalTitle').textContent = 'Edit Product & Variants: ' + p.name;
    document.getElementById('modalSubmitBtn').textContent = 'Update Product & Variants';
    document.getElementById('formAction').value = 'update';
    document.getElementById('formProductId').value = p.id;
    document.getElementById('formExistingImage').value = p.image_path || p.image || '';
    document.getElementById('formExistingGallery').value = p.gallery_images || '';

    document.getElementById('pName').value = p.name || '';
    document.getElementById('pSlug').value = p.slug || '';
    document.getElementById('pSlug').dataset.manualEdited = 'true';
    document.getElementById('pColors').value = p.colors || '';

    // Load Sizes / Liters Table
    const tbody = document.getElementById('sizesTableBody');
    tbody.innerHTML = '';
    if (p.sizes) {
        try {
            let sData = JSON.parse(p.sizes);
            if (Array.isArray(sData)) {
                sData.forEach(item => {
                    addSizeRow(item.size || item.name || '', item.price || p.price || '');
                });
            }
        } catch (e) {
            const sParts = p.sizes.split(',');
            sParts.forEach(sp => {
                if (sp.includes(':')) {
                    const [sN, sP] = sp.split(':');
                    addSizeRow(sN.trim(), sP.trim());
                } else if (sp.trim()) {
                    addSizeRow(sp.trim(), p.price || '');
                }
            });
        }
    }
    checkSizesCount();

    document.getElementById('pMetaTitle').value = p.meta_title || '';
    document.getElementById('pMetaTitle').dataset.manualEdited = 'true';
    document.getElementById('pMetaDesc').value = p.meta_description || '';
    document.getElementById('pFocusKeyword').value = p.focus_keyword || '';
    document.getElementById('pMetaKeywords').value = p.meta_keywords || '';

    document.getElementById('pSku').value = p.sku || '';
    document.getElementById('pCategory').value = p.category_id || '';
    document.getElementById('pSubcategory').value = p.subcategory_id || '';
    document.getElementById('pPrice').value = p.price || '';
    document.getElementById('pSalePrice').value = p.sale_price || '';
    document.getElementById('pWholesalePrice').value = p.wholesale_price || '';
    document.getElementById('pWholesaleMoq').value = p.wholesale_moq || p.wholesale_min_qty || '5';
    document.getElementById('pStock').value = p.stock_quantity || p.stock || '50';
    document.getElementById('pShortDesc').value = p.short_description || '';
    document.getElementById('pDesc').value = p.description || '';
    document.getElementById('pSpecs').value = p.specifications || '';
    document.getElementById('pWhyBuy').value = p.why_buy_from_us || '';
    document.getElementById('pIsWholesale').checked = Boolean(Number(p.is_wholesale));
    document.getElementById('pIsFeatured').checked = Boolean(Number(p.is_featured));
    document.getElementById('pIsActive').checked = Boolean(Number(p.is_active));

    // Thumbnail Preview
    const imgPath = p.image_path || p.image;
    if (imgPath) {
        const preview = document.getElementById('primaryImagePreview');
        preview.src = '/' + imgPath.replace(/^\/+/, '');
        document.getElementById('imagePreviewContainer').classList.remove('hidden');
    }

    // Gallery Preview
    const galContainer = document.getElementById('galleryPreviewContainer');
    galContainer.innerHTML = '';
    if (p.gallery_images) {
        const gList = p.gallery_images.split(',');
        gList.forEach(g => {
            if (g.trim()) {
                const img = document.createElement('img');
                img.src = '/' + g.trim().replace(/^\/+/, '');
                img.className = 'w-14 h-14 object-cover rounded-lg border border-slate-800';
                galContainer.appendChild(img);
            }
        });
    }

    document.getElementById('productModalContainer').classList.remove('hidden');
}

function closeProductModal() {
    document.getElementById('productModalContainer').classList.add('hidden');
}

document.getElementById('pSlug').addEventListener('input', function() { this.dataset.manualEdited = 'true'; });
document.getElementById('pMetaTitle').addEventListener('input', function() { this.dataset.manualEdited = 'true'; });
document.getElementById('pFocusKeyword').addEventListener('input', function() { this.dataset.manualEdited = 'true'; });
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
