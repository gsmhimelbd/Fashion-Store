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
            $boundary = '=_obm_' . md5(uniqid(microtime(true), true));
            $headers = [];
            $headers[] = "Date: " . date('r');
            $headers[] = "From: =?UTF-8?B?" . base64_encode($this->fromName) . "?= <{$from}>";
            $headers[] = "To: <{$to}>";
            $headers[] = "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=";
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-Type: text/html; charset=UTF-8";
            $headers[] = "Content-Transfer-Encoding: 8bit";
            $headers[] = "X-Mailer: OnlineBdMart SMTP Engine";

            $messagePayload = implode("\r\n", $headers) . "\r\n\r\n" . $htmlBody . "\r\n.";
            
            $this->log("Transmitting message body (" . strlen($htmlBody) . " bytes)...");
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

        // Items HTML rows
        $itemsRows = '';
        foreach ($items as $item) {
            $itemTotal = number_format($item['price'] * $item['quantity'], 2);
            $variantTag = '';
            if (!empty($item['color'])) $variantTag .= "<div style='font-size: 11px; color: #6366f1; font-weight: bold;'>Color: " . htmlspecialchars($item['color']) . "</div>";
            if (!empty($item['size'])) $variantTag .= "<div style='font-size: 11px; color: #d97706; font-weight: bold;'>Size/Liter: " . htmlspecialchars($item['size']) . "</div>";
            $itemsRows .= "<tr>
                <td style='padding: 10px 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #1e293b;'>
                    <div>{$item['product_name']}</div>
                    {$variantTag}
                </td>
                <td style='padding: 10px 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px; text-align: center; color: #64748b;'>{$item['quantity']}</td>
                <td style='padding: 10px 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px; text-align: right; color: #1e293b;'>৳" . number_format($item['price'], 2) . "</td>
                <td style='padding: 10px 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px; text-align: right; font-weight: bold; color: #4338ca;'>৳{$itemTotal}</td>
            </tr>";
        }

        $emailHtml = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden;'>
            <div style='background: linear-gradient(135deg, #0f172a, #312e81); padding: 25px; text-align: center; color: #ffffff;'>
                <h1 style='margin: 0; font-size: 24px; font-weight: 800; letter-spacing: 1px;'>{$storeName}</h1>
                <p style='margin: 6px 0 0 0; font-size: 13px; color: #cbd5e1;'>Order Confirmation • #{$orderNo}</p>
            </div>
            
            <div style='padding: 25px;'>
                <h2 style='font-size: 18px; color: #0f172a; margin-top: 0;'>Thank you for your order, {$name}!</h2>
                <p style='font-size: 13px; color: #475569; line-height: 1.6;'>Your order has been received and is being prepared for packaging and fast dispatch.</p>
                
                <div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; margin: 20px 0;'>
                    <h3 style='margin: 0 0 10px 0; font-size: 14px; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px;'>Shipping & Contact Info</h3>
                    <p style='margin: 4px 0; font-size: 13px; color: #334155;'><strong>Recipient:</strong> {$name}</p>
                    <p style='margin: 4px 0; font-size: 13px; color: #334155;'><strong>Phone:</strong> {$phone}</p>
                    <p style='margin: 4px 0; font-size: 13px; color: #334155;'><strong>Address:</strong> {$address}, {$district}</p>
                    <p style='margin: 4px 0; font-size: 13px; color: #334155;'><strong>Payment Method:</strong> " . strtoupper($order['payment_method'] ?? 'COD') . "</p>
                </div>

                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <thead>
                        <tr style='background: #f1f5f9; text-align: left;'>
                            <th style='padding: 10px 12px; font-size: 12px; color: #475569; border-radius: 8px 0 0 0;'>Item</th>
                            <th style='padding: 10px 12px; font-size: 12px; color: #475569; text-align: center;'>Qty</th>
                            <th style='padding: 10px 12px; font-size: 12px; color: #475569; text-align: right;'>Price</th>
                            <th style='padding: 10px 12px; font-size: 12px; color: #475569; text-align: right; border-radius: 0 8px 0 0;'>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$itemsRows}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan='3' style='padding: 12px; text-align: right; font-size: 13px; font-weight: bold; color: #0f172a;'>Grand Total:</td>
                            <td style='padding: 12px; text-align: right; font-size: 16px; font-weight: 800; color: #4f46e5;'>৳{$total}</td>
                        </tr>
                    </tfoot>
                </table>

                <div style='text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #e2e8f0;'>
                    <a href='https://onlinebdmart.com/track-order.php?order=" . urlencode($orderNo) . "' style='background: #4f46e5; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 10px; font-size: 13px; font-weight: bold; display: inline-block;'>Track Order Status Live &rarr;</a>
                </div>
            </div>

            <div style='background: #f8fafc; padding: 15px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0;'>
                &copy; " . date('Y') . " {$storeName}. All rights reserved.
            </div>
        </div>";

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
