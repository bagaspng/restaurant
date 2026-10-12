# DOKUMEN DESAIN BASIS DATA
## Sistem Pemesanan Restoran (*Restaurant Reservation & Ordering System*)

* **Kode Dokumen**: DOC-DB-003
* **Versi**: 1.0.0
* **Status**: Disetujui
* **Tanggal Penyusunan**: 12 Oktober 2026
* **Penulis**: Mahasiswa Teknik Informatika (Kandidat Asesi BNSP)
* **Target Asesmen**: Skema Sertifikasi Pemrograman BNSP (Unit J.620100.021.02 — Menerapkan Akses Basis Data)

---

## 1. PENDAHULUAN
Dokumen Desain Basis Data ini menguraikan struktur fisik, kamus data (*data dictionary*), integritas referensial, optimasi indeks, serta strategi transaksi atomik untuk Sistem Pemesanan Restoran. Desain ini mengimplementasikan skema relasional 3NF (*Third Normal Form*) dengan MySQL/MariaDB menggunakan mesin penyimpanan **InnoDB** yang mendukung penuh transaksi ACID (*Atomicity, Consistency, Isolation, Durability*) dan penegakan *Foreign Key Constraints*.

---

## 2. KAMUS DATA (*DATA DICTIONARY*)

### 2.1 Tabel `users`
Menyimpan akun pengguna internal yang memiliki hak akses sistem (Admin dan Staf).

| Nama Kolom | Tipe Data | Nullable | Default | Keterangan / Batasan |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | **Primary Key** |
| `name` | VARCHAR(100) | NO | - | Nama lengkap staf/admin |
| `email` | VARCHAR(100) | NO | - | **UNIQUE**, format email login |
| `password` | VARCHAR(255) | NO | - | Hash sandi Bcrypt (`$2y$10$...`) |
| `role` | ENUM('admin','staff') | NO | 'staff' | Peran otorisasi RBAC |
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
| `capacity` | INT UNSIGNED | NO | - | CHECK (`capacity` >= 1), jumlah kursi |
| `status` | ENUM('available','occupied') | NO | 'available' | Status ketersediaan operasional meja |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu pendaftaran meja |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu perubahan data |

---

### 2.3 Tabel `customers`
Menyimpan data master pelanggan yang melakukan pemesanan meja atau pesanan makanan.

| Nama Kolom | Tipe Data | Nullable | Default | Keterangan / Batasan |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | **Primary Key** |
| `name` | VARCHAR(100) | NO | - | Nama pemesan |
| `phone` | VARCHAR(20) | NO | - | Nomor kontak teks (10-15 digit) |
| `notes` | TEXT | YES | NULL | Catatan riwayat (alergi/preferensi) |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu pendaftaran pelanggan |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu update data |

* **Indeks**: `idx_customers_phone (phone)`, `idx_customers_name (name)`

---

### 2.4 Tabel `menus`
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

### 2.5 Tabel `reservations`
Menyimpan data transaksi reservasi meja terjadwal.

| Nama Kolom | Tipe Data | Nullable | Default | Keterangan / Batasan |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | **Primary Key** |
| `customer_id` | INT UNSIGNED | NO | - | **Foreign Key** $\rightarrow$ `customers(id)` |
| `table_id` | INT UNSIGNED | NO | - | **Foreign Key** $\rightarrow$ `restaurant_tables(id)` |
| `user_id` | INT UNSIGNED | NO | - | **Foreign Key** $\rightarrow$ `users(id)` |
| `start_time` | DATETIME | NO | - | Waktu mulai slot reservasi |
| `end_time` | DATETIME | NO | - | Waktu selesai slot (start + 90 mnt) |
| `guest_count` | INT UNSIGNED | NO | - | CHECK (`guest_count` >= 1) |
| `status` | ENUM('confirmed','completed','cancelled') | NO | 'confirmed' | Status siklus reservasi |
| `notes` | TEXT | YES | NULL | Permintaan khusus pelanggan |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu reservasi dicatat |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu perubahan reservasi |

* **Constraints**:
  * CHECK (`guest_count` >= 1)
  * CHECK (`end_time` > `start_time`)
