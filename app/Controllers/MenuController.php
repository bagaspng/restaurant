<?php

namespace App\Controllers;

use App\Repositories\MenuRepository;
use App\Helpers\Session;
use App\Helpers\View;
use Throwable;

/**
 * Controller katalog menu hidangan restoran.
 */
class MenuController
{
    private MenuRepository $menuRepo;

    public function __construct()
    {
        $this->menuRepo = new MenuRepository();
    }

    public function index(): void
    {
        $menus = $this->menuRepo->getAll();
        View::render('menus.index', [
            'title' => 'Katalog Menu - Sistem Pemesanan Restoran',
            'menus' => $menus
        ]);
    }

    public function create(): void
    {
        View::render('menus.form', [
            'title' => 'Tambah Menu Baru',
            'menu' => null,
            'isEdit' => false
        ]);
    }

    public function store(): void
    {
        if (!Session::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token CSRF tidak valid.');
            View::redirect('/menus');
        }

        $name = trim($_POST['name'] ?? '');
        $category = $_POST['category'] ?? 'food';
        $price = (float)($_POST['price'] ?? 0);
        $isAvailable = isset($_POST['is_available']);

        // Validasi Aturan Bisnis
        if (empty($name)) {
            Session::setFlash('error', 'Nama menu wajib diisi.');
            View::redirect('/menus/create');
        }

        if ($price < 0) {
            Session::setFlash('error', 'Harga menu tidak boleh bernilai negatif.');
            View::redirect('/menus/create');
        }

        $this->menuRepo->create([
            'name' => $name,
            'category' => $category,
            'price' => $price,
            'is_available' => $isAvailable
        ]);

        Session::setFlash('success', "Menu '{$name}' berhasil ditambahkan ke katalog.");
        View::redirect('/menus');
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $menu = $this->menuRepo->findById($id);

        if (!$menu) {
            Session::setFlash('error', 'Menu tidak ditemukan.');
            View::redirect('/menus');
        }

        View::render('menus.form', [
            'title' => 'Ubah Menu: ' . $menu['name'],
            'menu' => $menu,
            'isEdit' => true
        ]);
    }

    public function update(): void
    {
        if (!Session::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token CSRF tidak valid.');
            View::redirect('/menus');
        }

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $category = $_POST['category'] ?? 'food';
        $price = (float)($_POST['price'] ?? 0);
        $isAvailable = isset($_POST['is_available']);

        if (empty($name) || $price < 0) {
            Session::setFlash('error', 'Nama menu wajib diisi dan harga tidak boleh negatif.');
            View::redirect('/menus/edit?id=' . $id);
        }

        $this->menuRepo->update($id, [
            'name' => $name,
            'category' => $category,
            'price' => $price,
            'is_available' => $isAvailable
        ]);

        Session::setFlash('success', "Perubahan menu '{$name}' berhasil disimpan.");
        View::redirect('/menus');
    }

    public function delete(): void
    {
        if (!Session::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token CSRF tidak valid.');
            View::redirect('/menus');
        }

        $id = (int)($_POST['id'] ?? 0);

        try {
            $this->menuRepo->delete($id);
            Session::setFlash('success', 'Menu berhasil dihapus dari sistem.');
        } catch (Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        View::redirect('/menus');
    }
}
