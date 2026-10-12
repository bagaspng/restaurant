# PANDUAN PENGGUNA SISTEM (*USER MANUAL*)
## Sistem Pemesanan Restoran (*Restaurant Reservation & Ordering System*)

* **Kode Dokumen**: DOC-USR-007
* **Versi**: 2.1.0 (Revisi Alur Reservasi Terpadu 6-Langkah & Penghapusan Master Pelanggan)
* **Status**: Disetujui
* **Tanggal Penyusunan**: 12 Oktober 2026
* **Penulis**: Mahasiswa Teknik Informatika (Kandidat Asesi BNSP)

---

## 1. PENDAHULUAN
Aplikasi Sistem Pemesanan Restoran ini beroperasi dalam **Mode Lokal dengan Aktor Tunggal: Administrator**. Seluruh fungsi manajemen meja, katalog menu, reservasi berdurasi 120 menit, pemesanan makanan/minuman, dan laporan rekapitulasi dapat langsung diakses melalui peramban web pada komputer lokal kasir tanpa harus melakukan otentikasi login.

---

## 2. MEMBUKA APLIKASI
1. Buka peramban web (Google Chrome, Edge, atau Mozilla Firefox).
2. Akses alamat web lokal:
   ```
   http://localhost:8000
   ```
3. Sistem secara otomatis langsung membuka **Dashboard Operasional Restoran** dengan wewenang penuh Administrator.

---

## 3. NAVIGASI DASHBOARD OPERASIONAL

Dashboard menampilkan 4 metrik kinerja utama secara *real-time*:
* **Status Meja**: Jumlah meja berstatus Tersedia (*Available*) dibandingkan total meja fisik restoran.
* **Reservasi Hari Ini**: Jumlah jadwal reservasi meja yang tercatat untuk hari ini.
* **Pesanan Hari Ini**: Jumlah transaksi pesanan hidangan hari ini beserta jumlah yang belum lunas.
* **Pendapatan Hari Ini**: Total kas masuk nyata (*Realized Revenue*) dari pesanan yang telah lunas.
* **Aksi Cepat**: Tombol pintas untuk membuat reservasi baru, pesanan baru, mengelola meja, atau membuka laporan.

---

## 4. PENGELOLAAN MEJA RESTORAN

* Akses via menu navigasi: **Meja** (URL: `/tables`).
* **Menambah Meja Baru**:
  1. Klik tombol **Tambah Meja Baru**.
  2. Masukkan nomor/kode meja fisik (misal: `T-06`).
  3. Masukkan daya tampung kapasitas kursi (misal: `4` kursi).
  4. Pilih status operasional (`Tersedia` atau `Terisi/Rusak`).
  5. Klik **Simpan Data Meja**.
* **Mengubah Meja**: Klik ikon pensil (*edit*) pada baris meja yang ingin diubah kapasitas atau statusnya.
* **Menghapus Meja**: Klik tombol tempat sampah (*delete*). *Catatan: Meja yang memiliki riwayat transaksi dilindungi oleh integritas referensial dan tidak dapat dihapus sembarangan.*

---

## 5. PENGELOLAAN KATALOG MENU

* Akses via menu navigasi: **Menu** (URL: `/menus`).
* **Menambah Menu Baru**:
  1. Klik tombol **Tambah Menu Baru**.
  2. Masukkan nama hidangan (misal: `Ayam Bakar Madu`).
  3. Pilih kategori (`Makanan`, `Minuman`, atau `Hidangan Penutup`).
  4. Masukkan harga jual satuan (Rp).
  5. Centang saklar **Status Bahan Tersedia di Dapur**.
  6. Klik **Simpan Data Menu**.
* **Menonaktifkan Menu Habis**: Klik edit pada menu yang kehabisan stok dapur, matikan centang ketersediaan, lalu simpan. Menu tersebut otomatis tidak dapat dipilih pada transaksi pesanan baru.

---

## 6. ALUR PEMBUATAN RESERVASI MEJA (6-LANGKAH TERPADU)

Akses via menu navigasi: **Reservasi** (URL: `/reservations`) &rarr; Klik **Buat Reservasi Baru** (URL: `/reservations/create`).

Alur kerja dirancang berurutan, mudah, dan melindungi data agar tidak hilang:

### Langkah 1 — Input Identitas Pemesan
1. Masukkan **Nama Lengkap Pemesan** (minimal 2 karakter, misal: *Bpk. Sanusi*).
2. Masukkan **Nomor Telepon Kontak** (10 s.d. 16 digit numerik, misal: *081234567890*).
3. Klik tombol **Lanjut: Pilih Tanggal & Slot**.
*(Tidak perlu mencari atau membuat master data pelanggan terlebih dahulu. Data disimpan langsung sebagai snapshot transaksi).*

### Langkah 2 — Pilih Tanggal Kunjungan
1. Pilih tanggal reservasi menggunakan kalender interaktif (*date picker*).
2. Tanggal yang sudah lewat dinonaktifkan secara otomatis.
3. Sistem secara otomatis memuat ketersediaan slot meja untuk tanggal yang dipilih.

### Langkah 3 — Pilih Slot Waktu Operasional (120 Menit)
1. Reservasi memiliki durasi tetap selama **2 jam (120 menit)**.
2. Pilihan slot waktu ditampilkan dalam bentuk kartu waktu:
   * `10.00 – 12.00 WIB`
   * `12.00 – 14.00 WIB`
   * `14.00 – 16.00 WIB`
   * `16.00 – 18.00 WIB`
   * `18.00 – 20.00 WIB`
   * `20.00 – 22.00 WIB`
