<?php
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Header Halaman -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">
                    <i class="bi bi-calendar-plus me-2 text-primary"></i>
                    Buat Reservasi Meja Baru
                </h3>
                <p class="text-muted small mb-0">
                    Alur reservasi terpadu: Input pemesan &rarr; Pilih tanggal & slot &rarr; Pilih meja berkapasitas &rarr; Catatan khusus &rarr; Tinjau & simpan.
                </p>
            </div>
            <a href="/reservations" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>

        <!-- Wizard Navigasi Langkah -->
        <div class="card p-3 bg-white shadow-sm mb-4">
            <div class="d-flex justify-content-between text-center position-relative">
                <div class="step-nav active flex-fill" id="stepPill1">
                    <div class="step-circle mx-auto mb-1">1</div>
                    <small class="fw-semibold">Identitas</small>
                </div>
                <div class="step-nav flex-fill" id="stepPill2">
                    <div class="step-circle mx-auto mb-1">2</div>
                    <small class="fw-semibold">Tanggal & Slot</small>
                </div>
                <div class="step-nav flex-fill" id="stepPill3">
                    <div class="step-circle mx-auto mb-1">3</div>
                    <small class="fw-semibold">Pilih Meja</small>
                </div>
                <div class="step-nav flex-fill" id="stepPill4">
                    <div class="step-circle mx-auto mb-1">4</div>
                    <small class="fw-semibold">Catatan</small>
                </div>
                <div class="step-nav flex-fill" id="stepPill5">
                    <div class="step-circle mx-auto mb-1">5</div>
                    <small class="fw-semibold">Tinjau & Simpan</small>
                </div>
            </div>
        </div>

        <!-- Form Utama Reservasi -->
        <form action="/reservations/store" method="POST" id="reservationForm">
            <input type="hidden" name="_csrf_token" value="<?= \App\Helpers\Session::generateCsrfToken() ?>">

            <!-- ===================================================================
                 LANGKAH 1: INPUT IDENTITAS PEMESAN
                 =================================================================== -->
            <div class="step-section" id="sectionStep1">
                <div class="card p-4 bg-white shadow-sm mb-4">
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-person-badge me-2 text-primary"></i>
                        Langkah 1 — Identitas Pemesan
                    </h5>
                    <p class="text-muted small">
                        Masukkan nama lengkap dan nomor telepon kontak tamu pemesan. Data ini disimpan langsung pada reservasi tanpa perlu membuat master data pelanggan.
                    </p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="customer_name" class="form-label fw-semibold">
                                Nama Lengkap Pemesan <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-lg" id="customer_name" name="customer_name" 
                                   placeholder="Contoh: Bpk. Sanusi / Citra Lestari" 
                                   value="<?= \App\Helpers\View::escape($old['customer_name'] ?? '') ?>" required>
                            <div class="form-text">Nama wajib diisi minimal 2 karakter.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="customer_phone" class="form-label fw-semibold">
                                Nomor Telepon Kontak <span class="text-danger">*</span>
                            </label>
                            <input type="tel" class="form-control form-control-lg" id="customer_phone" name="customer_phone" 
                                   placeholder="Contoh: 081234567890 / +6281234567890" 
                                   value="<?= \App\Helpers\View::escape($old['customer_phone'] ?? '') ?>" required>
                            <div class="form-text">Format numerik 10–16 digit untuk verifikasi konfirmasi.</div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-primary px-4 fw-semibold" onclick="goToStep(2)">
                            Lanjut: Pilih Tanggal & Slot <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ===================================================================
                 LANGKAH 2 & 3: PILIH TANGGAL & SLOT WAKTU (120 MENIT)
                 =================================================================== -->
            <div class="step-section d-none" id="sectionStep2">
                <div class="card p-4 bg-white shadow-sm mb-4">
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-calendar-event me-2 text-primary"></i>
                        Langkah 2 & 3 — Tanggal & Slot Waktu Reservasi
                    </h5>
                    <p class="text-muted small">
                        Pilih tanggal kunjungan dan slot waktu operasional (durasi tetap 120 menit / 2 jam).
                    </p>

                    <!-- Tanggal -->
                    <div class="mb-4">
                        <label for="reservation_date" class="form-label fw-semibold">
                            Pilih Tanggal Reservasi <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control form-control-lg" id="reservation_date" name="reservation_date" 
                               min="<?= date('Y-m-d') ?>" 
                               value="<?= \App\Helpers\View::escape($old['reservation_date'] ?? date('Y-m-d')) ?>" required>
                        <div class="form-text">Tanggal yang sudah lewat dinonaktifkan secara otomatis.</div>
                    </div>

                    <!-- Slot Waktu -->
                    <div>
                        <label class="form-label fw-semibold d-block">
                            Pilih Slot Waktu Operasional (Durasi 120 Menit) <span class="text-danger">*</span>
                        </label>
                        <div id="slotLoadingSpinner" class="text-center py-3 d-none">
                            <div class="spinner-border text-primary spinner-border-sm me-2"></div>
                            <span class="text-muted small">Memeriksa ketersediaan meja...</span>
                        </div>
                        <div class="row g-3" id="slotContainer">
                            <!-- Slot cards akan dirender secara dinamis oleh JavaScript -->
                        </div>
                        <input type="hidden" name="slot_start" id="selectedSlotStart" value="">
                        <input type="hidden" name="slot_end" id="selectedSlotEnd" value="">
                        <input type="hidden" name="slot_label" id="selectedSlotLabel" value="">
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary px-4" onclick="goToStep(1)">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Identitas
                        </button>
                        <button type="button" class="btn btn-primary px-4 fw-semibold" id="btnNextToStep3" onclick="goToStep(3)" disabled>
                            Lanjut: Pilih Meja <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ===================================================================
                 LANGKAH 4: PILIH MEJA BERDASARKAN KAPASITAS
                 =================================================================== -->
            <div class="step-section d-none" id="sectionStep3">
                <div class="card p-4 bg-white shadow-sm mb-4">
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-grid-3x3 me-2 text-primary"></i>
                        Langkah 4 — Pilih Meja Berdasarkan Kapasitas
                    </h5>
                    <p class="text-muted small mb-3">
                        Pilih meja makan fisik berdasarkan daya tampung kursi yang sesuai. Meja yang terisi atau rusak dinonaktifkan.
                    </p>

                    <!-- Alert Info Slot Terpilih -->
                    <div class="alert alert-info py-2 px-3 small d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <i class="bi bi-info-circle me-1"></i>
                            Jadwal Terpilih: <strong id="infoSlotBadge">-</strong>
                        </div>
                        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" onclick="goToStep(2)">
                            Ubah Jadwal &rarr;
                        </button>
                    </div>

                    <!-- Container Kartu Meja -->
                    <div class="row g-3" id="tableCardsContainer">
                        <!-- Kartu Meja akan dirender secara dinamis oleh JavaScript -->
                    </div>
                    <input type="hidden" name="table_id" id="selectedTableId" value="">
                    <input type="hidden" id="selectedTableNumber" value="">
                    <input type="hidden" id="selectedTableCapacity" value="">

                    <div id="noTableAlert" class="alert alert-warning mt-3 d-none">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Tidak ada meja yang tersedia pada slot waktu ini. Silakan pilih slot waktu lain.
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary px-4" onclick="goToStep(2)">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Slot
                        </button>
                        <button type="button" class="btn btn-primary px-4 fw-semibold" id="btnNextToStep4" onclick="goToStep(4)" disabled>
                            Lanjut: Catatan Khusus <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ===================================================================
                 LANGKAH 5: PILIH CATATAN KHUSUS (CHECKBOX TERSTRUKTUR)
                 =================================================================== -->
            <div class="step-section d-none" id="sectionStep4">
                <div class="card p-4 bg-white shadow-sm mb-4">
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-card-checklist me-2 text-primary"></i>
                        Langkah 5 — Permintaan & Catatan Khusus
                    </h5>
                    <p class="text-muted small">
                        Pilih fasilitas atau kebutuhan khusus tamu restoran (bisa memilih lebih dari satu atau dikosongkan).
                    </p>

                    <div class="row g-3 mb-3">
                        <?php foreach ($specialRequests as $key => $label): ?>
                            <div class="col-md-6">
                                <div class="form-check p-3 border rounded bg-light hover-shadow">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" 
                                           name="special_requests[]" value="<?= $key ?>" 
                                           id="req_<?= $key ?>" onchange="handleSpecialRequestChange()">
                                    <label class="form-check-label fw-semibold text-dark cursor-pointer" for="req_<?= $key ?>">
                                        <?= \App\Helpers\View::escape($label) ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Input Keterangan Lainnya (Kondisional) -->
                    <div class="mt-3 d-none" id="otherNotesWrapper">
                        <label for="other_notes" class="form-label fw-semibold">
                            Keterangan Tambahan (Lainnya) <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="other_notes" name="other_notes" 
                               placeholder="Contoh: Tamu ulang tahun, butuh kursi roda anak, dll.">
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary px-4" onclick="goToStep(3)">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Pilih Meja
                        </button>
                        <button type="button" class="btn btn-primary px-4 fw-semibold" onclick="goToStep(5)">
                            Lanjut: Tinjau Reservasi <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ===================================================================
                 LANGKAH 6: TINJAU DAN SIMPAN RESERVASI
                 =================================================================== -->
            <div class="step-section d-none" id="sectionStep5">
                <div class="card p-4 bg-white shadow-sm mb-4">
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-shield-check me-2 text-success"></i>
                        Langkah 6 — Tinjau & Simpan Reservasi
                    </h5>
                    <p class="text-muted small">
                        Pastikan seluruh data di bawah ini sudah benar sebelum disimpan ke sistem.
                    </p>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <tbody class="bg-light">
                                <tr>
                                    <th class="w-25 text-muted">Nama Pemesan</th>
                                    <td class="fw-bold fs-6 text-dark" id="revCustomerName">-</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Nomor Telepon</th>
                                    <td class="fw-bold text-dark" id="revCustomerPhone">-</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Tanggal Reservasi</th>
                                    <td class="fw-bold text-dark" id="revDate">-</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Slot Waktu (120 Menit)</th>
                                    <td class="fw-bold text-primary" id="revSlot">-</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Alokasi Meja</th>
                                    <td class="fw-bold text-dark" id="revTable">-</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Kapasitas Maksimal Meja</th>
                                    <td class="fw-semibold text-secondary" id="revCapacity">-</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Catatan Khusus</th>
                                    <td class="text-dark" id="revNotes">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary px-4" onclick="goToStep(4)">
                            <i class="bi bi-pencil me-1"></i> Kembali Mengubah
                        </button>
                        <button type="submit" class="btn btn-success px-5 py-2 fw-bold shadow-sm" id="btnSubmit">
                            <i class="bi bi-check2-circle me-1"></i> Simpan Reservasi Sekarang
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

