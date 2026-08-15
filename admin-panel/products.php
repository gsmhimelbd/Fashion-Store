<?php
$adminTitle = 'Product Catalog & Inventory';
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

try {
    $db = getDB();

    // Handle Actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'create' || $action === 'update') {
            $name = trim($_POST['name'] ?? '');
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            if (!$slug) $slug = 'product-' . time();
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

            // Handle Primary Image Upload from Computer
            $imagePath = trim($_POST['existing_image'] ?? 'images/products/watch-1.jpg');
            if (isset($_FILES['primary_image']) && $_FILES['primary_image']['error'] === UPLOAD_ERR_OK) {
                $uploaded = uploadProductFile($_FILES['primary_image'], 'products');
                if ($uploaded) $imagePath = $uploaded;
            }

            // Handle Multiple Gallery Images Upload
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

            if ($action === 'create') {
                $stmt = $db->prepare("INSERT INTO products (name, slug, sku, category_id, subcategory_id, price, sale_price, is_wholesale, wholesale_price, wholesale_moq, wholesale_min_qty, stock, stock_quantity, is_featured, is_active, image_path, gallery_images, short_description, description, specifications, why_buy_from_us, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                $stmt->execute([$name, $slug, $sku, $catId, $subcatId, $price, $salePrice, $isWholesale, $wholesalePrice, $wholesaleMoq, $wholesaleMoq, $stock, $stock, $isFeatured, $isActive, $imagePath, $galleryStr, $shortDesc, $desc, $specs, $whyBuy]);
                $msg = 'Product added successfully with uploaded photos and specifications!';
            } else {
                $id = (int)$_POST['product_id'];
                $stmt = $db->prepare("UPDATE products SET name = ?, slug = ?, sku = ?, category_id = ?, subcategory_id = ?, price = ?, sale_price = ?, is_wholesale = ?, wholesale_price = ?, wholesale_moq = ?, wholesale_min_qty = ?, stock = ?, stock_quantity = ?, is_featured = ?, is_active = ?, image_path = ?, gallery_images = ?, short_description = ?, description = ?, specifications = ?, why_buy_from_us = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $slug, $sku, $catId, $subcatId, $price, $salePrice, $isWholesale, $wholesalePrice, $wholesaleMoq, $wholesaleMoq, $stock, $stock, $isFeatured, $isActive, $imagePath, $galleryStr, $shortDesc, $desc, $specs, $whyBuy, $id]);
                $msg = 'Product updated successfully!';
            }
        } elseif ($action === 'delete') {
            $id = (int)$_POST['product_id'];
            $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
            $msg = 'Product deleted successfully!';
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
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1.5"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="p-4 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-400 text-xs font-bold">
    <i class="fas fa-circle-exclamation mr-1.5"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<!-- Products Header & Add Button -->
<div class="flex flex-wrap items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-6 rounded-3xl shadow-sm">
    <div>
        <span class="text-xs font-bold uppercase text-indigo-400">Inventory Controls</span>
        <h2 class="text-lg font-black text-white mt-1">Product Catalog (<?= count($products) ?> Items)</h2>
        <p class="text-xs text-slate-400">Add products with local photo uploads, multiple gallery pictures, specs, wholesale pricing, and zoom preview.</p>
    </div>
    <button type="button" onclick="openAddProductModal()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl text-xs shadow-lg transition flex items-center gap-2">
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
                    <th class="py-3">Product Name</th>
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
                    ?>
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3.5">
                            <img src="/<?= ltrim($p['image_path'], '/') ?>" class="w-12 h-12 object-cover rounded-xl bg-slate-950 border border-slate-800 shrink-0">
                        </td>
                        <td class="py-3.5 max-w-xs">
                            <p class="font-bold text-white truncate"><?= htmlspecialchars($p['name']) ?></p>
                            <span class="text-[10px] text-slate-400 font-mono">SKU: <?= htmlspecialchars($p['sku'] ?: 'N/A') ?></span>
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
                            <span class="text-[10px] text-rose-400">Sale: ৳<?= number_format($p['sale_price'], 2) ?></span>
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
                        <td class="py-3.5 text-center font-bold text-slate-200"><?= $p['stock_quantity'] ?? $p['stock'] ?></td>
                        <td class="py-3.5 text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $p['is_active'] ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-800 text-slate-500' ?>">
                                <?= $p['is_active'] ? 'Active' : 'Disabled' ?>
                            </span>
                        </td>
                        <td class="py-3.5 text-right space-x-2">
                            <a href="../product.php?slug=<?= htmlspecialchars($p['slug']) ?>" target="_blank" class="p-2 bg-slate-800 hover:bg-slate-700 rounded-xl text-slate-300 hover:text-white text-xs" title="Preview Product">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button type="button" onclick='openEditProductModal(<?= $prodJson ?>)' class="p-2 bg-indigo-600/20 hover:bg-indigo-600 text-indigo-400 hover:text-white rounded-xl text-xs" title="Edit Product">
                                <i class="fas fa-pen-to-square"></i>
                            </button>
                            <form method="POST" action="products.php" onsubmit="return confirm('Are you sure you want to delete this product?');" class="inline">
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
                    <div class="w-10 h-10 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center text-lg">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-white" id="modalTitle">Add New Product</h3>
                        <p class="text-xs text-slate-400">Complete details with local photo uploads & specs</p>
                    </div>
                </div>
                <button type="button" onclick="closeProductModal()" class="p-2 text-slate-400 hover:text-white rounded-xl hover:bg-slate-800">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form method="POST" action="products.php" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6 text-xs max-h-[80vh] overflow-y-auto">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="product_id" id="formProductId" value="">
                <input type="hidden" name="existing_image" id="formExistingImage" value="images/products/watch-1.jpg">
                <input type="hidden" name="existing_gallery" id="formExistingGallery" value="">

                <!-- Row 1: Name, SKU, Category, Subcategory -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-slate-300 font-bold mb-1">Product Title *</label>
                        <input type="text" name="name" id="pName" required placeholder="e.g. Naviforce Chronograph Watch" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">SKU / Model Code</label>
                        <input type="text" name="sku" id="pSku" placeholder="e.g. NF-9110" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Parent Category *</label>
                        <select name="category_id" id="pCategory" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                            <option value="">Select Category</option>
                            <?php foreach ($parentCategories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['emoji'] ?? '🛍️') ?> <?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Row 2: Subcategory & Pricing -->
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
                        <label class="block text-slate-300 font-bold mb-1">Regular Retail Price (৳) *</label>
                        <input type="number" step="0.01" name="price" id="pPrice" required placeholder="3850" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-indigo-400 font-bold outline-none">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Sale Discount Price (৳)</label>
                        <input type="number" step="0.01" name="sale_price" id="pSalePrice" placeholder="3250" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-rose-400 font-bold outline-none">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Stock Quantity</label>
                        <input type="number" name="stock_quantity" id="pStock" value="50" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    </div>
                </div>

                <!-- Row 3: Wholesale Controls -->
                <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-amber-400 text-xs"><i class="fas fa-boxes-stacked mr-1"></i> Wholesale & B2B Bulk Settings</span>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_wholesale" id="pIsWholesale" value="1" checked class="rounded text-amber-500">
                            <span class="text-amber-300 font-bold">Enable Wholesale</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-300 font-bold mb-1">Wholesale Factory Rate per Piece (৳)</label>
                            <input type="number" step="0.01" name="wholesale_price" id="pWholesalePrice" placeholder="2450" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-amber-400 font-bold outline-none">
                        </div>
                        <div>
                            <label class="block text-slate-300 font-bold mb-1">Minimum Order Quantity (MOQ)</label>
                            <input type="number" name="wholesale_moq" id="pWholesaleMoq" value="5" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-bold outline-none">
                        </div>
                    </div>
                </div>

                <!-- Row 4: Photo Uploads from Computer -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-2">
                        <label class="block text-white font-bold"><i class="fas fa-upload text-indigo-400 mr-1"></i> Upload Cover Image (from Local Computer)</label>
                        <input type="file" name="primary_image" accept="image/*" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-slate-300 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500">
                        <div id="imagePreviewContainer" class="pt-2 hidden">
                            <img id="primaryImagePreview" src="" class="w-20 h-20 object-cover rounded-xl border border-slate-800">
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-2">
                        <label class="block text-white font-bold"><i class="fas fa-images text-emerald-400 mr-1"></i> Upload Multiple Gallery Images</label>
                        <input type="file" name="gallery_images[]" multiple accept="image/*" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-slate-300 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500">
                        <p class="text-[10px] text-slate-400">Select multiple photos for product zoom & angle gallery.</p>
                        <div id="galleryPreviewContainer" class="flex flex-wrap gap-2 pt-2"></div>
                    </div>
                </div>

                <!-- Row 5: Descriptions & Specifications -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Short Summary</label>
                        <input type="text" name="short_description" id="pShortDesc" placeholder="e.g. Japanese Quartz Movement, 316L Stainless Steel, 30M Waterproof" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    </div>

                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Full Detailed Description</label>
                        <textarea name="description" id="pDesc" rows="4" placeholder="Detailed product story, features, and comfort details..." class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-300 font-bold mb-1"><i class="fas fa-list-check text-cyan-400 mr-1"></i> Product Specifications (Specs Tab)</label>
                            <textarea name="specifications" id="pSpecs" rows="3" placeholder="Dial Diameter: 45mm&#10;Case Thickness: 12mm&#10;Glass: Hardlex Sapphire&#10;Water Resistance: 3ATM" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-slate-300 font-bold mb-1"><i class="fas fa-shield-halved text-emerald-400 mr-1"></i> Why Buy from OnlineBdMart (Trust Badges)</label>
                            <textarea name="why_buy_from_us" id="pWhyBuy" rows="3" placeholder="✓ 100% Original Product Guarantee&#10;✓ 7 Days Free Replacement Policy&#10;✓ Open Parcel Before Payment (COD)&#10;✓ Official Warranty Included" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Row 6: Toggles -->
                <div class="flex items-center gap-6 pt-2 border-t border-slate-800">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" id="pIsFeatured" value="1" checked class="rounded text-indigo-600">
                        <span class="text-slate-300 font-bold">Featured on Home Page</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" id="pIsActive" value="1" checked class="rounded text-emerald-600">
                        <span class="text-slate-300 font-bold">Active in Catalog</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 border-t border-slate-800 flex justify-end gap-3">
                    <button type="button" onclick="closeProductModal()" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl">Cancel</button>
                    <button type="submit" class="px-8 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg transition" id="modalSubmitBtn">
                        Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddProductModal() {
    document.getElementById('modalTitle').textContent = 'Add New Product';
    document.getElementById('modalSubmitBtn').textContent = 'Save & Publish Product';
    document.getElementById('formAction').value = 'create';
    document.getElementById('formProductId').value = '';
    document.getElementById('formExistingImage').value = 'images/products/watch-1.jpg';
    document.getElementById('formExistingGallery').value = '';
    
    document.getElementById('pName').value = '';
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
    document.getElementById('pSpecs').value = "Dial Diameter: 45mm\nCase Thickness: 12mm\nMaterial: Surgical Grade 316L Stainless Steel\nWater Resistance: 3ATM (30M)";
    document.getElementById('pWhyBuy').value = "✓ 100% Original Product Guarantee\n✓ 7 Days Free Replacement Policy\n✓ Cash On Delivery Across 64 Districts\n✓ Official Warranty Card Included";
    document.getElementById('pIsWholesale').checked = true;
    document.getElementById('pIsFeatured').checked = true;
    document.getElementById('pIsActive').checked = true;

    document.getElementById('imagePreviewContainer').classList.add('hidden');
    document.getElementById('galleryPreviewContainer').innerHTML = '';

    document.getElementById('productModalContainer').classList.remove('hidden');
}

function openEditProductModal(p) {
    document.getElementById('modalTitle').textContent = 'Edit Product: ' + p.name;
    document.getElementById('modalSubmitBtn').textContent = 'Update Product';
    document.getElementById('formAction').value = 'update';
    document.getElementById('formProductId').value = p.id;
    document.getElementById('formExistingImage').value = p.image_path || '';
    document.getElementById('formExistingGallery').value = p.gallery_images || '';

    document.getElementById('pName').value = p.name || '';
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
    if (p.image_path) {
        const preview = document.getElementById('primaryImagePreview');
        preview.src = '/' + p.image_path.replace(/^\/+/, '');
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
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
