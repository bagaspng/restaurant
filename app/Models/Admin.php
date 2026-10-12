<?php

namespace App\Models;

/**
 * Model Administrator dengan akses penuh ke seluruh modul sistem.
 */
class Admin extends User
{
    public function __construct(?int $id, string $name, string $email)
    {
        parent::__construct($id, $name, $email, 'admin');
    }

    /**
     * Seluruh izin operasional sistem.
     */
    public function getPermissions(): array
    {
        return [
            'manage_users',
            'manage_tables',
            'manage_menus',
            'manage_reservations',
            'manage_orders',
            'view_reports',
            'system_configuration'
        ];
    }
}
