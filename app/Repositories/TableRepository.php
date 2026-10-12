<?php

namespace App\Repositories;

use Config\Database;
use PDO;
use PDOException;
use RuntimeException;

/**
 * Repository data meja restoran.
 */
class TableRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM restaurant_tables ORDER BY table_number ASC");
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM restaurant_tables WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function findByNumber(string $number, ?int $excludeId = null): ?array
    {
        $sql = "SELECT * FROM restaurant_tables WHERE table_number = :number";
        $params = [':number' => $number];

        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function getAvailableTables(int $minCapacity = 1): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM restaurant_tables 
            WHERE status = 'available' AND capacity >= :capacity 
            ORDER BY capacity ASC, table_number ASC
        ");
        $stmt->execute([':capacity' => $minCapacity]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO restaurant_tables (table_number, capacity, status)
            VALUES (:table_number, :capacity, :status)
        ");
        $stmt->execute([
            ':table_number' => $data['table_number'],
            ':capacity' => (int)$data['capacity'],
            ':status' => $data['status'] ?? 'available'
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE restaurant_tables
            SET table_number = :table_number, capacity = :capacity, status = :status
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':table_number' => $data['table_number'],
            ':capacity' => (int)$data['capacity'],
            ':status' => $data['status']
        ]);
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM restaurant_tables WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            // Tangani pelanggaran integritas referensial jika meja sudah memiliki reservasi/pesanan
            if ($e->getCode() === '23000') {
                throw new RuntimeException("Meja tidak dapat dihapus karena masih memiliki riwayat reservasi atau pesanan terkait.");
            }
            throw $e;
        }
    }
}
