<?php
// Local router script for PHP Built-in Server with Slim (php -S localhost:8000 router.php)
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve physical assets (CSS, images, etc.) from the public/ folder directly if they exist
$file = __DIR__ . '/public' . $uri;
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    // If server document root is already set to public/, let the server serve it natively
    if (basename($_SERVER['DOCUMENT_ROOT']) === 'public') {
        return false;
    }
    
    // Otherwise, serve the asset manually with the correct mime type
    $mime_types = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'webp' => 'image/webp',
        'ico'  => 'image/x-icon',
        'html' => 'text/html',
        'htm'  => 'text/html'
    ];
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mime = $mime_types[$ext] ?? 'application/octet-stream';
    header("Content-Type: $mime");
    readfile($file);
    return true;
}

// Pass all virtual URLs to the Slim router
require_once __DIR__ . '/public/index.php';
