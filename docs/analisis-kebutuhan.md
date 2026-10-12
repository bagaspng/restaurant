# DOKUMEN ANALISIS KEBUTUHAN SISTEM (VERSI RAMPING & FOKUS OKUPASI)
## Sistem Pemesanan Restoran (*Restaurant Reservation & Ordering System*)

* **Kode Dokumen**: DOC-REQ-001
* **Versi**: 1.1.0 (Disederhanakan untuk Efektivitas Asesmen BNSP)
* **Status**: Disetujui
* **Tanggal Penyusunan**: 12 Oktober 2026
* **Penulis**: Mahasiswa Teknik Informatika (Kandidat Asesi BNSP)
* **Target Asesmen**: Skema Sertifikasi Pemrograman BNSP (9 Unit Kompetensi)

---

## 1. PENDAHULUAN & RUANG LINGKUP SEDERHANA

### 1.1 Latar Belakang & Masalah Utama
Sistem ini dirancang untuk menyelesaikan 4 masalah operasional inti pada restoran:
1. **Pencegahan Bentrok Reservasi (*Double-Booking*)**: Mencegah meja yang sama dipesan oleh dua pelanggan berbeda pada rentang jam yang bertabrakan.
2. **Validasi Kapasitas Meja**: Memastikan jumlah tamu tidak melebihi daya tampung meja.
3. **Integritas Harga Historis (*Price Snapshot*)**: Menyimpan harga menu pada saat pesanan dibuat agar perubahan harga menu di masa depan tidak merusak total transaksi masa lalu.
4. **Kalkulasi Otomatis Sisi Server**: Menghitung subtotal dan grand total secara akurat dan atomik (didukung transaksi database) untuk menghindari kesalahan manusia (*human error*).

### 1.2 Ruang Lingkup Sistem
Aplikasi dibuat ramping (*lean*), fungsional, dan mudah dipahami:
* **Fokus Fitur Utama**:
  1. Autentikasi & Hak Akses (Admin & Staf).
  2. Kelola Data Meja (nomor meja, kapasitas).
  3. Kelola Data Menu (nama, kategori, harga, status ketersediaan).
  4. Kelola Data Pelanggan (nama, nomor telepon).
  5. Reservasi Meja (tanggal, jam mulai, durasi 90 menit, cek bentrok jadwal).
  6. Pesanan Menu (pilih meja/reservasi, multi-item menu, snapshot harga, hitung total).
  7. Laporan Sederhana (filter berdasarkan periode tanggal dan status).
* **Batasan Sistem (Disederhanakan)**:
  * Tidak menggunakan payment gateway daring (pembayaran diverifikasi kasir di tempat).
  * Tidak menggunakan integrasi hardware kasir fisik.
  * Alur status disederhanakan tanpa birokrasi status perantara yang berlebihan.

---

## 2. AKTOR DAN HAK AKSES (RBAC)

Sistem hanya memiliki 2 peran pengguna internal:

| Peran | Wewenang Operasional |
| :--- | :--- |
| **Admin** | Memiliki wewenang penuh: mengelola akun staf, master data meja, master data menu, pelanggan, reservasi, pesanan, dan mencetak laporan transaksi. |
| **Staf (Staff / Kasir)** | Memiliki wewenang operasional harian: menginput pelanggan, membuat reservasi meja, membuat pesanan, dan memperbarui status pesanan. *Tidak dapat menghapus master meja/menu atau mengelola akun pengguna lain.* |

---

## 3. KEBUTUHAN FUNGSIONAL (FR)

Setiap kebutuhan fungsional dirancang spesifik untuk mendukung bukti kompetensi BNSP:

* **FR-01 (Autentikasi)**: Pengguna dapat login menggunakan email dan password (terenkripsi `password_hash`), serta logout secara aman.
* **FR-02 (Manajemen Meja)**: Admin dapat menambah, melihat, mengedit, dan menghapus meja (dengan nomor meja unik dan kapasitas minimal 1 kursi).
* **FR-03 (Manajemen Menu)**: Admin dapat menambah, melihat, mengedit menu makanan/minuman dengan harga bertipe `DECIMAL` (tidak boleh negatif).
* **FR-04 (Manajemen Pelanggan)**: Staf/Admin dapat mendaftarkan nama dan nomor telepon pelanggan.
* **FR-05 (Reservasi & Validasi Bentrok)**:
  * Pengguna memilih pelanggan, meja, tanggal, jam mulai, dan jumlah tamu.
  * Sistem menghitung waktu selesai otomatis ($\text{jam mulai} + 90\text{ menit}$).
  * Sistem menolak jika jumlah tamu > kapasitas meja.
  * Sistem menolak jika ada jadwal reservasi aktif lain di meja yang sama pada rentang jam tersebut.
