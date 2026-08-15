<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Send new order notification to Telegram with interactive management buttons
     */
    public static function sendOrderNotification(Order $order)
    {
        $botToken = Setting::get('telegram_bot_token');
        $chatId = Setting::get('telegram_chat_id');

        if (!$botToken || !$chatId) {
            return false;
        }

        $itemsText = '';
        foreach ($order->items as $item) {
            $itemsText .= "• {$item->product_name} × {$item->quantity} (৳" . number_format($item->price * $item->quantity, 2) . ")\n";
        }

        $appUrl = config('app.url', url('/'));
        $invoiceUrl = "{$appUrl}/order/{$order->id}/invoice";

        $message = "🛒 *NEW ORDER RECEIVED - #{$order->id}*\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "👤 *Customer:* {$order->customer_name}\n";
        $message .= "📞 *Phone:* `{$order->phone}`\n";
        $message .= "📍 *Location:* {$order->district}, {$order->upazila}\n";
        $message .= "🏠 *Address:* {$order->address}\n";
        $message .= "💳 *Payment:* " . strtoupper($order->payment_method);
        if ($order->transaction_id) {
            $message .= " (TrxID: `{$order->transaction_id}`)";
        }
        $message .= "\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "📦 *Items:*\n{$itemsText}";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "💰 *Subtotal:* ৳" . number_format($order->subtotal, 2) . "\n";
        $message .= "🚚 *Delivery Charge:* ৳" . number_format($order->delivery_charge, 2) . "\n";
        $message .= "💵 *Grand Total:* *৳" . number_format($order->grand_total, 2) . "*\n";
        $message .= "📊 *Current Status:* *" . strtoupper($order->status) . "*\n\n";
        $message .= "⚡ _Manage this order instantly using the buttons below:_";

        $inlineKeyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ Confirm', 'callback_data' => "set_status:confirmed:{$order->id}"],
                    ['text' => '📦 Processing', 'callback_data' => "set_status:processing:{$order->id}"],
                ],
                [
                    ['text' => '🚚 Shipped', 'callback_data' => "set_status:shipped:{$order->id}"],
                    ['text' => '🎉 Delivered', 'callback_data' => "set_status:delivered:{$order->id}"],
                ],
                [
                    ['text' => '❌ Cancel', 'callback_data' => "set_status:cancelled:{$order->id}"],
                    ['text' => '📄 View Invoice', 'url' => $invoiceUrl],
                ]
            ]
        ];

        try {
            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
                'reply_markup' => json_encode($inlineKeyboard),
            ]);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Telegram notification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle incoming Telegram Webhook updates & button clicks & commands
     */
    public static function handleWebhook(array $update)
    {
        $botToken = Setting::get('telegram_bot_token');
        if (!$botToken) return false;

        // 1. Handle Callback Query (Inline Button Clicks)
        if (isset($update['callback_query'])) {
            $callback = $update['callback_query'];
            $callbackId = $callback['id'];
            $data = $callback['data'];
            $messageId = $callback['message']['message_id'] ?? null;
            $chatId = $callback['message']['chat']['id'] ?? null;

            if (str_starts_with($data, 'set_status:')) {
                $parts = explode(':', $data);
                $newStatus = $parts[1] ?? 'pending';
                $orderId = intval($parts[2] ?? 0);

                $order = Order::find($orderId);
                if ($order) {
                    $order->update(['status' => $newStatus]);

                    // Answer Telegram popup alert
                    Http::post("https://api.telegram.org/bot{$botToken}/answerCallbackQuery", [
                        'callback_query_id' => $callbackId,
                        'text' => "Order #{$orderId} updated to " . ucfirst($newStatus) . "!",
                        'show_alert' => false,
                    ]);

                    // Update message text to reflect new status
                    if ($messageId && $chatId) {
                        $appUrl = config('app.url', url('/'));
                        $invoiceUrl = "{$appUrl}/order/{$order->id}/invoice";

                        $statusEmoji = match ($newStatus) {
                            'confirmed' => '✅ CONFIRMED',
                            'processing' => '📦 PROCESSING / PACKAGED',
                            'shipped' => '🚚 SHIPPED / IN TRANSIT',
                            'delivered' => '🎉 DELIVERED SUCCESSFULLY',
                            'cancelled' => '❌ CANCELLED',
                            default => strtoupper($newStatus),
                        };

                        $text = $callback['message']['text'];
                        $text = preg_replace('/📊 Current Status: .*/', "📊 Current Status: *{$statusEmoji}*", $text);

                        Http::post("https://api.telegram.org/bot{$botToken}/editMessageText", [
                            'chat_id' => $chatId,
                            'message_id' => $messageId,
                            'text' => $text,
                            'parse_mode' => 'Markdown',
                            'reply_markup' => json_encode([
                                'inline_keyboard' => [
                                    [
                                        ['text' => '✅ Confirm', 'callback_data' => "set_status:confirmed:{$order->id}"],
                                        ['text' => '📦 Processing', 'callback_data' => "set_status:processing:{$order->id}"],
                                    ],
                                    [
                                        ['text' => '🚚 Shipped', 'callback_data' => "set_status:shipped:{$order->id}"],
                                        ['text' => '🎉 Delivered', 'callback_data' => "set_status:delivered:{$order->id}"],
                                    ],
                                    [
                                        ['text' => '❌ Cancel', 'callback_data' => "set_status:cancelled:{$order->id}"],
                                        ['text' => '📄 View Invoice', 'url' => $invoiceUrl],
                                    ]
                                ]
                            ]),
                        ]);
                    }
                }
            }
        }

        // 2. Handle Text Commands (/pending, /confirm <id>, /deliver <id>, etc.)
        if (isset($update['message']['text'])) {
            $text = trim($update['message']['text']);
            $chatId = $update['message']['chat']['id'] ?? null;

            if ($chatId) {
                if ($text === '/pending' || $text === '/orders') {
                    $pendingOrders = Order::where('status', 'pending')->latest()->take(5)->get();
                    $count = Order::where('status', 'pending')->count();

                    $msg = "📦 *PENDING ORDERS LIST ({$count})*\n━━━━━━━━━━━━━━━━━━━━\n";
                    if ($pendingOrders->isEmpty()) {
                        $msg .= "🎉 No pending orders right now! All orders are processed.";
                    } else {
                        foreach ($pendingOrders as $o) {
                            $msg .= "• *#{$o->id}* - {$o->customer_name} ({$o->phone}) - *৳" . number_format($o->grand_total, 2) . "*\n";
                        }
                        $msg .= "\n_Use /confirm <id> or /deliver <id> to update instantly._";
                    }

                    Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id' => $chatId,
                        'text' => $msg,
                        'parse_mode' => 'Markdown',
                    ]);
                } elseif (preg_match('/^\/(confirm|deliver|cancel|ship)\s+(\d+)$/i', $text, $matches)) {
                    $cmd = strtolower($matches[1]);
                    $orderId = intval($matches[2]);
                    $statusMap = ['confirm' => 'confirmed', 'deliver' => 'delivered', 'cancel' => 'cancelled', 'ship' => 'shipped'];
                    $newStatus = $statusMap[$cmd] ?? 'pending';

                    $order = Order::find($orderId);
                    if ($order) {
                        $order->update(['status' => $newStatus]);
                        $reply = "✅ *Order #{$orderId} status changed to " . strtoupper($newStatus) . "!*";
                    } else {
                        $reply = "❌ Order #{$orderId} not found.";
                    }

                    Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id' => $chatId,
                        'text' => $reply,
                        'parse_mode' => 'Markdown',
                    ]);
                }
            }
        }

        return true;
    }
}
