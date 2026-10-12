<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\MenuRepository;
use App\Repositories\TableRepository;
use InvalidArgumentException;

/**
 * Layanan pemrosesan pesanan, snapshot harga, dan kalkulasi subtotal.
 */
class OrderService
{
    private OrderRepository $orderRepo;
    private MenuRepository $menuRepo;
    private TableRepository $tableRepo;
    private OrderCalculatorInterface $calculator;

    public function __construct(
        ?OrderRepository $orderRepo = null,
        ?MenuRepository $menuRepo = null,
        ?TableRepository $tableRepo = null,
        ?OrderCalculatorInterface $calculator = null
    ) {
        $this->orderRepo = $orderRepo ?? new OrderRepository();
        $this->menuRepo = $menuRepo ?? new MenuRepository();
        $this->tableRepo = $tableRepo ?? new TableRepository();
        $this->calculator = $calculator ?? new StandardOrderCalculator();
    }

    /**
     * Buat pesanan baru, snapshot harga menu, dan simpan rincian item.
     */
    public function createOrder(array $headerData, array $rawItems): int
    {
        if (empty($rawItems)) {
            throw new InvalidArgumentException('Pesanan harus memiliki minimal 1 (satu) item menu.');
        }

        $preparedItems = [];
        $totalAmount = 0.00;

        foreach ($rawItems as $item) {
            $menuId = (int)($item['menu_id'] ?? 0);
            $qty = (int)($item['quantity'] ?? 0);

            if ($qty < 1) {
                continue; // Abaikan item dengan kuantitas 0
            }

            $menu = $this->menuRepo->findById($menuId);
            if (!$menu) {
                throw new InvalidArgumentException("Item menu ID [{$menuId}] tidak ditemukan.");
            }

            if (empty($menu['is_available'])) {
                throw new InvalidArgumentException("Menu '{$menu['name']}' saat ini habis dan tidak dapat dipesan.");
            }

            $priceAtOrder = (float)$menu['price'];
            $subtotal = $this->calculator->calculateSubtotal($priceAtOrder, $qty);

            $preparedItems[] = [
                'menu_id' => $menuId,
                'item_name' => $menu['name'],        // Price & Name Snapshotting
                'price_at_order' => $priceAtOrder,   // Price Snapshotting
                'quantity' => $qty,
                'subtotal' => $subtotal
            ];

            $totalAmount += $subtotal;
        }

        if (empty($preparedItems)) {
            throw new InvalidArgumentException('Tidak ada item valid yang dipilih dalam pesanan.');
        }

        $headerData['total_amount'] = $totalAmount;
        $headerData['status'] = 'pending';

        // Simpan ke database via transaksi atomik
        $orderId = $this->orderRepo->createOrderWithItems($headerData, $preparedItems);

        // Ubah status meja menjadi occupied
        $this->tableRepo->update($headerData['table_id'], [
            'table_number' => $this->tableRepo->findById($headerData['table_id'])['table_number'],
            'capacity' => $this->tableRepo->findById($headerData['table_id'])['capacity'],
            'status' => 'occupied'
        ]);

        return $orderId;
    }

    /**
     * Memperbarui status pesanan (misal: 'completed' saat pelanggan membayar di kasir)
     */
    public function updateOrderStatus(int $id, string $status): bool
    {
        $validStatuses = ['pending', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses, true)) {
            throw new InvalidArgumentException("Status pesanan [{$status}] tidak valid.");
        }

        $order = $this->orderRepo->findByIdWithItems($id);
        if (!$order) {
            throw new InvalidArgumentException("Pesanan ID [{$id}] tidak ditemukan.");
        }

        $res = $this->orderRepo->updateStatus($id, $status);

        // Jika pesanan selesai atau dibatalkan, bebaskan status meja kembali jadi available
        if (in_array($status, ['completed', 'cancelled'], true)) {
            $table = $this->tableRepo->findById($order['table_id']);
            if ($table) {
                $this->tableRepo->update($table['id'], [
                    'table_number' => $table['table_number'],
                    'capacity' => $table['capacity'],
                    'status' => 'available'
                ]);
            }
        }

        return $res;
    }
}
