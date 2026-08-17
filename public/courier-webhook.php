<?php
/**
 * OnlineBdMart - Unified Courier Webhook Listener
 * Endpoint: https://onlinebdmart.com/courier-webhook.php
 * Handles webhook callbacks from Steadfast, Pathao, RedX, Paperfly, and Custom APIs.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/courier_service.php';
require_once __DIR__ . '/includes/smtp_mailer.php';

CourierService::ensureSchema();

$rawBody = file_get_contents('php://input');
$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
if (str_contains($ip, ',')) $ip = trim(explode(',', $ip)[0]);

$payload = json_decode($rawBody, true);
if (!$payload && !empty($_POST)) {
    $payload = $_POST;
}

if (!$payload) {
    echo json_encode([
        'status' => 'active',
        'message' => 'OnlineBdMart Courier Webhook Endpoint is Live and Listening.',
        'supported_couriers' => ['Steadfast', 'Pathao', 'RedX', 'Paperfly', 'Custom API']
    ]);
    exit;
}

$courierName = 'Unknown';
$invoiceOrOrderId = '';
$consignmentId = '';
$rawStatus = '';

// 1. Detect Steadfast Webhook Payload
if (isset($payload['notification_type']) || isset($payload['consignment_id'])) {
    $courierName = 'Steadfast';
    $consignmentId = (string)($payload['consignment_id'] ?? '');
    $invoiceOrOrderId = (string)($payload['invoice'] ?? '');
    $rawStatus = (string)($payload['status'] ?? ($payload['notification_type'] ?? ''));
}
// 2. Detect Pathao Webhook Payload
elseif (isset($payload['event_type']) || (isset($payload['data']['consignment_id']))) {
    $courierName = 'Pathao';
    $data = $payload['data'] ?? $payload;
    $consignmentId = (string)($data['consignment_id'] ?? '');
    $invoiceOrOrderId = (string)($data['merchant_order_id'] ?? '');
    $rawStatus = (string)($payload['event_type'] ?? ($data['order_status'] ?? ''));
}
// 3. Detect RedX Webhook Payload
elseif (isset($payload['tracking_id']) || isset($payload['parcel_status'])) {
    $courierName = 'RedX';
    $consignmentId = (string)($payload['tracking_id'] ?? '');
    $invoiceOrOrderId = (string)($payload['merchant_invoice_id'] ?? '');
    $rawStatus = (string)($payload['parcel_status'] ?? ($payload['status'] ?? ''));
}
// 4. Custom REST Webhook / Generic Payload
else {
    $courierName = (string)($payload['courier'] ?? 'Custom');
    $consignmentId = (string)($payload['consignment_id'] ?? ($payload['tracking_id'] ?? ''));
    $invoiceOrOrderId = (string)($payload['order_id'] ?? ($payload['invoice'] ?? ($payload['order_number'] ?? '')));
    $rawStatus = (string)($payload['status'] ?? ($payload['event'] ?? ''));
}

try {
    $db = getDB();

    // Find matching order in database by invoice, order_number, id, or consignment_id
    $order = null;
    if ($invoiceOrOrderId) {
        $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ? OR order_number = ? OR id = ? LIMIT 1");
        $stmt->execute([$invoiceOrOrderId, 'OBM-' . $invoiceOrOrderId, (int)$invoiceOrOrderId]);
        $order = $stmt->fetch();
    }

    if (!$order && $consignmentId) {
        $stmt = $db->prepare("SELECT * FROM orders WHERE courier_consignment_id = ? OR courier_tracking_code = ? LIMIT 1");
        $stmt->execute([$consignmentId, $consignmentId]);
        $order = $stmt->fetch();
    }

    $orderDbId = $order ? (int)$order['id'] : null;

    // Log webhook callback for audit trail
    $logStmt = $db->prepare("INSERT INTO courier_webhook_logs (courier_name, order_id, consignment_id, event_status, raw_payload, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
    $logStmt->execute([$courierName, $orderDbId, $consignmentId, $rawStatus, $rawBody, $ip]);

    if ($order && $rawStatus) {
        $mappedStatus = CourierService::mapCourierStatusToStoreStatus($rawStatus);
        
        // Update order status and courier status in database
        $upd = $db->prepare("UPDATE orders SET status = ?, courier_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $upd->execute([$mappedStatus, $rawStatus, $order['id']]);

        // If newly delivered, send automated delivery confirmation email to customer
        if ($mappedStatus === 'delivered' && $order['status'] !== 'delivered') {
            @sendOrderDeliveredEmailNotification($order['id']);
        } elseif ($mappedStatus === 'shipped' && $order['status'] !== 'shipped') {
            @sendOrderShippedEmailNotification($order['id']);
        }

        echo json_encode([
            'success' => true,
            'message' => "Order #{$order['id']} status updated to {$mappedStatus} ({$rawStatus}) via {$courierName} Webhook.",
            'order_id' => $order['id'],
            'status' => $mappedStatus
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Webhook received and logged.',
        'courier' => $courierName
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
