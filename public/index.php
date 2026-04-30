<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

// Dotenv
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Error Reporting
if ($_ENV['APP_ENV'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// PHP hatalarını dosyaya yönlendir
ini_set('error_log', __DIR__ . '/../storage/logs/php_errors.log');

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

// Set up settings
$settings = require __DIR__ . '/../config/settings.php';
$settings($containerBuilder);

// Set up dependencies (Veritabanı, Service vb.)
$dependencies = require __DIR__ . '/../config/dependencies.php';
$dependencies($containerBuilder);

// Twig Container'a Ekleniyor
$containerBuilder->addDefinitions([
    Twig::class => function () {
        return Twig::create(__DIR__ . '/../views', ['cache' => false]);
    }
]);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();

// Twig Middleware
$app->add(TwigMiddleware::createFromContainer($app, Twig::class));

// Set up Base Path
$basePath = $_ENV['APP_BASE_PATH'] ?? '';
if (!empty($basePath)) {
    $app->setBasePath($basePath);
}

// Register middleware
$middleware = require __DIR__ . '/../config/middleware.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/../config/routes.php';
$routes($app);

// Run app
$app->run();