<style>
.step-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    transition: all 0.3s ease;
}
.step-nav.active .step-circle {
    background-color: #0d6efd;
    color: #fff;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
}
.step-nav.completed .step-circle {
    background-color: #198754;
    color: #fff;
}
.cursor-pointer {
    cursor: pointer;
}
.slot-card {
    cursor: pointer;
    border: 2px solid #dee2e6;
    border-radius: 10px;
    transition: all 0.2s ease;
}
.slot-card:hover:not(.disabled) {
    border-color: #0d6efd;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.06);
}
.slot-card.selected {
    border-color: #0d6efd;
    background-color: #e7f1ff !important;
}
.slot-card.disabled {
    opacity: 0.55;
    cursor: not-allowed;
    background-color: #f8f9fa !important;
}
.table-card {
    cursor: pointer;
    border: 2px solid #dee2e6;
    border-radius: 12px;
    transition: all 0.2s ease;
}
.table-card:hover:not(.disabled) {
    border-color: #198754;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.table-card.selected {
    border-color: #198754;
    background-color: #d1e7dd !important;
}
.table-card.disabled {
    opacity: 0.55;
    cursor: not-allowed;
    background-color: #f8f9fa !important;
}
</style>

<script>
let currentAvailabilityData = null;
let currentSelectedSlot = null;
let currentSelectedTable = null;

