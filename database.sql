
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+05:30";

CREATE DATABASE IF NOT EXISTS `vegihub` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `vegihub`;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `role` ENUM('buyer','seller','admin') NOT NULL DEFAULT 'buyer',
    `avatar` VARCHAR(255) DEFAULT NULL,
    `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
    `verification_code` VARCHAR(6) DEFAULT NULL,
    `verification_expires` DATETIME DEFAULT NULL,
    `reset_token` VARCHAR(64) DEFAULT NULL,
    `reset_expires` DATETIME DEFAULT NULL,
    `status` ENUM('active','banned','pending') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_email` (`email`),
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `image` VARCHAR(255) DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `parent_id` INT DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_categories_slug` (`slug`),
    INDEX `idx_categories_parent` (`parent_id`),
    FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `seller_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `name` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(200) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `short_description` VARCHAR(500) DEFAULT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `sale_price` DECIMAL(10,2) DEFAULT NULL,
    `unit` ENUM('kg','g','piece','bunch','dozen','pack') NOT NULL DEFAULT 'kg',
    `stock` INT NOT NULL DEFAULT 0,
    `min_order_qty` INT NOT NULL DEFAULT 1,
    `image` VARCHAR(255) DEFAULT NULL,
    `is_organic` TINYINT(1) NOT NULL DEFAULT 0,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `status` ENUM('active','inactive','pending') NOT NULL DEFAULT 'pending',
    `avg_rating` DECIMAL(2,1) NOT NULL DEFAULT 0.0,
    `total_reviews` INT NOT NULL DEFAULT 0,
    `total_sold` INT NOT NULL DEFAULT 0,
    `views` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_products_seller` (`seller_id`),
    INDEX `idx_products_category` (`category_id`),
    INDEX `idx_products_slug` (`slug`),
    INDEX `idx_products_status` (`status`),
    INDEX `idx_products_featured` (`is_featured`),
    INDEX `idx_products_price` (`price`),
    FOREIGN KEY (`seller_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `addresses`;
