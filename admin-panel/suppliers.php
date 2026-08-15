<?php
$adminTitle = 'Suppliers & Product Vendors';
require_once __DIR__ . '/header.php';

$msg = '';
$error = '';

function uploadSupplierFile($fileArray) {
    if (!empty($fileArray['name']) && $fileArray['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'];
        if (in_array($ext, $allowed)) {
            $newName = uniqid('supp_', true) . '.' . $ext;
            $relPath = "uploads/suppliers/" . $newName;
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'create' || $action === 'update') {
            $name = trim($_POST['name'] ?? '');
            $contact = trim($_POST['contact_person'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $supplyProducts = trim($_POST['supply_products'] ?? '');
            $category = trim($_POST['category'] ?? '');

            $photoPath = trim($_POST['existing_photo'] ?? 'uploads/suppliers/supplier-default.jpg');
            if (isset($_FILES['supplier_photo']) && $_FILES['supplier_photo']['error'] === UPLOAD_ERR_OK) {
                $up = uploadSupplierFile($_FILES['supplier_photo']);
                if ($up) $photoPath = $up;
            }

            if ($action === 'create') {
                $stmt = $db->prepare("INSERT INTO suppliers (name, contact_person, phone, email, address, photo, supply_products, category, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $contact, $phone, $email, $address, $photoPath, $supplyProducts, $category]);
                $msg = 'Supplier added with profile picture and product list!';
            } else {
                $id = (int)$_POST['supplier_id'];
                $stmt = $db->prepare("UPDATE suppliers SET name = ?, contact_person = ?, phone = ?, email = ?, address = ?, photo = ?, supply_products = ?, category = ? WHERE id = ?");
                $stmt->execute([$name, $contact, $phone, $email, $address, $photoPath, $supplyProducts, $category, $id]);
                $msg = 'Supplier updated successfully!';
            }
        } elseif ($action === 'delete') {
            $id = (int)$_POST['supplier_id'];
            $db->prepare("DELETE FROM suppliers WHERE id = ?")->execute([$id]);
            $msg = 'Supplier deleted!';
        }
    }

    $suppliers = $db->query("SELECT * FROM suppliers ORDER BY id DESC")->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
    $suppliers = [];
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1.5"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="flex flex-wrap items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-6 rounded-3xl shadow-sm">
    <div>
        <span class="text-xs font-bold uppercase text-indigo-400">Supply Chain & Vendors</span>
        <h2 class="text-lg font-black text-white mt-1">Suppliers & Sourcing Partners (<?= count($suppliers) ?>)</h2>
        <p class="text-xs text-slate-400">Add product suppliers with profile pictures, contact details, and catalog product supplies.</p>
    </div>
    <button type="button" onclick="openAddSupplierModal()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl text-xs shadow-lg transition flex items-center gap-2">
        <i class="fas fa-plus"></i> <span>+ Add Supplier</span>
    </button>
</div>

<!-- Supplier Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($suppliers as $s): 
        $sJson = htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8');
        $photo = !empty($s['photo']) ? $s['photo'] : 'images/products/watch-1.jpg';
        $wa = preg_replace('/[^0-9]/', '', $s['phone'] ?? '');
        if (str_starts_with($wa, '0')) $wa = '88' . $wa;
    ?>
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4 flex flex-col justify-between">
        <div class="space-y-4">
            <div class="flex items-center gap-4">
                <img src="/<?= ltrim($photo, '/') ?>" class="w-14 h-14 object-cover rounded-2xl bg-slate-950 border border-slate-800 shrink-0">
                <div class="min-w-0 flex-1">
                    <span class="text-[10px] uppercase font-bold text-indigo-400 block"><?= htmlspecialchars($s['category'] ?? 'General Supplier') ?></span>
                    <h3 class="text-sm font-extrabold text-white truncate"><?= htmlspecialchars($s['name']) ?></h3>
                    <p class="text-xs text-slate-300 font-medium"><?= htmlspecialchars($s['contact_person'] ?? 'Owner') ?></p>
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800/80 space-y-2 text-xs">
                <p class="text-slate-300"><i class="fas fa-phone text-emerald-400 mr-1.5"></i> Phone: <strong><?= htmlspecialchars($s['phone'] ?? 'N/A') ?></strong></p>
                <?php if (!empty($s['email'])): ?>
                <p class="text-slate-400"><i class="fas fa-envelope text-indigo-400 mr-1.5"></i> <?= htmlspecialchars($s['email']) ?></p>
                <?php endif; ?>
                <?php if (!empty($s['address'])): ?>
                <p class="text-slate-400"><i class="fas fa-location-dot text-rose-400 mr-1.5"></i> <?= htmlspecialchars($s['address']) ?></p>
                <?php endif; ?>
            </div>

            <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800 text-xs space-y-1">
                <span class="font-bold text-slate-400 uppercase text-[10px] block">Supplied Products:</span>
                <p class="text-indigo-300 leading-relaxed"><?= htmlspecialchars($s['supply_products'] ?: 'General fashion accessories and gadgets') ?></p>
            </div>
        </div>

        <div class="pt-3 border-t border-slate-800 flex items-center justify-between gap-2">
            <?php if ($wa): ?>
            <a href="https://wa.me/<?= $wa ?>" target="_blank" class="px-3 py-1.5 bg-emerald-600/20 text-emerald-400 hover:bg-emerald-600 hover:text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fab fa-whatsapp"></i> Chat
            </a>
            <?php endif; ?>
            <div class="flex items-center gap-2">
                <button type="button" onclick='openEditSupplierModal(<?= $sJson ?>)' class="p-2 bg-indigo-600/20 text-indigo-400 hover:bg-indigo-600 hover:text-white rounded-xl text-xs" title="Edit">
                    <i class="fas fa-pen-to-square"></i>
                </button>
                <form method="POST" action="suppliers.php" onsubmit="return confirm('Delete this supplier?');" class="inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="supplier_id" value="<?= $s['id'] ?>">
                    <button type="submit" class="p-2 bg-rose-500/20 text-rose-400 hover:bg-rose-500 hover:text-white rounded-xl text-xs" title="Delete">
                        <i class="fas fa-trash-can"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- SUPPLIER MODAL -->
<div id="supplierModal" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="closeSupplierModal()"></div>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="relative w-full max-w-xl bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden z-10">
            <div class="p-6 border-b border-slate-800 flex items-center justify-between bg-slate-950">
                <h3 class="text-base font-extrabold text-white" id="suppModalTitle">Add New Supplier</h3>
                <button type="button" onclick="closeSupplierModal()" class="text-slate-400 hover:text-white"><i class="fas fa-times text-lg"></i></button>
            </div>

            <form method="POST" action="suppliers.php" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-4 text-xs">
                <input type="hidden" name="action" id="suppAction" value="create">
                <input type="hidden" name="supplier_id" id="suppId" value="">
                <input type="hidden" name="existing_photo" id="suppExistingPhoto" value="uploads/suppliers/supplier-default.jpg">

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Supplier / Factory Name *</label>
                    <input type="text" name="name" id="sName" required placeholder="e.g. BD Watch Importers Ltd" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Contact Person Name</label>
                        <input type="text" name="contact_person" id="sContact" placeholder="e.g. Kamal Hossain" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Phone / WhatsApp *</label>
                        <input type="tel" name="phone" id="sPhone" required placeholder="017xxxxxxxx" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Email Address</label>
                        <input type="email" name="email" id="sEmail" placeholder="supplier@example.com" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Supply Category</label>
                        <input type="text" name="category" id="sCategory" placeholder="e.g. Quartz Watches & Leather" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Products Supplied by this Vendor *</label>
                    <input type="text" name="supply_products" id="sSupplyProducts" placeholder="e.g. Naviforce Chronograph, Curren Watches, Cowhide Leather Wallets" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                </div>

                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-2">
                    <label class="block text-white font-bold"><i class="fas fa-upload text-indigo-400 mr-1"></i> Upload Supplier Photo / Logo (from Computer)</label>
                    <input type="file" name="supplier_photo" accept="image/*" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-slate-300 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white">
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Warehouse / Factory Address</label>
                    <textarea name="address" id="sAddress" rows="2" placeholder="Chawkbazar, Dhaka / Tangail" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-800 flex justify-end gap-3">
                    <button type="button" onclick="closeSupplierModal()" class="px-5 py-2.5 bg-slate-800 text-slate-300 font-bold rounded-xl">Cancel</button>
                    <button type="submit" class="px-7 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddSupplierModal() {
    document.getElementById('suppModalTitle').textContent = 'Add New Supplier';
    document.getElementById('suppAction').value = 'create';
    document.getElementById('suppId').value = '';
    document.getElementById('suppExistingPhoto').value = 'uploads/suppliers/supplier-default.jpg';
    document.getElementById('sName').value = '';
    document.getElementById('sContact').value = '';
    document.getElementById('sPhone').value = '';
    document.getElementById('sEmail').value = '';
    document.getElementById('sCategory').value = 'Watches & Leather';
    document.getElementById('sSupplyProducts').value = '';
    document.getElementById('sAddress').value = '';
    document.getElementById('supplierModal').classList.remove('hidden');
}

function openEditSupplierModal(s) {
    document.getElementById('suppModalTitle').textContent = 'Edit Supplier: ' + s.name;
    document.getElementById('suppAction').value = 'update';
    document.getElementById('suppId').value = s.id;
    document.getElementById('suppExistingPhoto').value = s.photo || '';
    document.getElementById('sName').value = s.name || '';
    document.getElementById('sContact').value = s.contact_person || '';
    document.getElementById('sPhone').value = s.phone || '';
    document.getElementById('sEmail').value = s.email || '';
    document.getElementById('sCategory').value = s.category || '';
    document.getElementById('sSupplyProducts').value = s.supply_products || '';
    document.getElementById('sAddress').value = s.address || '';
    document.getElementById('supplierModal').classList.remove('hidden');
}

function closeSupplierModal() {
    document.getElementById('supplierModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
