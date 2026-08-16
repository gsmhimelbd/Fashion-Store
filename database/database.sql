-- OnlineBdMart Complete MySQL Database Dump
-- Compatible with MySQL 5.7, 8.0, MariaDB 10.x and phpMyAdmin

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Admins Table
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL DEFAULT 'Super Admin',
  `username` varchar(191) NOT NULL DEFAULT 'admin',
  `email` varchar(191) NOT NULL DEFAULT 'admin@fashionstore.com',
  `password` varchar(191) NOT NULL,
  `profile_photo` varchar(255) DEFAULT 'uploads/admin/avatar.png',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admins` (`id`, `name`, `username`, `email`, `password`, `profile_photo`) VALUES
(1, 'OnlineBdMart Admin', 'admin', 'admin@onlinebdmart.com', 'y02IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'uploads/admin/avatar.png');

-- 2. Categories & Subcategories Table
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `emoji` varchar(50) DEFAULT '🛍️',
  `icon` varchar(100) DEFAULT 'fa-tag',
  `image_path` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `show_on_homepage` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `emoji`, `icon`, `display_order`) VALUES
(1, NULL, 'Watches', 'watches', '⌚', 'fa-clock', 1),
(2, NULL, 'Smart Gadgets', 'smart-gadgets', '📱', 'fa-mobile-screen-button', 2),
(3, NULL, 'Leather Wallets', 'leather-wallets', '👛', 'fa-wallet', 3),
(4, NULL, 'Luxury Bags', 'luxury-bags', '👜', 'fa-bag-shopping', 4),
(5, NULL, 'Sunglasses', 'sunglasses', '🕶️', 'fa-glasses', 5),
(6, NULL, 'Accessories & Belts', 'accessories-belts', '👔', 'fa-gem', 6),
(7, 1, 'Chronograph Watches', 'chronograph-watches', '⏱️', 'fa-clock', 1),
(8, 1, 'Automatic Mechanical', 'automatic-mechanical', '⚙️', 'fa-gear', 2),
(9, 3, 'Full Grain Leather Wallets', 'full-grain-wallets', '💼', 'fa-wallet', 1),
(10, 4, 'Executive Handbags', 'executive-handbags', '👝', 'fa-briefcase', 1);

-- 3. Products Table
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `subcategory_id` int(11) DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `wholesale_price` decimal(10,2) DEFAULT NULL,
  `wholesale_moq` int(11) DEFAULT 5,
  `wholesale_min_qty` int(11) DEFAULT 5,
  `is_wholesale` tinyint(1) DEFAULT 1,
  `stock` int(11) DEFAULT 50,
  `stock_quantity` int(11) DEFAULT 50,
  `is_featured` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `image_path` varchar(255) DEFAULT 'images/products/watch-1.jpg',
  `gallery_images` text DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `specifications` text DEFAULT NULL,
  `why_buy_from_us` text DEFAULT NULL,
  `colors` text DEFAULT NULL,
  `sizes` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `focus_keyword` varchar(191) DEFAULT NULL,
  `reviews_count` int(11) DEFAULT 45,
  `rating` decimal(3,1) DEFAULT 4.9,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `sku`, `price`, `sale_price`, `wholesale_price`, `wholesale_moq`, `is_wholesale`, `stock`, `is_featured`, `image_path`, `gallery_images`, `short_description`, `description`, `specifications`, `why_buy_from_us`) VALUES
(1, 1, 'Naviforce Luxury Chronograph Watch', 'naviforce-luxury-chronograph-watch', 'WAT-01', 3850.00, 3250.00, 2450.00, 5, 1, 45, 1, 'images/products/watch-1.jpg', 'images/products/watch-1.jpg,images/products/watch-2.jpg,images/products/smartwatch-1.jpg', 'Premium Japanese quartz movement with genuine stainless steel strap and water resistance.', 'Experience timeless elegance with the Naviforce Luxury Chronograph. Crafted from surgical-grade 316L stainless steel with hardened mineral crystal glass and full functional sub-dials.', 'Dial Diameter: 45mm
Case Thickness: 12mm
Band Material: Stainless Steel
Movement: Japanese Quartz
Water Resistance: 30M (3ATM)
Glass: Scratch-resistant Hardlex', '✓ 100% Original Product Guarantee
✓ 7 Days Free Replacement Policy
✓ Cash On Delivery Across 64 Districts
✓ Official Warranty Card Included'),
(2, 2, 'Ultra AMOLED Bluetooth Calling Smartwatch', 'ultra-amoled-bluetooth-calling-smartwatch', 'SMW-01', 4200.00, 3499.00, 2600.00, 5, 1, 30, 1, 'images/products/smartwatch-1.jpg', 'images/products/smartwatch-1.jpg,images/products/headphone-1.jpg', 'High-definition 1.96-inch curved AMOLED display with health monitoring and Bluetooth 5.3 calling.', 'Stay connected in style with this Ultra Smartwatch. Includes heart rate, SpO2, sleep tracking, 100+ sports modes, wireless fast charging, and IP68 waterproof rating.', 'Display: 1.96-inch AMOLED 410x502
Battery Life: 7-10 Days
Connectivity: Bluetooth 5.3
Sensor: BioTracker PPG 4.0
Water Resistance: IP68
Compatibility: Android & iOS', '✓ 1 Year Replacement Guarantee
✓ Instant WhatsApp Support
✓ Free Home Delivery Available
✓ Original Box with Wireless Charger'),
(3, 3, 'Handcrafted Full-Grain Cowhide Leather Wallet', 'handcrafted-full-grain-cowhide-leather-wallet', 'WAL-01', 1650.00, 1290.00, 950.00, 5, 1, 80, 1, 'images/products/wallet-1.jpg', 'images/products/wallet-1.jpg,images/products/belt-1.jpg', 'Genuine full-grain cowhide leather with RFID blocking technology and dual cash compartments.', 'Handcrafted by master leather artisans in Tangail, this wallet combines minimalist slim aesthetics with maximum capacity for 8 cards, IDs, and Bangladeshi currency notes.', 'Material: 100% Genuine Full-Grain Cowhide Leather
Card Slots: 8 Dedicated Slots
Cash Compartments: 2 Full-Length Pockets
RFID Protection: Built-in RFID Shielding
Dimensions: 11.5cm x 9.5cm', '✓ Genuine Leather Certified
✓ 5 Years Leather Durability Guarantee
✓ Premium Gift Box Included
✓ Cash on Delivery at Your Doorstep'),
(4, 4, 'Executive Minimalist Leather Handbag', 'executive-minimalist-leather-handbag', 'BAG-01', 3950.00, 3190.00, 2400.00, 5, 1, 25, 1, 'images/products/bag-1.jpg', 'images/products/bag-1.jpg,images/products/wallet-1.jpg', 'Structured luxury design with gold-tone hardware and spacious multi-compartment interior.', 'The ultimate statement piece for professional and casual outings. Accommodates 13-inch laptop, tablets, makeup, and daily essentials with detachable shoulder strap.', 'Material: Premium Textured PU & Cowhide Trim
Lining: High-density Polyester
Closure: Heavy-duty Gold Metal Zipper
Strap: Detachable & Adjustable
Weight: 680 grams', '✓ Luxury Presentation Box & Dust Bag
✓ 100% Quality Checked Before Dispatch
✓ Free Parcel Open Check on Delivery
✓ 24/7 WhatsApp Hotline Support'),
(5, 5, 'Aviator Polarized UV400 Sunglasses', 'aviator-polarized-uv400-sunglasses', 'SUN-01', 1850.00, 1390.00, 990.00, 5, 1, 60, 1, 'images/products/sunglasses-1.jpg', 'images/products/sunglasses-1.jpg,images/products/watch-1.jpg', 'HD Polarized TAC lenses with full UV400 protection and ultra-lightweight magnesium aluminum frame.', 'Shield your eyes with timeless aviator elegance. Eliminates glare from road surfaces and water for high-definition visual clarity while driving or outdoors.', 'Frame Material: Aluminum Magnesium Alloy
Lens Type: Triacetate TAC HD Polarized
UV Protection: 100% UV400 (UVA/UVB)
Lens Width: 62mm
Bridge Width: 14mm', '✓ Polarized Test Card Included in Box
✓ Hard Leather Protective Case + Microfiber Cloth
✓ 7 Days Return Guarantee
✓ Cash on Delivery'),
(6, 6, 'Automatic Reversible Genuine Leather Belt', 'automatic-reversible-genuine-leather-belt', 'BLT-01', 1450.00, 1150.00, 850.00, 5, 1, 75, 1, 'images/products/belt-1.jpg', 'images/products/belt-1.jpg,images/products/wallet-1.jpg', 'Premium ratchet automatic buckle with micro-adjustable no-hole strap design in genuine leather.', 'Say goodbye to stretched belt holes. The smooth micro-click sliding ratchet buckle gives a customized perfect fit for waist sizes from 28 to 44 inches.', 'Material: 100% Genuine Split Cowhide Leather
Buckle: Zinc Alloy Scratch-resistant Automatic
Belt Width: 3.5cm (Standard Formal & Casual)
Length: 125cm (Easy to trim to custom size)', '✓ Heavy-duty Alloy Buckle Guarantee
✓ Elegant Magnetic Gift Packaging
✓ Direct Factory Wholesale Rate Available
✓ Cash on Delivery');

-- 4. Banners Table
DROP TABLE IF EXISTS `banners`;
CREATE TABLE `banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(191) NOT NULL,
  `subtitle` varchar(191) DEFAULT NULL,
  `badge_text` varchar(100) DEFAULT NULL,
  `button_text` varchar(100) DEFAULT 'Shop Now',
  `button_url` varchar(191) DEFAULT 'shop.php',
  `image_path` varchar(255) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `banners` (`id`, `title`, `subtitle`, `badge_text`, `button_text`, `button_url`, `image_path`, `display_order`) VALUES
