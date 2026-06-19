<?php
$db = get_db_connection();

// Fetch navigation items
try {
    $nav_stmt = $db->query("SELECT * FROM navigation ORDER BY position ASC");
    $nav_items = $nav_stmt->fetchAll();
} catch (Exception $e) {
    $nav_items = [];
}

// Check if a specific story or page is requested
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : null;
$post = null;

if ($slug) {
    $post_stmt = $db->prepare("SELECT p.*, u.username as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.slug = ? AND p.status = 'published'");
    $post_stmt->execute([$slug]);
    $post = $post_stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post ? htmlspecialchars($post['title']) . ' - FablePress.io' : 'FablePress.io - Your stories deserve a beautiful home'; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

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
                            // Determine if this item is active
                            $is_active = false;
                            $clean_url = trim($item['url'], '/');
                            if (!$slug && $clean_url === '') {
                                $is_active = true;
                            } elseif ($slug && $clean_url === $slug) {
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
            
            <?php if ($slug && !$post): ?>
                <!-- 404 Page Not Found -->
                <div class="container" style="text-align: center; padding: 8rem 2rem;">
                    <h2>404: Page Not Found</h2>
                    <p class="text-muted" style="margin-bottom: 2rem;">The story or page you are looking for does not exist or has been unpublished.</p>
                    <a href="/" class="btn btn-primary">Return to Homepage</a>
                </div>
            <?php else: ?>
                <!-- Homepage Layout -->
                
                <!-- Hero Section -->
                <section class="hero">
                    <div class="container">
                        <h1>Thoughts, stories, and ideas.</h1>
                        <p class="subhead">A publication exploring typography, minimal design, and web technology. Written and compiled with clean, distraction-free simplicity.</p>
                        <div class="hero-ctas">
                            <a href="/stories/" class="btn btn-primary">Read the Stories</a>
                            <a href="/about-us/" class="btn btn-secondary">About FablePress</a>
                        </div>
                    </div>
                </section>
                
                <!-- Value Props / Features -->
                <section class="features-section">
                    <div class="container">
                        <div class="section-header">
                            <h2>Everything you need to publish. Nothing you don't.</h2>
                            <p>We stripped away the noise so you can focus on what matters most—your words.</p>
                        </div>
                        <div class="features-grid">
                            <div class="feature-card">
                                <div class="feature-icon">✒️</div>
                                <h3>The Zen Editor</h3>
                                <p>Support for Markdown and intuitive rich text. Write in an interface that feels like a clean sheet of paper. Auto-saves every keystroke.</p>
                            </div>
                            <div class="feature-card">
                                <div class="feature-icon">⚡</div>
                                <h3>Blazing Fast by Design</h3>
                                <p>No heavy databases or redundant plugins. FablePress pages load instantly on any device, giving your readers a premium experience and boosting SEO.</p>
                            </div>
                            <div class="feature-card">
                                <div class="feature-icon">🌐</div>
                                <h3>Your Brand, Your Domain</h3>
                                <p>Connect your custom domain in one click. Customize fonts, spacing, and colors to match your brand identity. Zero "Powered by" watermarks.</p>
                            </div>
                            <div class="feature-card">
                                <div class="feature-icon">🛡️</div>
                                <h3>Zero Maintenance</h3>
                                <p>No security patches to install. No databases to backup. We handle security, hosting, and image optimization so your site stays live 24/7.</p>
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- Stories List Section -->
                <section id="stories" class="stories-section">
                    <div class="container">
                        <div class="section-header">
                            <h2>Recent Stories</h2>
                            <p>Explore articles, columns, and notes published using FablePress.</p>
                        </div>
                        
                        <div class="stories-list">
                            <?php
                            try {
                                $stories_stmt = $db->query("SELECT p.*, u.username as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.type = 'story' AND p.status = 'published' ORDER BY p.created_at DESC");
                                $stories = $stories_stmt->fetchAll();
                                
                                if (empty($stories)):
                            ?>
                                <p style="text-align: center; color: var(--color-muted);">No stories have been published yet. Log in to write your first story!</p>
                            <?php
                                else:
                                    foreach ($stories as $story):
                                        // Simple excerpt builder
                                        $excerpt = strip_tags($story['content']);
                                        if (strlen($excerpt) > 180) {
                                            $excerpt = substr($excerpt, 0, 175) . '...';
                                        }
                            ?>
                                <article class="story-summary-card">
                                    <div class="story-meta">
                                        <span class="badge badge-forest"><?php echo htmlspecialchars($story['category'] ?? 'General'); ?></span>
                                        <span>By <?php echo htmlspecialchars($story['author_name'] ?? 'System'); ?></span>
                                        <span>&bull;</span>
                                        <span><?php echo date('M j, Y', strtotime($story['created_at'])); ?></span>
                                    </div>
                                    <h3><a href="/<?php echo htmlspecialchars($story['slug']); ?>/"><?php echo htmlspecialchars($story['title']); ?></a></h3>
                                    <div class="story-excerpt">
                                        <?php echo htmlspecialchars($excerpt); ?>
                                    </div>
                                    <a href="/<?php echo htmlspecialchars($story['slug']); ?>/" style="font-family: var(--font-sans); font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        Read Story &rarr;
                                    </a>
                                </article>
                            <?php
                                    endforeach;
                                endif;
                            } catch (Exception $e) {
                                echo '<p style="color: red;">Failed to load stories: ' . htmlspecialchars($e->getMessage()) . '</p>';
                            }
                            ?>
                        </div>
                    </div>
                </section>
                
                <!-- Pricing CTA Section -->
                <section style="background-color: var(--color-card); padding: 5rem 0; text-align: center; border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border);">
                    <div class="container">
                        <h2 style="font-size: 2.25rem; margin-bottom: 1rem;">One simple plan.</h2>
                        <p class="text-muted" style="margin-bottom: 3rem; font-family: var(--font-sans);">No tiers, no hidden fees. Just honest software.</p>
                        
                        <div style="background-color: var(--color-paper); border: 1px solid var(--color-border); border-radius: var(--radius-lg); max-width: 450px; margin: 0 auto; padding: 3rem 2rem; box-shadow: var(--shadow-md);">
                            <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">FablePress Pro</h3>
                            <div style="font-size: 2.5rem; font-weight: 700; margin: 1.5rem 0; font-family: var(--font-sans); color: var(--color-forest);">
                                $9<span style="font-size: 1.125rem; font-weight: 500; color: var(--color-muted);"> / month</span>
                            </div>
                            <p style="font-family: var(--font-sans); font-size: 0.875rem; color: var(--color-muted); margin-bottom: 2rem;">Billed annually ($108/year)</p>
                            <ul style="list-style: none; text-align: left; max-width: 280px; margin: 0 auto 2.5rem auto; font-family: var(--font-sans); font-size: 0.95rem; line-height: 2;">
                                <li>✓ Unlimited Posts & Pages</li>
                                <li>✓ Custom Domain Setup</li>
                                <li>✓ Premium Literary Themes</li>
                                <li>✓ Blazing-Fast Global CDN</li>
                                <li>✓ Email Newsletter Integration</li>
                                <li>✓ No Transaction Fees or Ads</li>
                            </ul>
                            <a href="/admin/login/" class="btn btn-primary" style="display: block; width: 100%;">Start Your 14-Day Free Trial</a>
                        </div>
                    </div>
                </section>
                
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container footer-inner">
            <div>
                <a href="/" style="font-family: var(--font-serif); font-size: 1.25rem; font-weight: 700; color: #fff;">FablePress.io</a>
                <p class="text-muted" style="font-size: 0.85rem; margin-top: 0.5rem; margin-bottom: 0;">© 2026 FablePress.io. All rights reserved.</p>
            </div>
            <div style="display: flex; gap: 2rem;">
                <a href="/">Features</a>
                <a href="/">Themes</a>
                <a href="/">Pricing</a>
                <a href="/admin/login/">Admin Login</a>
            </div>
        </div>
    </footer>

</body>
</html>
