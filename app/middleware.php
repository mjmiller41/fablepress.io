<?php
// FablePress global middleware configuration (Slim-Skeleton style)
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
    // Middleware to normalize trailing slashes (Optional, but ensures clean URL uniformity)
    $app->add(function (Request $request, $handler) {
        $uri = $request->getUri();
        $path = $uri->getPath();
        
        // If it is a clean directory-like path without a dot (not a file) and doesn't end with a slash, redirect
        if ($path !== '/' && !strpos($path, '.') && substr($path, -1) !== '/') {
            $uri = $uri->withPath($path . '/');
            $response = new \Slim\Psr7\Response();
            return $response->withHeader('Location', (string)$uri)->withStatus(301);
        }
        
        return $handler->handle($request);
    });

    // Add routing and error middlewares
    $app->addRoutingMiddleware();
    $app->addErrorMiddleware(true, true, true);
};
