<?php

namespace App\Models;

/**
 * Class MenuItem
 * Model entitas menu makanan/minuman dengan tipe data harga desimal presisi.
 * Standar: J.620100.018.02 (Pemrograman Berorientasi Objek)
 */
class MenuItem
{
    private ?int $id;
    private string $name;
    private string $category;
    private float $price;
    private bool $isAvailable;

    public function __construct(?int $id, string $name, string $category, float $price, bool $isAvailable = true)
    {
        $this->id = $id;
        $this->name = $name;
        $this->category = $category;
        $this->price = $price;
        $this->isAvailable = $isAvailable;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    public function getFormattedPrice(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }
}
