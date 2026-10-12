-- =============================================================================
-- SISTEM PEMESANAN RESTORAN (RESTAURANT RESERVATION & ORDERING SYSTEM)
-- Skrip DDL Skema Basis Data (MySQL / MariaDB)
-- File: database/schema.sql
-- =============================================================================

-- 1. Pembuatan Database & Pengaturan Charset
CREATE DATABASE IF NOT EXISTS `restaurant_db`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `restaurant_db`;

-- Nonaktifkan pengecekan foreign key sementara saat reset skema
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `reservations`;
DROP TABLE IF EXISTS `menus`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `restaurant_tables`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- TABEL 1: users
-- Menyimpan kredensial pengguna internal (Admin & Staf Kasir/Pelayan)
-- =============================================================================
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL COMMENT 'Nama lengkap pengguna',
    `email` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Email pengguna untuk login (unik)',
    `password` VARCHAR(255) NOT NULL COMMENT 'Hash password terenkripsi (Bcrypt/Argon2id)',
    `role` ENUM('admin', 'staff') NOT NULL DEFAULT 'staff' COMMENT 'Peran pengguna dalam RBAC',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABEL 2: restaurant_tables
-- Menyimpan nomor meja fisik, kapasitas kursi, dan status operasional
-- =============================================================================
CREATE TABLE `restaurant_tables` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `table_number` VARCHAR(20) NOT NULL UNIQUE COMMENT 'Nomor/kode meja fisik, misal: T-01',
    `capacity` INT UNSIGNED NOT NULL COMMENT 'Kapasitas maksimal tamu (>= 1)',
    `status` ENUM('available', 'occupied') NOT NULL DEFAULT 'available' COMMENT 'Status operasional meja',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `chk_table_capacity` CHECK (`capacity` >= 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABEL 3: menus
-- Menyimpan master katalog makanan, minuman, dan hidangan penutup
-- =============================================================================
CREATE TABLE `menus` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL COMMENT 'Nama item menu',
    `category` ENUM('food', 'beverage', 'dessert') NOT NULL COMMENT 'Kategori menu',
    `price` DECIMAL(12,2) NOT NULL COMMENT 'Harga menu terkini (DECIMAL presisi moneter)',
    `is_available` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Status ketersediaan bahan di dapur',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `chk_menu_price` CHECK (`price` >= 0.00),
    INDEX `idx_menus_category` (`category`),
    INDEX `idx_menus_availability` (`is_available`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABEL 4: reservations
-- Transaksi reservasi meja dengan snapshot nama dan kontak pemesan
-- =============================================================================
CREATE TABLE `reservations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `table_id` INT UNSIGNED NOT NULL COMMENT 'Relasi ke meja yang dialokasikan',
    `user_id` INT UNSIGNED NOT NULL COMMENT 'Relasi ke staf/admin yang mencatat',
    `customer_name` VARCHAR(100) NOT NULL COMMENT 'Nama pemesan (Snapshot saat reservasi dibuat)',
    `customer_phone` VARCHAR(20) NOT NULL COMMENT 'Nomor telepon pemesan (Snapshot kontak)',
    `start_time` DATETIME NOT NULL COMMENT 'Waktu mulai slot reservasi',
    `end_time` DATETIME NOT NULL COMMENT 'Waktu selesai slot reservasi (start_time + 120 menit)',
    `guest_count` INT UNSIGNED NULL DEFAULT NULL COMMENT 'Riwayat jumlah tamu (Opsional/Kompatibilitas)',
    `status` ENUM('confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'confirmed' COMMENT 'Status operasional reservasi',
    `notes` TEXT NULL COMMENT 'Catatan khusus terstruktur JSON (baby chair, jendela, dll)',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Constraint Integritas Data
    CONSTRAINT `chk_reservation_time_range` CHECK (`end_time` > `start_time`),
    
    -- Foreign Keys
    CONSTRAINT `fk_reservations_table` FOREIGN KEY (`table_id`) 
        REFERENCES `restaurant_tables` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_reservations_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,

    -- Indeks Optimasi Query Bentrok Jadwal & Laporan
    INDEX `idx_reservations_collision` (`table_id`, `status`, `start_time`, `end_time`),
    INDEX `idx_reservations_date_range` (`start_time`, `end_time`),
    INDEX `idx_reservations_status` (`status`),
    INDEX `idx_reservations_customer_name` (`customer_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABEL 6: orders
-- Menyimpan header pesanan transaksi
-- =============================================================================
CREATE TABLE `orders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `reservation_id` INT UNSIGNED NULL COMMENT 'Tautan reservasi (NULL jika tamu walk-in)',
    `table_id` INT UNSIGNED NOT NULL COMMENT 'Meja yang ditempati pesanan',
    `user_id` INT UNSIGNED NOT NULL COMMENT 'Staf/kasir yang memproses pesanan',
    `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Grand total hasil kalkulasi server-side',
    `status` ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'pending' COMMENT 'Status pembayaran/pesanan',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Constraint & Foreign Keys
    CONSTRAINT `chk_order_total_amount` CHECK (`total_amount` >= 0.00),
    CONSTRAINT `fk_orders_reservation` FOREIGN KEY (`reservation_id`) 
        REFERENCES `reservations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_orders_table` FOREIGN KEY (`table_id`) 
        REFERENCES `restaurant_tables` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,

    -- Indeks Laporan
    INDEX `idx_orders_status` (`status`),
    INDEX `idx_orders_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABEL 7: order_items
-- Menyimpan rincian item pesanan (dengan Price Snapshotting)
-- =============================================================================
CREATE TABLE `order_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL COMMENT 'Relasi ke header pesanan',
    `menu_id` INT UNSIGNED NOT NULL COMMENT 'Relasi ke master menu',
    `item_name` VARCHAR(100) NOT NULL COMMENT 'Snapshot nama menu saat pemesanan',
    `price_at_order` DECIMAL(12,2) NOT NULL COMMENT 'Snapshot harga satuan saat pemesanan',
    `quantity` INT UNSIGNED NOT NULL COMMENT 'Jumlah porsi dipesan (>= 1)',
    `subtotal` DECIMAL(12,2) NOT NULL COMMENT 'price_at_order * quantity (kalkulasi server)',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Constraint Integritas
    CONSTRAINT `chk_order_item_qty` CHECK (`quantity` >= 1),
    CONSTRAINT `chk_order_item_price` CHECK (`price_at_order` >= 0.00),
    CONSTRAINT `chk_order_item_subtotal` CHECK (`subtotal` >= 0.00),

    -- Foreign Keys
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) 
        REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_order_items_menu` FOREIGN KEY (`menu_id`) 
        REFERENCES `menus` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,

    -- Indeks
    INDEX `idx_order_items_order_id` (`order_id`),
    INDEX `idx_order_items_menu_id` (`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
