<?php

namespace App\Middleware;

use App\Helpers\Session;

/**
 * Class AdminMiddleware
 * Memastikan pengguna memiliki peran 'admin' untuk mengakses fitur manajerial.
 */
class AdminMiddleware
{
    public function handle(): void
    {
        $user = Session::get('user');

        if (!$user || ($user['role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo "<div style='font-family:sans-serif; text-align:center; padding:50px;'>";
            echo "<h1 style='color:#dc3545;'>403 Akses Ditolak (Forbidden)</h1>";
            echo "<p>Halaman ini hanya dapat diakses oleh Administrator.</p>";
            echo "<a href='/dashboard'>Kembali ke Dashboard</a>";
            echo "</div>";
            exit;
        }
    }
}
