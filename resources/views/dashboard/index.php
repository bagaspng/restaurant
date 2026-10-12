<?php
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">
            <i class="bi bi-speedometer2 me-2 text-primary"></i>
            Dashboard Operasional Restoran
        </h2>
        <p class="text-muted small mb-0">
            Panel kendali terpusat operasional restoran (Mode Administrator Lokal).
        </p>
    </div>
    <div class="text-end text-muted small">
        <i class="bi bi-clock me-1"></i> <?= date('l, d F Y') ?>
    </div>
</div>

<!-- 4 Kartu Metrik Utama -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 bg-white h-100 border-start border-4 border-primary shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold">Status Meja</span>
                    <h3 class="fw-bold mb-0 text-dark"><?= $availableTables ?> / <?= $totalTables ?></h3>
                    <small class="text-success fw-semibold"><?= $availableTables ?> Meja Tersedia</small>
                </div>
                <div class="fs-1 text-primary"><i class="bi bi-grid-3x3"></i></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3 bg-white h-100 border-start border-4 border-info shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold">Reservasi Hari Ini</span>
                    <h3 class="fw-bold mb-0 text-dark"><?= count($todayReservations) ?></h3>
                    <small class="text-muted">Jadwal meja tercatat</small>
                </div>
                <div class="fs-1 text-info"><i class="bi bi-calendar-check"></i></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3 bg-white h-100 border-start border-4 border-warning shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold">Pesanan Hari Ini</span>
                    <h3 class="fw-bold mb-0 text-dark"><?= count($todayOrders) ?></h3>
                    <small class="text-muted"><?= (int)($financialSummary['pending_orders'] ?? 0) ?> belum lunas</small>
                </div>
                <div class="fs-1 text-warning"><i class="bi bi-cart3"></i></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3 bg-white h-100 border-start border-4 border-success shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold">Pendapatan Hari Ini</span>
                    <h4 class="fw-bold mb-0 text-success">
                        Rp <?= number_format((float)($financialSummary['realized_revenue'] ?? 0), 0, ',', '.') ?>
                    </h4>
                    <small class="text-muted">Kas pesanan lunas</small>
                </div>
                <div class="fs-1 text-success"><i class="bi bi-cash-stack"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Pintasan Aksi Cepat -->
<div class="row g-4 mb-4">
    <div class="col-md-7">
        <div class="card p-3 bg-white shadow-sm h-100">
            <h5 class="fw-bold mb-3 border-bottom pb-2">
                <i class="bi bi-calendar-event me-2 text-primary"></i>
                Reservasi Meja Terjadwal Hari Ini
            </h5>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu</th>
                            <th>Meja</th>
                            <th>Pemesan</th>
                            <th>Kontak</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($todayReservations)): ?>
                            <tr><td colspan="5" class="text-center py-3 text-muted">Belum ada reservasi untuk hari ini.</td></tr>
                        <?php else: ?>
                            <?php foreach (array_slice($todayReservations, 0, 5) as $res): ?>
                                <tr>
                                    <td><?= date('H:i', strtotime($res['start_time'])) ?> - <?= date('H:i', strtotime($res['end_time'])) ?></td>
                                    <td><strong>Meja <?= \App\Helpers\View::escape($res['table_number']) ?></strong></td>
                                    <td><?= \App\Helpers\View::escape($res['customer_name']) ?></td>
                                    <td><small class="text-muted"><?= \App\Helpers\View::escape($res['customer_phone']) ?></small></td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= $res['status'] ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-3">
                <a href="/reservations" class="btn btn-sm btn-outline-primary">Lihat Seluruh Reservasi &rarr;</a>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card p-3 bg-white shadow-sm h-100">
            <h5 class="fw-bold mb-3 border-bottom pb-2">
                <i class="bi bi-lightning-charge me-2 text-warning"></i>
                Aksi Cepat Operasional
            </h5>
            <div class="d-grid gap-2">
                <a href="/reservations/create" class="btn btn-outline-primary text-start py-2">
                    <i class="bi bi-calendar-plus me-2"></i> Buat Reservasi Meja Baru
                </a>
                <a href="/orders/create" class="btn btn-outline-success text-start py-2">
                    <i class="bi bi-cart-plus me-2"></i> Buat Pesanan Makanan / Minuman
                </a>
                <a href="/tables" class="btn btn-outline-secondary text-start py-2">
                    <i class="bi bi-grid-3x3 me-2"></i> Kelola Kapasitas & Status Meja
                </a>
                <a href="/reports" class="btn btn-outline-dark text-start py-2">
                    <i class="bi bi-file-earmark-bar-graph me-2"></i> Rekapitulasi Laporan Finansial
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
?>
