<?php
/**
 * OnlineBdMart - Telegram Bot Order Management & Notification Engine
 * Full support for instant alerts, inline keyboard action buttons, webhook callbacks, and bot commands.
 */

if (!defined('ABSPATH')) {
    // allow direct include
}

require_once __DIR__ . '/../config/database.php';

function getTelegramBotConfig() {
    $s = getAllSettings();
    
    // Auto-detect site base URL
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'onlinebdmart.com';
    $baseUrl = $protocol . $host;

    return [
        'bot_token' => trim($s['telegram_bot_token'] ?? ''),
        'chat_id' => trim($s['telegram_chat_id'] ?? ''),
        'enabled' => ($s['telegram_alerts_enabled'] ?? '0') === '1',
        'site_url' => $baseUrl,
    ];
}

function telegramApiCall($method, array $params = [], $token = null) {
    if (!$token) {
        $cfg = getTelegramBotConfig();
        $token = $cfg['bot_token'];
    }

    if (empty($token)) {
        return ['ok' => false, 'description' => 'Telegram Bot Token is empty.'];
    }

    $url = "https://api.telegram.org/bot{$token}/{$method}";

    // Use cURL if available, fallback to stream context
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['ok' => false, 'description' => 'cURL Error: ' . $err];
        }
    } else {
        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($params),
                'timeout' => 15,
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];
        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return ['ok' => false, 'description' => 'Stream HTTP request failed. Check server outgoing connection.'];
        }
    }

    $result = json_decode($response, true);
    return is_array($result) ? $result : ['ok' => false, 'description' => 'Invalid JSON: ' . $response];
}

function getTelegramBotMe($token = null) {
    return telegramApiCall('getMe', [], $token);
}

function setTelegramBotWebhook($webhookUrl, $token = null) {
    return telegramApiCall('setWebhook', [
        'url' => $webhookUrl,
        'allowed_updates' => ['message', 'callback_query']
    ], $token);
}

function deleteTelegramBotWebhook($token = null) {
    return telegramApiCall('deleteWebhook', ['drop_pending_updates' => true], $token);
}

function getTelegramBotWebhookInfo($token = null) {
    return telegramApiCall('getWebhookInfo', [], $token);
}