(1, 'Premium Quartz & Leather Collection', 'Discover original chronographs, handcrafted leather wallets, and smart gadgets in Bangladesh.', '✨ 2026 LUXURY ARRIVALS', 'Explore Catalog', 'shop.php', 'images/hero/hero-1.jpg', 1),
(2, 'Wholesale & B2B Bulk Supply Portal', 'Get factory direct pricing with low 5 pcs MOQ for online retailers, boutique stores, and corporate gifting.', '📦 B2B WHOLESALE RATES', 'Open Wholesale', 'wholesale.php', 'images/hero/hero-2.jpg', 2);

-- 5. Districts Table (64 Bangladesh Districts)
DROP TABLE IF EXISTS `districts`;
CREATE TABLE `districts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `division_name` varchar(100) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 120.00,
  `estimated_days` varchar(50) DEFAULT '2-4 days',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `districts` (`name`, `division_name`, `delivery_fee`, `estimated_days`) VALUES
('Dhaka', 'Dhaka', 80.00, '1-2 days'),
('Tangail', 'Dhaka', 50.00, '24 hours'),
('Gazipur', 'Dhaka', 80.00, '1-2 days'),
('Narayanganj', 'Dhaka', 80.00, '1-2 days'),
('Chittagong', 'Chittagong', 130.00, '2-3 days'),
('Sylhet', 'Sylhet', 130.00, '2-3 days'),
('Rajshahi', 'Rajshahi', 130.00, '2-4 days'),
('Khulna', 'Khulna', 130.00, '2-4 days'),
('Barisal', 'Barisal', 130.00, '2-4 days'),
('Rangpur', 'Rangpur', 130.00, '2-4 days'),
('Mymensingh', 'Mymensingh', 100.00, '2-3 days'),
('Comilla', 'Chittagong', 120.00, '2-3 days'),
('Bogra', 'Rajshahi', 120.00, '2-3 days'),
('Jessore', 'Khulna', 120.00, '2-3 days'),
('Cox's Bazar', 'Chittagong', 130.00, '2-4 days'),
('Narsingdi', 'Dhaka', 100.00, '2-3 days'),
('Faridpur', 'Dhaka', 120.00, '2-3 days'),
('Kushtia', 'Khulna', 120.00, '2-3 days'),
('Pabna', 'Rajshahi', 120.00, '2-3 days'),
('Dinajpur', 'Rangpur', 130.00, '2-4 days'),
('Sirajganj', 'Rajshahi', 100.00, '2-3 days'),
('Jamalpur', 'Mymensingh', 100.00, '2-3 days'),
('Brahmanbaria', 'Chittagong', 120.00, '2-3 days'),
('Noakhali', 'Chittagong', 120.00, '2-3 days'),
('Feni', 'Chittagong', 120.00, '2-3 days'),
('Manikganj', 'Dhaka', 100.00, '2-3 days'),
('Munshiganj', 'Dhaka', 100.00, '2-3 days'),
('Kishoreganj', 'Dhaka', 100.00, '2-3 days'),
('Netrokona', 'Mymensingh', 120.00, '2-3 days'),
('Sherpur', 'Mymensingh', 120.00, '2-3 days'),
('Habiganj', 'Sylhet', 130.00, '2-3 days'),
('Moulvibazar', 'Sylhet', 130.00, '2-3 days'),
('Sunamganj', 'Sylhet', 130.00, '2-4 days'),
('Natore', 'Rajshahi', 120.00, '2-3 days'),
('Naogaon', 'Rajshahi', 120.00, '2-3 days'),
('Chapainawabganj', 'Rajshahi', 130.00, '2-4 days'),
('Joypurhat', 'Rajshahi', 130.00, '2-4 days'),
('Kurigram', 'Rangpur', 130.00, '2-4 days'),
('Gaibandha', 'Rangpur', 130.00, '2-4 days'),
('Lalmonirhat', 'Rangpur', 130.00, '2-4 days'),
('Nilphamari', 'Rangpur', 130.00, '2-4 days'),
('Panchagarh', 'Rangpur', 140.00, '3-5 days'),
('Thakurgaon', 'Rangpur', 140.00, '3-5 days'),
('Satkhira', 'Khulna', 130.00, '2-4 days'),
('Bagerhat', 'Khulna', 130.00, '2-4 days'),
('Jhenaidah', 'Khulna', 120.00, '2-3 days'),
('Magura', 'Khulna', 120.00, '2-3 days'),
('Narail', 'Khulna', 120.00, '2-3 days'),
('Chuadanga', 'Khulna', 130.00, '2-4 days'),
('Meherpur', 'Khulna', 130.00, '2-4 days'),
('Bhola', 'Barisal', 140.00, '3-5 days'),
('Jhalokati', 'Barisal', 130.00, '2-4 days'),
('Pirojpur', 'Barisal', 130.00, '2-4 days'),
('Patuakhali', 'Barisal', 140.00, '3-5 days'),
('Barguna', 'Barisal', 140.00, '3-5 days'),
('Chandpur', 'Chittagong', 120.00, '2-3 days'),
('Lakshmipur', 'Chittagong', 120.00, '2-3 days'),
('Bandarban', 'Chittagong', 140.00, '3-5 days'),
('Rangamati', 'Chittagong', 140.00, '3-5 days'),
('Khagrachhari', 'Chittagong', 140.00, '3-5 days'),
('Gopalganj', 'Dhaka', 120.00, '2-3 days'),
('Madaripur', 'Dhaka', 120.00, '2-3 days'),
('Rajbari', 'Dhaka', 120.00, '2-3 days'),
('Shariatpur', 'Dhaka', 120.00, '2-3 days');

-- 6. Orders Table
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(100) DEFAULT NULL,
  `customer_name` varchar(191) NOT NULL,
  `customer_email` varchar(191) DEFAULT NULL,
  `customer_phone` varchar(100) NOT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `whatsapp` varchar(100) DEFAULT NULL,
  `delivery_address` text NOT NULL,
  `address` text DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `district_name` varchar(100) DEFAULT NULL,
  `upazila` varchar(100) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_cost` decimal(10,2) NOT NULL DEFAULT 120.00,
  `delivery_charge` decimal(10,2) NOT NULL DEFAULT 120.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `coupon_code` varchar(50) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT 'cod',
  `payment_number` varchar(100) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Order Items Table
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(191) NOT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `total_price` decimal(10,2) DEFAULT NULL,
  `is_wholesale` tinyint(1) DEFAULT 0,
  `color` varchar(100) DEFAULT NULL,
  `size` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7.1 Discount Coupons Table
DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `discount_type` varchar(20) NOT NULL DEFAULT 'fixed',
  `discount_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_spend` decimal(10,2) NOT NULL DEFAULT 0.00,
  `product_id` int(11) DEFAULT NULL,
  `first_order_only` tinyint(1) DEFAULT 0,
  `show_in_header` tinyint(1) DEFAULT 1,
  `header_banner_text` varchar(255) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `coupons` (`id`, `code`, `discount_type`, `discount_value`, `min_spend`, `product_id`, `show_in_header`, `header_banner_text`, `is_active`) VALUES
(1, 'SPECIAL100', 'fixed', 100.00, 1000.00, NULL, 1, '🎁 Special Offer: Use Code "SPECIAL100" to get ৳100 OFF on orders above ৳1000!', 1);

-- 8. Users Table
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `address` text DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Suppliers Table
DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `contact_person` varchar(191) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT 'uploads/suppliers/supplier-default.jpg',
  `supply_products` text DEFAULT NULL,
  `category` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `suppliers` (`id`, `name`, `contact_person`, `phone`, `email`, `address`, `photo`, `supply_products`, `category`) VALUES
(1, 'BD Watch & Quartz Importers Ltd', 'Kamal Hossain', '01711223344', 'kamal@bdwatch.com', 'Chawkbazar, Dhaka', 'uploads/suppliers/supplier-default.jpg', 'Naviforce, Curren, Skmei, Casio Quartz Watches', 'Watches & Chronographs'),
(2, 'Tangail Artisan Leather Works', 'Siddiqur Rahman', '01811998877', 'tangailleather@gmail.com', 'Court Bazar, Tangail', 'uploads/suppliers/supplier-default.jpg', 'Handmade Cowhide Wallets, Leather Belts, Card Holders', 'Leather Goods'),
(3, 'SmartTech Gadgets Bangladesh', 'Fahim Morshed', '01911445566', 'fahim@smarttech.bd', 'Elephant Road, Dhaka', 'uploads/suppliers/supplier-default.jpg', 'Ultra AMOLED Smartwatches, Bluetooth Earbuds', 'Smart Electronics');

