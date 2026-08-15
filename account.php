<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';

if (empty($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit;
}

$userOrders = [];
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM orders WHERE customer_email = ? ORDER BY id DESC");
    $stmt->execute([$_SESSION['user_email'] ?? '']);
    $userOrders = $stmt->fetchAll();
} catch (Exception $e) {}

$pageTitle = 'My Account - OnlineBdMart';
require_once 'includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        <div class="flex items-center justify-between border-b pb-4">
            <div>
                <h1 class="text-2xl font-black font-serif text-slate-900">Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Customer') ?></h1>
                <p class="text-xs text-slate-500"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></p>
            </div>
            <a href="logout.php" class="px-4 py-2 bg-slate-100 hover:bg-rose-50 text-rose-600 rounded-xl text-xs font-bold transition">Sign Out</a>
        </div>

        <div>
            <h2 class="text-sm font-bold uppercase text-slate-900 mb-4">My Order History (<?= count($userOrders) ?>)</h2>
            <?php if (!empty($userOrders)): ?>
                <div class="space-y-3">
                    <?php foreach ($userOrders as $o): ?>
                    <div class="p-4 rounded-2xl bg-slate-50 border flex items-center justify-between text-xs">
                        <div>
                            <span class="font-mono font-bold text-slate-900"><?= htmlspecialchars($o['order_number']) ?></span>
                            <p class="text-slate-500 text-[11px]"><?= date('d M Y', strtotime($o['created_at'])) ?> • <?= htmlspecialchars($o['district_name'] ?? 'Dhaka') ?></p>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-indigo-600">৳<?= number_format($o['total_amount'], 2) ?></span>
                            <span class="block uppercase text-[10px] font-bold text-emerald-600"><?= htmlspecialchars($o['status']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="p-8 text-center text-slate-400 text-xs bg-slate-50 rounded-2xl border">
                    You haven't placed any orders with this email yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
