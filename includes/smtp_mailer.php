<?php
/**
 * OnlineBdMart - Pure PHP Socket SMTP Mailer
 * Zero-dependency, self-contained SMTP client supporting SSL, TLS (STARTTLS), and AUTH LOGIN.
 */

if (!defined('ABSPATH')) {
    // allow direct include
}

class SocketSMTPMailer {
    private $host;
    private $port;
    private $username;
    private $password;
    private $encryption;
    private $fromAddress;
    private $fromName;
    private $timeout = 15;
    private $socket = null;
    public $logs = [];

    public function __construct(array $config = []) {
        $this->host = $config['smtp_host'] ?? 'localhost';
        $this->port = (int)($config['smtp_port'] ?? 465);
        $this->username = $config['smtp_username'] ?? '';
        $this->password = $config['smtp_password'] ?? '';
        $this->encryption = strtolower($config['smtp_encryption'] ?? 'ssl');
        $this->fromAddress = $config['smtp_from_address'] ?? $this->username;
        $this->fromName = $config['smtp_from_name'] ?? 'OnlineBdMart';
    }

    private function log($msg) {
        $this->logs[] = date('[Y-m-d H:i:s] ') . $msg;
    }

    private function readResponse() {
        $response = '';
        while ($str = fgets($this->socket, 515)) {
            $response .= $str;
            if (substr($str, 3, 1) === ' ') {
                break;
            }
        }
        $this->log('SERVER: ' . trim($response));
        return $response;
    }

    private function sendCommand($cmd, $expectedCode = 250, $mask = false) {
        $this->log('CLIENT: ' . ($mask ? '********' : $cmd));
        fwrite($this->socket, $cmd . "\r\n");
        $resp = $this->readResponse();
        $code = (int)substr($resp, 0, 3);
        if ($expectedCode && $code !== $expectedCode) {
            throw new Exception("SMTP Error: Expected {$expectedCode}, but received [{$resp}]");
        }
        return $resp;
    }

    public function send($to, $subject, $htmlBody, $plainAlt = '') {
        $this->logs = [];
        $this->log("Connecting to SMTP server {$this->host}:{$this->port} (Encryption: {$this->encryption})...");

        // Determine protocol prefix
        $targetHost = $this->host;
        if ($this->encryption === 'ssl' || $this->port === 465) {
            $targetHost = 'ssl://' . $this->host;
        }

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $errno = 0;
        $errstr = '';
        $this->socket = @stream_socket_client($targetHost . ':' . $this->port, $errno, $errstr, $this->timeout, STREAM_CLIENT_CONNECT, $context);

        if (!$this->socket) {
            $this->log("Direct socket failed ({$errno}: {$errstr}). Attempting mail() fallback...");
            return $this->fallbackMail($to, $subject, $htmlBody);
        }

        stream_set_timeout($this->socket, $this->timeout);

        try {
            $this->readResponse(); // Initial greeting

            $this->sendCommand('EHLO ' . gethostname(), 250);

            // If TLS is requested on non-ssl connection
            if ($this->encryption === 'tls' || $this->port === 587) {
                $this->log("Initiating STARTTLS cryptographic handshake...");
                $this->sendCommand('STARTTLS', 220);
                if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new Exception("STARTTLS cryptographic negotiation failed.");
                }
                $this->sendCommand('EHLO ' . gethostname(), 250);
            }

            // Authentication
            if (!empty($this->username)) {
                $this->log("Authenticating as {$this->username}...");
                $this->sendCommand('AUTH LOGIN', 334);
                $this->sendCommand(base64_encode($this->username), 334, false);
                $this->sendCommand(base64_encode($this->password), 235, true);
                $this->log("SMTP Authentication successful!");
            }

            // Envelope
            $from = $this->fromAddress ?: $this->username;
            $this->sendCommand("MAIL FROM:<{$from}>", 250);
            $this->sendCommand("RCPT TO:<{$to}>", 250);
            $this->sendCommand("DATA", 354);

            // Message Data & Headers
            $headers = [];
            $headers[] = "Date: " . date('r');
            $headers[] = "From: =?UTF-8?B?" . base64_encode($this->fromName) . "?= <{$from}>";
            $headers[] = "To: <{$to}>";
            $headers[] = "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=";
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-Type: text/html; charset=UTF-8";
            $headers[] = "Content-Transfer-Encoding: base64";
            $headers[] = "X-Mailer: OnlineBdMart SMTP Engine";

            // Use chunk_split base64 to prevent SMTP line-length truncation and email clipping
            $encodedBody = rtrim(chunk_split(base64_encode($htmlBody), 76, "\r\n"));
            $messagePayload = implode("\r\n", $headers) . "\r\n\r\n" . $encodedBody . "\r\n.";
            
            $this->log("Transmitting message body (" . strlen($htmlBody) . " bytes, base64 encoded)...");
            fwrite($this->socket, $messagePayload . "\r\n");
            $resp = $this->readResponse();
            if ((int)substr($resp, 0, 3) !== 250) {
                throw new Exception("Message data rejected: " . $resp);
            }

            $this->sendCommand("QUIT", 221);
            @fclose($this->socket);
            $this->log("Message successfully accepted for delivery by SMTP server!");
            return true;

        } catch (Exception $e) {
            $this->log("SMTP Exception: " . $e->getMessage());
            if ($this->socket) {
                @fclose($this->socket);
            }
            // Fallback to PHP native mail
            return $this->fallbackMail($to, $subject, $htmlBody);
        }
    }

    private function fallbackMail($to, $subject, $htmlBody) {
        $from = $this->fromAddress ?: $this->username;
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: =?UTF-8?B?" . base64_encode($this->fromName) . "?= <{$from}>\r\n";
        $headers .= "Reply-To: {$from}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        $this->log("Trying native PHP mail() function...");
        if (@mail($to, $subject, $htmlBody, $headers)) {
            $this->log("Sent via native PHP mail() successfully!");
            return true;
        }
        $this->log("Native PHP mail() failed or is disabled on server.");
        return false;
    }
}

