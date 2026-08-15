<?php
/**
 * OnlineBdMart - Universal Database Configuration
 * Auto-detects MySQL from .env or config, with SQLite fallback
 * Self-healing schema: auto-creates missing tables if needed
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

// Auto ensure schema exists (creates missing tables on the fly)
function ensureTablesExist($pdo) {
    static $checked = false;
    if ($checked) return;
    $checked = true;

    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS admins (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(191) DEFAULT 'Super Admin',
                username VARCHAR(191) UNIQUE,
                email VARCHAR(191) UNIQUE,
                password VARCHAR(191),
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(191) NOT NULL,
                slug VARCHAR(191) UNIQUE NOT NULL,
                icon VARCHAR(100) DEFAULT 'fa-tag',
                image_path VARCHAR(191) NULL,
                description TEXT NULL,
                is_active TINYINT(1) DEFAULT 1,
                display_order INT DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                category_id INT NULL,
                name VARCHAR(191) NOT NULL,
                slug VARCHAR(191) UNIQUE NOT NULL,
                sku VARCHAR(100) NULL,
                price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                sale_price DECIMAL(10,2) NULL,
                wholesale_price DECIMAL(10,2) NULL,
                wholesale_moq INT DEFAULT 5,
                wholesale_min_qty INT DEFAULT 5,
                is_wholesale TINYINT(1) DEFAULT 1,
                stock INT DEFAULT 50,
                stock_quantity INT DEFAULT 50,
                is_featured TINYINT(1) DEFAULT 1,
                is_active TINYINT(1) DEFAULT 1,
                image_path VARCHAR(191) DEFAULT 'images/products/watch-1.jpg',
                short_description TEXT NULL,
                description TEXT NULL,
                reviews_count INT DEFAULT 45,
                rating DECIMAL(3,1) DEFAULT 4.9,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS banners (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(191) NOT NULL,
                subtitle VARCHAR(191) NULL,
                badge_text VARCHAR(100) NULL,
                button_text VARCHAR(100) DEFAULT 'Shop Now',
                button_url VARCHAR(191) DEFAULT 'shop.php',
                image_path VARCHAR(191) NOT NULL,
                display_order INT DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS districts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                division_name VARCHAR(100) NOT NULL,
                delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 120.00,
                estimated_days VARCHAR(50) DEFAULT '2-4 days',
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_number VARCHAR(100) NULL,
                customer_name VARCHAR(191) NOT NULL,
                customer_email VARCHAR(191) NULL,
                customer_phone VARCHAR(100) NOT NULL,
                phone VARCHAR(100) NULL,
                whatsapp VARCHAR(100) NULL,
                delivery_address TEXT NOT NULL,
                address TEXT NULL,
                district VARCHAR(100) NULL,
                district_name VARCHAR(100) NULL,
                upazila VARCHAR(100) NULL,
                subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                delivery_cost DECIMAL(10,2) NOT NULL DEFAULT 120.00,
                delivery_charge DECIMAL(10,2) NOT NULL DEFAULT 120.00,
                total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                grand_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                payment_method VARCHAR(50) DEFAULT 'cod',
                payment_number VARCHAR(100) NULL,
                transaction_id VARCHAR(100) NULL,
                status VARCHAR(50) DEFAULT 'pending',
                notes TEXT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS order_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_id INT NULL,
                product_name VARCHAR(191) NOT NULL,
                product_image VARCHAR(191) NULL,
                price DECIMAL(10,2) NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                total_price DECIMAL(10,2) NULL,
                is_wholesale TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS cart_abandonments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(191) NULL,
                customer_name VARCHAR(191) NULL,
                customer_phone VARCHAR(100) NULL,
                district_name VARCHAR(100) NULL,
                product_name VARCHAR(191) NULL,
                cart_value DECIMAL(10,2) DEFAULT 0.00,
                step VARCHAR(50) DEFAULT 'cart',
                recovered TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                `key` VARCHAR(191) UNIQUE NULL,
                setting_key VARCHAR(191) UNIQUE NULL,
                `value` TEXT NULL,
                setting_value TEXT NULL,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS suppliers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(191) NOT NULL,
                contact_person VARCHAR(191) NULL,
                phone VARCHAR(100) NULL,
                category VARCHAR(191) NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(191) NOT NULL,
                email VARCHAR(191) NULL,
                phone VARCHAR(100) NULL,
                message TEXT NOT NULL,
                is_read TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS reviews (
                id INT AUTO_INCREMENT PRIMARY KEY,
                author_name VARCHAR(191) NOT NULL,
                rating INT NOT NULL DEFAULT 5,
                review_text TEXT NOT NULL,
                district_name VARCHAR(100) DEFAULT 'Dhaka',
                is_approved TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS blog_posts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(191) NOT NULL,
                slug VARCHAR(191) UNIQUE NOT NULL,
                excerpt TEXT NULL,
                content LONGTEXT NULL,
                is_published TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS blogs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(191) NOT NULL,
                slug VARCHAR(191) UNIQUE NOT NULL,
                category VARCHAR(100) DEFAULT 'Buying Guide',
                author VARCHAR(100) DEFAULT 'OnlineBdMart Team',
                summary TEXT NULL,
                content LONGTEXT NULL,
                image_path VARCHAR(191) DEFAULT 'uploads/hero-banner-1.svg',
                is_published TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS wholesale_inquiries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                business_name VARCHAR(191) NOT NULL,
                contact_person VARCHAR(191) NOT NULL,
                phone VARCHAR(100) NOT NULL,
                whatsapp VARCHAR(100) NULL,
                district VARCHAR(100) NULL,
                estimated_monthly_quantity VARCHAR(100) NULL,
                message TEXT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // Seed Default Admin if missing
        $admCount = (int)$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
        if ($admCount === 0) {
            $hashed = password_hash('password', PASSWORD_BCRYPT);
            $pdo->prepare("INSERT INTO admins (name, username, email, password) VALUES ('Super Admin', 'admin', 'admin@fashionstore.com', ?)")->execute([$hashed]);
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
    } catch (Exception $e) {
        // Silently skip if DB permissions don't allow CREATE TABLE
    }
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
            ensureTablesExist($pdo);
            return $pdo;
        } catch (Exception $ex) {
            die("Database Connection Error: " . $e->getMessage() . "<br>Please check your database credentials in config/database.php or .env file.");
        }
    }
}

// 3. Settings Helper
function getSetting($key, $default = '') {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ? OR `key` = ? LIMIT 1");
        $stmt->execute([$key, $key]);
        $row = $stmt->fetch();
        if ($row) return $row['setting_value'] ?: $row['value'];
        return $default;
    } catch (Exception $e) {
        return $default;
    }
}

function getAllSettings() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $k = $row['setting_key'] ?: ($row['key'] ?? '');
            $v = $row['setting_value'] ?: ($row['value'] ?? '');
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

// 5. Admin Auth Helper
function isAdminLoggedIn() {
    return !empty($_SESSION['admin_id']) || !empty($_SESSION['admin_logged_in']);
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
