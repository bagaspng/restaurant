<?php
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i>Laporan Transaksi Restoran</h2>
        <p class="text-muted small mb-0">Rekapitulasi reservasi dan pendapatan pesanan berdasarkan periode dan status.</p>
    </div>
    <a href="/reports/print?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&status=<?= $selectedStatus ?>" 
       class="btn btn-outline-dark shadow-sm" target="_blank">
        <i class="bi bi-printer me-1"></i> Cetak Format Bersih
    </a>
</div>

<!-- Form Filter Rentang Tanggal -->
<div class="card p-3 bg-white shadow-sm mb-4">
    <form action="/reports" method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label for="start_date" class="form-label fw-semibold small">Tanggal Mulai</label>
            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $startDate ?>" required>
        </div>
        <div class="col-md-3">
            <label for="end_date" class="form-label fw-semibold small">Tanggal Akhir</label>
            <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $endDate ?>" required>
        </div>
        <div class="col-md-3">
            <label for="status" class="form-label fw-semibold small">Filter Status Pesanan</label>
            <select class="form-select" id="status" name="status">
                <option value="">-- Semua Status --</option>
                <option value="completed" <?= ($selectedStatus === 'completed') ? 'selected' : '' ?>>Selesai / Lunas Saja</option>
                <option value="pending" <?= ($selectedStatus === 'pending') ? 'selected' : '' ?>>Belum Lunas (Pending)</option>
                <option value="cancelled" <?= ($selectedStatus === 'cancelled') ? 'selected' : '' ?>>Dibatalkan Saja</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100 fw-semibold">
                <i class="bi bi-funnel me-1"></i> Terapkan Filter
            </button>
        </div>
    </form>
</div>

<!-- Kartu Ringkasan Finansial (BR-REP-02) -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card p-3 bg-white border-start border-4 border-success h-100">
            <span class="text-muted small fw-semibold">Kas Diterima (Realized Revenue):</span>
            <h3 class="fw-bold text-success mb-0 mt-1">
                Rp <?= number_format((float)($summary['realized_revenue'] ?? 0), 0, ',', '.') ?>
            </h3>
            <small class="text-muted mt-1"><?= (int)($summary['completed_orders'] ?? 0) ?> pesanan lunas</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 bg-white border-start border-4 border-primary h-100">
            <span class="text-muted small fw-semibold">Total Nilai Pesanan Aktif (Gross):</span>
            <h3 class="fw-bold text-primary mb-0 mt-1">
                Rp <?= number_format((float)($summary['gross_order_value'] ?? 0), 0, ',', '.') ?>
            </h3>
            <small class="text-muted mt-1">Termasuk pesanan pending yang belum dibayar</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 bg-white border-start border-4 border-secondary h-100">
            <span class="text-muted small fw-semibold">Pesanan Dibatalkan:</span>
            <h3 class="fw-bold text-secondary mb-0 mt-1">
                <?= (int)($summary['cancelled_orders'] ?? 0) ?> Pesanan
            </h3>
            <small class="text-muted mt-1">Nilai dibatalkan: Rp <?= number_format((float)($summary['cancelled_value'] ?? 0), 0, ',', '.') ?></small>
        </div>
    </div>
</div>

<!-- Nav Tab Laporan -->
<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
    <li class="nav-item">
        <button class="nav-link <?= ($tab === 'orders') ? 'active' : '' ?>" id="pills-orders-tab" data-bs-toggle="pill" data-bs-target="#pills-orders" type="button">
            <i class="bi bi-cart3 me-1"></i> Data Transaksi Pesanan (<?= count($orders) ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link <?= ($tab === 'reservations') ? 'active' : '' ?>" id="pills-res-tab" data-bs-toggle="pill" data-bs-target="#pills-res" type="button">
            <i class="bi bi-calendar-check me-1"></i> Data Reservasi Meja (<?= count($reservations) ?>)
        </button>
    </li>
</ul>

<div class="tab-content" id="pills-tabContent">
    <!-- Tab Pesanan -->
    <div class="tab-pane fade <?= ($tab === 'orders') ? 'show active' : '' ?>" id="pills-orders">
        <div class="card p-3 bg-white shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>ID Pesanan</th>
                            <th>Tanggal & Jam</th>
                            <th>Meja</th>
                            <th>Kasir / Staf</th>
                            <th>Total Tagihan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Tidak ada transaksi pesanan dalam periode ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $idx => $ord): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td><strong>#<?= $ord['id'] ?></strong></td>
                                    <td class="small"><?= date('d M Y, H:i', strtotime($ord['created_at'])) ?></td>
                                    <td>Meja <?= \App\Helpers\View::escape($ord['table_number']) ?></td>
                                    <td><?= \App\Helpers\View::escape($ord['staff_name']) ?></td>
                                    <td class="fw-bold">Rp <?= number_format((float)$ord['total_amount'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php
                                        $badge = match($ord['status']) {
                                            'pending' => '<span class="badge bg-warning text-dark">Pending</span>',
                                            'completed' => '<span class="badge bg-success">Lunas</span>',
                                            'cancelled' => '<span class="badge bg-secondary">Batal</span>',
                                            default => $ord['status']
                                        };
                                        echo $badge;
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab Reservasi -->
    <div class="tab-pane fade <?= ($tab === 'reservations') ? 'show active' : '' ?>" id="pills-res">
        <div class="card p-3 bg-white shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Waktu Mulai</th>
                            <th>Meja</th>
                            <th>Nama Pemesan</th>
                            <th>Kapasitas Meja</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reservations)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Tidak ada jadwal reservasi dalam periode ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reservations as $idx => $r): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td><?= date('d M Y, H:i', strtotime($r['start_time'])) ?> WIB</td>
                                    <td>Meja <?= \App\Helpers\View::escape($r['table_number']) ?></td>
                                    <td><strong><?= \App\Helpers\View::escape($r['customer_name']) ?></strong></td>
                                    <td><?= (int)$r['table_capacity'] ?> Tamu</td>
                                    <td>
                                        <?php
                                        $rb = match($r['status']) {
                                            'confirmed' => '<span class="badge bg-primary">Terkonfirmasi</span>',
                                            'completed' => '<span class="badge bg-success">Selesai</span>',
                                            'cancelled' => '<span class="badge bg-secondary">Batal</span>',
                                            default => $r['status']
                                        };
                                        echo $rb;
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
?>
