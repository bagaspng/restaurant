<?php

namespace App\Models;

/**
 * Class OrderItem
 * Model entitas rincian item pesanan dengan fitur Price Snapshotting.
 * Standar: J.620100.018.02 (Pemrograman Berorientasi Objek)
 */
class OrderItem
{
    private ?int $id;
    private int $orderId;
    private int $menuId;
    private string $itemName;
    private float $priceAtOrder;
    private int $quantity;
    private float $subtotal;

    public function __construct(
        ?int $id,
        int $orderId,
        int $menuId,
        string $itemName,
        float $priceAtOrder,
        int $quantity,
        float $subtotal
    ) {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->menuId = $menuId;
        $this->itemName = $itemName;
        $this->priceAtOrder = $priceAtOrder;
        $this->quantity = $quantity;
        $this->subtotal = $subtotal;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getMenuId(): int
    {
        return $this->menuId;
    }

    public function getItemName(): string
    {
        return $this->itemName;
    }

    public function getPriceAtOrder(): float
    {
        return $this->priceAtOrder;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getSubtotal(): float
    {
        return $this->subtotal;
    }
}
