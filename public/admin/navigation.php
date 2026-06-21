<?php
$page_active = 'navigation';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="margin-bottom: 0.25rem;">Navigation Menu Editor</h2>
        <p class="text-muted" style="margin-bottom: 0; font-family: var(--font-sans); font-size: 0.9rem;">
            Customize the header links displayed on the public site.
        </p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="nav-editor-layout">
    <!-- Add / Edit Link Form (Left column) -->
    <div class="admin-card">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.15rem;">
            <?php echo $edit_item ? 'Edit Link' : 'Add New Link'; ?>
        </h3>
        
        <form action="/admin/navigation/" method="POST">
            <?php if ($edit_item): ?>
                <input type="hidden" name="form_id" value="<?php echo $edit_item['id']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="title">Link Title</label>
                <input type="text" id="title" name="title" class="form-control" placeholder="e.g. Portfolio" value="<?php echo htmlspecialchars($edit_item['title'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="url">URL Path</label>
                <input type="text" id="url" name="url" class="form-control" placeholder="e.g. /about-us/" value="<?php echo htmlspecialchars($edit_item['url'] ?? ''); ?>" required>
                <span style="font-size: 0.7rem; color: var(--color-muted);">Use relative clean paths like <code>/my-page/</code> or full URLs like <code>https://google.com</code>.</span>
            </div>
            
            <div class="form-group">
                <label for="position">Order Position</label>
                <input type="number" id="position" name="position" class="form-control" placeholder="e.g. 1" value="<?php echo htmlspecialchars($edit_item['position'] ?? '0'); ?>">
                <span style="font-size: 0.7rem; color: var(--color-muted);">Lower numbers will display first in the navigation bar.</span>
            </div>
            
            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="target">Open In</label>
                <select id="target" name="target" class="form-control">
                    <option value="_self" <?php echo ($edit_item && $edit_item['target'] === '_self') ? 'selected' : ''; ?>>Same Window / Tab</option>
                    <option value="_blank" <?php echo ($edit_item && $edit_item['target'] === '_blank') ? 'selected' : ''; ?>>New Window / Tab</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" name="save_nav" class="btn btn-primary" style="flex-grow: 1;">
                    <i class="fa-solid fa-save"></i> Save Link
                </button>
                <?php if ($edit_item): ?>
                    <a href="/admin/navigation/" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Link Structure (Right column) -->
    <div class="admin-card">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.15rem;">Navigation Menu Order</h3>
        
        <?php if (empty($nav_items)): ?>
            <div style="text-align: center; padding: 4rem 2rem; color: var(--color-muted);">
                <p>No navigation links created yet.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column;">
                <?php foreach ($nav_items as $item): ?>
                    <div class="nav-item-row">
                        <div style="display: flex; align-items: center;">
                            <span class="badge badge-gold" style="margin-right: 1rem; width: 24px; text-align: center; padding: 0.2rem 0;"><?php echo (int)$item['position']; ?></span>
                            <div class="nav-item-details">
                                <div class="nav-item-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                <div class="nav-item-url">
                                    <code><?php echo htmlspecialchars($item['url']); ?></code> 
                                    <?php if ($item['target'] === '_blank'): ?>
                                        <span style="font-size: 0.65rem; color: var(--color-gold);">(New tab)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 0.25rem;">
                            <a href="/admin/navigation/?action=move_up&id=<?php echo $item['id']; ?>" class="btn btn-secondary btn-sm" title="Move Up">
                                <i class="fa-solid fa-arrow-up"></i>
                            </a>
                            <a href="/admin/navigation/?action=move_down&id=<?php echo $item['id']; ?>" class="btn btn-secondary btn-sm" title="Move Down">
                                <i class="fa-solid fa-arrow-down"></i>
                            </a>
                            <a href="/admin/navigation/?edit_id=<?php echo $item['id']; ?>" class="btn btn-secondary btn-sm" title="Edit link settings">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="/admin/navigation/?action=delete&id=<?php echo $item['id']; ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('Are you sure you want to remove this link?');"
                               title="Remove link">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/admin-footer.php';
?>
