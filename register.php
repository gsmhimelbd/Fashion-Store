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
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($name && $email && $password) {
        try {
            $db = getDB();
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (name, email, phone, password, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$name, $email, $phone, $hashed]);

            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $db->lastInsertId();
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            header('Location: account.php');
            exit;
        } catch (Exception $e) {
            $error = 'Registration failed: ' . $e->getMessage();
        }
    } else {
        $error = 'Please fill out all required fields.';
    }
}

$pageTitle = 'Register Account - OnlineBdMart';
require_once 'includes/header.php';
?>

<div class="max-w-md mx-auto px-4 py-12">
    <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm space-y-6">
        <div class="text-center space-y-1">
            <h1 class="text-2xl font-black font-serif text-slate-900">Create Account</h1>
            <p class="text-xs text-slate-500">Track orders and manage wholesale purchases</p>
        </div>

        <?php if ($error): ?>
        <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs font-bold">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="register.php" class="space-y-4 text-xs">
            <div>
                <label class="block text-slate-700 font-bold mb-1">Full Name *</label>
                <input type="text" name="name" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none">
            </div>
            <div>
                <label class="block text-slate-700 font-bold mb-1">Email Address *</label>
                <input type="email" name="email" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none">
            </div>
            <div>
                <label class="block text-slate-700 font-bold mb-1">Mobile Phone *</label>
                <input type="tel" name="phone" required placeholder="017xxxxxxxx" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none">
            </div>
            <div>
                <label class="block text-slate-700 font-bold mb-1">Password *</label>
                <input type="password" name="password" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none">
            </div>

            <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow transition">Register</button>
        </form>

        <div class="text-center pt-2 text-xs text-slate-500">
            Already have an account? <a href="login.php" class="text-indigo-600 font-bold hover:underline">Sign in</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
