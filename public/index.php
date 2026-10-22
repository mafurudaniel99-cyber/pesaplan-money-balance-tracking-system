<?php
/**
 * PesaPlan — Front Controller (public/index.php)
 * All non-asset requests enter here. Apache rewrites via .htaccess.
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

// Autoload our App\ namespace (PSR-4 style without Composer).
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $relative = substr($class, strlen($prefix));
    $relative = str_replace('\\', '/', $relative);
    $file = BASE_PATH . '/app/' . $relative . '.php';
    if (file_exists($file)) require $file;
});

// Load helper functions.
require BASE_PATH . '/app/Helpers/helpers.php';

// Load configuration.
$config = require BASE_PATH . '/config/app.php';

use App\Core\App;

$GLOBALS['app'] = $app = new App($config);
$app->configureDatabase();
$app->loadRoutes();
$app->dispatch();
