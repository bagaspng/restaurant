<?php

namespace App\Repositories;

use Config\Database;
use PDO;
use PDOException;
use RuntimeException;

/**
 * Repository katalog menu hidangan.
 */
class MenuRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM menus ORDER BY category ASC, name ASC");
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM menus WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getAvailableMenus(): array
    {
        $stmt = $this->db->query("SELECT * FROM menus WHERE is_available = 1 ORDER BY category ASC, name ASC");
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO menus (name, category, price, is_available)
            VALUES (:name, :category, :price, :is_available)
        ");
        $stmt->execute([
            ':name' => $data['name'],
            ':category' => $data['category'],
            ':price' => (float)$data['price'],
            ':is_available' => !empty($data['is_available']) ? 1 : 0
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE menus
            SET name = :name, category = :category, price = :price, is_available = :is_available
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':category' => $data['category'],
            ':price' => (float)$data['price'],
            ':is_available' => !empty($data['is_available']) ? 1 : 0
        ]);
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM menus WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            // Tangani RESTRICT Foreign Key jika menu sudah pernah dipesan di order_items
            if ($e->getCode() === '23000') {
                throw new RuntimeException("Menu tidak dapat dihapus karena pernah tercatat pada transaksi pesanan. Anda dapat mengubah statusnya menjadi 'Habis' sebagai gantinya.");
            }
            throw $e;
        }
    }
}
