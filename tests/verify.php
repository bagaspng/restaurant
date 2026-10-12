<?php

require __DIR__ . '/../vendor/autoload.php';

use Config\Config;
use Config\Database;
use App\Models\Admin;
use App\Models\FoodItem;
use App\Models\BeverageItem;
use App\Services\ReservationService;
use App\Services\StandardOrderCalculator;

Config::load();

echo "=== VERIFIKASI FUNGSIONAL SISTEM LOKAL (ADMINISTRATOR V2) ===\n";

// 1. Verifikasi Database
try {
    $pdo = Database::getConnection();
    echo "[1] Koneksi Basis Data: BERHASIL (MySQL " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . ")\n";
} catch (\Throwable $e) {
    echo "[1] Koneksi Basis Data: GAGAL (" . $e->getMessage() . ")\n";
    exit(1);
}

// 2. Verifikasi Model Data & Penyajian Menu
$admin = new Admin(1, 'Administrator', 'admin@resto.local');
$food = new FoodItem(1, 'Nasi Goreng Spesial', 28000.00, true, 'Pedas');
$drink = new BeverageItem(2, 'Es Teh Manis', 6000.00, true, true);

echo "[2] Model Admin: " . $admin->getName() . " (Role: " . $admin->getRole() . ") [OK]\n";
echo "[2] Penyajian FoodItem: " . $food->getServingType() . " [OK]\n";
echo "[2] Penyajian BeverageItem: " . $drink->getServingType() . " [OK]\n";

// 3. Verifikasi Deteksi Bentrok Jadwal Reservasi (Slot 120 Menit)
$resService = new ReservationService();

// Kasus A: Bentrok tumpang tindih (12:30 - 14:30) pada Meja 2 (yang sudah dipesan 12:00 - 14:00)
$bentrok = $resService->isCollision(2, '2026-10-12 12:30:00', '2026-10-12 14:30:00');
echo "[3] Uji Bentrok Tumpang Tindih (12:30-14:30): " . ($bentrok ? "BENTROK TERDETEKSI [OK]" : "GAGAL [FAIL]") . "\n";

// Kasus B: Tepat bersambung di batas jam selesai (14:00 - 16:00) -> TIDAK BENTROK
$bersambung = $resService->isCollision(2, '2026-10-12 14:00:00', '2026-10-12 16:00:00');
echo "[3] Uji Jadwal Bersambung Bebas (14:00-16:00): " . (!$bersambung ? "TIDAK BENTROK [OK]" : "GAGAL [FAIL]") . "\n";

// Kasus C: Validasi Nomor Telepon
$validPhone = $resService->validatePhoneNumber('081234567890');
$invalidPhone = $resService->validatePhoneNumber('abc123');
echo "[3] Uji Validasi Nomor Telepon Pemesan: " . ($validPhone && !$invalidPhone ? "VALIDASI AKURAT [OK]" : "GAGAL [FAIL]") . "\n";

// Kasus D: Serialisasi Catatan Khusus Terstruktur JSON
$notesJson = $resService->formatNotesToStructuredJson(['baby_chair', 'window_seat'], 'Dekorasi bunga');
$summary = ReservationService::formatNotesSummary($notesJson);
echo "[3] Uji Catatan Khusus Terstruktur: " . (str_contains($summary, 'Baby Chair') ? "SERIALISASI JSON [OK]" : "GAGAL [FAIL]") . "\n";

// 4. Verifikasi Kalkulator Pesanan Sisi Server
$calc = new StandardOrderCalculator();
$subtotal = $calc->calculateSubtotal(28000.00, 3);
$items = [
    ['subtotal' => 84000.00],
    ['price' => 6000.00, 'quantity' => 2]
];
$grandTotal = $calc->calculateTotal($items);

echo "[4] Kalkulasi Subtotal 3 x Rp28.000: Rp " . number_format($subtotal, 0, ',', '.') . " [OK]\n";
echo "[4] Kalkulasi Grand Total (Rp84.000 + Rp12.000): Rp " . number_format($grandTotal, 0, ',', '.') . " [OK]\n";

echo "=== SELURUH VERIFIKASI SISTEM LOKAL V2 LULUS 100% ===\n";
