<?php

namespace App\Controllers;

use App\Repositories\TableRepository;
use App\Helpers\Session;
use App\Helpers\View;
use Throwable;

/**
 * Controller manajemen master data meja restoran.
 */
class TableController
{
    private TableRepository $tableRepo;

    public function __construct()
    {
        $this->tableRepo = new TableRepository();
    }

    public function index(): void
    {
        $tables = $this->tableRepo->getAll();
        View::render('tables.index', [
            'title' => 'Manajemen Meja - Sistem Pemesanan Restoran',
            'tables' => $tables
        ]);
    }

    public function create(): void
    {
        View::render('tables.form', [
            'title' => 'Tambah Meja Baru',
            'table' => null,
            'isEdit' => false
        ]);
    }

    public function store(): void
    {
        if (!Session::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token CSRF tidak valid.');
            View::redirect('/tables');
        }

        $tableNumber = trim($_POST['table_number'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 0);
        $status = $_POST['status'] ?? 'available';

        // Validasi Aturan Bisnis BR-01
        if (empty($tableNumber)) {
            Session::setFlash('error', 'Nomor meja wajib diisi.');
            View::redirect('/tables/create');
        }

        if ($capacity < 1) {
            Session::setFlash('error', 'Kapasitas meja harus minimal 1 orang.');
            View::redirect('/tables/create');
        }

        if ($this->tableRepo->findByNumber($tableNumber)) {
            Session::setFlash('error', "Nomor meja [{$tableNumber}] sudah terdaftar. Gunakan nomor lain.");
            View::redirect('/tables/create');
        }

        $this->tableRepo->create([
            'table_number' => $tableNumber,
            'capacity' => $capacity,
            'status' => $status
        ]);

        Session::setFlash('success', "Meja {$tableNumber} berhasil ditambahkan.");
        View::redirect('/tables');
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $table = $this->tableRepo->findById($id);

        if (!$table) {
            Session::setFlash('error', 'Data meja tidak ditemukan.');
            View::redirect('/tables');
        }

        View::render('tables.form', [
            'title' => 'Edit Meja ' . $table['table_number'],
            'table' => $table,
            'isEdit' => true
        ]);
    }

    public function update(): void
    {
        if (!Session::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token CSRF tidak valid.');
            View::redirect('/tables');
        }

        $id = (int)($_POST['id'] ?? 0);
        $tableNumber = trim($_POST['table_number'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 0);
        $status = $_POST['status'] ?? 'available';

        if (empty($tableNumber) || $capacity < 1) {
            Session::setFlash('error', 'Nomor meja wajib diisi dan kapasitas minimal 1.');
            View::redirect('/tables/edit?id=' . $id);
        }

        if ($this->tableRepo->findByNumber($tableNumber, $id)) {
            Session::setFlash('error', "Nomor meja [{$tableNumber}] sudah digunakan meja lain.");
            View::redirect('/tables/edit?id=' . $id);
        }

        $this->tableRepo->update($id, [
            'table_number' => $tableNumber,
            'capacity' => $capacity,
            'status' => $status
        ]);

        Session::setFlash('success', "Perubahan meja {$tableNumber} berhasil disimpan.");
        View::redirect('/tables');
    }

    public function delete(): void
    {
        if (!Session::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token CSRF tidak valid.');
            View::redirect('/tables');
        }

        $id = (int)($_POST['id'] ?? 0);

        try {
            $this->tableRepo->delete($id);
            Session::setFlash('success', 'Meja berhasil dihapus.');
        } catch (Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        View::redirect('/tables');
    }
}
