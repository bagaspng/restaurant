<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\FoodItem;
use App\Models\BeverageItem;

/**
 * Unit test untuk model menu (makanan dan minuman).
 */
class MenuTest extends TestCase
{
    public function testFoodItemInheritanceAndOverriding(): void
    {
        $food = new FoodItem(1, 'Nasi Goreng Spesial', 28000.00, true, 'Sedang');

        $this->assertEquals(1, $food->getId());
        $this->assertEquals('Nasi Goreng Spesial', $food->getName());
        $this->assertEquals(28000.00, $food->getPrice());
        $this->assertEquals('food', $food->getCategory());
        $this->assertEquals('Sedang', $food->getSpicinessLevel());

        // Pengujian Method Overriding (Karakteristik khusus makanan)
        $serving = $food->getServingType();
        $this->assertStringContainsString('Disajikan hangat dari Dapur Utama', $serving);
        $this->assertStringContainsString('Tingkat Pedas: Sedang', $serving);
    }

    public function testBeverageItemInheritanceAndOverriding(): void
    {
        $drink = new BeverageItem(2, 'Es Teh Manis', 6000.00, true, true);

        $this->assertEquals('beverage', $drink->getCategory());
        $this->assertTrue($drink->isCold());

        // Pengujian Method Overriding (Karakteristik khusus minuman)
        $serving = $drink->getServingType();
        $this->assertStringContainsString('Disajikan segar dari Bar Minuman', $serving);
        $this->assertStringContainsString('Dingin dengan Es Batu', $serving);
    }

    public function testMenuPriceCannotBeNegative(): void
    {
        $food = new FoodItem(3, 'Menu Promo', 0.00, true);
        $this->assertGreaterThanOrEqual(0.00, $food->getPrice());
    }
}
