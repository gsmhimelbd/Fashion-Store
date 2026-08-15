<?php
/**
 * OnlineBdMart - Universal PHP / Laravel Web Dispatcher
 * Compatible with cPanel, DirectAdmin, Apache, LiteSpeed, Nginx & Laravel
 */

require_once __DIR__ . '/../config/database.php';

// If Laravel Composer vendor exists and is valid, use Laravel Kernel
if (file_exists(__DIR__ . '/../vendor/autoload.php') && file_exists(__DIR__ . '/../bootstrap/app.php')) {
    try {
        require __DIR__ . '/../vendor/autoload.php';
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        if (is_object($app) && method_exists($app, 'handleRequest')) {
            $app->handleRequest(\Illuminate\Http\Request::capture());
            exit;
        }
    } catch (\Throwable $e) {
        // Fallback to standalone dispatcher if Laravel throws exception
    }
}

// -------------------------------------------------------------
// Standalone High-Performance Dispatcher for cPanel
// -------------------------------------------------------------
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 1. Direct ZIP Download Handlers
if ($uri === '/download/cpanel' || $uri === '/OnlineBdMart-cPanel-Ready.zip' || $uri === '/OnlineBdMart-Laravel.zip') {
    $zipFile = __DIR__ . '/../OnlineBdMart-cPanel-Ready.zip';
    if (file_exists($zipFile)) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="OnlineBdMart-cPanel-Ready.zip"');
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);
        exit;
    }
}

if ($uri === '/download' || $uri === '/download/') {
    if (file_exists(__DIR__ . '/download.html')) {
        include __DIR__ . '/download.html';
        exit;
    }
}

// 2. Realtime Search API
if ($uri === '/search-suggestions') {
    header('Content-Type: application/json');
    $q = trim($_GET['q'] ?? '');
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, name, slug, price, sale_price, image_path, category_id FROM products WHERE is_active = 1 AND (name LIKE ? OR description LIKE ?) LIMIT 6");
        $stmt->execute(["%{$q}%", "%{$q}%"]);
        $rows = $stmt->fetchAll();
        $results = array_map(function($p) {
            $price = ($p['sale_price'] && $p['sale_price'] > 0 && $p['sale_price'] < $p['price']) ? $p['sale_price'] : $p['price'];
            return [
                'id' => $p['id'],
                'name' => $p['name'],
                'slug' => $p['slug'],
                'price' => $price,
                'formatted_price' => '৳' . number_format($price, 2),
                'image' => '/' . ltrim($p['image_path'], '/'),
                'url' => '/product/' . $p['slug'],
            ];
        }, $rows);
        echo json_encode($results);
    } catch (Exception $e) {
        echo json_encode([]);
    }
    exit;
}

// 3. Cart Add API
if ($uri === '/cart/add' && $method === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $pid = intval($input['product_id'] ?? 0);
    $isWholesale = !empty($input['is_wholesale']);
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$pid]);
    $p = $stmt->fetch();

    if (!$p) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }

    $minQty = $isWholesale ? ($p['wholesale_min_qty'] ?: 5) : 1;
    $qty = max($minQty, intval($input['quantity'] ?? $minQty));
    $unitPrice = ($isWholesale || $qty >= ($p['wholesale_min_qty'] ?: 5))
        ? ($p['wholesale_price'] ?: ($p['price'] * 0.75))
        : (($p['sale_price'] && $p['sale_price'] < $p['price']) ? $p['sale_price'] : $p['price']);

    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    if (isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid]['quantity'] += $qty;
    } else {
        $_SESSION['cart'][$pid] = [
            'id' => $p['id'],
            'name' => $p['name'],
            'slug' => $p['slug'],
            'price' => floatval($unitPrice),
            'image' => $p['image_path'],
            'quantity' => $qty,
            'is_wholesale' => $isWholesale,
        ];
    }

    echo json_encode([
        'success' => true,
        'message' => $isWholesale ? "Added {$qty} pcs to cart at Wholesale Factory Rate!" : 'Added to cart successfully!',
        'count' => getCartCount(),
        'subtotal' => getCartSubtotal(),
        'items' => getCartItems(),
    ]);
    exit;
}

// 4. Cart Update API
if ($uri === '/cart/update' && $method === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $pid = intval($input['product_id'] ?? 0);
    $qty = intval($input['quantity'] ?? 0);

    if (isset($_SESSION['cart'][$pid])) {
        if ($qty > 0) $_SESSION['cart'][$pid]['quantity'] = $qty;
        else unset($_SESSION['cart'][$pid]);
    }

    echo json_encode(['success' => true, 'count' => getCartCount(), 'subtotal' => getCartSubtotal(), 'items' => getCartItems()]);
    exit;
}

