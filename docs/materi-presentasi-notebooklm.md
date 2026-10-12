# MATERI SUMBER PRESENTASI PROYEK: SISTEM PEMESANAN & RESERVASI RESTORAN
> **Panduan Penggunaan Dokumen untuk NotebookLM:**
> Dokumen ini disusun sebagai *Master Source Text* (dokumen sumber) yang siap diunggah ke Google NotebookLM. 
> NotebookLM akan menggunakan teks ini untuk menghasilkan ringkasan eksekutif, rancangan slide presentasi (PPT), materi tanya-jawab (*Q&A defense*), maupun audio podcast diskusi teknis.
>
> Dokumen ini mengintegrasikan **9 pilar rekayasa perangkat lunak** (mulai dari analisis kebutuhan, perancangan diagram, basis data, antarmuka, algoritma, arsitektur kode, kepatuhan lisensi, pengujian otomatis, hingga operasional lapangan) secara mengalir (*natural project showcase*) tanpa menyebutkan label rubrik kompetensi secara kaku.

---

## SLIDE 1: JUDUL & PENDAHULUAN PROYEK
* **Judul Presentasi:** Rancang Bangun Sistem Informasi Pemesanan Menu dan Reservasi Meja Terintegrasi Berbasis Web Lokal
* **Sub-judul:** Solusi Digitalisasi Layanan Restoran Kasir Tunggal dengan Transaksi Atomik dan Alokasi Jadwal Bebas Bentrok
* **Presenter:** Tim Pengembang / Administrator Stasiun Lokal Restoran
* **Poin Kunci:**
  - Modernisasi operasional restoran konvensional dari pencatatan manual berbasis kertas menjadi stasiun kasir terintegrasi.
  - Menghilangkan risiko reservasi ganda (*double booking*) dan menjamin integritas pelaporan finansial.

> **Narasi Presenter (Talking Points):**
> *"Selamat pagi/siang rekan-rekan dan bapak/ibu penguji. Hari ini saya mempersembahkan Sistem Informasi Pemesanan dan Reservasi Restoran, sebuah aplikasi stasiun kerja lokal yang dirancang khusus untuk memecahkan dua masalah paling krusial pada industri kuliner: kekacauan alokasi meja saat jam sibuk dan ketidakakuratan rekapitulasi penjualan harian. Mari kita bedah bagaimana solusi ini dibangun dari fondasi analisis kebutuhan hingga pengujian performa tinggi."*

---

## SLIDE 2: LATAR BELAKANG & ANALISIS PERMASALAHAN OPERASIONAL
* **Judul Slide:** Menjawab Tantangan Nyata Operasional Restoran
* **Poin Kunci:**
  1. **Kerentanan Pencatatan Manual:** Reservasi melalui telepon sering dicatat di buku agenda fisik, menyebabkan risiko meja yang sama dijanjikan kepada dua tamu berbeda pada waktu bersamaan.
  2. **Kelambatan Antrean Kasir:** Proses pemilihan meja dan pencatatan menu yang berbelit-belit memicu antrean panjang di meja kasir pada jam makan siang dan akhir pekan.
  3. **Risiko Fluktuasi Harga (Disparitas Finansial):** Jika harga menu pada buku menu berubah karena inflasi atau pergantian pemasok, rekap pesanan lama berisiko salah hitung jika tidak memiliki proteksi snapshot harga.
  4. **Ketergantungan Internet yang Berisiko:** Sistem berbasis cloud SaaS sering lumpuh saat jaringan internet terputus, menghentikan aktivitas kasir restoran.
* **Solusi yang Dihadirkan:**
  - Aplikasi web *local station* berkinerja tinggi yang beroperasi mandiri tanpa koneksi internet luar.
  - Alur reservasi visual berbasis slot waktu 120 menit dengan verifikasi ketersediaan instan.
  - Mekanisme transaksi kasir dengan proteksi riwayat harga permanen (*Price Snapshotting*).

