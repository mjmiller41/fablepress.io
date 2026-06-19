<?php

namespace App\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Application\Services\StaticGenerator;
use Exception;

class AdminController {
    
    /**
     * Helper to render PHP view file with scope data.
     */
    private function render(Response $response, string $templateName, array $data = []): Response {
        extract($data);
        
        // Template is located directly in public/admin/
        $templatePath = __DIR__ . '/../../../public/admin/' . $templateName;
        
        ob_start();
        require $templatePath;
        $html = ob_get_clean();
        
        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Admin Dashboard Overview
     */
    public function dashboard(Request $request, Response $response): Response {
        if (!is_logged_in()) {
            return $response->withHeader('Location', '/admin/login/')->withStatus(302);
        }
        $current_user = get_logged_in_user();
        if (!$current_user) {
            return $response->withHeader('Location', '/admin/logout/')->withStatus(302);
        }

        $db = get_db_connection();

        // Fetch counts
        try {
            $total_stories = $db->query("SELECT COUNT(*) FROM posts WHERE type = 'story'")->fetchColumn();
            $published_stories = $db->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
            $draft_stories = $db->query("SELECT COUNT(*) FROM posts WHERE status = 'draft'")->fetchColumn();
            $total_users = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        } catch (Exception $e) {
            $total_stories = $published_stories = $draft_stories = $total_users = 0;
        }

        // Fetch recent stories
        try {
            $recent_stmt = $db->query("SELECT p.*, u.username as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id ORDER BY p.created_at DESC LIMIT 5");
            $recent_stories = $recent_stmt->fetchAll();
        } catch (Exception $e) {
            $recent_stories = [];
        }

        return $this->render($response, 'index.php', [
            'total_stories' => $total_stories,
            'published_stories' => $published_stories,
            'draft_stories' => $draft_stories,
            'total_users' => $total_users,
            'recent_stories' => $recent_stories,
            'current_user' => $current_user,
            'page_active' => 'dashboard'
        ]);
    }

    /**
     * Admin Sign In Screen
     */
    public function login(Request $request, Response $response): Response {
        if (is_logged_in()) {
            return $response->withHeader('Location', '/admin/')->withStatus(302);
        }

        $error = '';
        if ($request->getMethod() === 'POST') {
            $params = $request->getParsedBody() ?? $_POST;
            $username = trim($params['username'] ?? '');
            $password = trim($params['password'] ?? '');

            if ($username !== '' && $password !== '') {
                $db = get_db_connection();
                $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
                $stmt->execute([$username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['username'] = $user['username'];

                    return $response->withHeader('Location', '/admin/')->withStatus(302);
                } else {
                    $error = 'Invalid username or password.';
                }
            } else {
                $error = 'Please enter both username and password.';
            }
        }

        return $this->render($response, 'login.php', [
            'error' => $error
        ]);
    }

    /**
     * Admin Sign Out Action
     */
    public function logout(Request $request, Response $response): Response {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        return $response->withHeader('Location', '/admin/login/')->withStatus(302);
    }

    /**
     * Stories & Pages list page
     */
    public function stories(Request $request, Response $response): Response {
        if (!is_logged_in()) {
            return $response->withHeader('Location', '/admin/login/')->withStatus(302);
        }
        $current_user = get_logged_in_user();
        if (!$current_user) {
            return $response->withHeader('Location', '/admin/logout/')->withStatus(302);
        }

        $db = get_db_connection();
        $queryParams = $request->getQueryParams();
        $message = '';
        $message_type = 'success';

        // Handle deletion
        if (isset($queryParams['action']) && $queryParams['action'] === 'delete' && isset($queryParams['id'])) {
            $delete_id = (int)$queryParams['id'];
            
            // First, fetch the post to check ownership
            $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
            $stmt->execute([$delete_id]);
            $post_to_delete = $stmt->fetch();
            
            if ($post_to_delete) {
                if ($current_user['role'] === 'contributor') {
                    $message = 'Unauthorized: Contributors are not permitted to delete stories.';
                    $message_type = 'danger';
                } else {
                    $del_stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
                    $del_stmt->execute([$delete_id]);
                    $message = 'Post deleted successfully.';
                    
                    // Regenerate static site
                    StaticGenerator::generateAll();
                }
            } else {
                $message = 'Error: Story or Page not found.';
                $message_type = 'danger';
            }
        }

        // Fetch filter
        $filter_type = isset($queryParams['type']) ? trim($queryParams['type']) : 'all';

        // Build Query
        $query = "SELECT p.*, u.username as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id";
        if ($filter_type === 'story') {
            $query .= " WHERE p.type = 'story'";
        } elseif ($filter_type === 'page') {
            $query .= " WHERE p.type = 'page'";
        }
        $query .= " ORDER BY p.created_at DESC";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
            $posts = $stmt->fetchAll();
        } catch (Exception $e) {
            $posts = [];
            $message = 'Database error: ' . $e->getMessage();
            $message_type = 'danger';
        }

        return $this->render($response, 'stories.php', [
            'posts' => $posts,
            'filter_type' => $filter_type,
            'message' => $message,
            'message_type' => $message_type,
            'current_user' => $current_user,
            'page_active' => 'stories'
        ]);
    }

    /**
     * Create / Edit Story
     */
    public function editStory(Request $request, Response $response, array $args): Response {
        if (!is_logged_in()) {
            return $response->withHeader('Location', '/admin/login/')->withStatus(302);
        }
        $current_user = get_logged_in_user();
        if (!$current_user) {
            return $response->withHeader('Location', '/admin/logout/')->withStatus(302);
        }

        $db = get_db_connection();
        $id = isset($args['id']) ? (int)$args['id'] : null;
        $post = null;
        $error = '';
        $success = '';

        // Load existing post if ID is provided
        if ($id) {
            $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
            $stmt->execute([$id]);
            $post = $stmt->fetch();
            
            if (!$post) {
                return $response->withHeader('Location', '/admin/stories/')->withStatus(302);
            }
            
            // Authorization check: Contributor can only edit their own posts
            if ($current_user['role'] === 'contributor' && $post['author_id'] != $current_user['id']) {
                return $response->withHeader('Location', '/admin/stories/')->withStatus(302);
            }
        }

        // Handle Form Submission
        if ($request->getMethod() === 'POST') {
            $params = $request->getParsedBody() ?? $_POST;
            $title = trim($params['title'] ?? '');
            $slug = trim($params['slug'] ?? '');
            $content = $params['content'] ?? '';
            $type = trim($params['type'] ?? 'story');
            $category = trim($params['category'] ?? 'stories');
            $tags = trim($params['tags'] ?? '');
            $status = trim($params['status'] ?? 'draft');
            
            // Auto-generate slug if blank
            if ($slug === '' && $title !== '') {
                $slug = lowercase(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9\s]/', '', $title)));
                $slug = strtolower(preg_replace('/-+/', '-', $slug));
            } else {
                $slug = strtolower(preg_replace('/[^a-zA-Z0-9\-]/', '', str_replace(' ', '-', $slug)));
            }
            
            // Fallback slug if still empty
            if ($slug === '') {
                $slug = 'untitled-' . time();
            }
            
            // Permission checks:
            // 1. Contributor cannot publish. Force status to 'draft'
            if ($current_user['role'] === 'contributor' || !has_permission($current_user['role'], 'publish_posts')) {
                $status = 'draft';
            }
            
            // 2. Contributor cannot create/edit pages. Force type to 'story'
            if ($current_user['role'] === 'contributor' || !has_permission($current_user['role'], 'edit_pages')) {
                $type = 'story';
            }

            if ($title === '') {
                $error = 'Please enter a title.';
            } else {
                try {
                    // Check if slug is already in use by ANOTHER post
                    $slug_check = $db->prepare("SELECT id FROM posts WHERE slug = ? AND id != ?");
                    $slug_check->execute([$slug, $id ? $id : 0]);
                    if ($slug_check->fetch()) {
                        $slug .= '-' . time();
                    }
                    
                    if ($id) {
                        // Update
                        $stmt = $db->prepare("UPDATE posts SET title = ?, slug = ?, content = ?, type = ?, category = ?, tags = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                        $stmt->execute([$title, $slug, $content, $type, $category, $tags, $status, $id]);
                        $success = 'Post saved successfully.';
                        
                        // Regenerate static site
                        StaticGenerator::generateAll();
                        
                        // Reload post data
                        $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
                        $stmt->execute([$id]);
                        $post = $stmt->fetch();
                    } else {
                        // Insert
                        $stmt = $db->prepare("INSERT INTO posts (title, slug, content, type, category, tags, status, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$title, $slug, $content, $type, $category, $tags, $status, $current_user['id']]);
                        $id = $db->lastInsertId();
                        
                        // Regenerate static site
                        StaticGenerator::generateAll();
                        
                        return $response->withHeader('Location', "/admin/stories/edit/$id/?created=1")->withStatus(302);
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }

        $queryParams = $request->getQueryParams();
        if (isset($queryParams['created'])) {
            $success = 'Post created successfully.';
        }

        return $this->render($response, 'story-edit.php', [
            'id' => $id,
            'post' => $post,
            'error' => $error,
            'success' => $success,
            'current_user' => $current_user,
            'page_active' => 'stories'
        ]);
    }

    /**
     * Media Library
     */
    public function media(Request $request, Response $response): Response {
        if (!is_logged_in()) {
            return $response->withHeader('Location', '/admin/login/')->withStatus(302);
        }
        $current_user = get_logged_in_user();
        if (!$current_user) {
            return $response->withHeader('Location', '/admin/logout/')->withStatus(302);
        }

        $db = get_db_connection();
        $message = '';
        $message_type = 'success';

        $upload_dir = __DIR__ . '/../../../public/assets/uploads';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Handle File Upload
        $uploadedFiles = $request->getUploadedFiles();
        if ($request->getMethod() === 'POST' && isset($uploadedFiles['media_file'])) {
            $uploadedFile = $uploadedFiles['media_file'];
            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                $filename = $uploadedFile->getClientFilename();
                $filename = preg_replace('/[^a-zA-Z0-9\._-]/', '', $filename);
                $filetype = $uploadedFile->getClientMediaType();
                $filesize = $uploadedFile->getSize();

                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];

                if (!in_array($filetype, $allowed_types)) {
                    $message = 'Error: Only images (JPG, PNG, GIF, WEBP, SVG) are allowed.';
                    $message_type = 'danger';
                } elseif ($filesize > 5 * 1024 * 1024) {
                    $message = 'Error: File size exceeds the 5MB limit.';
                    $message_type = 'danger';
                } else {
                    $target_path = $upload_dir . '/' . $filename;
                    $path_info = pathinfo($filename);
                    $counter = 1;
                    
                    while (file_exists($target_path)) {
                        $filename = $path_info['filename'] . '_' . $counter . '.' . $path_info['extension'];
                        $target_path = $upload_dir . '/' . $filename;
                        $counter++;
                    }
                    
                    try {
                        $uploadedFile->moveTo($target_path);
                        $relative_path = 'assets/uploads/' . $filename;

                        $stmt = $db->prepare("INSERT INTO media (filename, filepath, filetype, filesize, uploaded_by) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$filename, $relative_path, $filetype, $filesize, $current_user['id']]);
                        $message = 'File uploaded successfully.';
                    } catch (Exception $e) {
                        $message = 'Error uploading file: ' . $e->getMessage();
                        $message_type = 'danger';
                    }
                }
            } else {
                $message = 'Error: File upload error code ' . $uploadedFile->getError();
                $message_type = 'danger';
            }
        }

        // Handle File Deletion
        $queryParams = $request->getQueryParams();
        if (isset($queryParams['action']) && $queryParams['action'] === 'delete' && isset($queryParams['id'])) {
            $delete_id = (int)$queryParams['id'];

            $stmt = $db->prepare("SELECT * FROM media WHERE id = ?");
            $stmt->execute([$delete_id]);
            $media = $stmt->fetch();

            if ($media) {
                if ($current_user['role'] === 'contributor') {
                    $message = 'Unauthorized: Contributors cannot delete media files.';
                    $message_type = 'danger';
                } else {
                    $disk_path = __DIR__ . '/../../../public/' . $media['filepath'];
                    if (file_exists($disk_path)) {
                        unlink($disk_path);
                    }
                    
                    $del_stmt = $db->prepare("DELETE FROM media WHERE id = ?");
                    $del_stmt->execute([$delete_id]);
                    $message = 'Media file deleted successfully.';
                }
            } else {
                $message = 'Error: File not found in database.';
                $message_type = 'danger';
            }
        }

        // Fetch all media items
        try {
            $media_stmt = $db->query("SELECT m.*, u.username FROM media m LEFT JOIN users u ON m.uploaded_by = u.id ORDER BY m.created_at DESC");
            $media_items = $media_stmt->fetchAll();
        } catch (Exception $e) {
            $media_items = [];
            $message = 'Database error: ' . $e->getMessage();
            $message_type = 'danger';
        }

        return $this->render($response, 'media.php', [
            'message' => $message,
            'message_type' => $message_type,
            'media_items' => $media_items,
            'current_user' => $current_user,
            'page_active' => 'media'
        ]);
    }

    /**
     * Navigation menu editor
     */
    public function navigation(Request $request, Response $response): Response {
        if (!is_logged_in()) {
            return $response->withHeader('Location', '/admin/login/')->withStatus(302);
        }
        $current_user = get_logged_in_user();
        if (!$current_user) {
            return $response->withHeader('Location', '/admin/logout/')->withStatus(302);
        }

        // Authorization Check: Contributor cannot manage navigation
        if ($current_user['role'] === 'contributor' || !has_permission($current_user['role'], 'edit_pages')) {
            $response->getBody()->write('<div class="alert alert-danger">Access Denied: You do not have permissions to edit navigation.</div>');
            return $response->withStatus(403);
        }

        $db = get_db_connection();
        $message = '';
        $message_type = 'success';
        $edit_item = null;

        $queryParams = $request->getQueryParams();
        $edit_id = isset($queryParams['edit_id']) ? (int)$queryParams['edit_id'] : null;

        if ($edit_id) {
            $stmt = $db->prepare("SELECT * FROM navigation WHERE id = ?");
            $stmt->execute([$edit_id]);
            $edit_item = $stmt->fetch();
        }

        // Handle Form Submission (Add or Edit)
        if ($request->getMethod() === 'POST') {
            $params = $request->getParsedBody() ?? $_POST;
            $title = trim($params['title'] ?? '');
            $url = trim($params['url'] ?? '');
            $position = (int)($params['position'] ?? 0);
            $target = trim($params['target'] ?? '_self');
            $form_id = isset($params['form_id']) ? (int)$params['form_id'] : null;
            
            if ($title === '' || $url === '') {
                $message = 'Error: Title and URL are required.';
                $message_type = 'danger';
            } else {
                try {
                    if ($form_id) {
                        $stmt = $db->prepare("UPDATE navigation SET title = ?, url = ?, position = ?, target = ? WHERE id = ?");
                        $stmt->execute([$title, $url, $position, $target, $form_id]);
                        StaticGenerator::generateAll();
                        return $response->withHeader('Location', '/admin/navigation/?success=updated')->withStatus(302);
                    } else {
                        $stmt = $db->prepare("INSERT INTO navigation (title, url, position, target) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$title, $url, $position, $target]);
                        $message = 'Navigation link added successfully.';
                        StaticGenerator::generateAll();
                    }
                } catch (Exception $e) {
                    $message = 'Database error: ' . $e->getMessage();
                    $message_type = 'danger';
                }
            }
        }

        // Handle Deletion
        if (isset($queryParams['action']) && $queryParams['action'] === 'delete' && isset($queryParams['id'])) {
            $delete_id = (int)$queryParams['id'];
            try {
                $stmt = $db->prepare("DELETE FROM navigation WHERE id = ?");
                $stmt->execute([$delete_id]);
                $message = 'Navigation link removed successfully.';
                StaticGenerator::generateAll();
            } catch (Exception $e) {
                $message = 'Database error: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }

        if (isset($queryParams['success']) && $queryParams['success'] === 'updated') {
            $message = 'Navigation link updated successfully.';
        }

        // Fetch all navigation items
        try {
            $stmt = $db->query("SELECT * FROM navigation ORDER BY position ASC");
            $nav_items = $stmt->fetchAll();
        } catch (Exception $e) {
            $nav_items = [];
            $message = 'Database error: ' . $e->getMessage();
            $message_type = 'danger';
        }

        return $this->render($response, 'navigation.php', [
            'edit_item' => $edit_item,
            'message' => $message,
            'message_type' => $message_type,
            'nav_items' => $nav_items,
            'current_user' => $current_user,
            'page_active' => 'navigation'
        ]);
    }

    /**
     * Roles and privileges editor
     */
    public function roles(Request $request, Response $response): Response {
        if (!is_logged_in()) {
            return $response->withHeader('Location', '/admin/login/')->withStatus(302);
        }
        $current_user = get_logged_in_user();
        if (!$current_user) {
            return $response->withHeader('Location', '/admin/logout/')->withStatus(302);
        }

        $db = get_db_connection();
        $message = '';
        $message_type = 'success';

        // Handle permissions updates
        if ($request->getMethod() === 'POST' && isset($_POST['update_role_permissions'])) {
            if ($current_user['role'] !== 'developer') {
                $message = 'Unauthorized: Only Developers can modify role permissions.';
                $message_type = 'danger';
            } else {
                $params = $request->getParsedBody() ?? $_POST;
                $role_to_update = $params['role_to_update'] ?? '';
                
                $keys = [];
                if ($role_to_update === 'developer') {
                    $keys = ['edit_theme', 'manage_plugins', 'api_access', 'edit_css_html', 'view_logs', 'deploy_changes'];
                } elseif ($role_to_update === 'content_manager') {
                    $keys = ['publish_posts', 'edit_pages', 'manage_categories', 'moderate_comments', 'schedule_content', 'create_snippets'];
                } elseif ($role_to_update === 'contributor') {
                    $keys = ['write_drafts', 'edit_own_posts', 'view_analytics'];
                }
                
                try {
                    foreach ($keys as $key) {
                        $value = isset($params[$key]) ? 1 : 0;
                        
                        $del = $db->prepare("DELETE FROM role_permissions WHERE role = ? AND permission_key = ?");
                        $del->execute([$role_to_update, $key]);
                        
                        $ins = $db->prepare("INSERT INTO role_permissions (role, permission_key, value) VALUES (?, ?, ?)");
                        $ins->execute([$role_to_update, $key, $value]);
                    }
                    $message = 'Permissions updated for ' . get_role_label($role_to_update) . '.';
                } catch (Exception $e) {
                    $message = 'Database error: ' . $e->getMessage();
                    $message_type = 'danger';
                }
            }
        }

        // Fetch current permissions state
        $perms = [];
        try {
            $stmt = $db->query("SELECT * FROM role_permissions");
            while ($row = $stmt->fetch()) {
                $perms[$row['role']][$row['permission_key']] = (int)$row['value'];
            }
        } catch (Exception $e) {
            // Falls back to defaults in has_permission()
        }

        return $this->render($response, 'roles.php', [
            'perms' => $perms,
            'message' => $message,
            'message_type' => $message_type,
            'current_user' => $current_user,
            'page_active' => 'roles'
        ]);
    }
}
