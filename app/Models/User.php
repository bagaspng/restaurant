<?php

namespace App\Models;

/**
 * Model dasar pengguna sistem.
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
     * Daftar izin akses peran pengguna.
     */
    abstract public function getPermissions(): array;

    /**
     * Cek apakah pengguna memiliki izin tertentu.
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getPermissions(), true);
    }
}