function formatTelegramOrderMessage($order, $items, $statusTitle = '🛍️ NEW ORDER RECEIVED!') {
    $orderNo = $order['order_number'] ?: ('OBM-' . $order['id']);
    $orderId = $order['id'];
    $name = htmlspecialchars($order['customer_name'] ?: 'Customer');
    $phone = htmlspecialchars($order['customer_phone'] ?: ($order['phone'] ?: 'N/A'));
    $address = htmlspecialchars($order['delivery_address'] ?: ($order['address'] ?: 'N/A'));
    $district = htmlspecialchars($order['district_name'] ?: ($order['district'] ?: 'Bangladesh'));
    $upazila = htmlspecialchars($order['upazila'] ?? '');
    $postOffice = htmlspecialchars($order['post_office'] ?? '');
    $notes = htmlspecialchars($order['notes'] ?? '');
    $status = strtoupper($order['status'] ?? 'PENDING');
    $createdAt = date('d M Y, h:i A', strtotime($order['created_at'] ?? 'now'));

    $subtotal = number_format((float)($order['subtotal'] ?? 0), 2);
    $deliveryCost = number_format((float)($order['delivery_cost'] ?? ($order['delivery_charge'] ?? 120)), 2);
    $grandTotal = number_format((float)($order['grand_total'] ?: ($order['total_amount'] ?? 0)), 2);

    $payMethod = strtoupper($order['payment_method'] ?? 'COD');
    $payNumber = htmlspecialchars($order['payment_number'] ?? '');
    $trxId = htmlspecialchars($order['transaction_id'] ?? '');

    // Item breakdown
    $itemsText = '';
    $totalQty = 0;
    foreach ($items as $idx => $item) {
        $pName = htmlspecialchars($item['product_name'] ?? 'Product');
        $qty = (int)($item['quantity'] ?? 1);
        $totalQty += $qty;
        $pPrice = number_format((float)($item['price'] ?? 0), 2);
        $pTotal = number_format((float)($item['total_price'] ?? ($item['price'] * $qty)), 2);
        $wholesaleTag = !empty($item['is_wholesale']) ? ' [Wholesale]' : '';
        $itemsText .= "• <b>{$pName}</b>{$wholesaleTag}\n  └ <i>{$qty} pcs × ৳{$pPrice}</i> = <b>৳{$pTotal}</b>\n";
    }

    $locationInfo = $address . "\n  └ <b>District:</b> " . $district;
    if ($upazila) $locationInfo .= " • <b>Upazila:</b> " . $upazila;
    if ($postOffice) $locationInfo .= " • <b>Post:</b> " . $postOffice;

    // Payment details block
    $payInfo = "• <b>Method:</b> <code>{$payMethod}</code>\n";
    if ($payNumber) {
        $payInfo .= "• <b>Sender Number:</b> <code>{$payNumber}</code>\n";
    }
    if ($trxId) {
        $payInfo .= "• <b>TrxID / Reference:</b> <code>{$trxId}</code>\n";
    }
    if ($payMethod === 'COD') {
        $payInfo .= "• <b>Type:</b> <i>Cash on Delivery (Pay upon doorstep delivery)</i>\n";
    }

    $msg = "<b>{$statusTitle}</b>\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "🧾 <b>Order ID:</b> <code>#{$orderNo}</code> (DB: #{$orderId})\n";
    $msg .= "📊 <b>Status:</b> <code>[{$status}]</code>\n";
    $msg .= "⏰ <b>Placed At:</b> {$createdAt}\n\n";

    $msg .= "👤 <b>CUSTOMER DETAILS</b>\n";
    $msg .= "• <b>Name:</b> {$name}\n";
    $msg .= "• <b>Phone:</b> <code>{$phone}</code>\n";
    $msg .= "• <b>Address:</b> {$locationInfo}\n\n";

    $msg .= "📦 <b>ORDERED ITEMS ({$totalQty} pcs)</b>\n";
    $msg .= $itemsText . "\n";

    $msg .= "💰 <b>PAYMENT & BILLING SUMMARY</b>\n";
    $msg .= "• <b>Subtotal:</b> ৳{$subtotal}\n";
    $msg .= "• <b>Delivery Fee:</b> ৳{$deliveryCost}\n";
    $msg .= "• <b>GRAND TOTAL:</b> <b>৳{$grandTotal}</b>\n";
    $msg .= $payInfo . "\n";

    if (!empty($notes)) {
        $msg .= "📝 <b>Customer Note:</b> <i>{$notes}</i>\n\n";
    }

    $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "⚡ <i>Tap an action button below to instantly update this order:</i>";

    return $msg;
}

function getTelegramOrderInlineKeyboard($orderId, $status = 'pending', $siteUrl = '') {
    if (!$siteUrl) {
        $cfg = getTelegramBotConfig();
        $siteUrl = $cfg['site_url'];
    }

    $status = strtolower($status);
    $adminUrl = rtrim($siteUrl, '/') . "/admin-panel/order-detail.php?id={$orderId}";

    $buttons = [];

    // Row 1: Primary Actions
    $row1 = [];
    if ($status !== 'confirmed' && $status !== 'completed' && $status !== 'delivered') {
        $row1[] = ['text' => '✅ Confirm Order', 'callback_data' => "confirm_{$orderId}"];
    }
    if ($status !== 'shipped' && $status !== 'delivered') {
        $row1[] = ['text' => '🚚 Mark Shipped', 'callback_data' => "ship_{$orderId}"];
    }
    if (!empty($row1)) {
        $buttons[] = $row1;
    }

    // Row 2: Secondary Actions
    $row2 = [];
    if ($status !== 'delivered') {
        $row2[] = ['text' => '📦 Mark Delivered', 'callback_data' => "deliver_{$orderId}"];
    }
    if ($status !== 'cancelled') {
        $row2[] = ['text' => '❌ Cancel Order', 'callback_data' => "cancel_{$orderId}"];
    }
    if (!empty($row2)) {
        $buttons[] = $row2;
    }

    // Row 3: Admin Web Direct Link
    $buttons[] = [
        ['text' => '👁️ View in Admin Panel', 'url' => $adminUrl]
    ];

    return ['inline_keyboard' => $buttons];
}

