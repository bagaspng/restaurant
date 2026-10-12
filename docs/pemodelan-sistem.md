# DOKUMEN PEMODELAN SISTEM
## Sistem Pemesanan Restoran (*Restaurant Reservation & Ordering System*)

* **Kode Dokumen**: DOC-MOD-002
* **Versi**: 2.1.0 (Revisi Sistem Lokal - Aktor Tunggal: Administrator, Alur 6-Langkah Reservasi Terpadu, Penghapusan Master Pelanggan)
* **Status**: Disetujui
* **Tanggal Penyusunan**: 12 Oktober 2026
* **Penulis**: Mahasiswa Teknik Informatika (Kandidat Asesi BNSP)
* **Target Asesmen**: Skema Sertifikasi Pemrograman BNSP (9 Unit Kompetensi)

---

## 1. PENDAHULUAN
Dokumen Pemodelan Sistem ini merupakan spesifikasi rancang bangun perangkat lunak dari [docs/analisis-kebutuhan.md](file:///d:/Documents/Projek/restaurant/docs/analisis-kebutuhan.md). Sistem dirancang sebagai aplikasi lokal terintegrasi untuk kasir dan manajemen restoran dengan **Aktor Tunggal: Administrator**. Pemodelan ini mentransformasikan kebutuhan fungsional dan aturan bisnis ke dalam Diagram Konteks, DFD Level 1, Use Case Diagram, Activity Diagram, Entity Relationship Diagram (ERD), Class Diagram OOP, spesifikasi modul, dan Traceability Matrix.

---

## 2. DIAGRAM KONTEKS (CONTEXT DIAGRAM / DFD LEVEL 0)

Diagram konteks mendeskripsikan batas sistem dan interaksi aplikasi dengan satu entitas eksternal: **Administrator**.

```mermaid
flowchart TD
    Admin[Administrator]
    System((Sistem Pemesanan Restoran - Mode Lokal))

    %% Aliran Masuk
    Admin -->|Kelola Meja & Kapasitas| System
    Admin -->|Kelola Menu Makanan & Minuman| System
    Admin -->|Input Pemesan & Pilih Slot 120 Mnt| System
    Admin -->|Pilih Meja Berkapasitas & Catatan Khusus| System
    Admin -->|Pencatatan Pesanan Multi-Item| System
    Admin -->|Pelunasan Tagihan Kasir| System
    Admin -->|Filter Parameter Laporan| System

    %% Aliran Keluar
    System -->|Dashboard Metrik Operasional| System
    System -->|Ketersediaan Slot & Kartu Meja| Admin
    System -->|Hasil Validasi Bentrok Jadwal| Admin
    System -->|Nota Tagihan & Snapshot Harga| Admin
    System -->|Rekapitulasi Laporan Finansial & Kas| Admin
```

---

## 3. DATA FLOW DIAGRAM (DFD) LEVEL 1

DFD Level 1 menguraikan sistem operasional restoran menjadi 4 proses utama dan interaksinya dengan 5 penyimpan data (*data stores*):

```mermaid
flowchart TD
    Admin[Administrator]

    subgraph PROSES_OPERASIONAL
        P1["1.0 Kelola Master Meja & Menu"]
        P2["2.0 Kelola Reservasi Alur 6-Langkah"]
        P3["3.0 Kelola Pesanan & Snapshot Harga"]
        P4["4.0 Buat Rekapitulasi Laporan"]
    end

    subgraph DATA_STORES
        D1[("D1: restaurant_tables")]
        D2[("D2: menus")]
        D3[("D3: reservations")]
        D4[("D4: orders")]
        D5[("D5: order_items")]
    end

    %% P1 Master Data
    Admin -->|CRUD Meja & Menu| P1
    P1 -->|Simpan Data Meja| D1
    P1 -->|Simpan Data Menu| D2

    %% P2 Reservasi (Identitas Pemesan Tersimpan Langsung di D3)
    Admin -->|Input Pemesan, Slot 120 Mnt, Meja & Catatan| P2
    P2 <-->|Baca Kapasitas Meja & Status| D1
    P2 <-->|Validasi Jadwal Eksis (Cek Bentrok Slot)| D3
    P2 -->|Simpan Snapshot Pemesan & Reservasi| D3

    %% P3 Pesanan
    Admin -->|Input Pesanan Item Meja| P3
    P3 <-->|Baca Harga Menu Terkini| D2
    P3 <-->|Tautkan Meja / Reservasi| D3
    P3 -->|Simpan Header Pesanan| D4
    P3 -->|Simpan Snapshot Harga Detail Item| D5

    %% P4 Laporan
    Admin -->|Filter Rentang Tanggal & Status| P4
    P4 <-->|Rekap Data Reservasi| D3
    P4 <-->|Rekap Data Pesanan Lunas| D4
    P4 -->|Tampilan & Cetak Laporan Formal| Admin
```

---

## 4. USE CASE DIAGRAM & DESKRIPSI USE CASE

### 4.1 Use Case Diagram (Aktor Tunggal: Administrator)

```mermaid
flowchart LR
    Admin((Administrator))

    subgraph "Sistem Pemesanan Restoran (Local Station)"
        UC1([UC-01: Pantau Dashboard Metrik])
        UC2([UC-02: Kelola Data Meja])
        UC3([UC-03: Kelola Katalog Menu])
        UC4([UC-04: Buat Reservasi Alur 6-Langkah])
        UC5([UC-05: Cek Ketersediaan Slot & Meja])
        UC6([UC-06: Deteksi Bentrok Jadwal Interval])
        UC7([UC-07: Buat Pesanan Menu])
        UC8([UC-08: Snapshot & Hitung Total])
        UC9([UC-09: Cetak Struk & Pelunasan Kasir])
        UC10([UC-10: Cetak Laporan Finansial])
    end

    Admin --> UC1
    Admin --> UC2
    Admin --> UC3
    Admin --> UC4
    Admin --> UC7
    Admin --> UC9
    Admin --> UC10

    UC4 -.->|<<include>>| UC5
    UC4 -.->|<<include>>| UC6
    UC7 -.->|<<include>>| UC8
```

### 4.2 Deskripsi Use Case Terpilih

#### Deskripsi UC-04: Buat Reservasi Alur 6-Langkah
* **Aktor**: Administrator.
* **Prakondisi**: Master data meja telah tersedia di sistem.
* **Pascakondisi**: Reservasi tersimpan dengan status `confirmed`, identitas pemesan tersimpan permanen (*snapshot*), durasi alokasi 120 menit.
* **Skenario Normal**:
  1. Administrator membuka wizard reservasi baru.
  2. **Langkah 1**: Administrator mengisi nama pemesan dan nomor telepon kontak (tanpa memilih dari master pelanggan).
  3. **Langkah 2**: Administrator memilih tanggal kunjungan (tanggal lampau otomatis nonaktif).
  4. **Langkah 3**: Administrator memilih salah satu slot waktu operasional 120 menit (misal 12.00–14.00 WIB). Sistem menampilkan badge ketersediaan slot.
  5. **Langkah 4**: Administrator memilih meja dari kartu visual berdasarkan kapasitas kursi fisik yang dibutuhkan. Meja yang penuh/rusak dinonaktifkan.
  6. **Langkah 5**: Administrator memilih checkbox kebutuhan khusus (*baby chair*, kursi roda, jendela, dekorasi, lainnya).
  7. **Langkah 6**: Administrator meninjau ringkasan lengkap data pemesanan, kemudian menekan "Simpan Reservasi".
  8. Sistem memverifikasi ulang jadwal di server terhadap bentrok interval $(\text{Start Baru} < \text{End Lama}) \land (\text{End Baru} > \text{Start Lama})$.
  9. Sistem menyimpan data reservasi atomik dan menampilkan notifikasi berhasil.
* **Skenario Alternatif**:
  * **8a. Terjadi Bentrok Jadwal**: Meja baru saja dipesan oleh pemesanan lain. Sistem menolak transaksi dan meminta admin memilih meja atau slot lain tanpa menghapus data input identitas yang telah diisi.

---

## 5. ACTIVITY DIAGRAM

### 5.1 Activity Diagram: Alur Reservasi Meja 6 Langkah Terpadu

```mermaid
stateDiagram-v2
    [*] --> Langkah1_Identitas : Buka wizard reservasi
    Langkah1_Identitas --> Langkah2_Tanggal : Input nama pemesan & nomor kontak
    Langkah2_Tanggal --> Langkah3_Slot : Pilih tanggal valid (min hari ini)
    Langkah3_Slot --> Langkah4_Meja : Pilih slot operasional 120 Menit (Tersedia)
    Langkah4_Meja --> Langkah5_Catatan : Pilih kartu meja sesuai kapasitas kursi
    Langkah5_Catatan --> Langkah6_Tinjau : Pilih checkbox fasilitas khusus
    Langkah6_Tinjau --> Langkah4_Meja : Klik 'Kembali Mengubah' (Data tetap utuh)
    Langkah6_Tinjau --> ValidasiServer : Klik 'Simpan Reservasi'

    state ValidasiServer <<choice>>
    ValidasiServer --> TolakBentrok : Terjadi bentrok interval di server
    ValidasiServer --> SimpanDB : Bebas bentrok & meja aktif

    TolakBentrok --> Langkah6_Tinjau : Tampilkan pesan peringatan bentrok
    SimpanDB --> Selesai : Simpan reservasi status 'confirmed'
    Selesai --> [*]
```

### 5.2 Activity Diagram: Alur Pembuatan Pesanan & Transaksi Atomik

```mermaid
stateDiagram-v2
    [*] --> PilihMejaMenu : Pilih meja aktif dan tambah item menu
    PilihMejaMenu --> SubmitPesanan : Input kuantitas (>= 1) dan klik Simpan
    SubmitPesanan --> BukaTransaksiDB : Eksekusi PDO beginTransaction()

    BukaTransaksiDB --> SalinSnapshot : Salin nama menu dan harga master saat ini
    SalinSnapshot --> HitungServerSide : Hitung Subtotal & Total di Sisi Server
    HitungServerSide --> SimpanHeaderItem : Simpan tabel orders dan order_items

    state SimpanHeaderItem <<choice>>
    SimpanHeaderItem --> CommitDB : Seluruh data berhasil tersimpan
    SimpanHeaderItem --> RollbackDB : Terjadi error / anomali penyimpanan

    CommitDB --> TampilkanNota : Tampilkan ringkasan nota pesanan & total
    RollbackDB --> TampilkanError : Batalkan mutasi DB & tampilkan pesan error
    TampilkanNota --> [*]
    TampilkanError --> [*]
```

---

## 6. ENTITY RELATIONSHIP DIAGRAM (ERD)

Skema basis data relasional 3NF (*Third Normal Form*) dengan MySQL/MariaDB InnoDB (tanpa tabel master pelanggan):

```mermaid
erDiagram
    users ||--o{ reservations : "mencatat"
    users ||--o{ orders : "memproses"
    restaurant_tables ||--o{ reservations : "dialokasikan"
    restaurant_tables ||--o{ orders : "ditempati"
    reservations ||--o{ orders : "menghasilkan"
    orders ||--|{ order_items : "memuat rincian"
    menus ||--o{ order_items : "dipesan sebagai"

    users {
        int id PK
        string name "Nama admin lokal"
        string email UK "admin@resto.local"
        string password "Hash Bcrypt"
        string role "admin"
        datetime created_at
    }

    restaurant_tables {
        int id PK
        string table_number UK "Kode meja fisik, misal: T-01"
        int capacity "Kapasitas tamu >= 1"
        string status "available / occupied"
        datetime created_at
    }

    menus {
        int id PK
        string name "Nama item menu"
        string category "food / beverage / dessert"
        decimal price "DECIMAL(12,2) >= 0"
        boolean is_available "Tersedia / Habis"
        datetime created_at
    }

    reservations {
        int id PK
        int table_id FK "Relasi ke restaurant_tables"
        int user_id FK "Relasi ke users (Admin ID 1)"
        string customer_name "Snapshot nama pemesan"
        string customer_phone "Snapshot telepon pemesan"
        datetime start_time "Waktu mulai slot"
        datetime end_time "Waktu selesai (start + 120 mnt)"
        int guest_count "Nullable (audit riwayat lama)"
        string status "confirmed / completed / cancelled"
        string notes "Catatan terstruktur JSON"
        datetime created_at
    }

    orders {
        int id PK
        int reservation_id FK "NULL jika walk-in"
        int table_id FK "Relasi ke restaurant_tables"
        int user_id FK "Relasi ke users (Admin ID 1)"
        decimal total_amount "DECIMAL(12,2) kalkulasi server"
        string status "pending / completed / cancelled"
        datetime created_at
    }

    order_items {
        int id PK
        int order_id FK "Relasi ke orders"
        int menu_id FK "Relasi ke menus"
        string item_name "Snapshot nama menu saat pesan"
        decimal price_at_order "Snapshot harga DECIMAL(12,2)"
        int quantity "Kuantitas >= 1"
        decimal subtotal "DECIMAL(12,2) price * qty"
        datetime created_at
    }
```

---

## 7. CLASS DIAGRAM (DIAGRAM KELAS OOP)

Class Diagram ini memenuhi seluruh target pada Unit **J.620100.018.02 (Pemrograman Berorientasi Objek)**:
1. **Inheritance & Overriding**:
   * Kelas abstrak **`MenuItem`** diwarisi oleh subclass **`FoodItem`** dan **`BeverageItem`**, yang meng-override method `getServingType(): string`.
   * Kelas abstrak **`User`** diwarisi oleh **`Admin`**.
2. **Encapsulation**: Seluruh properti berakses `protected` atau `private` dengan `getter` dan `setter`.
3. **Polymorphism via Interface**: **`OrderCalculatorInterface`** diimplementasikan oleh **`StandardOrderCalculator`**.
4. **Pemisahan Lapisan**: Model/Entity, Service (Logika Bisnis), Repository (Akses PDO), dan Controller.

```mermaid
classDiagram
    %% Interface & Abstraction
    class OrderCalculatorInterface {
        <<interface>>
        +calculateSubtotal(float price, int qty) float
        +calculateTotal(array items) float
    }

    class StandardOrderCalculator {
        +calculateSubtotal(float price, int qty) float
        +calculateTotal(array items) float
    }

    OrderCalculatorInterface <|.. StandardOrderCalculator

    %% User & Admin
    class User {
        <<abstract>>
        #int id
        #string name
        #string email
        #string role
        +getId() int
        +getName() string
        +getRole() string
    }

    class Admin {
        +getPermissions() array
    }

    User <|-- Admin

    %% MenuItem Hierarchy (Inheritance & Overriding)
    class MenuItem {
        <<abstract>>
        #int id
        #string name
        #string category
        #float price
        #bool isAvailable
        +getId() int
        +getName() string
        +getPrice() float
        +isAvailable() bool
        +getServingType()* string
    }

    class FoodItem {
        -string spicinessLevel
        +getSpicinessLevel() string
        +getServingType() string
    }

    class BeverageItem {
        -bool isCold
        +isCold() bool
        +getServingType() string
    }

    MenuItem <|-- FoodItem
    MenuItem <|-- BeverageItem

    %% Entities
    class RestaurantTable {
        -int id
        -string tableNumber
        -int capacity
        -string status
        +getId() int
        +getTableNumber() string
        +getCapacity() int
        +isAvailable() bool
    }

    class Reservation {
        -int id
        -int tableId
        -int userId
        -string customerName
        -string customerPhone
        -string startTime
        -string endTime
        -int guestCount
        -string status
        -string notes
        +getCustomerName() string
        +getCustomerPhone() string
        +isConfirmed() bool
        +getStructuredRequests() array
    }

    class Order {
        -int id
        -int tableId
        -float totalAmount
        -string status
        -array items
        +addItem(OrderItem item) void
        +getTotalAmount() float
    }

    class OrderItem {
        -int id
        -string itemName
        -float priceAtOrder
        -int quantity
        -float subtotal
        +getSubtotal() float
    }

    Order "1" *-- "many" OrderItem

    %% Services & Repositories
    class ReservationService {
        -ReservationRepository reservationRepo
        -TableRepository tableRepo
        +calculateEndTime(string start, int duration) string
        +getOperatingSlots() array
        +getAvailability(string date) array
        +isCollision(int tableId, string start, string end) bool
        +validatePhoneNumber(string phone) bool
        +createReservation(array data) int
        +updateReservation(int id, array data) bool
    }

    class OrderService {
        -OrderRepository orderRepo
        -OrderCalculatorInterface calculator
        +createOrder(array header, array items) int
    }

    OrderService o-- OrderCalculatorInterface
```

---

## 8. SPESIFIKASI MODUL PROGRAM

| Modul | Controller / Service Terkait | Input | Proses Utama | Output | Penanganan Kesalahan |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Dashboard** | `DashboardController.php` | - | Agregasi data meja, pesanan hari ini, dan kas masuk. | Kartu metrik & ringkasan operasional harian. | Nilai 0 jika data kosong. |
| **Meja** | `TableController.php`<br>`TableRepository.php` | Nomor meja, kapasitas | Validasi nomor meja unik, kapasitas kursi $\ge 1$. | Data meja tersimpan di database. | Error: "Nomor meja sudah terdaftar / kapasitas minimal 1". |
| **Menu** | `MenuController.php`<br>`MenuRepository.php` | Nama, kategori, harga | Validasi harga $\ge 0$, tipe `DECIMAL(12,2)`. | Menu aktif di katalog. | Error: "Harga tidak boleh bernilai negatif". |
| **Reservasi** | `ReservationController.php`<br>`ReservationService.php` | Nama pemesan, telepon, tanggal, slot 120 mnt, meja, catatan | 1. Validasi identitas pemesan.<br>2. Validasi slot 120 mnt.<br>3. Pemilihan kartu meja berkapasitas.<br>4. Serialisasi catatan JSON.<br>5. Deteksi bentrok interval di server. | Reservasi `confirmed`. | Error: "Nomor telepon tidak valid" atau "Meja baru saja dipesan pada slot waktu tersebut". |
| **Pesanan** | `OrderController.php`<br>`OrderService.php` | ID Meja, array items [menu_id, qty] | 1. Buka transaksi DB.<br>2. Salin snapshot nama & harga.<br>3. Hitung subtotal & total.<br>4. Simpan header & detail.<br>5. Commit DB. | ID Pesanan tersimpan, nota tagihan. | Jika salah satu item gagal: eksekusi `rollBack()`, batalkan seluruh mutasi. |
| **Laporan** | `ReportController.php`<br>`ReportService.php` | Tanggal mulai, tanggal akhir, status | Filter query berdasarkan rentang tanggal dan status transaksi, pemisahan nilai finansial (Gross vs Realized). | Tampilan rekapitulasi & cetak bersih. | Notifikasi: "Tanggal mulai tidak boleh melebihi tanggal akhir". |

---

## 9. TRACEABILITY MATRIX (MATRIKS KETERLACAKAN SPESIFIKASI)

| ID Kebutuhan | Komponen Use Case | Class / Modul Pengimplementasi | File Terkait | Target Kasus Uji | Unit Kompetensi BNSP |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **FR-01 (Dashboard)** | UC-01 | `DashboardController`<br>`ReportService` | `app/Controllers/DashboardController.php` | `DashboardTest::testMetricsDisplay` | J.620100.016.01 |
| **FR-02 (Meja)** | UC-02 | `TableController`<br>`RestaurantTable` | `app/Controllers/TableController.php`<br>`app/Models/RestaurantTable.php` | `TableTest::testTableCapacityMustBeGreaterThanZero` | J.620100.017.02<br>J.620100.021.02 |
| **FR-03 (Menu & OOP)** | UC-03 | `MenuController`<br>`MenuItem` (`FoodItem`/`BeverageItem`) | `app/Controllers/MenuController.php`<br>`app/Models/MenuItem.php` | `MenuTest::testFoodItemInheritanceAndOverriding`<br>`MenuTest::testBeverageItemInheritanceAndOverriding` | J.620100.017.02<br>J.620100.018.02 |
| **FR-04 (Reservasi 6-Langkah)** | UC-04<br>UC-05<br>UC-06 | `ReservationController`<br>`ReservationService`<br>`ReservationRepository` | `app/Controllers/ReservationController.php`<br>`app/Services/ReservationService.php` | `ReservationTest::testGetOperatingSlotsStructure`<br>`ReservationTest::testValidatePhoneNumber`<br>`ReservationTest::testFormatNotesToStructuredJson` | J.620100.009.01<br>J.620100.017.02<br>J.620100.033.02 |
| **FR-05 (Deteksi Bentrok)** | UC-06 | `ReservationService`<br>`ReservationRepository` | `app/Services/ReservationService.php` | `ReservationTest::testCalculateEndTimeFixedTwoHours` | J.620100.017.02<br>J.620100.021.02 |
| **FR-06 (Pesanan & Snapshot)** | UC-07<br>UC-08 | `OrderService`<br>`OrderCalculatorInterface`<br>`StandardOrderCalculator` | `app/Services/OrderService.php`<br>`app/Services/StandardOrderCalculator.php` | `OrderCalculatorTest::testCalculateSubtotalPrecision`<br>`OrderCalculatorTest::testCalculateTotalMultiItems` | J.620100.018.02<br>J.620100.021.02<br>J.620100.033.02 |
| **FR-07 (Status)** | UC-04, UC-07, UC-09 | `Reservation`<br>`Order` | `app/Models/Reservation.php`<br>`app/Models/Order.php` | `ReservationTest::testReservationModelStructuredNotes` | J.620100.017.02 |
| **FR-08 (Laporan)** | UC-10 | `ReportController`<br>`ReportService` | `app/Controllers/ReportController.php`<br>`app/Services/ReportService.php` | `ReportService::getFinancialSummary` | J.620100.017.02<br>J.620100.021.02 |
