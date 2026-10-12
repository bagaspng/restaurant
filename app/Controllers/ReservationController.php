<?php

namespace App\Controllers;

use App\Services\ReservationService;
use App\Repositories\ReservationRepository;
use App\Repositories\TableRepository;
use App\Repositories\CustomerRepository;
use App\Helpers\Session;
use App\Helpers\View;
use Throwable;

/**
 * Class ReservationController
 * Pengendali aksi reservasi meja restoran.
 */
class ReservationController
{
    private ReservationService $resService;
    private ReservationRepository $resRepo;
    private TableRepository $tableRepo;
    private CustomerRepository $custRepo;

    public function __construct()
    {
        $this->resService = new ReservationService();
        $this->resRepo = new ReservationRepository();
        $this->tableRepo = new TableRepository();
        $this->custRepo = new CustomerRepository();
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
        $customers = $this->custRepo->getAll();
        $tables = $this->tableRepo->getAll();

        View::render('reservations.create', [
            'title' => 'Buat Reservasi Baru',
            'customers' => $customers,
            'tables' => $tables
        ]);
    }

    public function store(): void
    {
        if (!Session::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token CSRF tidak valid.');
            View::redirect('/reservations');
        }

        $customerId = (int)($_POST['customer_id'] ?? 0);
        $tableId = (int)($_POST['table_id'] ?? 0);
        $guestCount = (int)($_POST['guest_count'] ?? 1);
        $startTimeRaw = trim($_POST['start_time'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (!$customerId || !$tableId || empty($startTimeRaw)) {
            Session::setFlash('error', 'Semua bidang bertanda bintang wajib diisi.');
            View::redirect('/reservations/create');
        }

        // Format waktu: 'Y-m-d H:i:s'
        $startTime = date('Y-m-d H:i:s', strtotime($startTimeRaw));

        $user = Session::get('user');
        $userId = $user['id'] ?? 1;

        try {
            $this->resService->createReservation([
                'customer_id' => $customerId,
                'table_id' => $tableId,
                'user_id' => $userId,
                'start_time' => $startTime,
                'guest_count' => $guestCount,
                'notes' => $notes ?: null
            ]);

            Session::setFlash('success', 'Reservasi meja berhasil dijadwalkan!');
            View::redirect('/reservations');
        } catch (Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            View::redirect('/reservations/create');
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