function sendTelegramOrderAlert($orderId) {
    $cfg = getTelegramBotConfig();
    if (empty($cfg['bot_token']) || empty($cfg['chat_id'])) {
        return ['ok' => false, 'description' => 'Telegram token or Chat ID is not configured.'];
    }

    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        if (!$order) {
            return ['ok' => false, 'description' => 'Order not found in database.'];
        }

        $itemsStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $itemsStmt->execute([$orderId]);
        $items = $itemsStmt->fetchAll();

        $text = formatTelegramOrderMessage($order, $items, '🛍️ NEW ORDER RECEIVED!');
        $keyboard = getTelegramOrderInlineKeyboard($order['id'], $order['status'] ?? 'pending', $cfg['site_url']);

        $params = [
            'chat_id' => $cfg['chat_id'],
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => $keyboard,
            'disable_web_page_preview' => true
        ];

        return telegramApiCall('sendMessage', $params, $cfg['bot_token']);
    } catch (Exception $e) {
        return ['ok' => false, 'description' => $e->getMessage()];
    }
}

function sendTelegramTestNotification() {
    $cfg = getTelegramBotConfig();
    if (empty($cfg['bot_token']) || empty($cfg['chat_id'])) {
        return ['ok' => false, 'description' => 'Please enter both Telegram Bot Token and Chat ID before testing.'];
    }

    $sampleOrder = [
        'id' => 9999,
        'order_number' => 'TEST-' . strtoupper(substr(md5(time()), 0, 6)),
        'customer_name' => 'Himel (Test Customer)',
        'customer_phone' => '01775153740',
        'delivery_address' => 'House #12, Road #4, Sector #11, Uttara',
        'district_name' => 'Dhaka',
        'upazila' => 'Uttara',
        'post_office' => '1230',
        'subtotal' => 3200.00,
        'delivery_cost' => 60.00,
        'grand_total' => 3260.00,
        'payment_method' => 'bkash',
        'payment_number' => '01775153740',
        'transaction_id' => 'TRX897TEST123',
        'status' => 'pending',
        'notes' => 'This is a live test notification from OnlineBdMart Admin Panel.',
        'created_at' => date('Y-m-d H:i:s'),
    ];

    $sampleItems = [
        [
            'product_name' => 'Naviforce Chronograph Luxury Watch',
            'quantity' => 1,
            'price' => 2450.00,
            'total_price' => 2450.00,
            'is_wholesale' => 0
        ],
        [
            'product_name' => 'Full Grain Leather Long Wallet',
            'quantity' => 1,
            'price' => 750.00,
            'total_price' => 750.00,
            'is_wholesale' => 0
        ]
    ];

    $text = formatTelegramOrderMessage($sampleOrder, $sampleItems, '🚀 TELEGRAM BOT CONNECTION TEST');
    $keyboard = getTelegramOrderInlineKeyboard(9999, 'pending', $cfg['site_url']);

    $params = [
        'chat_id' => $cfg['chat_id'],
        'text' => $text,
        'parse_mode' => 'HTML',
        'reply_markup' => $keyboard,
        'disable_web_page_preview' => true
    ];

    return telegramApiCall('sendMessage', $params, $cfg['bot_token']);
}
