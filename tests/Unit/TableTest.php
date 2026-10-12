<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\RestaurantTable;

/**
 * Unit test untuk model meja restoran.
 */
class TableTest extends TestCase
{
    public function testTableInstantiationAndCapacity(): void
    {
        $table = new RestaurantTable(1, 'T-01', 4, 'available');

        $this->assertEquals(1, $table->getId());
        $this->assertEquals('T-01', $table->getTableNumber());
        $this->assertEquals(4, $table->getCapacity());
        $this->assertEquals('available', $table->getStatus());
        $this->assertTrue($table->isAvailable());
    }

    public function testTableCapacityMustBeGreaterThanZero(): void
    {
        $table = new RestaurantTable(2, 'T-02', 6, 'available');
        $this->assertGreaterThan(0, $table->getCapacity());
    }
}