-- 10. Customer Behavior & Visits
DROP TABLE IF EXISTS `customer_visits`;
CREATE TABLE `customer_visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(100) DEFAULT NULL,
  `session_id` varchar(191) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `referrer` varchar(255) DEFAULT NULL,
  `page_url` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `device_type` varchar(50) DEFAULT 'Mobile',
  `visited_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Cart Abandonments Table
DROP TABLE IF EXISTS `cart_abandonments`;
CREATE TABLE `cart_abandonments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(191) DEFAULT NULL,
  `customer_name` varchar(191) DEFAULT NULL,
  `customer_phone` varchar(100) DEFAULT NULL,
  `district_name` varchar(100) DEFAULT NULL,
  `product_name` varchar(191) DEFAULT NULL,
  `cart_value` decimal(10,2) DEFAULT 0.00,
  `step` varchar(50) DEFAULT 'cart',
  `recovered` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Messages Table
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. Customer Reviews Table
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `author_name` varchar(191) NOT NULL,
  `rating` int(11) NOT NULL DEFAULT 5,
  `review_text` text NOT NULL,
  `district_name` varchar(100) DEFAULT 'Dhaka',
  `phone` varchar(100) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `reviews` (`id`, `author_name`, `rating`, `review_text`, `district_name`) VALUES
(1, 'Arif Hossain', 5, 'Luxury watch quality outstanding! Heavy steel weight and sapphire glass looks premium. Delivery was completed in 2 days.', 'Dhaka'),
(2, 'Nusrat Jahan', 5, 'Got genuine leather handbag and pearl necklace. Best price in BD and cash on delivery was smooth.', 'Chittagong'),
(3, 'Tanvir Ahmed', 5, 'Polarized sunglasses and leather wallet are authentic. Real-time order tracking updated every step.', 'Sylhet');

-- 14. Blog Posts Table
DROP TABLE IF EXISTS `blog_posts`;
CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `category` varchar(100) DEFAULT 'Buying Guide',
  `author` varchar(100) DEFAULT 'OnlineBdMart Team',
  `summary` text DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `image_path` varchar(255) DEFAULT 'images/hero/hero-1.jpg',
  `meta_title` varchar(191) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `blog_posts` (`id`, `title`, `slug`, `category`, `author`, `summary`, `content`, `image_path`, `meta_title`, `meta_description`) VALUES
(1, 'Top 5 Luxury Watches & Accessories Under ৳5000 in 2026', 'top-5-luxury-watches-2026', 'Buying Guide', 'OnlineBdMart Experts', 'Complete breakdown of build materials, glass scratch resistance, and Japanese quartz movements available in BD.', 'We tested 15+ premium Japanese quartz watches and full-grain cowhide leather wallets to find the best value for money in Bangladesh. Finding authentic accessories with durable build quality under ৳5,000 is now easier with verified warranty backing.', 'images/hero/hero-1.jpg', 'Top 5 Luxury Watches Under 5000 BD 2026', 'Best luxury chronograph watches in Bangladesh under 5000 taka.'),
(2, 'How to Spot Original vs Copy Accessories Before Paying', 'spot-original-vs-copy-accessories', 'Tips & Tricks', 'OnlineBdMart Quality Team', 'Simple checks on stitching quality, serial engravings, and packaging seals to ensure you get authentic goods.', 'Avoid cheap replicas with these 5 quick verification checks before making payment to courier riders: 1. Check serial number engraving, 2. Inspect leather grain smell and texture, 3. Test chronograph sub-dials.', 'images/hero/hero-2.jpg', 'How to Spot Original vs Copy Accessories BD', 'Avoid counterfeit replica watches and leather bags in Bangladesh with these 5 checks.'),
(3, 'Wholesale & Reselling Guide for Beginners in Bangladesh', 'wholesale-reselling-guide-bangladesh', 'Wholesale & B2B', 'B2B Wholesale Manager', 'Start an online boutique or Facebook shop with minimal capital using OnlineBdMart low 5 pcs MOQ bulk policy.', 'Starting an online accessory boutique in Bangladesh no longer requires large capital. With OnlineBdMart low 5 pcs MOQ policy, you can source at factory rates and sell directly to customers.', 'images/hero/hero-1.jpg', 'Wholesale Reselling Business Guide Bangladesh', 'How to start online fashion accessories wholesale and dropshipping in BD.');

-- 15. Settings Table
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(191) DEFAULT NULL,
  `setting_key` varchar(191) DEFAULT NULL,
  `value` longtext DEFAULT NULL,
  `setting_value` longtext DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_unique` (`key`),
  UNIQUE KEY `setting_key_unique` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`key`, `setting_key`, `value`, `setting_value`) VALUES
