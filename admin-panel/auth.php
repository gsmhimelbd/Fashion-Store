<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function checkAdminAuth() {
    if (empty($_SESSION['admin_logged_in']) && empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function getAdminPermissions() {
    if (!empty($_SESSION['admin_permissions'])) {
        return $_SESSION['admin_permissions'];
    }

    try {
        $db = getDB();
        $adminId = (int)($_SESSION['admin_id'] ?? 1);
        $stmt = $db->prepare("SELECT role, permissions FROM admins WHERE id = ? LIMIT 1");
        $stmt->execute([$adminId]);
        $adm = $stmt->fetch();

        if ($adm) {
            $role = $adm['role'] ?? 'superadmin';
            $_SESSION['admin_role'] = $role;

            if ($role === 'superadmin' || ($adm['permissions'] ?? '') === 'all') {
                $_SESSION['admin_permissions'] = 'all';
                return 'all';
            }

            $perms = json_decode($adm['permissions'] ?? '[]', true);
            if (!is_array($perms)) {
                $perms = array_filter(array_map('trim', explode(',', $adm['permissions'] ?? '')));
            }
            $_SESSION['admin_permissions'] = $perms;
            return $perms;
        }
    } catch (Exception $e) {}

    $_SESSION['admin_permissions'] = 'all';
    return 'all';
}

function hasPermission($section) {
    $perms = getAdminPermissions();
    if ($perms === 'all' || ($_SESSION['admin_role'] ?? 'superadmin') === 'superadmin') {
        return true;
    }
    if (is_array($perms)) {
        return in_array($section, $perms);
    }
    return false;
}

function requirePermission($section) {
    checkAdminAuth();
    if (!hasPermission($section)) {
        $adminTitle = 'Access Denied';
        require_once __DIR__ . '/header.php';
        echo '
        <div class="max-w-2xl mx-auto py-16 text-center space-y-4 bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl">
            <div class="w-16 h-16 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center text-3xl mx-auto"><i class="fas fa-lock"></i></div>
            <h2 class="text-xl font-extrabold text-white">Restricted Section - Access Denied</h2>
            <p class="text-xs text-slate-400 leading-relaxed">Your staff / salesman account does not have permission to access <strong>' . htmlspecialchars((string)$section) . '</strong>.<br>Please contact the Super Administrator if you need access to this feature.</p>
            <div class="pt-4"><a href="index.php" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow inline-block">Return to Dashboard</a></div>
        </div>
        ';
        require_once __DIR__ . '/footer.php';
        exit;
    }
}
