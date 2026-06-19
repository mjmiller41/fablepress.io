<?php
$db = get_db_connection();
try {
    $nav_stmt = $db->query("SELECT * FROM navigation ORDER BY position ASC");
    $nav_items = $nav_stmt->fetchAll();
} catch (Exception $e) {
    $nav_items = [];
}

// Determine active state based on request URI
$current_uri = isset($_SERVER['REQUEST_URI']) ? urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) : '/';
$clean_current = trim($current_uri, '/');
?>
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
                        // Determine if active
                        $clean_item_url = trim($item['url'], '/');
                        
                        $is_active = false;
                        if ($clean_item_url === '' && $clean_current === '') {
                            $is_active = true;
                        } elseif ($clean_item_url !== '' && strpos($clean_current, $clean_item_url) === 0) {
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
