<?php

namespace App\Middleware;

use App\Helpers\Session;
use App\Helpers\View;

/**
 * Class AuthMiddleware
 * Memastikan pengguna telah terautentikasi sebelum mengakses halaman internal.
 */
class AuthMiddleware
{
    public function handle(): void
    {
        if (!Session::has('user')) {
            Session::setFlash('error', 'Silakan masuk terlebih dahulu untuk mengakses sistem.');
            View::redirect('/login');
        }
    }
}