3. Kartu slot menampilkan label ketersediaan:
   * **Tersedia** (berwarna hijau, menampilkan jumlah meja yang siap dipesan).
   * **Penuh** (berwarna merah, dinonaktifkan jika seluruh meja telah terisi).
   * **Waktu Lewat** (berwarna abu-abu, dinonaktifkan untuk slot hari ini yang telah berlalu).
4. Klik salah satu slot yang bertanda **Tersedia**, lalu klik **Lanjut: Pilih Meja**.

### Langkah 4 — Pilih Meja Berdasarkan Kartu Kapasitas
1. Input jumlah tamu manual ditiadakan untuk efisiensi operasional.
2. Seluruh meja restoran disajikan dalam bentuk **kartu visual**:
   * Nomor meja (misal: *Meja T-02*).
   * Daya tampung maksimal kursi (misal: *Kapasitas: 4 Tamu*).
   * Status ketersediaan pada slot terpilih (*Tersedia*, *Terisi (Sudah Dipesan)*, atau *Tidak dapat digunakan*).
3. Klik kartu meja yang bertanda **Tersedia** (kartu akan berwarna hijau dengan seleksi aktif).
4. Klik **Lanjut: Catatan Khusus**.

### Langkah 5 — Pilih Permintaan & Catatan Khusus
1. Pilih fasilitas yang dibutuhkan tamu menggunakan checkbox:
   * [x] *Baby Chair (Kursi Bayi)*
   * [x] *Akses Kursi Roda*
   * [x] *Meja Dekat Jendela*
   * [x] *Dekorasi Khusus*
   * [x] *Lainnya*
2. Jika opsi **Lainnya** dicentang, kolom teks keterangan tambahan akan muncul otomatis (misal: *Perayaan ulang tahun, butuh tempat kue*).
3. Admin dapat mencentang beberapa opsi sekaligus atau membiarkannya kosong.
4. Klik **Lanjut: Tinjau Reservasi**.

### Langkah 6 — Tinjau & Simpan Reservasi
1. Sistem menampilkan tabel ringkasan lengkap data pemesanan:
   * Nama & nomor telepon pemesan.
   * Tanggal dan slot waktu 120 menit terpilih.
   * Alokasi nomor meja dan kapasitas kursi.
   * Catatan fasilitas khusus yang dipilih.
2. Jika ingin mengganti meja atau waktu, klik **Kembali Mengubah** (data nama dan telepon tetap utuh tanpa harus mengetik ulang).
3. Jika sudah sesuai, klik **Simpan Reservasi Sekarang**.
4. Sistem memverifikasi ulang jadwal di sisi server untuk mencegah bentrok, lalu menyimpan transaksi secara konsisten.

---

## 7. MENGELOLA RIWAYAT & STATUS RESERVASI

Pada tabel **Daftar Reservasi Meja** (`/reservations`):
* **Mengubah Data (Edit)**: Klik tombol **Edit** untuk memperbarui nama, nomor telepon, meja, waktu, atau catatan khusus. Checkbox catatan khusus otomatis tercentang sesuai pilihan sebelumnya.
* **Menandai Selesai**: Klik tombol **Selesai** saat tamu telah selesai bersantap untuk memfinalisasi status reservasi.
* **Membatalkan Reservasi**: Klik tombol **Batal** jika tamu membatalkan jadwal. Meja tersebut akan otomatis kembali berstatus **Tersedia** pada slot waktu terkait.
* **Buat Pesanan Langsung**: Klik tombol **Pesan** berwarna kuning untuk langsung membuka form kasir dan menautkan pesanan hidangan dengan reservasi tersebut.

---

## 8. PENGELOLAAN PESANAN KASIR & NOTA TAGIHAN

* Akses via menu navigasi: **Pesanan** (URL: `/orders`).
* **Membuat Pesanan Baru**:
  1. Klik tombol **Buat Pesanan Baru** (URL: `/orders/create`).
  2. Pilih nomor meja yang ditempati tamu.
  3. Masukkan kuantitas (porsi) pada hidangan yang dipesan (subtotal terhitung otomatis).
  4. Klik **Simpan Transaksi Pesanan**.
  5. Sistem menyalin harga menu saat ini (*price snapshot*) dan menghitung total tagihan secara atomik.
* **Pelunasan Kasir**:
  * Pada halaman rincian nota pesanan (`/orders/show?id=...`), klik tombol hijau **Pelunasan & Selesaikan Pesanan** saat pelanggan membayar tagihan.
  * Status pesanan berubah menjadi `completed` dan pendapatan tercatat ke kas nyata.

---

## 9. PENARIKAN LAPORAN REKAPITULASI FINANSIAL

* Akses via menu navigasi: **Laporan** (URL: `/reports`).
* **Menerapkan Filter Periode**:
  1. Tentukan tanggal mulai dan tanggal akhir periode evaluasi.
  2. Pilih filter status transaksi (Semua, Selesai/Lunas Saja, atau Belum Lunas).
  3. Klik **Terapkan Filter**.
* **Membaca Ringkasan Finansial**:
  * **Kas Diterima (Realized Revenue)**: Uang riil yang diterima kasir dari transaksi pesanan yang telah lunas.
  * **Total Nilai Pesanan (Gross)**: Seluruh akumulasi pesanan termasuk yang masih berstatus pending.
* **Mencetak Laporan**: Klik tombol **Cetak Format Bersih** untuk membuka jendela cetak resmi (*print-friendly*) yang siap dijadikan dokumen pertanggungjawaban operasional restoran.
