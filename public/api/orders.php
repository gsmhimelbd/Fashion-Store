<?php
/**
 * OnlineBdMart - Custom Orders REST API Endpoint
 * Endpoint: https://onlinebdmart.com/api/orders.php
 * Authentication: Header "X-Api-Key: obm_live_..." OR "Authorization: Bearer obm_live_..."
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/courier_service.php';
require_once __DIR__ . '/../includes/smtp_mailer.php';

// 1. Authenticate Request API Key
$settings = getAllSettings();
$masterKey = trim($settings['store_api_key'] ?? '');

$providedKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if (empty($providedKey) && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
    if (preg_match('/Bearer\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $m)) {
        $providedKey = trim($m[1]);
    }
}
if (empty($providedKey)) {
    $providedKey = $_GET['api_key'] ?? ($_POST['api_key'] ?? '');
}

if (empty($masterKey) || empty($providedKey) || !hash_equals($masterKey, $providedKey)) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized: Invalid or missing API Key. Provide valid X-Api-Key header.'
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

try {
    // GET: List or Retrieve Orders
    if ($method === 'GET') {
        $id = (int)($_GET['id'] ?? 0);
        $orderNo = trim($_GET['order_number'] ?? '');

        if ($id > 0 || $orderNo) {
            $stmt = $id > 0 
                ? $db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1")
                : $db->prepare("SELECT * FROM orders WHERE order_number = ? LIMIT 1");
            $stmt->execute([$id ?: $orderNo]);
            $order = $stmt->fetch();

            if (!$order) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Order not found']);
                exit;
            }

            $items = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $items->execute([$order['id']]);
            $order['items'] = $items->fetchAll();

            echo json_encode(['success' => true, 'data' => $order]);
            exit;
        }

        // List latest 50 orders
        $limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));
        $orders = $db->query("SELECT * FROM orders ORDER BY id DESC LIMIT {$limit}")->fetchAll();
        echo json_encode(['success' => true, 'count' => count($orders), 'data' => $orders]);
        exit;
    }

    // POST: Create New Order
    if ($method === 'POST') {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $name = trim($data['customer_name'] ?? '');
        $phone = trim($data['customer_phone'] ?? ($data['phone'] ?? ''));
        $address = trim($data['delivery_address'] ?? ($data['address'] ?? ''));
        $district = trim($data['district'] ?? ($data['district_name'] ?? 'Dhaka'));
        $subtotal = (float)($data['subtotal'] ?? 0);
        $deliveryFee = (float)($data['delivery_cost'] ?? 120);
        $total = (float)($data['total_amount'] ?? ($subtotal + $deliveryFee));
        $paymentMethod = strtolower(trim($data['payment_method'] ?? 'cod'));

        if (empty($name) || empty($phone) || empty($address)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Missing required fields: customer_name, customer_phone, delivery_address']);
            exit;
        }

        $orderNumber = 'OBM-' . strtoupper(bin2hex(random_bytes(4)));

        $stmt = $db->prepare("INSERT INTO orders (order_number, customer_name, customer_phone, phone, delivery_address, address, district_name, district, subtotal, delivery_cost, total_amount, grand_total, payment_method, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
        $stmt->execute([$orderNumber, $name, $phone, $phone, $address, $address, $district, $district, $subtotal, $deliveryFee, $total, $total, $paymentMethod]);
        $orderId = $db->lastInsertId();

        // Insert items if provided
        $items = $data['items'] ?? [];
        if (is_array($items)) {
            foreach ($items as $it) {
                $pName = $it['product_name'] ?? ($it['name'] ?? 'Product');
                $price = (float)($it['price'] ?? 0);
                $qty = (int)($it['quantity'] ?? 1);
                $color = $it['color'] ?? null;
                $size = $it['size'] ?? null;
                $iStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total_price, color, size) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $iStmt->execute([$orderId, (int)($it['product_id'] ?? 0), $pName, $price, $qty, $price * $qty, $color, $size]);
            }
        }

        // Auto-send to courier if requested
        $courierRes = null;
        if (!empty($data['auto_dispatch_courier'])) {
            $courierRes = CourierService::dispatchOrderToCourier($orderId, $data['courier_name'] ?? null);
        }

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Order created successfully',
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'courier_dispatch' => $courierRes
        ]);
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