* **Foreign Key Rules**:
  * `customer_id`: ON DELETE RESTRICT ON UPDATE CASCADE
  * `table_id`: ON DELETE RESTRICT ON UPDATE CASCADE
  * `user_id`: ON DELETE RESTRICT ON UPDATE CASCADE
* **Indeks Khusus**:
  * `idx_reservations_collision (table_id, status, start_time, end_time)`: Optimasi query cek bentrok jadwal.
  * `idx_reservations_date_range (start_time, end_time)`: Optimasi query laporan periode.

---

### 2.6 Tabel `orders`
Menyimpan *header* transaksi pemesanan menu makanan/minuman.

| Nama Kolom | Tipe Data | Nullable | Default | Keterangan / Batasan |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | **Primary Key** |
| `reservation_id` | INT UNSIGNED | YES | NULL | **Foreign Key** $\rightarrow$ `reservations(id)` |
| `table_id` | INT UNSIGNED | NO | - | **Foreign Key** $\rightarrow$ `restaurant_tables(id)` |
| `user_id` | INT UNSIGNED | NO | - | **Foreign Key** $\rightarrow$ `users(id)` (kasir) |
| `total_amount` | DECIMAL(12,2) | NO | 0.00 | CHECK (`total_amount` >= 0.00) |
| `status` | ENUM('pending','completed','cancelled') | NO | 'pending' | Status penyelesaian tagihan |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu pesanan dibuat |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu perubahan pesanan |

* **Foreign Key Rules**:
  * `reservation_id`: ON DELETE SET NULL ON UPDATE CASCADE
  * `table_id`: ON DELETE RESTRICT ON UPDATE CASCADE
  * `user_id`: ON DELETE RESTRICT ON UPDATE CASCADE
* **Indeks**: `idx_orders_status (status)`, `idx_orders_created_at (created_at)`

---

### 2.7 Tabel `order_items`
Menyimpan rincian item pesanan (*line items*) dengan mekanisme **Price Snapshotting**.

| Nama Kolom | Tipe Data | Nullable | Default | Keterangan / Batasan |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | **Primary Key** |
| `order_id` | INT UNSIGNED | NO | - | **Foreign Key** $\rightarrow$ `orders(id)` |
| `menu_id` | INT UNSIGNED | NO | - | **Foreign Key** $\rightarrow$ `menus(id)` |
| `item_name` | VARCHAR(100) | NO | - | *Snapshot* nama menu saat transaksi |
| `price_at_order` | DECIMAL(12,2) | NO | - | *Snapshot* harga satuan saat transaksi |
| `quantity` | INT UNSIGNED | NO | - | CHECK (`quantity` >= 1) |
| `subtotal` | DECIMAL(12,2) | NO | - | CHECK (`subtotal` >= 0.00) |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Waktu item disimpan |

* **Foreign Key Rules**:
  * `order_id`: ON DELETE CASCADE ON UPDATE CASCADE (Jika pesanan dihapus, rincian otomatis terhapus)
  * `menu_id`: ON DELETE RESTRICT ON UPDATE CASCADE (Menu yang pernah dipesan tidak boleh dihapus begitu saja)
* **Indeks**: `idx_order_items_order_id (order_id)`, `idx_order_items_menu_id (menu_id)`

---

## 3. ANALISIS INTEGRITAS DAN ATURAN RELASIONAL

### 3.1 Alasan Pemilihan Aksi Foreign Key
1. **`ON DELETE RESTRICT` pada Master Data**:
   * Menghapus meja (`restaurant_tables`), pelanggan (`customers`), atau menu (`menus`) yang sudah memiliki riwayat transaksi akan ditolak oleh mesin database MySQL.
   * *Justifikasi Asesmen*: Menjaga konsistensi data historis dan mencegah terjadinya *orphaned records*.
2. **`ON DELETE CASCADE` pada Rincian Pesanan (`order_items`)**:
   * Jika header `orders` dihapus (misalnya pada skenario pembatalan draft tertentu), seluruh detail item yang melekat padanya ikut terhapus.
3. **`ON DELETE SET NULL` pada Relasi `orders.reservation_id`**:
   * Pesanan tidak boleh hilang meskipun data reservasi referensinya dibersihkan. Jika reservasi dihapus, pesanan tetap berdiri sendiri sebagai catatan meja fisik.

