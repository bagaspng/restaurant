<?php
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-egg-fried me-2 text-warning"></i>Katalog Menu Restoran</h2>
        <p class="text-muted small mb-0">Kelola daftar makanan, minuman, hidangan penutup, dan status ketersediaan.</p>
    </div>
    <a href="/menus/create" class="btn btn-primary shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> Tambah Menu
    </a>
</div>

<div class="card p-3 bg-white shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col" style="width: 60px;">No</th>
                    <th scope="col">Nama Menu</th>
                    <th scope="col">Kategori</th>
                    <th scope="col">Harga Satuan</th>
                    <th scope="col">Ketersediaan</th>
                    <th scope="col" class="text-end" style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($menus)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada item menu yang terdaftar.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($menus as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><strong class="text-dark"><?= \App\Helpers\View::escape($item['name']) ?></strong></td>
                            <td>
                                <?php
                                $catBadge = match($item['category']) {
                                    'food' => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle">Makanan</span>',
                                    'beverage' => '<span class="badge bg-info-subtle text-info border border-info-subtle">Minuman</span>',
                                    'dessert' => '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Penutup</span>',
                                    default => '<span class="badge bg-light text-dark">Lainnya</span>'
                                };
                                echo $catBadge;
                                ?>
                            </td>
                            <td class="fw-bold text-dark">
                                Rp <?= number_format((float)$item['price'], 0, ',', '.') ?>
                            </td>
                            <td>
                                <?php if ($item['is_available']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Tersedia</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Habis</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="/menus/edit?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-secondary me-1" title="Ubah">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="/menus/delete" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus menu ini?');">
                                    <input type="hidden" name="_csrf_token" value="<?= \App\Helpers\Session::generateCsrfToken() ?>">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
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
