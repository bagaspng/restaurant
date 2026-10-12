<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= \App\Helpers\View::escape($title ?? 'Cetak Laporan') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 14px; color: #000; background: #fff; }
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12px; }
        }
    </style>
</head>
<body class="p-4">

    <div class="no-print mb-3 text-end">
        <button class="btn btn-primary btn-sm" onclick="window.print()">Cetak Halaman Ini</button>
        <button class="btn btn-secondary btn-sm" onclick="window.close()">Tutup</button>
    </div>

    <div class="text-center border-bottom pb-3 mb-4">
        <h2 class="fw-bold mb-0"><?= \App\Helpers\View::escape(\Config\Config::get('app_name')) ?></h2>
        <h5 class="text-muted">Laporan Rekapitulasi Transaksi Penjualan</h5>
        <div class="small">Periode: <?= date('d M Y', strtotime($startDate)) ?> s.d. <?= date('d M Y', strtotime($endDate)) ?></div>
    </div>

    <!-- Ringkasan Angka Finansial -->
    <div class="row mb-4">
        <div class="col-4">
            <div class="p-2 border rounded">
                <small class="text-muted d-block">Kas Diterima (Realized):</small>
                <strong>Rp <?= number_format((float)($summary['realized_revenue'] ?? 0), 0, ',', '.') ?></strong>
            </div>
        </div>
        <div class="col-4">
            <div class="p-2 border rounded">
                <small class="text-muted d-block">Total Nilai Pesanan (Gross):</small>
                <strong>Rp <?= number_format((float)($summary['gross_order_value'] ?? 0), 0, ',', '.') ?></strong>
            </div>
        </div>
        <div class="col-4">
            <div class="p-2 border rounded">
                <small class="text-muted d-block">Jumlah Pesanan:</small>
                <strong><?= (int)($summary['completed_orders'] ?? 0) ?> Lunas / <?= (int)($summary['total_orders'] ?? 0) ?> Total</strong>
            </div>
        </div>
    </div>

    <table class="table table-bordered table-sm align-middle">
        <thead class="table-light">
            <tr>
                <th style="width: 40px;">No</th>
                <th>ID Pesanan</th>
                <th>Tanggal & Waktu</th>
                <th>Nomor Meja</th>
                <th>Kasir</th>
                <th class="text-end">Total Nilai</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="7" class="text-center py-3">Tidak ada data transaksi.</td></tr>
            <?php else: ?>
                <?php foreach ($orders as $i => $o): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>#<?= $o['id'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                        <td>Meja <?= \App\Helpers\View::escape($o['table_number']) ?></td>
                        <td><?= \App\Helpers\View::escape($o['staff_name']) ?></td>
                        <td class="text-end">Rp <?= number_format((float)$o['total_amount'], 0, ',', '.') ?></td>
                        <td><?= strtoupper($o['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="row mt-5 pt-4 text-center">
        <div class="col-6"></div>
        <div class="col-6">
            <p class="mb-5">Dicetak pada: <?= date('d M Y, H:i') ?> WIB<br>Penanggung Jawab,</p>
            <p class="fw-bold text-decoration-underline mb-0"><?= \App\Helpers\View::escape(\App\Helpers\Session::get('user')['name'] ?? 'Administrator') ?></p>
            <small class="text-muted">Manajemen Restoran</small>
        </div>
    </div>

</body>
</html>
