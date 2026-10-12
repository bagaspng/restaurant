# DOKUMEN DESAIN BASIS DATA
## Sistem Pemesanan Restoran (*Restaurant Reservation & Ordering System*)

* **Kode Dokumen**: DOC-DB-003
* **Versi**: 2.1.0 (Revisi Snapshot Pemesan Langsung, Durasi 120 Menit, Penghapusan Master Pelanggan)
* **Status**: Disetujui
* **Tanggal Penyusunan**: 12 Oktober 2026
* **Penulis**: Mahasiswa Teknik Informatika (Kandidat Asesi BNSP)
* **Target Asesmen**: Skema Sertifikasi Pemrograman BNSP (Unit J.620100.021.02 — Menerapkan Akses Basis Data)

---

## 1. PENDAHULUAN
Dokumen Desain Basis Data ini menguraikan struktur fisik, kamus data (*data dictionary*), integritas referensial, optimasi indeks, serta strategi transaksi atomik untuk Sistem Pemesanan Restoran. Desain ini mengimplementasikan skema relasional 3NF (*Third Normal Form*) dengan MySQL/MariaDB menggunakan mesin penyimpanan **InnoDB** yang mendukung penuh transaksi ACID (*Atomicity, Consistency, Isolation, Durability*) dan penegakan *Foreign Key Constraints*.

Pada revisi v2.1.0, master data pelanggan dieleminasi untuk menerapkan **Prinsip Minimisasi Data** (*Data Minimization*). Identitas pemesan disimpan langsung pada baris reservasi sebagai snapshot transaksi.

---

## 2. KAMUS DATA (*DATA DICTIONARY*)

### 2.1 Tabel `users`
Menyimpan akun pengguna internal yang memiliki hak akses sistem (Administrator Lokal).

| Nama Kolom | Tipe Data | Nullable | Default | Keterangan / Batasan |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | **Primary Key** |
| `name` | VARCHAR(100) | NO | - | Nama lengkap Administrator |
| `email` | VARCHAR(100) | NO | - | **UNIQUE**, format email login |
| `password` | VARCHAR(255) | NO | - | Hash sandi Bcrypt (`$2y$10$...`) |
| `role` | ENUM('admin','staff') | NO | 'admin' | Peran otorisasi RBAC |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu pencatatan akun |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu pembaruan akun |

* **Indeks**: `idx_users_role (role)`

---

### 2.2 Tabel `restaurant_tables`
Menyimpan data fisik meja makan di area restoran beserta daya tampung tamu.

| Nama Kolom | Tipe Data | Nullable | Default | Keterangan / Batasan |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | **Primary Key** |
| `table_number` | VARCHAR(20) | NO | - | **UNIQUE**, kode meja (misal: 'T-01') |
| `capacity` | INT UNSIGNED | NO | - | CHECK (`capacity` >= 1), daya tampung kursi |
| `status` | ENUM('available','occupied') | NO | 'available' | Status ketersediaan operasional meja |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu pendaftaran meja |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu perubahan data |

---

### 2.3 Tabel `menus`
Menyimpan daftar makanan, minuman, dan makanan penutup yang ditawarkan restoran.

| Nama Kolom | Tipe Data | Nullable | Default | Keterangan / Batasan |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | **Primary Key** |
| `name` | VARCHAR(100) | NO | - | Nama hidangan |
| `category` | ENUM('food','beverage','dessert') | NO | - | Kelompok hidangan |
| `price` | DECIMAL(12,2) | NO | - | CHECK (`price` >= 0.00), harga master |
| `is_available` | BOOLEAN | NO | 1 (TRUE) | 1 = Tersedia, 0 = Habis |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu pembuatan menu |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu revisi menu |

* **Indeks**: `idx_menus_category (category)`, `idx_menus_availability (is_available)`

---

### 2.4 Tabel `reservations`
Menyimpan data transaksi reservasi meja terjadwal dengan snapshot identitas pemesan langsung dan durasi slot tetap 120 menit.

| Nama Kolom | Tipe Data | Nullable | Default | Keterangan / Batasan |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | **Primary Key** |
| `table_id` | INT UNSIGNED | NO | - | **Foreign Key** $\rightarrow$ `restaurant_tables(id)` |
| `user_id` | INT UNSIGNED | NO | - | **Foreign Key** $\rightarrow$ `users(id)` |
| `customer_name` | VARCHAR(100) | NO | - | Snapshot nama lengkap pemesan |
| `customer_phone` | VARCHAR(20) | NO | - | Snapshot nomor telepon pemesan |
| `start_time` | DATETIME | NO | - | Waktu mulai slot reservasi |
| `end_time` | DATETIME | NO | - | Waktu selesai slot (start + 120 mnt) |
| `guest_count` | INT UNSIGNED | YES | NULL | Riwayat tamu lampau (Nullable untuk kompatibilitas) |
| `status` | ENUM('confirmed','completed','cancelled') | NO | 'confirmed' | Status siklus reservasi |
| `notes` | TEXT | YES | NULL | Catatan terstruktur format JSON |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu reservasi dicatat |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu perubahan reservasi |

* **Constraints**: CHECK (`end_time` > `start_time`).
* **Indeks Optimasi**:
  * `idx_reservations_collision (table_id, status, start_time, end_time)` — Indeks gabungan B-Tree untuk akselerasi kueri deteksi bentrok jadwal interval 120 menit.
  * `idx_reservations_date_range (start_time, end_time)` — Akselerasi filter laporan periode.
  * `idx_reservations_status (status)` — Akselerasi filter status operasional.
  * `idx_reservations_customer_name (customer_name)` — Akselerasi pencarian pemesan.