/**
 * Global helper to send SMTP email using saved settings
 */
function sendSMTPEmail($to, $subject, $htmlBody, &$debugLog = '') {
    require_once __DIR__ . '/../config/database.php';
    $settings = getAllSettings();

    $mailer = new SocketSMTPMailer([
        'smtp_host' => $settings['smtp_host'] ?? 'localhost',
        'smtp_port' => $settings['smtp_port'] ?? '465',
        'smtp_username' => $settings['smtp_username'] ?? '',
        'smtp_password' => $settings['smtp_password'] ?? '',
        'smtp_encryption' => $settings['smtp_encryption'] ?? 'ssl',
        'smtp_from_address' => $settings['smtp_from_address'] ?? ($settings['smtp_username'] ?? 'no-reply@onlinebdmart.com'),
        'smtp_from_name' => $settings['smtp_from_name'] ?? ($settings['store_name'] ?? 'OnlineBdMart'),
    ]);

    $success = $mailer->send($to, $subject, $htmlBody);
    $debugLog = implode("\n", $mailer->logs);
    return $success;
}

/**
 * Send order confirmation emails to customer & store admin
 */
function sendOrderEmailNotifications($orderId) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        if (!$order) return false;

        $itemsStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $itemsStmt->execute([$orderId]);
        $items = $itemsStmt->fetchAll();

        $settings = getAllSettings();
        $storeName = $settings['store_name'] ?? 'OnlineBdMart';
        $orderNo = $order['order_number'] ?: ('OBM-' . $order['id']);
        $total = number_format($order['grand_total'] ?: $order['total_amount'], 2);
        $phone = $order['customer_phone'] ?: $order['phone'];
        $name = $order['customer_name'] ?: 'Valued Customer';
        $address = $order['delivery_address'] ?: $order['address'];
        $district = $order['district_name'] ?: ($order['district'] ?: 'Bangladesh');

        $subtotalVal = number_format((float)($order['subtotal'] ?? 0), 2);
        $deliveryCostVal = number_format((float)($order['delivery_cost'] ?? ($order['delivery_charge'] ?? 120)), 2);
        $discountVal = number_format((float)($order['discount_amount'] ?? 0), 2);
        $grandTotalVal = number_format((float)($order['grand_total'] ?: ($order['total_amount'] ?? 0)), 2);
        $isCod = strtolower($order['payment_method'] ?? 'cod') === 'cod';

        // Items HTML rows
        $itemsRows = '';
        foreach ($items as $item) {
            $itemTotal = number_format($item['price'] * $item['quantity'], 2);
            $variantTag = '';
            if (!empty($item['color'])) $variantTag .= "<span style='display:inline-block; font-size:10px; font-weight:bold; color:#4338ca; background:#e0e7ff; padding:2px 6px; border-radius:4px; margin-right:4px;'>Color: " . htmlspecialchars($item['color']) . "</span>";
            if (!empty($item['size'])) $variantTag .= "<span style='display:inline-block; font-size:10px; font-weight:bold; color:#b45309; background:#fef3c7; padding:2px 6px; border-radius:4px;'>Size: " . htmlspecialchars($item['size']) . "</span>";
            
            $itemsRows .= "<tr>
                <td style='padding: 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #1e293b;'>
                    <div style='font-weight: bold; color: #0f172a; margin-bottom: 3px;'>{$item['product_name']}</div>
                    {$variantTag}
                </td>
                <td style='padding: 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px; text-align: center; color: #475569; font-weight: bold;'>{$item['quantity']}</td>
                <td style='padding: 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px; text-align: right; color: #1e293b;'>৳" . number_format($item['price'], 2) . "</td>
                <td style='padding: 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px; text-align: right; font-weight: bold; color: #4338ca;'>৳{$itemTotal}</td>
            </tr>";
        }

        $emailHtml = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Order Invoice #{$orderNo}</title>
        </head>
        <body style='margin: 0; padding: 20px 0; background-color: #f1f5f9; font-family: Helvetica, Arial, sans-serif;'>
            <table role='presentation' width='100%' border='0' cellspacing='0' cellpadding='0'>
                <tr>
                    <td align='center'>
                        <table role='presentation' width='600' border='0' cellspacing='0' cellpadding='0' style='max-width: 600px; width: 100%; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;'>
                            
                            <!-- Header Banner -->
                            <tr>
                                <td style='background: linear-gradient(135deg, #0f172a, #312e81); padding: 30px 25px; text-align: center; color: #ffffff;'>
                                    <h1 style='margin: 0; font-size: 24px; font-weight: 900; letter-spacing: 0.5px;'>{$storeName}</h1>
                                    <p style='margin: 6px 0 0 0; font-size: 13px; color: #cbd5e1;'>Official Order Invoice & Tax Confirmation</p>
                                    <div style='margin-top: 12px;'>
                                        <span style='background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; font-family: monospace;'>#{$orderNo}</span>
                                    </div>
                                </td>
                            </tr>

                            <!-- Order Greeting & Status -->
                            <tr>
                                <td style='padding: 25px 25px 15px 25px;'>
                                    <h2 style='font-size: 17px; color: #0f172a; margin: 0 0 8px 0;'>Thank you for your order, {$name}!</h2>
                                    <p style='font-size: 13px; color: #475569; line-height: 1.5; margin: 0;'>Your order has been verified and is being packaged for dispatch to your delivery address.</p>
                                </td>
                            </tr>

                            <!-- Customer & Delivery Address Card -->
                            <tr>
                                <td style='padding: 0 25px;'>
                                    <table role='presentation' width='100%' border='0' cellspacing='0' cellpadding='0' style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin: 10px 0;'>
                                        <tr>
                                            <td style='font-size: 12px; color: #475569; line-height: 1.6;'>
                                                <div style='font-weight: bold; color: #0f172a; font-size: 13px; margin-bottom: 6px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px;'>📍 Delivery & Contact Information:</div>
                                                <div><strong>Customer:</strong> {$name}</div>
                                                <div><strong>Mobile Phone:</strong> <span style='font-family: monospace; font-weight: bold; color: #4338ca;'>{$phone}</span></div>
                                                <div><strong>Delivery Address:</strong> {$address}, {$district}</div>
                                                <div><strong>Payment Method:</strong> <strong style='text-transform: uppercase; color: #0f172a;'>" . strtoupper($order['payment_method'] ?? 'COD') . "</strong></div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- Items Table -->
                            <tr>
                                <td style='padding: 10px 25px;'>
                                    <table role='presentation' width='100%' border='0' cellspacing='0' cellpadding='0' style='border-collapse: collapse; margin: 10px 0;'>
                                        <thead>
                                            <tr style='background: #f1f5f9;'>
                                                <th style='padding: 10px 12px; font-size: 11px; text-transform: uppercase; color: #475569; text-align: left;'>Product Item</th>
                                                <th style='padding: 10px 12px; font-size: 11px; text-transform: uppercase; color: #475569; text-align: center;'>Qty</th>
                                                <th style='padding: 10px 12px; font-size: 11px; text-transform: uppercase; color: #475569; text-align: right;'>Unit Price</th>
                                                <th style='padding: 10px 12px; font-size: 11px; text-transform: uppercase; color: #475569; text-align: right;'>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {$itemsRows}
                                        </tbody>
                                    </table>
                                </td>
                            </tr>

                            <!-- Price Breakdown Box -->
                            <tr>
                                <td style='padding: 0 25px;'>
                                    <table role='presentation' width='100%' border='0' cellspacing='0' cellpadding='0' style='border-top: 2px solid #e2e8f0; padding-top: 10px;'>
                                        <tr>
                                            <td style='font-size: 13px; color: #64748b; padding: 4px 0;'>Items Subtotal:</td>
                                            <td style='font-size: 13px; color: #1e293b; font-weight: bold; text-align: right; padding: 4px 0;'>৳{$subtotalVal}</td>
                                        </tr>
                                        <tr>
                                            <td style='font-size: 13px; color: #64748b; padding: 4px 0;'>Delivery Charge:</td>
                                            <td style='font-size: 13px; color: #1e293b; font-weight: bold; text-align: right; padding: 4px 0;'>৳{$deliveryCostVal}</td>
                                        </tr>";

        if ((float)($order['discount_amount'] ?? 0) > 0) {
            $emailHtml .= "
                                        <tr>
                                            <td style='font-size: 13px; color: #059669; padding: 4px 0;'>Coupon Discount:</td>
                                            <td style='font-size: 13px; color: #059669; font-weight: bold; text-align: right; padding: 4px 0;'>-৳{$discountVal}</td>
                                        </tr>";
        }

        $emailHtml .= "
                                        <tr>
                                            <td style='font-size: 15px; font-weight: 900; color: #0f172a; padding: 10px 0 4px 0; border-top: 1px solid #e2e8f0;'>Grand Total:</td>
                                            <td style='font-size: 17px; font-weight: 900; color: #4338ca; text-align: right; padding: 10px 0 4px 0; border-top: 1px solid #e2e8f0;'>৳{$grandTotalVal}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- Live Tracking Call to Action Button -->
                            <tr>
                                <td style='padding: 25px; text-align: center;'>
                                    <a href='https://onlinebdmart.com/track-order.php?order=" . urlencode($orderNo) . "' style='background: #4f46e5; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 12px; font-size: 13px; font-weight: bold; display: inline-block; box-shadow: 0 4px 10px rgba(79,70,229,0.3);'>Track Order Live Status &rarr;</a>
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style='background: #f8fafc; padding: 20px 25px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0;'>
                                    <div>&copy; " . date('Y') . " {$storeName}. All rights reserved.</div>
                                    <div style='margin-top: 4px;'>Helpline: {$phone} | Web: https://onlinebdmart.com</div>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>";

        // 1. Send to Customer if email is provided and notify setting enabled
        if (!empty($order['customer_email']) && filter_var($order['customer_email'], FILTER_VALIDATE_EMAIL)) {
            if (($settings['notify_order_placed'] ?? '1') === '1') {
                $dbg = '';
                sendSMTPEmail($order['customer_email'], "Order Confirmation #{$orderNo} - {$storeName}", $emailHtml, $dbg);
            }
        }

        // 2. Send Alert to Store Owner Email if configured
        $adminEmail = $settings['notify_admin_email'] ?? '';
        if (!empty($adminEmail) && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $dbg = '';
            sendSMTPEmail($adminEmail, "🚨 [NEW ORDER] #{$orderNo} - ৳{$total} ({$name})", $emailHtml, $dbg);
        }

        return true;
    } catch (Exception $e) {
        return false;
    }
}
