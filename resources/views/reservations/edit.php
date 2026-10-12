<?php
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4 bg-white shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>
                        Edit Data Reservasi #<?= $reservation['id'] ?>
                    </h4>
                    <p class="text-muted small mb-0">Perbarui identitas pemesan, meja, atau catatan fasilitas khusus.</p>
                </div>
                <a href="/reservations" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Batal & Kembali
                </a>
            </div>

            <form action="/reservations/update" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= \App\Helpers\Session::generateCsrfToken() ?>">
                <input type="hidden" name="id" value="<?= $reservation['id'] ?>">

                <!-- Identitas Pemesan Langsung -->
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-person-badge me-2 text-primary"></i>Identitas Pemesan</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="customer_name" class="form-label fw-semibold">Nama Pemesan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" 
                               value="<?= \App\Helpers\View::escape($reservation['customer_name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="customer_phone" class="form-label fw-semibold">Nomor Telepon <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="customer_phone" name="customer_phone" 
                               value="<?= \App\Helpers\View::escape($reservation['customer_phone']) ?>" required>
                    </div>
                </div>

                <!-- Alokasi Meja & Waktu -->
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>Jadwal & Meja</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="table_id" class="form-label fw-semibold">Pilih Meja Restoran <span class="text-danger">*</span></label>
                        <select class="form-select" id="table_id" name="table_id" required>
                            <?php foreach ($tables as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($reservation['table_id'] == $t['id']) ? 'selected' : '' ?>>
                                    Meja <?= \App\Helpers\View::escape($t['table_number']) ?> (Kapasitas: <?= $t['capacity'] ?> Tamu)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="start_time" class="form-label fw-semibold">Waktu Mulai Reservasi <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="start_time" name="start_time" 
                               value="<?= date('Y-m-d\TH:i', strtotime($reservation['start_time'])) ?>" required>
                        <div class="form-text">Durasi otomatis dihitung 120 menit (2 Jam).</div>
                    </div>
                </div>

                <!-- Catatan Fasilitas Khusus Terstruktur -->
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-card-checklist me-2 text-primary"></i>Catatan & Fasilitas Khusus</h5>
                <div class="row g-3 mb-3">
                    <?php foreach ($specialRequests as $key => $label): ?>
                        <div class="col-md-6">
                            <div class="form-check p-3 border rounded bg-light">
                                <input class="form-check-input ms-0 me-2" type="checkbox" 
                                       name="special_requests[]" value="<?= $key ?>" 
                                       id="req_edit_<?= $key ?>" 
                                       <?= in_array($key, $selectedRequests, true) ? 'checked' : '' ?>
                                       onchange="toggleOtherEdit()">
                                <label class="form-check-label fw-semibold text-dark" for="req_edit_<?= $key ?>">
                                    <?= \App\Helpers\View::escape($label) ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mb-4 <?= in_array('other', $selectedRequests, true) ? '' : 'd-none' ?>" id="otherEditWrapper">
                    <label for="other_notes" class="form-label fw-semibold">Keterangan Tambahan (Lainnya)</label>
                    <input type="text" class="form-control" id="other_notes" name="other_notes" 
                           value="<?= \App\Helpers\View::escape($otherNote) ?>"
                           placeholder="Contoh: Meja sudut, perayaan ulang tahun, dll.">
                </div>

                <div class="d-flex justify-content-between pt-3 border-top">
                    <a href="/reservations" class="btn btn-outline-secondary px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleOtherEdit() {
    const cb = document.getElementById('req_edit_other');
    const wrap = document.getElementById('otherEditWrapper');
    if (cb && wrap) {
        if (cb.checked) {
            wrap.classList.remove('d-none');
        } else {
            wrap.classList.add('d-none');
        }
    }
}
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
?>
