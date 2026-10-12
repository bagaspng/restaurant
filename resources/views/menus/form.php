<?php
ob_start();
$isEditMode = $isEdit ?? false;
$actionUrl = $isEditMode ? '/menus/update' : '/menus/store';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card p-4 bg-white shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">
                    <i class="bi bi-egg-fried me-2 text-warning"></i>
                    <?= $isEditMode ? 'Ubah Data Menu' : 'Tambah Menu Baru' ?>
                </h4>
                <a href="/menus" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <form action="<?= $actionUrl ?>" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= \App\Helpers\Session::generateCsrfToken() ?>">
                <?php if ($isEditMode): ?>
                    <input type="hidden" name="id" value="<?= $menu['id'] ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Nama Item Menu <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?= \App\Helpers\View::escape($menu['name'] ?? '') ?>" 
                           placeholder="Contoh: Nasi Goreng Spesial" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label fw-semibold">Kategori Menu</label>
                    <select class="form-select" id="category" name="category">
                        <option value="food" <?= ($menu['category'] ?? 'food') === 'food' ? 'selected' : '' ?>>Makanan (Food)</option>
                        <option value="beverage" <?= ($menu['category'] ?? '') === 'beverage' ? 'selected' : '' ?>>Minuman (Beverage)</option>
                        <option value="dessert" <?= ($menu['category'] ?? '') === 'dessert' ? 'selected' : '' ?>>Hidangan Penutup (Dessert)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label fw-semibold">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" id="price" name="price" 
                               value="<?= isset($menu['price']) ? (int)$menu['price'] : '' ?>" 
                               min="0" step="500" placeholder="25000" required>
                    </div>
                    <div class="form-text">Harga disimpan menggunakan tipe presisi moneter DECIMAL.</div>
                </div>

                <div class="mb-4 form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_available" name="is_available" 
                           <?= (!isset($menu) || !empty($menu['is_available'])) ? 'checked' : '' ?>>
                    <label class="form-check-label fw-semibold" for="is_available">Status Bahan Tersedia di Dapur</label>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary py-2 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Data Menu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
?>
