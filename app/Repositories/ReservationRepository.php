<?php

namespace App\Repositories;

use Config\Database;
use PDO;

/**
 * Repository data reservasi meja dan pengecekan bentrok jadwal.
 */
class ReservationRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public function getAllWithDetails(): array
    {
        $sql = "
            SELECT r.*, 
                   t.table_number, t.capacity AS table_capacity,
                   u.name AS staff_name
            FROM reservations r
            JOIN restaurant_tables t ON r.table_id = t.id
            LEFT JOIN users u ON r.user_id = u.id
            ORDER BY r.start_time DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $sql = "
            SELECT r.*, 
                   t.table_number, t.capacity AS table_capacity,
                   u.name AS staff_name
            FROM reservations r
            JOIN restaurant_tables t ON r.table_id = t.id
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.id = :id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Memeriksa bentrok jadwal reservasi pada meja yang sama (BR-02):
     * Aturan Interval: (new_start < existing_end) AND (new_end > existing_start)
     * Hanya berlaku untuk reservasi berstatus 'confirmed'.
     */
    public function getCollisions(int $tableId, string $startTime, string $endTime, ?int $excludeId = null): array
    {
        $sql = "
            SELECT id, table_id, customer_name, start_time, end_time, status
            FROM reservations
            WHERE table_id = :table_id
              AND status = 'confirmed'
              AND start_time < :new_end_time
              AND end_time > :new_start_time
        ";
        $params = [
            ':table_id' => $tableId,
            ':new_end_time' => $endTime,
            ':new_start_time' => $startTime
        ];

        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Mengambil reservasi terkonfirmasi pada tanggal tertentu untuk kalkulasi ketersediaan slot & meja
     */
    public function getActiveReservationsByDate(string $date): array
    {
        $sql = "
            SELECT r.id, r.table_id, r.customer_name, r.start_time, r.end_time, r.status
            FROM reservations r
            WHERE DATE(r.start_time) = :res_date
              AND r.status = 'confirmed'
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':res_date' => $date]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO reservations (table_id, user_id, customer_name, customer_phone, start_time, end_time, guest_count, status, notes)
            VALUES (:table_id, :user_id, :customer_name, :customer_phone, :start_time, :end_time, :guest_count, :status, :notes)
        ");
        $stmt->execute([
            ':table_id' => (int)$data['table_id'],
            ':user_id' => (int)($data['user_id'] ?? 1),
            ':customer_name' => trim($data['customer_name']),
            ':customer_phone' => trim($data['customer_phone']),
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':guest_count' => isset($data['guest_count']) && $data['guest_count'] !== null ? (int)$data['guest_count'] : null,
            ':status' => $data['status'] ?? 'confirmed',
            ':notes' => $data['notes'] ?? null
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE reservations 
            SET table_id = :table_id,
                customer_name = :customer_name,
                customer_phone = :customer_phone,
                start_time = :start_time,
                end_time = :end_time,
                notes = :notes
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':table_id' => (int)$data['table_id'],
            ':customer_name' => trim($data['customer_name']),
            ':customer_phone' => trim($data['customer_phone']),
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':notes' => $data['notes'] ?? null
        ]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE reservations SET status = :status WHERE id = :id");
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id
        ]);
    }

    public function getReport(string $startDate, string $endDate, ?string $status = null): array
    {
        $sql = "
            SELECT r.*, t.table_number, t.capacity AS table_capacity
            FROM reservations r
            JOIN restaurant_tables t ON r.table_id = t.id
            WHERE r.start_time >= :start_date AND r.start_time <= :end_date
        ";
        $params = [
            ':start_date' => $startDate . ' 00:00:00',
            ':end_date' => $endDate . ' 23:59:59'
        ];

        if (!empty($status)) {
            $sql .= " AND r.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY r.start_time ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
