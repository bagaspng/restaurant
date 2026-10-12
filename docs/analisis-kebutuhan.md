# DOKUMEN ANALISIS KEBUTUHAN SISTEM (AKTOR TUNGGAL: ADMINISTRATOR LOKAL)
## Sistem Pemesanan Restoran (*Restaurant Reservation & Ordering System*)

* **Kode Dokumen**: DOC-REQ-001
* **Versi**: 2.0.0 (Revisi Sistem Lokal - Aktor Tunggal: Administrator)
* **Status**: Disetujui
* **Tanggal Penyusunan**: 12 Oktober 2026
* **Penulis**: Mahasiswa Teknik Informatika (Kandidat Asesi BNSP)
* **Target Asesmen**: Skema Sertifikasi Pemrograman BNSP (9 Unit Kompetensi)

---

## 1. PENDAHULUAN & RUANG LINGKUP SISTEM LOKAL

### 1.1 Latar Belakang & Masalah Utama
Sistem ini dirancang khusus untuk operasional kasir dan manajemen internal pada perangkat komputer lokal restoran. Sistem menyelesaikan 4 masalah inti:
1. **Pencegahan Bentrok Reservasi (*Double-Booking*)**: Mencegah alokasi meja yang sama pada rentang jam bertabrakan.
2. **Validasi Kapasitas Meja**: Memastikan jumlah tamu sesuai daya tampung meja.
3. **Integritas Harga Historis (*Price Snapshot*)**: Menyimpan harga menu pada saat pesanan dicatat agar histori transaksi lampau kebal terhadap kenaikan harga di kemudian hari.
4. **Kalkulasi Otomatis Sisi Server**: Menghitung subtotal dan total secara presisi dengan transaksi database atomik (*commit/rollback*).

### 1.2 Karakteristik Sistem Lokal & Aktor Tunggal
* **Aplikasi Tanpa Form Login Eksternal**: Karena aplikasi ini merupakan sistem operasional kasir lokal (*single-station local POS*), sistem langsung membuka antarmuka kerja Administrator tanpa birokrasi otentikasi login yang berulang.
* **Aktor Tunggal (Administrator)**: Administrator mencakup seluruh wewenang operasional: mengelola meja, katalog menu, pendaftaran pelanggan, penjadwalan reservasi, pencatatan pesanan multi-item, pelunasan kasir, dan penarikan laporan transaksi.

---

## 2. KEBUTUHAN FUNGSIONAL (FR)

* **FR-01 (Dashboard Operasional)**: Sistem menampilkan ringkasan metrik harian (ketersediaan meja, jumlah reservasi hari ini, pesanan hari ini, dan kas masuk aktual).
* **FR-02 (Manajemen Meja)**: Administrator dapat menambah, melihat, mengedit, dan menghapus meja makan (nomor meja unik, kapasitas minimal 1 kursi).
* **FR-03 (Manajemen Menu & Hirarki OOP)**: Administrator dapat mengelola katalog hidangan makanan dan minuman dengan tipe presisi moneter `DECIMAL` (harga non-negatif).
* **FR-04 (Manajemen Pelanggan)**: Administrator dapat mendaftarkan nama dan nomor kontak pelanggan (validasi format numerik 10-15 digit).
* **FR-05 (Reservasi & Validasi Bentrok)**:
  * Memilih pelanggan, meja, tanggal, jam mulai, dan jumlah tamu.
  * Durasi otomatis dihitung 90 menit ($\text{selesai} = \text{mulai} + 90\text{ menit}$).
  * Validasi kapasitas: $\text{tamu} \le \text{kapasitas meja}$.
  * Deteksi bentrok jadwal: menolak jika meja telah memiliki reservasi aktif di rentang waktu tersebut.
* **FR-06 (Pesanan & Price Snapshotting)**:
  * Pembuatan pesanan multi-item untuk meja tertentu.
  * Penyalinan snapshot nama dan harga menu ke detail transaksi.
  * Kalkulasi subtotal dan grand total di sisi server.
  * Transaksi database atomik multi-tabel (`beginTransaction`, `commit`, `rollBack`).
* **FR-07 (Status Operasional Transaksi)**:
  * Reservasi: `confirmed` (terjadwal), `completed` (selesai), `cancelled` (batal).
  * Pesanan: `pending` (belum lunas), `completed` (lunas/selesai), `cancelled` (batal).
