CREATE DATABASE IF NOT EXISTS `tnmshop_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tnmshop_db`;

CREATE TABLE IF NOT EXISTS `tnmshop_products` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sku` VARCHAR(64) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'THB',
  `image_url` VARCHAR(2048) NULL,
  `stock_qty` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tnmshop_products_sku_unique` (`sku`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `tnmshop_orders` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR(64) NOT NULL,
  `status` VARCHAR(40) NOT NULL,
  `customer_name` VARCHAR(255) NOT NULL,
  `customer_email` VARCHAR(255) NOT NULL,
  `customer_phone` VARCHAR(50) NULL,
  `shipping_address` TEXT NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `tax` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `shipping_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'THB',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tnmshop_orders_order_number_unique` (`order_number`),
  KEY `tnmshop_orders_status_index` (`status`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `tnmshop_order_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `sku` VARCHAR(64) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `quantity` INT UNSIGNED NOT NULL,
  `line_total` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tnmshop_order_items_order_id_foreign` (`order_id`),
  KEY `tnmshop_order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `tnmshop_order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `tnmshop_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tnmshop_order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `tnmshop_products` (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `tnmshop_payment_intents` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `intent_id` VARCHAR(40) NOT NULL,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `status` VARCHAR(40) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'THB',
  `return_url` VARCHAR(2048) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tnmshop_payment_intents_intent_id_unique` (`intent_id`),
  KEY `tnmshop_payment_intents_status_index` (`status`),
  CONSTRAINT `tnmshop_payment_intents_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `tnmshop_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
