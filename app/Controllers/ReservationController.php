<?php

namespace App\Controllers;

use App\Services\ReservationService;
use App\Repositories\ReservationRepository;
use App\Repositories\TableRepository;
use App\Helpers\Session;
use App\Helpers\View;
use Throwable;

/**
 * Controller reservasi meja (alur wizard 6 langkah, slot 120 menit, dan snapshot pemesan).
 */
class ReservationController
{
    private ReservationService $resService;
    private ReservationRepository $resRepo;
    private TableRepository $tableRepo;

    public function __construct()
    {
        $this->resService = new ReservationService();
        $this->resRepo = new ReservationRepository();
        $this->tableRepo = new TableRepository();
    }

    public function index(): void
    {
        $reservations = $this->resRepo->getAllWithDetails();
        View::render('reservations.index', [
            'title' => 'Daftar Reservasi - Sistem Pemesanan Restoran',
            'reservations' => $reservations
        ]);
    }

    public function create(): void
    {
        $today = date('Y-m-d');
        $slots = $this->resService->getOperatingSlots();
        $specialRequests = ReservationService::ALLOWED_SPECIAL_REQUESTS;
        $oldInput = Session::getFlash('old', []);

        View::render('reservations.create', [
            'title' => 'Buat Reservasi Meja Baru',
            'today' => $today,
            'slots' => $slots,
            'specialRequests' => $specialRequests,
            'old' => $oldInput
        ]);
    }

    /**
     * Endpoint API ketersediaan slot dan meja untuk tanggal yang dipilih
     */
    public function getAvailability(): void
    {
        $date = trim($_GET['date'] ?? date('Y-m-d'));

        try {
            $data = $this->resService->getAvailability($date);
            View::json([
                'success' => true,
                'data' => $data
            ]);
        } catch (Throwable $e) {
            View::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function store(): void
    {
        if (!Session::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token CSRF tidak valid.');
            View::redirect('/reservations');
        }

        $customerName = trim($_POST['customer_name'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $resDate = trim($_POST['reservation_date'] ?? '');
        $slotStart = trim($_POST['slot_start'] ?? '');
        $tableId = (int)($_POST['table_id'] ?? 0);
        $specialRequests = (array)($_POST['special_requests'] ?? []);
        $otherNotes = trim($_POST['other_notes'] ?? '');

        // Validasi input awal
        if (empty($customerName) || empty($customerPhone) || empty($resDate) || empty($slotStart) || !$tableId) {
            Session::setFlash('error', 'Seluruh langkah reservasi wajib dilengkapi.');
            Session::setFlash('old', $_POST);
            View::redirect('/reservations/create');
        }

        $startTime = "{$resDate} {$slotStart}:00";
        $user = Session::get('user');
        $userId = $user['id'] ?? 1;

        try {
            $this->resService->createReservation([
                'table_id' => $tableId,
                'user_id' => $userId,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'start_time' => $startTime,
                'special_requests' => $specialRequests,
                'other_notes' => $otherNotes
            ]);

            Session::setFlash('success', "Reservasi untuk {$customerName} berhasil disimpan!");
            View::redirect('/reservations');
        } catch (Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            Session::setFlash('old', $_POST);
            View::redirect('/reservations/create');
        }
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $reservation = $this->resRepo->findById($id);

        if (!$reservation) {
            Session::setFlash('error', 'Data reservasi tidak ditemukan.');
            View::redirect('/reservations');
        }

        $tables = $this->tableRepo->getAll();
        $specialRequests = ReservationService::ALLOWED_SPECIAL_REQUESTS;
        
        $decoded = json_decode($reservation['notes'] ?? '', true);
        $selectedRequests = $decoded['requests'] ?? [];
        $otherNote = $decoded['other_note'] ?? ($reservation['notes'] ?? '');

        View::render('reservations.edit', [
            'title' => 'Edit Reservasi #' . $reservation['id'],
            'reservation' => $reservation,
            'tables' => $tables,
            'specialRequests' => $specialRequests,
            'selectedRequests' => $selectedRequests,
            'otherNote' => $otherNote
        ]);
    }

    public function update(): void
    {
        if (!Session::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token CSRF tidak valid.');
            View::redirect('/reservations');
        }

        $id = (int)($_POST['id'] ?? 0);
        $customerName = trim($_POST['customer_name'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $tableId = (int)($_POST['table_id'] ?? 0);
        $startTime = trim($_POST['start_time'] ?? '');
        $specialRequests = (array)($_POST['special_requests'] ?? []);
        $otherNotes = trim($_POST['other_notes'] ?? '');

        try {
            $this->resService->updateReservation($id, [
                'table_id' => $tableId,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'start_time' => $startTime,
                'special_requests' => $specialRequests,
                'other_notes' => $otherNotes
            ]);

            Session::setFlash('success', 'Data reservasi berhasil diperbarui.');
            View::redirect('/reservations');
        } catch (Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            View::redirect('/reservations/edit?id=' . $id);
        }
    }

    public function updateStatus(): void
    {
        if (!Session::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token CSRF tidak valid.');
            View::redirect('/reservations');
        }

        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        try {
            $this->resService->updateStatus($id, $status);
            Session::setFlash('success', "Status reservasi berhasil diubah menjadi '{$status}'.");
        } catch (Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        View::redirect('/reservations');
    }
}
