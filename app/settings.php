<?php
// FablePress settings configuration (Slim-Skeleton style)
use Slim\App;

return function (App $app) {
    // Load environment variables from .env file if it exists
    if (file_exists(__DIR__ . '/../.env')) {
        $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, '#') === 0 || !strpos($line, '=')) {
                continue;
            }
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (preg_match('/^"(.*)"$/', $value, $matches) || preg_match('/^\'(.*)\'$/', $value, $matches)) {
                $value = $matches[1];
            }
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }

    // DB configuration mode: 'sqlite' or 'mysql'
    if (!defined('DB_MODE')) {
        define('DB_MODE', getenv('DB_MODE') ?: 'sqlite');
    }

    // MySQL Configuration
    if (!defined('DB_HOST')) {
        define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    }
    if (!defined('DB_NAME')) {
        define('DB_NAME', getenv('DB_NAME') ?: 'fablepress');
    }
    if (!defined('DB_USER')) {
        define('DB_USER', getenv('DB_USER') ?: 'root');
    }
    if (!defined('DB_PASS')) {
        define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
    }

    // SQLite Configuration
    if (!defined('DB_SQLITE_PATH')) {
        define('DB_SQLITE_PATH', __DIR__ . '/Database/fablepress.db');
    }

    // Session configuration
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
};
