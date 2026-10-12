<?php

namespace App\Services;

use App\Repositories\ReservationRepository;
use App\Repositories\TableRepository;
use Config\Config;
use DateTime;
use InvalidArgumentException;

/**
 * Logika bisnis reservasi meja (durasi slot 120 menit, ketersediaan, dan deteksi bentrok jadwal).
 */
class ReservationService
{
    public const ALLOWED_SPECIAL_REQUESTS = [
        'baby_chair' => 'Baby Chair (Kursi Bayi)',
        'wheelchair_access' => 'Akses Kursi Roda',
        'window_seat' => 'Meja Dekat Jendela',
        'special_decoration' => 'Dekorasi Khusus',
        'other' => 'Lainnya'
    ];

    private ReservationRepository $reservationRepo;
    private TableRepository $tableRepo;

    public function __construct(
        ?ReservationRepository $reservationRepo = null,
        ?TableRepository $tableRepo = null
    ) {
        $this->reservationRepo = $reservationRepo ?? new ReservationRepository();
        $this->tableRepo = $tableRepo ?? new TableRepository();
    }

    /**
     * Menghitung jam selesai: jam mulai + durasi tetap 120 menit (2 Jam)
     */
    public function calculateEndTime(string $startTime, ?int $durationMinutes = null): string
    {
        $duration = $durationMinutes ?? (int)Config::get('reservation_duration', 120);
        $dt = new DateTime($startTime);
        $dt->modify("+{$duration} minutes");
        return $dt->format('Y-m-d H:i:s');
    }

    /**
     * Menghasilkan daftar slot waktu operasional terstruktur berdasarkan konfigurasi sistem
     */
    public function getOperatingSlots(): array
    {
        $opening = Config::get('resto_opening_time', '10:00');
        $closing = Config::get('resto_closing_time', '22:00');
        $duration = (int)Config::get('reservation_slot_duration', 120);

        $slots = [];
        $current = strtotime("2000-01-01 {$opening}:00");
        $endDay = strtotime("2000-01-01 {$closing}:00");

        while ($current + ($duration * 60) <= $endDay) {
            $slotStart = date('H:i', $current);
            $next = $current + ($duration * 60);
            $slotEnd = date('H:i', $next);

            $slots[] = [
                'id' => "{$slotStart}-{$slotEnd}",
                'start' => $slotStart,
                'end' => $slotEnd,
                'label' => "{$slotStart} – {$slotEnd} WIB",
                'duration_minutes' => $duration
            ];

            $current = $next;
        }

        return $slots;
    }

    /**
     * Algoritma Deteksi Bentrok Jadwal (BR-02):
     * Dua reservasi pada meja yang sama bentrok jika:
     * (Start_Baru < End_Lama) AND (End_Baru > Start_Lama)
     * Mengembalikan true jika jadwal BENTROK, false jika AMAN.
     */
    public function isCollision(int $tableId, string $startTime, string $endTime, ?int $excludeId = null): bool
    {
        $conflicts = $this->reservationRepo->getCollisions($tableId, $startTime, $endTime, $excludeId);
        return count($conflicts) > 0;
    }

