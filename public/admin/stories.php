<?php
$page_active = 'stories';
require_once __DIR__ . '/../../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="margin-bottom: 0.25rem;">Manage Stories & Pages</h2>
        <p class="text-muted" style="margin-bottom: 0; font-family: var(--font-sans); font-size: 0.9rem;">
            Create, update, and publish your content across the site.
        </p>
    </div>
    
    <div style="display: flex; gap: 1rem;">
        <a href="/admin/stories/?type=all" class="btn <?php echo $filter_type === 'all' ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">All</a>
        <a href="/admin/stories/?type=story" class="btn <?php echo $filter_type === 'story' ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">Stories</a>
        <a href="/admin/stories/?type=page" class="btn <?php echo $filter_type === 'page' ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">Pages</a>
        
        <a href="/admin/stories/edit/" class="btn btn-accent" style="margin-left: 1rem;">
            <i class="fa-solid fa-plus"></i> New Post
        </a>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="admin-card" style="padding: 0;">
    <div class="table-container" style="border: none;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Title</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th style="text-align: right; width: 15%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($posts)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--color-muted); padding: 3rem;">No entries found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($posts as $item): ?>
                        <tr>
                            <td>
                                <div style="font-family: var(--font-serif); font-size: 1.05rem; font-weight: 600; margin-bottom: 0.25rem;">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </div>
                                <div style="font-family: var(--font-sans); font-size: 0.75rem; color: var(--color-muted);">
                                    Slug: <code><?php echo htmlspecialchars($item['slug']); ?></code>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="font-size: 0.7rem; font-family: var(--font-sans);">
                                    <?php echo ucfirst($item['type']); ?>
                                </span>
                            </td>
                            <td style="font-family: var(--font-sans); font-size: 0.85rem;">
                                <?php echo htmlspecialchars($item['category'] ?? 'None'); ?>
                            </td>
                            <td style="font-family: var(--font-sans); font-size: 0.85rem;">
                                <?php echo htmlspecialchars($item['author_name'] ?? 'System'); ?>
                            </td>
                            <td style="font-family: var(--font-sans); font-size: 0.8rem; color: var(--color-muted);">
                                <?php echo date('M j, Y', strtotime($item['created_at'])); ?>
                            </td>
                            <td>
                                <?php if ($item['status'] === 'published'): ?>
                                    <span class="badge badge-forest">Published</span>
                                <?php else: ?>
                                    <span class="badge">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="/<?php echo htmlspecialchars($item['slug']); ?>/" target="_blank" class="btn btn-secondary btn-sm" title="View Public Post">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    
                                    <?php 
                                    // Check if user is allowed to edit this post
                                    $can_edit = true;
                                    if ($current_user['role'] === 'contributor' && $item['author_id'] != $current_user['id']) {
                                        $can_edit = false;
                                    }
                                    ?>
                                    
                                    <?php if ($can_edit): ?>
                                        <a href="/admin/stories/edit/<?php echo $item['id']; ?>/" class="btn btn-secondary btn-sm" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm" disabled style="opacity: 0.4; cursor: not-allowed;" title="Cannot Edit Others' Posts">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($current_user['role'] !== 'contributor'): ?>
                                        <a href="/admin/stories/?action=delete&id=<?php echo $item['id']; ?>&type=<?php echo $filter_type; ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Are you sure you want to delete this content?');"
                                           title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-danger btn-sm" disabled style="opacity: 0.4; cursor: not-allowed;" title="Delete Blocked for Contributors">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