// Inisialisasi saat DOM siap
document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.getElementById('reservation_date');
    if (dateInput) {
        dateInput.addEventListener('change', function () {
            fetchAvailability(this.value);
        });
        // Muat slot awal
        fetchAvailability(dateInput.value);
    }
});

function fetchAvailability(date) {
    const spinner = document.getElementById('slotLoadingSpinner');
    const container = document.getElementById('slotContainer');
    spinner.classList.remove('d-none');

    fetch('/reservations/availability?date=' + encodeURIComponent(date))
        .then(response => response.json())
        .then(result => {
            spinner.classList.add('d-none');
            if (result.success && result.data) {
                currentAvailabilityData = result.data;
                renderSlots(result.data.slots);
            }
        })
        .catch(err => {
            spinner.classList.add('d-none');
            console.error('Gagal mengambil ketersediaan slot:', err);
        });
}

function renderSlots(slots) {
    const container = document.getElementById('slotContainer');
    container.innerHTML = '';

    // Reset pilihan slot sebelumnya jika tidak lagi valid
    currentSelectedSlot = null;
    document.getElementById('selectedSlotStart').value = '';
    document.getElementById('selectedSlotEnd').value = '';
    document.getElementById('selectedSlotLabel').value = '';
    document.getElementById('btnNextToStep3').disabled = true;

    slots.forEach(slot => {
        const col = document.createElement('div');
        col.className = 'col-md-4 col-sm-6';

        const isAvailable = (slot.status === 'available');
        const badgeClass = isAvailable ? 'bg-success' : (slot.status === 'full' ? 'bg-danger' : 'bg-secondary');

        col.innerHTML = `
            <div class="card p-3 slot-card ${!isAvailable ? 'disabled' : ''}" 
                 id="slot_${slot.slot_id}" 
                 onclick="${isAvailable ? `selectSlot('${slot.slot_id}', '${slot.start}', '${slot.end}', '${slot.label}')` : ''}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-dark fs-6">${slot.label}</span>
                    <span class="badge ${badgeClass}">${slot.status_badge}</span>
                </div>
                <div class="small text-muted">
                    <i class="bi bi-clock me-1"></i>Durasi: 120 Menit (2 Jam)
                </div>
            </div>
        `;
        container.appendChild(col);
    });
}

