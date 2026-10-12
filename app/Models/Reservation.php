<?php

namespace App\Models;

/**
 * Model data reservasi meja (jadwal kunjungan, snapshot pemesan, dan catatan preferensi).
 */
class Reservation
{
    private ?int $id;
    private int $tableId;
    private int $userId;
    private string $customerName;
    private string $customerPhone;
    private string $startTime;
    private string $endTime;
    private ?int $guestCount;
    private string $status;
    private ?string $notes;

    public function __construct(
        ?int $id,
        int $tableId,
        int $userId,
        string $customerName,
        string $customerPhone,
        string $startTime,
        string $endTime,
        ?int $guestCount = null,
        string $status = 'confirmed',
        ?string $notes = null
    ) {
        $this->id = $id;
        $this->tableId = $tableId;
        $this->userId = $userId;
        $this->customerName = $customerName;
        $this->customerPhone = $customerPhone;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->guestCount = $guestCount;
        $this->status = $status;
        $this->notes = $notes;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTableId(): int
    {
        return $this->tableId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getCustomerName(): string
    {
        return $this->customerName;
    }

    public function getCustomerPhone(): string
    {
        return $this->customerPhone;
    }

    public function getStartTime(): string
    {
        return $this->startTime;
    }

    public function getEndTime(): string
    {
        return $this->endTime;
    }

    public function getGuestCount(): ?int
    {
        return $this->guestCount;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Mem-parsing catatan khusus terstruktur (JSON) ke representasi array
     */
    public function getStructuredRequests(): array
    {
        if (empty($this->notes)) {
            return ['requests' => [], 'other_note' => ''];
        }

        $decoded = json_decode($this->notes, true);
        if (is_array($decoded)) {
            return [
                'requests' => $decoded['requests'] ?? [],
                'other_note' => $decoded['other_note'] ?? ''
            ];
        }

        // Backward compatibility jika catatan lama berupa teks biasa
        return [
            'requests' => ['other'],
            'other_note' => $this->notes
        ];
    }
}
