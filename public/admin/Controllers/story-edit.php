<?php
use App\Application\Services\StaticGenerator;

// Check login
if (!is_logged_in()) {
    header('Location: /admin/login/');
    exit;
}

$current_user = get_logged_in_user();
$db = get_db_connection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$post = null;
$error = '';
$success = '';

// Load existing post if ID is provided
if ($id) {
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    
    if (!$post) {
        header('Location: /admin/stories/');
        exit;
    }
    
    // Authorization check: Contributor can only edit their own posts
    if ($current_user['role'] === 'contributor' && $post['author_id'] != $current_user['id']) {
        header('Location: /admin/stories/');
        exit;
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $content = $_POST['content'] ?? '';
    $type = trim($_POST['type'] ?? 'story');
    $category = trim($_POST['category'] ?? 'stories');
    $tags = trim($_POST['tags'] ?? '');
    $status = trim($_POST['status'] ?? 'draft');
    
    // Auto-generate slug if blank
    if ($slug === '' && $title !== '') {
        $slug = lowercase(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9\s]/', '', $title)));
        $slug = strtolower(preg_replace('/-+/', '-', $slug));
    } else {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9\-]/', '', str_replace(' ', '-', $slug)));
    }
    
    // Fallback slug if still empty
    if ($slug === '') {
        $slug = 'untitled-' . time();
    }
    
    // Permission checks:
    // 1. Contributor cannot publish. Force status to 'draft' if they are a contributor
    if ($current_user['role'] === 'contributor' || !has_permission($current_user['role'], 'publish_posts')) {
        $status = 'draft';
    }
    
    // 2. Contributor cannot create/edit pages. Force type to 'story' if they are a contributor
    if ($current_user['role'] === 'contributor' || !has_permission($current_user['role'], 'edit_pages')) {
        $type = 'story';
    }

    if ($title === '') {
        $error = 'Please enter a title.';
    } else {
        try {
            // Check if slug is already in use by ANOTHER post
            $slug_check = $db->prepare("SELECT id FROM posts WHERE slug = ? AND id != ?");
            $slug_check->execute([$slug, $id ? $id : 0]);
            if ($slug_check->fetch()) {
                // Append unique timestamp to make it unique
                $slug .= '-' . time();
            }
            
            if ($id) {
                // Update
                $stmt = $db->prepare("UPDATE posts SET title = ?, slug = ?, content = ?, type = ?, category = ?, tags = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$title, $slug, $content, $type, $category, $tags, $status, $id]);
                $success = 'Post saved successfully.';
                
                // Regenerate static site
                StaticGenerator::generateAll();
                
                // Reload post data
                $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
                $stmt->execute([$id]);
                $post = $stmt->fetch();
            } else {
                // Insert
                $stmt = $db->prepare("INSERT INTO posts (title, slug, content, type, category, tags, status, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $slug, $content, $type, $category, $tags, $status, $current_user['id']]);
                $id = $db->lastInsertId();
                
                // Regenerate static site
                StaticGenerator::generateAll();
                
                header("Location: /admin/stories/edit/$id/?created=1");
                exit;
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

if (isset($_GET['created'])) {
    $success = 'Post created successfully.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - FablePress.io</title>
    <link rel="stylesheet" href="/admin/assets/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Editor Page custom header */
        .editor-header {
            background-color: var(--color-white);
            border-bottom: 1px solid var(--color-border);
            height: 70px;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .editor-header-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-family: var(--font-serif);
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--color-ink);
        }
        
        .editor-layout {
            display: flex;
            min-height: calc(100vh - 70px);
        }
        
        .editor-canvas-container {
            flex-grow: 1;
            padding: 3rem 4rem;
            background-color: var(--color-paper);
            display: flex;
            justify-content: center;
            overflow-y: auto;
        }
        
        .editor-canvas {
            width: 100%;
            max-width: 720px; /* SCREEN B SPEC */
        }
        
        .editor-sidebar {
            width: 300px;
            background-color: var(--color-white);
            border-left: 1px solid var(--color-border);
            padding: 2rem 1.5rem;
            flex-shrink: 0;
            overflow-y: auto;
        }
        
        .editor-sidebar h4 {
            font-family: var(--font-sans);
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--color-muted);
            margin-bottom: 1.25rem;
            border-bottom: 1px solid var(--color-border);
            padding-bottom: 0.5rem;
        }
    </style>
</head>
<body style="display: flex; flex-direction: column; min-height: 100vh;">

    <!-- Top Editor Header (Screen B SPEC) -->
    <header class="editor-header">
        <div class="editor-header-logo">
            <div class="sidebar-logo-icon" style="width:24px; height:24px; font-size: 0.9rem;">F</div>
            <span>FablePress Editor</span>
            <span class="badge badge-gold" style="font-size: 0.65rem; margin-left: 0.5rem;">
                <?php echo $post ? 'Editing ' . ucfirst($post['type']) : 'New Post'; ?>
            </span>
        </div>
        
        <div style="display: flex; gap: 0.75rem; align-items: center;">
            <a href="/admin/stories/" class="btn btn-secondary">
                <i class="fa-solid fa-xmark"></i> Close
            </a>
            
            <button type="button" onclick="submitForm('draft')" class="btn btn-secondary">
                <i class="fa-solid fa-floppy-disk"></i> Save Draft
            </button>
            
            <?php if ($id && $post['status'] === 'published'): ?>
                <a href="/<?php echo htmlspecialchars($post['slug']); ?>/" target="_blank" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Preview
                </a>
            <?php endif; ?>
            
            <?php 
            // Check if user is allowed to publish
            $can_publish = has_permission($current_user['role'], 'publish_posts');
            ?>
            
            <?php if ($can_publish): ?>
                <button type="button" onclick="submitForm('published')" class="btn btn-primary" style="background-color: var(--color-forest);">
                    <i class="fa-solid fa-circle-check"></i> Publish
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-primary" disabled style="background-color: var(--color-muted); opacity: 0.5; cursor: not-allowed;" title="Publishing restricted for Contributors">
                    <i class="fa-solid fa-circle-check"></i> Publish
                </button>
            <?php endif; ?>
        </div>
    </header>

    <div class="editor-layout">
        <form id="editor-form" action="/admin/stories/edit/<?php echo $id ? $id . '/' : ''; ?>" method="POST" style="display: flex; width: 100%;">
            <!-- Main Content Area -->
            <div class="editor-canvas-container">
                <div class="editor-canvas">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Title Canvas Input (Screen B SPEC) -->
                    <input type="text" 
                           id="title-field"
                           name="title" 
                           class="editor-title-field" 
                           placeholder="Enter Title..." 
                           value="<?php echo htmlspecialchars($post['title'] ?? ''); ?>" 
                           required 
                           autocomplete="off">
                    
                    <!-- Content Canvas Textarea (Screen B SPEC) -->
                    <textarea name="content" 
                              id="content-field"
                              class="editor-content-field" 
                              placeholder="Start writing your story in Markdown or plain text..." 
                              autocomplete="off"><?php echo htmlspecialchars($post['content'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Sidebar Panel for Publishing Options (Screen B SPEC) -->
            <div class="editor-sidebar">
                <!-- Hidden inputs for status -->
                <input type="hidden" name="status" id="post-status" value="<?php echo htmlspecialchars($post['status'] ?? 'draft'); ?>">
                
                <h4>Post Settings</h4>
                
                <div class="form-group">
                    <label for="post-type">Content Type</label>
                    <select id="post-type" name="type" class="form-control" <?php echo !has_permission($current_user['role'], 'edit_pages') ? 'disabled' : ''; ?>>
                        <option value="story" <?php echo ($post && $post['type'] === 'story') ? 'selected' : ''; ?>>Story (Blog Post)</option>
                        <option value="page" <?php echo ($post && $post['type'] === 'page') ? 'selected' : ''; ?>>Page (Static Site page)</option>
                    </select>
                    <?php if (!has_permission($current_user['role'], 'edit_pages')): ?>
                        <span style="font-size: 0.7rem; color: var(--color-muted);">Pages restricted to Manager / Developer</span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="post-slug">URL Slug</label>
                    <input type="text" id="post-slug" name="slug" class="form-control" placeholder="slug-url-text" value="<?php echo htmlspecialchars($post['slug'] ?? ''); ?>">
                    <span style="font-size: 0.7rem; color: var(--color-muted);">Leave empty to auto-generate from title.</span>
                </div>
                
                <div class="form-group">
                    <label for="post-category">Category</label>
                    <select id="post-category" name="category" class="form-control">
                        <option value="stories" <?php echo ($post && strtolower($post['category']) === 'stories') ? 'selected' : ''; ?>>Stories</option>
                        <option value="page" <?php echo ($post && strtolower($post['category']) === 'page') ? 'selected' : ''; ?>>Page</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="post-tags">Tags</label>
                    <input type="text" id="post-tags" name="tags" class="form-control" placeholder="comma, separated, tags" value="<?php echo htmlspecialchars($post['tags'] ?? ''); ?>">
                </div>

                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border);">
                    <div style="font-size: 0.75rem; color: var(--color-muted);">
                        <strong>Author:</strong> <?php echo htmlspecialchars($post ? 'User #' . $post['author_id'] : $current_user['username']); ?><br>
                        <strong>Last Saved:</strong> <?php echo $post ? date('M j, Y H:i', strtotime($post['updated_at'])) : 'Never'; ?><br>
                        <strong>Current Status:</strong> 
                        <?php if ($post && $post['status'] === 'published'): ?>
                            <span class="badge badge-forest" style="padding: 0.1rem 0.4rem; font-size: 0.65rem;">Published</span>
                        <?php else: ?>
                            <span class="badge" style="padding: 0.1rem 0.4rem; font-size: 0.65rem;">Draft</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Form submit status toggler
        function submitForm(status) {
            document.getElementById('post-status').value = status;
            document.getElementById('editor-form').submit();
        }

        // Auto-generate slug from Title
        const titleField = document.getElementById('title-field');
        const slugField = document.getElementById('post-slug');
        
        let isSlugManuallyEdited = <?php echo ($post && $post['slug']) ? 'true' : 'false'; ?>;
        
        slugField.addEventListener('input', function() {
            isSlugManuallyEdited = true;
            if (slugField.value.trim() === '') {
                isSlugManuallyEdited = false;
            }
        });

        titleField.addEventListener('input', function() {
            if (!isSlugManuallyEdited) {
                let text = titleField.value;
                let slug = text.toLowerCase()
                               .replace(/[^a-z0-9\s-]/g, '') // remove invalid chars
                               .replace(/\s+/g, '-')         // collapse whitespace and replace by -
                               .replace(/-+/g, '-');         // collapse dashes
                
                // Remove leading/trailing dashes
                if (slug.startsWith('-')) slug = slug.substring(1);
                if (slug.endsWith('-')) slug = slug.substring(0, slug.length - 1);
                
                slugField.value = slug;
            }
        });
    </script>
</body>
</html>