* **FR-06 (Pesanan & Snapshot Harga)**:
  * Pengguna membuat pesanan untuk meja/reservasi tertentu dengan memilih beberapa item menu dan kuantitasnya.
  * Sistem menyimpan snapshot harga menu ke dalam detail pesanan.
  * Sistem menghitung subtotal per item dan total pesanan di sisi server.
  * Header dan detail pesanan disimpan menggunakan transaksi database (commit/rollback).
* **FR-07 (Status Operasional)**:
  * Status Reservasi: `confirmed` (aktif), `completed` (selesai), `cancelled` (batal).
  * Status Pesanan: `pending` (belum bayar / sedang disajikan), `completed` (lunas / selesai), `cancelled` (batal).
* **FR-08 (Laporan Transaksi)**:
  * Menampilkan rekapitulasi reservasi dan pesanan berdasarkan filter tanggal mulai dan tanggal akhir.
  * Membedakan rekapitulasi total nilai pesanan dan pesanan yang selesai (*completed*).

---

## 4. KEBUTUHAN NONFUNGSIONAL (NFR)

* **NFR-SEC (Keamanan)**: Menggunakan Prepared Statements PDO untuk mencegah SQL Injection, enkripsi password satu arah (Bcrypt/Argon2id), sanitasi output (`htmlspecialchars`) dari XSS, serta perlindungan token CSRF.
* **NFR-REL (Integritas Data)**: Skema database menerapkan Foreign Key dengan constraint yang tepat, perhitungan uang menggunakan `DECIMAL(12,2)`, serta transaksi database atomik pada pemesanan.
* **NFR-MNT (Kualitas Kode)**: Kode PHP ditulis rapi mengikuti pedoman standar PSR-12, arsitektur modular sederhana (Model-View-Controller & Service), dan mudah diuji unit test.
* **NFR-USB (Kemudahan Penggunaan)**: Tampilan bersih, intuitif, dan responsif menggunakan Bootstrap 5 dengan navigasi yang mudah didemonstrasikan ke asesor.

---

## 5. ATURAN BISNIS UTAMA (BUSINESS RULES)

Aturan bisnis disederhanakan agar mudah dipahami mahasiswa dan mudah diuji:

1. **BR-01 (Kapasitas Meja)**:
   Jumlah tamu reservasi harus memenuhi syarat:
   $$1 \le \text{Jumlah Tamu} \le \text{Kapasitas Meja}$$
2. **BR-02 (Pemeriksaan Bentrok Jadwal)**:
   Dua reservasi pada meja yang sama bertabrakan jika:
   $$(\text{Start Baru} < \text{End Lama}) \land (\text{End Baru} > \text{Start Lama})$$
   Pemeriksaan ini HANYA mengecek reservasi yang berstatus aktif (`confirmed`). Reservasi yang berstatus `cancelled` diabaikan.
3. **BR-03 (Snapshot Harga Menu)**:
   Saat item menu dimasukkan ke pesanan, harga saat itu wajib disalin ke kolom `order_items.price_at_order`. Perubahan harga master menu tidak boleh mengubah total harga pada pesanan yang sudah tercatat sebelumnya.
4. **BR-04 (Kalkulasi Server-Side)**:
   $$\text{Subtotal} = \text{price\_at\_order} \times \text{quantity}$$
   $$\text{Total Pesanan} = \sum \text{Subtotal}$$
   Semua komputasi wajib dihitung di sisi server PHP.
5. **BR-05 (Konsistensi Transaksi Database)**:
   Penyimpanan tabel pesanan (`orders`) dan rincian item (`order_items`) wajib berada dalam satu blok transaksi database (`BEGIN TRANSACTION`, `COMMIT`, `ROLLBACK`). Jika ada item yang gagal disimpan, seluruh pesanan harus dibatalkan (*rollback*).

---

## 6. ACCEPTANCE CRITERIA (KRITERIA UJI)

Kriteria pengujian dibuat spesifik, lugas, dan terukur:

* **AC-01 (Login)**:
  * Input email & password benar $\rightarrow$ Masuk ke Dashboard dengan role yang sesuai.
  * Input password salah $\rightarrow$ Muncul pesan error "Email atau password salah", tidak dapat masuk.
