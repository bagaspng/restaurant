# DOKUMEN EVALUASI LIBRARY DAN LISENSI
## Sistem Pemesanan Restoran (*Restaurant Reservation & Ordering System*)

* **Kode Dokumen**: DOC-LIB-004
* **Versi**: 1.0.0
* **Status**: Disetujui
* **Tanggal Evaluasi**: 12 Oktober 2026
* **Penulis**: Mahasiswa Teknik Informatika (Kandidat Asesi BNSP)
* **Target Asesmen**: Skema Sertifikasi Pemrograman BNSP (Unit J.620100.019.02 — Menggunakan Library atau Komponen Pre-existing)

---

## 1. PENDAHULUAN
Untuk memenuhi kriteria unjuk kerja Unit **J.620100.019.02**, pemilihan pustaka (*library*) atau komponen perangkat lunak pihak ketiga harus didasari oleh analisis kebutuhan yang jelas, perbandingan dengan pembuatan dari awal (*build vs buy/use*), pemeriksaan kompatibilitas versi PHP, verifikasi status pemeliharaan aktif, serta kepatuhan terhadap lisensi open-source.

Aplikasi ini menggunakan dependensi secara terukur dan minimalis guna menghindari *dependency bloat*.

---

## 2. EVALUASI DAN JUSTIFIKASI SETIAP LIBRARY

### 2.1 Library 1: `vlucas/phpdotenv` (v5.5+)

#### 1. Kebutuhan yang Diselesaikan
Memisahkan konfigurasi sensitif (kredensial database, nama host, password) dari source code dengan memuat variabel lingkungan dari file `.env` ke `$_ENV` dan `$_SERVER`.

#### 2. Perbandingan Library vs Implementasi dari Awal (*Scratch*)
* **Jika Membuat Sendiri**: Menulis parser file teks sederhana menggunakan `file()` dan `explode('=')`. Rentan bug parsing ketika nilai memiliki tanda kutip bertingkat, spasi, baris baru (*multiline*), atau karakter khusus `#`.
* **Menggunakan Library**: `phpdotenv` menangani validasi sintaks `.env`, variabel bersarang, penanganan tipe data, dan immutability secara andal.

#### 3. Manfaat
* **Keamanan**: Mencegah kebocoran password database ke repositori Git (memenuhi prinsip *12-Factor App*).
* **Konsistensi**: Standar de facto ekosistem PHP yang digunakan oleh framework modern seperti Laravel.
* **Waktu**: Menghemat waktu pembuatan parser mandiri (~3 jam kerja).

#### 4. Kompatibilitas Versi PHP
* Memerlukan PHP >= 8.0.
* Sistem saat ini: **PHP 8.3.31 (cli)** $\rightarrow$ **Kompatibel Penuh**.

