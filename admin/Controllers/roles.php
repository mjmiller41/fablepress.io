<?php
$page_active = 'roles';
require_once __DIR__ . '/../Views/header.php';

// Authorization: Only Developers can modify permissions
$db = get_db_connection();

$message = '';
$message_type = 'success';

// Handle permissions updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role_permissions'])) {
    // Only developers can save changes
    if ($current_user['role'] !== 'developer') {
        $message = 'Unauthorized: Only Developers can modify role permissions.';
        $message_type = 'danger';
    } else {
        $role_to_update = $_POST['role_to_update'];
        
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
                $value = isset($_POST[$key]) ? 1 : 0;
                
                // Cross-DB safe delete & insert
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

// Helper to check permission checked state in form
function is_checked($perms, $role, $key, $default) {
    if (isset($perms[$role][$key])) {
        return $perms[$role][$key] === 1 ? 'checked' : '';
    }
    return $default === 1 ? 'checked' : '';
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;">
    <div>
        <h2 style="margin-bottom: 0.25rem;">User Roles & Permissions</h2>
        <p class="text-muted" style="margin-bottom: 0; font-family: var(--font-sans); font-size: 0.9rem;">
            Configure system-wide authorization policies using Role-Based Access Control (RBAC).
        </p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Screen C Three Roles Permissions Grid -->
<div class="role-cards-grid">

    <!-- DEVELOPER ROLE CARD (High Access) -->
    <div class="role-card developer-card">
        <h3 class="role-title">Developer</h3>
        <span class="badge badge-gold role-badge">High Access</span>
        
        <form action="/admin/roles/" method="POST" style="display: flex; flex-direction: column; flex-grow: 1;">
            <input type="hidden" name="role_to_update" value="developer">
            
            <ul class="role-permissions-list">
                <li>
                    <span class="permission-label">Edit Theme</span>
                    <label class="switch">
                        <input type="checkbox" name="edit_theme" <?php echo is_checked($perms, 'developer', 'edit_theme', 1); ?>>
                        <span class="slider"></span>
                    </label>
                </li>
                <li>
                    <span class="permission-label">Manage Plugins</span>
                    <label class="switch">
                        <input type="checkbox" name="manage_plugins" <?php echo is_checked($perms, 'developer', 'manage_plugins', 1); ?>>
                        <span class="slider"></span>
                    </label>
                </li>
                <li>
                    <span class="permission-label">API Access</span>
                    <label class="switch">
                        <input type="checkbox" name="api_access" <?php echo is_checked($perms, 'developer', 'api_access', 1); ?>>
                        <span class="slider"></span>
                    </label>
                </li>
                <li>
                    <span class="permission-label">Edit CSS/HTML</span>
                    <label class="switch">
                        <input type="checkbox" name="edit_css_html" <?php echo is_checked($perms, 'developer', 'edit_css_html', 1); ?>>
                        <span class="slider"></span>
                    </label>
                </li>
                <li>
                    <span class="permission-label">View Logs</span>
                    <label class="switch">
                        <input type="checkbox" name="view_logs" <?php echo is_checked($perms, 'developer', 'view_logs', 1); ?>>
                        <span class="slider"></span>
                    </label>
                </li>
                <li>
                    <span class="permission-label">Deploy Changes</span>
                    <label class="switch">
                        <input type="checkbox" name="deploy_changes" <?php echo is_checked($perms, 'developer', 'deploy_changes', 1); ?>>
                        <span class="slider"></span>
                    </label>
                </li>
            </ul>
            
            <div class="role-actions">
                <button type="submit" name="update_role_permissions" class="btn btn-secondary" style="border-color: var(--color-forest); color: var(--color-forest);">
                    Save Settings
                </button>
                <a href="/admin/users/?role=developer" class="btn btn-accent" style="color: #fff;">
                    View Users
                </a>
            </div>
        </form>
    </div>

    <!-- CONTENT MANAGER ROLE CARD (Medium Access) -->
    <div class="role-card manager-card">
        <h3 class="role-title">Content Manager</h3>
        <span class="badge badge-gold role-badge" style="background-color: var(--color-forest);">Medium Access</span>
        
        <form action="/admin/roles/" method="POST" style="display: flex; flex-direction: column; flex-grow: 1;">
            <input type="hidden" name="role_to_update" value="content_manager">
            
            <ul class="role-permissions-list">
                <li>
                    <span class="permission-label">Publish Posts</span>
                    <label class="switch">
                        <input type="checkbox" name="publish_posts" <?php echo is_checked($perms, 'content_manager', 'publish_posts', 1); ?>>
                        <span class="slider"></span>
                    </label>
                </li>
                <li>
                    <span class="permission-label">Edit Pages</span>
                    <label class="switch">
                        <input type="checkbox" name="edit_pages" <?php echo is_checked($perms, 'content_manager', 'edit_pages', 1); ?>>
                        <span class="slider"></span>
                    </label>
                </li>
                <li>
                    <span class="permission-label">Manage Categories</span>
                    <label class="switch">
                        <input type="checkbox" name="manage_categories" <?php echo is_checked($perms, 'content_manager', 'manage_categories', 1); ?>>
                        <span class="slider"></span>
                    </label>
                </li>
                <li>
                    <span class="permission-label">Moderate Comments</span>
                    <label class="switch">
                        <input type="checkbox" name="moderate_comments" <?php echo is_checked($perms, 'content_manager', 'moderate_comments', 1); ?>>
                        <span class="slider"></span>
                    </label>
                </li>
                <li>
                    <span class="permission-label">Schedule Content</span>
                    <label class="switch">
                        <input type="checkbox" name="schedule_content" <?php echo is_checked($perms, 'content_manager', 'schedule_content', 1); ?>>
                        <span class="slider"></span>
                    </label>
                </li>
                <li>
                    <span class="permission-label">Create Snippets</span>
                    <label class="switch">
                        <input type="checkbox" name="create_snippets" <?php echo is_checked($perms, 'content_manager', 'create_snippets', 1); ?>>
                        <span class="slider"></span>
                    </label>
                </li>
            </ul>
            
            <div class="role-actions">
                <button type="submit" name="update_role_permissions" class="btn btn-secondary" style="border-color: var(--color-forest); color: var(--color-forest);">
                    Save Settings
                </button>
                <a href="/admin/users/?role=content_manager" class="btn btn-accent" style="color: #fff;">
                    View Users
                </a>
            </div>
        </form>
    </div>

    <!-- CONTRIBUTOR ROLE CARD (Limited Access) -->
    <div class="role-card contributor-card">
        <h3 class="role-title">Contributor</h3>
        <span class="badge badge-gold role-badge" style="background-color: var(--color-muted);">Limited Access</span>
        
        <form action="/admin/roles/" method="POST" style="display: flex; flex-direction: column; flex-grow: 1;">
            <input type="hidden" name="role_to_update" value="contributor">
            
            <ul class="role-permissions-list">
                <li>
                    <span class="permission-label">Write Drafts</span>
                    <label class="switch">
                        <input type="checkbox" name="write_drafts" <?php echo is_checked($perms, 'contributor', 'write_drafts', 1); ?>>
                        <span class="slider"></span>
                    </label>
                </li>
                <li>
                    <span class="permission-label">Edit Own Posts</span>
                    <label class="switch">
                        <input type="checkbox" name="edit_own_posts" <?php echo is_checked($perms, 'contributor', 'edit_own_posts', 1); ?>>
                        <span class="slider"></span>
                    </label>
                </li>
                <li>
                    <span class="permission-label">View Analytics</span>
                    <label class="switch">
                        <input type="checkbox" name="view_analytics" <?php echo is_checked($perms, 'contributor', 'view_analytics', 1); ?>>
                        <span class="slider"></span>
                    </label>
                </li>
            </ul>
            
            <div class="role-actions">
                <button type="submit" name="update_role_permissions" class="btn btn-secondary" style="border-color: var(--color-forest); color: var(--color-forest);">
                    Save Settings
                </button>
                <a href="/admin/users/?role=contributor" class="btn btn-accent" style="color: #fff;">
                    View Users
                </a>
            </div>
        </form>
    </div>

</div>

<?php
require_once __DIR__ . '/../Views/footer.php';
?>
