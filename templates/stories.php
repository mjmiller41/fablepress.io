<?php
$page_title = 'Stories';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/header.php';

$db = get_db_connection();
// Fetch all stories
try {
    $stories_stmt = $db->query("SELECT p.*, u.username as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.type = 'story' AND p.status = 'published' ORDER BY p.created_at DESC");
    $stories = $stories_stmt->fetchAll();
} catch (Exception $e) {
    $stories = [];
}
?>

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
                                <h2 style="font-size: 1.85rem; margin-bottom: 1rem; font-family: var(--font-serif);"><a href="/stories/<?php echo htmlspecialchars($story['slug']); ?>/" style="color: var(--color-ink);"><?php echo htmlspecialchars($story['title']); ?></a></h2>
                                <p style="font-size: 1.05rem; line-height: 1.6; color: var(--color-muted); margin-bottom: 1.5rem; font-family: var(--font-serif);"><?php echo htmlspecialchars($excerpt); ?></p>
                                <a href="/stories/<?php echo htmlspecialchars($story['slug']); ?>/" class="btn btn-secondary" style="display: inline-block;">Read Story &rarr;</a>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