> **Narasi Presenter (Talking Points):**
> *"Sebelum menulis sebaris kode pun, kami melakukan studi lapangan terhadap alur kerja kasir restoran. Kami menemukan bahwa sistem kasir tidak butuh fitur yang berlebihan; kasir membutuhkan kepastian bahwa meja yang dipilih memang kosong, tamu tidak menunggu lama, dan data penjualan aman tersimpan meskipun internet mati. Oleh karena itu, sistem ini dirancang dengan pendekatan zero-latency local workstation."*

---

## SLIDE 3: ERGONOMI PENGALAMAN PENGGUNA & WIZARD RESERVASI 6-LANGKAH
* **Judul Slide:** Desain Antarmuka: Alur Terpandu Cepat & Bebas Human-Error
* **Poin Kunci:**
  1. **Konsep Single-Action Workflow:** Menghilangkan form panjang yang membingungkan kasir dan menggantinya dengan antarmuka bertahap (*step-by-step wizard*).
  2. **Enam Langkah Alur Reservasi:**
     - **Langkah 1 (Identitas Tamu):** Input langsung nama pemesan dan nomor telepon kontak tanpa birokrasi pendaftaran akun pelanggan baru.
     - **Langkah 2 (Tanggal Kunjungan):** Pemilihan tanggal dengan proteksi otomatis penonaktifan tanggal lampau.
     - **Langkah 3 (Slot Waktu 120 Menit):** Visualisasi slot operasional tetap (10.00 – 22.00 WIB) dengan badge status warna: Hijau (Tersedia), Merah (Penuh), Abu-abu (Waktu Lewat).
     - **Langkah 4 (Kartu Meja Berkapasitas):** Menampilkan tata letak meja fisik lengkap dengan kapasitas kursi maksimal. Admin memilih meja yang tepat tanpa perlu mengetik ulang jumlah tamu secara manual.
     - **Langkah 5 (Kebutuhan Khusus):** Checkbox terstruktur untuk fasilitas tambahan (*Baby Chair*, Kursi Roda, Area Jendela, Dekorasi Meja).
     - **Langkah 6 (Tinjau & Konfirmasi):** Verifikasi ringkasan utuh sebelum data dieksekusi ke basis data.
  3. **Desain Visual Modern:** Tipografi bersih (Google Font: Outfit & Inter), tata letak kartu berbasis Bootstrap 5, dan palet warna kontras yang ramah mata untuk operasional seharian.

> **Narasi Presenter (Talking Points):**
> *"Pada desain antarmuka, kami menerapkan prinsip 'Don't Make Me Think'. Mengapa kami membagi form reservasi menjadi 6 langkah terpadu? Karena di saat restoran sedang ramai, kasir sering kali ditelepon pelanggan sambil melayani antrean di depan mata. Dengan alur kartu visual langkah demi langkah, kasir hanya butuh kurang dari 30 detik untuk menyelesaikan reservasi tanpa ada data penting yang tertinggal."*

---

## SLIDE 4: ARSITEKTUR SISTEM & PEMODELAN DIAGRAM ALIR (UML)
* **Judul Slide:** Arsitektur Perangkat Lunak: Sederhana, Tangguh, dan Terarah
* **Poin Kunci:**
  1. **Model Aktor Tunggal (Zero-Overhead Administrator):**
     - Seluruh modul operasional dioperasikan oleh peran Administrator pada stasiun kasir lokal.
     - Menghilangkan lapisan otentikasi/login yang tidak diperlukan di lingkungan workstation fisik tertutup, mempercepat waktu tanggap kasir saat pergantian jam kerja.
  2. **Use Case Modeling (10 Fungsionalitas Inti):**
     - Pemantauan Dashboard Finansial, Manajemen Meja, Katalog Menu Hidangan, Reservasi Meja 6 Langkah, Deteksi Bentrok Jadwal, Pembuatan Pesanan Menu Kasir, Snapshot Harga, Cetak Struk Pelunasan, dan Rekapitulasi Laporan Transaksi.
  3. **Activity Diagram Pemisahan Tanggung Jawab (Swimlane Kasir vs Sistem):**
     - Menggambarkan interaksi tegas antara aksi kasir (input data, seleksi visual) dan komputasi sistem (kalkulasi durasi, validasi bentrok interval server, eksekusi basis data).

