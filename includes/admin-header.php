<?php
// Check authentication
if (!is_logged_in()) {
    header('Location: /admin/login/');
    exit;
}

$current_user = get_logged_in_user();
if (!$current_user) {
    header('Location: /admin/logout/');
    exit;
}

// Ensure the page activity variable is set
if (!isset($page_active)) {
    $page_active = 'dashboard';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FablePress Admin Panel</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="/assets/vendor/font-awesome/css/all.min.css">
</head>
<body>

<div class="admin-container">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <a href="/" class="sidebar-logo" target="_blank">
            <div class="sidebar-logo-icon">F</div>
            FablePress.io
        </a>
        
        <nav>
            <a href="/admin/" class="<?php echo $page_active === 'dashboard' ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-line"></i> Dashboard
            </a>
            
            <a href="/admin/stories/" class="<?php echo $page_active === 'stories' ? 'active' : ''; ?>">
                <i class="fa-solid fa-book-open"></i> Stories & Pages
            </a>
            
            <a href="/admin/media/" class="<?php echo $page_active === 'media' ? 'active' : ''; ?>">
                <i class="fa-solid fa-photo-film"></i> Media Library
            </a>
            
            <a href="/admin/navigation/" class="<?php echo $page_active === 'navigation' ? 'active' : ''; ?>">
                <i class="fa-solid fa-bars"></i> Navigation
            </a>
            
            <a href="/admin/roles/" class="<?php echo $page_active === 'roles' ? 'active' : ''; ?>">
                <i class="fa-solid fa-user-shield"></i> Roles & Permissions
            </a>
            
            <?php if ($current_user['role'] === 'developer'): ?>
                <a href="/admin/users/" class="<?php echo $page_active === 'users' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-users"></i> User Accounts
                </a>
            <?php endif; ?>
        </nav>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <strong><?php echo htmlspecialchars($current_user['username']); ?></strong>
                <span class="badge badge-gold" style="font-size: 0.7rem;"><?php echo get_role_label($current_user['role']); ?></span>
            </div>
            <a href="/admin/logout/" class="btn btn-danger btn-sm" style="color: #fff; width: 100%;">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="admin-main">
        <header class="admin-header">
            <div class="header-title">
                <?php
                switch ($page_active) {
                    case 'dashboard': echo 'Dashboard Overview'; break;
                    case 'stories': echo 'Stories & Pages Manager'; break;
                    case 'media': echo 'Media Library'; break;
                    case 'navigation': echo 'Navigation Menu Editor'; break;
                    case 'roles': echo 'Roles & Permissions'; break;
                    case 'users': echo 'User Accounts'; break;
                    default: echo 'Control Panel';
                }
                ?>
            </div>
            <div class="header-actions">
                <a href="/" class="public-site-link" target="_blank">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Visit Public Site
                </a>
            </div>
        </header>
        
        <div class="admin-content">
