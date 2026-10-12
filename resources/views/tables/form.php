<?php
ob_start();
$isEditMode = $isEdit ?? false;
$actionUrl = $isEditMode ? '/tables/update' : '/tables/store';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card p-4 bg-white shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">
                    <i class="bi bi-grid-3x3 me-2 text-primary"></i>
                    <?= $isEditMode ? 'Ubah Data Meja' : 'Tambah Meja Baru' ?>
                </h4>
                <a href="/tables" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <form action="<?= $actionUrl ?>" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= \App\Helpers\Session::generateCsrfToken() ?>">
                <?php if ($isEditMode): ?>
                    <input type="hidden" name="id" value="<?= $table['id'] ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="table_number" class="form-label fw-semibold">Nomor / Kode Meja <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="table_number" name="table_number" 
                           value="<?= \App\Helpers\View::escape($table['table_number'] ?? '') ?>" 
                           placeholder="Contoh: T-06" required autofocus>
                    <div class="form-text">Nomor meja harus unik dan belum digunakan.</div>
                </div>

                <div class="mb-3">
                    <label for="capacity" class="form-label fw-semibold">Kapasitas Tamu (Kursi) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="capacity" name="capacity" 
                           value="<?= (int)($table['capacity'] ?? 2) ?>" 
                           min="1" max="50" required>
                    <div class="form-text">Minimal 1 orang sesuai aturan bisnis BR-01.</div>
                </div>

                <div class="mb-4">
                    <label for="status" class="form-label fw-semibold">Status Operasional</label>
                    <select class="form-select" id="status" name="status">
                        <option value="available" <?= ($table['status'] ?? 'available') === 'available' ? 'selected' : '' ?>>Tersedia (Available)</option>
                        <option value="occupied" <?= ($table['status'] ?? '') === 'occupied' ? 'selected' : '' ?>>Terisi (Occupied)</option>
                    </select>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary py-2 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Data Meja
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
