<?php
/**
 * OnlineBdMart - Meta Conversions API (CAPI) & Stape GTM Server Engine
 * Implements server-side event tracking, SHA-256 customer data normalization,
 * event deduplication (event_id), and Stape/Meta API dispatch.
 */

if (!defined('ABSPATH')) {
    // allow direct include
}

require_once __DIR__ . '/../config/database.php';

class MetaConversionsAPI {
    
    /**
     * Normalize and SHA-256 hash customer parameters as per Meta specifications
     */
    public static function hashParam($value) {
        $clean = trim((string)$value);
        if ($clean === '') return null;
        $clean = mb_strtolower($clean, 'UTF-8');
        return hash('sha256', $clean);
    }

    /**
     * Format phone number to international E.164 (e.g. 8801712345678) before hashing
     */
    public static function hashPhone($phone) {
        $digits = preg_replace('/[^0-9]/', '', (string)$phone);
        if (empty($digits)) return null;

        if (str_starts_with($digits, '880')) {
            $formatted = $digits;
        } elseif (str_starts_with($digits, '01') && strlen($digits) === 11) {
            $formatted = '88' . $digits;
        } elseif (str_starts_with($digits, '1') && strlen($digits) === 10) {
            $formatted = '880' . $digits;
        } else {
            $formatted = '88' . $digits;
        }

        return hash('sha256', $formatted);
    }

    /**
     * Extract browser _fbp and _fbc cookies or fbclid URL parameter
     */
    public static function getMetaCookies() {
        $fbp = $_COOKIE['_fbp'] ?? null;
        $fbc = $_COOKIE['_fbc'] ?? null;

        // If _fbc is not in cookie but fbclid is in URL, generate standard _fbc string
        if (empty($fbc) && !empty($_GET['fbclid'])) {
            $fbclid = trim($_GET['fbclid']);
            $fbc = 'fb.1.' . time() . '.' . $fbclid;
        }

        return [
            'fbp' => $fbp,
            'fbc' => $fbc
        ];
    }

    /**
     * Build User Data payload with Event Match Quality hashing
     */
    public static function buildUserData(array $customer = []) {
        $cookies = self::getMetaCookies();

        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (str_contains($ip, ',')) {
            $ip = trim(explode(',', $ip)[0]);
        }
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $userData = [
            'client_ip_address' => $ip,
            'client_user_agent' => $ua,
        ];

        if (!empty($cookies['fbp'])) $userData['fbp'] = $cookies['fbp'];
        if (!empty($cookies['fbc'])) $userData['fbc'] = $cookies['fbc'];

        if (!empty($customer['email'])) {
            $userData['em'] = [self::hashParam($customer['email'])];
        }
        if (!empty($customer['phone'])) {
            $userData['ph'] = [self::hashPhone($customer['phone'])];
        }
        if (!empty($customer['first_name'])) {
            $userData['fn'] = [self::hashParam($customer['first_name'])];
        }
        if (!empty($customer['last_name'])) {
            $userData['ln'] = [self::hashParam($customer['last_name'])];
        }
        if (!empty($customer['name']) && empty($customer['first_name'])) {
            $parts = explode(' ', trim($customer['name']), 2);
            $userData['fn'] = [self::hashParam($parts[0])];
            if (!empty($parts[1])) {
                $userData['ln'] = [self::hashParam($parts[1])];
            }
        }
        if (!empty($customer['city']) || !empty($customer['district'])) {
            $userData['ct'] = [self::hashParam($customer['city'] ?? $customer['district'])];
        }
        
        $userData['country'] = [self::hashParam($customer['country'] ?? 'bd')];

        return $userData;
    }

    /**
     * Dispatch server event directly to Meta Graph API Conversions endpoint
     */
    public static function sendToMetaGraphApi($eventName, $eventId, array $customData, array $userData, $eventSourceUrl = '') {
        $settings = getAllSettings();
        $pixelId = trim($settings['facebook_pixel_id'] ?? '');
        $accessToken = trim($settings['meta_capi_access_token'] ?? '');
        $testCode = trim($settings['meta_capi_test_code'] ?? '');

        if (empty($pixelId) || empty($accessToken)) {
            return ['success' => false, 'error' => 'Pixel ID or Meta CAPI Access Token not configured.'];
        }

        if (empty($eventSourceUrl)) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'] ?? 'onlinebdmart.com';
            $eventSourceUrl = $protocol . $host . ($_SERVER['REQUEST_URI'] ?? '/');
        }

        $event = [
            'event_name' => $eventName,
            'event_time' => time(),
            'event_id' => (string)$eventId,
            'event_source_url' => $eventSourceUrl,
            'action_source' => 'website',
            'user_data' => $userData,
            'custom_data' => $customData,
        ];

        $payload = [
            'data' => [$event]
        ];

        if (!empty($testCode)) {
            $payload['test_event_code'] = $testCode;
        }

        $url = "https://graph.facebook.com/v19.0/{$pixelId}/events?access_token=" . urlencode($accessToken);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        $resDecoded = json_decode($response, true);
        return [
            'success' => ($httpCode >= 200 && $httpCode < 300),
            'http_code' => $httpCode,
            'response' => $resDecoded,
            'error' => $err ?: ($resDecoded['error']['message'] ?? null)
        ];
    }

    /**
     * Dispatch server event to Stape GTM Server Container endpoint (if configured)
     */
    public static function sendToStapeGtmServer($eventName, $eventId, array $customData, array $userData, $eventSourceUrl = '') {
        $settings = getAllSettings();
        $stapeUrl = rtrim(trim($settings['stape_server_container_url'] ?? ''), '/');
        if (empty($stapeUrl)) {
            return ['success' => false, 'error' => 'Stape GTM Server URL not set.'];
        }

        $payload = [
            'event_name' => $eventName,
            'event_id' => (string)$eventId,
            'event_time' => time(),
            'event_source_url' => $eventSourceUrl ?: ('https://onlinebdmart.com' . ($_SERVER['REQUEST_URI'] ?? '/')),
            'user_data' => $userData,
            'custom_data' => $customData,
        ];

        $targetEndpoint = $stapeUrl . '/event';
        $ch = curl_init($targetEndpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-Gtm-Server-Client: Stape'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'success' => ($httpCode >= 200 && $httpCode < 300),
            'http_code' => $httpCode
        ];
    }

    /**
     * Master dispatcher: Sends to both Meta CAPI & Stape Server Container
     */
    public static function trackServerEvent($eventName, $eventId, array $customData = [], array $customerData = [], $sourceUrl = '') {
        $userData = self::buildUserData($customerData);

        // 1. Direct Meta Conversions API
        $metaRes = self::sendToMetaGraphApi($eventName, $eventId, $customData, $userData, $sourceUrl);

        // 2. Stape GTM Server Container (if endpoint configured)
        $stapeRes = self::sendToStapeGtmServer($eventName, $eventId, $customData, $userData, $sourceUrl);

        return [
            'event_name' => $eventName,
            'event_id' => $eventId,
            'meta' => $metaRes,
            'stape' => $stapeRes
        ];
    }
}
