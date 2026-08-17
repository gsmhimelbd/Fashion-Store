<?php
$adminTitle = 'Admin Profile & Security';
require_once __DIR__ . '/header.php';

$msg = '';
$error = '';

function uploadAdminAvatar($fileArray) {
    if (!empty($fileArray['name']) && $fileArray['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'];
        if (in_array($ext, $allowed)) {
            $newName = 'admin_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $relPath = "uploads/admin/" . $newName;
            
            $dirs = [
                __DIR__ . '/../uploads/admin/',
                __DIR__ . '/../public/uploads/admin/',
                __DIR__ . '/uploads/admin/',
                dirname(__DIR__) . '/uploads/admin/'
            ];

            foreach ($dirs as $dir) {
                if (!file_exists($dir)) {
                    @mkdir($dir, 0777, true);
                }
            }

            $primaryDest = __DIR__ . '/../' . $relPath;
            if (@move_uploaded_file($fileArray['tmp_name'], $primaryDest)) {
                // Copy to public if exists
                @copy($primaryDest, __DIR__ . '/../public/' . $relPath);
                return $relPath;
            } elseif (is_uploaded_file($fileArray['tmp_name'])) {
                // Fallback copy
                if (@copy($fileArray['tmp_name'], $primaryDest)) {
                    @copy($primaryDest, __DIR__ . '/../public/' . $relPath);
                    return $relPath;
                }
            }
        }
    }
    return null;
}

try {
    $db = getDB();
    $adminId = (int)($_SESSION['admin_id'] ?? 1);
    $adminUser = $_SESSION['admin_username'] ?? 'admin';

    // Auto-heal table columns in database
    try { @$db->exec("ALTER TABLE `admins` ADD COLUMN `profile_photo` varchar(255) DEFAULT 'uploads/admin/avatar.png'"); } catch (Exception $ex) {}
    try { @$db->exec("ALTER TABLE `admins` ADD `profile_photo` varchar(255) DEFAULT 'uploads/admin/avatar.png'"); } catch (Exception $ex) {}
    try { @$db->exec("ALTER TABLE `admins` ADD COLUMN `two_factor_enabled` tinyint(1) DEFAULT 0"); } catch (Exception $ex) {}
    try { @$db->exec("ALTER TABLE `admins` ADD COLUMN `two_factor_pin` varchar(50) DEFAULT '123456'"); } catch (Exception $ex) {}

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? 'Super Admin');
        $username = trim($_POST['username'] ?? $adminUser);
        $email = trim($_POST['email'] ?? 'admin@onlinebdmart.com');
        $newPass = trim($_POST['new_password'] ?? '');
        $twoFactorEnabled = isset($_POST['two_factor_enabled']) ? 1 : 0;
        $twoFactorPin = trim($_POST['two_factor_pin'] ?? '123456');

        $photoPath = trim($_POST['existing_photo'] ?? 'uploads/admin/avatar.png');
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $up = uploadAdminAvatar($_FILES['profile_photo']);
            if ($up) {
                $photoPath = $up;
            }
        }

        if ($username && $email) {
            // Find existing admin record
            $fStmt = $db->prepare("SELECT id FROM admins WHERE id = ? OR username = ? LIMIT 1");
            $fStmt->execute([$adminId, $adminUser]);
            $existingRow = $fStmt->fetch();

            if ($existingRow) {
                $targetId = $existingRow['id'];
                if (!empty($newPass)) {
                    $hashed = password_hash($newPass, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("UPDATE admins SET name = ?, username = ?, email = ?, password = ?, profile_photo = ?, two_factor_enabled = ?, two_factor_pin = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$name, $username, $email, $hashed, $photoPath, $twoFactorEnabled, $twoFactorPin, $targetId]);
                } else {
                    $stmt = $db->prepare("UPDATE admins SET name = ?, username = ?, email = ?, profile_photo = ?, two_factor_enabled = ?, two_factor_pin = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$name, $username, $email, $photoPath, $twoFactorEnabled, $twoFactorPin, $targetId]);
                }
            } else {
                $hashed = !empty($newPass) ? password_hash($newPass, PASSWORD_BCRYPT) : password_hash('password', PASSWORD_BCRYPT);
                $stmt = $db->prepare("INSERT INTO admins (name, username, email, password, profile_photo, two_factor_enabled, two_factor_pin, role, permissions, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'superadmin', 'all', CURRENT_TIMESTAMP)");
                $stmt->execute([$name, $username, $email, $hashed, $photoPath, $twoFactorEnabled, $twoFactorPin]);
                $targetId = $db->lastInsertId();
            }

            $_SESSION['admin_id'] = $targetId;
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_name'] = $name;
            $_SESSION['admin_photo'] = $photoPath;
            $msg = '✓ Admin profile picture, details, and security settings saved successfully!';
        }
    }

    $stmt = $db->prepare("SELECT * FROM admins WHERE id = ? OR username = ? LIMIT 1");
    $stmt->execute([$adminId, $adminUser]);
    $admin = $stmt->fetch();

    if (!$admin) {
        $admin = [
            'name' => $_SESSION['admin_name'] ?? 'Super Admin',
            'username' => $adminUser,
            'email' => 'admin@onlinebdmart.com',
            'profile_photo' => $_SESSION['admin_photo'] ?? 'uploads/admin/avatar.png',
            'two_factor_enabled' => 0,
            'two_factor_pin' => '123456'
        ];
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    $admin = ['name' => 'Super Admin', 'username' => 'admin', 'email' => 'admin@onlinebdmart.com', 'profile_photo' => 'uploads/admin/avatar.png'];
}

$avatar = !empty($admin['profile_photo']) ? $admin['profile_photo'] : (!empty($_SESSION['admin_photo']) ? $_SESSION['admin_photo'] : 'uploads/admin/avatar.png');
$avatarVer = file_exists(__DIR__ . '/../' . ltrim($avatar, '/')) ? @filemtime(__DIR__ . '/../' . ltrim($avatar, '/')) : time();
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
            <img src="/<?= ltrim($avatar, '/') ?>?v=<?= $avatarVer ?>" onerror="this.src='/images/products/watch-1.jpg'" class="w-16 h-16 rounded-2xl object-cover border-2 border-indigo-500/40 bg-slate-900 shrink-0 shadow-md">
            <div class="flex-1">
                <label class="block text-white font-bold mb-1"><i class="fas fa-camera text-indigo-400 mr-1"></i> Upload Profile Picture (from Local Computer)</label>
                <input type="file" name="profile_photo" accept="image/*" class="w-full px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-300 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white cursor-pointer">
                <p class="text-[10px] text-slate-400 mt-1">Supports JPG, PNG, WEBP, SVG or GIF format.</p>
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

        <!-- Personal 2FA Security PIN -->
        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-white text-sm"><i class="fas fa-shield-halved text-amber-400 mr-1.5"></i> My 2-Factor Authentication (2FA) PIN</h3>
                    <p class="text-slate-400 text-[11px]">Require your personal security PIN during login verification.</p>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="two_factor_enabled" <?= !empty($admin['two_factor_enabled']) ? 'checked' : '' ?> class="rounded text-amber-500">
                    <span class="text-amber-400 font-bold">2FA Active</span>
                </label>
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Set 6-Digit / 4-Digit Security PIN</label>
                <input type="password" name="two_factor_pin" value="<?= htmlspecialchars($admin['two_factor_pin'] ?? '123456') ?>" placeholder="e.g. 123456" maxlength="10" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono text-base outline-none">
            </div>
        </div>

        <button type="submit" class="px-8 py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg transition">
            Save Profile Credentials
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