('store_name', 'store_name', 'OnlineBdMart', 'OnlineBdMart'),
('store_tagline', 'store_tagline', 'Online Shopping BD - Wholesale & Retail', 'Online Shopping BD - Wholesale & Retail'),
('store_logo', 'store_logo', 'images/logo.png', 'images/logo.png'),
('store_favicon', 'store_favicon', 'images/logo.png', 'images/logo.png'),
('store_email', 'store_email', 'support@onlinebdmart.com', 'support@onlinebdmart.com'),
('store_phone', 'store_phone', '01775153740', '01775153740'),
('store_address', 'store_address', 'Tangail, Dhaka Division, Bangladesh', 'Tangail, Dhaka Division, Bangladesh'),
('whatsapp_number', 'whatsapp_number', '01775153740', '01775153740'),
('whatsapp_floating_enabled', 'whatsapp_floating_enabled', '1', '1'),
('payment_cod_enabled', 'payment_cod_enabled', '1', '1'),
('payment_bkash_enabled', 'payment_bkash_enabled', '1', '1'),
('payment_bkash_number', 'payment_bkash_number', '01775153740', '01775153740'),
('payment_bkash_type', 'payment_bkash_type', 'merchant', 'merchant'),
('payment_nagad_enabled', 'payment_nagad_enabled', '1', '1'),
('payment_nagad_number', 'payment_nagad_number', '01775153740', '01775153740'),
('payment_rocket_enabled', 'payment_rocket_enabled', '1', '1'),
('payment_rocket_number', 'payment_rocket_number', '01775153740', '01775153740'),
('payment_bank_enabled', 'payment_bank_enabled', '1', '1'),
('payment_bank_name', 'payment_bank_name', 'Islami Bank Bangladesh Ltd', 'Islami Bank Bangladesh Ltd'),
('payment_bank_acc_name', 'payment_bank_acc_name', 'OnlineBdMart Enterprise', 'OnlineBdMart Enterprise'),
('payment_bank_acc_no', 'payment_bank_acc_no', '2050123456789012', '2050123456789012'),
('payment_bank_branch', 'payment_bank_branch', 'Tangail Branch', 'Tangail Branch'),
('payment_bank_routing', 'payment_bank_routing', '125272648', '125272648'),
('payment_ssl_enabled', 'payment_ssl_enabled', '0', '0'),
('payment_ssl_store_id', 'payment_ssl_store_id', 'onlinebdmart_live', 'onlinebdmart_live'),
('payment_ssl_store_passwd', 'payment_ssl_store_passwd', 'sslcommerz_secret_key', 'sslcommerz_secret_key'),
('payment_ssl_sandbox', 'payment_ssl_sandbox', '1', '1'),
('footer_about_text', 'footer_about_text', 'OnlineBdMart is Bangladesh premier wholesale and retail fashion destination offering 100% verified authentic accessories and smart gadgets with Cash on Delivery nationwide.', 'OnlineBdMart is Bangladesh premier wholesale and retail fashion destination offering 100% verified authentic accessories and smart gadgets with Cash on Delivery nationwide.'),
('footer_copyright', 'footer_copyright', 'OnlineBdMart • Online Shopping Bangladesh. All rights reserved.', 'OnlineBdMart • Online Shopping Bangladesh. All rights reserved.'),
('seo_meta_title', 'seo_meta_title', 'OnlineBdMart • Online Shopping BD - Wholesale & Retail', 'OnlineBdMart • Online Shopping BD - Wholesale & Retail'),
('seo_meta_description', 'seo_meta_description', 'Buy original luxury watches, leather wallets, handbags, and smart gadgets at best prices in Bangladesh with Cash on Delivery across 64 districts.', 'Buy original luxury watches, leather wallets, handbags, and smart gadgets at best prices in Bangladesh with Cash on Delivery across 64 districts.'),
('seo_meta_keywords', 'seo_meta_keywords', 'online shopping bd, onlinebdmart, wholesale bangladesh, watches bd, leather wallet, accessories bangladesh, cash on delivery', 'online shopping bd, onlinebdmart, wholesale bangladesh, watches bd, leather wallet, accessories bangladesh, cash on delivery'),
('seo_og_image', 'seo_og_image', 'images/hero/hero-1.jpg', 'images/hero/hero-1.jpg'),
('telegram_bot_token', 'telegram_bot_token', '', ''),
('telegram_chat_id', 'telegram_chat_id', '', ''),
('telegram_alerts_enabled', 'telegram_alerts_enabled', '0', '0'),
('smtp_host', 'smtp_host', 'mail.onlinebdmart.com', 'mail.onlinebdmart.com'),
('smtp_port', 'smtp_port', '465', '465'),
('smtp_username', 'smtp_username', 'info@onlinebdmart.com', 'info@onlinebdmart.com'),
('smtp_password', 'smtp_password', '', ''),
('smtp_encryption', 'smtp_encryption', 'ssl', 'ssl'),
('smtp_from_address', 'smtp_from_address', 'info@onlinebdmart.com', 'info@onlinebdmart.com'),
('smtp_from_name', 'smtp_from_name', 'OnlineBdMart', 'OnlineBdMart'),
('notify_admin_email', 'notify_admin_email', 'admin@onlinebdmart.com', 'admin@onlinebdmart.com'),
('notify_order_placed', 'notify_order_placed', '1', '1'),
('notify_order_shipped', 'notify_order_shipped', '1', '1'),
('notify_order_delivered', 'notify_order_delivered', '1', '1');

SET FOREIGN_KEY_CHECKS = 1;
