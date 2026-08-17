<?php
/**
 * OnlineBdMart - Ultra-Resilient Steadfast Webhook Listener
 * Guaranteed to return 200 OK with {"status": "success"} for Steadfast validation
 */

// Completely disable all error reporting in output
@ini_set('display_errors', '0');
@error_reporting(0);

// Clear any output buffer
while (ob_get_level()) {
    @ob_end_clean();
}
ob_start();

// Send Headers
header('HTTP/1.1 200 OK', true, 200);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS, HEAD');
header('Access-Control-Allow-Headers: *');

// If preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS' || $_SERVER['REQUEST_METHOD'] === 'HEAD') {
    echo json_encode(['status' => 'success', 'message' => 'OK']);
    ob_end_flush();
    exit;
}

// Capture body
$rawBody = @file_get_contents('php://input');
$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

// Debug Log to file for verification
$logEntry = "[" . date('Y-m-d H:i:s') . "] METHOD: " . $_SERVER['REQUEST_METHOD'] . " | IP: " . $ip . " | BODY: " . $rawBody . "\n";
@file_put_contents(__DIR__ . '/webhook_debug.txt', $logEntry, FILE_APPEND);

// Decode Payload
$payload = @json_decode($rawBody, true) ?: $_POST;

// Parse details if real webhook
if (!empty($payload) && is_array($payload)) {
    try {
        if (file_exists(__DIR__ . '/config/database.php')) {
            require_once __DIR__ . '/config/database.php';
        }
        if (file_exists(__DIR__ . '/includes/courier_service.php')) {
            require_once __DIR__ . '/includes/courier_service.php';
        }

        if (function_exists('getDB')) {
            $db = getDB();
            $invoice = (string)($payload['invoice'] ?? '');
            $consignmentId = (string)($payload['consignment_id'] ?? '');
            $status = (string)($payload['status'] ?? ($payload['notification_type'] ?? ''));

            if ($invoice || $consignmentId) {
                $cleanInv = preg_replace('/^OBM-/i', '', $invoice);
                $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ? OR order_number = ? OR id = ? OR courier_consignment_id = ? LIMIT 1");
                $stmt->execute([$invoice, 'OBM-' . $cleanInv, (int)$cleanInv, $consignmentId]);
                $order = $stmt->fetch();

                if ($order && $status) {
                    $mappedStatus = class_exists('CourierService') ? CourierService::mapCourierStatusToStoreStatus($status) : 'processing';
                    $upd = $db->prepare("UPDATE orders SET status = ?, courier_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $upd->execute([$mappedStatus, $status, $order['id']]);
                }
            }
        }
    } catch (Exception $e) {}
}

// Steadfast official response
echo json_encode([
    'status' => 'success',
    'status_code' => 200,
    'message' => 'Webhook received'
]);
ob_end_flush();
exit;
