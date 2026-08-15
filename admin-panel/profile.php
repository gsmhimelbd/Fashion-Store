<?php
$adminTitle = 'Admin Profile & Security';
require_once __DIR__ . '/header.php';

$msg = '';
$error = '';
try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $newPass = trim($_POST['new_password'] ?? '');

        if ($username && $email) {
            if (!empty($newPass)) {
                $hashed = password_hash($newPass, PASSWORD_BCRYPT);
                $stmt = $db->prepare("UPDATE admins SET username = ?, email = ?, password = ? WHERE id = ?");
                $stmt->execute([$username, $email, $hashed, $_SESSION['admin_id'] ?? 1]);
            } else {
                $stmt = $db->prepare("UPDATE admins SET username = ?, email = ? WHERE id = ?");
                $stmt->execute([$username, $email, $_SESSION['admin_id'] ?? 1]);
            }
            $_SESSION['admin_username'] = $username;
            $msg = 'Profile updated successfully!';
        }
    }

    $adminId = $_SESSION['admin_id'] ?? 1;
    $admin = $db->query("SELECT * FROM admins WHERE id = {$adminId}")->fetch();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="p-4 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-400 text-xs font-bold">
    <i class="fas fa-circle-exclamation mr-1"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm max-w-2xl">
    <div>
        <span class="text-xs font-bold uppercase text-indigo-400">Security</span>
        <h2 class="text-lg font-black text-white mt-1">Admin Account Credentials</h2>
        <p class="text-xs text-slate-400">Update your username, email, and master administrative password.</p>
    </div>

    <form method="POST" action="profile.php" class="space-y-4 text-xs">
        <div>
            <label class="block text-slate-300 font-bold mb-1">Username *</label>
            <input type="text" name="username" value="<?= htmlspecialchars($admin['username'] ?? 'admin') ?>" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
        </div>
        <div>
            <label class="block text-slate-300 font-bold mb-1">Email Address *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($admin['email'] ?? 'admin@fashionstore.com') ?>" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
        </div>
        <div>
            <label class="block text-slate-300 font-bold mb-1">New Password (Leave blank to keep current)</label>
            <input type="password" name="new_password" placeholder="••••••••••••" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
        </div>

        <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg transition">Update Credentials</button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