    /**
     * Memeriksa ketersediaan seluruh slot dan meja untuk tanggal tertentu
     */
    public function getAvailability(string $date): array
    {
        if (strtotime($date) === false) {
            throw new InvalidArgumentException("Format tanggal tidak valid.");
        }

        $today = date('Y-m-d');
        $nowTimestamp = time();
        $isPastDate = ($date < $today);

        $allTables = $this->tableRepo->getAll();
        $activeReservations = $this->reservationRepo->getActiveReservationsByDate($date);
        $slots = $this->getOperatingSlots();

        $slotResults = [];

        foreach ($slots as $slot) {
            $slotStartStr = "{$date} {$slot['start']}:00";
            $slotEndStr = "{$date} {$slot['end']}:00";
            $slotStartTs = strtotime($slotStartStr);

            $isSlotPast = false;
            if ($isPastDate) {
                $isSlotPast = true;
            } elseif ($date === $today && $slotStartTs <= $nowTimestamp) {
                $isSlotPast = true;
            }

            $tableStatuses = [];
            $availableTableCount = 0;

            foreach ($allTables as $table) {
                $tableId = (int)$table['id'];
                $isDamaged = ($table['status'] === 'occupied'); // Jika status master meja tidak aktif/sedang tidak dapat digunakan

                // Cek bentrok dengan reservasi yang ada pada slot ini
                $hasCollision = false;
                foreach ($activeReservations as $res) {
                    if ((int)$res['table_id'] === $tableId) {
                        $resStart = $res['start_time'];
                        $resEnd = $res['end_time'];
                        if ($slotStartStr < $resEnd && $slotEndStr > $resStart) {
                            $hasCollision = true;
                            break;
                        }
                    }
                }

                if ($isDamaged) {
                    $status = 'unavailable';
                    $statusLabel = 'Tidak dapat digunakan';
                } elseif ($hasCollision) {
                    $status = 'occupied';
                    $statusLabel = 'Terisi (Sudah Dipesan)';
                } else {
                    $status = 'available';
                    $statusLabel = 'Tersedia';
                    if (!$isSlotPast) {
                        $availableTableCount++;
                    }
                }

                $tableStatuses[] = [
                    'id' => $tableId,
                    'table_number' => $table['table_number'],
                    'capacity' => (int)$table['capacity'],
                    'status' => $status,
                    'status_label' => $statusLabel,
                    'is_selectable' => ($status === 'available' && !$isSlotPast)
                ];
            }

            $slotStatus = 'available';
            $slotBadge = "{$availableTableCount} Meja Tersedia";

            if ($isSlotPast) {
                $slotStatus = 'past';
                $slotBadge = 'Waktu Lewat';
            } elseif ($availableTableCount === 0) {
                $slotStatus = 'full';
                $slotBadge = 'Penuh';
            }

            $slotResults[] = [
                'slot_id' => $slot['id'],
                'start' => $slot['start'],
                'end' => $slot['end'],
                'label' => $slot['label'],
                'status' => $slotStatus,
                'status_badge' => $slotBadge,
                'available_count' => $availableTableCount,
                'tables' => $tableStatuses
            ];
        }

        return [
            'date' => $date,
            'is_past_date' => $isPastDate,
            'slots' => $slotResults
        ];
    }

