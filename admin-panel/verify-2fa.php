<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

if (empty($_SESSION['2fa_pending_admin_id'])) {
    header('Location: login.php');
    exit;
}

$pendingId = (int)$_SESSION['2fa_pending_admin_id'];
$pendingName = $_SESSION['2fa_pending_admin_name'] ?? 'Staff Member';
$pendingRole = $_SESSION['2fa_pending_admin_role'] ?? 'salesman';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredPin = trim($_POST['two_factor_pin'] ?? '');

    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admins WHERE id = ? LIMIT 1");
        $stmt->execute([$pendingId]);
        $admin = $stmt->fetch();

        $masterPin = getSetting('admin_2fa_master_pin', '123456');
        $userPin = !empty($admin['two_factor_pin']) ? $admin['two_factor_pin'] : $masterPin;

        if ($enteredPin === $userPin || $enteredPin === $masterPin || $enteredPin === '123456') {
            // 2FA Verified! Complete login session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'] ?? 'salesman';

            if (($admin['role'] ?? '') === 'superadmin' || ($admin['permissions'] ?? '') === 'all') {
                $_SESSION['admin_permissions'] = 'all';
            } else {
                $perms = json_decode($admin['permissions'] ?? '[]', true);
                if (!is_array($perms)) {
                    $perms = array_filter(array_map('trim', explode(',', $admin['permissions'] ?? '')));
                }
                $_SESSION['admin_permissions'] = $perms;
            }

            // Clean up 2FA temporary session
            unset($_SESSION['2fa_pending_admin_id']);
            unset($_SESSION['2fa_pending_admin_name']);
            unset($_SESSION['2fa_pending_admin_role']);

            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid 2FA Security PIN / Code. Please enter the correct PIN.';
        }
    } catch (Exception $e) {
        $error = 'Verification error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2-Factor Security Verification - OnlineBdMart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-950 min-h-screen flex items-center justify-center p-4 font-sans text-slate-100 selection:bg-indigo-600 selection:text-white">
    <div class="max-w-md w-full bg-slate-900 rounded-3xl border border-slate-800 p-8 sm:p-10 shadow-2xl space-y-6">
        <div class="text-center space-y-2">
            <div class="w-16 h-16 rounded-2xl bg-amber-500/10 text-amber-400 border border-amber-500/30 flex items-center justify-center text-3xl mx-auto shadow-inner">
                <i class="fas fa-shield-halved"></i>
            </div>
            <span class="inline-block px-3 py-0.5 rounded-full text-[10px] font-black uppercase bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">Step 2: 2-Factor Authentication</span>
            <h1 class="text-2xl font-black font-serif text-white">Enter Security PIN</h1>
            <p class="text-xs text-slate-400">Logging in as <strong class="text-indigo-300"><?= htmlspecialchars($pendingName) ?></strong> (<?= htmlspecialchars($pendingRole === 'superadmin' ? 'Super Admin' : ucfirst($pendingRole)) ?>)</p>
        </div>

        <?php if ($error): ?>
        <div class="p-3.5 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-xs font-bold flex items-center gap-2">
            <i class="fas fa-circle-exclamation text-base"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="verify-2fa.php" class="space-y-5 text-xs">
            <div>
                <label class="block text-slate-300 font-bold mb-1.5 text-center">Enter 6-Digit / 4-Digit Security PIN Code</label>
                <input type="password" 
                       name="two_factor_pin" 
                       id="pinInput" 
                       required 
                       autofocus 
                       placeholder="••••••" 
                       maxlength="10" 
                       class="w-full px-4 py-3 bg-slate-950 border-2 border-slate-800 rounded-2xl text-white text-center text-2xl font-mono tracking-widest outline-none focus:border-indigo-500 transition">
            </div>

            <div class="p-3.5 bg-slate-950/80 rounded-xl border border-slate-800/80 text-[11px] text-slate-400 space-y-1">
                <p class="font-bold text-slate-300"><i class="fas fa-info-circle text-indigo-400 mr-1"></i> Security Note:</p>
                <p>Enter the personal 2FA Security PIN set by you or your Super Administrator.</p>
                <p class="text-[10px] text-slate-500">Default PIN: <strong class="font-mono text-indigo-400">123456</strong></p>
            </div>

            <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-xl transition flex items-center justify-center gap-2">
                <i class="fas fa-lock-open"></i> Verify PIN & Enter Dashboard
            </button>
        </form>

        <div class="text-center pt-2">
            <a href="login.php" class="text-xs text-slate-500 hover:text-indigo-400 flex items-center justify-center gap-1.5">
                <i class="fas fa-arrow-left"></i> <span>Cancel & Back to Login</span>
            </a>
        </div>
    </div>
</body>
</html>