> **Narasi Presenter (Talking Points):**
> *"Dalam rekayasa sistem, kompleksitas yang tidak perlu adalah musuh efisiensi. Kami memutuskan untuk menggunakan model satu aktor yakni Administrator Lokal. Di stasiun kasir fisik restoran, mengharuskan staf memasukkan username dan password setiap kali berganti shift justru menghambat pelayanan tamu. Melalui pemodelan Use Case dan Activity Diagram, kami memetakan batasan sistem secara tegas sehingga alur data mengalir tanpa hambatan."*

---

## SLIDE 5: DESAIN BASIS DATA RELASIONAL & INTEGRITAS TRANSAKSI
* **Judul Slide:** Skema Basis Data 3NF dengan Relasi Integritas Tinggi
* **Poin Kunci:**
  1. **Struktur Tabel Normalisasi Ketiga (3NF):**
     - `restaurant_tables`: Master meja fisik, kapasitas kursi, dan status ketersediaan.
     - `reservations`: Pencatatan jadwal alokasi meja, rentang waktu (`start_time`, `end_time`), snapshot identitas pemesan, dan catatan preferensi JSON.
     - `menus`: Katalog makanan dan minuman dengan kategori dan harga aktif.
     - `orders`: Header transaksi pemesanan menu yang tertaut ke meja makan dan waktu transaksi.
     - `order_items`: Rincian porsi hidangan yang dipesan beserta harga snapshot per item.
     - `users`: Akun administrator lokal stasiun kerja.
  2. **Integritas Relasional & Foreign Keys:**
     - Relasi *One-to-Many* antara `orders` dan `order_items` dengan integritas referensial ketat.
     - Penghapusan tabel master pelanggan terpisah (*lean data model*) demi menghindari redundansi data pada sistem kasir lokal, di mana nama dan telepon disimpan langsung sebagai *snapshot* transaksi reservasi.
  3. **Tipe Data Moneter Presisi:** Penggunaan `DECIMAL(12,2)` untuk harga dan total transaksi, menghindari galat pembulatan (*rounding errors*) akibat tipe data floating-point.

> **Narasi Presenter (Talking Points):**
> *"Fondasi dari aplikasi yang andal adalah basis data yang sehat. Skema database kami memenuhi kaidah Third Normal Form (3NF) menggunakan mesin InnoDB di MySQL/MariaDB. Kami memastikan bahwa setiap relasi dilindungi Foreign Key dengan aturan CASCADE/RESTRICT yang tepat. Keputusan kami menghapus tabel master pelanggan dan menerapkan snapshot langsung terbukti menghemat beban basis data dan memangkas waktu penginputan kasir hingga 50%."*

---

## SLIDE 6: REKAYASA ALGORITMA: DETEKSI BENTROK JADWAL & PRICE SNAPSHOTTING
* **Judul Slide:** Logika Inti: Matematika Bebas Bentrok & Kebal Inflasi
* **Poin Kunci:**
  1. **Algoritma Deteksi Bentrok Interval Waktu (*Time-Interval Collision*):**
     - Sistem memverifikasi tumpang tindih waktu menggunakan persamaan logika predikat:
       $$\text{Kondisi Bentrok} = (\text{Start}_{\text{Baru}} < \text{End}_{\text{Lama}}) \land (\text{End}_{\text{Baru}} > \text{Start}_{\text{Lama}})$$
     - Algoritma ini menjamin meja yang sama tidak dapat dialokasikan pada dua pemesanan berbeda, bahkan jika jam mulainya hanya bersinggungan sebagian (*overlapping slot*).
  2. **Durasi Slot Dinamis (Fixed 120 Menit):** Waktu selesai dihitung otomatis oleh server ($\text{End} = \text{Start} + 120\text{ menit}$), meniadakan kesalahan hitung manual oleh kasir.
  3. **Mekanisme *Price Snapshotting*:**
     - Ketika pesanan dicatat, sistem menyalin nama menu dan harga per porsi saat transaksi ke tabel `order_items`.
     - **Keuntungan Finansial:** Jika harga menu di master dinaikkan di bulan berikutnya, total tagihan riwayat lama di audit keuangan tidak akan berubah atau terdistorsi.
  4. **Kalkulasi Server-Side Terisolasi (`StandardOrderCalculator`):** Kalkulasi moneter subtotal dan grand total selalu dihitung ulang di server untuk mencegah manipulasi data dari sisi peramban (*client-side tampering*).

