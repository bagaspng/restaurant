<?php
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-calendar-check me-2 text-primary"></i>Daftar Reservasi Meja</h2>
        <p class="text-muted small mb-0">Pantau jadwal slot 120 menit, informasi pemesan, dan kelola status kedatangan.</p>
    </div>
    <a href="/reservations/create" class="btn btn-primary shadow-sm">
        <i class="bi bi-calendar-plus me-1"></i> Buat Reservasi Baru
    </a>
</div>

<div class="card p-3 bg-white shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col">Jadwal Slot Waktu</th>
                    <th scope="col">Meja & Kapasitas</th>
                    <th scope="col">Identitas Pemesan</th>
                    <th scope="col">Permintaan Khusus</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-end" style="width: 250px;">Aksi & Kelola</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reservations)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat reservasi tercatat.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reservations as $res): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">
                                    <i class="bi bi-calendar3 me-1 text-primary"></i>
                                    <?= date('d M Y', strtotime($res['start_time'])) ?>
                                </div>
                                <div class="small text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    <?= date('H:i', strtotime($res['start_time'])) ?> – <?= date('H:i', strtotime($res['end_time'])) ?> WIB
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-dark-subtle text-dark border border-dark-subtle px-2 py-1 fs-6">
                                    Meja <?= \App\Helpers\View::escape($res['table_number']) ?>
                                </span>
                                <div class="small text-muted mt-1">
                                    <i class="bi bi-people me-1"></i>Kapasitas: <?= (int)$res['table_capacity'] ?> Tamu
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= \App\Helpers\View::escape($res['customer_name']) ?></div>
                                <small class="text-muted">
                                    <i class="bi bi-telephone me-1"></i><?= \App\Helpers\View::escape($res['customer_phone']) ?>
                                </small>
                            </td>
                            <td>
                                <span class="small text-secondary">
                                    <?= \App\Helpers\View::escape(\App\Services\ReservationService::formatNotesSummary($res['notes'] ?? '')) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusBadge = match($res['status']) {
                                    'confirmed' => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">Terkonfirmasi</span>',
                                    'completed' => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Selesai</span>',
                                    'cancelled' => '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">Dibatalkan</span>',
                                    default => '<span class="badge bg-light text-dark">' . $res['status'] . '</span>'
                                };
                                echo $statusBadge;
                                ?>
                            </td>
                            <td class="text-end">
                                <?php if ($res['status'] === 'confirmed'): ?>
                                    <!-- Aksi Edit -->
                                    <a href="/reservations/edit?id=<?= $res['id'] ?>" class="btn btn-sm btn-outline-secondary me-1" title="Edit Reservasi">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>

                                    <!-- Aksi Selesai -->
                                    <form action="/reservations/status" method="POST" class="d-inline">
                                        <input type="hidden" name="_csrf_token" value="<?= \App\Helpers\Session::generateCsrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= $res['id'] ?>">
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn btn-sm btn-outline-success me-1" title="Tandai Selesai">
                                            <i class="bi bi-check-lg"></i> Selesai
                                        </button>
                                    </form>

                                    <!-- Aksi Batalkan -->
                                    <form action="/reservations/status" method="POST" class="d-inline" onsubmit="return confirm('Batalkan reservasi ini? Meja akan otomatis tersedia kembali.');">
                                        <input type="hidden" name="_csrf_token" value="<?= \App\Helpers\Session::generateCsrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= $res['id'] ?>">
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="btn btn-sm btn-outline-danger me-1" title="Batalkan">
                                            <i class="bi bi-x-lg"></i> Batal
                                        </button>
                                    </form>

                                    <!-- Aksi Buat Pesanan Langsung -->
                                    <a href="/orders/create?table_id=<?= $res['table_id'] ?>&reservation_id=<?= $res['id'] ?>" 
                                       class="btn btn-sm btn-warning text-dark" title="Buat Pesanan Langsung">
                                        <i class="bi bi-cart-plus"></i> Pesan
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small fst-italic">Status Final</span>
                                <?php endif; ?>
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
