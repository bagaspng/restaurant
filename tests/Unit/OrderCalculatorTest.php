<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\StandardOrderCalculator;
use App\Services\OrderCalculatorInterface;
use InvalidArgumentException;

/**
 * Unit test untuk kalkulator subtotal dan total pesanan.
 */
class OrderCalculatorTest extends TestCase
{
    private OrderCalculatorInterface $calculator;

    protected function setUp(): void
    {
        $this->calculator = new StandardOrderCalculator();
    }

    public function testCalculateSubtotalPrecision(): void
    {
        $price = 28000.00;
        $qty = 3;
        $subtotal = $this->calculator->calculateSubtotal($price, $qty);

        $this->assertEquals(84000.00, $subtotal);
    }

    public function testCalculateTotalMultiItems(): void
    {
        $items = [
            ['subtotal' => 56000.00], // 2x Nasi Goreng
            ['price' => 6000.00, 'quantity' => 2] // 2x Es Teh (12.000)
        ];

        $grandTotal = $this->calculator->calculateTotal($items);
        $this->assertEquals(68000.00, $grandTotal);
    }

    public function testZeroQuantityThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculateSubtotal(25000.00, 0);
    }

    public function testNegativeQuantityThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculateSubtotal(25000.00, -2);
    }
}
