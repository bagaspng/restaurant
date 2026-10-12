<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Helpers\Session;
use App\Helpers\View;

/**
 * Class AuthController
 * Pengendali aksi autentikasi (Login dan Logout).
 */
class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function showLogin(): void
    {
        if (Session::has('user')) {
            View::redirect('/dashboard');
        }

        View::render('auth.login', [
            'title' => 'Masuk - Sistem Pemesanan Restoran'
        ]);
    }

    public function processLogin(): void
    {
        // Validasi CSRF
        $token = $_POST['_csrf_token'] ?? '';
        if (!Session::verifyCsrfToken($token)) {
            Session::setFlash('error', 'Token keamanan CSRF tidak valid. Silakan coba lagi.');
            View::redirect('/login');
        }

        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            Session::setFlash('error', 'Email dan kata sandi wajib diisi.');
            View::redirect('/login');
        }

        if ($this->authService->attempt($email, $password)) {
            Session::setFlash('success', 'Selamat datang kembali di sistem restoran!');
            View::redirect('/dashboard');
        } else {
            Session::setFlash('error', 'Email atau kata sandi yang Anda masukkan salah.');
            View::redirect('/login');
        }
    }

    public function logout(): void
    {
        $this->authService->logout();
        Session::setFlash('success', 'Anda telah berhasil keluar dari sistem.');
        View::redirect('/login');
    }
}
