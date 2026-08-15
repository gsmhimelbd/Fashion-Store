<?php
$adminTitle = 'Admin Profile & Security';
require_once __DIR__ . '/header.php';

$msg = '';
$error = '';

function uploadAdminAvatar($fileArray) {
    if (!empty($fileArray['name']) && $fileArray['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
        if (in_array($ext, $allowed)) {
            $newName = 'admin_' . time() . '.' . $ext;
            $relPath = "uploads/admin/" . $newName;
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
    $adminId = $_SESSION['admin_id'] ?? 1;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? 'Super Admin');
        $username = trim($_POST['username'] ?? 'admin');
        $email = trim($_POST['email'] ?? 'admin@onlinebdmart.com');
        $newPass = trim($_POST['new_password'] ?? '');

        $photoPath = trim($_POST['existing_photo'] ?? 'uploads/admin/avatar.png');
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $up = uploadAdminAvatar($_FILES['profile_photo']);
            if ($up) $photoPath = $up;
        }

        if ($username && $email) {
            if (!empty($newPass)) {
                $hashed = password_hash($newPass, PASSWORD_BCRYPT);
                $stmt = $db->prepare("UPDATE admins SET name = ?, username = ?, email = ?, password = ?, profile_photo = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$name, $username, $email, $hashed, $photoPath, $adminId]);
            } else {
                $stmt = $db->prepare("UPDATE admins SET name = ?, username = ?, email = ?, profile_photo = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$name, $username, $email, $photoPath, $adminId]);
            }
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_name'] = $name;
            $msg = 'Admin profile, avatar photo, and credentials updated successfully!';
        }
    }

    $admin = $db->query("SELECT * FROM admins WHERE id = {$adminId}")->fetch();
    if (!$admin) {
        $admin = ['name' => 'OnlineBdMart Admin', 'username' => 'admin', 'email' => 'admin@onlinebdmart.com', 'profile_photo' => 'uploads/admin/avatar.png'];
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

$avatar = !empty($admin['profile_photo']) ? $admin['profile_photo'] : 'images/products/watch-1.jpg';
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

<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm max-w-2xl">
    <div>
        <span class="text-xs font-bold uppercase text-indigo-400">Account Security</span>
        <h2 class="text-lg font-black text-white mt-1">Admin Profile & Security</h2>
        <p class="text-xs text-slate-400">Upload admin avatar picture from computer, update name, email, and password.</p>
    </div>

    <form method="POST" action="profile.php" enctype="multipart/form-data" class="space-y-5 text-xs">
        <input type="hidden" name="existing_photo" value="<?= htmlspecialchars($avatar) ?>">

        <!-- Profile Photo -->
        <div class="flex items-center gap-5 p-4 rounded-2xl bg-slate-950 border border-slate-800">
            <img src="/<?= ltrim($avatar, '/') ?>" class="w-16 h-16 rounded-2xl object-cover border border-slate-800 bg-slate-900 shrink-0">
            <div class="flex-1">
                <label class="block text-white font-bold mb-1"><i class="fas fa-camera text-indigo-400 mr-1"></i> Upload Profile Picture (from Local Computer)</label>
                <input type="file" name="profile_photo" accept="image/*" class="w-full px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-300 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white">
            </div>
        </div>

        <div>
            <label class="block text-slate-300 font-bold mb-1">Full Name *</label>
            <input type="text" name="name" value="<?= htmlspecialchars($admin['name'] ?? 'Super Admin') ?>" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Username *</label>
                <input type="text" name="username" value="<?= htmlspecialchars($admin['username'] ?? 'admin') ?>" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none font-mono">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Email Address *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($admin['email'] ?? 'admin@onlinebdmart.com') ?>" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 space-y-2">
            <label class="block text-amber-400 font-bold"><i class="fas fa-lock mr-1"></i> New Password (Leave blank to keep current password)</label>
            <input type="password" name="new_password" placeholder="Enter new strong password" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
        </div>

        <button type="submit" class="px-8 py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg transition">
            Save Profile Credentials
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
