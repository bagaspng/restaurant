# DOKUMEN SPESIFIKASI DAN DOKUMENTASI MODUL
## Sistem Pemesanan Restoran (*Restaurant Reservation & Ordering System*)

* **Kode Dokumen**: DOC-MOD-006
* **Versi**: 2.1.0 (Revisi Alur Reservasi Terpadu 6-Langkah, Slot 120 Menit, Kartu Meja Kapasitas & Snapshot Pemesan)
* **Status**: Disetujui
* **Tanggal Penyusunan**: 12 Oktober 2026
* **Penulis**: Mahasiswa Teknik Informatika (Kandidat Asesi BNSP)
* **Target Asesmen**: Skema Sertifikasi Pemrograman BNSP (Unit J.620100.023.02 — Membuat Dokumentasi Kode Program)

---

## 1. PENDAHULUAN
Dokumen ini mendokumentasikan rincian teknis setiap modul kode program pada Sistem Pemesanan Restoran dalam mode lokal terintegrasi. Dokumentasi mencakup tanggung jawab modul, daftar parameter, tipe data kembalian (*return types*), hierarki OOP, algoritma utama, dan penanganan eksepsi (*exception handling*).

---

## 2. MODUL 1: DASHBOARD & IDENTITAS ADMINISTRATOR LOKAL

* **Controller**: `App\Controllers\DashboardController`
* **Helper**: `App\Helpers\Session`
* **Model**: `App\Models\User`, `App\Models\Admin`

### 2.1 Inisialisasi Sesi Otomatis (Local Mode)
* **Tujuan**: Memastikan pengguna lokal langsung bekerja dengan identitas Administrator (ID: 1) tanpa melalui form login.
* **Implementasi**: Dilakukan pada berkas pintu masuk `public/index.php`:
  ```php
  if (!Session::has('user')) {
      Session::set('user', [
          'id' => 1,
          'name' => 'Administrator',
          'email' => 'admin@resto.local',
          'role' => 'admin'
      ]);
  }
  ```
* **Manfaat Integritas**: Menjamin kueri `INSERT` pada tabel `reservations` dan `orders` selalu memiliki nilai Foreign Key `user_id = 1` yang valid di basis data MySQL.

---

## 3. MODUL 2: MANAJEMEN MEJA RESTORAN (TABLES)

* **Controller**: `App\Controllers\TableController`
* **Repository**: `App\Repositories\TableRepository`
* **Model**: `App\Models\RestaurantTable`

### 3.1 Method Kunci: `TableRepository::delete(int $id): bool`
* **Tujuan**: Menghapus meja fisik dari basis data dengan perlindungan integritas referensial.
* **Parameter**: `$id` (int): ID primer meja.
* **Penanganan Eksepsi (Exception Handling)**:
  ```php
  try {
      $stmt = $this->db->prepare("DELETE FROM restaurant_tables WHERE id = :id");
      return $stmt->execute([':id' => $id]);
  } catch (PDOException $e) {
      if ($e->getCode() === '23000') { // Integrity Constraint Violation
          throw new RuntimeException("Meja tidak dapat dihapus karena masih memiliki riwayat reservasi atau pesanan.");
      }
      throw $e;
  }
  ```

---

## 4. MODUL 3: KATALOG MENU & HIERARKI OOP (MENUS)

* **Controller**: `App\Controllers\MenuController`
* **Repository**: `App\Repositories\MenuRepository`
* **Model Abstrak**: `App\Models\MenuItem`
* **Subclasses**: `App\Models\FoodItem`, `App\Models\BeverageItem`

### 4.1 Penerapan Konsep OOP (Unit J.620100.018.02)
* **Inheritance & Abstraction**:
  * Kelas abstrak `MenuItem` mendefinisikan atribut umum (`id`, `name`, `category`, `price`, `isAvailable`) dengan method abstrak:
    ```php
    abstract public function getServingType(): string;
    ```
* **Method Overriding**:
  * `FoodItem` meng-override `getServingType()` untuk mengembalikan deskripsi penyajian dapur hangat dan tingkat kepedasan (`spicinessLevel`).
  * `BeverageItem` meng-override `getServingType()` untuk mengembalikan deskripsi penyajian bar minuman segar (`isCold` / dingin dengan es atau hangat).

### 4.2 Validasi Sisi Server (Server-Side Validation):
* Memastikan `$price >= 0.00` dan disimpan menggunakan tipe `DECIMAL(12,2)`.
* Menolak penghapusan menu jika ID menu telah terasosiasi dengan `order_items` (Foreign Key `ON DELETE RESTRICT`).

---

## 5. MODUL 4: RESERVASI ALUR 6-LANGKAH & DETEKSI BENTROK (RESERVATIONS)