CREATE TABLE `addresses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `label` VARCHAR(50) DEFAULT 'Home',
    `full_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `address_line1` VARCHAR(255) NOT NULL,
    `address_line2` VARCHAR(255) DEFAULT NULL,
    `city` VARCHAR(100) NOT NULL,
    `state` VARCHAR(100) NOT NULL,
    `pincode` VARCHAR(10) NOT NULL,
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_addresses_user` (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_cart_user_product` (`user_id`, `product_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE `wishlist` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_wishlist_user_product` (`user_id`, `product_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(20) NOT NULL UNIQUE,
    `type` ENUM('percentage','fixed') NOT NULL DEFAULT 'percentage',
    `value` DECIMAL(10,2) NOT NULL,
    `min_order` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `max_discount` DECIMAL(10,2) DEFAULT NULL,
    `usage_limit` INT NOT NULL DEFAULT 100,
    `used_count` INT NOT NULL DEFAULT 0,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_coupons_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(20) NOT NULL UNIQUE,
    `user_id` INT NOT NULL,
    `address_id` INT DEFAULT NULL,
    `address_snapshot` TEXT DEFAULT NULL,
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `discount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `delivery_fee` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `total` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `coupon_id` INT DEFAULT NULL,
    `coupon_code` VARCHAR(20) DEFAULT NULL,
    `payment_method` ENUM('razorpay','cod') NOT NULL DEFAULT 'razorpay',
    `payment_status` ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    `razorpay_order_id` VARCHAR(100) DEFAULT NULL,
    `razorpay_payment_id` VARCHAR(100) DEFAULT NULL,
    `razorpay_signature` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('pending','confirmed','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    `notes` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_orders_user` (`user_id`),
    INDEX `idx_orders_status` (`status`),
    INDEX `idx_orders_number` (`order_number`),
    INDEX `idx_orders_payment` (`payment_status`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `seller_id` INT NOT NULL,
    `product_name` VARCHAR(200) NOT NULL,
    `product_image` VARCHAR(255) DEFAULT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `quantity` INT NOT NULL,
    `total` DECIMAL(10,2) NOT NULL,
    `status` ENUM('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    INDEX `idx_oi_order` (`order_id`),
    INDEX `idx_oi_seller` (`seller_id`),
    INDEX `idx_oi_product` (`product_id`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`seller_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `order_id` INT DEFAULT NULL,
    `rating` TINYINT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `comment` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_review_user_product` (`user_id`, `product_id`),
    INDEX `idx_reviews_product` (`product_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `seller_reviews`;
CREATE TABLE `seller_reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `seller_id` INT NOT NULL,
    `product_id` INT DEFAULT NULL,
    `order_id` INT DEFAULT NULL,
    `rating` TINYINT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `comment` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_seller_review_user_seller_product` (`user_id`, `seller_id`, `product_id`),
    INDEX `idx_seller_reviews_seller` (`seller_id`),
    INDEX `idx_seller_reviews_product` (`product_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`seller_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `subject` VARCHAR(200) DEFAULT NULL,
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `seller_payouts`;
CREATE TABLE `seller_payouts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `seller_id` INT NOT NULL,
    `order_id` INT NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `commission` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `net_amount` DECIMAL(10,2) NOT NULL,
    `status` ENUM('pending','paid') NOT NULL DEFAULT 'pending',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_payouts_seller` (`seller_id`),
    FOREIGN KEY (`seller_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `message` TEXT DEFAULT NULL,
    `type` VARCHAR(50) DEFAULT 'info',
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `link` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_notif_user` (`user_id`),
    INDEX `idx_notif_read` (`is_read`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `platform_settings`;
CREATE TABLE `platform_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT DEFAULT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `users` (`name`, `email`, `password`, `phone`, `role`, `email_verified`, `status`) VALUES
('Admin', 'rudranahak1000@gmail.com', '$2y$10$RfmPZfwcPhUjs.n3bPPF1.wuQO1IIfYiYVqIpEVE.xnJUOZhBGIr.', '9876543210', 'admin', 1, 'active');

INSERT INTO `users` (`name`, `email`, `password`, `phone`, `role`, `email_verified`, `status`) VALUES
('Fresh Farms', 'seller@vegihub.com', '$2y$10$61Zr5PylJfSVEoU/xeQuF.PrkobuuhDvbYyjEyzR3bCaRG3qKb5Iy', '9876543211', 'seller', 1, 'active'),
('Green Valley Organics', 'seller2@vegihub.com', '$2y$10$61Zr5PylJfSVEoU/xeQuF.PrkobuuhDvbYyjEyzR3bCaRG3qKb5Iy', '9876543212', 'seller', 1, 'active');

INSERT INTO `users` (`name`, `email`, `password`, `phone`, `role`, `email_verified`, `status`) VALUES
('Chandan Kumar', 'buyer@vegihub.com', '$2y$10$8xsDfLwDXgnIXLnjzmXRn.CUVC3W1R3eWFtFY/PsCX/XXINIeJ3S.', '9876543213', 'buyer', 1, 'active');

INSERT INTO `categories` (`name`, `slug`, `icon`, `description`, `sort_order`) VALUES
('Leafy Greens', 'leafy-greens', '🥬', 'Fresh spinach, lettuce, kale and more', 1),
('Root Vegetables', 'root-vegetables', '🥕', 'Carrots, potatoes, onions, beetroot', 2),
('Tomatoes & Peppers', 'tomatoes-peppers', '🍅', 'Fresh tomatoes, bell peppers, chillies', 3),
('Gourds & Squash', 'gourds-squash', '🫛', 'Bottle gourd, pumpkin, zucchini', 4),
('Beans & Peas', 'beans-peas', '🫘', 'Green beans, peas, chickpeas', 5),
('Exotic Vegetables', 'exotic-vegetables', '🥦', 'Broccoli, asparagus, artichoke', 6),
('Herbs & Seasonings', 'herbs-seasonings', '🌿', 'Coriander, mint, basil, curry leaves', 7),
('Fruits', 'fruits', '🍎', 'Fresh seasonal fruits', 8),
('Organic', 'organic', '🌱', 'Certified organic vegetables', 9),
('Combo Packs', 'combo-packs', '📦', 'Value packs and combos', 10);

INSERT INTO `products` (`seller_id`, `category_id`, `name`, `slug`, `description`, `short_description`, `price`, `sale_price`, `unit`, `stock`, `image`, `is_organic`, `is_featured`, `status`, `avg_rating`, `total_reviews`, `total_sold`) VALUES
(2, 1, 'Fresh Baby Spinach', 'fresh-baby-spinach', 'Hand-picked baby spinach leaves, washed and ready to eat. Rich in iron and vitamins.', 'Premium baby spinach, 250g pack', 45.00, 35.00, 'pack', 100, 'spinach.jpg', 1, 1, 'active', 4.5, 28, 156),
(2, 1, 'Organic Kale Bundle', 'organic-kale-bundle', 'Fresh organic kale, perfect for salads and smoothies. Packed with nutrients.', 'Organic kale, 200g bunch', 65.00, NULL, 'bunch', 50, 'kale.jpg', 1, 1, 'active', 4.3, 15, 89),
(2, 2, 'Farm Fresh Carrots', 'farm-fresh-carrots', 'Sweet and crunchy carrots straight from the farm. Perfect for juicing or cooking.', 'Fresh orange carrots, per kg', 40.00, 32.00, 'kg', 200, 'carrots.jpg', 0, 1, 'active', 4.7, 42, 312),
(2, 2, 'Premium Red Onions', 'premium-red-onions', 'High-quality red onions with strong flavor. Essential for every kitchen.', 'Red onions, per kg', 35.00, NULL, 'kg', 500, 'onions.jpg', 0, 0, 'active', 4.2, 35, 520),
(2, 2, 'Golden Potatoes', 'golden-potatoes', 'Fresh golden potatoes, perfect for frying, boiling, or mashing.', 'Fresh potatoes, per kg', 30.00, 25.00, 'kg', 400, 'potatoes.jpg', 0, 1, 'active', 4.6, 48, 680),
(2, 3, 'Vine Ripened Tomatoes', 'vine-ripened-tomatoes', 'Naturally vine-ripened tomatoes with rich flavor. Perfect for salads and cooking.', 'Fresh red tomatoes, per kg', 50.00, 40.00, 'kg', 150, 'tomatoes.jpg', 0, 1, 'active', 4.8, 56, 445),
(2, 3, 'Green Bell Peppers', 'green-bell-peppers', 'Crisp green bell peppers, great for stir-fry and stuffing.', 'Fresh bell peppers, per kg', 80.00, NULL, 'kg', 80, 'bellpepper.jpg', 0, 0, 'active', 4.1, 12, 78),
(2, 3, 'Fresh Green Chillies', 'fresh-green-chillies', 'Spicy green chillies to add heat to your dishes.', 'Green chillies, 100g pack', 15.00, 10.00, 'pack', 300, 'chillies.jpg', 0, 0, 'active', 4.4, 22, 290),
(3, 4, 'Bottle Gourd (Lauki)', 'bottle-gourd-lauki', 'Fresh bottle gourd, light and nutritious. Perfect for curries and juices.', 'Bottle gourd, per piece', 35.00, NULL, 'piece', 60, 'lauki.jpg', 0, 0, 'active', 4.0, 8, 45),
(3, 4, 'Sweet Pumpkin', 'sweet-pumpkin', 'Sweet orange pumpkin, rich in beta-carotene and fiber.', 'Pumpkin, per kg', 25.00, 20.00, 'kg', 80, 'pumpkin.jpg', 0, 1, 'active', 4.3, 14, 67),
(3, 5, 'French Beans', 'french-beans', 'Tender French beans, perfect for stir-fry and salads.', 'Fresh French beans, per kg', 60.00, 50.00, 'kg', 100, 'beans.jpg', 0, 0, 'active', 4.5, 18, 95),
(3, 5, 'Green Peas (Shelled)', 'green-peas-shelled', 'Fresh shelled green peas, ready to cook. Sweet and tender.', 'Shelled green peas, per kg', 90.00, 75.00, 'kg', 60, 'peas.jpg', 0, 1, 'active', 4.6, 25, 130),
(3, 6, 'Fresh Broccoli', 'fresh-broccoli', 'Premium broccoli florets, packed with vitamins and minerals.', 'Fresh broccoli, per piece', 55.00, 45.00, 'piece', 40, 'broccoli.jpg', 0, 1, 'active', 4.7, 30, 112),
(3, 6, 'Baby Corn Pack', 'baby-corn-pack', 'Tender baby corn, great for Asian cuisine and stir-fry.', 'Baby corn, 200g pack', 50.00, NULL, 'pack', 70, 'babycorn.jpg', 0, 0, 'active', 4.2, 10, 55),
(3, 7, 'Fresh Coriander', 'fresh-coriander', 'Aromatic fresh coriander leaves for garnishing and chutneys.', 'Coriander bunch', 10.00, NULL, 'bunch', 200, 'coriander.jpg', 0, 0, 'active', 4.4, 20, 380),
(3, 7, 'Fresh Mint Leaves', 'fresh-mint-leaves', 'Fresh mint leaves, perfect for chutneys, raita, and drinks.', 'Mint leaves bunch', 10.00, 8.00, 'bunch', 180, 'mint.jpg', 0, 0, 'active', 4.5, 16, 290),
(2, 8, 'Royal Gala Apples', 'royal-gala-apples', 'Sweet and crisp Royal Gala apples imported from Shimla.', 'Apples, per kg', 180.00, 150.00, 'kg', 100, 'apples.jpg', 0, 1, 'active', 4.8, 35, 200),
(2, 8, 'Fresh Bananas', 'fresh-bananas', 'Ripe yellow bananas, naturally sweet and energy-packed.', 'Bananas, per dozen', 50.00, 40.00, 'dozen', 200, 'bananas.jpg', 0, 0, 'active', 4.6, 40, 350),
(3, 9, 'Organic Tomatoes', 'organic-tomatoes', 'Certified organic tomatoes grown without pesticides.', 'Organic tomatoes, per kg', 70.00, 60.00, 'kg', 60, 'organic_tomatoes.jpg', 1, 1, 'active', 4.9, 22, 88),
(3, 9, 'Organic Cucumber', 'organic-cucumber', 'Fresh organic cucumbers, crisp and refreshing.', 'Organic cucumbers, per kg', 45.00, NULL, 'kg', 80, 'organic_cucumber.jpg', 1, 0, 'active', 4.3, 12, 55);

INSERT INTO `coupons` (`code`, `type`, `value`, `min_order`, `max_discount`, `usage_limit`, `used_count`, `start_date`, `end_date`, `is_active`) VALUES
('WELCOME10', 'percentage', 10.00, 200.00, 100.00, 1000, 45, '2026-01-01', '2026-12-31', 1),
('FRESH50', 'fixed', 50.00, 500.00, NULL, 500, 12, '2026-01-01', '2026-06-30', 1),
('ORGANIC20', 'percentage', 20.00, 300.00, 200.00, 200, 8, '2026-01-01', '2026-12-31', 1),
('VEGGIE100', 'fixed', 100.00, 1000.00, NULL, 100, 3, '2026-03-01', '2026-05-31', 1);

INSERT INTO `platform_settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'Vegihub'),
('site_tagline', 'Fresh Vegetables Delivered to Your Door'),
('delivery_fee', '40'),
('free_delivery_above', '500'),
('commission_rate', '10'),
('min_order_amount', '100'),
('currency', 'INR'),
('currency_symbol', '₹'),
('contact_email', 'rudranahak1000@gmail.com'),
('contact_phone', '1800-VEGIHUB');

COMMIT;
