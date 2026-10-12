<?php

namespace App\Models;

/**
 * Class BeverageItem
 * Subclass dari MenuItem untuk minuman.
 * Standar: J.620100.018.02 (Inheritance & Method Overriding)
 */
class BeverageItem extends MenuItem
{
    private bool $isCold;

    public function __construct(
        ?int $id,
        string $name,
        float $price,
        bool $isAvailable = true,
        bool $isCold = true
    ) {
        parent::__construct($id, $name, 'beverage', $price, $isAvailable);
        $this->isCold = $isCold;
    }

    public function isCold(): bool
    {
        return $this->isCold;
    }

    /**
     * Override method getServingType
     */
    public function getServingType(): string
    {
        $penyajian = $this->isCold ? 'Dingin dengan Es Batu' : 'Hangat';
        return "Disajikan segar dari Bar Minuman ({$penyajian})";
    }
}
