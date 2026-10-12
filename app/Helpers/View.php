<?php

namespace App\Helpers;

/**
 * Class View
 * Mengelola rendering view antarmuka, sanitasi XSS, dan respon redirect.
 * Standar: J.620100.016.01 (Menulis Kode sesuai Guidelines dan Best Practices)
 */
class View
{
    /**
     * Me-render file view dengan data yang diberikan
     *
     * @param string $viewPath Path relatif di dalam resources/views (tanpa .php)
     * @param array $data Data yang diekstrak ke dalam variabel view
     */
    public static function render(string $viewPath, array $data = []): void
    {
        $baseViewDir = dirname(__DIR__, 2) . '/resources/views/';
        $file = $baseViewDir . str_replace('.', '/', $viewPath) . '.php';

        if (!file_exists($file)) {
            http_response_code(500);
            echo "Kesalahan View: Berkas view [{$viewPath}] tidak ditemukan.";
            return;
        }

        // Ekstrak data agar dapat diakses sebagai variabel lokal di file view
        extract($data);

        require $file;
    }

    /**
     * Sanitasi string untuk mencegah serangan Cross-Site Scripting (XSS)
     */
    public static function escape(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Mengarahkan peramban (redirect) ke URL tertentu
     */
    public static function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Mengembalikan respon JSON
     */
    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
