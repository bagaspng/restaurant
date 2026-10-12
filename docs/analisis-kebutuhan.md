# DOKUMEN ANALISIS KEBUTUHAN SISTEM (AKTOR TUNGGAL: ADMINISTRATOR LOKAL)
## Sistem Pemesanan Restoran (*Restaurant Reservation & Ordering System*)

* **Kode Dokumen**: DOC-REQ-001
* **Versi**: 2.1.0 (Revisi Alur Reservasi Terpadu, Penghapusan Master Pelanggan, Slot 120 Menit & Kartu Meja Kapasitas)
* **Status**: Disetujui
* **Tanggal Penyusunan**: 12 Oktober 2026
* **Penulis**: Mahasiswa Teknik Informatika (Kandidat Asesi BNSP)
* **Target Asesmen**: Skema Sertifikasi Pemrograman BNSP (9 Unit Kompetensi)

---

## 1. PENDAHULUAN & RUANG LINGKUP SISTEM LOKAL

### 1.1 Latar Belakang & Masalah Utama
Sistem ini dirancang khusus untuk operasional kasir dan manajemen internal pada perangkat komputer lokal restoran. Sistem menyelesaikan masalah operasional inti:
1. **Pencegahan Bentrok Reservasi (*Double-Booking*)**: Mencegah alokasi meja yang sama pada rentang jam bertabrakan melalui aturan interval matematika yang ketat.
2. **Pemilihan Slot Waktu Terstandarisasi (120 Menit)**: Mencegah input menit sembarangan dengan menyediakan slot operasional tetap 2 jam (misal 10.00–12.00, 12.00–14.00, dst.).
3. **Pemilihan Meja Berbasis Kapasitas**: Mengeliminasi input manual jumlah tamu dan menggantinya dengan kartu meja visual yang menampilkan kapasitas kursi fisik dan status ketersediaan langsung.
4. **Minimisasi Data & Snapshot Identitas Langsung**: Mengeliminasi form master pelanggan; nama dan nomor kontak pemesan diinput langsung saat reservasi dan disimpan sebagai snapshot transaksi tanpa relasi master data.
5. **Integritas Harga Historis (*Price Snapshot*)**: Menyimpan nama dan harga menu pada saat pesanan dicatat ke detail transaksi pesanan.
6. **Kalkulasi Otomatis Sisi Server**: Menghitung subtotal dan grand total secara presisi dengan transaksi database atomik (*commit/rollback*).

### 1.2 Karakteristik Sistem Lokal & Aktor Tunggal
* **Aplikasi Tanpa Form Login Eksternal**: Beroperasi pada stasiun kerja lokal kasir restoran (*single-station local POS*), sistem langsung membuka antarmuka kerja Administrator tanpa birokrasi otentikasi login.
* **Aktor Tunggal (Administrator)**: Administrator mencakup seluruh wewenang operasional: mengelola meja fisik, katalog menu hidangan, pembuatan reservasi meja alur 6 langkah, pencatatan pesanan multi-item, pelunasan kasir, dan penarikan laporan rekapitulasi finansial.

---

## 2. KEBUTUHAN FUNGSIONAL (FR)

* **FR-01 (Dashboard Operasional)**: Sistem menampilkan ringkasan metrik harian (ketersediaan meja, jumlah reservasi hari ini, pesanan hari ini, dan kas masuk aktual).
* **FR-02 (Manajemen Meja Makan)**: Administrator dapat menambah, melihat, mengedit, dan menghapus meja makan (nomor meja unik, kapasitas kursi $\ge 1$, status operasional).
* **FR-03 (Manajemen Menu & Hirarki OOP)**: Administrator dapat mengelola katalog hidangan makanan dan minuman dengan tipe presisi moneter `DECIMAL` (harga non-negatif) dan konsep OOP Inheritance & Overriding.
* **FR-04 (Alur Reservasi 6-Langkah Terpadu)**:
  * **Langkah 1 (Identitas Pemesan)**: Admin menginput nama pemesan (wajib, min 2 karakter) dan nomor telepon kontak (wajib, 10–16 digit numerik) langsung pada reservasi tanpa mencari dari master pelanggan.
  * **Langkah 2 (Pilih Tanggal)**: Admin memilih tanggal reservasi dengan date picker. Tanggal lampau dinonaktifkan otomatis.
  * **Langkah 3 (Pilih Slot Waktu Operasional)**: Durasi tetap 120 menit (2 jam). Slot terstandarisasi (10.00–12.00, 12.00–14.00, 14.00–16.00, 16.00–18.00, 18.00–20.00, 20.00–22.00). Slot berstatus "Penuh" atau "Waktu Lewat" dinonaktifkan; slot dengan meja tersedia menampilkan badge "Tersedia" beserta jumlah meja siap pakai.
  * **Langkah 4 (Pilih Meja Berdasarkan Kartu Kapasitas)**: Input jumlah tamu manual ditiadakan. Meja disajikan dalam bentuk kartu visual yang menampilkan nomor meja, kapasitas maksimal, dan label ketersediaan ("Tersedia", "Terisi", atau "Tidak dapat digunakan"). Hanya meja yang tersedia yang dapat dipilih.
  * **Langkah 5 (Pilih Catatan Khusus Terstruktur)**: Pilihan checkbox terstandarisasi: *Baby chair*, *Akses kursi roda*, *Meja dekat jendela*, *Dekorasi khusus*, dan opsi *Lainnya* (memunculkan isian teks tambahan kondisional). Pilihan disimpan sebagai JSON terstruktur.
  * **Langkah 6 (Tinjau & Simpan)**: Ringkasan data ditampilkan sebelum penyimpanan. Admin dapat menekan "Kembali Mengubah" tanpa kehilangan isian identitas, atau "Simpan Reservasi" untuk validasi menyeluruh dan penyimpanan database atomik.