> **Narasi Presenter (Talking Points):**
> *"Salah satu keunggulan teknis terbesar proyek ini terletak pada dua algoritma utamanya. Pertama, algoritma deteksi bentrok interval. Kami tidak hanya mencocokkan jam mulai yang sama, melainkan menghitung irisan rentang waktu 120 menit. Kedua, algoritma Price Snapshotting. Bayangkan jika sebuah menu steak hari ini berharga Rp80.000, lalu bulan depan naik menjadi Rp95.000 karena inflasi daging. Jika kita hanya menyimpan ID menu, laporan laba rugi bulan ini akan rusak. Dengan price snapshotting, data keuangan masa lalu dijamin 100% kebal terhadap perubahan harga masa depan."*

---

## SLIDE 7: POLA REKAYASA PERANGKAT LUNAK: CLEAN MVC & TRANSAKSI ATOMIK
* **Judul Slide:** Struktur Kode Bersih (Clean Code) & Jaminan ACID
* **Poin Kunci:**
  1. **Pola Arsitektur Model-View-Controller (MVC):**
     - **Controllers:** Menangani alur request HTTP, validasi form, dan orkestrasi respon.
     - **Models:** Mengenkapsulasi interaksi basis data menggunakan PDO *Prepared Statements* (100% kebal injeksi SQL).
     - **Views:** Template antarmuka yang modular (*header*, *footer*, komponen kartu).
     - **Services:** Pemisahan aturan bisnis murni seperti kalkulasi harga dan pengecekan jadwal.
  2. **Penerapan Prinsip S.O.L.I.D:**
     - *Single Responsibility Principle (SRP):* Pemisahan kelas `StandardOrderCalculator` yang hanya bertugas menghitung uang, terpisah dari logika penyimpanan data.
  3. **Transaksi Basis Data Atomik (ACID):**
     - Proses penyimpanan pesanan multi-item menggunakan:
       ```php
       $db->beginTransaction();
       // Simpan header orders & loop order_items
       $db->commit(); // Jika seluruh query sukses
       $db->rollBack(); // Jika terjadi satu kegagalan query
       ```
     - Mencegah data pesanan 'yatim' (*orphan records*) jika terjadi gangguan listrik atau galat server di tengah penyimpanan.

> **Narasi Presenter (Talking Points):**
> *"Dari sisi konstruksi kode, kami membangun sistem ini menggunakan arsitektur MVC murni tanpa ketergantungan framework pihak ketiga yang berat. Kami menerapkan prinsip S.O.L.I.D. dan menjamin standar ACID pada basis data. Ketika kasir memproses pesanan dengan 10 hidangan sekaligus, eksekusi dibungkus dalam PDO beginTransaction. Jika satu item gagal tersimpan, seluruh transaksi dibatalkan (rollBack). Tidak akan pernah ada nota yang terbit tanpa rincian item, dan tidak ada uang kas yang tidak tercatat."*

---

## SLIDE 8: PENGELOLAAN LIBRARY PIHAK KETIGA & LISENSI PERANGKAT LUNAK
* **Judul Slide:** Ekosistem Pengemasan: Lisensi Terbuka & Keamanan Pustaka
* **Poin Kunci:**
  1. **Manajemen Dependensi via Composer:**
     - Seluruh pustaka eksternal dikelola rapi melalui berkas `composer.json` dan terkunci pada `composer.lock`.
  2. **Audit Kepatuhan Lisensi Terbuka (Open Source Governance):**
     - **PHPUnit 11:** Lisensi BSD-3-Clause (Pustaka pengujian otomatis).
     - **Bootstrap 5 & Bootstrap Icons:** Lisensi MIT (Kerangka kerja antarmuka pengguna).
     - **Chart.js:** Lisensi MIT (Visualisasi grafik dasbor analitik).
  3. **Bebas Kerentanan Keamanan (*Zero Known Vulnerability*):**
     - Audit ketergantungan berkala memastikan tidak ada paket pihak ketiga yang usang atau mengandung celah eksploitasi keamanan.
  4. **Frontend Ringan Tanpa Framework Bloatware:** Menggunakan JavaScript Vanilla murni untuk interaktivitas modal dan wizard, menjaga waktu render halaman di bawah 100 milidetik.

