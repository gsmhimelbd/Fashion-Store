<?php
/**
 * OnlineBdMart - Unified Bangladesh Courier Webhook & Callback Listener
 * Specially optimized for Steadfast, Pathao, RedX, Paperfly
 * Endpoint: https://onlinebdmart.com/courier-webhook.php
 */

// Disable all error display in response body
error_reporting(0);
ini_set('display_errors', '0');

// Start clean output buffer
if (ob_get_level()) ob_end_clean();
ob_start();

// Set JSON headers and CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS, HEAD');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Api-Key, X-Api-Key, Accept, X-Requested-With');

// Respond 200 OK immediately to OPTIONS or HEAD
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS' || $_SERVER['REQUEST_METHOD'] === 'HEAD') {
    http_response_code(200);
    echo json_encode(['status' => 200, 'message' => 'OK']);
    ob_end_flush();
    exit;
}

// 1. Capture Raw Request Body & Client IP
$rawBody = file_get_contents('php://input');
$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
if (strpos($ip, ',') !== false) {
    $ip = trim(explode(',', $ip)[0]);
}

// 2. Parse Bearer Token if present
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
$bearerToken = '';
if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
    $bearerToken = trim($matches[1]);
}

// 3. Decode Payload
$payload = json_decode($rawBody, true);
if (!$payload && !empty($_POST)) {
    $payload = $_POST;
}

// 4. Steadfast Validation / Health Check Response
// Steadfast tests the webhook with either a GET, empty body, or test payload
if ($_SERVER['REQUEST_METHOD'] === 'GET' || empty($payload) || !empty($payload['test']) || (isset($payload['notification_type']) && $payload['notification_type'] === 'test') || (isset($payload['invoice']) && $payload['invoice'] === 'test')) {
    http_response_code(200);
    echo json_encode([
        'status' => 200,
        'success' => true,
        'message' => 'Webhook received',
        'received_ip' => $ip,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    ob_end_flush();
    exit;
}

// 5. Detect Courier & Parse Notification Payload
$courierName = 'Steadfast';
$invoiceOrOrderId = '';
$consignmentId = '';
$rawStatus = '';

// Steadfast payload detection
if (isset($payload['notification_type']) || isset($payload['consignment_id']) || isset($payload['invoice'])) {
    $courierName = 'Steadfast';
    $consignmentId = (string)($payload['consignment_id'] ?? '');
    $invoiceOrOrderId = (string)($payload['invoice'] ?? '');
    $rawStatus = (string)($payload['status'] ?? ($payload['notification_type'] ?? ''));
}
// Pathao payload detection
elseif (isset($payload['event_type']) || isset($payload['data']['consignment_id'])) {
    $courierName = 'Pathao';
    $data = $payload['data'] ?? $payload;
    $consignmentId = (string)($data['consignment_id'] ?? '');
    $invoiceOrOrderId = (string)($data['merchant_order_id'] ?? '');
    $rawStatus = (string)($payload['event_type'] ?? ($data['order_status'] ?? ''));
}
// RedX payload detection
elseif (isset($payload['tracking_id']) || isset($payload['parcel_status'])) {
    $courierName = 'RedX';
    $consignmentId = (string)($payload['tracking_id'] ?? '');
    $invoiceOrOrderId = (string)($payload['merchant_invoice_id'] ?? '');
    $rawStatus = (string)($payload['parcel_status'] ?? ($payload['status'] ?? ''));
}
// Custom payload
else {
    $courierName = (string)($payload['courier'] ?? 'Custom');
    $consignmentId = (string)($payload['consignment_id'] ?? ($payload['tracking_id'] ?? ''));
    $invoiceOrOrderId = (string)($payload['order_id'] ?? ($payload['invoice'] ?? ($payload['order_number'] ?? '')));
    $rawStatus = (string)($payload['status'] ?? ($payload['event'] ?? ''));
}

// 6. Safe Database Update
try {
    if (file_exists(__DIR__ . '/config/database.php')) {
        require_once __DIR__ . '/config/database.php';
    }
    if (file_exists(__DIR__ . '/includes/courier_service.php')) {
        require_once __DIR__ . '/includes/courier_service.php';
    }

    if (function_exists('getDB')) {
        $db = getDB();

        try {
            if (class_exists('CourierService')) {
                CourierService::ensureSchema();
            }
        } catch (Exception $e) {}

        $order = null;
        if ($invoiceOrOrderId && $invoiceOrOrderId !== 'test') {
            $cleanInv = preg_replace('/^OBM-/i', '', $invoiceOrOrderId);
            $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ? OR order_number = ? OR id = ? LIMIT 1");
            $stmt->execute([$invoiceOrOrderId, 'OBM-' . $cleanInv, (int)$cleanInv]);
            $order = $stmt->fetch();
        }

        if (!$order && $consignmentId && $consignmentId !== '0') {
            $stmt = $db->prepare("SELECT * FROM orders WHERE courier_consignment_id = ? OR courier_tracking_code = ? LIMIT 1");
            $stmt->execute([$consignmentId, $consignmentId]);
            $order = $stmt->fetch();
        }

        $orderDbId = $order ? (int)$order['id'] : null;

        try {
            $logStmt = $db->prepare("INSERT INTO courier_webhook_logs (courier_name, order_id, consignment_id, event_status, raw_payload, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
            $logStmt->execute([$courierName, $orderDbId, $consignmentId, $rawStatus, $rawBody, $ip]);
        } catch (Exception $lex) {}

        if ($order && $rawStatus) {
            $mappedStatus = class_exists('CourierService') 
                ? CourierService::mapCourierStatusToStoreStatus($rawStatus)
                : 'processing';
            
            $upd = $db->prepare("UPDATE orders SET status = ?, courier_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $upd->execute([$mappedStatus, $rawStatus, $order['id']]);

            if (file_exists(__DIR__ . '/includes/smtp_mailer.php')) {
                @require_once __DIR__ . '/includes/smtp_mailer.php';
                if ($mappedStatus === 'delivered' && ($order['status'] ?? '') !== 'delivered') {
                    if (function_exists('sendOrderDeliveredEmailNotification')) {
                        @sendOrderDeliveredEmailNotification($order['id']);
                    }
                } elseif ($mappedStatus === 'shipped' && ($order['status'] ?? '') !== 'shipped') {
                    if (function_exists('sendOrderShippedEmailNotification')) {
                        @sendOrderShippedEmailNotification($order['id']);
                    }
                }
            }
        }
    }
} catch (Exception $ex) {}

// 7. Return 200 OK
http_response_code(200);
echo json_encode([
    'status' => 200,
    'success' => true,
    'message' => 'Webhook received',
    'courier' => $courierName
]);
ob_end_flush();
exit;
