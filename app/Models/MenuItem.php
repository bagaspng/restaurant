<?php

namespace App\Models;

/**
 * Class Abstrak MenuItem
 * Fondasi entitas katalog menu restoran untuk menerapkan prinsip Object-Oriented Programming (OOP).
 * Standar: J.620100.018.02 (Mengimplementasikan Pemrograman Berorientasi Objek)
 */
abstract class MenuItem
{
    protected ?int $id;
    protected string $name;
    protected string $category;
    protected float $price;
    protected bool $isAvailable;

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

    /**
     * Method abstrak yang wajib di-override oleh setiap subclass (FoodItem dan BeverageItem)
     * Menunjukkan penerapan Inheritance, Abstraction, dan Method Overriding.
     */
    abstract public function getServingType(): string;
}