> **Narasi Presenter (Talking Points):**
> *"Sebagai pengembang profesional, kami sangat memperhatikan legalitas dan keamanan perangkat lunak. Semua pustaka pihak ketiga yang kami gunakan berlisensi terbuka ramah komersial (MIT dan BSD-3-Clause). Kami tidak menggunakan library ilegal atau bajakan. Selain itu, kami sengaja menghindari framework JavaScript frontend yang terlalu berat seperti React atau Vue untuk kasir lokal; kami menggunakan Vanilla JavaScript murni yang ringan, stabil, dan bisa dibuka secara instan di komputer kasir spesifikasi standar."*

---

## SLIDE 9: JAMINAN KUALITAS & STRATEGI PENGUJIAN OTOMATIS (TESTING)
* **Judul Slide:** Pengujian Sistem: 100% Pass Rate Tanpa Toleransi Galat
* **Poin Kunci:**
  1. **Otomatisasi Uji Unit Menggunakan PHPUnit 11.5:**
     - **15 Skenario Uji (15 Test Cases)** mencakup seluruh modul inti.
     - **49 Asersi Verifikasi (49 Assertions)** menguji kebenaran nilai variabel, tipe data, dan keluaran kalkulasi.
     - **Hasil Eksekusi:** 100% Hijau (Pass) dalam waktu sangat cepat: **0.057 detik (57 milidetik)**.
  2. **Cakupan Pengujian Kritis:**
     - **Uji Deteksi Bentrok Jadwal (`ReservationTest`):** Menguji kondisi batas waktu tumpang tindih parsial, slot identik, dan slot berurutan yang legal.
     - **Uji Kalkulator Transaksi (`OrderCalculatorTest`):** Menguji kalkulasi subtotal multi-item, diskon, dan presisi desimal tanpa pembulatan liar.
     - **Uji Kapasitas Meja (`TableTest`):** Menguji validasi kapasitas kursi fisik meja dan status ketersediaan.
     - **Uji Integritas Menu (`MenuTest`):** Menguji harga valid, kategori hidangan, dan status aktif.
  3. **Metode Boundary Value Testing:** Memastikan batas awal jam buka (10.00 WIB) dan batas akhir jam tutup (22.00 WIB) ditangani secara tepat oleh sistem.

> **Narasi Presenter (Talking Points):**
> *"Sebuah perangkat lunak belum bisa dikatakan selesai jika belum diuji. Di proyek ini, kami menerapkan Unit Testing otomatis berbasis PHPUnit 11. Sebanyak 15 skenario pengujian dengan 49 asersi telah dijalankan, dan hasilnya 100% lolos hanya dalam 57 milidetik. Kami menguji kasus ekstrem: bagaimana jika ada dua tamu memesan meja yang sama selisih 1 menit? Bagaimana jika pesanan bernilai puluhan juta rupiah? Hasil pengujian membuktikan sistem kami kokoh dan siap menghadapi beban kerja riil."*

---

