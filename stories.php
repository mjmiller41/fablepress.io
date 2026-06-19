<?php
require_once __DIR__ . '/config.php';

$db = get_db_connection();

// Fetch navigation items
try {
    $nav_stmt = $db->query("SELECT * FROM navigation ORDER BY position ASC");
    $nav_items = $nav_stmt->fetchAll();
} catch (Exception $e) {
    $nav_items = [];
}

// Fetch all stories
try {
    $stories_stmt = $db->query("SELECT p.*, u.username as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.type = 'story' AND p.status = 'published' ORDER BY p.created_at DESC");
    $stories = $stories_stmt->fetchAll();
} catch (Exception $e) {
    $stories = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stories - FablePress.io</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Header & Navigation -->
    <header class="site-header">
        <div class="container header-inner">
            <a href="index.php" class="logo">
                <div class="logo-icon">F</div>
                FablePress.io
            </a>
            <nav class="main-nav">
                <ul>
                    <?php foreach ($nav_items as $item): ?>
                        <?php 
                            // Hide admin pages from the header navigation
                            if (strpos($item['url'], 'admin/') !== false) {
                                continue;
                            }
                            // Determine if this item is active
                            $is_active = ($item['url'] === 'stories.php');
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
        <section class="stories-hero" style="background-color: var(--color-card); padding: 5rem 0; border-bottom: 1px solid var(--color-border); text-align: center;">
            <div class="container">
                <h1 style="font-size: 3rem; margin-bottom: 1rem;">The Library</h1>
                <p class="text-muted" style="max-width: 600px; margin: 0 auto; font-family: var(--font-sans);">A collection of essays, tutorials, and field notes exploring web architecture, typographical craft, and clean development paradigms.</p>
            </div>
        </section>

        <section class="stories-section" style="padding: 5rem 0;">
            <div class="container">
                <div class="stories-list" style="display: grid; gap: 3rem; max-width: 800px; margin: 0 auto;">
                    <?php if (empty($stories)): ?>
                        <p style="text-align: center; color: var(--color-muted);">No stories have been published yet. Log in to the Admin Panel to write your first story!</p>
                    <?php else: ?>
                        <?php foreach ($stories as $story): 
                            // Simple excerpt builder
                            $excerpt = strip_tags($story['content']);
                            if (strlen($excerpt) > 280) {
                                $excerpt = substr($excerpt, 0, 275) . '...';
                            }
                        ?>
                            <article class="story-summary-card" style="background-color: var(--color-card); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 2.5rem; transition: transform 0.2s ease, box-shadow 0.2s ease; box-shadow: var(--shadow-sm);">
                                <div class="story-meta" style="font-family: var(--font-sans); font-size: 0.8rem; color: var(--color-muted); margin-bottom: 1rem; display: flex; gap: 1rem; align-items: center;">
                                    <span class="badge badge-forest" style="background-color: var(--color-forest); color: #fff; padding: 0.2rem 0.6rem; border-radius: var(--radius-sm); font-weight: 600;"><?php echo htmlspecialchars($story['category'] ?? 'General'); ?></span>
                                    <span>By <strong><?php echo htmlspecialchars($story['author_name'] ?? 'System'); ?></strong></span>
                                    <span>&bull;</span>
                                    <span><?php echo date('F j, Y', strtotime($story['created_at'])); ?></span>
                                </div>
                                <h2 style="font-size: 1.85rem; margin-bottom: 1rem; font-family: var(--font-serif);"><a href="index.php?slug=<?php echo htmlspecialchars($story['slug']); ?>" style="color: var(--color-ink);"><?php echo htmlspecialchars($story['title']); ?></a></h2>
                                <p style="font-size: 1.05rem; line-height: 1.6; color: var(--color-muted); margin-bottom: 1.5rem; font-family: var(--font-serif);"><?php echo htmlspecialchars($excerpt); ?></p>
                                <a href="index.php?slug=<?php echo htmlspecialchars($story['slug']); ?>" class="btn btn-secondary" style="display: inline-block;">Read Story &rarr;</a>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container footer-inner">
            <div>
                <a href="index.php" style="font-family: var(--font-serif); font-size: 1.25rem; font-weight: 700; color: #fff;">FablePress.io</a>
                <p class="text-muted" style="font-size: 0.85rem; margin-top: 0.5rem; margin-bottom: 0;">© 2026 FablePress.io. All rights reserved.</p>
            </div>
            <div style="display: flex; gap: 2rem;">
                <a href="index.php">Features</a>
                <a href="index.php">Themes</a>
                <a href="index.php">Pricing</a>
                <a href="admin/login.php">Admin Login</a>
            </div>
        </div>
    </footer>

</body>
</html>
