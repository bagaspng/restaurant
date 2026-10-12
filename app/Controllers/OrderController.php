<?php

namespace App\Controllers;

use App\Services\OrderService;
use App\Repositories\OrderRepository;
use App\Repositories\TableRepository;
use App\Repositories\MenuRepository;
use App\Repositories\ReservationRepository;
use App\Helpers\Session;
use App\Helpers\View;
use Throwable;

/**
 * Controller transaksi pesanan hidangan dan pembuatan nota tagihan.
 */
class OrderController
{
    private OrderService $orderService;
    private OrderRepository $orderRepo;
    private TableRepository $tableRepo;
    private MenuRepository $menuRepo;
    private ReservationRepository $resRepo;

    public function __construct()
    {
        $this->orderService = new OrderService();
        $this->orderRepo = new OrderRepository();
        $this->tableRepo = new TableRepository();
        $this->menuRepo = new MenuRepository();
        $this->resRepo = new ReservationRepository();
    }

    public function index(): void
    {
        $orders = $this->orderRepo->getAllWithDetails();
        View::render('orders.index', [
            'title' => 'Daftar Pesanan - Sistem Pemesanan Restoran',
            'orders' => $orders
        ]);
    }

    public function create(): void
    {
        $tableId = (int)($_GET['table_id'] ?? 0);
        $reservationId = (int)($_GET['reservation_id'] ?? 0);

        $tables = $this->tableRepo->getAll();
        $menus = $this->menuRepo->getAvailableMenus();

        View::render('orders.create', [
            'title' => 'Buat Pesanan Baru',
            'tables' => $tables,
            'menus' => $menus,
            'selectedTableId' => $tableId,
            'selectedReservationId' => $reservationId
        ]);
    }

    public function store(): void
    {
        if (!Session::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token CSRF tidak valid.');
            View::redirect('/orders');
        }

        $tableId = (int)($_POST['table_id'] ?? 0);
        $reservationId = (int)($_POST['reservation_id'] ?? 0);
        $itemsRaw = $_POST['items'] ?? []; // format: [menu_id => qty]

        if (!$tableId) {
            Session::setFlash('error', 'Silakan pilih meja untuk pesanan ini.');
            View::redirect('/orders/create');
        }

        // Susun array item
        $items = [];
        foreach ($itemsRaw as $menuId => $qty) {
            $quantity = (int)$qty;
            if ($quantity > 0) {
                $items[] = [
                    'menu_id' => (int)$menuId,
                    'quantity' => $quantity
                ];
            }
        }

        if (empty($items)) {
            Session::setFlash('error', 'Silakan pilih minimal 1 item menu dengan jumlah kuantitas lebih dari 0.');
            View::redirect('/orders/create?table_id=' . $tableId);
        }

        $user = Session::get('user');
        $userId = $user['id'] ?? 1;

        try {
            $orderId = $this->orderService->createOrder([
                'table_id' => $tableId,
                'reservation_id' => $reservationId ?: null,
                'user_id' => $userId
            ], $items);

            Session::setFlash('success', "Pesanan #{$orderId} berhasil dibuat!");
            View::redirect('/orders/show?id=' . $orderId);
        } catch (Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            View::redirect('/orders/create?table_id=' . $tableId);
        }
    }

    public function show(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $order = $this->orderRepo->findByIdWithItems($id);

        if (!$order) {
            Session::setFlash('error', 'Pesanan tidak ditemukan.');
            View::redirect('/orders');
        }

        View::render('orders.show', [
            'title' => 'Rincian Pesanan #' . $order['id'],
            'order' => $order
        ]);
    }

    public function updateStatus(): void
    {
        if (!Session::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token CSRF tidak valid.');
            View::redirect('/orders');
        }

        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        try {
            $this->orderService->updateOrderStatus($id, $status);
            Session::setFlash('success', "Status pesanan #{$id} berhasil diubah menjadi '{$status}'.");
        } catch (Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        View::redirect('/orders/show?id=' . $id);
    }
}