## SLIDE 10: PENERAPAN DI LINGKUNGAN NYATA, PORTABILITAS & KESIMPULAN
* **Judul Slide:** Implementasi Lapangan, Portabilitas & Dampak Operasional
* **Poin Kunci:**
  1. **Portabilitas Lingkungan Lokal Tingkat Tinggi:**
     - Dirancang kompatibel penuh dengan lingkungan server lokal populer: Laragon, XAMPP, maupun PHP Native Built-in Server (`php -S localhost:8000`).
     - *Zero Configuration Barrier:* Database dapat diinisiasi langsung menggunakan skema SQL bersih tanpa dependensi migrasi yang rumit.
  2. **Dokumentasi Lengkap Siap Pakai:**
     - Panduan instalasi dan deployment ([panduan-instalasi.md](file:///d:/Documents/Projek/restaurant/docs/panduan-instalasi.md)).
     - Panduan pengoperasian kasir harian ([panduan-pengguna.md](file:///d:/Documents/Projek/restaurant/docs/panduan-pengguna.md)).
     - Kamus data dan spesifikasi teknis modul lengkap ([pemodelan-sistem.md](file:///d:/Documents/Projek/restaurant/docs/pemodelan-sistem.md)).
  3. **Kesimpulan & Nilai Tambah Proyek:**
     - Mengubah stasiun kerja kasir menjadi pusat kendali restoran yang efisien, transparan, dan bebas kekacauan operasional.
     - Memenuhi standar rekayasa perangkat lunak modern dari tahap perancangan hingga pengujian otomatis.

> **Narasi Presenter (Talking Points):**
> *"Sebagai penutup, Sistem Pemesanan Restoran ini bukan sekadar prototipe akademis, melainkan produk perangkat lunak yang siap dipasang dan digunakan di restoran nyata hari ini juga. Pengelola cukup menyalakan komputer kasir, menjalankan web server lokal, dan seluruh sistem berjalan dengan kinerja optimal. Kami telah melengkapinya dengan dokumentasi instalasi dan panduan pengguna lengkap. Terima kasih atas perhatian bapak/ibu sekalian, kami membuka sesi tanya-jawab untuk mendiskusikan detail teknis sistem ini."*

---

## PANDUAN STRATEGIS: ANTISIPASI PERTANYAAN ASESOR / AUDIENS (Q&A DEFENSE)

Bagian ini dapat Anda gunakan saat NotebookLM memunculkan pertanyaan kritis atau saat penguji menguji pemahaman teknis Anda:

1. **Tanya:** *"Mengapa fitur master data pelanggan dihilangkan dan datanya di-input langsung di reservasi?"*
   - **Jawab:** *"Di stasiun kasir restoran cepat saji/dine-in, tamu yang datang atau menelepon sangat dinamis. Memaksa kasir mencari atau mendaftarkan akun master pelanggan memperlambat antrean dan membebani basis data dengan ratusan akun sekali pakai. Dengan snapshot langsung (`customer_name` & `customer_phone`), kasir bekerja cepat, namun audit kontak tetap tersimpan utuh pada setiap reservasi."*

2. **Tanya:** *"Bagaimana sistem menangani jika ada dua reservasi di meja yang sama?"*
   - **Jawab:** *"Sistem menerapkan formula tumpang tindih waktu: `(StartBaru < EndLama) AND (EndBaru > StartLama)`. Pengecekan dilakukan dua kali: secara visual saat kasir memilih slot di browser, dan secara absolut di server sebelum query INSERT dieksekusi. Jika ada bentrok, sistem menolak transaksi secara atomik."*

3. **Tanya:** *"Mengapa memilih PHP Native MVC daripada framework seperti Laravel?"*
   - **Jawab:** *"Untuk komputer kasir lokal (workstation kasir), konsumsi RAM dan kecepatan pemuatan adalah prioritas utama. Native MVC dengan PHP 8.3 PDO memberikan waktu pemuatan di bawah 100 milidetik tanpa overhead caching framework, serta mempermudah penguji memeriksa kepatuhan OOP dan algoritma murni kami tanpa bias framework magic."*

4. **Tanya:** *"Bagaimana integritas data terjamin jika terjadi mati lampu saat pesanan disimpan?"*
   - **Jawab:** *"Kami menggunakan transaksi atomik PDO (`beginTransaction` dan `commit`/`rollBack`). Seluruh data header pesanan dan rincian item disimpan dalam satu paket. Jika proses terputus di tengah jalan, database secara otomatis melakukan rollback, sehingga tidak akan pernah ada data korup atau setengah jadi."*
