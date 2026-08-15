<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';

if (!empty($_SESSION['user_logged_in'])) {
    header('Location: account.php');
    exit;
}

$redirect = trim($_GET['redirect'] ?? 'account.php');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $redirect = trim($_POST['redirect'] ?? 'account.php');

    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? OR phone = ? LIMIT 1");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_phone'] = $user['phone'];
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid email/phone or password. Please try again.';
        }
    } catch (Exception $e) {
        $error = 'Login error: ' . $e->getMessage();
    }
}

$pageTitle = 'Customer Login - OnlineBdMart';
require_once 'includes/header.php';
?>

<div class="max-w-md mx-auto px-4 py-14">
    <div class="bg-white rounded-3xl border border-slate-200 p-8 sm:p-10 shadow-sm space-y-6">
        <div class="text-center space-y-1">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl mx-auto mb-2"><i class="fas fa-user-circle"></i></div>
            <h1 class="text-2xl font-black font-serif text-slate-900">Welcome Back</h1>
            <p class="text-xs text-slate-500">Sign in to your customer dashboard</p>
        </div>

        <?php if ($error): ?>
        <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs font-bold">
            <i class="fas fa-circle-exclamation mr-1"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-4 text-xs">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

            <div>
                <label class="block text-slate-700 font-bold mb-1">Email Address or Phone Number *</label>
                <input type="text" name="identifier" required placeholder="user@example.com or 017xxxxxxxx" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
            </div>
            <div>
                <label class="block text-slate-700 font-bold mb-1">Password *</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl shadow-lg transition">Sign In to Dashboard &rarr;</button>
        </form>

        <div class="text-center pt-2 text-xs text-slate-500">
            Don't have an account? <a href="register.php" class="text-indigo-600 font-bold hover:underline">Create Account</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