function selectSlot(slotId, start, end, label) {
    document.querySelectorAll('.slot-card').forEach(c => c.classList.remove('selected'));
    const el = document.getElementById('slot_' + slotId);
    if (el) el.classList.add('selected');

    currentSelectedSlot = { id: slotId, start: start, end: end, label: label };
    document.getElementById('selectedSlotStart').value = start;
    document.getElementById('selectedSlotEnd').value = end;
    document.getElementById('selectedSlotLabel').value = label;
    document.getElementById('btnNextToStep3').disabled = false;

    // Render tabel untuk slot terpilih
    renderTableCards(slotId);
}

function renderTableCards(slotId) {
    const container = document.getElementById('tableCardsContainer');
    const noTableAlert = document.getElementById('noTableAlert');
    container.innerHTML = '';

    document.getElementById('infoSlotBadge').textContent = 
        document.getElementById('reservation_date').value + ' | ' + currentSelectedSlot.label;

    if (!currentAvailabilityData) return;

    const slotData = currentAvailabilityData.slots.find(s => s.slot_id === slotId);
    if (!slotData) return;

    let hasAnyAvailable = false;
    currentSelectedTable = null;
    document.getElementById('selectedTableId').value = '';
    document.getElementById('selectedTableNumber').value = '';
    document.getElementById('selectedTableCapacity').value = '';
    document.getElementById('btnNextToStep4').disabled = true;

    slotData.tables.forEach(table => {
        if (table.is_selectable) hasAnyAvailable = true;

        const col = document.createElement('div');
        col.className = 'col-md-4 col-sm-6';

        let badgeClass = 'bg-success';
        if (table.status === 'occupied') badgeClass = 'bg-danger';
        if (table.status === 'unavailable') badgeClass = 'bg-secondary';

        col.innerHTML = `
            <div class="card p-3 table-card ${!table.is_selectable ? 'disabled' : ''}" 
                 id="tableCard_${table.id}"
                 onclick="${table.is_selectable ? `selectTable(${table.id}, '${table.table_number}', ${table.capacity})` : ''}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="fw-bold mb-0 text-dark">Meja ${table.table_number}</h5>
                    <span class="badge ${badgeClass}">${table.status_label}</span>
                </div>
                <div class="fw-semibold text-secondary mb-1">
                    <i class="bi bi-people-fill me-1 text-primary"></i>
                    Kapasitas: ${table.capacity} Tamu
                </div>
                <small class="text-muted">
                    ${table.is_selectable ? 'Siap digunakan pada slot ini' : 'Tidak tersedia untuk waktu ini'}
                </small>
            </div>
        `;
        container.appendChild(col);
    });

    if (!hasAnyAvailable) {
        noTableAlert.classList.remove('d-none');
    } else {
        noTableAlert.classList.add('d-none');
    }
}

