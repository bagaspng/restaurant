<?php

namespace App\Models;

/**
 * Model item makanan dengan atribut tingkat kepedasan.
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
