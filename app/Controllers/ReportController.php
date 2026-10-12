<?php

namespace App\Controllers;

use App\Services\ReportService;
use App\Helpers\Session;
use App\Helpers\View;
use Throwable;

/**
 * Class ReportController
 * Pengendali laporan operasional dan rekapitulasi finansial restoran.
 */
class ReportController
{
    private ReportService $reportService;

    public function __construct()
    {
        $this->reportService = new ReportService();
    }

    public function index(): void
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $status = !empty($_GET['status']) ? $_GET['status'] : null;
        $tab = $_GET['tab'] ?? 'orders';

        $orders = [];
        $reservations = [];
        $summary = [];

        try {
            $orders = $this->reportService->getOrderReport($startDate, $endDate, $status);
            $reservations = $this->reportService->getReservationReport($startDate, $endDate, $status);
            $summary = $this->reportService->getFinancialSummary($orders);
        } catch (Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        View::render('reports.index', [
            'title' => 'Laporan Rekapitulasi - Sistem Pemesanan Restoran',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedStatus' => $status,
            'tab' => $tab,
            'orders' => $orders,
            'reservations' => $reservations,
            'summary' => $summary
        ]);
    }

    public function printView(): void
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $status = !empty($_GET['status']) ? $_GET['status'] : null;

        $orders = $this->reportService->getOrderReport($startDate, $endDate, $status);
        $summary = $this->reportService->getFinancialSummary($orders);

        View::render('reports.print', [
            'title' => 'Cetak Laporan Transaksi',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'orders' => $orders,
            'summary' => $summary
        ]);
    }
}
