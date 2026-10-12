<?php

namespace App\Models;

/**
 * Class FoodItem
 * Subclass dari MenuItem untuk hidangan makanan.
 * Standar: J.620100.018.02 (Inheritance & Method Overriding)
 */
class FoodItem extends MenuItem
{
    private string $spicinessLevel;

    public function __construct(
        ?int $id,
        string $name,
        float $price,
        bool $isAvailable = true,
        string $spicinessLevel = 'Sedang'
    ) {
        parent::__construct($id, $name, 'food', $price, $isAvailable);
        $this->spicinessLevel = $spicinessLevel;
    }

    public function getSpicinessLevel(): string
    {
        return $this->spicinessLevel;
    }

    /**
     * Override method getServingType
     */
    public function getServingType(): string
    {
        return "Disajikan hangat dari Dapur Utama (Tingkat Pedas: {$this->spicinessLevel})";
    }
}
