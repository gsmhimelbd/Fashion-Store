<?php
$adminTitle = 'Slider Banners Management';
require_once __DIR__ . '/header.php';

$msg = '';
$error = '';

function uploadBannerFile($fileArray) {
    if (!empty($fileArray['name']) && $fileArray['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'];
        if (in_array($ext, $allowed)) {
            $newName = uniqid('banner_', true) . '.' . $ext;
            $relPath = "uploads/banners/" . $newName;
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
            $title = trim($_POST['title'] ?? '');
            $subtitle = trim($_POST['subtitle'] ?? '');
            $badge = trim($_POST['badge_text'] ?? '');
            $btnText = trim($_POST['button_text'] ?? 'Shop Now');
            $btnUrl = trim($_POST['button_url'] ?? 'shop.php');
            $displayOrder = (int)($_POST['display_order'] ?? 1);
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            $imagePath = trim($_POST['existing_image'] ?? 'images/hero/hero-1.jpg');
            if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
                $up = uploadBannerFile($_FILES['banner_image']);
                if ($up) $imagePath = $up;
            }

            if ($action === 'create') {
                $stmt = $db->prepare("INSERT INTO banners (title, subtitle, badge_text, button_text, button_url, image_path, display_order, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
                $stmt->execute([$title, $subtitle, $badge, $btnText, $btnUrl, $imagePath, $displayOrder, $isActive]);
                $msg = 'Banner slide added successfully!';
            } else {
                $id = (int)$_POST['banner_id'];
                $stmt = $db->prepare("UPDATE banners SET title = ?, subtitle = ?, badge_text = ?, button_text = ?, button_url = ?, image_path = ?, display_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$title, $subtitle, $badge, $btnText, $btnUrl, $imagePath, $displayOrder, $isActive, $id]);
                $msg = 'Banner slide updated successfully!';
            }
        } elseif ($action === 'delete') {
            $id = (int)$_POST['banner_id'];
            $db->prepare("DELETE FROM banners WHERE id = ?")->execute([$id]);
            $msg = 'Banner removed!';
        }
    }

    $banners = $db->query("SELECT * FROM banners ORDER BY display_order ASC, id DESC")->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
    $banners = [];
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

<div class="flex flex-wrap items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-6 rounded-3xl shadow-sm">
    <div>
        <span class="text-xs font-bold uppercase text-indigo-400">Home Showcase</span>
        <h2 class="text-lg font-black text-white mt-1">Hero Carousel Banners (<?= count($banners) ?>)</h2>
        <p class="text-xs text-slate-400">Upload slider graphics directly from your computer and customize text, badges, and CTA links.</p>
    </div>
    <button type="button" onclick="openAddBannerModal()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl text-xs shadow-lg transition flex items-center gap-2">
        <i class="fas fa-plus"></i> <span>+ Add New Banner</span>
    </button>
</div>

<!-- Banners Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <?php foreach ($banners as $b): 
        $bJson = htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8');
    ?>
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-sm flex flex-col justify-between">
        <div class="relative aspect-[21/9] bg-slate-950">
            <img src="/<?= ltrim($b['image_path'], '/') ?>" class="w-full h-full object-cover">
            <span class="absolute top-3 left-3 px-2.5 py-1 bg-black/70 backdrop-blur-md text-amber-400 rounded-full text-[10px] font-bold">
                <?= htmlspecialchars($b['badge_text'] ?: 'SLIDE') ?>
            </span>
            <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-[10px] font-bold <?= $b['is_active'] ? 'bg-emerald-500/80 text-white' : 'bg-rose-500/80 text-white' ?>">
                <?= $b['is_active'] ? 'Active' : 'Hidden' ?>
            </span>
        </div>

        <div class="p-6 space-y-3">
            <div>
                <h3 class="text-base font-extrabold text-white"><?= htmlspecialchars($b['title']) ?></h3>
                <p class="text-xs text-slate-400 mt-1"><?= htmlspecialchars($b['subtitle']) ?></p>
            </div>

            <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-800">
                <span class="text-indigo-400 font-bold">Button: "<?= htmlspecialchars($b['button_text']) ?>" &rarr; <?= htmlspecialchars($b['button_url']) ?></span>
                <span class="text-slate-400">Order: #<?= $b['display_order'] ?></span>
            </div>

            <div class="pt-3 border-t border-slate-800 flex items-center justify-end gap-2">
                <button type="button" onclick='openEditBannerModal(<?= $bJson ?>)' class="px-3 py-1.5 bg-indigo-600/20 hover:bg-indigo-600 text-indigo-400 hover:text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fas fa-pen-to-square"></i> Edit
                </button>
                <form method="POST" action="banners.php" onsubmit="return confirm('Delete this banner?');" class="inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="banner_id" value="<?= $b['id'] ?>">
                    <button type="submit" class="px-3 py-1.5 bg-rose-500/20 hover:bg-rose-500 text-rose-400 hover:text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                        <i class="fas fa-trash-can"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- BANNER MODAL -->
<div id="bannerModalContainer" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="closeBannerModal()"></div>
    <div class="min-h-screen flex items-center justify-center p-4 sm:p-6">
        <div class="relative w-full max-w-2xl bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden z-10">
            <div class="p-6 border-b border-slate-800 flex items-center justify-between bg-slate-950">
                <h3 class="text-base font-extrabold text-white" id="bannerModalTitle">Add Hero Banner</h3>
                <button type="button" onclick="closeBannerModal()" class="text-slate-400 hover:text-white"><i class="fas fa-times text-lg"></i></button>
            </div>

            <form method="POST" action="banners.php" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-4 text-xs">
                <input type="hidden" name="action" id="bannerAction" value="create">
                <input type="hidden" name="banner_id" id="bannerId" value="">
                <input type="hidden" name="existing_image" id="bannerExistingImage" value="images/hero/hero-1.jpg">

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Banner Title *</label>
                    <input type="text" name="title" id="bTitle" required placeholder="e.g. Premium Quartz & Leather Collection" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Subtitle / Description</label>
                    <input type="text" name="subtitle" id="bSubtitle" placeholder="e.g. Discover original accessories with Cash on Delivery" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Badge Highlight</label>
                        <input type="text" name="badge_text" id="bBadge" placeholder="✨ 2026 COLLECTION" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Button Text</label>
                        <input type="text" name="button_text" id="bBtnText" value="Shop Now" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Button Link</label>
                        <input type="text" name="button_url" id="bBtnUrl" value="shop.php" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-2">
                    <label class="block text-white font-bold"><i class="fas fa-upload text-indigo-400 mr-1"></i> Upload Banner Image (from Local Computer)</label>
                    <input type="file" name="banner_image" accept="image/*" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-slate-300 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500">
                    <div id="bannerPreviewBox" class="pt-2 hidden">
                        <img id="bannerImgPreview" src="" class="w-full h-24 object-cover rounded-xl border border-slate-800">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <div class="flex items-center gap-3">
                        <label class="block text-slate-300 font-bold">Display Order:</label>
                        <input type="number" name="display_order" id="bOrder" value="1" class="w-16 px-2.5 py-1 bg-slate-950 border border-slate-800 rounded-lg text-white font-bold outline-none">
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" id="bIsActive" value="1" checked class="rounded text-indigo-600">
                        <span class="text-slate-300 font-bold">Active in Slider</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-slate-800 flex justify-end gap-3">
                    <button type="button" onclick="closeBannerModal()" class="px-5 py-2.5 bg-slate-800 text-slate-300 font-bold rounded-xl">Cancel</button>
                    <button type="submit" class="px-8 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow">Save Banner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddBannerModal() {
    document.getElementById('bannerModalTitle').textContent = 'Add Hero Banner';
    document.getElementById('bannerAction').value = 'create';
    document.getElementById('bannerId').value = '';
    document.getElementById('bannerExistingImage').value = 'images/hero/hero-1.jpg';
    document.getElementById('bTitle').value = '';
    document.getElementById('bSubtitle').value = '';
    document.getElementById('bBadge').value = '✨ NEW ARRIVALS';
    document.getElementById('bBtnText').value = 'Shop Now';
    document.getElementById('bBtnUrl').value = 'shop.php';
    document.getElementById('bOrder').value = '1';
    document.getElementById('bIsActive').checked = true;
    document.getElementById('bannerPreviewBox').classList.add('hidden');
    document.getElementById('bannerModalContainer').classList.remove('hidden');
}

function openEditBannerModal(b) {
    document.getElementById('bannerModalTitle').textContent = 'Edit Banner: ' + b.title;
    document.getElementById('bannerAction').value = 'update';
    document.getElementById('bannerId').value = b.id;
    document.getElementById('bannerExistingImage').value = b.image_path || '';
    document.getElementById('bTitle').value = b.title || '';
    document.getElementById('bSubtitle').value = b.subtitle || '';
    document.getElementById('bBadge').value = b.badge_text || '';
    document.getElementById('bBtnText').value = b.button_text || 'Shop Now';
    document.getElementById('bBtnUrl').value = b.button_url || 'shop.php';
    document.getElementById('bOrder').value = b.display_order || '1';
    document.getElementById('bIsActive').checked = Boolean(Number(b.is_active));

    if (b.image_path) {
        document.getElementById('bannerImgPreview').src = '/' + b.image_path.replace(/^\/+/, '');
        document.getElementById('bannerPreviewBox').classList.remove('hidden');
    }
    document.getElementById('bannerModalContainer').classList.remove('hidden');
}

function closeBannerModal() {
    document.getElementById('bannerModalContainer').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
