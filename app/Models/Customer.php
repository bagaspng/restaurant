<?php

namespace App\Models;

/**
 * Class Customer
 * Model entitas pelanggan restoran.
 */
class Customer
{
    private ?int $id;
    private string $name;
    private string $phone;
    private ?string $notes;

    public function __construct(?int $id, string $name, string $phone, ?string $notes = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->phone = $phone;
        $this->notes = $notes;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }
}