---

### 2.5 Tabel `orders`
Menyimpan header transaksi pesanan hidangan di restoran.

| Nama Kolom | Tipe Data | Nullable | Default | Keterangan / Batasan |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | **Primary Key** |
| `reservation_id` | INT UNSIGNED | YES | NULL | **Foreign Key** $\rightarrow$ `reservations(id)` |
| `table_id` | INT UNSIGNED | NO | - | **Foreign Key** $\rightarrow$ `restaurant_tables(id)` |
| `user_id` | INT UNSIGNED | NO | - | **Foreign Key** $\rightarrow$ `users(id)` |
| `total_amount` | DECIMAL(12,2) | NO | 0.00 | Nilai grand total terhitung di server |
| `status` | ENUM('pending','completed','cancelled') | NO | 'pending' | Status pembayaran transaksi |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu pembukaan pesanan |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu pelunasan/perubahan |

---

### 2.6 Tabel `order_items`
Menyimpan rincian item pesanan dengan teknik *Price Snapshotting*.

| Nama Kolom | Tipe Data | Nullable | Default | Keterangan / Batasan |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | **Primary Key** |
| `order_id` | INT UNSIGNED | NO | - | **Foreign Key** $\rightarrow$ `orders(id)` (`ON DELETE CASCADE`) |
| `menu_id` | INT UNSIGNED | NO | - | **Foreign Key** $\rightarrow$ `menus(id)` (`ON DELETE RESTRICT`) |
| `item_name` | VARCHAR(100) | NO | - | **Price Snapshot**: Salinan nama menu saat transaksi |
| `price_at_order` | DECIMAL(12,2) | NO | - | **Price Snapshot**: Salinan harga riil menu saat dipesan |
| `quantity` | INT UNSIGNED | NO | 1 | Kuantitas porsi hidangan (>= 1) |
| `subtotal` | DECIMAL(12,2) | NO | - | Hasil komputasi server: `price_at_order * quantity` |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu penambahan item |

---

## 3. RELASI ANTAR TABEL DAN INTEGRITAS REFERENSIAL

```
users (1) ------------------------< (N) reservations
users (1) ------------------------< (N) orders
restaurant_tables (1) ------------< (N) reservations
restaurant_tables (1) ------------< (N) orders
reservations (0..1) --------------< (N) orders
orders (1) -----------------------< (N) order_items [ON DELETE CASCADE]
menus (1) ------------------------< (N) order_items [ON DELETE RESTRICT]
```

### Aturan Penghapusan (*Referential Actions*)
1. **Master Table (`restaurant_tables`, `menus`, `users`)**:
   * Menggunakan `ON DELETE RESTRICT` dan `ON UPDATE CASCADE`. Mencegah meja atau hidangan terhapus jika memiliki catatan riwayat transaksi.
2. **Detail Transaksi (`order_items`)**:
   * Menggunakan `ON DELETE CASCADE` terhadap `orders(id)`. Jika satu berkas pesanan dibatalkan/dihapus total, seluruh item detail terhapus serentak secara bersih.

---

## 4. STRATEGI MIGRASI BASIS DATA (DARI V1 KE V2)

Untuk memastikan riwayat data reservasi yang ada tidak hilang ketika menghapus tabel master `customers`:
1. Kolom `customer_name` dan `customer_phone` ditambahkan pada `reservations`.
2. Data disalin dari `customers` ke `reservations`:
   ```sql
   UPDATE reservations r 
   JOIN customers c ON r.customer_id = c.id 
   SET r.customer_name = c.name, r.customer_phone = c.phone;
   ```
3. Kolom dijadikan `NOT NULL`.
4. Kolom `guest_count` diubah menjadi `NULLABLE` (menghapus check constraint `chk_reservation_guest_count`) agar riwayat jumlah tamu lampau tetap aman, namun tidak lagi dituntut pada alur reservasi berbasis kapasitas meja yang baru.
5. Foreign key `fk_reservations_customer` dilepas dan kolom `customer_id` dihapus.
6. Tabel `customers` di-*drop*.

---

## 5. OPTIMASI KUERI & TRANSAKSI ATOMIK

### 5.1 Kueri Deteksi Bentrok Jadwal (BR-02)
Kueri ini memanfaatkan indeks gabungan `idx_reservations_collision`:
```sql
SELECT id, table_id, start_time, end_time, status
FROM reservations
WHERE table_id = :table_id
  AND status = 'confirmed'
  AND start_time < :new_end_time
  AND end_time > :new_start_time;
```
Beban pencarian berkurang dari $O(N)$ (Full Table Scan) menjadi $O(\log N)$ (Index Range Scan).

### 5.2 Kueri Transaksi Pembuatan Pesanan Atomik
```sql
START TRANSACTION;
INSERT INTO orders (table_id, user_id, total_amount, status) VALUES (...);
SET @new_order_id = LAST_INSERT_ID();

INSERT INTO order_items (order_id, menu_id, item_name, price_at_order, quantity, subtotal)
VALUES (@new_order_id, :menu_id, :item_name, :price_at_order, :quantity, :subtotal);

COMMIT; -- Rollback jika salah satu query gagal
```
