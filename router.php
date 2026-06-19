<?php
// Local router script for PHP Built-in Server with Slim (php -S localhost:8000 router.php)
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve physical assets (CSS, images, etc.) directly if they exist
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Pass all virtual URLs to the Slim router
require_once __DIR__ . '/index.php';
