<?php
/**
 * OnlineBdMart - Universal Database Configuration
 * Auto-detects MySQL from .env or config, with SQLite fallback
 * Self-healing schema: auto-creates missing tables, columns, and SQLite NOW() function
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

// Helper to add missing column safely
function addColumnIfNotExists($pdo, $table, $col, $colDef) {
    try {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $stmt = $pdo->query("PRAGMA table_info({$table})");
            $cols = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array($col, $cols)) {
                $pdo->exec("ALTER TABLE {$table} ADD COLUMN {$col} {$colDef}");
            }
        } else {
            $stmt = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE '{$col}'");
            if ($stmt->rowCount() === 0) {
                $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$col}` {$colDef}");
            }
        }
    } catch (Exception $e) {}
}

// Auto ensure schema exists (creates missing tables on the fly)
function ensureTablesExist($pdo) {
    static $checked = false;
    if ($checked) return;
    $checked = true;

    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $isSqlite = ($driver === 'sqlite');

    try {
        if ($isSqlite) {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS admins (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT DEFAULT 'Super Admin',
                    username TEXT UNIQUE,
                    email TEXT UNIQUE,
                    password TEXT,
                    profile_photo TEXT DEFAULT 'uploads/admin/avatar.png',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS categories (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    parent_id INTEGER,
                    name TEXT NOT NULL,
                    slug TEXT UNIQUE NOT NULL,
                    emoji TEXT DEFAULT '🛍️',
                    icon TEXT DEFAULT 'fa-tag',
                    image_path TEXT,
                    description TEXT,
                    is_active INTEGER DEFAULT 1,
                    display_order INTEGER DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS products (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    category_id INTEGER,
                    subcategory_id INTEGER,
                    name TEXT NOT NULL,
                    slug TEXT UNIQUE NOT NULL,
                    sku TEXT,
                    price REAL NOT NULL DEFAULT 0.00,
                    sale_price REAL,
                    wholesale_price REAL,
                    wholesale_moq INTEGER DEFAULT 5,
                    wholesale_min_qty INTEGER DEFAULT 5,
                    is_wholesale INTEGER DEFAULT 1,
                    stock INTEGER DEFAULT 50,
                    stock_quantity INTEGER DEFAULT 50,
                    is_featured INTEGER DEFAULT 1,
                    is_active INTEGER DEFAULT 1,
                    image_path TEXT DEFAULT 'images/products/watch-1.jpg',
                    gallery_images TEXT,
                    short_description TEXT,
                    description TEXT,
                    specifications TEXT,
                    why_buy_from_us TEXT,
                    reviews_count INTEGER DEFAULT 45,
                    rating REAL DEFAULT 4.9,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS banners (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    subtitle TEXT,
                    badge_text TEXT,
                    button_text TEXT DEFAULT 'Shop Now',
                    button_url TEXT DEFAULT 'shop.php',
                    image_path TEXT NOT NULL,
                    display_order INTEGER DEFAULT 1,
                    is_active INTEGER DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS districts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    division_name TEXT NOT NULL,
                    delivery_fee REAL NOT NULL DEFAULT 120.00,
                    estimated_days TEXT DEFAULT '2-4 days',
                    is_active INTEGER DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS orders (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    order_number TEXT,
                    customer_name TEXT NOT NULL,
                    customer_email TEXT,
                    customer_phone TEXT,
                    phone TEXT,
                    whatsapp TEXT,
                    delivery_address TEXT,
                    address TEXT,
                    district TEXT,
                    district_name TEXT,
                    upazila TEXT,
                    subtotal REAL NOT NULL DEFAULT 0.00,
                    delivery_cost REAL NOT NULL DEFAULT 120.00,
                    delivery_charge REAL NOT NULL DEFAULT 120.00,
                    total_amount REAL NOT NULL DEFAULT 0.00,
                    grand_total REAL NOT NULL DEFAULT 0.00,
                    payment_method TEXT DEFAULT 'cod',
                    payment_number TEXT,
                    transaction_id TEXT,
                    status TEXT DEFAULT 'pending',
                    notes TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS order_items (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    order_id INTEGER NOT NULL,
                    product_id INTEGER,
                    product_name TEXT NOT NULL,
                    product_image TEXT,
                    price REAL NOT NULL,
                    quantity INTEGER NOT NULL DEFAULT 1,
                    total_price REAL,
                    is_wholesale INTEGER DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    email TEXT UNIQUE,
                    phone TEXT,
                    password TEXT NOT NULL,
                    address TEXT,
                    district TEXT DEFAULT 'Dhaka',
                    is_active INTEGER DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS cart_abandonments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    session_id TEXT,
                    customer_name TEXT,
                    customer_phone TEXT,
                    district_name TEXT,
                    product_name TEXT,
                    cart_value REAL DEFAULT 0.00,
                    step TEXT DEFAULT 'cart',
                    recovered INTEGER DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS settings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    key TEXT UNIQUE,
                    setting_key TEXT UNIQUE,
                    value TEXT,
                    setting_value TEXT,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS suppliers (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    contact_person TEXT,
                    phone TEXT,
                    email TEXT,
                    address TEXT,
                    photo TEXT DEFAULT 'uploads/suppliers/supplier-default.jpg',
                    supply_products TEXT,
                    category TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS customer_visits (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    ip_address TEXT,
                    session_id TEXT,
                    city TEXT,
                    district TEXT,
                    referrer TEXT,
                    page_url TEXT,
                    user_agent TEXT,
                    device_type TEXT,
                    visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS messages (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    email TEXT,
                    phone TEXT,
                    message TEXT NOT NULL,
                    is_read INTEGER DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS reviews (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    product_id INTEGER,
                    author_name TEXT NOT NULL,
                    customer_name TEXT,
                    rating INTEGER NOT NULL DEFAULT 5,
                    review_text TEXT,
                    comment TEXT,
                    district_name TEXT DEFAULT 'Dhaka',
                    is_approved INTEGER DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS blog_posts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    slug TEXT UNIQUE NOT NULL,
                    category TEXT DEFAULT 'Buying Guide',
                    author TEXT DEFAULT 'OnlineBdMart Team',
                    summary TEXT,
                    excerpt TEXT,
                    content TEXT,
                    image_path TEXT DEFAULT 'images/hero/hero-1.jpg',
                    meta_title TEXT,
                    meta_description TEXT,
                    meta_keywords TEXT,
                    is_published INTEGER DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS wholesale_inquiries (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    business_name TEXT NOT NULL,
                    contact_person TEXT NOT NULL,
                    phone TEXT NOT NULL,
                    whatsapp TEXT,
                    district TEXT,
                    estimated_monthly_quantity TEXT,
                    message TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
            ");
        } else {
            // MySQL Native DDL
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `admins` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(191) DEFAULT 'Super Admin',
                    `username` VARCHAR(191) UNIQUE,
                    `email` VARCHAR(191) UNIQUE,
                    `password` VARCHAR(191),
                    `profile_photo` VARCHAR(255) DEFAULT 'uploads/admin/avatar.png',
                    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS `categories` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `parent_id` INT NULL,
                    `name` VARCHAR(191) NOT NULL,
                    `slug` VARCHAR(191) UNIQUE NOT NULL,
                    `emoji` VARCHAR(50) DEFAULT '🛍️',
                    `icon` VARCHAR(100) DEFAULT 'fa-tag',
                    `image_path` VARCHAR(255) NULL,
                    `description` TEXT NULL,
                    `is_active` TINYINT(1) DEFAULT 1,
                    `display_order` INT DEFAULT 1,
                    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS `products` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `category_id` INT NULL,
                    `subcategory_id` INT NULL,
                    `name` VARCHAR(191) NOT NULL,
                    `slug` VARCHAR(191) UNIQUE NOT NULL,
                    `sku` VARCHAR(100) NULL,
                    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    `sale_price` DECIMAL(10,2) NULL,
                    `wholesale_price` DECIMAL(10,2) NULL,
                    `wholesale_moq` INT DEFAULT 5,
                    `wholesale_min_qty` INT DEFAULT 5,
                    `is_wholesale` TINYINT(1) DEFAULT 1,
                    `stock` INT DEFAULT 50,
                    `stock_quantity` INT DEFAULT 50,
                    `is_featured` TINYINT(1) DEFAULT 1,
                    `is_active` TINYINT(1) DEFAULT 1,
                    `image_path` VARCHAR(255) DEFAULT 'images/products/watch-1.jpg',
                    `gallery_images` TEXT NULL,
                    `short_description` TEXT NULL,
                    `description` LONGTEXT NULL,
                    `specifications` TEXT NULL,
                    `why_buy_from_us` TEXT NULL,
                    `reviews_count` INT DEFAULT 45,
                    `rating` DECIMAL(3,1) DEFAULT 4.9,
                    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS `banners` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `title` VARCHAR(191) NOT NULL,
                    `subtitle` VARCHAR(191) NULL,
                    `badge_text` VARCHAR(100) NULL,
                    `button_text` VARCHAR(100) DEFAULT 'Shop Now',
                    `button_url` VARCHAR(191) DEFAULT 'shop.php',
                    `image_path` VARCHAR(255) NOT NULL,
                    `display_order` INT DEFAULT 1,
                    `is_active` TINYINT(1) DEFAULT 1,
                    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS `districts` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(100) NOT NULL,
                    `division_name` VARCHAR(100) NOT NULL,
                    `delivery_fee` DECIMAL(10,2) NOT NULL DEFAULT 120.00,
                    `estimated_days` VARCHAR(50) DEFAULT '2-4 days',
                    `is_active` TINYINT(1) DEFAULT 1,
                    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS `orders` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `order_number` VARCHAR(100) NULL,
                    `customer_name` VARCHAR(191) NOT NULL,
                    `customer_email` VARCHAR(191) NULL,
                    `customer_phone` VARCHAR(100) NULL,
                    `phone` VARCHAR(100) NULL,
                    `whatsapp` VARCHAR(100) NULL,
                    `delivery_address` TEXT NULL,
                    `address` TEXT NULL,
                    `district` VARCHAR(100) NULL,
                    `district_name` VARCHAR(100) NULL,
                    `upazila` VARCHAR(100) NULL,
                    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    `delivery_cost` DECIMAL(10,2) NOT NULL DEFAULT 120.00,
                    `delivery_charge` DECIMAL(10,2) NOT NULL DEFAULT 120.00,
                    `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    `grand_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    `payment_method` VARCHAR(50) DEFAULT 'cod',
                    `payment_number` VARCHAR(100) NULL,
                    `transaction_id` VARCHAR(100) NULL,
                    `status` VARCHAR(50) DEFAULT 'pending',
                    `notes` TEXT NULL,
                    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS `order_items` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `order_id` INT NOT NULL,
                    `product_id` INT NULL,
                    `product_name` VARCHAR(191) NOT NULL,
                    `product_image` VARCHAR(255) NULL,
                    `price` DECIMAL(10,2) NOT NULL,
                    `quantity` INT NOT NULL DEFAULT 1,
                    `total_price` DECIMAL(10,2) NULL,
                    `is_wholesale` TINYINT(1) DEFAULT 0,
                    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS `users` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(191) NOT NULL,
                    `email` VARCHAR(191) UNIQUE,
                    `phone` VARCHAR(100) NULL,
                    `password` VARCHAR(191) NOT NULL,
                    `address` TEXT NULL,
                    `district` VARCHAR(100) DEFAULT 'Dhaka',
                    `is_active` TINYINT(1) DEFAULT 1,
                    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS `cart_abandonments` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `session_id` VARCHAR(191) NULL,
                    `customer_name` VARCHAR(191) NULL,
                    `customer_phone` VARCHAR(100) NULL,
                    `district_name` VARCHAR(100) NULL,
                    `product_name` VARCHAR(191) NULL,
                    `cart_value` DECIMAL(10,2) DEFAULT 0.00,
                    `step` VARCHAR(50) DEFAULT 'cart',
                    `recovered` TINYINT(1) DEFAULT 0,
                    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS `settings` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `key` VARCHAR(191) UNIQUE NULL,
                    `setting_key` VARCHAR(191) UNIQUE NULL,
                    `value` LONGTEXT NULL,
                    `setting_value` LONGTEXT NULL,
                    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS `suppliers` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(191) NOT NULL,
                    `contact_person` VARCHAR(191) NULL,
                    `phone` VARCHAR(100) NULL,
                    `email` VARCHAR(191) NULL,
                    `address` TEXT NULL,
                    `photo` VARCHAR(255) DEFAULT 'uploads/suppliers/supplier-default.jpg',
                    `supply_products` TEXT NULL,
                    `category` VARCHAR(191) NULL,
                    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS `customer_visits` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `ip_address` VARCHAR(100) NULL,
                    `session_id` VARCHAR(191) NULL,
                    `city` VARCHAR(100) NULL,
                    `district` VARCHAR(100) NULL,
                    `referrer` VARCHAR(255) NULL,
                    `page_url` VARCHAR(255) NULL,
                    `user_agent` TEXT NULL,
                    `device_type` VARCHAR(50) DEFAULT 'Mobile',
                    `visited_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS `messages` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(191) NOT NULL,
                    `email` VARCHAR(191) NULL,
                    `phone` VARCHAR(100) NULL,
                    `message` TEXT NOT NULL,
                    `is_read` TINYINT(1) DEFAULT 0,
                    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS `reviews` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `product_id` INT NULL,
                    `author_name` VARCHAR(191) NOT NULL,
                    `customer_name` VARCHAR(191) NULL,
                    `rating` INT NOT NULL DEFAULT 5,
                    `review_text` TEXT NULL,
                    `comment` TEXT NULL,
                    `district_name` VARCHAR(100) DEFAULT 'Dhaka',
                    `is_approved` TINYINT(1) DEFAULT 1,
                    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS `blog_posts` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `title` VARCHAR(191) NOT NULL,
                    `slug` VARCHAR(191) UNIQUE NOT NULL,
                    `category` VARCHAR(100) DEFAULT 'Buying Guide',
                    `author` VARCHAR(100) DEFAULT 'OnlineBdMart Team',
                    `summary` TEXT NULL,
                    `excerpt` TEXT NULL,
                    `content` LONGTEXT NULL,
                    `image_path` VARCHAR(255) DEFAULT 'images/hero/hero-1.jpg',
                    `meta_title` VARCHAR(191) NULL,
                    `meta_description` TEXT NULL,
                    `meta_keywords` TEXT NULL,
                    `is_published` TINYINT(1) DEFAULT 1,
                    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS `wholesale_inquiries` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `business_name` VARCHAR(191) NOT NULL,
                    `contact_person` VARCHAR(191) NOT NULL,
                    `phone` VARCHAR(100) NOT NULL,
                    `whatsapp` VARCHAR(100) NULL,
                    `district` VARCHAR(100) NULL,
                    `estimated_monthly_quantity` VARCHAR(100) NULL,
                    `message` TEXT NULL,
                    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS `coupons` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `code` VARCHAR(50) UNIQUE NOT NULL,
                    `discount_type` VARCHAR(20) NOT NULL DEFAULT 'fixed',
                    `discount_value` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    `min_spend` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    `product_id` INT NULL,
                    `show_in_header` TINYINT(1) DEFAULT 1,
                    `header_banner_text` VARCHAR(255) NULL,
                    `expiry_date` DATE NULL,
                    `usage_limit` INT NULL,
                    `used_count` INT DEFAULT 0,
                    `is_active` TINYINT(1) DEFAULT 1,
                    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }

        // Add dynamic columns if existing tables lack them
        addColumnIfNotExists($pdo, 'categories', 'parent_id', $isSqlite ? 'INTEGER' : 'INT NULL');
        addColumnIfNotExists($pdo, 'categories', 'emoji', $isSqlite ? "TEXT DEFAULT '🛍️'" : "VARCHAR(50) DEFAULT '🛍️'");
        addColumnIfNotExists($pdo, 'categories', 'show_on_homepage', $isSqlite ? 'INTEGER DEFAULT 1' : 'TINYINT(1) DEFAULT 1');
        addColumnIfNotExists($pdo, 'categories', 'is_featured', $isSqlite ? 'INTEGER DEFAULT 1' : 'TINYINT(1) DEFAULT 1');
        addColumnIfNotExists($pdo, 'categories', 'display_order', $isSqlite ? 'INTEGER DEFAULT 1' : 'INT DEFAULT 1');
        addColumnIfNotExists($pdo, 'categories', 'is_active', $isSqlite ? 'INTEGER DEFAULT 1' : 'TINYINT(1) DEFAULT 1');

        addColumnIfNotExists($pdo, 'settings', 'key', $isSqlite ? 'TEXT' : 'VARCHAR(191) NULL');
        addColumnIfNotExists($pdo, 'settings', 'value', $isSqlite ? 'TEXT' : 'LONGTEXT NULL');
        addColumnIfNotExists($pdo, 'settings', 'setting_key', $isSqlite ? 'TEXT' : 'VARCHAR(191) NULL');
        addColumnIfNotExists($pdo, 'settings', 'setting_value', $isSqlite ? 'TEXT' : 'LONGTEXT NULL');

        addColumnIfNotExists($pdo, 'orders', 'customer_phone', $isSqlite ? 'TEXT' : 'VARCHAR(100) NULL');
        addColumnIfNotExists($pdo, 'orders', 'customer_email', $isSqlite ? 'TEXT' : 'VARCHAR(191) NULL');
        addColumnIfNotExists($pdo, 'orders', 'delivery_address', $isSqlite ? 'TEXT' : 'TEXT NULL');
        addColumnIfNotExists($pdo, 'orders', 'district_name', $isSqlite ? 'TEXT' : 'VARCHAR(100) NULL');
        addColumnIfNotExists($pdo, 'orders', 'delivery_cost', $isSqlite ? 'REAL DEFAULT 120.00' : 'DECIMAL(10,2) DEFAULT 120.00');
        addColumnIfNotExists($pdo, 'orders', 'total_amount', $isSqlite ? 'REAL DEFAULT 0.00' : 'DECIMAL(10,2) DEFAULT 0.00');
        addColumnIfNotExists($pdo, 'orders', 'order_number', $isSqlite ? 'TEXT' : 'VARCHAR(100) NULL');

        addColumnIfNotExists($pdo, 'products', 'subcategory_id', $isSqlite ? 'INTEGER' : 'INT NULL');
        addColumnIfNotExists($pdo, 'products', 'stock_quantity', $isSqlite ? 'INTEGER DEFAULT 50' : 'INT DEFAULT 50');
        addColumnIfNotExists($pdo, 'products', 'wholesale_moq', $isSqlite ? 'INTEGER DEFAULT 5' : 'INT DEFAULT 5');
        addColumnIfNotExists($pdo, 'products', 'gallery_images', $isSqlite ? 'TEXT' : 'TEXT NULL');
        addColumnIfNotExists($pdo, 'products', 'specifications', $isSqlite ? 'TEXT' : 'TEXT NULL');
        addColumnIfNotExists($pdo, 'products', 'why_buy_from_us', $isSqlite ? 'TEXT' : 'TEXT NULL');

        addColumnIfNotExists($pdo, 'admins', 'name', $isSqlite ? "TEXT DEFAULT 'Super Admin'" : "VARCHAR(191) DEFAULT 'Super Admin'");
        addColumnIfNotExists($pdo, 'admins', 'role', $isSqlite ? "TEXT DEFAULT 'superadmin'" : "VARCHAR(50) DEFAULT 'superadmin'");
        addColumnIfNotExists($pdo, 'admins', 'permissions', $isSqlite ? "TEXT DEFAULT 'all'" : "TEXT NULL");
        addColumnIfNotExists($pdo, 'admins', 'is_active', $isSqlite ? "INTEGER DEFAULT 1" : "TINYINT(1) DEFAULT 1");
        addColumnIfNotExists($pdo, 'admins', 'two_factor_enabled', $isSqlite ? "INTEGER DEFAULT 0" : "TINYINT(1) DEFAULT 0");
        addColumnIfNotExists($pdo, 'admins', 'two_factor_pin', $isSqlite ? "TEXT DEFAULT '123456'" : "VARCHAR(50) DEFAULT '123456'");
        addColumnIfNotExists($pdo, 'admins', 'google_2fa_enabled', $isSqlite ? "INTEGER DEFAULT 0" : "TINYINT(1) DEFAULT 0");
        addColumnIfNotExists($pdo, 'admins', 'google_2fa_secret', $isSqlite ? "TEXT DEFAULT 'JBSWY3DPEHPK3PXP'" : "VARCHAR(64) DEFAULT 'JBSWY3DPEHPK3PXP'");
        addColumnIfNotExists($pdo, 'admins', 'profile_photo', $isSqlite ? "TEXT DEFAULT 'uploads/admin/avatar.png'" : "VARCHAR(255) DEFAULT 'uploads/admin/avatar.png'");

        addColumnIfNotExists($pdo, 'suppliers', 'photo', $isSqlite ? "TEXT DEFAULT 'uploads/suppliers/supplier-default.jpg'" : "VARCHAR(255) DEFAULT 'uploads/suppliers/supplier-default.jpg'");
        addColumnIfNotExists($pdo, 'suppliers', 'supply_products', $isSqlite ? 'TEXT' : 'TEXT NULL');

        addColumnIfNotExists($pdo, 'order_items', 'product_image', $isSqlite ? 'TEXT NULL' : 'VARCHAR(255) NULL');
        addColumnIfNotExists($pdo, 'order_items', 'total_price', $isSqlite ? 'REAL DEFAULT 0.00' : 'DECIMAL(10,2) DEFAULT NULL');
        addColumnIfNotExists($pdo, 'order_items', 'is_wholesale', $isSqlite ? 'INTEGER DEFAULT 0' : 'TINYINT(1) DEFAULT 0');

        addColumnIfNotExists($pdo, 'orders', 'payment_number', $isSqlite ? 'TEXT' : 'VARCHAR(100) NULL');
        addColumnIfNotExists($pdo, 'orders', 'transaction_id', $isSqlite ? 'TEXT' : 'VARCHAR(100) NULL');
        addColumnIfNotExists($pdo, 'orders', 'coupon_code', $isSqlite ? 'TEXT' : 'VARCHAR(50) NULL');
        addColumnIfNotExists($pdo, 'orders', 'discount_amount', $isSqlite ? 'REAL DEFAULT 0.00' : 'DECIMAL(10,2) DEFAULT 0.00');
        addColumnIfNotExists($pdo, 'orders', 'upazila', $isSqlite ? "TEXT DEFAULT ''" : "VARCHAR(100) DEFAULT ''");
        addColumnIfNotExists($pdo, 'orders', 'post_office', $isSqlite ? "TEXT DEFAULT ''" : "VARCHAR(100) DEFAULT ''");
        addColumnIfNotExists($pdo, 'orders', 'country', $isSqlite ? "TEXT DEFAULT 'Bangladesh'" : "VARCHAR(100) DEFAULT 'Bangladesh'");

        addColumnIfNotExists($pdo, 'products', 'colors', $isSqlite ? 'TEXT' : 'TEXT NULL');
        addColumnIfNotExists($pdo, 'products', 'sizes', $isSqlite ? 'TEXT' : 'TEXT NULL');

        addColumnIfNotExists($pdo, 'order_items', 'color', $isSqlite ? 'TEXT NULL' : 'VARCHAR(100) NULL');
        addColumnIfNotExists($pdo, 'order_items', 'size', $isSqlite ? 'TEXT NULL' : 'VARCHAR(100) NULL');

        addColumnIfNotExists($pdo, 'coupons', 'show_in_header', $isSqlite ? 'INTEGER DEFAULT 1' : 'TINYINT(1) DEFAULT 1');
        addColumnIfNotExists($pdo, 'coupons', 'header_banner_text', $isSqlite ? 'TEXT NULL' : 'VARCHAR(255) NULL');
        addColumnIfNotExists($pdo, 'coupons', 'product_id', $isSqlite ? 'INTEGER NULL' : 'INT NULL');
        addColumnIfNotExists($pdo, 'coupons', 'min_spend', $isSqlite ? 'REAL DEFAULT 0.00' : 'DECIMAL(10,2) DEFAULT 0.00');

        addColumnIfNotExists($pdo, 'users', 'upazila', $isSqlite ? "TEXT DEFAULT ''" : "VARCHAR(100) DEFAULT ''");
        addColumnIfNotExists($pdo, 'users', 'post_office', $isSqlite ? "TEXT DEFAULT ''" : "VARCHAR(100) DEFAULT ''");
        addColumnIfNotExists($pdo, 'users', 'country', $isSqlite ? "TEXT DEFAULT 'Bangladesh'" : "VARCHAR(100) DEFAULT 'Bangladesh'");
        addColumnIfNotExists($pdo, 'users', 'address', $isSqlite ? 'TEXT' : 'TEXT NULL');
        addColumnIfNotExists($pdo, 'users', 'district', $isSqlite ? "TEXT DEFAULT 'Dhaka'" : "VARCHAR(100) DEFAULT 'Dhaka'");
        addColumnIfNotExists($pdo, 'users', 'is_active', $isSqlite ? 'INTEGER DEFAULT 1' : 'TINYINT(1) DEFAULT 1');

        // Seed default categories if empty
        $catCount = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        if ($catCount === 0) {
            $categoriesData = [
                [1, null, 'Watches', 'watches', '⌚', 'fa-clock', 1],
                [2, null, 'Smart Gadgets', 'smart-gadgets', '📱', 'fa-mobile-screen-button', 2],
                [3, null, 'Leather Wallets', 'leather-wallets', '👛', 'fa-wallet', 3],
                [4, null, 'Luxury Bags', 'luxury-bags', '👜', 'fa-bag-shopping', 4],
                [5, null, 'Sunglasses', 'sunglasses', '🕶️', 'fa-glasses', 5],
                [6, null, 'Accessories & Belts', 'accessories-belts', '👔', 'fa-gem', 6],
                [7, 1, 'Chronograph Watches', 'chronograph-watches', '⏱️', 'fa-clock', 1],
                [8, 1, 'Automatic Mechanical', 'automatic-mechanical', '⚙️', 'fa-gear', 2],
                [9, 3, 'Full Grain Leather Wallets', 'full-grain-wallets', '💼', 'fa-wallet', 1],
                [10, 4, 'Executive Handbags', 'executive-handbags', '👝', 'fa-briefcase', 1],
            ];
            $cStmt = $pdo->prepare("INSERT INTO categories (id, parent_id, name, slug, emoji, icon, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($categoriesData as $c) {
                $cStmt->execute($c);
            }
        }

        // Seed 64 districts if empty
        $distCount = (int)$pdo->query("SELECT COUNT(*) FROM districts")->fetchColumn();
        if ($distCount === 0) {
            $districtsData = [
                ['Dhaka', 'Dhaka', 80.00, '1-2 days'],
                ['Tangail', 'Dhaka', 50.00, '24 hours'],
                ['Gazipur', 'Dhaka', 80.00, '1-2 days'],
                ['Narayanganj', 'Dhaka', 80.00, '1-2 days'],
                ['Chittagong', 'Chittagong', 130.00, '2-3 days'],
                ['Sylhet', 'Sylhet', 130.00, '2-3 days'],
                ['Rajshahi', 'Rajshahi', 130.00, '2-4 days'],
                ['Khulna', 'Khulna', 130.00, '2-4 days'],
                ['Barisal', 'Barisal', 130.00, '2-4 days'],
                ['Rangpur', 'Rangpur', 130.00, '2-4 days'],
                ['Mymensingh', 'Mymensingh', 100.00, '2-3 days'],
                ['Comilla', 'Chittagong', 120.00, '2-3 days'],
                ['Bogra', 'Rajshahi', 120.00, '2-3 days'],
                ['Jessore', 'Khulna', 120.00, '2-3 days'],
                ['Cox\'s Bazar', 'Chittagong', 130.00, '2-4 days'],
                ['Narsingdi', 'Dhaka', 100.00, '2-3 days'],
                ['Faridpur', 'Dhaka', 120.00, '2-3 days'],
                ['Kushtia', 'Khulna', 120.00, '2-3 days'],
                ['Pabna', 'Rajshahi', 120.00, '2-3 days'],
                ['Dinajpur', 'Rangpur', 130.00, '2-4 days'],
                ['Sirajganj', 'Rajshahi', 100.00, '2-3 days'],
                ['Jamalpur', 'Mymensingh', 100.00, '2-3 days'],
                ['Brahmanbaria', 'Chittagong', 120.00, '2-3 days'],
                ['Noakhali', 'Chittagong', 120.00, '2-3 days'],
                ['Feni', 'Chittagong', 120.00, '2-3 days'],
                ['Manikganj', 'Dhaka', 100.00, '2-3 days'],
                ['Munshiganj', 'Dhaka', 100.00, '2-3 days'],
                ['Kishoreganj', 'Dhaka', 100.00, '2-3 days'],
                ['Netrokona', 'Mymensingh', 120.00, '2-3 days'],
                ['Sherpur', 'Mymensingh', 120.00, '2-3 days'],
                ['Habiganj', 'Sylhet', 130.00, '2-3 days'],
                ['Moulvibazar', 'Sylhet', 130.00, '2-3 days'],
                ['Sunamganj', 'Sylhet', 130.00, '2-4 days'],
                ['Natore', 'Rajshahi', 120.00, '2-3 days'],
                ['Naogaon', 'Rajshahi', 120.00, '2-3 days'],
                ['Chapainawabganj', 'Rajshahi', 130.00, '2-4 days'],
                ['Joypurhat', 'Rajshahi', 130.00, '2-4 days'],
                ['Kurigram', 'Rangpur', 130.00, '2-4 days'],
                ['Gaibandha', 'Rangpur', 130.00, '2-4 days'],
                ['Lalmonirhat', 'Rangpur', 130.00, '2-4 days'],
                ['Nilphamari', 'Rangpur', 130.00, '2-4 days'],
                ['Panchagarh', 'Rangpur', 140.00, '3-5 days'],
                ['Thakurgaon', 'Rangpur', 140.00, '3-5 days'],
                ['Satkhira', 'Khulna', 130.00, '2-4 days'],
                ['Bagerhat', 'Khulna', 130.00, '2-4 days'],
                ['Jhenaidah', 'Khulna', 120.00, '2-3 days'],
                ['Magura', 'Khulna', 120.00, '2-3 days'],
                ['Narail', 'Khulna', 120.00, '2-3 days'],
                ['Chuadanga', 'Khulna', 130.00, '2-4 days'],
                ['Meherpur', 'Khulna', 130.00, '2-4 days'],
                ['Bhola', 'Barisal', 140.00, '3-5 days'],
                ['Jhalokati', 'Barisal', 130.00, '2-4 days'],
                ['Pirojpur', 'Barisal', 130.00, '2-4 days'],
                ['Patuakhali', 'Barisal', 140.00, '3-5 days'],
                ['Barguna', 'Barisal', 140.00, '3-5 days'],
                ['Chandpur', 'Chittagong', 120.00, '2-3 days'],
                ['Lakshmipur', 'Chittagong', 120.00, '2-3 days'],
                ['Bandarban', 'Chittagong', 140.00, '3-5 days'],
                ['Rangamati', 'Chittagong', 140.00, '3-5 days'],
                ['Khagrachhari', 'Chittagong', 140.00, '3-5 days'],
                ['Gopalganj', 'Dhaka', 120.00, '2-3 days'],
                ['Madaripur', 'Dhaka', 120.00, '2-3 days'],
                ['Rajbari', 'Dhaka', 120.00, '2-3 days'],
                ['Shariatpur', 'Dhaka', 120.00, '2-3 days'],
            ];
            $dStmt = $pdo->prepare("INSERT INTO districts (name, division_name, delivery_fee, estimated_days) VALUES (?, ?, ?, ?)");
            foreach ($districtsData as $d) {
                $dStmt->execute($d);
            }
        }
    } catch (Exception $e) {}
}

// 2. Universal PDO Database Connection with SQLite NOW() support
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
        ensureTablesExist($pdo);
        return $pdo;
    } catch (Exception $e) {
        // Fallback to SQLite database
        $sqlitePath = __DIR__ . '/../database/database.sqlite';
        try {
            $pdo = new PDO("sqlite:{$sqlitePath}", null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // Register NOW() function in SQLite so any NOW() call works natively
            if (method_exists($pdo, 'sqliteCreateFunction')) {
                $pdo->sqliteCreateFunction('NOW', function() {
                    return date('Y-m-d H:i:s');
                });
            }

            ensureTablesExist($pdo);
            return $pdo;
        } catch (Exception $ex) {
            die("Database Connection Error: " . $e->getMessage() . "<br>Please check your database credentials in config/database.php or .env file.");
        }
    }
}

// 3. Settings Helper (Bulletproof against both key/setting_key column naming)
function getSetting($key, $default = '') {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM settings WHERE `key` = ? OR setting_key = ? LIMIT 1");
        $stmt->execute([$key, $key]);
        $row = $stmt->fetch();
        if ($row) {
            return !empty($row['value']) ? $row['value'] : (!empty($row['setting_value']) ? $row['setting_value'] : $default);
        }
        return $default;
    } catch (Exception $e) {
        return $default;
    }
}

function saveSetting($key, $value) {
    try {
        $db = getDB();
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        $now = date('Y-m-d H:i:s');
        if ($driver === 'sqlite') {
            $stmt = $db->prepare("DELETE FROM settings WHERE `key` = ? OR setting_key = ?");
            $stmt->execute([$key, $key]);
            $ins = $db->prepare("INSERT INTO settings (`key`, setting_key, `value`, setting_value, updated_at) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$key, $key, $value, $value, $now]);
        } else {
            $stmt = $db->prepare("INSERT INTO settings (`key`, setting_key, `value`, setting_value, updated_at) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), setting_value = VALUES(setting_value), updated_at = VALUES(updated_at)");
            $stmt->execute([$key, $key, $value, $value, $now]);
        }
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function getAllSettings() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $k = !empty($row['key']) ? $row['key'] : (!empty($row['setting_key']) ? $row['setting_key'] : '');
            $v = isset($row['value']) ? $row['value'] : (isset($row['setting_value']) ? $row['setting_value'] : '');
            if ($k) $settings[$k] = $v;
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

// 5. Admin Auth Helpers
function isAdminLoggedIn() {
    return !empty($_SESSION['admin_id']) || !empty($_SESSION['admin_logged_in']);
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// 6. Universal Customer Behavior & Visit Tracker
function trackCustomerVisit() {
    // Only track public frontend views, not static assets or admin ajax
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    if (str_contains($uri, '/admin-panel/') || str_ends_with($uri, '.js') || str_ends_with($uri, '.css') || str_ends_with($uri, '.png') || str_ends_with($uri, '.jpg')) {
        return;
    }

    try {
        $db = getDB();
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (str_contains($ip, ',')) {
            $ip = trim(explode(',', $ip)[0]);
        }
        $sessId = session_id() ?: 'guest_' . md5($ip);
        $page = $uri;
        $ref = $_SERVER['HTTP_REFERER'] ?? 'Direct Visit / Bookmark';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Mobile';
        
        $dev = 'Desktop';
        if (preg_match('/(android|iphone|ipad|mobile|touch)/i', $ua)) {
            $dev = (preg_match('/(ipad|tablet)/i', $ua)) ? 'Tablet' : 'Mobile';
        }

        // District / Location determination
        $district = 'Bangladesh';
        if (str_contains(strtolower($page), 'tangail')) $district = 'Tangail';
        elseif (str_contains(strtolower($page), 'dhaka')) $district = 'Dhaka';
        elseif (str_contains(strtolower($page), 'chittagong')) $district = 'Chittagong';
        elseif (str_contains(strtolower($page), 'sylhet')) $district = 'Sylhet';

        $now = date('Y-m-d H:i:s');
        $stmt = $db->prepare("INSERT INTO customer_visits (ip_address, session_id, district, referrer, page_url, user_agent, device_type, visited_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$ip, $sessId, $district, $ref, $page, $ua, $dev, $now]);
    } catch (Exception $e) {}
}

// 7. Universal Pure PHP Google Authenticator (TOTP RFC 6238 Engine)
if (!class_exists('GoogleAuthenticator')) {
    class GoogleAuthenticator {
        private static $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

        public static function generateSecret($length = 16) {
            $secret = '';
            for ($i = 0; $i < $length; $i++) {
                $secret .= self::$base32Chars[random_int(0, 31)];
            }
            return $secret;
        }

        public static function getCode($secret, $timeSlice = null) {
            if ($timeSlice === null) {
                $timeSlice = floor(time() / 30);
            }
            $secretKey = self::base32Decode($secret);
            if (empty($secretKey)) return '000000';
            $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
            $hmac = hash_hmac('sha1', $time, $secretKey, true);
            $offset = ord(substr($hmac, -1)) & 0x0F;
            $hashpart = substr($hmac, $offset, 4);
            $value = unpack('N', $hashpart);
            $value = $value[1] & 0x7FFFFFFF;
            $modulo = pow(10, 6);
            return str_pad($value % $modulo, 6, '0', STR_PAD_LEFT);
        }

        public static function verifyCode($secret, $code, $discrepancy = 2) {
            $code = trim((string)$code);
            if (strlen($code) !== 6 && strlen($code) !== 4) return false;
            
            $currentTimeSlice = floor(time() / 30);
            for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
                $calculatedCode = self::getCode($secret, $currentTimeSlice + $i);
                if (hash_equals((string)$calculatedCode, (string)$code)) {
                    return true;
                }
            }
            return false;
        }

        public static function getQrCodeUrl($name, $secret, $issuer = 'OnlineBdMart') {
            $encodedIssuer = rawurlencode($issuer);
            $encodedName = rawurlencode($name);
            $otpauth = "otpauth://totp/{$encodedIssuer}:{$encodedName}?secret={$secret}&issuer={$encodedIssuer}";
            return "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" . urlencode($otpauth);
        }

        private static function base32Decode($secret) {
            if (empty($secret)) return '';
            $base32chars = self::$base32Chars;
            $base32charsFlipped = array_flip(str_split($base32chars));
            $secret = strtoupper(str_replace('=', '', $secret));
            $secret = str_split($secret);
            $binaryString = '';
            for ($i = 0; $i < count($secret); $i = $i + 8) {
                $x = '';
                if (!isset($base32charsFlipped[$secret[$i]])) return false;
                for ($j = 0; $j < 8; $j++) {
                    if (isset($secret[$i + $j]) && isset($base32charsFlipped[$secret[$i + $j]])) {
                        $x .= str_pad(base_convert($base32charsFlipped[$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
                    }
                }
                $eightBits = str_split($x, 8);
                for ($z = 0; $z < count($eightBits); $z++) {
                    if (strlen($eightBits[$z]) === 8) {
                        $binaryString .= chr(base_convert($eightBits[$z], 2, 10));
                    }
                }
            }
            return $binaryString;
        }
    }
}
