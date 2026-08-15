<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';

if (!empty($_SESSION['user_logged_in'])) {
    header('Location: account.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            header('Location: account.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    } catch (Exception $e) {
        $error = 'Login error: ' . $e->getMessage();
    }
}

$pageTitle = 'Customer Login - OnlineBdMart';
require_once 'includes/header.php';
?>

<div class="max-w-md mx-auto px-4 py-12">
    <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm space-y-6">
        <div class="text-center space-y-1">
            <h1 class="text-2xl font-black font-serif text-slate-900">Welcome Back</h1>
            <p class="text-xs text-slate-500">Sign in to your customer account</p>
        </div>

        <?php if ($error): ?>
        <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs font-bold">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-4 text-xs">
            <div>
                <label class="block text-slate-700 font-bold mb-1">Email Address</label>
                <input type="email" name="email" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none">
            </div>
            <div>
                <label class="block text-slate-700 font-bold mb-1">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none">
            </div>

            <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow transition">Sign In</button>
        </form>

        <div class="text-center pt-2 text-xs text-slate-500">
            Don't have an account? <a href="register.php" class="text-indigo-600 font-bold hover:underline">Register now</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
