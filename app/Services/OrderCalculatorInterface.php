<?php

namespace App\Services;

/**
 * Kontrak kalkulasi nilai pesanan restoran.
 */
interface OrderCalculatorInterface
{
    /**
     * Hitung subtotal item (harga x kuantitas).
     */
    public function calculateSubtotal(float $priceAtOrder, int $quantity): float;

    /**
     * Menghitung grand total dari kumpulan item pesanan
     *
     * @param array $items Array asosiatif yang memuat ['subtotal' => float] atau ['price' => float, 'quantity' => int]
     */
    public function calculateTotal(array $items): float;
}
