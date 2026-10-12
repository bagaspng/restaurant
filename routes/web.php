<?php

use App\Helpers\Router;
use App\Helpers\View;
use Config\Config;
use Config\Database;

// Controllers
use App\Controllers\DashboardController;
use App\Controllers\TableController;
use App\Controllers\MenuController;
use App\Controllers\ReservationController;
use App\Controllers\OrderController;
use App\Controllers\ReportController;

// =============================================================================
// RUTE SISTEM LOKAL RESTORAN (AKTOR TUNGGAL: ADMINISTRATOR)
// Seluruh fitur dapat langsung diakses tanpa login.
// =============================================================================

// Dashboard Utama
Router::get('/', [DashboardController::class, 'index']);
Router::get('/dashboard', [DashboardController::class, 'index']);

// Pemeriksaan Kesehatan Sistem & Database
Router::get('/health', function () {
    $dbStatus = 'TIDAK TERHUBUNG';
    $dbError = null;

    try {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT 1");
        if ($stmt) {
            $dbStatus = 'TERHUBUNG (MySQL ' . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . ')';
        }
    } catch (\Throwable $e) {
        $dbError = $e->getMessage();
    }

    View::json([
        'status' => 'OK',
        'system_mode' => 'Single-Actor Administrator (Local Mode)',
        'php_version' => PHP_VERSION,
        'app_env' => Config::get('app_env'),
        'database' => $dbStatus,
        'database_error' => $dbError
    ]);
});

// Master Data Meja
Router::get('/tables', [TableController::class, 'index']);
Router::get('/tables/create', [TableController::class, 'create']);
Router::post('/tables/store', [TableController::class, 'store']);
Router::get('/tables/edit', [TableController::class, 'edit']);
Router::post('/tables/update', [TableController::class, 'update']);
Router::post('/tables/delete', [TableController::class, 'delete']);

// Master Data Menu
Router::get('/menus', [MenuController::class, 'index']);
Router::get('/menus/create', [MenuController::class, 'create']);
Router::post('/menus/store', [MenuController::class, 'store']);
Router::get('/menus/edit', [MenuController::class, 'edit']);
Router::post('/menus/update', [MenuController::class, 'update']);
Router::post('/menus/delete', [MenuController::class, 'delete']);

// Reservasi Meja (Alur 6-Langkah & Ketersediaan Slot 120 Menit)
Router::get('/reservations', [ReservationController::class, 'index']);
Router::get('/reservations/create', [ReservationController::class, 'create']);
Router::get('/reservations/availability', [ReservationController::class, 'getAvailability']);
Router::post('/reservations/store', [ReservationController::class, 'store']);
Router::get('/reservations/edit', [ReservationController::class, 'edit']);
Router::post('/reservations/update', [ReservationController::class, 'update']);
Router::post('/reservations/status', [ReservationController::class, 'updateStatus']);

// Pesanan & Nota Tagihan
Router::get('/orders', [OrderController::class, 'index']);
Router::get('/orders/create', [OrderController::class, 'create']);
Router::post('/orders/store', [OrderController::class, 'store']);
Router::get('/orders/show', [OrderController::class, 'show']);
Router::post('/orders/status', [OrderController::class, 'updateStatus']);

// Laporan Rekapitulasi Finansial & Operasional
Router::get('/reports', [ReportController::class, 'index']);
Router::get('/reports/print', [ReportController::class, 'printView']);
