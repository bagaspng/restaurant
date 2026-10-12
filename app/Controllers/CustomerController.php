<?php

namespace App\Controllers;

use App\Repositories\CustomerRepository;
use App\Helpers\Session;
use App\Helpers\View;

/**
 * Class CustomerController
 * Pengendali master data pelanggan.
 */
class CustomerController
{
    private CustomerRepository $custRepo;

    public function __construct()
    {
        $this->custRepo = new CustomerRepository();
    }

    public function index(): void
    {
        $customers = $this->custRepo->getAll();
        View::render('customers.index', [
            'title' => 'Data Pelanggan - Sistem Pemesanan Restoran',
            'customers' => $customers
        ]);
    }

    public function store(): void
    {
        if (!Session::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token CSRF tidak valid.');
            View::redirect('/customers');
        }

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        // Validasi Aturan Bisnis BR-CUST
        if (strlen($name) < 2) {
            Session::setFlash('error', 'Nama pelanggan minimal harus 2 karakter.');
            View::redirect('/customers');
        }

        // Validasi nomor telepon berupa teks digit angka 10-15 karakter
        if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
            Session::setFlash('error', 'Nomor telepon harus berupa angka dengan panjang 10-15 digit.');
            View::redirect('/customers');
        }

        $this->custRepo->create([
            'name' => $name,
            'phone' => $phone,
            'notes' => $notes ?: null
        ]);

        Session::setFlash('success', "Pelanggan '{$name}' berhasil didaftarkan.");
        View::redirect('/customers');
    }
}
