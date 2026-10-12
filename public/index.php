<?php

/**
 * Front Controller (public/index.php)
 * Titik masuk utama seluruh permintaan HTTP aplikasi web.
 */

// 1. Muat Composer Autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Config\Config;
use App\Helpers\Session;
use App\Helpers\Router;

// 2. Muat variabel lingkungan dari .env jika file ada
if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv = Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
}

// 3. Inisialisasi konfigurasi terpusat & session aman
Config::load();
Session::start();

// Inisialisasi identitas Administrator lokal (Single-Actor tanpa login)
if (!Session::has('user')) {
    Session::set('user', [
        'id' => 1,
        'name' => 'Administrator',
        'email' => 'admin@resto.local',
        'role' => 'admin'
    ]);
}

// 4. Muat definisi rute aplikasi
require_once dirname(__DIR__) . '/routes/web.php';

// 5. Eksekusi Router
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

Router::dispatch($requestUri, $requestMethod);
