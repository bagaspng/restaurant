<?php

namespace App\Models;

/**
 * Class Abstrak User
 * Fondasi entitas pengguna sistem untuk menerapkan prinsip Object-Oriented Programming (OOP).
 * Standar: J.620100.018.02 (Mengimplementasikan Pemrograman Berorientasi Objek)
 */
abstract class User
{
    protected ?int $id;
    protected string $name;
    protected string $email;
    protected string $role;

    public function __construct(?int $id, string $name, string $email, string $role)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->role = $role;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * Method abstrak yang wajib di-override oleh setiap subclass (Admin dan Staff)
     * Menunjukkan penerapan Polymorphism dan Abstraction.
     */
    abstract public function getPermissions(): array;

    /**
     * Memeriksa apakah pengguna memiliki hak akses tertentu
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getPermissions(), true);
    }
}
