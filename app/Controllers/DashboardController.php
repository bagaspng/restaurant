<?php

namespace App\Controllers;

use App\Repositories\TableRepository;
use App\Repositories\ReservationRepository;
use App\Repositories\OrderRepository;
use App\Services\ReportService;
use App\Helpers\Session;
use App\Helpers\View;

/**
 * Class DashboardController
 * Pengendali halaman utama ringkasan operasional harian.
 */
class DashboardController
{
    private TableRepository $tableRepo;
    private ReservationRepository $resRepo;
    private OrderRepository $orderRepo;
    private ReportService $reportService;

    public function __construct()
    {
        $this->tableRepo = new TableRepository();
        $this->resRepo = new ReservationRepository();
        $this->orderRepo = new OrderRepository();
        $this->reportService = new ReportService();
    }

    public function index(): void
    {
        $tables = $this->tableRepo->getAll();
        $today = date('Y-m-d');

        $todayReservations = $this->resRepo->getReport($today, $today);
        $todayOrders = $this->orderRepo->getReport($today, $today);
        $financialSummary = $this->reportService->getFinancialSummary($todayOrders);

        $availableTablesCount = 0;
        foreach ($tables as $t) {
            if ($t['status'] === 'available') {
                $availableTablesCount++;
            }
        }

        View::render('dashboard.index', [
            'title' => 'Dashboard Operasional - Sistem Pemesanan Restoran',
            'user' => Session::get('user'),
            'totalTables' => count($tables),
            'availableTables' => $availableTablesCount,
            'todayReservations' => $todayReservations,
            'todayOrders' => $todayOrders,
            'financialSummary' => $financialSummary
        ]);
    }
}
