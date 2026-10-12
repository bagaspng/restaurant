-- =============================================================================
-- MIGRASI BASIS DATA: REVISI FITUR RESERVASI (V2.0)
-- 1. Denormalisasi data pelanggan langsung ke tabel reservations (Snapshot Identitas)
-- 2. Migrasi data pelanggan eksisting agar riwayat reservasi tetap utuh
-- 3. Penghapusan tabel master customers
-- 4. Pengubahan guest_count menjadi nullable (kompatibilitas riwayat)
-- =============================================================================

USE `restaurant_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- Langkah 1: Tambahkan kolom customer_name dan customer_phone ke reservations
ALTER TABLE `reservations` 
  ADD COLUMN `customer_name` VARCHAR(100) NULL AFTER `user_id`,
  ADD COLUMN `customer_phone` VARCHAR(20) NULL AFTER `customer_name`;

-- Langkah 2: Migrasikan data pelanggan eksisting dari tabel customers
UPDATE `reservations` r 
JOIN `customers` c ON r.customer_id = c.id 
SET r.customer_name = c.name, r.customer_phone = c.phone;

-- Fallback jika ada baris tanpa relasi
UPDATE `reservations` 
SET `customer_name` = 'Tamu Restoran', `customer_phone` = '081234567890' 
WHERE `customer_name` IS NULL;

-- Langkah 3: Tetapkan kolom customer_name dan customer_phone sebagai NOT NULL
ALTER TABLE `reservations` 
  MODIFY COLUMN `customer_name` VARCHAR(100) NOT NULL,
  MODIFY COLUMN `customer_phone` VARCHAR(20) NOT NULL;

-- Langkah 4: Ubah guest_count menjadi NULLABLE untuk kompatibilitas data lama
ALTER TABLE `reservations` 
  MODIFY COLUMN `guest_count` INT UNSIGNED NULL DEFAULT NULL;

-- Hapus check constraint guest_count jika ada
ALTER TABLE `reservations` DROP CHECK `chk_reservation_guest_count`;

-- Langkah 5: Hapus Foreign Key relasi ke customers dan kolom customer_id
ALTER TABLE `reservations` DROP FOREIGN KEY `fk_reservations_customer`;
ALTER TABLE `reservations` DROP COLUMN `customer_id`;

-- Langkah 6: Hapus tabel master data customers
DROP TABLE IF EXISTS `customers`;

-- Langkah 7: Tambahkan indeks pencarian nama pelanggan pada reservations
ALTER TABLE `reservations` ADD INDEX `idx_reservations_customer_name` (`customer_name`);

SET FOREIGN_KEY_CHECKS = 1;