* **FR-08 (Laporan Rekapitulasi Finansial)**:
  * Filter berdasarkan periode tanggal mulai dan tanggal akhir.
  * Pemisahan tegas antara nilai pesanan kotor (*Gross*) dan penerimaan kas nyata (*Realized Revenue*).
  * Format cetak bersih formal (*print-friendly*).

---

## 3. KEBUTUHAN NONFUNGSIONAL (NFR)

* **NFR-SEC (Keamanan & Integritas)**: Prepared Statements PDO pada seluruh kueri database (anti SQL Injection), sanitasi keluaran `htmlspecialchars` (anti XSS), dan proteksi form CSRF token.
* **NFR-REL (Keandalan Basis Data)**: Foreign Key dengan `RESTRICT` pada master data dan `CASCADE` pada item pesanan, tipe moneter `DECIMAL(12,2)`, dan transaksi atomik ACID.
* **NFR-MNT (Kualitas Kode)**: Standar PSR-12, arsitektur modular MVC, dan prinsip OOP (Encapsulation, Inheritance, Polymorphism via Interface, Overriding).
* **NFR-USB (Antarmuka Pengguna)**: Bootstrap 5.3 responsif dan bersih dengan notifikasi flash yang komunikatif.

---

## 4. ATURAN BISNIS UTAMA (BUSINESS RULES)

1. **BR-01 (Kapasitas Meja)**: $1 \le \text{Jumlah Tamu} \le \text{Kapasitas Meja}$.
2. **BR-02 (Pemeriksaan Bentrok Jadwal)**:
   $$(\text{Start Baru} < \text{End Lama}) \land (\text{End Baru} > \text{Start Lama}) \land (\text{Status} = \text{'confirmed'})$$
3. **BR-03 (Snapshot Harga Menu)**: Saat transaksi dibuat, nama dan harga menu saat itu disalin permanen ke `order_items`. Kenaikan harga menu di masa depan tidak mempengaruhi transaksi lama.
4. **BR-04 (Kalkulasi Server-Side)**:
   $$\text{Subtotal} = \text{price\_at\_order} \times \text{quantity}$$
   $$\text{Total Pesanan} = \sum \text{Subtotal}$$
5. **BR-05 (Konsistensi Transaksi Database)**: Penyimpanan tabel `orders` dan `order_items` wajib atomik (`commit`/`rollBack`).
6. **BR-06 (Pemisahan Laporan Finansial)**: Kas Masuk Nyata (*Realized*) hanya dihitung dari pesanan berstatus `completed`.

---

## 5. PEMETAAN 9 UNIT KOMPETENSI BNSP (TETAP TERPENUHI 100%)

| Unit Kompetensi | Bukti Implementasi pada Sistem Lokal Aktor Tunggal |
| :--- | :--- |
| **1. Spesifikasi Program** (J.620100.009.01) | Dokumen FR, NFR, aturan bisnis, kamus data, dan pemodelan sistem terpadu. |
| **2. Guidelines & Best Practices** (J.620100.016.01) | Standar PSR-12, Prepared Statements PDO, sanitasi XSS (`View::escape`), CSRF token, konfigurasi `.env`. |
| **3. Pemrograman Terstruktur** (J.620100.017.02) | Algoritma cek bentrok reservasi, kalkulasi subtotal dan grand total, perulangan item, percabangan status. |
| **4. Pemrograman Berorientasi Objek** (J.620100.018.02) | Class, Encapsulation, Inheritance & Overriding (`FoodItem` & `BeverageItem` mewarisi `MenuItem`), Polymorphism via Interface (`OrderCalculatorInterface`). |
| **5. Library Pre-existing** (J.620100.019.02) | Composer, PHPUnit 11, Bootstrap 5.3, `vlucas/phpdotenv`. |
| **6. Akses Basis Data** (J.620100.021.02) | Relasi FK MySQL, `DECIMAL(12,2)`, Transaksi Atomik PDO (`beginTransaction`, `commit`, `rollBack`), indeks B-Tree bentrok jadwal. |
| **7. Dokumentasi Program** (J.620100.023.02) | Berkas lengkap di `docs/` (`analisis-kebutuhan.md`, `pemodelan-sistem.md`, `desain-database.md`, `panduan-pengguna.md`, `README.md`). |
| **8. Debugging** (J.620100.025.02) | Studi kasus nyata: pencegahan double-booking, rollback kegagalan item pesanan, dan penanganan ekstensi zip. |
| **9. Pengujian Unit Program** (J.620100.033.02) | Test suite PHPUnit untuk validasi kapasitas meja, logika bentrok jadwal, dan komputasi harga. |