// 5. Cart Remove API
if ($uri === '/cart/remove' && $method === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $pid = intval($input['product_id'] ?? 0);
    unset($_SESSION['cart'][$pid]);

    echo json_encode(['success' => true, 'count' => getCartCount(), 'subtotal' => getCartSubtotal(), 'items' => getCartItems()]);
    exit;
}

// 6. Checkout Process API
if ($uri === '/checkout' && $method === 'POST') {
    header('Content-Type: application/json');
    $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $items = getCartItems();
    if (empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit;
    }

    $subtotal = getCartSubtotal();
    $d = strtolower($body['district'] ?? 'tangail');
    $deliveryCharge = 150.0;
    if ($d === 'tangail') $deliveryCharge = 50.0;
    elseif (in_array($d, ['dhaka', 'gazipur', 'narayanganj'])) $deliveryCharge = 80.0;
    elseif (in_array($d, ['chittagong', 'sylhet', 'comilla'])) $deliveryCharge = 130.0;

    $grandTotal = $subtotal + $deliveryCharge;

    $db = getDB();
    $stmt = $db->prepare("INSERT INTO orders (customer_name, phone, whatsapp, district, upazila, address, notes, payment_method, payment_number, transaction_id, subtotal, delivery_charge, grand_total, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([
        $body['customer_name'] ?? 'Customer',
        $body['phone'] ?? '',
        $body['phone'] ?? '',
        $body['district'] ?? 'Tangail',
        $body['upazila'] ?? '',
        $body['address'] ?? '',
        $body['email'] ?? '',
        $body['payment_method'] ?? 'cod',
        $body['payment_number'] ?? null,
        $body['transaction_id'] ?? null,
        $subtotal,
        $deliveryCharge,
        $grandTotal,
    ]);

    $orderId = $db->lastInsertId();
    $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($items as $item) {
        $itemStmt->execute([$orderId, $item['id'], $item['name'], $item['image'], $item['price'], $item['quantity']]);
    }

    $_SESSION['cart'] = [];
    echo json_encode(['success' => true, 'redirect' => "/order-success/{$orderId}", 'order_id' => $orderId]);
    exit;
}

// 7. Telegram Webhook API
if ($uri === '/api/telegram/webhook' && $method === 'POST') {
    header('Content-Type: application/json');
    $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    if (isset($body['callback_query']['data'])) {
        $data = $body['callback_query']['data'];
        if (str_starts_with($data, 'set_status:')) {
            $parts = explode(':', $data);
            $newStatus = $parts[1] ?? 'pending';
            $orderId = intval($parts[2] ?? 0);
            if ($orderId > 0) {
                $db = getDB();
                $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $orderId]);
                echo json_encode(['success' => true, 'message' => "Order #{$orderId} updated to {$newStatus}!"]);
                exit;
            }
        }
    }
    echo json_encode(['success' => true]);
    exit;
}

// 8. Admin Auth Routes
if ($uri === '/admin' || $uri === '/admin/' || $uri === '/admin/login') {
    header('Location: /admin-panel/login');
    exit;
}

if ($uri === '/admin-panel/login' && $method === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    if ($user === 'admin' && ($pass === 'password' || $pass === 'admin')) {
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_username'] = 'admin';
        header('Location: /admin-panel/dashboard');
        exit;
    }
}

if ($uri === '/admin-panel/logout') {
    unset($_SESSION['admin_id']);
    header('Location: /admin-panel/login');
    exit;
}

// 9. Load Global Data for Views
$db = getDB();
$s = getAllSettings();
$storeName = $s['store_name'] ?? 'OnlineBdMart';
$whatsapp = $s['whatsapp_number'] ?? '01775153740';
$phone = $s['contact_phone'] ?? '01775153740';

try {
    $categories = $db->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as products_count FROM categories c")->fetchAll();
    $banners = $db->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY display_order ASC")->fetchAll();
    $featured = $db->query("SELECT * FROM products WHERE is_active = 1 AND is_featured = 1 ORDER BY id DESC LIMIT 8")->fetchAll();
    $allProducts = $db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1")->fetchAll();
} catch (Exception $e) {
    $categories = [];
    $banners = [];
    $featured = [];
    $allProducts = [];
}

// Render Modern HTML View
include __DIR__ . '/../resources/views/layouts/app.blade.php';
