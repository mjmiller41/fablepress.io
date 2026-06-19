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
        require __DIR__ . '/../templates/stories-list.php';
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
            $page_active = 'dashboard';
            ob_start();
            require __DIR__ . '/../public/admin/Controllers/index.php';
            $html = ob_get_clean();
            $response->getBody()->write($html);
            return $response;
        });

        // Login page
        $group->map(['GET', 'POST'], '/login/', function (Request $request, Response $response) {
            ob_start();
            require __DIR__ . '/../public/admin/Controllers/login.php';
            $html = ob_get_clean();
            $response->getBody()->write($html);
            return $response;
        });

        // Logout page
        $group->get('/logout/', function (Request $request, Response $response) {
            ob_start();
            require __DIR__ . '/../public/admin/Controllers/logout.php';
            $html = ob_get_clean();
            $response->getBody()->write($html);
            return $response;
        });

        // Stories list manager
        $group->get('/stories/', function (Request $request, Response $response) {
            $page_active = 'stories';
            ob_start();
            require __DIR__ . '/../public/admin/Controllers/stories.php';
            $html = ob_get_clean();
            $response->getBody()->write($html);
            return $response;
        });

        // Edit/Create story (id-based path)
        $group->map(['GET', 'POST'], '/stories/edit/{id}/', function (Request $request, Response $response, array $args) {
            $page_active = 'stories';
            $_GET['id'] = $args['id']; // Inject route ID parameter for legacy compat
            ob_start();
            require __DIR__ . '/../public/admin/Controllers/story-edit.php';
            $html = ob_get_clean();
            $response->getBody()->write($html);
            return $response;
        });

        // Create story (new post, no ID in url path)
        $group->map(['GET', 'POST'], '/stories/edit/', function (Request $request, Response $response) {
            $page_active = 'stories';
            ob_start();
            require __DIR__ . '/../public/admin/Controllers/story-edit.php';
            $html = ob_get_clean();
            $response->getBody()->write($html);
            return $response;
        });

        // Media library
        $group->map(['GET', 'POST'], '/media/', function (Request $request, Response $response) {
            $page_active = 'media';
            ob_start();
            require __DIR__ . '/../public/admin/Controllers/media.php';
            $html = ob_get_clean();
            $response->getBody()->write($html);
            return $response;
        });

        // Navigation menu editor
        $group->map(['GET', 'POST'], '/navigation/', function (Request $request, Response $response) {
            $page_active = 'navigation';
            ob_start();
            require __DIR__ . '/../public/admin/Controllers/navigation.php';
            $html = ob_get_clean();
            $response->getBody()->write($html);
            return $response;
        });

        // Roles and privileges manager
        $group->get('/roles/', function (Request $request, Response $response) {
            $page_active = 'roles';
            ob_start();
            require __DIR__ . '/../public/admin/Controllers/roles.php';
            $html = ob_get_clean();
            $response->getBody()->write($html);
            return $response;
        });
    });

    // Dynamic Slug-based Stories and Pages (e.g. /about-us/, /welcome-to-fablepress/)
    // (Defined last to prevent shadowing static routes like /admin/ and /stories/)
    $app->get('/{slug}/', function (Request $request, Response $response, array $args) {
        $slug = $args['slug'];
        $_GET['slug'] = $slug; // Inject slug for public-home.php compatibility
        
        ob_start();
        require __DIR__ . '/../templates/home.php';
        $html = ob_get_clean();
        $response->getBody()->write($html);
        return $response;
    });
};
