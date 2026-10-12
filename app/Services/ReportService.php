<?php

namespace App\Services;

use App\Repositories\ReservationRepository;
use App\Repositories\OrderRepository;
use InvalidArgumentException;

/**
 * Layanan rekapitulasi data laporan dan ringkasan finansial restoran.
 */
class ReportService
{
    private ReservationRepository $resRepo;
    private OrderRepository $orderRepo;

    public function __construct(
        ?ReservationRepository $resRepo = null,
        ?OrderRepository $orderRepo = null
    ) {
        $this->resRepo = $resRepo ?? new ReservationRepository();
        $this->orderRepo = $orderRepo ?? new OrderRepository();
    }

    /**
     * Validasi rentang tanggal laporan.
     */
    public function validateDateRange(string $startDate, string $endDate): void
    {
        if (strtotime($startDate) > strtotime($endDate)) {
            throw new InvalidArgumentException('Tanggal mulai tidak boleh lebih besar dari tanggal akhir.');
        }
    }

    public function getReservationReport(string $startDate, string $endDate, ?string $status = null): array
    {
        $this->validateDateRange($startDate, $endDate);
        return $this->resRepo->getReport($startDate, $endDate, $status);
    }

    public function getOrderReport(string $startDate, string $endDate, ?string $status = null): array
    {
        $this->validateDateRange($startDate, $endDate);
        return $this->orderRepo->getReport($startDate, $endDate, $status);
    }

    /**
     * Hitung ringkasan pendapatan: kotor, kas terealisasi (lunas), dan pembatalan.
     */
    public function getFinancialSummary(array $orders): array
    {
        $totalOrdersCount = count($orders);
        $grossAmount = 0.00;
        $realizedRevenue = 0.00;
        $cancelledAmount = 0.00;
        $completedCount = 0;
        $cancelledCount = 0;
        $pendingCount = 0;

        foreach ($orders as $o) {
            $amount = (float)$o['total_amount'];
            $st = $o['status'];

            if ($st === 'completed') {
                $realizedRevenue += $amount;
                $grossAmount += $amount;
                $completedCount++;
            } elseif ($st === 'pending') {
                $grossAmount += $amount;
                $pendingCount++;
            } elseif ($st === 'cancelled') {
                $cancelledAmount += $amount;
                $cancelledCount++;
            }
        }

        return [
            'total_orders' => $totalOrdersCount,
            'completed_orders' => $completedCount,
            'pending_orders' => $pendingCount,
            'cancelled_orders' => $cancelledCount,
            'gross_order_value' => $grossAmount,
            'realized_revenue' => $realizedRevenue,
            'cancelled_value' => $cancelledAmount
        ];
    }
}
