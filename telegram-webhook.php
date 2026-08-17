<?php
/**
 * OnlineBdMart - Telegram Webhook Handler & Bot Controller
 * Receives Webhook callbacks from Telegram, updates orders in database, and edits messages in realtime.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/telegram_bot.php';

header('Content-Type: application/json');

$content = file_get_contents('php://input');
$update = json_decode($content, true);

if (!$update) {
    echo json_encode(['ok' => true, 'message' => 'OnlineBdMart Telegram Webhook is active and running!']);
    exit;
}

$cfg = getTelegramBotConfig();
$token = $cfg['bot_token'];

if (empty($token)) {
    echo json_encode(['ok' => false, 'error' => 'Bot token not set']);
    exit;
}

try {
    $db = getDB();

    // 1. Handle Inline Button Callback Queries (Confirm, Process, Ship, Deliver, Cancel)
    if (isset($update['callback_query'])) {
        $callback = $update['callback_query'];
        $callbackId = $callback['id'];
        $chatId = $callback['message']['chat']['id'] ?? ($callback['from']['id'] ?? '');
        $messageId = $callback['message']['message_id'] ?? null;
        $data = trim($callback['data'] ?? '');

        $action = '';
        $orderId = 0;

        if (str_contains($data, '_')) {
            list($action, $orderId) = explode('_', $data, 2);
            $orderId = (int)$orderId;
        }

        if ($orderId === 9999) {
            // Test order mock action
            telegramApiCall('answerCallbackQuery', [
                'callback_query_id' => $callbackId,
                'text' => "✓ Action '{$action}' acknowledged! Telegram Bot webhook is working perfectly.",
                'show_alert' => true
            ], $token);
            exit;
        }

        if ($orderId > 0 && in_array($action, ['confirm', 'process', 'ship', 'deliver', 'cancel'])) {
            $statusMap = [
                'confirm' => 'confirmed',
                'process' => 'processing',
                'ship'    => 'shipped',
                'deliver' => 'delivered',
                'cancel'  => 'cancelled',
            ];
            $newStatus = $statusMap[$action];

            // Update database
            $upd = $db->prepare("UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $upd->execute([$newStatus, $orderId]);

            // Trigger customer notification email
            require_once __DIR__ . '/includes/smtp_mailer.php';
            if ($newStatus === 'delivered') {
                @sendOrderDeliveredEmailNotification($orderId);
            } elseif ($newStatus === 'shipped') {
                @sendOrderShippedEmailNotification($orderId);
            }

            // Fetch refreshed order
            $orderStmt = $db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
            $orderStmt->execute([$orderId]);
            $order = $orderStmt->fetch();

            if ($order) {
                $itemsStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $itemsStmt->execute([$orderId]);
                $items = $itemsStmt->fetchAll();

                $statusHead = match($newStatus) {
                    'confirmed'  => '✅ ORDER #' . ($order['order_number'] ?: $orderId) . ' CONFIRMED BY ADMIN!',
                    'processing' => '⚙️ ORDER #' . ($order['order_number'] ?: $orderId) . ' IS NOW PROCESSING / PACKING!',
                    'shipped'    => '🚚 ORDER #' . ($order['order_number'] ?: $orderId) . ' MARKED AS SHIPPED!',
                    'delivered'  => '📦 ORDER #' . ($order['order_number'] ?: $orderId) . ' DELIVERED!',
                    'cancelled'  => '❌ ORDER #' . ($order['order_number'] ?: $orderId) . ' CANCELLED!',
                    default      => 'ORDER STATUS UPDATED'
                };

                $rawPhone = $order['customer_phone'] ?: ($order['phone'] ?: '');
                $updatedText = formatTelegramOrderMessage($order, $items, $statusHead);
                $updatedKeyboard = getTelegramOrderInlineKeyboard($orderId, $newStatus, $cfg['site_url'], $rawPhone);

                // Edit original message text
                if ($messageId) {
                    telegramApiCall('editMessageText', [
                        'chat_id' => $chatId,
                        'message_id' => $messageId,
                        'text' => $updatedText,
                        'parse_mode' => 'HTML',
                        'reply_markup' => $updatedKeyboard,
                        'disable_web_page_preview' => true
                    ], $token);
                }

                // Popup feedback
                telegramApiCall('answerCallbackQuery', [
                    'callback_query_id' => $callbackId,
                    'text' => "✓ Order #{$orderId} updated to " . strtoupper($newStatus) . "!",
                    'show_alert' => false
                ], $token);
            }
        }
        exit;
    }

    // 2. Handle Text Bot Commands (/start, /orders, /confirm, /process, /ship, /cancel, /view)
    if (isset($update['message'])) {
        $msg = $update['message'];
        $chatId = $msg['chat']['id'];
        $text = trim($msg['text'] ?? '');

        if ($text === '/start' || $text === '/help') {
            $welcome = "👋 <b>Welcome to OnlineBdMart Bot Manager!</b>\n";
            $welcome .= "━━━━━━━━━━━━━━━━━━━━\n";
            $welcome .= "Use the commands below to monitor and manage your store orders directly inside Telegram:\n\n";
            $welcome .= "📦 <b>/orders</b> — View latest pending orders\n";
            $welcome .= "🔍 <b>/view [ID]</b> — View full order & customer details\n";
            $welcome .= "✅ <b>/confirm [ID]</b> — Confirm order\n";
            $welcome .= "⚙️ <b>/process [ID]</b> — Set order to Processing\n";
            $welcome .= "🚚 <b>/ship [ID]</b> — Mark order as shipped\n";
            $welcome .= "❌ <b>/cancel [ID]</b> — Cancel an order\n";
            $welcome .= "📊 <b>/status</b> — Store summary statistics\n\n";
            $welcome .= "🌐 Admin Panel: <a href='{$cfg['site_url']}/admin-panel'>Open Dashboard</a>";

            telegramApiCall('sendMessage', [
                'chat_id' => $chatId,
                'text' => $welcome,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true
            ], $token);
            exit;
        }

        if ($text === '/orders' || $text === '/pending') {
            $orders = $db->query("SELECT * FROM orders WHERE status = 'pending' ORDER BY id DESC LIMIT 5")->fetchAll();
            if (empty($orders)) {
                telegramApiCall('sendMessage', [
                    'chat_id' => $chatId,
                    'text' => "🎉 <b>No pending orders!</b> All current orders have been processed.",
                    'parse_mode' => 'HTML'
                ], $token);
                exit;
            }

            foreach ($orders as $ord) {
                $itemsStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $itemsStmt->execute([$ord['id']]);
                $items = $itemsStmt->fetchAll();

                $rawPhone = $ord['customer_phone'] ?: ($ord['phone'] ?: '');
                $ordText = formatTelegramOrderMessage($ord, $items, '⏳ PENDING ORDER #' . ($ord['order_number'] ?: $ord['id']));
                $ordKb = getTelegramOrderInlineKeyboard($ord['id'], $ord['status'], $cfg['site_url'], $rawPhone);

                telegramApiCall('sendMessage', [
                    'chat_id' => $chatId,
                    'text' => $ordText,
                    'parse_mode' => 'HTML',
                    'reply_markup' => $ordKb,
                    'disable_web_page_preview' => true
                ], $token);
            }
            exit;
        }

        if ($text === '/status') {
            $totalOrders = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
            $pendingOrders = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
            $processingOrders = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'processing'")->fetchColumn();
            $confirmedOrders = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'confirmed'")->fetchColumn();
            $deliveredOrders = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered'")->fetchColumn();
            $totalSales = (float)$db->query("SELECT SUM(grand_total) FROM orders WHERE status != 'cancelled'")->fetchColumn();

            $stats = "📊 <b>OnlineBdMart Store Analytics</b>\n";
            $stats .= "━━━━━━━━━━━━━━━━━━━━\n";
            $stats .= "🧾 Total Orders: <b>{$totalOrders}</b>\n";
            $stats .= "⏳ Pending Orders: <b>{$pendingOrders}</b>\n";
            $stats .= "⚙️ Processing: <b>{$processingOrders}</b>\n";
            $stats .= "✅ Confirmed: <b>{$confirmedOrders}</b>\n";
            $stats .= "📦 Delivered: <b>{$deliveredOrders}</b>\n";
            $stats .= "💰 Total Revenue: <b>৳" . number_format($totalSales, 2) . "</b>\n";

            telegramApiCall('sendMessage', [
                'chat_id' => $chatId,
                'text' => $stats,
                'parse_mode' => 'HTML'
            ], $token);
            exit;
        }

        // Handle single command with ID: e.g. /confirm 102 or /process 102 or /ship 102
        if (preg_match('/^\/(confirm|process|ship|deliver|cancel|view)[_ ](\d+)$/i', $text, $matches)) {
            $cmd = strtolower($matches[1]);
            $ordId = (int)$matches[2];

            $orderStmt = $db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
            $orderStmt->execute([$ordId]);
            $order = $orderStmt->fetch();

            if (!$order) {
                telegramApiCall('sendMessage', [
                    'chat_id' => $chatId,
                    'text' => "❌ Order #{$ordId} not found.",
                    'parse_mode' => 'HTML'
                ], $token);
                exit;
            }

            $rawPhone = $order['customer_phone'] ?: ($order['phone'] ?: '');

            if ($cmd === 'view') {
                $itemsStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $itemsStmt->execute([$ordId]);
                $items = $itemsStmt->fetchAll();

                $ordText = formatTelegramOrderMessage($order, $items, '🔍 ORDER DETAILS #' . ($order['order_number'] ?: $ordId));
                $ordKb = getTelegramOrderInlineKeyboard($ordId, $order['status'], $cfg['site_url'], $rawPhone);

                telegramApiCall('sendMessage', [
                    'chat_id' => $chatId,
                    'text' => $ordText,
                    'parse_mode' => 'HTML',
                    'reply_markup' => $ordKb,
                    'disable_web_page_preview' => true
                ], $token);
                exit;
            } else {
                $statusMap = [
                    'confirm' => 'confirmed',
                    'process' => 'processing',
                    'ship'    => 'shipped',
                    'deliver' => 'delivered',
                    'cancel'  => 'cancelled',
                ];
                $newStatus = $statusMap[$cmd];

                $db->prepare("UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$newStatus, $ordId]);

                $order['status'] = $newStatus;
                $itemsStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $itemsStmt->execute([$ordId]);
                $items = $itemsStmt->fetchAll();

                $ordText = formatTelegramOrderMessage($order, $items, '✓ ORDER #' . ($order['order_number'] ?: $ordId) . ' UPDATED TO ' . strtoupper($newStatus));
                $ordKb = getTelegramOrderInlineKeyboard($ordId, $newStatus, $cfg['site_url'], $rawPhone);

                telegramApiCall('sendMessage', [
                    'chat_id' => $chatId,
                    'text' => $ordText,
                    'parse_mode' => 'HTML',
                    'reply_markup' => $ordKb,
                    'disable_web_page_preview' => true
                ], $token);
                exit;
            }
        }
    }

} catch (Exception $e) {
    // Log exception safely
}

echo json_encode(['ok' => true]);
