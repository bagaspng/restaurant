<?php

namespace App\Repositories;

use Config\Database;
use PDO;
use Throwable;

/**
 * Repository data pesanan dan rincian item pesanan.
 */
class OrderRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public function getAllWithDetails(): array
    {
        $sql = "
            SELECT o.*, 
                   t.table_number,
                   u.name AS staff_name,
                   COUNT(oi.id) AS total_items
            FROM orders o
            JOIN restaurant_tables t ON o.table_id = t.id
            JOIN users u ON o.user_id = u.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function findByIdWithItems(int $id): ?array
    {
        $sqlOrder = "
            SELECT o.*, t.table_number, u.name AS staff_name
            FROM orders o
            JOIN restaurant_tables t ON o.table_id = t.id
            JOIN users u ON o.user_id = u.id
            WHERE o.id = :id
            LIMIT 1
        ";
        $stmtOrder = $this->db->prepare($sqlOrder);
        $stmtOrder->execute([':id' => $id]);
        $order = $stmtOrder->fetch();

        if (!$order) {
            return null;
        }

        $sqlItems = "SELECT * FROM order_items WHERE order_id = :order_id ORDER BY id ASC";
        $stmtItems = $this->db->prepare($sqlItems);
        $stmtItems->execute([':order_id' => $id]);
        $order['items'] = $stmtItems->fetchAll();

        return $order;
    }

    /**
     * Menyimpan transaksi pesanan multi-tabel dalam satu transaksi database atomik (BR-05)
     */
    public function createOrderWithItems(array $orderData, array $items): int
    {
        $this->db->beginTransaction();

        try {
            // 1. Simpan Header Pesanan (total_amount awal = 0)
            $stmtOrder = $this->db->prepare("
                INSERT INTO orders (table_id, reservation_id, user_id, total_amount, status)
                VALUES (:table_id, :reservation_id, :user_id, :total_amount, :status)
            ");
            $stmtOrder->execute([
                ':table_id' => (int)$orderData['table_id'],
                ':reservation_id' => !empty($orderData['reservation_id']) ? (int)$orderData['reservation_id'] : null,
                ':user_id' => (int)$orderData['user_id'],
                ':total_amount' => (float)($orderData['total_amount'] ?? 0.00),
                ':status' => $orderData['status'] ?? 'pending'
            ]);
            $orderId = (int)$this->db->lastInsertId();

            // 2. Simpan Setiap Detail Item dengan Price Snapshotting
            $stmtItem = $this->db->prepare("
                INSERT INTO order_items (order_id, menu_id, item_name, price_at_order, quantity, subtotal)
                VALUES (:order_id, :menu_id, :item_name, :price_at_order, :quantity, :subtotal)
            ");

            foreach ($items as $item) {
                $stmtItem->execute([
                    ':order_id' => $orderId,
                    ':menu_id' => (int)$item['menu_id'],
                    ':item_name' => $item['item_name'],
                    ':price_at_order' => (float)$item['price_at_order'],
                    ':quantity' => (int)$item['quantity'],
                    ':subtotal' => (float)$item['subtotal']
                ]);
            }

            // 3. Commit Transaksi jika seluruh proses berhasil
            $this->db->commit();
            return $orderId;

        } catch (Throwable $e) {
            // Batalkan seluruh data jika terjadi kesalahan
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE orders SET status = :status WHERE id = :id");
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id
        ]);
    }

    public function getReport(string $startDate, string $endDate, ?string $status = null): array
    {
        $sql = "
            SELECT o.*, t.table_number, u.name AS staff_name
            FROM orders o
            JOIN restaurant_tables t ON o.table_id = t.id
            JOIN users u ON o.user_id = u.id
            WHERE o.created_at >= :start_date AND o.created_at <= :end_date
        ";
        $params = [
            ':start_date' => $startDate . ' 00:00:00',
            ':end_date' => $endDate . ' 23:59:59'
        ];

        if (!empty($status)) {
            $sql .= " AND o.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY o.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
