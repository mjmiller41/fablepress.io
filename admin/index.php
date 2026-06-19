<?php
$page_active = 'dashboard';
require_once __DIR__ . '/header.php';

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
?>

<div style="margin-bottom: 2.5rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h2 style="margin-bottom: 0.25rem;">Welcome back, <?php echo htmlspecialchars($current_user['username']); ?>!</h2>
        <p class="text-muted" style="margin-bottom: 0; font-family: var(--font-sans); font-size: 0.9rem;">
            Today is <?php echo date('F j, Y'); ?> &bull; Role: <strong><?php echo get_role_label($current_user['role']); ?></strong>
        </p>
    </div>
    <a href="story-edit.php" class="btn btn-primary">
        <i class="fa-solid fa-pen-nib"></i> Write New Story
    </a>
</div>

<!-- Quick Statistics (Screen A SPEC) -->
<div class="stats-grid">
    <div class="stat-box">
        <div class="stat-num"><?php echo $total_stories; ?></div>
        <div class="stat-label">Total Stories</div>
    </div>
    <div class="stat-box">
        <div class="stat-num"><?php echo $published_stories; ?></div>
        <div class="stat-label">Published</div>
    </div>
    <div class="stat-box">
        <div class="stat-num"><?php echo $draft_stories; ?></div>
        <div class="stat-label">Drafts</div>
    </div>
    <div class="stat-box">
        <div class="stat-num"><?php echo $total_users; ?></div>
        <div class="stat-label">Total Authors</div>
    </div>
</div>

<div class="admin-grid">
    <!-- Recent Stories List (Screen A SPEC) -->
    <div class="admin-card" style="grid-column: span 2;">
        <div class="card-header">
            <h3>Recent Stories</h3>
            <a href="stories.php" class="ui-text" style="color: var(--color-forest);">View All &rarr;</a>
        </div>
        
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_stories)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--color-muted); padding: 2rem;">No stories found. Click "Write New Story" to get started!</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_stories as $story): ?>
                            <tr>
                                <td style="font-family: var(--font-serif); font-weight: 600;">
                                    <a href="story-edit.php?id=<?php echo $story['id']; ?>" style="color: var(--color-ink);">
                                        <?php echo htmlspecialchars($story['title']); ?>
                                    </a>
                                    <div style="font-size: 0.75rem; font-family: var(--font-sans); color: var(--color-muted); font-weight: normal; margin-top: 0.25rem;">
                                        Slug: <?php echo htmlspecialchars($story['slug']); ?> | Type: <?php echo ucfirst($story['type']); ?>
                                    </div>
                                </td>
                                <td style="font-family: var(--font-sans);"><?php echo htmlspecialchars($story['author_name'] ?? 'System'); ?></td>
                                <td style="font-family: var(--font-sans); font-size: 0.8rem; color: var(--color-muted);">
                                    <?php echo date('M j, Y', strtotime($story['created_at'])); ?>
                                </td>
                                <td>
                                    <?php if ($story['status'] === 'published'): ?>
                                        <span class="badge badge-forest">Published</span>
                                    <?php else: ?>
                                        <span class="badge">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <a href="story-edit.php?id=<?php echo $story['id']; ?>" class="btn btn-secondary btn-sm" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Publishing Overview Widget (Screen A SPEC: Line chart mockup) -->
    <div class="admin-card">
        <div class="card-header">
            <h3>Publishing Overview</h3>
        </div>
        
        <p class="text-muted" style="font-size: 0.85rem; font-family: var(--font-sans); margin-bottom: 1.5rem;">
            Submissions (Gold) vs. Publications (Forest Green) over the last 6 months.
        </p>
        
        <!-- SVG mockup of a premium line chart -->
        <div style="text-align: center; background-color: var(--color-card); padding: 1.5rem 1rem; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
            <svg viewBox="0 0 300 150" style="width: 100%; height: auto; display: block;">
                <!-- Grid lines -->
                <line x1="20" y1="20" x2="280" y2="20" stroke="#E6DFD5" stroke-dasharray="3,3" />
                <line x1="20" y1="60" x2="280" y2="60" stroke="#E6DFD5" stroke-dasharray="3,3" />
                <line x1="20" y1="100" x2="280" y2="100" stroke="#E6DFD5" stroke-dasharray="3,3" />
                <line x1="20" y1="130" x2="280" y2="130" stroke="#C6BFB5" stroke-width="1.5" />
                
                <!-- Axis labels (Months) -->
                <text x="20" y="145" font-family="Inter" font-size="8" fill="#606870" text-anchor="middle">Jan</text>
                <text x="72" y="145" font-family="Inter" font-size="8" fill="#606870" text-anchor="middle">Feb</text>
                <text x="124" y="145" font-family="Inter" font-size="8" fill="#606870" text-anchor="middle">Mar</text>
                <text x="176" y="145" font-family="Inter" font-size="8" fill="#606870" text-anchor="middle">Apr</text>
                <text x="228" y="145" font-family="Inter" font-size="8" fill="#606870" text-anchor="middle">May</text>
                <text x="280" y="145" font-family="Inter" font-size="8" fill="#606870" text-anchor="middle">Jun</text>
                
                <!-- Submissions Line (Gold: #C99B3B) -->
                <path d="M 20 120 Q 72 80 124 90 T 228 40 T 280 30" fill="none" stroke="#C99B3B" stroke-width="2.5" stroke-linecap="round" />
                <!-- Publications Line (Forest Green: #1C4E35) -->
                <path d="M 20 130 Q 72 110 124 100 T 228 60 T 280 45" fill="none" stroke="#1C4E35" stroke-width="2.5" stroke-linecap="round" />
                
                <!-- Legend dots -->
                <circle cx="60" cy="10" r="3" fill="#C99B3B" />
                <text x="68" y="13" font-family="Inter" font-size="8" fill="#606870">Submissions</text>
                
                <circle cx="160" cy="10" r="3" fill="#1C4E35" />
                <text x="168" y="13" font-family="Inter" font-size="8" fill="#606870">Publications</text>
            </svg>
        </div>
        
        <div style="display: flex; justify-content: space-between; margin-top: 1.5rem; font-family: var(--font-sans); font-size: 0.8rem; color: var(--color-muted);">
            <span>Active Submissions: <strong>14</strong></span>
            <span>Target Velocity: <strong>Good</strong></span>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/footer.php';
?>
