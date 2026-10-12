<?php

namespace Config;

/**
 * Konfigurasi aplikasi dari environment (.env).
 */
class Config
{
    private static ?array $settings = null;

    /**
     * Muat konfigurasi default dan environment.
     */
    public static function load(): void
    {
        if (self::$settings !== null) {
            return;
        }

        self::$settings = [
            'app_name' => $_ENV['APP_NAME'] ?? getenv('APP_NAME') ?: 'Sistem Pemesanan Restoran',
            'app_env' => $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'development',
            'app_debug' => filter_var($_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: true, FILTER_VALIDATE_BOOLEAN),
            'app_url' => $_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'http://localhost:8000',
            'app_timezone' => $_ENV['APP_TIMEZONE'] ?? getenv('APP_TIMEZONE') ?: 'Asia/Jakarta',
            'reservation_duration' => (int) ($_ENV['RESERVATION_DEFAULT_DURATION'] ?? getenv('RESERVATION_DEFAULT_DURATION') ?: 120),
            'resto_opening_time' => $_ENV['RESTO_OPENING_TIME'] ?? getenv('RESTO_OPENING_TIME') ?: '10:00',
            'resto_closing_time' => $_ENV['RESTO_CLOSING_TIME'] ?? getenv('RESTO_CLOSING_TIME') ?: '22:00',
            'reservation_slot_duration' => (int) ($_ENV['RESERVATION_SLOT_DURATION'] ?? getenv('RESERVATION_SLOT_DURATION') ?: 120),
            
            // Database
            'db_host' => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1',
            'db_port' => (int) ($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 3306),
            'db_name' => $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'restaurant_db',
            'db_user' => $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root',
            'db_pass' => $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '',
        ];

        date_default_timezone_set(self::$settings['app_timezone']);
    }

    /**
     * Mengambil nilai konfigurasi berdasarkan kunci
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::load();
        return self::$settings[$key] ?? $default;
    }
}
