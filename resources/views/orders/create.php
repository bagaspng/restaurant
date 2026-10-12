<?php
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card p-4 bg-white shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="bi bi-cart-plus me-2 text-primary"></i>
                        Buat Pesanan Baru
                    </h4>
                    <p class="text-muted small mb-0">Pilih meja makan dan tentukan kuantitas menu yang dipesan.</p>
                </div>
                <a href="/orders" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <form action="/orders/store" method="POST" id="orderForm">
                <input type="hidden" name="_csrf_token" value="<?= \App\Helpers\Session::generateCsrfToken() ?>">
                <?php if (!empty($selectedReservationId)): ?>
                    <input type="hidden" name="reservation_id" value="<?= $selectedReservationId ?>">
                <?php endif; ?>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="table_id" class="form-label fw-semibold">Pilih Meja Pesanan <span class="text-danger">*</span></label>
                        <select class="form-select" id="table_id" name="table_id" required>
                            <option value="">-- Pilih Meja --</option>
                            <?php foreach ($tables as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($selectedTableId == $t['id']) ? 'selected' : '' ?>>
                                    Meja <?= \App\Helpers\View::escape($t['table_number']) ?> (Kapasitas: <?= $t['capacity'] ?> Orang - Status: <?= $t['status'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tautan Reservasi</label>
                        <input type="text" class="form-control bg-light" readonly 
                               value="<?= !empty($selectedReservationId) ? 'Terkait Reservasi #' . $selectedReservationId : 'Pesanan Langsung (Walk-in)' ?>">
                    </div>
                </div>

                <h5 class="fw-bold mb-3 border-bottom pb-2">
                    <i class="bi bi-card-checklist me-2 text-warning"></i>
                    Pilih Item Menu
                </h5>

                <div class="table-responsive mb-4">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Item Menu</th>
                                <th>Kategori</th>
                                <th style="width: 150px;">Harga Satuan</th>
                                <th style="width: 160px;">Jumlah Porsi (Qty)</th>
                                <th class="text-end" style="width: 160px;">Subtotal (Est.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($menus)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted">Tidak ada menu tersedia saat ini.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($menus as $m): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= \App\Helpers\View::escape($m['name']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?= ucfirst($m['category']) ?></span>
                                        </td>
                                        <td class="item-price" data-price="<?= (float)$m['price'] ?>">
                                            Rp <?= number_format((float)$m['price'], 0, ',', '.') ?>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <button type="button" class="btn btn-outline-secondary btn-minus" onclick="decreaseQty(<?= $m['id'] ?>)">-</button>
                                                <input type="number" class="form-control text-center item-qty" 
                                                       id="qty_<?= $m['id'] ?>" 
                                                       name="items[<?= $m['id'] ?>]" 
                                                       value="0" min="0" max="99" 
                                                       onchange="recalculateTotal()">
                                                <button type="button" class="btn btn-outline-secondary btn-plus" onclick="increaseQty(<?= $m['id'] ?>)">+</button>
                                            </div>
                                        </td>
                                        <td class="text-end fw-semibold item-subtotal text-dark" id="subtotal_<?= $m['id'] ?>">
                                            Rp 0
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-group-divider">
                            <tr class="fs-5 fw-bold bg-light">
                                <td colspan="4" class="text-end">Estimasi Total:</td>
                                <td class="text-end text-primary" id="grandTotalDisplay">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="alert alert-info py-2 small mb-4">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    <strong>Penting:</strong> Perhitungan final subtotal dan grand total tetap diproses dan diverifikasi secara independen di sisi server (BR-04) dengan price snapshotting (BR-03).
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary py-2 fw-semibold fs-6 shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Proses & Simpan Transaksi Pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function increaseQty(id) {
    const input = document.getElementById('qty_' + id);
    input.value = parseInt(input.value || 0) + 1;
    recalculateTotal();
}

function decreaseQty(id) {
    const input = document.getElementById('qty_' + id);
    let val = parseInt(input.value || 0);
    if (val > 0) {
        input.value = val - 1;
    }
    recalculateTotal();
}

function recalculateTotal() {
    let grandTotal = 0;
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const priceEl = row.querySelector('.item-price');
        const qtyEl = row.querySelector('.item-qty');
        const subtotalEl = row.querySelector('.item-subtotal');
        
        if (priceEl && qtyEl && subtotalEl) {
            const price = parseFloat(priceEl.dataset.price) || 0;
            const qty = parseInt(qtyEl.value) || 0;
            const subtotal = price * qty;
            
            subtotalEl.innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
            grandTotal += subtotal;
        }
    });

    document.getElementById('grandTotalDisplay').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
}
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
?>
