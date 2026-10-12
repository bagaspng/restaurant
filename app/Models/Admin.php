<?php

namespace App\Models;

/**
 * Class Admin
 * Subclass dari User yang merepresentasikan Administrator dengan hak akses penuh.
 * Standar: J.620100.018.02 (Inheritance & Method Overriding)
 */
class Admin extends User
{
    public function __construct(?int $id, string $name, string $email)
    {
        parent::__construct($id, $name, $email, 'admin');
    }

    /**
     * Override method getPermissions untuk mengembalikan seluruh izin sistem
     */
    public function getPermissions(): array
    {
        return [
            'manage_users',
            'manage_tables',
            'manage_menus',
            'manage_customers',
            'manage_reservations',
            'manage_orders',
            'view_reports',
            'system_configuration'
        ];
    }
}
