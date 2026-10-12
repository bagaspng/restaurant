# PANDUAN INSTALASI DAN KONFIGURASI SISTEM
## Sistem Pemesanan Restoran (*Restaurant Reservation & Ordering System*)

* **Kode Dokumen**: DOC-INS-005
* **Versi**: 2.1.0 (Revisi Mode Lokal - Aktor Tunggal: Administrator)
* **Status**: Disetujui
* **Tanggal Penyusunan**: 12 Oktober 2026
* **Penulis**: Mahasiswa Teknik Informatika (Kandidat Asesi BNSP)

---

## 1. PRASYARAT PERANGKAT LUNAK (*SYSTEM REQUIREMENTS*)

Sebelum menjalankan aplikasi pada komputer lokal pengujian/asesi, pastikan prasyarat berikut telah terpasang:

1. **PHP**: Versi 8.2 atau yang lebih baru (Disarankan **PHP 8.3**).
   * Ekstensi wajib aktif: `pdo`, `pdo_mysql`, `mbstring`, `json`, `session`.
2. **Database Server**: MySQL 8.0+ atau MariaDB 10.4+ (misalnya melalui **Laragon** atau **XAMPP**).
3. **Composer**: Versi 2.x untuk instalasi dependensi PHP.
4. **Web Browser**: Google Chrome, Mozilla Firefox, atau Microsoft Edge versi terkini.

---

## 2. LANGKAH-LANGKAH INSTALASI (*STEP-BY-STEP*)

### Langkah 1: Kloning Repositori Proyek
Buka terminal (PowerShell atau Git Bash) dan arahkan ke direktori kerja:
```powershell
git clone https://github.com/bagaspng/restaurant.git
cd restaurant
```

### Langkah 2: Instal Dependensi PHP via Composer
Jalankan perintah Composer untuk mengunduh pustaka dan menghasilkan autoloader:
```powershell
composer install
```
*Perintah ini akan membaca `composer.json` dan membuat direktori `vendor/` beserta `composer.lock`.*

### Langkah 3: Konfigurasi Variabel Lingkungan (`.env`)
Salin file templat `.env.example` menjadi `.env`:
```powershell
cp .env.example .env
```
Buka file `.env` dan sesuaikan kredensial database lokal Anda:
```env
APP_NAME="Sistem Pemesanan Restoran"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta

# Konfigurasi Aturan Bisnis & Jam Operasional
RESERVATION_DEFAULT_DURATION=120
RESERVATION_SLOT_DURATION=120
RESTO_OPENING_TIME=10:00
RESTO_CLOSING_TIME=22:00

# Konfigurasi Basis Data MySQL
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=restaurant_db
DB_USER=root
DB_PASS=
```

### Langkah 4: Pembuatan dan Inisialisasi Basis Data
1. Pastikan servis database MySQL Anda sudah berjalan (misal: klik **Start All** pada kontrol panel Laragon/XAMPP).
2. Jalankan skrip DDL untuk membuat database dan skema tabel:
```powershell
# Impor Skema Tabel
mysql -u root -p < database/schema.sql

# Atau jika menggunakan path Laragon langsung di PowerShell:
& "D:\App\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe" -u root -e "source database/schema.sql"
```
3. Jalankan skrip DML untuk memasukkan data awal (*seeders*) demonstrasi:
```powershell
# Impor Data Awal Demonstrasi
mysql -u root -p < database/seeders.sql

# Atau via path Laragon:
& "D:\App\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe" -u root -e "source database/seeders.sql"
```

---

## 3. MENJALANKAN APLIKASI WEB

Aplikasi menggunakan pola *Front Controller* dengan berkas gerbang utama berada di direktori `public/index.php`. Jalankan web server bawaan PHP (*PHP Built-in Server*):

```powershell
php -S localhost:8000 -t public
```

Buka peramban web dan akses alamat:
```
http://localhost:8000
```

---

## 4. PENGOPERASIAN SISTEM LOKAL (AKTOR TUNGGAL: ADMINISTRATOR)

Aplikasi ini beroperasi dalam **Mode Lokal Stasiun Kasir**:
* **Tanpa Form Login Eksternal**: Sistem secara otomatis menginisialisasi sesi kerja sebagai **Administrator Utama (ID: 1)** sehingga seluruh menu navigasi dapat langsung diakses.
* **Hak Akses Penuh**: Mengelola master meja fisik, katalog hidangan makanan/minuman, reservasi meja alur 6 langkah (durasi 120 menit), pencatatan pesanan kasir, dan penarikan laporan rekapitulasi finansial.

---

## 5. MENJALANKAN PENGUJIAN UNIT OTOMATIS (*UNIT TESTING*)

Untuk memverifikasi kebenaran algoritma logika bisnis (slot 120 menit, deteksi bentrok interval, kalkulasi harga pesanan, dan hierarki OOP):

### Menggunakan PHPUnit Test Suite:
```powershell
vendor/bin/phpunit --testdox
```
*Seluruh pengujian unit akan menampilkan status centang hijau `[OK]` (15 tests, 49 assertions).*

### Menggunakan Skrip Verifikasi Mandiri:
```powershell
php tests/verify.php
```
*Menghasilkan laporan konsol komprehensif mengenai kesiapan database, model OOP, algoritma bentrok, dan kalkulasi pesanan.*
