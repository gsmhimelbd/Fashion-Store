-- =========================================================
-- OnlineBdMart - Complete cPanel Ready MySQL Database Dump
-- Compatible with MySQL 5.7, 8.0, 8.4+, MariaDB & cPanel phpMyAdmin
-- =========================================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+06:00";

-- --------------------------------------------------------
-- Table structure for `admins`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_username_unique` (`username`),
  UNIQUE KEY `admins_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@onlinebdmart.com', '$2y$12$e6bF00L22s0sZ3p1uI4wIe6jN6Z1.6iB0/Hk/eTj9EFe6U3oNqJ8a', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

-- --------------------------------------------------------
-- Table structure for `users` (Customers)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_phone_unique` (`phone`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `phone`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'Tanvir Ahmed', '01711223344', 'tanvir@example.com', '$2y$12$e6bF00L22s0sZ3p1uI4wIe6jN6Z1.6iB0/Hk/eTj9EFe6U3oNqJ8a', '2026-08-10 10:00:00', '2026-08-10 10:00:00');

-- --------------------------------------------------------
-- Table structure for `settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
('store_name', 'OnlineBdMart', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('store_tagline', 'Upgrade Your World with Latest Accessories & Gadgets', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('store_logo', 'images/logo.svg', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('contact_phone', '01775153740', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('contact_email', 'support@onlinebdmart.com', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('whatsapp_number', '01775153740', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('delivery_charge_tangail', '50', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('delivery_charge_dhaka', '80', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('delivery_charge_other', '150', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('free_delivery_threshold', '2000', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('bkash_number', '01775153740', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('bkash_type', 'Personal', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('nagad_number', '01775153740', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('nagad_type', 'Personal', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('rocket_number', '01775153740', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('facebook_page', 'https://facebook.com', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('instagram_page', 'https://instagram.com', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('store_address', 'Main Road, Tangail Sadar, Tangail - 1900, Bangladesh', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('currency', '৳', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('currency_code', 'BDT', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('announcement_bar', 'Free Delivery Tangail ৳50 | Others ৳150 • Free Shipping above ৳2000', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('telegram_bot_token', '7891234567:AAHxyz_sample_token_onlinebdmart', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
('telegram_chat_id', '123456789', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

-- --------------------------------------------------------
-- Table structure for `categories`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`) VALUES
(1, 'Men Collection', 'men-collection', 'fa-person', 'Premium luxury watches, leather wallets, formal belts, silk ties, and modern accessories for men.'),
(2, 'Women Collection', 'women-collection', 'fa-person-dress', 'Designer handbags, pearl necklaces, sterling silver jewelry, sunglasses, and elegant accessories for women.'),
(3, 'New Arrivals', 'new-arrivals', 'fa-sparkles', 'The latest trends and hot trending fashion accessories of the season.'),
(4, 'Watches & Tech', 'watches-tech', 'fa-clock', 'Luxury chronograph timepieces and AMOLED smartwatches with premium build.'),
(5, 'Leather Goods', 'leather-goods', 'fa-wallet', 'Handcrafted full-grain leather wallets, travel backpacks, card holders, and belts.'),
(6, 'Jewelry & Fragrance', 'jewelry-fragrance', 'fa-gem', 'Long-lasting luxury Eau De Parfum and sterling silver crystal rings and pendants.');

-- --------------------------------------------------------
-- Table structure for `products`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `wholesale_price` decimal(10,2) DEFAULT NULL,
  `wholesale_min_qty` int(11) DEFAULT 5,
  `is_wholesale` tinyint(1) NOT NULL DEFAULT 1,
  `stock` int(11) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `rating` decimal(2,1) NOT NULL DEFAULT 5.0,
  `reviews_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `sku`, `short_description`, `description`, `price`, `sale_price`, `wholesale_price`, `wholesale_min_qty`, `is_wholesale`, `stock`, `is_featured`, `is_active`, `rating`, `reviews_count`) VALUES
(1, 4, 'Luxury Chronograph Sapphire Watch', 'luxury-chronograph-sapphire-watch', 'WAT-001', 'Precision Japanese quartz movement with scratch-resistant sapphire crystal glass.', 'Crafted from surgical 316L stainless steel with anti-reflective sapphire glass, stopwatch function, and 50M water resistance.', 3850.00, 3250.00, 2450.00, 5, 1, 25, 1, 1, 4.9, 38),
(2, 5, 'Handcrafted Full-Grain Leather Wallet', 'handcrafted-full-grain-leather-wallet', 'WAL-002', '100% genuine vintage brown cowhide leather with RFID blocking technology.', '8 Card slots, 2 cash compartments, and RFID blocking security for total protection.', 1450.00, 1190.00, 890.00, 5, 1, 40, 1, 1, 4.8, 64),
(3, 1, 'Aviator Polarized Titanium Sunglasses', 'aviator-polarized-titanium-sunglasses', 'SUN-003', 'Ultra-lightweight titanium alloy frame with HD polarized UV400 lenses.', 'Glare-free polarized lenses block 100% of UVA and UVB radiation with titanium comfort.', 1850.00, 1490.00, 1050.00, 5, 1, 30, 1, 1, 4.7, 42),
(4, 2, 'Minimalist Luxury Leather Handbag', 'minimalist-luxury-leather-handbag', 'BAG-004', 'Italian style premium calfskin leather handbag with detachable shoulder strap.', 'Soft Italian calfskin with dual top handles, adjustable crossbody strap, and gold-plated hardware.', 4200.00, 3650.00, 2800.00, 3, 1, 15, 1, 1, 5.0, 29),
(5, 5, 'Classic Reversible Leather Formal Belt', 'classic-reversible-leather-formal-belt', 'BLT-005', '2-in-1 Black & Brown reversible leather belt with rotating nickel buckle.', 'Twist buckle effortlessly to switch between Black and Brown full-grain cowhide leather.', 1100.00, 890.00, 650.00, 5, 1, 50, 0, 1, 4.6, 51),
(6, 4, 'Pro Ultra AMOLED Smartwatch', 'pro-ultra-amoled-smartwatch', 'SMW-006', '1.96-inch HD AMOLED display, Bluetooth calling, heart rate & SpO2 health tracking.', 'Ultra-bright display, 10-day battery life, 100+ sport modes, and IP68 waterproof design.', 3200.00, 2750.00, 2100.00, 5, 1, 20, 1, 1, 4.9, 77),
(7, 6, 'Freshwater Pearl Pendant Necklace', 'freshwater-pearl-pendant-necklace', 'JWL-007', 'Cultured natural freshwater pearl on an 18K gold plated hypoallergenic chain.', 'AAA Grade baroque pearl with 18K yellow gold vermeil chain in luxury velvet gift box.', 1650.00, 1350.00, 950.00, 5, 1, 18, 1, 1, 4.9, 35),
(8, 6, 'Eau De Parfum Noir Edition (100ml)', 'eau-de-parfum-noir-edition', 'PER-008', 'Sensual blend of rich bergamot, smoky amber, oud wood, and warm vanilla.', 'Enduring 25% oil concentration for 12+ hours projection and signature aroma.', 2800.00, 2290.00, 1650.00, 5, 1, 22, 1, 1, 4.9, 93),
(9, 5, 'Urban Water-Resistant Leather Backpack', 'urban-water-resistant-leather-backpack', 'BAG-009', 'Sleek commuter backpack with 15.6-inch padded laptop sleeve.', 'Weather-proof matte leather with anti-theft hidden pockets and ergonomic back support.', 2950.00, 2450.00, 1850.00, 4, 1, 12, 0, 1, 4.7, 24),
(10, 1, '100% Jacquard Silk Tie & Cufflinks Set', 'jacquard-silk-tie-cufflinks-set', 'TIE-010', 'Handwoven pure mulberry silk necktie, pocket square, and cufflinks.', '1200-stitch pure mulberry silk necktie with matching cufflinks and silver tie bar.', 1400.00, 1050.00, 750.00, 5, 1, 35, 0, 1, 4.8, 19),
(11, 6, '925 Sterling Silver Crystal Ring', '925-sterling-silver-crystal-ring', 'RNG-011', 'Adjustable open-band cocktail ring with sparkling Austrian cubic zirconia.', 'Hand-set 5A cubic zirconia stones in pure 925 sterling silver with rhodium plating.', 1250.00, 950.00, 680.00, 5, 1, 28, 0, 1, 4.8, 31),
(12, 3, 'Vintage Distressed Cotton Baseball Cap', 'vintage-embroidered-cotton-baseball-cap', 'CAP-012', 'Washed vintage distressed cotton twill cap with 3D embroidery.', '100% Organic breathable washed cotton with adjustable antique brass buckle strap.', 750.00, 590.00, 420.00, 10, 1, 60, 1, 1, 4.6, 48);

-- --------------------------------------------------------
-- Table structure for `product_images`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_foreign` (`product_id`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `is_primary`) VALUES
(1, 1, 'uploads/luxury-watch.svg', 1),
(2, 2, 'uploads/leather-wallet.svg', 1),
(3, 3, 'uploads/polaroid-sunglasses.svg', 1),
(4, 4, 'uploads/designer-handbag.svg', 1),
(5, 5, 'uploads/leather-belt.svg', 1),
(6, 6, 'uploads/smart-watch.svg', 1),
(7, 7, 'uploads/pearl-necklace.svg', 1),
(8, 8, 'uploads/perfume-bottle.svg', 1),
(9, 9, 'uploads/leather-backpack.svg', 1),
(10, 10, 'uploads/silk-tie-set.svg', 1),
(11, 11, 'uploads/diamond-ring.svg', 1),
(12, 12, 'uploads/cotton-cap.svg', 1);

-- --------------------------------------------------------
-- Table structure for `banners`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `banners`;
CREATE TABLE `banners` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL,
  `subtitle` varchar(200) DEFAULT NULL,
  `badge_text` varchar(100) DEFAULT NULL,
  `button_text` varchar(50) NOT NULL DEFAULT 'Shop Now',
  `button_url` varchar(255) NOT NULL DEFAULT '/shop',
  `image_path` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `banners` (`id`, `title`, `subtitle`, `badge_text`, `button_text`, `button_url`, `image_path`, `is_active`, `display_order`) VALUES
(1, 'Elevate Your Everyday Style', 'Curated premium fashion accessories designed to make an impression.', '✨ NEW ARRIVALS 2026', 'Explore Shop', '/shop', 'uploads/hero-banner-1.svg', 1, 1),
(2, 'Exclusive Luxury Watches & Bags', 'Up to 40% OFF with Cash on Delivery nationwide across Bangladesh.', '🔥 LIMITED TIME OFFER', 'View Deals', '/deals', 'uploads/hero-banner-2.svg', 1, 2),
(3, 'Factory Prices for Retailers & Resellers', 'Low 5 Pcs Minimum Order Quantity with direct wholesale rates and fast dispatch.', '📦 WHOLESALE / B2B RATE', 'Open Wholesale', '/wholesale', 'uploads/hero-banner-3.svg', 1, 3);

-- --------------------------------------------------------
-- Table structure for `coupons`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `value` decimal(10,2) NOT NULL,
  `min_spend` decimal(10,2) DEFAULT NULL,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `times_used` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupons_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `coupons` (`id`, `code`, `type`, `value`, `min_spend`, `max_discount`, `usage_limit`, `times_used`, `is_active`, `expires_at`) VALUES
(1, 'FASHION10', 'percentage', 10.00, 1000.00, 500.00, 1000, 24, 1, '2027-01-01 00:00:00'),
(2, 'SAVE200', 'fixed', 200.00, 2000.00, 200.00, 500, 12, 1, '2027-01-01 00:00:00');

-- --------------------------------------------------------
-- Table structure for `blogs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `blogs`;
CREATE TABLE `blogs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT 'Buying Guide',
  `summary` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `author` varchar(100) DEFAULT 'OnlineBdMart Team',
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blogs_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `blogs` (`id`, `title`, `slug`, `category`, `summary`, `content`, `image_path`, `author`) VALUES
(1, 'Top 5 Luxury Chronograph Watches & Leather Wallets Under ৳5000 in 2026', 'top-5-luxury-watches-wallets-2026', 'Buying Guide', 'We tested 15+ premium Japanese quartz watches and full-grain cowhide leather wallets to find the best value for money in Bangladesh.', 'Finding genuine quality accessories in Bangladesh has never been easier. When choosing a chronograph watch, always check for 316L stainless steel and sapphire glass.\n\nFor leather wallets, full-grain cowhide develops a natural patina over time. All items at OnlineBdMart come with official warranty and nationwide cash on delivery.', 'uploads/hero-banner-1.svg', 'OnlineBdMart Editorial'),
(2, 'How to Spot Original vs Copy Accessories Before Paying', 'how-to-spot-original-vs-copy', 'Tips & Tricks', 'Avoid cheap replicas with these 5 quick verification checks before making payment to courier riders.', 'Always check the weight of the steel buckle, leather grain texture, and scratch resistance of TAC polarized lenses. With OnlineBdMart, you can inspect your parcel before accepting.', 'uploads/hero-banner-2.svg', 'Rahman T.'),
(3, 'Wholesale & Reselling Guide for Beginners in Bangladesh', 'wholesale-reselling-guide-bangladesh', 'B2B & Wholesale', 'How to start your online or offline accessory business with low MOQ and factory pricing directly from Tangail & Dhaka.', 'Start with high-demand everyday essentials: polarized sunglasses, reversible belts, and minimalist wallets. OnlineBdMart offers minimum 5 pcs bulk pricing with direct courier delivery.', 'uploads/hero-banner-3.svg', 'B2B Operations Team');

-- --------------------------------------------------------
-- Table structure for `orders`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `district` varchar(100) NOT NULL,
  `upazila` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `notes` text DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'cod',
  `payment_number` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `delivery_charge` decimal(10,2) NOT NULL DEFAULT 50.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `coupon_code` varchar(50) DEFAULT NULL,
  `grand_total` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `orders` (`id`, `customer_name`, `phone`, `whatsapp`, `district`, `upazila`, `address`, `notes`, `payment_method`, `payment_number`, `transaction_id`, `subtotal`, `delivery_charge`, `discount_amount`, `grand_total`, `status`, `created_at`) VALUES
(1001, 'Tanvir Ahmed', '01711223344', '01711223344', 'Tangail', 'Tangail Sadar', 'House 24, Road 4, Victoria Road', 'Please deliver after 4 PM', 'cod', NULL, NULL, 3250.00, 50.00, 0.00, 3300.00, 'delivered', '2026-08-10 11:20:00'),
(1002, 'Nusrat Jahan', '01899887766', '01899887766', 'Dhaka', 'Dhanmondi', 'House 5, Road 27, Dhanmondi', 'Call before arrival', 'bkash', '01899887766', 'TRX98432849', 3650.00, 80.00, 365.00, 3365.00, 'confirmed', '2026-08-12 14:15:00'),
(1003, 'Sabbir Hossain', '01912345678', '01912345678', 'Chittagong', 'Panchlaish', 'Nasirabad Housing Society', 'Handle with care', 'nagad', '01912345678', 'NGD77621893', 1190.00, 130.00, 0.00, 1320.00, 'pending', '2026-08-14 09:30:00');

-- --------------------------------------------------------
-- Table structure for `order_items`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `product_image`, `price`, `quantity`) VALUES
(1, 1001, 1, 'Luxury Chronograph Sapphire Watch', 'uploads/luxury-watch.svg', 3250.00, 1),
(2, 1002, 4, 'Minimalist Luxury Leather Handbag', 'uploads/designer-handbag.svg', 3650.00, 1),
(3, 1003, 2, 'Handcrafted Full-Grain Leather Wallet', 'uploads/leather-wallet.svg', 1190.00, 1);

-- --------------------------------------------------------
-- Table structure for `wholesale_inquiries`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `wholesale_inquiries`;
CREATE TABLE `wholesale_inquiries` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `business_name` varchar(150) NOT NULL,
  `contact_person` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `estimated_monthly_quantity` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `wholesale_inquiries` (`id`, `business_name`, `contact_person`, `phone`, `whatsapp`, `district`, `estimated_monthly_quantity`, `message`) VALUES
(1, 'Dhaka Gadget Zone', 'Tanvir Ahmed', '01711223344', '01711223344', 'Dhaka', '20 - 50 pcs', 'Interested in wholesale chronograph watches and genuine leather wallets.');

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
