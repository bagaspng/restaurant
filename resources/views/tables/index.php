<?php
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-grid-3x3 me-2 text-primary"></i>Manajemen Meja Restoran</h2>
        <p class="text-muted small mb-0">Kelola nomor meja fisik, kapasitas kursi, dan status operasional.</p>
    </div>
    <a href="/tables/create" class="btn btn-primary shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> Tambah Meja
    </a>
</div>

<div class="card p-3 bg-white shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col" style="width: 80px;">No</th>
                    <th scope="col">Nomor Meja</th>
                    <th scope="col">Kapasitas Kursi</th>
                    <th scope="col">Status Operasional</th>
                    <th scope="col" class="text-end" style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tables)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data meja terdaftar.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tables as $index => $tbl): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><strong class="text-dark"><?= \App\Helpers\View::escape($tbl['table_number']) ?></strong></td>
                            <td>
                                <i class="bi bi-people text-muted me-1"></i>
                                <?= (int)$tbl['capacity'] ?> Orang
                            </td>
                            <td>
                                <?php if ($tbl['status'] === 'available'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Tersedia</span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">Terisi</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="/tables/edit?id=<?= $tbl['id'] ?>" class="btn btn-sm btn-outline-secondary me-1" title="Ubah">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="/tables/delete" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus meja ini?');">
                                    <input type="hidden" name="_csrf_token" value="<?= \App\Helpers\Session::generateCsrfToken() ?>">
                                    <input type="hidden" name="id" value="<?= $tbl['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
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
