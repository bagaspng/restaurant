<?php

namespace App\Models;

/**
 * Model header transaksi pesanan restoran.
 */
class Order
{
    private ?int $id;
    private ?int $reservationId;
    private int $tableId;
    private int $userId;
    private float $totalAmount;
    private string $status;
    /** @var OrderItem[] */
    private array $items = [];

    public function __construct(
        ?int $id,
        ?int $reservationId,
        int $tableId,
        int $userId,
        float $totalAmount = 0.00,
        string $status = 'pending'
    ) {
        $this->id = $id;
        $this->reservationId = $reservationId;
        $this->tableId = $tableId;
        $this->userId = $userId;
        $this->totalAmount = $totalAmount;
        $this->status = $status;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReservationId(): ?int
    {
        return $this->reservationId;
    }

    public function getTableId(): int
    {
        return $this->tableId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(float $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function addItem(OrderItem $item): void
    {
        $this->items[] = $item;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