---

## 4. OPTIMASI QUERY DAN RENCANA INDEKS

### 4.1 Kueri Algoritma Deteksi Bentrok Jadwal (BR-02)
Query kritis yang sering dieksekusi saat proses pembuatan reservasi:

```sql
SELECT COUNT(*) AS total_conflicts
FROM reservations
WHERE table_id = :table_id
  AND status = 'confirmed'
  AND start_time < :new_end_time
  AND end_time > :new_start_time;
```

* **Indeks Pendukung**: `idx_reservations_collision (table_id, status, start_time, end_time)`.
* **Efisiensi**: Kueri di atas melakukan *Index Range Scan* langsung pada pohon B-Tree indeks gabungan tanpa perlu melakukan *Full Table Scan*, sehingga waktu eksekusi < 2 milidetik bahkan pada ribuan baris data reservasi.

---

## 5. STRATEGI TRANSAKSI BASIS DATA ATOMIK (ACID)

Untuk memenuhi kriteria unjuk kerja **J.620100.021.02**, operasi pembuatan pesanan multi-tabel (`orders` dan `order_items`) wajib dieksekusi dalam satu transaksi PDO:

```php
// Contoh Penerapan Transaksi PDO pada OrderService
$pdo->beginTransaction();
try {
    // 1. Simpan Header Pesanan (total_amount awal = 0)
    $stmtOrder = $pdo->prepare("
        INSERT INTO orders (table_id, reservation_id, user_id, total_amount, status)
        VALUES (:table_id, :reservation_id, :user_id, 0.00, 'pending')
    ");
    $stmtOrder->execute([
        ':table_id' => $tableId,
        ':reservation_id' => $reservationId,
        ':user_id' => $userId
    ]);
    $orderId = $pdo->lastInsertId();

    $grandTotal = 0.00;

    // 2. Loop dan Simpan Setiap Item dengan Snapshot Harga
    $stmtItem = $pdo->prepare("
        INSERT INTO order_items (order_id, menu_id, item_name, price_at_order, quantity, subtotal)
        VALUES (:order_id, :menu_id, :item_name, :price_at_order, :quantity, :subtotal)
    ");

    foreach ($items as $item) {
        // Ambil harga dan nama aktual terkini dari master menu
        $menu = $menuRepo->findById($item['menu_id']);
        if (!$menu || !$menu->isAvailable()) {
            throw new Exception("Menu ID {$item['menu_id']} tidak tersedia.");
        }

        $subtotal = $menu->getPrice() * $item['quantity'];
        $grandTotal += $subtotal;

        $stmtItem->execute([
            ':order_id' => $orderId,
            ':menu_id' => $menu->getId(),
            ':item_name' => $menu->getName(),
            ':price_at_order' => $menu->getPrice(),
            ':quantity' => $item['quantity'],
            ':subtotal' => $subtotal
        ]);
    }

    // 3. Update Grand Total di Header Pesanan
    $stmtUpdate = $pdo->prepare("UPDATE orders SET total_amount = :total WHERE id = :id");
    $stmtUpdate->execute([':total' => $grandTotal, ':id' => $orderId]);

    // 4. Commit Transaksi jika seluruh proses berhasil tanpa cela
    $pdo->commit();
    return $orderId;

} catch (Exception $e) {
    // Batalkan seluruh mutasi jika terjadi kegagalan parsial
    $pdo->rollBack();
    throw $e;
}
```

---

## 6. CARA EKSEKUSI SKRIP DATABASE

Skrip SQL telah disediakan pada:
1. Skema DDL: [database/schema.sql](file:///d:/Documents/Projek/restaurant/database/schema.sql)
2. Data Awal DML: [database/seeders.sql](file:///d:/Documents/Projek/restaurant/database/seeders.sql)

### Eksekusi melalui MySQL CLI / PowerShell:
```powershell
# 1. Masuk ke MySQL dan jalankan schema
mysql -u root -p < d:\Documents\Projek\restaurant\database\schema.sql

# 2. Masukkan data awal untuk demonstrasi
mysql -u root -p < d:\Documents\Projek\restaurant\database\seeders.sql
```
