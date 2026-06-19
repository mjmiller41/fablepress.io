<?php
// FablePress Configuration File

// DB configuration mode: 'sqlite' or 'mysql'
define('DB_MODE', 'sqlite');

// MySQL Configuration (uncomment and fill in if using MySQL)
define('DB_HOST', 'localhost');
define('DB_NAME', 'fablepress');
define('DB_USER', 'root');
define('DB_PASS', '');

// SQLite Configuration
define('DB_SQLITE_PATH', __DIR__ . '/fablepress.db');

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function get_db_connection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    
    try {
        if (DB_MODE === 'sqlite') {
            $db_exists = file_exists(DB_SQLITE_PATH);
            $pdo = new PDO('sqlite:' . DB_SQLITE_PATH);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            if (!$db_exists) {
                initialize_database($pdo);
            }
        } else {
            $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // For MySQL, we check if users table exists, otherwise initialize
            try {
                $stmt = $pdo->query("SELECT 1 FROM users LIMIT 1");
            } catch (PDOException $e) {
                initialize_database($pdo);
            }
        }
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
    
    return $pdo;
}

function initialize_database($pdo) {
    // Determine auto-increment syntax
    $ai = DB_MODE === 'sqlite' ? 'AUTOINCREMENT' : 'AUTO_INCREMENT';
    $pk = DB_MODE === 'sqlite' ? 'INTEGER PRIMARY KEY' : 'INT AUTO_INCREMENT PRIMARY KEY';
    $text_type = DB_MODE === 'sqlite' ? 'TEXT' : 'LONGTEXT';
    
    // Create Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id $pk,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL,
        role VARCHAR(20) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create Posts Table (stories/pages)
    $pdo->exec("CREATE TABLE IF NOT EXISTS posts (
        id $pk,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        content $text_type,
        type VARCHAR(20) DEFAULT 'story', -- 'story' or 'page'
        status VARCHAR(20) DEFAULT 'draft', -- 'draft' or 'published'
        category VARCHAR(100),
        tags VARCHAR(255),
        author_id INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create Media Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS media (
        id $pk,
        filename VARCHAR(255) NOT NULL,
        filepath VARCHAR(255) NOT NULL,
        filetype VARCHAR(50),
        filesize INTEGER,
        uploaded_by INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create Navigation Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS navigation (
        id $pk,
        title VARCHAR(100) NOT NULL,
        url VARCHAR(255) NOT NULL,
        position INTEGER DEFAULT 0,
        target VARCHAR(20) DEFAULT '_self'
    )");
    
    // Create Role Permissions Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS role_permissions (
        role VARCHAR(20) NOT NULL,
        permission_key VARCHAR(50) NOT NULL,
        value INTEGER DEFAULT 0,
        PRIMARY KEY (role, permission_key)
    )");
    
    // Seed default users
    $users = [
        ['developer', password_hash('developer123', PASSWORD_DEFAULT), 'developer@fablepress.io', 'developer'],
        ['manager', password_hash('manager123', PASSWORD_DEFAULT), 'manager@fablepress.io', 'content_manager'],
        ['writer', password_hash('writer123', PASSWORD_DEFAULT), 'writer@fablepress.io', 'contributor']
    ];
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
    if (DB_MODE !== 'sqlite') {
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
    }
    foreach ($users as $user) {
        $stmt->execute($user);
    }
    
    // Seed default posts
    $welcome_content = "Welcome to FablePress.io. This is your first story! FablePress is a distraction-free, minimalist CMS built for writers who care about typography, whitespace, and clean content presentation.\n\n### Why FablePress?\nWe believe that digital publishing has become too complicated. WordPress is bloated with plugins, and headless CMS platforms require extensive development support. FablePress gives you the simplicity of a writing pad with the power of a modern content engine.\n\nYou can edit this post, create new pages, upload images, and manage your navigation menu directly from the Admin Panel. Start typing your story today!";
    
    $about_content = "About FablePress.io\n\nFablePress.io is a simplified, distraction-free CMS designed for writers, creators, and small brands. Our mission is to strip away technical bloat, allowing you to focus on your writing.\n\nWe provide a clean, modern aesthetic with premium literary typography and standard publishing tools out of the box.";
    
    $posts = [
        ['Welcome to FablePress.io', 'welcome-to-fablepress', $welcome_content, 'story', 'published', 'General', 'welcome,fablepress', 1],
        ['About Us', 'about-us', $about_content, 'page', 'published', 'Company', 'about', 1]
    ];
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO posts (title, slug, content, type, status, category, tags, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (DB_MODE !== 'sqlite') {
        $stmt = $pdo->prepare("INSERT IGNORE INTO posts (title, slug, content, type, status, category, tags, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    }
    foreach ($posts as $post) {
        $stmt->execute($post);
    }
    
    // Seed default navigation
    $nav = [
        ['Home', 'index.php', 1, '_self'],
        ['About Us', 'index.php?slug=about-us', 2, '_self'],
        ['Admin Panel', 'admin/index.php', 3, '_self']
    ];
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO navigation (title, url, position, target) VALUES (?, ?, ?, ?)");
    if (DB_MODE !== 'sqlite') {
        $stmt = $pdo->prepare("INSERT IGNORE INTO navigation (title, url, position, target) VALUES (?, ?, ?, ?)");
    }
    foreach ($nav as $item) {
        $stmt->execute($item);
    }
    
    // Seed role permissions (from Screen C Mockup)
    $permissions = [
        // Developer
        ['developer', 'edit_theme', 1],
        ['developer', 'manage_plugins', 1],
        ['developer', 'api_access', 1],
        ['developer', 'edit_css_html', 1],
        ['developer', 'view_logs', 1],
        ['developer', 'deploy_changes', 1],
        
        // Content Manager
        ['content_manager', 'publish_posts', 1],
        ['content_manager', 'edit_pages', 1],
        ['content_manager', 'manage_categories', 1],
        ['content_manager', 'moderate_comments', 1],
        ['content_manager', 'schedule_content', 1],
        ['content_manager', 'create_snippets', 1],
        
        // Contributor
        ['contributor', 'write_drafts', 1],
        ['contributor', 'edit_own_posts', 1],
        ['contributor', 'view_analytics', 1],
        ['contributor', 'publish_posts', 0],
        ['contributor', 'edit_pages', 0]
    ];
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO role_permissions (role, permission_key, value) VALUES (?, ?, ?)");
    if (DB_MODE !== 'sqlite') {
        $stmt = $pdo->prepare("INSERT IGNORE INTO role_permissions (role, permission_key, value) VALUES (?, ?, ?)");
    }
    foreach ($permissions as $perm) {
        $stmt->execute($perm);
    }
}

// Authentication Helpers
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_logged_in_user() {
    if (!is_logged_in()) return null;
    $db = get_db_connection();
    $stmt = $db->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function has_permission($role, $permission_key) {
    // Developers have absolute power
    if ($role === 'developer') {
        return true;
    }
    
    $db = get_db_connection();
    $stmt = $db->prepare("SELECT value FROM role_permissions WHERE role = ? AND permission_key = ?");
    $stmt->execute([$role, $permission_key]);
    $res = $stmt->fetch();
    
    if ($res) {
        return (int)$res['value'] === 1;
    }
    
    // Default fallback rules
    if ($role === 'content_manager') {
        $allowed = ['publish_posts', 'edit_pages', 'manage_categories', 'moderate_comments', 'schedule_content', 'create_snippets', 'write_drafts', 'edit_own_posts', 'view_analytics'];
        return in_array($permission_key, $allowed);
    }
    
    if ($role === 'contributor') {
        $allowed = ['write_drafts', 'edit_own_posts', 'view_analytics'];
        return in_array($permission_key, $allowed);
    }
    
    return false;
}

function get_role_label($role) {
    switch ($role) {
        case 'developer':
            return 'Developer';
        case 'content_manager':
            return 'Content Manager';
        case 'contributor':
            return 'Contributor';
        default:
            return ucfirst($role);
    }
}

function parse_markdown($text) {
    // Escape HTML entities but preserve existing tags if any, or escape everything for security
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    
    // Unescape common safe markdown replacements (like strong, em, h1-h3) so they render
    // Bold: **text** -> <strong>text</strong>
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    
    // Italic: *text* -> <em>text</em>
    $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);
    
    // Headings
    $text = preg_replace('/^###\s+(.*?)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^##\s+(.*?)$/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^#\s+(.*?)$/m', '<h1>$1</h1>', $text);
    
    // Lists
    $lines = explode("\n", $text);
    $in_list = false;
    $html_lines = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (preg_match('/^-\s+(.*?)$/', $line, $matches)) {
            if (!$in_list) {
                $html_lines[] = '<ul>';
                $in_list = true;
            }
            $html_lines[] = '<li>' . $matches[1] . '</li>';
        } else {
            if ($in_list) {
                $html_lines[] = '</ul>';
                $in_list = false;
            }
            if ($line !== '') {
                if (!preg_match('/^<(h[1-6]|ul|ol|li|div|p|blockquote|pre)/', $line)) {
                    $html_lines[] = '<p>' . $line . '</p>';
                } else {
                    $html_lines[] = $line;
                }
            }
        }
    }
    if ($in_list) {
        $html_lines[] = '</ul>';
    }
    
    // Decode some simple character codes that are safe, e.g. &lt;strong&gt; back to html
    $text = implode("\n", $html_lines);
    $text = str_replace(
        ['&lt;strong&gt;', '&lt;/strong&gt;', '&lt;em&gt;', '&lt;/em&gt;', '&lt;h1&gt;', '&lt;/h1&gt;', '&lt;h2&gt;', '&lt;/h2&gt;', '&lt;h3&gt;', '&lt;/h3&gt;', '&lt;ul&gt;', '&lt;/ul&gt;', '&lt;li&gt;', '&lt;/li&gt;'],
        ['<strong>', '</strong>', '<em>', '</em>', '<h1>', '</h1>', '<h2>', '</h2>', '<h3>', '</h3>', '<ul>', '</ul>', '<li>', '</li>'],
        $text
    );
    
    return $text;
}
