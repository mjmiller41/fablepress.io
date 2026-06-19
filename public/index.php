<?php
// FablePress Front Controller & Slim Router (Slim-Skeleton style)
require_once __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();

// Load settings (environment variables, constants, etc.)
$settings = require __DIR__ . '/../app/settings.php';
$settings($app);

// Register middlewares
$middleware = require __DIR__ . '/../app/middleware.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

// Run the application
$app->run();
