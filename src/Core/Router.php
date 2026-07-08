<?php
namespace CoreCreds\Core;

class Router 
{
    private array $routes = [];

    public function add(string $method, string $path, callable $handler): void 
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => rtrim($path, '/'),
            'handler' => $handler
        ];
    }

    public function handle(): void 
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $requestUri = rtrim($requestUri, '/');
        if ($requestUri === '') {
            $requestUri = '';
        }

        foreach ($this->routes ?? [] as $route) {
            if ($route['method'] === $requestMethod && $route['path'] === $requestUri) {
                call_user_class_fn($route['handler']);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
    }
}

function call_user_class_fn($callable) {
    $callable();
}