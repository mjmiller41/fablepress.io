<?php
$db = get_db_connection();

// Fetch navigation items
try {
    $nav_stmt = $db->query("SELECT * FROM navigation ORDER BY position ASC");
    $nav_items = $nav_stmt->fetchAll();
} catch (Exception $e) {
    $nav_items = [];
}

// Fetch the requested story or page
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : null;
$post = null;

if ($slug) {
    $post_stmt = $db->prepare("SELECT p.*, u.username as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.slug = ? AND p.status = 'published'");
    $post_stmt->execute([$slug]);
    $post = $post_stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post ? htmlspecialchars($post['title']) . ' - FablePress.io' : 'Page Not Found - FablePress.io'; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

    <!-- Header & Navigation -->
    <header class="site-header">
        <div class="container header-inner">
            <a href="/" class="logo">
                <div class="logo-icon">F</div>
                FablePress.io
            </a>
            <nav class="main-nav">
                <ul>
                    <?php foreach ($nav_items as $item): ?>
                        <?php 
                            // Hide admin pages from the header navigation
                            if (strpos($item['url'], 'admin') !== false) {
                                continue;
                            }
                            // Determine if this item is active
                            $is_active = false;
                            $clean_url = trim($item['url'], '/');
                            if ($slug && $clean_url === $slug) {
                                $is_active = true;
                            }
                        ?>
                        <li class="<?php echo $is_active ? 'active' : ''; ?>">
                            <a href="<?php echo htmlspecialchars($item['url']); ?>" target="<?php echo htmlspecialchars($item['target']); ?>">
                                <?php echo htmlspecialchars($item['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <?php if ($post): ?>
            <!-- Single Article / Page View -->
            <article class="article-view">
                <div class="reader-container">
                    <header class="article-header">
                        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
                        <div class="article-meta">
                            <span class="badge badge-gold"><?php echo htmlspecialchars(get_role_label($post['category'] ?? 'General')); ?></span>
                            <span>By <strong><?php echo htmlspecialchars($post['author_name'] ?? 'System'); ?></strong></span>
                            <span>Published on <?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                        </div>
                    </header>
                    
                    <div class="article-content">
                        <?php echo parse_markdown($post['content']); ?>
                    </div>
                    
                    <div style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid var(--color-border); text-align: center;">
                        <a href="/" class="btn btn-secondary">&larr; Back to Home</a>
                    </div>
                </div>
            </article>
        <?php else: ?>
            <!-- 404 Page Not Found -->
            <div class="container" style="text-align: center; padding: 8rem 2rem;">
                <h2>404: Page Not Found</h2>
                <p class="text-muted" style="margin-bottom: 2rem;">The story or page you are looking for does not exist or has been unpublished.</p>
                <a href="/" class="btn btn-primary">Return to Homepage</a>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container footer-inner">
            <div>
                <a href="/" style="font-family: var(--font-serif); font-size: 1.25rem; font-weight: 700; color: #fff;">FablePress.io</a>
                <p class="text-muted" style="font-size: 0.85rem; margin-top: 0.5rem; margin-bottom: 0;">© 2026 FablePress.io. All rights reserved.</p>
            </div>
            <div style="display: flex; gap: 2rem;">
                <a href="/">Features</a>
                <a href="/">Themes</a>
                <a href="/">Pricing</a>
                <a href="/admin/login/">Admin Login</a>
            </div>
        </div>
    </footer>

</body>
</html>
