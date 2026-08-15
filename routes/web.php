<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\WholesaleController as AdminWholesaleController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryPageController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\DealsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WholesaleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - OnlineBdMart E-Commerce
| Header: Home | Shop | Wholesale | Categories | Deals | Blog | Contact
|--------------------------------------------------------------------------
*/

// Main Navigation Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/wholesale', [WholesaleController::class, 'index'])->name('wholesale');
Route::post('/wholesale/inquiry', [WholesaleController::class, 'submitInquiry'])->name('wholesale.inquiry');
Route::get('/categories', [CategoryPageController::class, 'index'])->name('categories');
Route::get('/deals', [DealsController::class, 'index'])->name('deals');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');

// Product & Search
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');
Route::get('/quick-view/{id}', [ProductController::class, 'quickView'])->name('product.quick_view');
Route::post('/product/{id}/review', [ProductController::class, 'storeReview'])->name('product.review');
Route::get('/search-suggestions', [ShopController::class, 'searchSuggestions'])->name('search.suggestions');

// Cart & Checkout
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon'])->name('cart.apply_coupon');
Route::post('/cart/remove-coupon', [CartController::class, 'removeCoupon'])->name('cart.remove_coupon');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');

// Order Confirmation & Live Order Tracking System
Route::get('/order-success/{id}', [OrderController::class, 'success'])->name('order.success');
Route::get('/track-order', [OrderController::class, 'track'])->name('order.track');
Route::get('/order/{id}/invoice', [OrderController::class, 'invoice'])->name('order.invoice');

// Customer Sign In / Sign Up Authentication
Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [CustomerAuthController::class, 'login'])->name('login.submit');
Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [CustomerAuthController::class, 'register'])->name('register.submit');
Route::get('/account', [CustomerAuthController::class, 'account'])->name('account');
Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
Route::get('/logout', [CustomerAuthController::class, 'logout'])->name('logout.get');

// Redirect /admin to /admin-panel
Route::get('/admin', fn() => redirect()->route('admin.dashboard'));
Route::get('/admin/login', fn() => redirect()->route('admin.login'));

// Custom Admin Portal URL Route Group (/admin-panel)
Route::prefix('admin-panel')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::get('/logout', [AdminAuthController::class, 'logout'])->name('logout.get');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard.index');

        // Products
        Route::resource('products', AdminProductController::class)->except(['create', 'show', 'edit']);
        Route::post('products/{id}/set-primary-image', [AdminProductController::class, 'setPrimaryImage'])->name('products.set_primary_image');
        Route::delete('products/images/{id}', [AdminProductController::class, 'deleteImage'])->name('products.delete_image');

        // Wholesale
        Route::get('wholesale', [AdminWholesaleController::class, 'index'])->name('wholesale.index');
        Route::post('wholesale/toggle/{id}', [AdminWholesaleController::class, 'toggleProduct'])->name('wholesale.toggle');

        // Categories
        Route::resource('categories', AdminCategoryController::class)->except(['create', 'show', 'edit']);

        // Orders
        Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update_status');
        Route::post('orders/export/csv', [AdminOrderController::class, 'exportCsv'])->name('orders.export_csv');

        // Banners
        Route::resource('banners', AdminBannerController::class)->except(['create', 'show', 'edit']);
        Route::post('banners/{id}/toggle', [AdminBannerController::class, 'toggle'])->name('banners.toggle');

        // Blogs
        Route::resource('blogs', \App\Http\Controllers\Admin\BlogController::class)->except(['create', 'show', 'edit']);

        // Coupons
        Route::resource('coupons', AdminCouponController::class)->except(['create', 'show', 'edit']);

        // Settings
        Route::get('settings', [AdminSettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [AdminSettingController::class, 'update'])->name('settings.update');
    });
});
