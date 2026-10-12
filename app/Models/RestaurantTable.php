<?php

namespace App\Models;

/**
 * Model meja makan restoran (kapasitas dan status ketersediaan).
 */
class RestaurantTable
{
    private ?int $id;
    private string $tableNumber;
    private int $capacity;
    private string $status;

    public function __construct(?int $id, string $tableNumber, int $capacity, string $status = 'available')
    {
        $this->id = $id;
        $this->tableNumber = $tableNumber;
        $this->capacity = $capacity;
        $this->status = $status;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTableNumber(): string
    {
        return $this->tableNumber;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