* **FR-05 (Deteksi Bentrok Jadwal di Server)**:
  * Memvalidasi ulang di server agar bebas dari kondisi balapan (*race conditions*).
  * Menolak reservasi jika $(\text{Start Baru} < \text{End Lama}) \land (\text{End Baru} > \text{Start Lama})$ pada meja yang sama untuk reservasi `confirmed`.
* **FR-06 (Pesanan & Price Snapshotting)**:
  * Pembuatan pesanan multi-item untuk meja tertentu (atau ditautkan ke reservasi aktif).
  * Penyalinan snapshot nama dan harga menu ke detail transaksi (`order_items`).
  * Kalkulasi subtotal dan grand total di sisi server.
  * Transaksi database atomik multi-tabel (`beginTransaction`, `commit`, `rollBack`).
* **FR-07 (Status Operasional Transaksi)**:
  * Reservasi: `confirmed` (terjadwal), `completed` (selesai), `cancelled` (batal). Reservasi batal/selesai tidak menghalangi reservasi masa depan.
  * Pesanan: `pending` (belum lunas), `completed` (lunas/selesai), `cancelled` (batal).
* **FR-08 (Laporan Rekapitulasi Finansial)**:
  * Filter berdasarkan periode tanggal dan status.
  * Pemisahan tegas antara nilai pesanan kotor (*Gross*) dan penerimaan kas nyata (*Realized Revenue*).
  * Format cetak bersih formal (*print-friendly*).

---

## 3. KEBUTUHAN NONFUNGSIONAL (NFR)

* **NFR-SEC (Keamanan & Integritas)**: Prepared Statements PDO pada seluruh kueri database (anti SQL Injection), sanitasi keluaran `htmlspecialchars` (anti XSS), dan proteksi CSRF token.
* **NFR-MIN (Minimisasi Data Pribadi)**: Data kontak pemesan hanya disimpan seperlunya pada baris reservasi terkait, tidak dikumpulkan menjadi direktori data pribadi massal.
* **NFR-REL (Keandalan Basis Data)**: Foreign Key dengan `RESTRICT` pada master data dan `CASCADE` pada item pesanan, tipe moneter `DECIMAL(12,2)`, dan transaksi atomik ACID.
* **NFR-MNT (Kualitas Kode)**: Standar PSR-12, arsitektur modular MVC, dan prinsip OOP (Encapsulation, Inheritance, Polymorphism via Interface, Overriding).
* **NFR-USB (Antarmuka Pengguna)**: Bootstrap 5.3 responsif dan bersih dengan alur wizard interaktif dan notifikasi flash yang komunikatif.

---

## 4. ATURAN BISNIS UTAMA (BUSINESS RULES)

1. **BR-01 (Kapasitas Meja Fisik)**: Tamu dialokasikan ke meja makan berdasarkan kapasitas maksimal kursi yang ditetapkan pada master meja restoran. Form reservasi tidak lagi meminta input tamu bebas.
2. **BR-02 (Pemeriksaan Bentrok Jadwal & Interval 120 Menit)**:
   $$\text{Durasi} = 120\text{ Menit (2 Jam)}$$
   $$\text{Bentrok} = (\text{Start Baru} < \text{End Lama}) \land (\text{End Baru} > \text{Start Lama}) \land (\text{Status} = \text{'confirmed'})$$
   Reservasi dengan status `cancelled` atau jadwal yang bersinggungan di batas waktu ($\text{Start Baru} = \text{End Lama}$) diizinkan dan tidak dianggap bentrok.
