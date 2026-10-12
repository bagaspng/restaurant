<?php
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-cart3 me-2 text-primary"></i>Daftar Pesanan Restoran</h2>
        <p class="text-muted small mb-0">Kelola pesanan meja aktif, cetak tagihan/struk, dan catat pelunasan kasir.</p>
    </div>
    <a href="/orders/create" class="btn btn-primary shadow-sm">
        <i class="bi bi-cart-plus me-1"></i> Buat Pesanan Baru
    </a>
</div>

<div class="card p-3 bg-white shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col" style="width: 100px;">ID Pesanan</th>
                    <th scope="col">Waktu Pesan</th>
                    <th scope="col">Meja</th>
                    <th scope="col">Kasir/Staf</th>
                    <th scope="col">Total Tagihan</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-end" style="width: 160px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat pesanan tercatat.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $ord): ?>
                        <tr>
                            <td><strong class="text-primary">#<?= $ord['id'] ?></strong></td>
                            <td class="small text-muted"><?= date('d M Y, H:i', strtotime($ord['created_at'])) ?></td>
                            <td>
                                <span class="badge bg-dark-subtle text-dark border border-dark-subtle px-2 py-1">
                                    Meja <?= \App\Helpers\View::escape($ord['table_number']) ?>
                                </span>
                            </td>
                            <td><?= \App\Helpers\View::escape($ord['staff_name']) ?></td>
                            <td>
                                <span class="fw-bold text-dark fs-6">
                                    Rp <?= number_format((float)$ord['total_amount'], 0, ',', '.') ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $badge = match($ord['status']) {
                                    'pending' => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">Belum Lunas</span>',
                                    'completed' => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Selesai / Lunas</span>',
                                    'cancelled' => '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">Dibatalkan</span>',
                                    default => '<span class="badge bg-light text-dark">' . $ord['status'] . '</span>'
                                };
                                echo $badge;
                                ?>
                            </td>
                            <td class="text-end">
                                <a href="/orders/show?id=<?= $ord['id'] ?>" class="btn btn-sm btn-outline-primary" title="Lihat Rincian / Nota">
                                    <i class="bi bi-receipt"></i> Rincian
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
?>
