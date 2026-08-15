<?php
/**
 * OnlineBdMart - Universal Database Configuration
 * Auto-detects MySQL from .env or config, with SQLite fallback
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Read .env file
function loadEnv() {
    static $env = null;
    if ($env !== null) return $env;

    $env = [
        'DB_HOST' => '127.0.0.1',
        'DB_PORT' => '3306',
        'DB_DATABASE' => 'onlinebdmart',
        'DB_USERNAME' => 'root',
        'DB_PASSWORD' => '',
        'APP_NAME' => 'OnlineBdMart',
    ];

    $envFiles = [
        __DIR__ . '/../.env',
        __DIR__ . '/.env',
        dirname(__DIR__) . '/.env',
    ];

    foreach ($envFiles as $file) {
        if (file_exists($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || str_starts_with($line, '#')) continue;
                if (str_contains($line, '=')) {
                    list($k, $v) = explode('=', $line, 2);
                    $k = trim($k);
                    $v = trim($v, " \t\n\r\0\x0B\"'");
                    $env[$k] = $v;
                }
            }
            break;
        }
    }
    return $env;
}

// 2. Universal PDO Database Connection
function getDB() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $env = loadEnv();
    $host = $env['DB_HOST'] ?? '127.0.0.1';
    $port = $env['DB_PORT'] ?? '3306';
    $dbname = $env['DB_DATABASE'] ?? 'onlinebdmart';
    $user = $env['DB_USERNAME'] ?? 'root';
    $pass = $env['DB_PASSWORD'] ?? '';

    // Attempt MySQL Connection
    try {
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (Exception $e) {
        // Fallback to SQLite database
        $sqlitePath = __DIR__ . '/../database/database.sqlite';
        try {
            $pdo = new PDO("sqlite:{$sqlitePath}", null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            return $pdo;
        } catch (Exception $ex) {
            die("Database Connection Error: " . $e->getMessage() . "<br>Please check your database settings in .env file.");
        }
    }
}

// 3. Settings Helper
function getSetting($key, $default = '') {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

function getAllSettings() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch (Exception $e) {
        return [];
    }
}

// 4. Cart Helpers
function getCartItems() {
    return array_values($_SESSION['cart'] ?? []);
}

function getCartCount() {
    $items = getCartItems();
    return array_sum(array_column($items, 'quantity'));
}

function getCartSubtotal() {
    $items = getCartItems();
    return array_sum(array_map(fn($i) => ($i['price'] ?? 0) * ($i['quantity'] ?? 1), $items));
}

// 5. Admin Auth Helper
function isAdminLoggedIn() {
    return !empty($_SESSION['admin_id']);
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: /admin-panel/login');
        exit;
    }
}