* **AC-02 (Validasi Meja)**:
  * Input kapasitas 0 atau negatif $\rightarrow$ Sistem menolak dan menampilkan pesan validasi.
* **AC-03 (Pencegahan Bentrok Reservasi)**:
  * Meja 1 sudah dipesan pukul 12:00 - 13:30 (status `confirmed`).
  * Input reservasi baru Meja 1 pukul 13:00 - 14:30 $\rightarrow$ Ditolak karena jadwal tumpang tindih.
  * Input reservasi baru Meja 1 pukul 13:30 - 15:00 $\rightarrow$ Berhasil (jadwal tepat bersambung).
  * Input reservasi Meja 1 pada slot yang sudah `cancelled` $\rightarrow$ Berhasil (slot dibebaskan).
* **AC-04 (Snapshot & Perhitungan Pesanan)**:
  * Pesan 2 Nasi Goreng (@Rp20.000) dan 1 Es Teh (@Rp5.000).
  * Sistem menghitung: Subtotal 1 = Rp40.000, Subtotal 2 = Rp5.000, Total = Rp45.000.
  * Ubah harga Nasi Goreng di master menu menjadi Rp25.000 $\rightarrow$ Total pesanan tadi tetap Rp45.000.
* **AC-05 (Filter Laporan)**:
  * Masukkan filter tanggal $\rightarrow$ Sistem hanya menampilkan data dalam rentang tanggal tersebut.

---

## 7. PEMETAAN LANGSUNG KE 9 UNIT KOMPETENSI BNSP

Dokumen yang disederhanakan ini tetap menjamin 100% ketercapaian seluruh unit kompetensi:

| Unit Kompetensi BNSP | Bagaimana Sistem Ini Memenuhinya |
| :--- | :--- |
| **1. Spesifikasi Program** (J.620100.009.01) | Melalui dokumen analisis kebutuhan ini yang memuat FR, NFR, BR, AC, serta pemodelan sistem dan ERD di tahap berikutnya. |
| **2. Guidelines & Best Practices** (J.620100.016.01) | Standar PSR-12, struktur kode modular MVC, Prepared Statements PDO, sanitasi input/output, konfigurasi aman di `.env`. |
| **3. Pemrograman Terstruktur** (J.620100.017.02) | Fungsi algoritma cek bentrok jadwal, fungsi kalkulasi subtotal/total pesanan, percabangan `if/else`, perulangan `foreach`, validasi data. |
| **4. Pemrograman Berorientasi Objek** (J.620100.018.02) | Class & Object, Encapsulation (getter/setter/private properties), Inheritance (`Admin` & `Staff` turunan dari `User`), Polymorphism (interface layanan/perhitungan), Method Overriding. |
| **5. Library / Komponen** (J.620100.019.02) | Composer untuk manajemen paket, PHPUnit untuk testing, Bootstrap 5 untuk CSS UI, `vlucas/phpdotenv` untuk file `.env`. |
| **6. Akses Basis Data** (J.620100.021.02) | Skema database relasional MySQL, Primary Key, Foreign Key, tipe `DECIMAL`, transaksi atomik (`beginTransaction`, `commit`, `rollback`), dan PDO Prepared Statements. |
| **7. Dokumentasi Program** (J.620100.023.02) | Berkas dokumentasi terstruktur di folder `docs/` (`analisis-kebutuhan.md`, `desain-database.md`, panduan instalasi, dan panduan presentasi). |
| **8. Debugging** (J.620100.025.02) | Dokumentasi log debugging kasus nyata: pencegahan double-booking, verifikasi rollback transaksi, dan penanganan exception database. |
| **9. Pengujian Unit Program** (J.620100.033.02) | Unit testing PHPUnit untuk menguji fungsi validasi kapasitas, kalkulasi total harga, dan algoritma bentrok jadwal secara otomatis. |

---

## 8. CHECKLIST VERIFIKASI TAHAP 1 (VERSI RAMPING)

- [x] Ruang lingkup disederhanakan, fokus pada alur esensial restoran tanpa kompleksitas berlebih.
- [x] Status disederhanakan (`confirmed`, `completed`, `cancelled`) agar alur operasional mudah dijelaskan.
- [x] Seluruh 9 Unit Kompetensi BNSP terpetakan dengan jelas dan tetap memiliki bukti nyata.
- [x] Aturan bisnis kunci (bentrok jadwal, snapshot harga, transaksi atomik) dipertahankan.
- [x] Acceptance criteria siap diuji dengan mudah dan objektif.
