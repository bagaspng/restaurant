<?php

namespace Config;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Koneksi database PDO (Singleton).
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }

    /**
     * Ambil instance koneksi PDO.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            Config::load();

            $host = Config::get('db_host', '127.0.0.1');
            $port = Config::get('db_port', 3306);
            $dbName = Config::get('db_name', 'restaurant_db');
            $user = Config::get('db_user', 'root');
            $pass = Config::get('db_pass', '');

            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";

            $options = [
                // Melemparkan exception pada setiap kesalahan query
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                // Mengembalikan hasil query sebagai array asosiatif secara default
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Menonaktifkan emulasi prepared statements agar dieksekusi native oleh MySQL
                PDO::ATTR_EMULATE_PREPARES => false,
                // Pengaturan koneksi persisten opsional
                PDO::ATTR_PERSISTENT => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Jangan tampilkan password atau rincian sensitif ke publik
                throw new RuntimeException("Gagal terhubung ke basis data: " . $e->getMessage(), (int)$e->getCode(), $e);
            }
        }

        return self::$instance;
    }

    /**
     * Set koneksi manual (berguna saat mocking/testing).
     */
    public static function setConnection(?PDO $pdo): void
    {
        self::$instance = $pdo;
    }

    /**
     * Reset koneksi database.
     */
    public static function closeConnection(): void
    {
        self::$instance = null;
    }
}
