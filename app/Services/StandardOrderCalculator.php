<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Kalkulator standar pesanan restoran.
 */
class StandardOrderCalculator implements OrderCalculatorInterface
{
    /**
     * Hitung subtotal = harga x kuantitas.
     */
    public function calculateSubtotal(float $priceAtOrder, int $quantity): float
    {
        if ($priceAtOrder < 0) {
            throw new InvalidArgumentException('Harga tidak boleh bernilai negatif.');
        }

        if ($quantity < 1) {
            throw new InvalidArgumentException('Kuantitas item harus minimal 1.');
        }

        return round($priceAtOrder * $quantity, 2);
    }

    /**
     * Menghitung grand total seluruh item
     */
    public function calculateTotal(array $items): float
    {
        $total = 0.00;

        foreach ($items as $item) {
            if (isset($item['subtotal'])) {
                $total += (float)$item['subtotal'];
            } elseif (isset($item['price'], $item['quantity'])) {
                $total += $this->calculateSubtotal((float)$item['price'], (int)$item['quantity']);
            }
        }

        return round($total, 2);
    }
}
