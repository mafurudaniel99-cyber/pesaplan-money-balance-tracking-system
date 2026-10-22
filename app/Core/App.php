<?php
/**
 * PesaPlan — Application bootstrap / mini DI container.
 *
 * Boots the session, loads configuration, wires Database and Auth,
 * registers routes, and dispatches the request.
 */

declare(strict_types=1);

namespace App\Core;

final class App
{
    private array $config;
    private Auth $auth;
    private array $registry = [];
    private Router $router;

    public function __construct(array $config)
    {
        $this->config = $config;
        date_default_timezone_set($config['timezone'] ?? 'Africa/Dar_es_Salaam');
        mb_internal_encoding('UTF-8');

        if ($config['debug'] ?? false) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
            ini_set('display_errors', '0');
        }

        Session::start($config);
        $this->auth = new Auth($config);
        $this->router = new Router();
    }

    public function configureDatabase(): void
    {
        $dbConfigFile = dirname(__DIR__, 2) . '/config/database.php';
        if (!file_exists($dbConfigFile)) {
            http_response_code(500);
            die('Faili ya usanidi wa hifadhidata haipo. Nakili database.example.php kuwa database.php kisha jaza taarifa halali.');
        }
        $dbConfig = require $dbConfigFile;
        Database::init($dbConfig);
    }

    public function loadRoutes(): void
    {
        $router = $this->router;
        require dirname(__DIR__, 2) . '/routes/web.php';
    }

    public function dispatch(): void
    {
        $this->router->dispatch(
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $_SERVER['REQUEST_URI'] ?? '/'
        );
    }

    /**
     * Resolve a controller class with its dependencies.
     */
    public function resolve(string $class): object
    {
        if (isset($this->registry[$class])) {
            return $this->registry[$class];
        }
        // All controllers extend Controller and accept (Auth, config).
        if (is_subclass_of($class, Controller::class)) {
            $instance = new $class($this->auth, $this->config);
            $this->registry[$class] = $instance;
            return $instance;
        }
        // Fallback: try no-arg constructor.
        $instance = new $class();
        $this->registry[$class] = $instance;
        return $instance;
    }

    public function auth(): Auth
    {
        return $this->auth;
    }

    public function config(): array
    {
        return $this->config;
    }
}