* **Controller**: `App\Controllers\ReservationController`
* **Service**: `App\Services\ReservationService`
* **Repository**: `App\Repositories\ReservationRepository`
* **Model**: `App\Models\Reservation`

### 5.1 Alur Kerja 6-Langkah Terpadu
1. **Langkah 1 (Identitas Pemesan)**: Mengisi nama pemesan (min 2 karakter) dan nomor telepon (10–16 digit numerik). Data disimpan langsung sebagai snapshot transaksi pada reservasi tanpa membuat master data pelanggan.
2. **Langkah 2 (Pilih Tanggal)**: Memilih tanggal melalui date picker, menonaktifkan tanggal yang telah berlalu.
3. **Langkah 3 (Pilih Slot Waktu)**: Durasi tetap 120 menit (2 Jam) dari jam operasional restoran (10.00–22.00 WIB). Ketersediaan slot dihitung dinamis.
4. **Langkah 4 (Pilih Meja Berdasarkan Kapasitas)**: Menampilkan kartu meja visual yang menampilkan kapasitas maksimal dan status ketersediaan pada slot terpilih.
5. **Langkah 5 (Pilih Catatan Khusus)**: Pilihan terstandarisasi berbasis checkbox: *Baby chair*, *Akses kursi roda*, *Meja dekat jendela*, *Dekorasi khusus*, dan opsi *Lainnya* (kondisional teks input). Disimpan sebagai JSON terstruktur.
6. **Langkah 6 (Tinjau & Simpan)**: Tinjauan ringkasan menyeluruh sebelum disimpan ke basis data secara atomik.

### 5.2 Method Kunci Service Reservasi
* **`calculateEndTime(string $startTime, ?int $durationMinutes = 120): string`**: Menghitung waktu selesai otomatis dengan menambahkan durasi tetap 120 menit.
* **`getOperatingSlots(): array`**: Menghasilkan array slot operasional terstruktur berdasarkan konfigurasi jam buka, jam tutup, dan durasi slot.
* **`getAvailability(string $date): array`**: Mengembalikan ketersediaan real-time setiap slot dan setiap kartu meja pada tanggal tertentu.
* **`isCollision(int $tableId, string $startTime, string $endTime, ?int $excludeId = null): bool`**:
  * Menguji kondisi bentrok jadwal interval:
    $$(\text{Start Baru} < \text{End Lama}) \land (\text{End Baru} > \text{Start Lama}) \land (\text{Status} = \text{'confirmed'})$$
* **`formatNotesToStructuredJson(array $requests, ?string $otherNote): string`**: Memvalidasi hanya pilihan yang diizinkan sistem dan mengemasnya dalam format JSON.
* **`formatNotesSummary(?string $notes): string`**: Mengurai kembali string JSON menjadi label ramah pengguna untuk ditampilkan pada daftar reservasi.

---

## 6. MODUL 5: PESANAN DAN PERHITUNGAN TRANSAKSIONAL (ORDERS)

* **Controller**: `App\Controllers\OrderController`
* **Service**: `App\Services\OrderService`
* **Repository**: `App\Repositories\OrderRepository`
* **Interface Polimorfik**: `App\Services\OrderCalculatorInterface`
* **Implementasi**: `App\Services\StandardOrderCalculator`
* **Model**: `App\Models\Order`, `App\Models\OrderItem`

### 6.1 Algoritma Transaksi Atomik & Price Snapshotting (`OrderService::createOrder`)
1. Mulai transaksi database: `$db->beginTransaction()`.
2. Simpan header `orders` dengan status `pending`.
3. Iterasi setiap item yang dipesan:
   * Ambil data aktual menu saat ini: `name` dan `price`.
   * Salin nama dan harga ke array detail (*Price Snapshotting*).
   * Hitung subtotal via kalkulator: `calculateSubtotal($price, $qty)`.
   * Simpan detail ke tabel `order_items`.
   * Akumulasikan ke `$grandTotal`.
4. Perbarui nilai `orders.total_amount` dengan `$grandTotal`.
5. Eksekusi `$db->commit()`.
6. Jika terjadi kesalahan pada langkah manapun: `$db->rollBack()` membatalkan seluruh mutasi secara bersih.

---

## 7. MODUL 6: LAPORAN REKAPITULASI (REPORTS)

* **Controller**: `App\Controllers\ReportController`
* **Service**: `App\Services\ReportService`

### 7.1 Method Kunci: `ReportService::getFinancialSummary(array $orders): array`
* **Tujuan**: Memisahkan nilai finansial pesanan secara akuntabel (BR-REP-02).
* **Struktur Output**:
  * `gross_order_value`: Total seluruh pesanan aktif (pending + completed).
  * `realized_revenue`: HANYA akumulasi pesanan yang berstatus `completed` (kas masuk nyata).
  * `cancelled_value`: Total nilai pesanan yang dibatalkan.
