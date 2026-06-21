<?php
$page_active = 'users';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="margin-bottom: 0.25rem;">User Accounts Manager</h2>
        <p class="text-muted" style="margin-bottom: 0; font-family: var(--font-sans); font-size: 0.9rem;">
            Create, update, and manage team members and their roles.
        </p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="nav-editor-layout">
    <!-- Add / Edit User Form (Left column) -->
    <div class="admin-card">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.15rem;">
            <?php echo $edit_user ? 'Edit User Account' : 'Create User Account'; ?>
        </h3>
        
        <form action="/admin/users/" method="POST">
            <?php if ($edit_user): ?>
                <input type="hidden" name="form_id" value="<?php echo $edit_user['id']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="e.g. janesmith" value="<?php echo htmlspecialchars($edit_user['username'] ?? ''); ?>" required <?php echo $edit_user ? 'readonly' : ''; ?>>
                <?php if ($edit_user): ?>
                    <span style="font-size: 0.7rem; color: var(--color-muted);">Username cannot be changed.</span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="e.g. jane@company.com" value="<?php echo htmlspecialchars($edit_user['email'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="role">System Role</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="developer" <?php echo ($edit_user && $edit_user['role'] === 'developer') ? 'selected' : ''; ?>>Developer (Full Access)</option>
                    <option value="content_manager" <?php echo ($edit_user && $edit_user['role'] === 'content_manager') ? 'selected' : ''; ?>>Content Manager (Medium Access)</option>
                    <option value="contributor" <?php echo ($edit_user && $edit_user['role'] === 'contributor') ? 'selected' : ''; ?>>Contributor (Limited Access)</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" <?php echo $edit_user ? '' : 'required'; ?>>
                <?php if ($edit_user): ?>
                    <span style="font-size: 0.7rem; color: var(--color-muted);">Leave empty to keep current password.</span>
                <?php endif; ?>
            </div>
            
            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary" style="flex-grow: 1;">
                    <i class="fa-solid fa-user-plus"></i> <?php echo $edit_user ? 'Save Changes' : 'Create Account'; ?>
                </button>
                <?php if ($edit_user): ?>
                    <a href="/admin/users/" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- User List (Right column) -->
    <div class="admin-card">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.15rem;">Registered Users</h3>
        
        <?php if (empty($users_list)): ?>
            <div style="text-align: center; padding: 4rem 2rem; color: var(--color-muted);">
                <p>No user accounts found.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column;">
                <?php foreach ($users_list as $user): ?>
                    <div class="nav-item-row" style="padding: 1rem 1.25rem; align-items: flex-start;">
                        <div style="display: flex; flex-direction: column; flex-grow: 1; min-width: 0;">
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.25rem; flex-wrap: wrap;">
                                <strong style="font-size: 0.95rem; color: var(--color-ink);"><?php echo htmlspecialchars($user['username']); ?></strong>
                                <?php 
                                    $role_badge_class = 'badge-gold';
                                    if ($user['role'] === 'content_manager') {
                                        $role_badge_class = 'badge-forest';
                                    } elseif ($user['role'] === 'contributor') {
                                        $role_badge_class = '';
                                    }
                                ?>
                                <span class="badge <?php echo $role_badge_class; ?>" style="font-size: 0.65rem; padding: 0.1rem 0.5rem;">
                                    <?php echo get_role_label($user['role']); ?>
                                </span>
                                <?php if ((int)$user['id'] === (int)$current_user['id']): ?>
                                    <span class="badge" style="font-size: 0.65rem; background-color: var(--color-ink); color: #fff; padding: 0.1rem 0.5rem;">You</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-family: var(--font-sans); font-size: 0.8rem; color: var(--color-muted); word-break: break-all; margin-bottom: 0.25rem;">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </div>
                            <div style="font-family: var(--font-sans); font-size: 0.7rem; color: var(--color-muted);">
                                Joined: <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 0.25rem; align-self: center; flex-shrink: 0; margin-left: 1rem;">
                            <a href="/admin/users/?edit_id=<?php echo $user['id']; ?>" class="btn btn-secondary btn-sm" title="Edit user profile">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            
                            <?php if ((int)$user['id'] !== (int)$current_user['id']): ?>
                                <form action="/admin/users/" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete user account: <?php echo htmlspecialchars($user['username']); ?>?');" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete user">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-danger btn-sm" disabled style="opacity: 0.4; cursor: not-allowed;" title="You cannot delete yourself">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            <?php endif; ?>
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