function selectTable(tableId, tableNumber, capacity) {
    document.querySelectorAll('.table-card').forEach(c => c.classList.remove('selected'));
    const el = document.getElementById('tableCard_' + tableId);
    if (el) el.classList.add('selected');

    currentSelectedTable = { id: tableId, number: tableNumber, capacity: capacity };
    document.getElementById('selectedTableId').value = tableId;
    document.getElementById('selectedTableNumber').value = tableNumber;
    document.getElementById('selectedTableCapacity').value = capacity;
    document.getElementById('btnNextToStep4').disabled = false;
}

function handleSpecialRequestChange() {
    const otherCheckbox = document.getElementById('req_other');
    const wrapper = document.getElementById('otherNotesWrapper');
    if (otherCheckbox && wrapper) {
        if (otherCheckbox.checked) {
            wrapper.classList.remove('d-none');
            document.getElementById('other_notes').focus();
        } else {
            wrapper.classList.add('d-none');
            document.getElementById('other_notes').value = '';
        }
    }
}

function goToStep(step) {
    // Validasi saat maju
    if (step === 2) {
        const name = document.getElementById('customer_name').value.trim();
        const phone = document.getElementById('customer_phone').value.trim();
        if (name.length < 2) {
            alert('Mohon isi nama lengkap pemesan (minimal 2 karakter).');
            document.getElementById('customer_name').focus();
            return;
        }
        if (phone.length < 10) {
            alert('Mohon isi nomor telepon kontak pemesan yang valid (minimal 10 digit).');
            document.getElementById('customer_phone').focus();
            return;
        }
    } else if (step === 3) {
        if (!currentSelectedSlot) {
            alert('Mohon pilih salah satu slot waktu terlebih dahulu.');
            return;
        }
    } else if (step === 4) {
        if (!currentSelectedTable) {
            alert('Mohon pilih salah satu meja yang tersedia terlebih dahulu.');
            return;
        }
    } else if (step === 5) {
        // Tinjau
        populateReview();
    }

    // Sembunyikan seluruh langkah
    for (let i = 1; i <= 5; i++) {
        const sec = document.getElementById('sectionStep' + i);
        const pill = document.getElementById('stepPill' + i);
        if (sec) sec.classList.add('d-none');
        if (pill) {
            pill.classList.remove('active');
            if (i < step) pill.classList.add('completed');
            else pill.classList.remove('completed');
        }
    }

    // Tampilkan langkah aktif
    const activeSec = document.getElementById('sectionStep' + step);
    const activePill = document.getElementById('stepPill' + step);
    if (activeSec) activeSec.classList.remove('d-none');
    if (activePill) activePill.classList.add('active');

    window.scrollTo({ top: 100, behavior: 'smooth' });
}

function populateReview() {
    document.getElementById('revCustomerName').textContent = document.getElementById('customer_name').value.trim();
    document.getElementById('revCustomerPhone').textContent = document.getElementById('customer_phone').value.trim();
    document.getElementById('revDate').textContent = document.getElementById('reservation_date').value;
    document.getElementById('revSlot').textContent = currentSelectedSlot ? currentSelectedSlot.label : '-';
    document.getElementById('revTable').textContent = currentSelectedTable ? 'Meja ' + currentSelectedTable.number : '-';
    document.getElementById('revCapacity').textContent = currentSelectedTable ? currentSelectedTable.capacity + ' Tamu' : '-';

    // Kumpulkan catatan
    const selectedLabels = [];
    document.querySelectorAll('input[name="special_requests[]"]:checked').forEach(cb => {
        if (cb.value === 'other') {
            const extra = document.getElementById('other_notes').value.trim();
            selectedLabels.push(extra ? 'Lainnya: ' + extra : 'Permintaan Lainnya');
        } else {
            const labelEl = document.querySelector('label[for="' + cb.id + '"]');
            if (labelEl) selectedLabels.push(labelEl.textContent.trim());
        }
    });

    document.getElementById('revNotes').textContent = selectedLabels.length > 0 ? selectedLabels.join(', ') : 'Tidak ada catatan khusus';
}
</script>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
?>
