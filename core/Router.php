<?php
// core/Router.php
namespace Core;

class Router {
    private static array $routes = [];

    public static function get(string $path, array $handler): void {
        self::$routes['GET'][] = [
            'path'    => $path,
            'handler' => $handler
        ];
    }

    public static function post(string $path, array $handler): void {
        self::$routes['POST'][] = [
            'path'    => $path,
            'handler' => $handler
        ];
    }

    public static function run(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $config = require dirname(__DIR__) . '/config/app.php';
        $basePath = rtrim($config['base_path'], '/');

        // Strip basePath
        if ($basePath && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = '/' . trim($uri, '/');
        if ($uri === '//') $uri = '/';

        // Validasi CSRF otomatis pada semua request POST
        if ($method === 'POST') {
            CSRF::check();
        }

        $routes = self::$routes[$method] ?? [];

        foreach ($routes as $route) {
            $pattern = self::convertPathToRegex($route['path']);
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                [$controllerClass, $actionMethod] = $route['handler'];

                if (!class_exists($controllerClass)) {
                    http_response_code(500);
                    die("Controller [{$controllerClass}] tidak ditemukan.");
                }

                $controller = new $controllerClass();
                if (!method_exists($controller, $actionMethod)) {
                    http_response_code(500);
                    die("Method [{$actionMethod}] tidak ditemukan pada controller [{$controllerClass}].");
                }

                call_user_func_array([$controller, $actionMethod], $matches);
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        header('Content-Type: text/html; charset=UTF-8');
        View::render('errors/404', ['uri' => $uri], 'layouts/plain');
    }

    private static function convertPathToRegex(string $path): string {
        $path = '/' . trim($path, '/');
        if ($path === '//') $path = '/';
        
        // Convert :param to named regex
        $pattern = preg_replace('#:([a-zA-Z0-9_]+)#', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
}
