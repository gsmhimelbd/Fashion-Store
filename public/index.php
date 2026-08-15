<?php
/**
 * OnlineBdMart - Universal PHP / Laravel Web Dispatcher
 * Compatible with cPanel, DirectAdmin, Apache, LiteSpeed, Nginx & Laravel
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. If Laravel Composer vendor exists and is valid, use Laravel Kernel
if (file_exists(__DIR__ . '/../vendor/autoload.php') && file_exists(__DIR__ . '/../bootstrap/app.php')) {
    try {
        require __DIR__ . '/../vendor/autoload.php';
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        if (is_object($app) && method_exists($app, 'handleRequest')) {
            $app->handleRequest(\Illuminate\Http\Request::capture());
            exit;
        }
    } catch (\Throwable $e) {
        // Fallback to standalone dispatcher if Laravel vendor is incomplete
    }
}

// 2. Standalone High-Performance Dispatcher for Shared cPanel Hosting
require_once __DIR__ . '/../config/database.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = '/' . trim($uri, '/');

// Static / download assets
if ($uri === '/OnlineBdMart-cPanel-Ready.zip' || $uri === '/OnlineBdMart-Laravel.zip') {
    $zipFile = __DIR__ . '/../OnlineBdMart-cPanel-Ready.zip';
    if (file_exists($zipFile)) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="OnlineBdMart-cPanel-Ready.zip"');
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);
        exit;
    }
}

// Route mapping
if ($uri === '/' || $uri === '/index.php' || $uri === '/home') {
    require __DIR__ . '/../index.php';
    exit;
}

if ($uri === '/shop' || $uri === '/shop.php') {
    require __DIR__ . '/../shop.php';
    exit;
}

if (str_starts_with($uri, '/product/')) {
    $slug = substr($uri, strlen('/product/'));
    $_GET['slug'] = $slug;
    require __DIR__ . '/../product.php';
    exit;
}
if ($uri === '/product.php') {
    require __DIR__ . '/../product.php';
    exit;
}

if ($uri === '/wholesale' || $uri === '/wholesale.php') {
    require __DIR__ . '/../wholesale.php';
    exit;
}

if ($uri === '/categories' || $uri === '/categories.php') {
    require __DIR__ . '/../categories.php';
    exit;
}

if ($uri === '/deals' || $uri === '/deals.php') {
    require __DIR__ . '/../deals.php';
    exit;
}

if ($uri === '/blog' || $uri === '/blog.php') {
    require __DIR__ . '/../blog.php';
    exit;
}

if ($uri === '/contact' || $uri === '/contact.php') {
    require __DIR__ . '/../contact.php';
    exit;
}

if ($uri === '/cart' || $uri === '/cart.php' || $uri === '/cart/add' || $uri === '/cart/update' || $uri === '/cart/remove') {
    require __DIR__ . '/../cart.php';
    exit;
}

if ($uri === '/checkout' || $uri === '/checkout.php') {
    require __DIR__ . '/../checkout.php';
    exit;
}

if (str_starts_with($uri, '/order-success/')) {
    $id = substr($uri, strlen('/order-success/'));
    $_GET['id'] = $id;
    require __DIR__ . '/../order-success.php';
    exit;
}
if ($uri === '/order-success' || $uri === '/order-success.php') {
    require __DIR__ . '/../order-success.php';
    exit;
}

if (str_starts_with($uri, '/order/') && str_ends_with($uri, '/invoice')) {
    $parts = explode('/', trim($uri, '/'));
    $_GET['id'] = $parts[1] ?? 0;
    require __DIR__ . '/../invoice.php';
    exit;
}
if ($uri === '/invoice' || $uri === '/invoice.php') {
    require __DIR__ . '/../invoice.php';
    exit;
}

if ($uri === '/track-order' || $uri === '/track-order.php') {
    require __DIR__ . '/../track-order.php';
    exit;
}

if ($uri === '/login' || $uri === '/login.php') {
    require __DIR__ . '/../login.php';
    exit;
}

if ($uri === '/register' || $uri === '/register.php') {
    require __DIR__ . '/../register.php';
    exit;
}

if ($uri === '/account' || $uri === '/account.php') {
    require __DIR__ . '/../account.php';
    exit;
}

if ($uri === '/logout' || $uri === '/logout.php') {
    require __DIR__ . '/../logout.php';
    exit;
}

// Admin Routes Mapping
if ($uri === '/admin' || $uri === '/admin/' || $uri === '/admin-panel' || $uri === '/admin-panel/' || $uri === '/admin-panel/dashboard' || $uri === '/admin-panel/index.php') {
    require __DIR__ . '/../admin-panel/index.php';
    exit;
}

if ($uri === '/admin-panel/login' || $uri === '/admin-panel/login.php') {
    require __DIR__ . '/../admin-panel/login.php';
    exit;
}

if ($uri === '/admin-panel/verify-2fa' || $uri === '/admin-panel/verify-2fa.php') {
    require __DIR__ . '/../admin-panel/verify-2fa.php';
    exit;
}

if ($uri === '/admin-panel/logout' || $uri === '/admin-panel/logout.php') {
    require __DIR__ . '/../admin-panel/logout.php';
    exit;
}

if ($uri === '/admin-panel/products' || $uri === '/admin-panel/products.php') {
    require __DIR__ . '/../admin-panel/products.php';
    exit;
}

if ($uri === '/admin-panel/wholesale' || $uri === '/admin-panel/wholesale.php') {
    require __DIR__ . '/../admin-panel/wholesale.php';
    exit;
}

if ($uri === '/admin-panel/orders' || $uri === '/admin-panel/orders.php') {
    require __DIR__ . '/../admin-panel/orders.php';
    exit;
}

if ($uri === '/admin-panel/delivery' || $uri === '/admin-panel/delivery.php') {
    require __DIR__ . '/../admin-panel/delivery.php';
    exit;
}

if ($uri === '/admin-panel/analytics' || $uri === '/admin-panel/analytics.php') {
    require __DIR__ . '/../admin-panel/analytics.php';
    exit;
}

if ($uri === '/admin-panel/smtp' || $uri === '/admin-panel/smtp.php') {
    require __DIR__ . '/../admin-panel/smtp.php';
    exit;
}

if ($uri === '/admin-panel/banners' || $uri === '/admin-panel/banners.php') {
    require __DIR__ . '/../admin-panel/banners.php';
    exit;
}

if ($uri === '/admin-panel/payments' || $uri === '/admin-panel/payments.php') {
    require __DIR__ . '/../admin-panel/payments.php';
    exit;
}

if ($uri === '/admin-panel/customers' || $uri === '/admin-panel/customers.php') {
    require __DIR__ . '/../admin-panel/customers.php';
    exit;
}

if ($uri === '/admin-panel/suppliers' || $uri === '/admin-panel/suppliers.php') {
    require __DIR__ . '/../admin-panel/suppliers.php';
    exit;
}

if ($uri === '/admin-panel/messages' || $uri === '/admin-panel/messages.php') {
    require __DIR__ . '/../admin-panel/messages.php';
    exit;
}

if ($uri === '/admin-panel/categories' || $uri === '/admin-panel/categories.php') {
    require __DIR__ . '/../admin-panel/categories.php';
    exit;
}

if ($uri === '/admin-panel/deals' || $uri === '/admin-panel/deals.php') {
    require __DIR__ . '/../admin-panel/deals.php';
    exit;
}

if ($uri === '/admin-panel/blog' || $uri === '/admin-panel/blog.php' || $uri === '/admin-panel/blogs') {
    require __DIR__ . '/../admin-panel/blog.php';
    exit;
}

if ($uri === '/admin-panel/reviews' || $uri === '/admin-panel/reviews.php') {
    require __DIR__ . '/../admin-panel/reviews.php';
    exit;
}

if ($uri === '/admin-panel/whatsapp' || $uri === '/admin-panel/whatsapp.php') {
    require __DIR__ . '/../admin-panel/whatsapp.php';
    exit;
}

if ($uri === '/admin-panel/telegram' || $uri === '/admin-panel/telegram.php') {
    require __DIR__ . '/../admin-panel/telegram.php';
    exit;
}

if ($uri === '/admin-panel/facebook-pixel' || $uri === '/admin-panel/pixel' || $uri === '/admin-panel/facebook-pixel.php') {
    require __DIR__ . '/../admin-panel/facebook-pixel.php';
    exit;
}

if ($uri === '/admin-panel/colors' || $uri === '/admin-panel/colors.php') {
    require __DIR__ . '/../admin-panel/colors.php';
    exit;
}

if ($uri === '/admin-panel/settings' || $uri === '/admin-panel/settings.php') {
    require __DIR__ . '/../admin-panel/settings.php';
    exit;
}

if ($uri === '/admin-panel/staff' || $uri === '/admin-panel/staff.php') {
    require __DIR__ . '/../admin-panel/staff.php';
    exit;
}

if ($uri === '/admin-panel/otp-system' || $uri === '/admin-panel/otp' || $uri === '/admin-panel/otp-system.php') {
    require __DIR__ . '/../admin-panel/otp-system.php';
    exit;
}

if ($uri === '/admin-panel/profile' || $uri === '/admin-panel/profile.php') {
    require __DIR__ . '/../admin-panel/profile.php';
    exit;
}

if ($uri === '/admin-panel/seo' || $uri === '/admin-panel/seo.php') {
    require __DIR__ . '/../admin-panel/seo.php';
    exit;
}

// Fallback to Home
require __DIR__ . '/../index.php';
