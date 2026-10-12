<?php
ob_start();
?>

<div class="row align-items-center justify-content-center py-5">
    <div class="col-lg-8 text-center">
        <div class="mb-4">
            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold">
                <i class="bi bi-award-fill me-1"></i> Proyek Portofolio Sertifikasi BNSP
            </span>
        </div>
        <h1 class="display-4 fw-bold text-dark mb-3">Sistem Pemesanan Restoran</h1>
        <p class="lead text-muted mb-4">
            Aplikasi operasional terintegrasi untuk manajemen meja, katalog menu, pencegahan bentrok reservasi otomatis, dan kalkulasi pesanan presisi.
        </p>

        <div class="d-flex justify-content-center gap-3 mb-5">
            <a href="/login" class="btn btn-primary btn-lg px-4 shadow-sm">
                <i class="bi bi-box-arrow-in-right me-2"></i> Masuk ke Sistem
            </a>
            <a href="/health" class="btn btn-outline-secondary btn-lg px-4" target="_blank">
                <i class="bi bi-activity me-2"></i> Status Sistem (Health Check)
            </a>
        </div>

        <div class="row g-4 text-start mt-2">
            <div class="col-md-4">
                <div class="card p-3 h-100 bg-white">
                    <div class="text-primary fs-3 mb-2"><i class="bi bi-calendar-event"></i></div>
                    <h5 class="fw-bold">Cek Bentrok Jadwal</h5>
                    <p class="small text-muted mb-0">Algoritma otomatis mendeteksi tumpang tindih waktu reservasi pada meja yang sama.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 h-100 bg-white">
                    <div class="text-success fs-3 mb-2"><i class="bi bi-receipt-cutoff"></i></div>
                    <h5 class="fw-bold">Price Snapshotting</h5>
                    <p class="small text-muted mb-0">Harga menu disalin saat pemesanan untuk menjamin integritas transaksi historis.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 h-100 bg-white">
                    <div class="text-danger fs-3 mb-2"><i class="bi bi-shield-lock"></i></div>
                    <h5 class="fw-bold">RBAC & Keamanan</h5>
                    <p class="small text-muted mb-0">Pemisahan wewenang Admin dan Staf, proteksi CSRF, dan sanitasi XSS.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
?>
