<?php
$db = get_db_connection();

// Fetch the requested story or page
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : null;
$post = null;

if ($slug) {
    $post_stmt = $db->prepare("SELECT p.*, u.username as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.slug = ? AND p.status = 'published'");
    $post_stmt->execute([$slug]);
    $post = $post_stmt->fetch();
}

$page_title = $post ? $post['title'] : 'Page Not Found';

require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/header.php';
?>

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

<?php require __DIR__ . '/../includes/footer.php'; ?>
