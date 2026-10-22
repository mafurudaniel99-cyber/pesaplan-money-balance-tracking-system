<?php
/**
 * PesaPlan — Simple regex-based router.
 * Routes are declared in routes/web.php.
 *
 * Example:
 *   $router->add('GET', '/dashboard', [DashboardController::class, 'index']);
 *
 * URL parameters use {name} placeholders and become method arguments.
 */

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, array $handler): void
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function get(string $pattern, array $handler): void    { $this->add('GET', $pattern, $handler); }
    public function post(string $pattern, array $handler): void   { $this->add('POST', $pattern, $handler); }
    public function put(string $pattern, array $handler): void    { $this->add('PUT', $pattern, $handler); }
    public function delete(string $pattern, array $handler): void { $this->add('DELETE', $pattern, $handler); }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        // Strip query string
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        // Normalise leading slash
        if ($path === '' || $path[0] !== '/') {
            $path = '/' . $path;
        }
        // Remove trailing slash except root
        if (strlen($path) > 1) {
            $path = rtrim($path, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            $pattern = $this->compile($route['pattern']);
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // drop full match
                [$class, $action] = $route['handler'];
                $params = array_map(fn($m) => urldecode($m), array_values($matches));
                // Resolve container
                $controller = $GLOBALS['app']->resolve($class);
                call_user_func_array([$controller, $action], $params);
                return;
            }
        }
        http_response_code(404);
        echo '404 — Ukurasa haupatikani.';
    }

    private function compile(string $pattern): string
    {
        // Convert {name} to a capturing group of [^/]+
        $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $regex . '$#';
    }
}
