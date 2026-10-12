<?php
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Kartu Tagihan / Struk -->
        <div class="card p-4 bg-white shadow-sm mb-4" id="printableArea">
            <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
                <div>
                    <h4 class="fw-bold text-dark mb-1">
                        <i class="bi bi-receipt me-2 text-primary"></i>
                        Nota Pesanan #<?= $order['id'] ?>
                    </h4>
                    <div class="text-muted small">
                        Dibuat pada: <?= date('d M Y, H:i', strtotime($order['created_at'])) ?> WIB
                    </div>
                </div>
                <div class="text-end">
                    <?php
                    $badge = match($order['status']) {
                        'pending' => '<span class="badge bg-warning text-dark px-3 py-2 fs-6">Belum Lunas (Pending)</span>',
                        'completed' => '<span class="badge bg-success px-3 py-2 fs-6">Lunas (Completed)</span>',
                        'cancelled' => '<span class="badge bg-secondary px-3 py-2 fs-6">Dibatalkan (Cancelled)</span>',
                        default => '<span class="badge bg-light text-dark px-3 py-2 fs-6">' . $order['status'] . '</span>'
                    };
                    echo $badge;
                    ?>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-6">
                    <span class="text-muted small d-block">Meja:</span>
                    <strong class="fs-5 text-dark">Meja <?= \App\Helpers\View::escape($order['table_number']) ?></strong>
                </div>
                <div class="col-6 text-end">
                    <span class="text-muted small d-block">Kasir / Pelayan:</span>
                    <strong class="text-dark"><?= \App\Helpers\View::escape($order['staff_name']) ?></strong>
                </div>
            </div>

            <h6 class="fw-bold text-dark mb-2">Rincian Item Menu (Price Snapshot):</h6>
            <div class="table-responsive mb-3">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Item (Snapshot)</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-center" style="width: 80px;">Qty</th>
                            <th class="text-end" style="width: 140px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $idx => $it): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td><strong><?= \App\Helpers\View::escape($it['item_name']) ?></strong></td>
                                <td class="text-end">Rp <?= number_format((float)$it['price_at_order'], 0, ',', '.') ?></td>
                                <td class="text-center"><?= (int)$it['quantity'] ?></td>
                                <td class="text-end fw-semibold">Rp <?= number_format((float)$it['subtotal'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light fs-5">
                            <td colspan="4" class="text-end fw-bold">Total Pembayaran:</td>
                            <td class="text-end fw-bold text-primary">
                                Rp <?= number_format((float)$order['total_amount'], 0, ',', '.') ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="text-muted small text-center pt-2 border-top">
                <em>Terima kasih telah bersantap di <?= \App\Helpers\View::escape(\Config\Config::get('app_name')) ?>.</em>
            </div>
        </div>

        <!-- Tombol Aksi Kasir -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <a href="/orders" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pesanan
            </a>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-dark" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Cetak Struk
                </button>

                <?php if ($order['status'] === 'pending'): ?>
                    <form action="/orders/status" method="POST" class="d-inline">
                        <input type="hidden" name="_csrf_token" value="<?= \App\Helpers\Session::generateCsrfToken() ?>">
                        <input type="hidden" name="id" value="<?= $order['id'] ?>">
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="btn btn-success fw-semibold">
                            <i class="bi bi-cash-coin me-1"></i> Terima Pembayaran (Lunas)
                        </button>
                    </form>

                    <form action="/orders/status" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');">
                        <input type="hidden" name="_csrf_token" value="<?= \App\Helpers\Session::generateCsrfToken() ?>">
                        <input type="hidden" name="id" value="<?= $order['id'] ?>">
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-x-circle me-1"></i> Batalkan Pesanan
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
?>
