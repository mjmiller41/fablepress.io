<?php
// FablePress application routes (Slim-Skeleton style)
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
    // ==========================================
    // 1. PUBLIC VISITOR ROUTES
    // ==========================================
    
    // Homepage
    $app->get('/', function (Request $request, Response $response) {
        $slug = null;
        ob_start();
        require __DIR__ . '/../templates/home.php';
        $html = ob_get_clean();
        $response->getBody()->write($html);
        return $response;
    });

    // Stories/Blog Catalog List
    $app->get('/stories/', function (Request $request, Response $response) {
        ob_start();
        require __DIR__ . '/../templates/stories.php';
        $html = ob_get_clean();
        $response->getBody()->write($html);
        return $response;
    });

    // Single Story Details Page (e.g. /stories/welcome-to-fablepress/)
    $app->get('/stories/{slug}/', function (Request $request, Response $response, array $args) {
        $slug = $args['slug'];
        $_GET['slug'] = $slug; // Inject slug for compatibility
        
        ob_start();
        require __DIR__ . '/../templates/story.php';
        $html = ob_get_clean();
        $response->getBody()->write($html);
        return $response;
    });

    // ==========================================
    // 2. ADMIN PORTAL ROUTES
    // ==========================================
    $app->group('/admin', function ($group) {
        
        // Dashboard main page
        $group->get('/', function (Request $request, Response $response) {
            return (new \App\Application\Controllers\AdminController())->dashboard($request, $response);
        });

        // Login page
        $group->map(['GET', 'POST'], '/login/', function (Request $request, Response $response) {
            return (new \App\Application\Controllers\AdminController())->login($request, $response);
        });

        // Logout page
        $group->get('/logout/', function (Request $request, Response $response) {
            return (new \App\Application\Controllers\AdminController())->logout($request, $response);
        });

        // Stories list manager
        $group->get('/stories/', function (Request $request, Response $response) {
            return (new \App\Application\Controllers\AdminController())->stories($request, $response);
        });

        // Edit/Create story (id-based path)
        $group->map(['GET', 'POST'], '/stories/edit/{id}/', function (Request $request, Response $response, array $args) {
            return (new \App\Application\Controllers\AdminController())->editStory($request, $response, $args);
        });

        // Create story (new post, no ID in url path)
        $group->map(['GET', 'POST'], '/stories/edit/', function (Request $request, Response $response) {
            return (new \App\Application\Controllers\AdminController())->editStory($request, $response, []);
        });

        // Media library
        $group->map(['GET', 'POST'], '/media/', function (Request $request, Response $response) {
            return (new \App\Application\Controllers\AdminController())->media($request, $response);
        });

        // Navigation menu editor
        $group->map(['GET', 'POST'], '/navigation/', function (Request $request, Response $response) {
            return (new \App\Application\Controllers\AdminController())->navigation($request, $response);
        });

        // Roles and privileges manager
        $group->get('/roles/', function (Request $request, Response $response) {
            return (new \App\Application\Controllers\AdminController())->roles($request, $response);
        });
    });

    // Dynamic Slug-based Stories and Pages (e.g. /about-us/, /welcome-to-fablepress/)
    // (Defined last to prevent shadowing static routes like /admin/ and /stories/)
    $app->get('/{slug}/', function (Request $request, Response $response, array $args) {
        $slug = $args['slug'];
        $_GET['slug'] = $slug; // Inject slug for public-home.php compatibility
        
        ob_start();
        require __DIR__ . '/../templates/story.php';
        $html = ob_get_clean();
        $response->getBody()->write($html);
        return $response;
    });
};