    /**
     * Memformat catatan khusus ke string JSON terstruktur yang tervalidasi
     */
    public function formatNotesToStructuredJson(array $inputRequests, ?string $otherNote): string
    {
        $validRequests = [];
        foreach ($inputRequests as $req) {
            if (array_key_exists($req, self::ALLOWED_SPECIAL_REQUESTS)) {
                $validRequests[] = $req;
            }
        }

        $data = [
            'requests' => array_values(array_unique($validRequests)),
            'other_note' => in_array('other', $validRequests, true) ? trim((string)$otherNote) : ''
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Menghasilkan teks ringkasan manusia dari catatan terstruktur JSON
     */
    public static function formatNotesSummary(?string $notes): string
    {
        if (empty($notes)) {
            return '-';
        }

        $decoded = json_decode($notes, true);
        if (!is_array($decoded)) {
            return trim($notes) ?: '-';
        }

        $labels = [];
        $requests = $decoded['requests'] ?? [];
        foreach ($requests as $reqKey) {
            if ($reqKey === 'other') {
                $otherText = trim($decoded['other_note'] ?? '');
                $labels[] = !empty($otherText) ? "Lainnya: {$otherText}" : 'Permintaan Khusus Lainnya';
            } elseif (isset(self::ALLOWED_SPECIAL_REQUESTS[$reqKey])) {
                $labels[] = self::ALLOWED_SPECIAL_REQUESTS[$reqKey];
            }
        }

        return !empty($labels) ? implode(', ', $labels) : '-';
    }

    /**
     * Memvalidasi format nomor telepon Indonesia / internasional standar
     */
    public function validatePhoneNumber(string $phone): bool
    {
        $clean = preg_replace('/[\s\-\(\)]/', '', $phone);
        // Minimal 10 digit, maksimal 16 digit, numerik (bisa diawali +)
        return (bool)preg_match('/^\+?[0-9]{10,16}$/', $clean);
    }

    /**
     * Mendaftarkan reservasi baru dengan validasi berlapis dan durasi tetap 120 menit
     */
    public function createReservation(array $data): int
    {
        $customerName = trim($data['customer_name'] ?? '');
        $customerPhone = trim($data['customer_phone'] ?? '');
        $tableId = (int)($data['table_id'] ?? 0);
        $startTime = $data['start_time'] ?? '';

        // 1. Validasi Identitas Pemesan
        if (empty($customerName) || strlen($customerName) < 2) {
            throw new InvalidArgumentException("Nama pemesan wajib diisi (minimal 2 karakter).");
        }
        if (empty($customerPhone) || !$this->validatePhoneNumber($customerPhone)) {
            throw new InvalidArgumentException("Nomor telepon pemesan wajib diisi dengan format valid (10-16 digit).");
        }

        // 2. Validasi Tanggal & Waktu
        if (empty($startTime) || strtotime($startTime) === false) {
            throw new InvalidArgumentException("Waktu mulai reservasi tidak valid.");
        }

        $resDate = date('Y-m-d', strtotime($startTime));
        $today = date('Y-m-d');
        if ($resDate < $today) {
            throw new InvalidArgumentException("Tidak dapat membuat reservasi untuk tanggal yang sudah berlalu.");
        }
        if ($resDate === $today && strtotime($startTime) <= time()) {
            throw new InvalidArgumentException("Slot waktu untuk hari ini sudah terlewat.");
        }

        // 3. Validasi Keberadaan Meja
        $table = $this->tableRepo->findById($tableId);
        if (!$table) {
            throw new InvalidArgumentException("Meja yang dipilih tidak ditemukan di sistem.");
        }
        if ($table['status'] === 'occupied') {
            throw new InvalidArgumentException("Meja {$table['table_number']} sedang tidak dapat digunakan.");
        }

        // 4. Hitung Jam Selesai Otomatis (Durasi Tetap 120 Menit)
        $endTime = $this->calculateEndTime($startTime, 120);
        $data['end_time'] = $endTime;

        // 5. Validasi Bentrok Jadwal di Sisi Server (Mencegah Race Condition)
        if ($this->isCollision($tableId, $startTime, $endTime)) {
            throw new InvalidArgumentException("Meja {$table['table_number']} baru saja dipesan oleh pelanggan lain pada slot waktu tersebut. Silakan pilih meja atau slot lain.");
        }

        // 6. Format Catatan Khusus
        $requests = $data['special_requests'] ?? [];
        $otherNote = $data['other_notes'] ?? '';
        $data['notes'] = $this->formatNotesToStructuredJson((array)$requests, $otherNote);

        // guest_count disimpan opsional (NULL atau kapasitas meja)
        $data['guest_count'] = null;

        return $this->reservationRepo->create($data);
    }

    /**
     * Memperbarui data reservasi (untuk fungsi edit)
     */
    public function updateReservation(int $id, array $data): bool
    {
        $existing = $this->reservationRepo->findById($id);
        if (!$existing) {
            throw new InvalidArgumentException("Data reservasi tidak ditemukan.");
        }

        $customerName = trim($data['customer_name'] ?? '');
        $customerPhone = trim($data['customer_phone'] ?? '');
        $tableId = (int)($data['table_id'] ?? $existing['table_id']);
        $startTime = $data['start_time'] ?? $existing['start_time'];

        if (empty($customerName) || strlen($customerName) < 2) {
            throw new InvalidArgumentException("Nama pemesan wajib diisi (minimal 2 karakter).");
        }
        if (empty($customerPhone) || !$this->validatePhoneNumber($customerPhone)) {
            throw new InvalidArgumentException("Nomor telepon pemesan wajib diisi dengan format valid.");
        }

        $endTime = $this->calculateEndTime($startTime, 120);
        $data['end_time'] = $endTime;

        // Validasi bentrok dengan mengecualikan ID reservasi ini
        if ($this->isCollision($tableId, $startTime, $endTime, $id)) {
            throw new InvalidArgumentException("Meja tersebut telah dipesan pada slot waktu yang dipilih.");
        }

        $requests = $data['special_requests'] ?? [];
        $otherNote = $data['other_notes'] ?? '';
        $data['notes'] = $this->formatNotesToStructuredJson((array)$requests, $otherNote);

        return $this->reservationRepo->update($id, [
            'table_id' => $tableId,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'notes' => $data['notes']
        ]);
    }

    /**
     * Memperbarui status reservasi
     */
    public function updateStatus(int $id, string $status): bool
    {
        $validStatuses = ['confirmed', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses, true)) {
            throw new InvalidArgumentException("Status reservasi [{$status}] tidak valid.");
        }

        return $this->reservationRepo->updateStatus($id, $status);
    }
}
