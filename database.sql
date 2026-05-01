-- E-Ticaret Hosting Database (Fresh Install)
-- Use this on a new/empty database.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+03:00";
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `returns`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `coupons`;
DROP TABLE IF EXISTS `blog_posts`;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `newsletter_emails`;
DROP TABLE IF EXISTS `slider_banners`;
DROP TABLE IF EXISTS `gallery`;
DROP TABLE IF EXISTS `site_settings`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user','admin') NOT NULL DEFAULT 'user',
  `phone` VARCHAR(50) NOT NULL DEFAULT '',
  `address` TEXT,
  `billing_address` TEXT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `image` VARCHAR(500) NOT NULL DEFAULT '',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `short_desc` VARCHAR(500) NOT NULL DEFAULT '',
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL,
  `sale_price` DECIMAL(10,2) DEFAULT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `sku` VARCHAR(100) NOT NULL DEFAULT '',
  `category_id` INT DEFAULT NULL,
  `images` JSON DEFAULT NULL,
  `video_url` VARCHAR(500) NOT NULL DEFAULT '',
  `featured` TINYINT(1) NOT NULL DEFAULT 0,
  `best_seller` TINYINT(1) NOT NULL DEFAULT 0,
  `new_product` TINYINT(1) NOT NULL DEFAULT 0,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `variants` JSON DEFAULT NULL,
  `weight` DECIMAL(10,2) DEFAULT NULL,
  `dimensions` JSON DEFAULT NULL,
  `color_code` VARCHAR(50) NOT NULL DEFAULT '',
  `material` VARCHAR(255) NOT NULL DEFAULT '',
  `warranty_period` INT NOT NULL DEFAULT 12,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) DEFAULT NULL,
  `user_id` INT NOT NULL,
  `status` ENUM('pending','confirmed','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `payment_method` VARCHAR(50) NOT NULL DEFAULT '',
  `payment_status` ENUM('pending','paid','notified','cancelled') NOT NULL DEFAULT 'pending',
  `total` DECIMAL(10,2) NOT NULL,
  `shipping_address` TEXT,
  `billing_address` TEXT,
  `tracking_number` VARCHAR(100) NOT NULL DEFAULT '',
  `notes` TEXT,
  `payment_notification` JSON DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_orders_order_number` (`order_number`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `variants` JSON DEFAULT NULL,
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `discount_type` ENUM('percent','fixed') NOT NULL,
  `discount_value` DECIMAL(10,2) NOT NULL,
  `min_order_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `max_uses` INT DEFAULT NULL,
  `used_count` INT NOT NULL DEFAULT 0,
  `valid_from` TIMESTAMP NULL,
  `valid_until` TIMESTAMP NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `blog_posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(500) NOT NULL,
  `slug` VARCHAR(500) NOT NULL UNIQUE,
  `excerpt` TEXT,
  `content` TEXT,
  `category` VARCHAR(100) NOT NULL DEFAULT 'General',
  `image` VARCHAR(500) NOT NULL DEFAULT '',
  `author` VARCHAR(255) NOT NULL DEFAULT 'E-Magaza Editor',
  `author_name` VARCHAR(255) NOT NULL DEFAULT 'E-Magaza Editor',
  `views` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `user_name` VARCHAR(255) NOT NULL DEFAULT '',
  `rating` INT NOT NULL,
  `comment` TEXT,
  `approved` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `subject` VARCHAR(255) NOT NULL DEFAULT 'General',
  `message` TEXT NOT NULL,
  `read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `newsletter_emails` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `subscribed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `site_settings` (
  `id` INT NOT NULL PRIMARY KEY DEFAULT 1,
  `site_name` VARCHAR(255) NOT NULL DEFAULT 'E-Magaza',
  `logo_text` VARCHAR(50) NOT NULL DEFAULT 'E',
  `logo_url` VARCHAR(500) NOT NULL DEFAULT '',
  `favicon` VARCHAR(500) NOT NULL DEFAULT '',
  `phone` VARCHAR(50) NOT NULL DEFAULT '',
  `email` VARCHAR(255) NOT NULL DEFAULT '',
  `address` TEXT,
  `whatsapp` VARCHAR(50) NOT NULL DEFAULT '',
  `social` JSON DEFAULT NULL,
  `meta_title` VARCHAR(500) NOT NULL DEFAULT '',
  `meta_description` TEXT,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'TRY',
  `free_shipping_limit` DECIMAL(10,2) NOT NULL DEFAULT 500,
  `shipping_cost` DECIMAL(10,2) NOT NULL DEFAULT 49.90,
  `bank_accounts` JSON DEFAULT NULL,
  `menu_items` JSON DEFAULT NULL,
  `footer_text` TEXT,
  `copyright_text` VARCHAR(500) NOT NULL DEFAULT '',
  `meta_keywords` TEXT,
  `paytr_merchant_id` VARCHAR(100) NOT NULL DEFAULT '',
  `paytr_merchant_key` VARCHAR(100) NOT NULL DEFAULT '',
  `paytr_merchant_salt` VARCHAR(100) NOT NULL DEFAULT '',
  `paytr_test_mode` TINYINT(1) NOT NULL DEFAULT 1,
  `smtp_host` VARCHAR(255) NOT NULL DEFAULT '',
  `smtp_port` INT NOT NULL DEFAULT 587,
  `smtp_user` VARCHAR(255) NOT NULL DEFAULT '',
  `smtp_pass` VARCHAR(255) NOT NULL DEFAULT '',
  `smtp_from_name` VARCHAR(255) NOT NULL DEFAULT '',
  `smtp_from_email` VARCHAR(255) NOT NULL DEFAULT '',
  `smtp_secure` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `slider_banners` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `subtitle` VARCHAR(500) NOT NULL DEFAULT '',
  `cta` VARCHAR(100) NOT NULL DEFAULT 'Explore',
  `gradient` VARCHAR(100) NOT NULL DEFAULT 'from-primary-500 to-primary-700',
  `image` VARCHAR(500) NOT NULL DEFAULT '',
  `link` VARCHAR(500) NOT NULL DEFAULT '/products',
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `order` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `returns` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `order_id` INT DEFAULT NULL,
  `user_name` VARCHAR(255) NOT NULL DEFAULT '',
  `user_email` VARCHAR(255) NOT NULL DEFAULT '',
  `reason` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `refund_type` ENUM('bank','credit') NOT NULL DEFAULT 'bank',
  `status` ENUM('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_returns_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_returns_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `gallery` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `image_url` VARCHAR(500) NOT NULL,
  `media_type` ENUM('image','video') NOT NULL DEFAULT 'image',
  `thumbnail_url` VARCHAR(500) NOT NULL DEFAULT '',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Admin', 'admin@eticaret.com', '\$2y\$10\$vHE17SSf6DrNSXAen8bLTeUHVDC2l.qmlWpZfqysS868yy4KK/Lu.', 'admin')
ON DUPLICATE KEY UPDATE `email` = VALUES(`email`);

INSERT INTO `categories` (`name`, `slug`, `image`) VALUES
('Elektronik', 'elektronik', 'https://picsum.photos/seed/electronics/400/300'),
('Giyim', 'giyim', 'https://picsum.photos/seed/clothing/400/300'),
('Ev-Yasam', 'ev-yasam', 'https://picsum.photos/seed/home/400/300'),
('Spor', 'spor', 'https://picsum.photos/seed/sports/400/300')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT INTO `products` (`name`, `slug`, `short_desc`, `description`, `price`, `sale_price`, `stock`, `sku`, `category_id`, `images`, `featured`, `best_seller`, `new_product`, `active`, `material`) VALUES
('Kablosuz Kulaklik Pro', 'kablosuz-kulaklik-pro', 'Aktif gurultu engelleme ve uzun pil omru.', 'Gunluk kullanim icin dengeli ses, hizli sarj ve hafif tasarim sunar.', 2499.00, 1999.00, 35, 'ELK-001', 1, '[\"https://picsum.photos/seed/headphone1/900/700\",\"https://picsum.photos/seed/headphone2/900/700\"]', 1, 1, 1, 1, 'TechSound'),
('Akilli Saat S3', 'akilli-saat-s3', 'Saglik takibi ve su gecirmez govde.', 'Adim, nabiz, uyku takibi ve telefon bildirimleri icin modern bir saat.', 3299.00, NULL, 22, 'ELK-002', 1, '[\"https://picsum.photos/seed/watch1/900/700\",\"https://picsum.photos/seed/watch2/900/700\"]', 1, 0, 1, 1, 'Chronix'),
('Oversize Hoodie', 'oversize-hoodie', 'Yumusak dokulu ve sicak tutan hoodie.', 'Sehir stili ile rahatligi birlestiren unisex kesim.', 899.00, 749.00, 58, 'GYM-001', 2, '[\"https://picsum.photos/seed/hoodie1/900/700\",\"https://picsum.photos/seed/hoodie2/900/700\"]', 0, 1, 0, 1, 'UrbanWeave'),
('Spor Tayt Flex', 'spor-tayt-flex', 'Yuksek bel ve esnek kumas.', 'Kosu, fitness ve gunluk aktif yasam icin destekleyici tasarim.', 699.00, NULL, 41, 'SPR-001', 4, '[\"https://picsum.photos/seed/leggings1/900/700\",\"https://picsum.photos/seed/leggings2/900/700\"]', 0, 0, 1, 1, 'MoveFit'),
('Dekoratif Masa Lambasi', 'dekoratif-masa-lambasi', 'Sicak beyaz LED ve modern tasarim.', 'Calisma odasi ve salon icin ayarlanabilir aydinlatma.', 1199.00, 999.00, 19, 'EVY-001', 3, '[\"https://picsum.photos/seed/lamp1/900/700\",\"https://picsum.photos/seed/lamp2/900/700\"]', 1, 0, 0, 1, 'LumaHome'),
('Yoga Mat Premium', 'yoga-mat-premium', 'Kaymaz yuzey ve 6mm kalinlik.', 'Evde veya salonda konforlu antrenman deneyimi saglar.', 549.00, 449.00, 67, 'SPR-002', 4, '[\"https://picsum.photos/seed/yoga1/900/700\",\"https://picsum.photos/seed/yoga2/900/700\"]', 0, 1, 0, 1, 'MoveFit')
ON DUPLICATE KEY UPDATE `price` = VALUES(`price`), `stock` = VALUES(`stock`), `active` = VALUES(`active`);

INSERT INTO `blog_posts` (`title`, `slug`, `excerpt`, `content`, `category`, `image`, `author`, `author_name`, `views`, `status`) VALUES
('Yaz Alisveris Rehberi: Dogru Urun Secimi', 'yaz-alisveris-rehberi-dogru-urun-secimi', 'Yaz sezonunda ihtiyaciniza gore urun secerken dikkat etmeniz gerekenleri derledik.', 'Yaz aylarinda konfor, performans ve fiyat dengesini yakalamak icin urun etiketlerini, malzeme kalitesini ve kullanim alanlarini birlikte degerlendirmek gerekir. Bu yazida en cok tercih edilen kategorilerde pratik secim ipuclari bulabilirsiniz.', 'Rehber', 'https://picsum.photos/seed/blogsummer/1200/800', 'Admin', 'Admin', 245, 1),
('Evde Calisma Alanini Verimli Hale Getirmenin 5 Yolu', 'evde-calisma-alanini-verimli-hale-getirmenin-5-yolu', 'Kucuk degisikliklerle calisma alaninizi daha odakli ve keyifli bir hale getirin.', 'Dogru aydinlatma, ergonomik aksesuarlar ve sade bir masa duzeni; odak suresini ciddi oranda artirir. Yazida adim adim uygulanabilir bir duzenleme listesi paylastik.', 'Yasam', 'https://picsum.photos/seed/blogworkspace/1200/800', 'Admin', 'Admin', 198, 1),
('Spor Rutini Kurarken Yapilan 7 Yaygin Hata', 'spor-rutini-kurarken-yapilan-7-yaygin-hata', 'Motivasyonu dusuren klasik hatalari erken fark ederek daha surdurulebilir bir rutin olusturun.', 'Hedefi fazla buyutmek, duzensiz takip ve yanlis ekipman secimi gibi problemler sporu birakmaya yol acabilir. Bu yazida surekli bir rutin kurmak icin uygulanabilir oneriler var.', 'Spor', 'https://picsum.photos/seed/blogfitness/1200/800', 'Admin', 'Admin', 173, 1)
ON DUPLICATE KEY UPDATE `excerpt` = VALUES(`excerpt`), `status` = VALUES(`status`);

INSERT INTO `coupons` (`code`, `discount_type`, `discount_value`, `min_order_amount`, `max_uses`, `active`) VALUES
('HOSGELDIN', 'percent', 10, 500, NULL, 1),
('HOSGELDIN50', 'fixed', 50, 200, NULL, 1)
ON DUPLICATE KEY UPDATE `discount_value` = VALUES(`discount_value`), `active` = VALUES(`active`);

INSERT INTO `site_settings` (`id`, `site_name`, `phone`, `email`, `address`, `currency`, `free_shipping_limit`, `shipping_cost`, `bank_accounts`, `social`, `menu_items`) VALUES
(1, 'E-Magaza', '0850 555 00 00', 'info@eticaret.com', 'Ataturk Cad. No: 123, Kadikoy, Istanbul', 'TRY', 500, 49.90,
 '[{"bank_name":"Ziraat Bankasi","account_name":"E-Ticaret A.S.","iban":"TR12 3456 7890 1234 5678 9012 34"}]',
 '{}',
 '[]')
ON DUPLICATE KEY UPDATE
`site_name` = VALUES(`site_name`),
`phone` = VALUES(`phone`),
`email` = VALUES(`email`),
`address` = VALUES(`address`),
`currency` = VALUES(`currency`);

INSERT INTO `slider_banners` (`title`, `subtitle`, `cta`, `gradient`, `link`, `active`, `order`) VALUES
('Yaz Indirimleri', 'Yeni sezon urunlerinde firsatlar', 'Alisverise Basla', 'from-cyan-500 via-blue-600 to-indigo-700', '/products', 1, 1),
('Yeni Sezon', 'Trend urunler ve ozel kampanyalar', 'Kesfet', 'from-violet-600 via-purple-600 to-pink-600', '/products', 1, 2),
('Elektronik Firsatlari', 'Teknoloji urunlerinde avantajli fiyatlar', 'Incele', 'from-emerald-500 via-teal-600 to-cyan-600', '/products?category=elektronik', 1, 3);

INSERT INTO `gallery` (`title`, `image_url`, `media_type`, `thumbnail_url`) VALUES
('Uretim Atolyesinden Kare', 'https://picsum.photos/seed/gallery-workshop/1200/900', 'image', ''),
('Paketleme Surecimiz', 'https://picsum.photos/seed/gallery-pack/1200/900', 'image', ''),
('Urun Tanitim Videosu', 'https://www.w3schools.com/html/mov_bbb.mp4', 'video', 'https://picsum.photos/seed/gallery-video/1200/900')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

COMMIT;
