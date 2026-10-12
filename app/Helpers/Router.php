<?php

namespace App\Helpers;

/**
 * Class Router
 * Router sederhana berbasis pola Front Controller untuk mengarahkan request HTTP ke Controller.
 * Standar: Pemrograman Berorientasi Objek & Modularitas (PSR-12)
 */
class Router
{
    private static array $routes = [];

    /**
     * Mendaftarkan rute HTTP GET
     */
    public static function get(string $path, array|callable $handler, array $middlewares = []): void
    {
        self::addRoute('GET', $path, $handler, $middlewares);
    }

    /**
     * Mendaftarkan rute HTTP POST
     */
    public static function post(string $path, array|callable $handler, array $middlewares = []): void
    {
        self::addRoute('POST', $path, $handler, $middlewares);
    }

    private static function addRoute(string $method, string $path, array|callable $handler, array $middlewares): void
    {
        self::$routes[] = [
            'method' => $method,
            'path' => rtrim($path, '/') ?: '/',
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    /**
     * Menjalankan dispatch rute berdasarkan URI dan HTTP Method
     */
    public static function dispatch(string $uri, string $method): void
    {
        // Bersihkan query string dari URI
        $parsedUrl = parse_url($uri, PHP_URL_PATH);
        $cleanPath = rtrim($parsedUrl, '/') ?: '/';

        foreach (self::$routes as $route) {
            if ($route['method'] === $method && $route['path'] === $cleanPath) {
                // Eksekusi Middleware jika ada
                foreach ($route['middlewares'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    if (method_exists($middleware, 'handle')) {
                        $middleware->handle();
                    }
                }

                // Eksekusi Handler
                if (is_callable($route['handler'])) {
                    call_user_func($route['handler']);
                    return;
                }

                if (is_array($route['handler'])) {
                    [$controllerClass, $action] = $route['handler'];
                    $controller = new $controllerClass();
                    $controller->$action();
                    return;
                }
            }
        }

        // Jika rute tidak ditemukan (404 Not Found)
        http_response_code(404);
        echo "<div style='font-family:sans-serif; text-align:center; padding:50px;'>";
        echo "<h1>404 Halaman Tidak Ditemukan</h1>";
        echo "<p>Rute [{$method} {$cleanPath}] tidak terdaftar dalam sistem.</p>";
        echo "<a href='/'>Kembali ke Beranda</a>";
        echo "</div>";
    }
}
