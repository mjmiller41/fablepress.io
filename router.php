<?php
// Local router script for PHP Built-in Server (php -S localhost:8000 router.php)
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Exclude actual files and directories so they are served directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Force trailing slashes on clean paths (bypassing dot file extensions)
if ($uri !== '/' && !strpos($uri, '.') && substr($uri, -1) !== '/') {
    header('Location: ' . $uri . '/', true, 301);
    exit;
}

$clean_uri = trim($uri, '/');

// Route /stories/ -> stories.php
if ($clean_uri === 'stories') {
    require __DIR__ . '/stories.php';
    exit;
}

// Route /admin/ -> admin/index.php
if ($clean_uri === 'admin') {
    require __DIR__ . '/admin/index.php';
    exit;
}

// Route generic slugs to index.php
if ($clean_uri !== '') {
    // If it's a file path in admin/ that doesn't exist, let it fail or route normally
    if (strpos($clean_uri, 'admin/') === 0) {
        return false;
    }
    
    $_GET['slug'] = $clean_uri;
}

require __DIR__ . '/index.php';