#### 5. Status Pemeliharaan & Risiko
* Dikelola aktif oleh Graham Campbell & Vance Lucas di GitHub.
* Repositori memiliki >15.000 bintang dan >500 juta unduhan Packagist.
* Dokumentasi resmi: [https://github.com/vlucas/phpdotenv](https://github.com/vlucas/phpdotenv).
* Risiko penggunaan: Sangat rendah.

#### 6. Lisensi & Kewajiban
* **Lisensi**: BSD 3-Clause License.
* **Kewajiban**: Mencantumkan pemberitahuan hak cipta dan klausul pelepasan tanggung jawab (*disclaimer*). Diizinkan untuk penggunaan edukasi, komersial, maupun modifikasi tanpa kewajiban membuka source code utama (*non-viral*).

---

### 2.2 Library 2: `phpunit/phpunit` (v10.5+ / v11.0+) — Dev Dependency

#### 1. Kebutuhan yang Diselesaikan
Menyediakan kerangka kerja pengujian unit otomatis (*automated unit testing*) untuk memvalidasi algoritma bisnis reservasi dan perhitungan pesanan tanpa harus menjalankan aplikasi web secara manual.

#### 2. Perbandingan Library vs Implementasi dari Awal
* **Jika Membuat Sendiri**: Menulis skrip PHP dengan pengecekan `if ($a !== $b) echo 'FAIL';`. Tidak memiliki laporan assertion kaya, mock object, test runner, data provider, maupun format laporan baku yang diakui penguji.
* **Menggunakan Library**: Menyediakan assertions lengkap (`assertEquals`, `assertTrue`, `expectException`), *test runner CLI*, serta integrasi CI/CD.

#### 3. Manfaat
* **Keandalan**: Menjamin regresi bug dapat dicegah sejak dini.
* **Kesesuaian Sertifikasi**: Wajib untuk membuktikan Unit **J.620100.033.02 (Melaksanakan Pengujian Unit Program)**.

#### 4. Kompatibilitas Versi PHP
* PHPUnit 10/11 memerlukan PHP >= 8.2.
* Sistem saat ini: **PHP 8.3.31 (cli)** $\rightarrow$ **Kompatibel Penuh**.

#### 5. Status Pemeliharaan & Risiko
* Dikelola langsung oleh Sebastian Bergmann (standar industri pengujian PHP).
* Dokumentasi resmi: [https://phpunit.de/documentation.html](https://phpunit.de/documentation.html).
* Risiko penggunaan: Nol (hanya dijalankan di lingkungan pengembang / `require-dev`).

#### 6. Lisensi & Kewajiban
* **Lisensi**: BSD 3-Clause License.
* **Kewajiban**: Mencantumkan lisensi BSD di direktori vendor (tercatat otomatis oleh Composer).

---

### 2.3 Komponen Antarmuka: `Bootstrap 5.3` (CSS & JS)

#### 1. Kebutuhan yang Diselesaikan
Menyediakan sistem tata letak grid responsif, tipografi bersih, komponen kartu (*card*) kapasitas meja, wizard interaktif, modal dialog, dan badge status ketersediaan untuk antarmuka pengguna Administrator.

#### 2. Perbandingan Library vs Implementasi dari Awal
* **Jika Membuat Sendiri**: Menulis ratusan baris CSS vanilla untuk media query layar desktop, tablet, dan ponsel. Rawan inkonsistensi rendering antar browser.
* **Menggunakan Library**: Menyediakan kelas utilitas siap pakai, responsif, dan tampilan modern yang meyakinkan asesor dalam demonstrasi sistem.

#### 3. Manfaat
* **Waktu**: Mempercepat pembuatan antarmuka hingga 70%.
* **Estetika**: Tampilan antarmuka terlihat profesional dan seragam.

#### 4. Status & Lisensi
* Versi: Bootstrap 5.3 (versi LTS stabil terkini).
* **Lisensi**: MIT License (sangat permisif dan bebas digunakan).

---

## 3. RINGKASAN MATRIKS DEPENDENSI & LISENSI

| Nama Paket | Versi Terpasang | Tipe | Lisensi | Fungsi Utama di Proyek |
| :--- | :--- | :--- | :--- | :--- |
| `php` | 8.3.31 | Lingkungan | PHP License 3.01 | Bahasa pemrograman backend |
| `vlucas/phpdotenv` | ^5.5 | `require` | BSD-3-Clause | Parsing file konfigurasi lingkungan `.env` |
| `phpunit/phpunit` | ^10.5 \| ^11.0 | `require-dev` | BSD-3-Clause | Pengujian unit otomatis (Unit 033) |
| `bootstrap` | 5.3.x | Frontend UI | MIT | Tata letak responsif dan komponen visual |

---

## 4. INTEGRASI PADA `composer.json` DAN BUKTI PENGGUNAAN

Dependensi diintegrasikan secara formal melalui berkas [composer.json](file:///d:/Documents/Projek/restaurant/composer.json) dengan PSR-4 Autoloading:

```json
{
    "require": {
        "php": ">=8.2",
        "vlucas/phpdotenv": "^5.5"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.5 || ^11.0"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Config\\": "config/"
        }
    }
}
```

* Bukti instalasi tercatat pada [composer.lock](file:///d:/Documents/Projek/restaurant/composer.lock).
* Seluruh dependensi dipanggil secara otomatis melalui `vendor/autoload.php`.