3. **BR-03 (Snapshot Harga Menu)**: Saat transaksi dibuat, nama dan harga menu saat itu disalin permanen ke `order_items`. Kenaikan harga menu di masa depan tidak mempengaruhi transaksi lama.
4. **BR-04 (Kalkulasi Server-Side)**:
   $$\text{Subtotal} = \text{price\_at\_order} \times \text{quantity}$$
   $$\text{Total Pesanan} = \sum \text{Subtotal}$$
5. **BR-05 (Konsistensi Transaksi Database)**: Penyimpanan tabel `reservations`, `orders`, dan `order_items` wajib atomik (`commit`/`rollBack`).
6. **BR-06 (Pemisahan Laporan Finansial)**: Kas Masuk Nyata (*Realized*) hanya dihitung dari pesanan berstatus `completed`.

---

## 5. STRATEGI MIGRASI DATA PELANGGAN & PRESERVASI RIWAYAT

Untuk menghapus modul master data pelanggan tanpa kehilangan riwayat reservasi yang telah ada di database:
1. **Penambahan Kolom Snapshot**: Kolom `customer_name VARCHAR(100)` dan `customer_phone VARCHAR(20)` ditambahkan ke tabel `reservations`.
2. **Migrasi Data Terpadu**: Kueri SQL menyalin nama dan nomor telepon dari tabel `customers` ke tabel `reservations`:
   ```sql
   UPDATE reservations r JOIN customers c ON r.customer_id = c.id
   SET r.customer_name = c.name, r.customer_phone = c.phone;
   ```
3. **Pelepasan Ketergantungan**: Foreign key `fk_reservations_customer` dan kolom `customer_id` dihapus dari tabel `reservations`.
4. **Penghapusan Tabel Master**: Tabel `customers` dihapus (*drop*) dengan aman setelah data tersimpan utuh di `reservations`.
5. **Penanganan Kolom `guest_count`**: Diubah menjadi `NULLABLE` di database untuk mempertahankan histori data lampau, namun sepenuhnya dihilangkan dari antarmuka input reservasi baru sesuai prinsip seleksi meja berbasis kapasitas.

---

## 6. PEMETAAN 9 UNIT KOMPETENSI BNSP

| Unit Kompetensi | Bukti Implementasi pada Sistem Lokal Aktor Tunggal V2 |
| :--- | :--- |
| **1. Spesifikasi Program** (J.620100.009.01) | Dokumen FR, NFR, aturan bisnis, kamus data, alur 6 langkah reservasi, dan strategi migrasi. |
| **2. Guidelines & Best Practices** (J.620100.016.01) | Standar PSR-12, Prepared Statements PDO, sanitasi XSS (`View::escape`), CSRF token, konfigurasi `.env`. |
| **3. Pemrograman Terstruktur** (J.620100.017.02) | Algoritma deteksi bentrok interval, perhitungan ketersediaan slot dinamis, kalkulasi pesanan server-side. |
| **4. Pemrograman Berorientasi Objek** (J.620100.018.02) | Class, Encapsulation, Inheritance & Overriding (`FoodItem` & `BeverageItem` mewarisi `MenuItem`), Polymorphism via Interface (`OrderCalculatorInterface`). |
| **5. Library Pre-existing** (J.620100.019.02) | Composer, PHPUnit 11, Bootstrap 5.3, `vlucas/phpdotenv`. |
| **6. Akses Basis Data** (J.620100.021.02) | Relasi FK MySQL, `DECIMAL(12,2)`, Transaksi Atomik PDO, indeks B-Tree pencarian bentrok jadwal dan nama pelanggan. |
| **7. Dokumentasi Program** (J.620100.023.02) | Berkas lengkap di `docs/` (`analisis-kebutuhan.md`, `pemodelan-sistem.md`, `desain-database.md`, `panduan-pengguna.md`, `panduan-instalasi.md`, `README.md`). |
| **8. Debugging** (J.620100.025.02) | Studi kasus nyata: mitigasi bentrok interval 120 menit, rollback transaksi item pesanan, penanganan database testing. |
| **9. Pengujian Unit Program** (J.620100.033.02) | Test suite PHPUnit otomatis (15 tests, 49 assertions, status OK) mencakup slot 120 menit, validasi telepon, JSON notes, dan OOP. |
