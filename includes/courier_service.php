<?php
/**
 * OnlineBdMart - Unified Bangladesh Courier API Service Engine
 * Supports Steadfast, Pathao, RedX, Paperfly & eCourier
 * Provides order consignment creation, tracking, status sync, and secure API Key auth.
 */

if (!defined('ABSPATH')) {
    // allow direct include
}

require_once __DIR__ . '/../config/database.php';

class CourierService {

    /**
     * Auto-heal database schema for courier integration
     */
    public static function ensureSchema() {
        static $done = false;
        if ($done) return;
        $done = true;

        try {
            $db = getDB();
            $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

            if ($driver === 'sqlite') {
                $db->exec("
                    CREATE TABLE IF NOT EXISTS courier_webhook_logs (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        courier_name TEXT NOT NULL,
                        order_id INTEGER NULL,
                        consignment_id TEXT NULL,
                        event_status TEXT NULL,
                        raw_payload TEXT NULL,
                        ip_address TEXT NULL,
                        processed INTEGER DEFAULT 1,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    );
                ");
            } else {
                $db->exec("
                    CREATE TABLE IF NOT EXISTS `courier_webhook_logs` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `courier_name` VARCHAR(50) NOT NULL,
                        `order_id` INT NULL,
                        `consignment_id` VARCHAR(100) NULL,
                        `event_status` VARCHAR(50) NULL,
                        `raw_payload` LONGTEXT NULL,
                        `ip_address` VARCHAR(100) NULL,
                        `processed` TINYINT(1) DEFAULT 1,
                        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ");
            }

            // Add columns to orders table if missing
            $cols = [
                'courier_name' => $driver === 'sqlite' ? 'TEXT' : 'VARCHAR(50) NULL',
                'courier_consignment_id' => $driver === 'sqlite' ? 'TEXT' : 'VARCHAR(100) NULL',
                'courier_tracking_code' => $driver === 'sqlite' ? 'TEXT' : 'VARCHAR(100) NULL',
                'courier_status' => $driver === 'sqlite' ? 'TEXT' : 'VARCHAR(50) NULL',
                'courier_invoice_id' => $driver === 'sqlite' ? 'TEXT' : 'VARCHAR(100) NULL',
                'courier_response' => $driver === 'sqlite' ? 'TEXT' : 'TEXT NULL',
            ];

            foreach ($cols as $col => $colDef) {
                try {
                    if ($driver === 'sqlite') {
                        $c = $db->query("PRAGMA table_info(orders)")->fetchAll(PDO::FETCH_COLUMN, 1);
                        if (!in_array($col, $c)) {
                            $db->exec("ALTER TABLE orders ADD COLUMN {$col} {$colDef}");
                        }
                    } else {
                        $stmt = $db->query("SHOW COLUMNS FROM `orders` LIKE '{$col}'");
                        if ($stmt->rowCount() === 0) {
                            $db->exec("ALTER TABLE `orders` ADD COLUMN `{$col}` {$colDef}");
                        }
                    }
                } catch (Exception $ex) {}
            }
        } catch (Exception $e) {}
    }

    /**
     * Generate or rotate secure 64-char API Secret Key for OnlineBdMart
     */
    public static function generateApiKey() {
        return 'obm_live_' . bin2hex(random_bytes(24));
    }

    /**
     * Clean phone to Bangladeshi 11-digit mobile format
     */
    public static function cleanPhone($phone) {
        $digits = preg_replace('/[^0-9]/', '', (string)$phone);
        if (substr($digits, 0, 3) === '880' && strlen($digits) === 13) {
            return substr($digits, 2);
        }
        if (substr($digits, 0, 2) === '01' && strlen($digits) === 11) {
            return $digits;
        }
        if (substr($digits, 0, 1) === '1' && strlen($digits) === 10) {
            return '0' . $digits;
        }
        return $digits;
    }

    /**
     * Calculate COD cash amount to collect by courier
     */
    public static function calculateCodAmount($order) {
        $isCod = strtolower($order['payment_method'] ?? 'cod') === 'cod';
        if (!$isCod) {
            return 0.00; // Prepaid orders
        }

        $grandTotal = (float)($order['total_amount'] ?: ($order['grand_total'] ?? 0));
        
        // If advance delivery fee was paid for COD (TrxID exists), COD balance is subtotal only
        $settings = getAllSettings();
        $advanceCod = ($settings['payment_cod_advance_delivery_charge'] ?? '1') === '1';
        $hasTrx = !empty($order['transaction_id']);

        if ($advanceCod && $hasTrx) {
            $subtotal = (float)($order['subtotal'] ?? 0);
            $discount = (float)($order['discount_amount'] ?? 0);
            return max(0, $subtotal - $discount);
        }

        return $grandTotal;
    }

    /**
     * Build item summary note (e.g. "2x Watch [Black], 1x Bag")
     */
    public static function buildItemNote($items) {
        $parts = [];
        foreach ($items as $it) {
            $var = [];
            if (!empty($it['color'])) $var[] = $it['color'];
            if (!empty($it['size'])) $var[] = $it['size'];
            $varStr = !empty($var) ? ' (' . implode(',', $var) . ')' : '';
            $parts[] = "{$it['quantity']}x {$it['product_name']}{$varStr}";
        }
        return implode('; ', $parts);
    }

    // =========================================================================
    // 1. STEADFAST COURIER INTEGRATION (https://steadfast.com.bd / packzy.com)
    // =========================================================================
    public static function sendToSteadfast($order, $items) {
        $settings = getAllSettings();
        $apiKey = trim($settings['steadfast_api_key'] ?? '');
        $secretKey = trim($settings['steadfast_secret_key'] ?? '');

        if (empty($apiKey) || empty($secretKey)) {
            return [
                'success' => false,
                'message' => 'Steadfast API Key or Secret Key not configured. Please go to Admin Panel -> Courier & API Hub and save your credentials.'
            ];
        }

        $orderNo = (string)($order['order_number'] ?: ('OBM-' . $order['id']));
        $codAmount = (int)round(self::calculateCodAmount($order));
        $phone = self::cleanPhone($order['customer_phone'] ?: $order['phone']);
        $name = trim((string)($order['customer_name'] ?: 'Customer'));
        $address = trim((string)($order['delivery_address'] ?: ($order['address'] ?: 'Dhaka, Bangladesh')));
        if (!empty($order['district_name']) && stripos($address, $order['district_name']) === false) {
            $address .= ', ' . $order['district_name'];
        }
        if (strlen($address) < 10) {
            $address .= ', Bangladesh';
        }
        $note = self::buildItemNote($items);

        $payload = [
            'invoice' => $orderNo,
            'recipient_name' => $name,
            'recipient_phone' => $phone,
            'recipient_address' => $address,
            'cod_amount' => (string)$codAmount,
            'note' => $note ?: 'OnlineBdMart Order'
        ];

        if (!empty($order['customer_email'])) {
            $payload['recipient_email'] = trim($order['customer_email']);
        }

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'api-key: ' . $apiKey,
            'secret-key: ' . $secretKey,
            'Api-Key: ' . $apiKey,
            'Secret-Key: ' . $secretKey
        ];

        // Endpoints to try (Primary & Cloud cluster)
        $endpoints = [
            'https://portal.steadfast.com.bd/api/v1/create_order',
            'https://portal.packzy.com/api/v1/create_order'
        ];

        $response = false;
        $httpCode = 0;
        $err = '';

        foreach ($endpoints as $url) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) OnlineBdMart Courier Dispatcher',
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200 || $httpCode === 201) {
                break;
            }
        }

        $res = json_decode($response, true);

        // Success condition
        if (($httpCode === 200 || $httpCode === 201) && (!empty($res['consignment']) || !empty($res['data']))) {
            $consignment = $res['consignment'] ?? ($res['data'] ?? []);
            $cid = (string)($consignment['consignment_id'] ?? ($consignment['id'] ?? ''));
            $trk = (string)($consignment['tracking_code'] ?? $cid);
            $status = (string)($consignment['status'] ?? 'in_review');

            self::updateOrderCourierData($order['id'], 'Steadfast', $cid, $trk, $status, $res);

            return [
                'success' => true,
                'courier' => 'Steadfast',
                'consignment_id' => $cid,
                'tracking_code' => $trk,
                'status' => $status,
                'tracking_url' => "https://steadfast.com.bd/t/{$cid}",
                'message' => "✓ Order successfully booked with Steadfast! Consignment ID: {$cid} | Tracking: {$trk}"
            ];
        }

        // Handle Duplicate Invoice: Retry once with unique invoice suffix
        if (!empty($res['errors']['invoice']) || (isset($res['message']) && stripos($res['message'], 'INVOICE_ALREADY_EXISTS') !== false)) {
            $uniqueOrderNo = $orderNo . '-' . substr(time(), -4);
            $payload['invoice'] = $uniqueOrderNo;

            $ch = curl_init('https://portal.steadfast.com.bd/api/v1/create_order');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $res = json_decode($response, true);

            if (($httpCode === 200 || $httpCode === 201) && !empty($res['consignment'])) {
                $consignment = $res['consignment'];
                $cid = (string)($consignment['consignment_id'] ?? ($consignment['id'] ?? ''));
                $trk = (string)($consignment['tracking_code'] ?? $cid);
                $status = (string)($consignment['status'] ?? 'in_review');

                self::updateOrderCourierData($order['id'], 'Steadfast', $cid, $trk, $status, $res);

                return [
                    'success' => true,
                    'courier' => 'Steadfast',
                    'consignment_id' => $cid,
                    'tracking_code' => $trk,
                    'status' => $status,
                    'tracking_url' => "https://steadfast.com.bd/t/{$cid}",
                    'message' => "✓ Order booked with Steadfast! Consignment ID: {$cid} (Invoice: {$uniqueOrderNo})"
                ];
            }
        }

        // Build detailed human readable error message
        $errMsg = '';
        if (!empty($res['errors']) && is_array($res['errors'])) {
            $errParts = [];
            foreach ($res['errors'] as $field => $messages) {
                $errParts[] = ucfirst($field) . ': ' . (is_array($messages) ? implode(', ', $messages) : $messages);
            }
            $errMsg = implode(' | ', $errParts);
        } elseif (!empty($res['message'])) {
            $errMsg = $res['message'];
        } elseif ($httpCode === 401) {
            $errMsg = 'Invalid Steadfast API Key or Secret Key (401 Unauthorized). Please check credentials in Admin Panel.';
        } elseif ($httpCode === 404) {
            $errMsg = 'Steadfast API Endpoint not found (404).';
        } elseif (!empty($err)) {
            $errMsg = 'cURL Connection Error: ' . $err;
        } else {
            $errMsg = 'Steadfast Booking Failed (HTTP ' . $httpCode . '). Response: ' . substr((string)$response, 0, 200);
        }

        return ['success' => false, 'error' => $errMsg, 'raw' => $response];
    }

    public static function checkSteadfastBalance() {
        $settings = getAllSettings();
        $apiKey = trim($settings['steadfast_api_key'] ?? '');
        $secretKey = trim($settings['steadfast_secret_key'] ?? '');
        if (empty($apiKey) || empty($secretKey)) return null;

        $url = 'https://portal.steadfast.com.bd/api/v1/get_balance';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Api-Key: ' . $apiKey, 'Secret-Key: ' . $secretKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        $d = json_decode($res, true);
        return $d['current_balance'] ?? null;
    }

    // =========================================================================
    // 2. PATHAO COURIER INTEGRATION (https://merchant.pathao.com)
    // =========================================================================
    public static function getPathaoToken() {
        $settings = getAllSettings();
        $clientId = trim($settings['pathao_client_id'] ?? '');
        $clientSecret = trim($settings['pathao_client_secret'] ?? '');
        $username = trim($settings['pathao_username'] ?? '');
        $password = trim($settings['pathao_password'] ?? '');

        if (empty($clientId) || empty($clientSecret) || empty($username) || empty($password)) {
            return null;
        }

        // Cache token in settings for 5 days
        $cachedToken = $settings['pathao_cached_token'] ?? '';
        $tokenExpiry = (int)($settings['pathao_token_expiry'] ?? 0);
        if ($cachedToken && time() < $tokenExpiry) {
            return $cachedToken;
        }

        $payload = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'username' => $username,
            'password' => $password,
            'grant_type' => 'password'
        ];

        $baseUrl = ($settings['pathao_sandbox'] ?? '0') === '1' ? 'https://courier-api-sandbox.pathao.com' : 'https://api-hermes.pathao.com';
        $ch = curl_init("{$baseUrl}/aladdin/api/v1/issue-token");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $res = curl_exec($ch);
        curl_close($ch);

        $d = json_decode($res, true);
        if (!empty($d['access_token'])) {
            $token = $d['access_token'];
            $expiresIn = (int)($d['expires_in'] ?? 86400 * 5);
            try {
                saveSetting('pathao_cached_token', $token);
                saveSetting('pathao_token_expiry', (string)(time() + $expiresIn - 3600));
            } catch (Exception $e) {}
            return $token;
        }
        return null;
    }

    public static function sendToPathao($order, $items) {
        $settings = getAllSettings();
        $clientId = trim($settings['pathao_client_id'] ?? '');
        $clientSecret = trim($settings['pathao_client_secret'] ?? '');

        if (empty($clientId) || empty($clientSecret)) {
            return [
                'success' => false,
                'message' => 'Pathao API credentials not configured. Please enter Client ID, Secret, Username and Password in Courier & API Hub.'
            ];
        }

        $token = self::getPathaoToken();
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Pathao authentication failed. Please verify your Pathao Client ID, Secret, Email and Password in Courier & API Hub.'
            ];
        }

        $storeId = (int)($settings['pathao_store_id'] ?? 0);
        $orderNo = $order['order_number'] ?: ('OBM-' . $order['id']);
        $codAmount = self::calculateCodAmount($order);
        $phone = self::cleanPhone($order['customer_phone'] ?: $order['phone']);
        $name = $order['customer_name'] ?: 'Customer';
        $address = $order['delivery_address'] ?: ($order['address'] ?: 'Dhaka');
        $itemNote = self::buildItemNote($items);
        $orderWeight = max(0.5, (float)($order['total_weight'] ?? 1.0));

        $payload = [
            'store_id' => $storeId,
            'merchant_order_id' => $orderNo,
            'recipient_name' => $name,
            'recipient_phone' => $phone,
            'recipient_address' => $address,
            'recipient_city' => 1,
            'recipient_zone' => 1,
            'delivery_type' => 48,
            'item_type' => 2,
            'special_instruction' => $itemNote ?: 'OnlineBdMart Parcel',
            'item_quantity' => count($items) ?: 1,
            'item_weight' => $orderWeight,
            'amount_to_collect' => (int)round($codAmount),
            'item_description' => $itemNote ?: 'E-commerce goods'
        ];

        $baseUrl = ($settings['pathao_sandbox'] ?? '0') === '1' ? 'https://courier-api-sandbox.pathao.com' : 'https://api-hermes.pathao.com';
        $ch = curl_init("{$baseUrl}/aladdin/api/v1/orders");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $token
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $res = json_decode($response, true);
        if ($httpCode === 200 && !empty($res['data'])) {
            $data = $res['data'];
            $cid = (string)($data['consignment_id'] ?? '');
            $trk = (string)($data['tracking_code'] ?? $cid);

            self::updateOrderCourierData($order['id'], 'Pathao', $cid, $trk, 'in_review', $res);

            return [
                'success' => true,
                'courier' => 'Pathao',
                'consignment_id' => $cid,
                'tracking_code' => $trk,
                'tracking_url' => "https://merchant.pathao.com/tracking?consignment_id={$cid}",
                'message' => "✓ Order successfully sent to Pathao Courier! Consignment ID: {$cid}"
            ];
        }

        $errMsg = $res['message'] ?? 'Pathao booking failed (HTTP ' . $httpCode . ').';
        if (!empty($res['errors']) && is_array($res['errors'])) {
            $errMsg .= ' ' . json_encode($res['errors']);
        }

        return ['success' => false, 'error' => $errMsg, 'raw' => $response];
    }

    // =========================================================================
    // 3. REDX COURIER INTEGRATION (https://redx.com.bd)
    // =========================================================================
    public static function sendToRedx($order, $items) {
        $settings = getAllSettings();
        $token = trim($settings['redx_access_token'] ?? '');
        if (empty($token)) {
            return ['success' => false, 'message' => 'RedX Access Token not configured in Admin Settings.'];
        }

        $orderNo = $order['order_number'] ?: ('OBM-' . $order['id']);
        $codAmount = self::calculateCodAmount($order);
        $phone = self::cleanPhone($order['customer_phone'] ?: $order['phone']);
        $name = $order['customer_name'] ?: 'Customer';
        $address = $order['delivery_address'] ?: ($order['address'] ?: 'Dhaka');
        $itemNote = self::buildItemNote($items);

        $payload = [
            'customer_name' => $name,
            'customer_phone' => $phone,
            'delivery_area' => $order['district_name'] ?: 'Dhaka',
            'customer_address' => $address,
            'merchant_invoice_id' => $orderNo,
            'cash_collection_amount' => (int)round($codAmount),
            'parcel_weight' => 500, // in grams
            'instruction' => $itemNote ?: 'OnlineBdMart Order'
        ];

        $baseUrl = ($settings['redx_sandbox'] ?? '0') === '1' ? 'https://sandbox.redx.com.bd/v1_0_0' : 'https://openapi.redx.com.bd/v1_0_0';
        $ch = curl_init("{$baseUrl}/parcels");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'API-ACCESS-TOKEN: Bearer ' . $token
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $res = json_decode($response, true);
        if (($httpCode === 200 || $httpCode === 201) && !empty($res['tracking_id'])) {
            $trk = (string)$res['tracking_id'];
            self::updateOrderCourierData($order['id'], 'RedX', $trk, $trk, 'pending', $res);

            return [
                'success' => true,
                'courier' => 'RedX',
                'consignment_id' => $trk,
                'tracking_code' => $trk,
                'tracking_url' => "https://redx.com.bd/track-parcel/?trackingId={$trk}",
                'message' => "✓ Order successfully sent to RedX Courier! Tracking ID: {$trk}"
            ];
        }

        return ['success' => false, 'error' => $res['message'] ?? 'RedX booking failed', 'raw' => $response];
    }

    // =========================================================================
    // 4. PAPERFLY COURIER INTEGRATION (https://paperfly.com.bd)
    // =========================================================================
    public static function sendToPaperfly($order, $items) {
        $settings = getAllSettings();
        $user = trim($settings['paperfly_username'] ?? '');
        $pass = trim($settings['paperfly_password'] ?? '');
        $key = trim($settings['paperfly_key'] ?? '');

        if (empty($user) || empty($pass) || empty($key)) {
            return ['success' => false, 'message' => 'Paperfly credentials not configured.'];
        }

        $orderNo = $order['order_number'] ?: ('OBM-' . $order['id']);
        $codAmount = self::calculateCodAmount($order);
        $phone = self::cleanPhone($order['customer_phone'] ?: $order['phone']);
        $name = $order['customer_name'] ?: 'Customer';
        $address = $order['delivery_address'] ?: ($order['address'] ?: 'Dhaka');
        $district = $order['district_name'] ?: 'Dhaka';

        $payload = [
            'merOrderRef' => $orderNo,
            'packageHighValue' => 1,
            'deliveryOption' => 'regular',
            'custName' => $name,
            'custAddress' => $address,
            'custPhone' => $phone,
            'max_weight' => '1',
            'price' => (string)round($codAmount),
            'destThana' => $order['upazila'] ?: $district,
            'destDistrict' => $district,
            'itemDescription' => self::buildItemNote($items)
        ];

        $url = 'https://api.paperfly.com.bd/OrderPlacement';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'paperflykey: ' . $key,
                'Authorization: Basic ' . base64_encode("{$user}:{$pass}")
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $res = json_decode($response, true);
        if ($httpCode === 200 && !empty($res['success']['tracking_number'])) {
            $trk = (string)$res['success']['tracking_number'];
            self::updateOrderCourierData($order['id'], 'Paperfly', $trk, $trk, 'pending', $res);
            return [
                'success' => true,
                'courier' => 'Paperfly',
                'consignment_id' => $trk,
                'tracking_code' => $trk,
                'tracking_url' => "https://paperfly.com.bd/tracking?ref={$trk}",
                'message' => "✓ Order successfully sent to Paperfly! Tracking: {$trk}"
            ];
        }

        return ['success' => false, 'error' => $res['error'] ?? 'Paperfly booking failed', 'raw' => $response];
    }

    /**
     * Master dispatcher based on default courier setting or explicit selection
     */
    public static function dispatchOrderToCourier($orderId, $preferredCourier = null) {
        self::ensureSchema();

        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
            if (!$order) return ['success' => false, 'message' => 'Order not found.'];

            $itemsStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $itemsStmt->execute([$orderId]);
            $items = $itemsStmt->fetchAll();

            $settings = getAllSettings();
            $courier = strtolower($preferredCourier ?: ($settings['default_courier'] ?? 'steadfast'));

            switch ($courier) {
                case 'steadfast':
                    return self::sendToSteadfast($order, $items);
                case 'pathao':
                    return self::sendToPathao($order, $items);
                case 'redx':
                    return self::sendToRedx($order, $items);
                case 'paperfly':
                    return self::sendToPaperfly($order, $items);
                default:
                    return self::sendToSteadfast($order, $items);
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update order record in database with courier tracking ID
     */
    public static function updateOrderCourierData($orderId, $courierName, $consignmentId, $trackingCode, $status, $rawResponse = []) {
        self::ensureSchema();
        try {
            $db = getDB();
            $stmt = $db->prepare("UPDATE orders SET courier_name = ?, courier_consignment_id = ?, courier_tracking_code = ?, courier_status = ?, courier_response = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$courierName, $consignmentId, $trackingCode, $status, json_encode($rawResponse), $orderId]);
        } catch (Exception $e) {}
    }

    /**
     * Map third-party courier status to standard OnlineBdMart order status
     */
    public static function mapCourierStatusToStoreStatus($courierStatus) {
        $s = strtolower(trim((string)$courierStatus));

        switch ($s) {
            case 'pending':
            case 'in_review':
            case 'hold':
                return 'pending';
            case 'confirmed':
            case 'accepted':
            case 'approved':
            case 'processing':
                return 'processing';
            case 'picked':
            case 'picked_up':
            case 'in_transit':
            case 'out_for_delivery':
            case 'dispatched':
                return 'shipped';
            case 'delivered':
            case 'partial_delivered':
            case 'paid':
                return 'delivered';
            case 'cancelled':
            case 'canceled':
            case 'returned':
            case 'return_to_merchant':
            case 'rejected':
                return 'cancelled';
            default:
                return 'processing';
        }
    }
}
