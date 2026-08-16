<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';

// Handle AJAX cart mutations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input) {
        $action = $input['action'] ?? '';
        $productId = (int)($input['product_id'] ?? 0);
        $qty = (int)($input['quantity'] ?? 1);
        $isWholesale = (bool)($input['is_wholesale'] ?? false);

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        try {
            $db = getDB();
            if ($action === 'add' && $productId > 0) {
                $stmt = $db->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
                $stmt->execute([$productId]);
                $p = $stmt->fetch();

                if ($p) {
                    $itemPrice = ($p['sale_price'] && $p['sale_price'] > 0 && $p['sale_price'] < $p['price']) ? (float)$p['sale_price'] : (float)$p['price'];
                    if ($isWholesale && $p['is_wholesale'] && $p['wholesale_price'] > 0) {
                        $itemPrice = (float)$p['wholesale_price'];
                    }

                    if (isset($_SESSION['cart'][$productId])) {
                        $_SESSION['cart'][$productId]['quantity'] += $qty;
                    } else {
                        $_SESSION['cart'][$productId] = [
                            'id' => $p['id'],
                            'name' => $p['name'],
                            'price' => $itemPrice,
                            'quantity' => $qty,
                            'image' => '/' . ltrim($p['image_path'], '/'),
                            'is_wholesale' => $isWholesale
                        ];
                    }
                }
            } elseif ($action === 'update' && $productId > 0) {
                if ($qty <= 0) {
                    unset($_SESSION['cart'][$productId]);
                } else if (isset($_SESSION['cart'][$productId])) {
                    $_SESSION['cart'][$productId]['quantity'] = $qty;
                }
            } elseif ($action === 'remove' && $productId > 0) {
                unset($_SESSION['cart'][$productId]);
            }
        } catch (Exception $e) {}

        // Calculate totals
        $cCount = 0;
        $cSubtotal = 0.0;
        $cItems = [];
        foreach ($_SESSION['cart'] as $item) {
            $cCount += $item['quantity'];
            $cSubtotal += $item['price'] * $item['quantity'];
            $cItems[] = $item;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'count' => $cCount,
            'subtotal' => $cSubtotal,
            'items' => $cItems,
            'message' => 'Cart updated!'
        ]);
        exit;
    }
}

// Render regular Cart Page
$pageTitle = 'Your Shopping Cart - OnlineBdMart';
require_once 'includes/header.php';
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold font-serif text-slate-900">Your Shopping Cart</h1>
        <p class="text-xs text-slate-500 mt-1">Review your selected items before proceeding to checkout.</p>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center space-y-4">
            <i class="fas fa-bag-shopping text-4xl text-slate-300"></i>
            <h2 class="text-lg font-bold text-slate-800">Your cart is currently empty</h2>
            <p class="text-xs text-slate-400">Discover premium fashion accessories and smart lifestyle goods.</p>
            <a href="shop.php" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow inline-block">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Items list -->
            <div class="lg:col-span-2 space-y-4">
                <?php foreach ($cartItems as $item): ?>
                <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <img src="/<?= ltrim($item['image'], '/') ?>" class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-xl bg-slate-50 border shrink-0">
                        <div>
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900 line-clamp-1"><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="text-xs font-black text-indigo-600 mt-1">৳<?= number_format($item['price'], 2) ?></p>
                            <?php if (!empty($item['is_wholesale'])): ?>
                            <span class="inline-block text-[10px] font-bold bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full mt-1">Wholesale Item</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex items-center border border-slate-200 rounded-xl bg-slate-50">
                            <button onclick="updateCartQty(<?= $item['id'] ?>, <?= $item['quantity'] - 1 ?>); setTimeout(() => location.reload(), 200);" class="w-8 h-8 flex items-center justify-center font-bold text-slate-600">-</button>
                            <span class="w-8 text-center text-xs font-bold"><?= $item['quantity'] ?></span>
                            <button onclick="updateCartQty(<?= $item['id'] ?>, <?= $item['quantity'] + 1 ?>); setTimeout(() => location.reload(), 200);" class="w-8 h-8 flex items-center justify-center font-bold text-slate-600">+</button>
                        </div>
                        <span class="text-xs sm:text-sm font-black text-slate-900 w-20 text-right">৳<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                        <button onclick="removeCartItem(<?= $item['id'] ?>); setTimeout(() => location.reload(), 200);" class="text-slate-400 hover:text-rose-600 p-2"><i class="fas fa-trash-can text-sm"></i></button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Summary -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-6 h-fit">
                <h3 class="text-base font-extrabold text-slate-900">Order Summary</h3>
                <div class="space-y-3 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal (<?= $cartCount ?> items)</span>
                        <span class="font-bold text-slate-900">৳<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Estimated Shipping</span>
                        <span class="font-bold text-emerald-600"><?= $subtotal >= 2000 ? 'FREE' : 'Calculated at checkout' ?></span>
                    </div>
                    <div class="pt-3 border-t border-slate-200 flex justify-between text-sm font-extrabold text-slate-900">
                        <span>Estimated Total</span>
                        <span class="text-indigo-600">৳<?= number_format($subtotal, 2) ?></span>
                    </div>
                </div>

                <a href="checkout.php" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                    Proceed to Checkout <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
