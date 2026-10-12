-- =============================================================================
-- SISTEM PEMESANAN RESTORAN (RESTAURANT RESERVATION & ORDERING SYSTEM)
-- Data awal untuk demonstrasi sistem restoran
-- File: database/seeders.sql
-- =============================================================================

USE `restaurant_db`;

-- 1. SEED DATA PENGGUNA (users) - Administrator Utama (Sistem Lokal)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Administrator Utama', 'admin@resto.local', '$2y$10$UavOkcdbNeAiGqdDvbDuf.biCycH9lVJ3uIA74NeDO625XhdMo10a', 'admin')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 2. SEED DATA MEJA RESTORAN (restaurant_tables)
INSERT INTO `restaurant_tables` (`id`, `table_number`, `capacity`, `status`) VALUES
(1, 'T-01', 2, 'available'),
(2, 'T-02', 4, 'available'),
(3, 'T-03', 4, 'available'),
(4, 'T-04', 6, 'available'),
(5, 'T-05', 8, 'available')
ON DUPLICATE KEY UPDATE `capacity` = VALUES(`capacity`);

-- 3. SEED DATA KATALOG MENU (menus)
INSERT INTO `menus` (`id`, `name`, `category`, `price`, `is_available`) VALUES
(1, 'Nasi Goreng Spesial', 'food', 28000.00, 1),
(2, 'Ayam Bakar Madu', 'food', 32000.00, 1),
(3, 'Mie Ayam Jamur', 'food', 24000.00, 1),
(4, 'Sate Ayam Madura', 'food', 30000.00, 1),
(5, 'Es Teh Manis', 'beverage', 6000.00, 1),
(6, 'Jus Alpukat Kocok', 'beverage', 18000.00, 1),
(7, 'Kopi Susu Gula Aren', 'beverage', 15000.00, 1),
(8, 'Pisang Goreng Keju', 'dessert', 16000.00, 1)
ON DUPLICATE KEY UPDATE `price` = VALUES(`price`);

-- 4. SEED DATA RESERVASI (reservations) dengan Snapshot Identitas & Slot 120 Menit
-- Slot 1: Meja T-02 terjadwal confirmed hari ini jam 12:00 - 14:00 (digunakan untuk pengujian bentrok)
-- Slot 2: Meja T-04 terjadwal confirmed hari ini jam 18:00 - 20:00
-- Slot 3: Meja T-03 reservasi kemarin (completed)
INSERT INTO `reservations` (`id`, `table_id`, `user_id`, `customer_name`, `customer_phone`, `start_time`, `end_time`, `guest_count`, `status`, `notes`) VALUES
(1, 2, 1, 'Ahmad Sanusi', '081234567890', '2026-10-12 12:00:00', '2026-10-12 14:00:00', 4, 'confirmed', '{"requests":["window_seat"],"other_note":"Makan siang kantor"}'),
(2, 4, 1, 'Citra Lestari', '081987654321', '2026-10-12 18:00:00', '2026-10-12 20:00:00', 6, 'confirmed', '{"requests":["baby_chair"],"other_note":"Makan malam keluarga"}'),
(3, 3, 1, 'Dimas Pratama', '085712345678', '2026-10-11 12:00:00', '2026-10-11 14:00:00', 4, 'completed', '{"requests":["window_seat"],"other_note":"Reservasi kemarin telah selesai"}')
ON DUPLICATE KEY UPDATE `customer_name` = VALUES(`customer_name`), `customer_phone` = VALUES(`customer_phone`);

-- 5. SEED DATA PESANAN (orders)
-- Pesanan 1: Ditautkan ke Reservasi ID 3 (sudah selesai dan lunas)
-- Subtotal: 2 x Nasi Goreng (56.000) + 2 x Es Teh (12.000) = 68.000
INSERT INTO `orders` (`id`, `reservation_id`, `table_id`, `user_id`, `total_amount`, `status`, `created_at`) VALUES
(1, 3, 3, 1, 68000.00, 'completed', '2026-10-11 13:15:00')
ON DUPLICATE KEY UPDATE `total_amount` = VALUES(`total_amount`);

-- 6. SEED DATA ITEM PESANAN (order_items) dengan Price Snapshotting
INSERT INTO `order_items` (`id`, `order_id`, `menu_id`, `item_name`, `price_at_order`, `quantity`, `subtotal`, `created_at`) VALUES
(1, 1, 1, 'Nasi Goreng Spesial', 28000.00, 2, 56000.00, '2026-10-11 13:15:00'),
(2, 1, 5, 'Es Teh Manis', 6000.00, 2, 12000.00, '2026-10-11 13:15:00')
ON DUPLICATE KEY UPDATE `price_at_order` = VALUES(`price_at_order`);
