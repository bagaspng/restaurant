# SISTEM PEMESANAN RESTORAN (MODE LOKAL)
### *Restaurant Reservation & Ordering System*

[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue.svg)](https://www.php.net/)
[![Database](https://img.shields.io/badge/Database-MySQL%208.0%20%2F%20MariaDB-orange.svg)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/Tests-PHPUnit%2011%20(15%20Passed)-brightgreen.svg)](tests/)

Aplikasi web operasional restoran internal berbasis komputer lokal yang dirancang untuk mengelola meja makan, katalog menu, alur reservasi meja 6-langkah terpadu dengan slot tetap 120 menit, pemilihan meja berbasis kartu kapasitas visual, pencatatan snapshot pemesan langsung, pencegahan bentrok jadwal otomatis (*interval collision detection*), transaksi pesanan multi-item dengan *price snapshotting*, serta rekapitulasi laporan finansial.

Sistem beroperasi dalam **Mode Lokal dengan Aktor Tunggal: Administrator**, di mana seluruh modul langsung dapat diakses tanpa birokrasi form login berulang, ideal untuk demonstrasi praktis dan portofolio **Skema Sertifikasi Pemrograman BNSP (9 Unit Kompetensi)**.

---

## DAFTAR ISI
1. [Prasyarat Sistem](#1-prasyarat-sistem)
2. [Panduan Cepat Menjalankan Proyek](#2-panduan-cepat-menjalankan-proyek)
3. [Karakteristik Sistem Lokal & Alur Reservasi 6-Langkah](#3-karakteristik-sistem-lokal--alur-reservasi-6-langkah)
4. [Cara Menjalankan Pengujian Program](#4-cara-menjalankan-pengujian-program)
5. [Alur Demonstrasi Fitur Utama (Panduan Asesmen BNSP)](#5-alur-demonstrasi-fitur-utama-panduan-asesmen-bnsp)
6. [Struktur Proyek](#6-struktur-proyek)
7. [Daftar Dokumentasi Teknis](#7-daftar-dokumentasi-teknis)

---

## 1. PRASYARAT SISTEM

Sebelum menjalankan aplikasi, pastikan komputer Anda telah terinstal:
* **PHP**: Versi **8.2 atau 8.3** (Ekstensi aktif: `pdo_mysql`, `zip`, `mbstring`, `session`).
* **Database Server**: **MySQL 8.0** atau MariaDB 10.4+ (misal via **Laragon** atau **XAMPP**).
* **Composer**: Versi 2.x.
* **Web Browser**: Google Chrome, Mozilla Firefox, atau Microsoft Edge.

---

## 2. PANDUAN CEPAT MENJALANKAN PROYEK

Buka terminal **PowerShell** dan ikuti langkah-langkah berikut:

### Langkah 1: Masuk ke Direktori Proyek
```powershell
cd d:\App\laragon\www\restaurant
```

### Langkah 2: Instal Dependensi Composer
```powershell
composer install
```

### Langkah 3: Siapkan File Konfigurasi Lingkungan (`.env`)
Salin file templat `.env.example` ke `.env`:
```powershell
cp .env.example .env
```

### Langkah 4: Nyalakan MySQL & Impor Basis Data
1. Pastikan servis MySQL sudah berjalan (klik tombol **Start All** pada Laragon/XAMPP).
2. Impor struktur tabel dan data awal demonstrasi:
```powershell
# Menggunakan CLI MySQL Laragon:
& "D:\App\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe" -u root -e "source database/schema.sql; source database/seeders.sql;"
```

### Langkah 5: Jalankan Server Lokal PHP
```powershell
php -S localhost:8000 -t public
```

### Langkah 6: Buka Aplikasi di Browser
Buka peramban web dan akses URL:
```
http://localhost:8000
```
> **Langsung Aktif:** Aplikasi langsung membuka **Dashboard Operasional Restoran** dengan wewenang penuh Administrator tanpa meminta login!

Untuk memeriksa status koneksi sistem dan database, akses:
```
http://localhost:8000/health
```

---

## 3. KARAKTERISTIK SISTEM LOKAL & ALUR RESERVASI 6-LANGKAH

* **Satu Aktor (Administrator)**: Administrator memegang kendali penuh operasional kasir dan manajemen tanpa form login eksternal.
* **Minimisasi Data Pemesan**: Form master pelanggan ditiadakan. Nama dan nomor telepon pemesan diinput langsung saat reservasi dan disimpan sebagai snapshot transaksi.
* **Alur Reservasi 6-Langkah Terpadu**:
  1. **Langkah 1 (Identitas Pemesan)**: Nama lengkap dan nomor telepon kontak (10–16 digit numerik).
  2. **Langkah 2 (Pilih Tanggal)**: Kalender interaktif; tanggal yang sudah lewat otomatis nonaktif.
  3. **Langkah 3 (Pilih Slot Waktu)**: Durasi tetap 120 menit (2 Jam) dari jam operasional (10.00–22.00 WIB) dengan badge ketersediaan dinamis (*Tersedia*, *Penuh*, atau *Waktu Lewat*).
  4. **Langkah 4 (Pilih Meja Berdasarkan Kapasitas)**: Kartu meja visual yang menampilkan kapasitas kursi fisik dan status kesiapan tanpa input tamu manual.
  5. **Langkah 5 (Pilih Catatan Khusus)**: Pilihan checkbox terstandarisasi (*Baby chair*, *Kursi roda*, *Meja dekat jendela*, *Dekorasi*, *Lainnya*) yang tersimpan sebagai JSON terstruktur.
  6. **Langkah 6 (Tinjau & Simpan)**: Ringkasan lengkap sebelum disimpan dengan tombol "Kembali Mengubah" yang melindungi data agar tidak hilang.

---

## 4. CARA MENJALANKAN PENGUJIAN PROGRAM

### A. Automated Unit Testing (PHPUnit)
Pengujian unit otomatis berbasis test runner PHPUnit mencakup durasi 120 menit, deteksi bentrok jadwal, validasi nomor telepon, serialisasi JSON catatan khusus, kapasitas meja, hierarki menu OOP, dan komputasi pesanan:
```powershell
vendor\bin\phpunit --testdox
```
*Hasil yang diharapkan: 15 tests, 49 assertions, status centang hijau `[OK]`.*

### B. Uji Verifikasi Fungsional Cepat (CLI)
Skrip mandiri untuk memverifikasi koneksi database, model OOP, dan algoritma bisnis:
```powershell
php tests/verify.php
```
*Hasil yang diharapkan: `=== SELURUH VERIFIKASI SISTEM LOKAL V2 LULUS 100% ===`.*

---

## 5. ALUR DEMONSTRASI FITUR UTAMA (PANDUAN ASESMEN BNSP)

Berikut skenario praktis yang dapat Anda peragakan di hadapan Asesor BNSP:

### Skenario 1: Uji Alur Reservasi 6-Langkah & Deteksi Bentrok Interval (BR-02)
1. Buka menu **Reservasi** $\rightarrow$ Klik **Buat Reservasi Baru**.
2. **Langkah 1**: Masukkan nama pemesan `"Ahmad Sanusi"` dan nomor telepon `"081234567890"`. Klik Lanjut.
3. **Langkah 2**: Pilih tanggal hari ini.
4. **Langkah 3**: Pilih slot `12:00 – 14:00 WIB` (pada seeder, Meja T-02 sudah terisi pada slot ini).
5. **Langkah 4**: Perhatikan bahwa kartu **Meja T-02** menampilkan badge merah *"Terisi (Sudah Dipesan)"* dan dinonaktifkan, sedangkan meja lain dapat dipilih.
6. Pilih meja yang bertanda *"Tersedia"* (misal Meja T-03), lalu centang fasilitas khusus di Langkah 5 (*Baby chair* / *Meja dekat jendela*).
7. Di Langkah 6, tinjau ringkasan dan simpan reservasi $\rightarrow$ Reservasi berhasil terkonfirmasi dengan durasi tepat 120 menit.

### Skenario 2: Uji Kartu Meja Berbasis Kapasitas & Catatan Khusus Terstruktur
1. Buka form edit pada reservasi yang telah dibuat (`/reservations/edit?id=...`).
2. Perhatikan nama, nomor telepon, meja, dan checkbox catatan khusus yang sebelumnya dipilih tetap tercentang secara otomatis.
3. Tambahkan catatan *Lainnya: "Dekorasi ulang tahun"*, lalu simpan.
4. Pada daftar reservasi, catatan muncul sebagai ringkasan ramah pengguna.

### Skenario 3: Uji Price Snapshotting & Kalkulasi Server-Side (BR-03, BR-04)
1. Buka menu **Pesanan** $\rightarrow$ Klik **Buat Pesanan Baru**.
2. Pilih meja dan tentukan porsi menu (misal: 2 porsi Nasi Goreng @Rp28.000).
3. Simpan pesanan $\rightarrow$ Muncul Nota Tagihan (Total: Rp56.000).
4. Buka menu **Menu** dan naikkan harga master Nasi Goreng menjadi Rp35.000.
5. Buka kembali nota pesanan tadi $\rightarrow$ **Hasil**: Harga item Nasi Goreng pada transaksi lama tetap Rp28.000 (terlindungi oleh *price snapshotting*).

### Skenario 4: Cetak Laporan Rekapitulasi Finansial (BR-REP)
1. Buka menu **Laporan**.
2. Masukkan rentang tanggal filter.
3. **Hasil**: Sistem menampilkan pemisahan jelas antara **Kas Diterima (Realized Revenue)** dari pesanan selesai (*completed*) vs **Total Nilai Pesanan Kotor (Gross)**.
4. Klik tombol **Cetak Format Bersih** untuk membuka pratinjau cetak struk/laporan formal.

---

## 6. STRUKTUR PROYEK

```
restaurant/
├── app/
│   ├── Controllers/        # Pengendali alur HTTP (Dashboard, Table, Menu, Reservation, Order, Report)
│   ├── Helpers/            # Helper Session, CSRF, XSS Escape, View, Router
│   ├── Models/             # Entitas OOP (Admin, Table, MenuItem, FoodItem, BeverageItem, Reservation, dll.)
│   ├── Repositories/       # Lapisan akses database PDO Prepared Statements
│   └── Services/           # Logika bisnis (ReservationService, OrderService, ReportService, Calculator)
├── config/
│   ├── Config.php          # Konfigurasi terpusat dari .env (Jam operasional & durasi slot)
│   └── Database.php        # Koneksi Singleton PDO MySQL
├── database/
│   ├── schema.sql          # Skrip DDL tabel, constraints, dan indeks B-Tree
│   ├── seeders.sql         # Skrip DML data awal demonstrasi
│   └── migration_v2_reservation.sql # Skrip migrasi preservasi data pemesan
├── docs/                   # Berkas dokumentasi teknis & sertifikasi BNSP
│   ├── analisis-kebutuhan.md
│   ├── pemodelan-sistem.md
│   ├── desain-database.md
│   ├── library-dan-lisensi.md
│   ├── panduan-instalasi.md
│   ├── panduan-pengguna.md
│   └── dokumentasi-modul.md
├── public/
│   └── index.php           # Front Controller (gerbang masuk aplikasi)
├── resources/
│   └── views/              # Template antarmuka Bootstrap 5.3 (reservations, tables, menus, orders, reports)
├── routes/
│   └── web.php             # Definisi rute URL aplikasi
├── tests/                  # Skrip pengujian & Unit Test PHPUnit
│   ├── Unit/               # Test suites (ReservationTest, TableTest, MenuTest, OrderCalculatorTest)
│   └── verify.php          # Skrip verifikasi mandiri CLI
├── .env.example            # Templat konfigurasi lingkungan
├── composer.json           # Dependensi pustaka & autoloading PSR-4
└── phpunit.xml             # Konfigurasi automated unit test runner
```

---

## 7. DAFTAR DOKUMENTASI TEKNIS

1. [docs/analisis-kebutuhan.md](file:///d:/Documents/Projek/restaurant/docs/analisis-kebutuhan.md) — Kebutuhan sistem, alur 6 langkah, dan strategi migrasi data.
2. [docs/pemodelan-sistem.md](file:///d:/Documents/Projek/restaurant/docs/pemodelan-sistem.md) — Diagram Konteks, DFD, Use Case, Activity Diagram, ERD, dan Class Diagram.
3. [docs/desain-database.md](file:///d:/Documents/Projek/restaurant/docs/desain-database.md) — Kamus data tabel, Foreign Key, indeks, dan transaksi atomik.
4. [docs/library-dan-lisensi.md](file:///d:/Documents/Projek/restaurant/docs/library-dan-lisensi.md) — Evaluasi pustaka pihak ketiga dan lisensi open-source.
5. [docs/panduan-instalasi.md](file:///d:/Documents/Projek/restaurant/docs/panduan-instalasi.md) — Panduan instalasi dan deployment teknis lokal.
6. [docs/panduan-pengguna.md](file:///d:/Documents/Projek/restaurant/docs/panduan-pengguna.md) — Buku manual operasional fitur bagi Administrator.
7. [docs/dokumentasi-modul.md](file:///d:/Documents/Projek/restaurant/docs/dokumentasi-modul.md) — Spesifikasi teknis setiap modul kode, parameter, dan penanganan error.

---

*Hak Cipta &copy; 2026. Disusun untuk Kelengkapan Portofolio Asesmen Pemrograman BNSP.*
