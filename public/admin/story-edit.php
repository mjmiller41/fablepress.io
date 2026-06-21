<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - FablePress.io</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="/assets/vendor/font-awesome/css/all.min.css">
    <!-- Zen Composer Editor Core CSS -->
    <link rel="stylesheet" href="/assets/vendor/fablepress-editor/zen-composer.min.css">
    <!-- Color picker CSS (Color syntax plugin dependency) -->
    <link rel="stylesheet" href="/assets/vendor/fablepress-editor/tui-color-picker.min.css">
    <!-- FablePress Zen Theme overrides -->
    <link rel="stylesheet" href="/assets/vendor/fablepress-editor/themes/fablepress-zen.css">
    <!-- ZenImages CSS -->
    <link rel="stylesheet" href="/assets/vendor/fablepress-images/zen-images.min.css">
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
            max-width: 1000px; /* Increased from 720px to comfortably fit toolbar and split screen */
            transition: max-width 0.3s ease;
        }
        
        .editor-canvas.wide-layout {
            max-width: 100%;
        }

        .editor-sidebar {
            position: relative; /* Relative positioning context for absolute toggle tab */
            width: 300px;
            background-color: var(--color-white);
            border-left: 1px solid var(--color-border);
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            transition: width 0.3s ease;
        }

        .editor-sidebar.collapsed {
            width: 0px;
            border-left-color: transparent;
        }

        .sidebar-content {
            padding: 2rem 1.5rem;
            overflow-y: auto;
            flex-grow: 1;
            width: 300px; /* Locks width during container width animation to prevent word wrapping */
            box-sizing: border-box;
            transition: opacity 0.2s ease;
        }

        .editor-sidebar.collapsed .sidebar-content {
            display: none;
            opacity: 0;
        }

        /* Sidebar sticking tab button styling */
        .sidebar-toggle-tab {
            position: absolute;
            top: 0; /* Aligned even with the top of the sidebar */
            left: -33px; /* Sticking out over the left border (width + 1px border spacer) */
            width: 32px; /* Compact width to fit single chevron content perfectly */
            height: 40px;
            background-color: var(--color-white);
            border: 1px solid var(--color-border);
            border-top: none; /* Sit flush against the header's bottom border line */
            border-right: none;
            border-radius: 0 0 0 var(--radius-sm); /* Rounded only on the bottom-left corner */
            color: var(--color-muted);
            display: flex;
            gap: 4px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            box-shadow: -4px 2px 8px rgba(30, 36, 43, 0.04);
            transition: background-color 0.2s, color 0.2s, left 0.3s, width 0.3s, border-radius 0.3s;
            outline: none;
        }

        .sidebar-toggle-tab:hover {
            background-color: var(--color-card);
            color: var(--color-gold);
        }

        /* Styling overrides when sidebar collapsed */
        .editor-sidebar.collapsed .sidebar-toggle-tab {
            left: -52px; /* Sticks out further to clear the wider tab */
            width: 52px; /* Expands to fit both gear and chevron icons side-by-side */
            border-right: 1px solid var(--color-border); /* restore right border since parent width is 0 */
            border-radius: 0 0 var(--radius-sm) var(--radius-sm); /* round both bottom corners when floating */
            box-shadow: -2px 2px 8px rgba(30, 36, 43, 0.08);
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

        @media (max-width: 768px) {
            .editor-canvas-container {
                padding: 1.5rem 1rem;
            }
            .editor-header {
                padding: 0 1rem;
            }
            /* Hide the main brand label but keep the action icons/badge to save header space */
            .editor-header-logo span:not(.badge) {
                display: none;
            }
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
            <button type="button" id="toggle-layout-btn" class="btn btn-secondary" title="Toggle Split View" style="display: none;">
                <i class="fa-solid fa-columns"></i> <span class="btn-text">Split: On</span>
            </button>

            <a href="/admin/stories/" class="btn btn-secondary">
                <i class="fa-solid fa-xmark"></i> Close
            </a>
            
            <button type="button" onclick="submitForm('draft')" class="btn btn-secondary">
                <i class="fa-solid fa-floppy-disk"></i> Save Draft
            </button>
            
            <?php if ($id && $post['status'] === 'published'): ?>
                <a href="/<?php echo $post['type'] === 'story' ? 'stories/' : ''; ?><?php echo htmlspecialchars($post['slug']); ?>/" target="_blank" class="btn btn-secondary">
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
                    
                    <!-- Content Editor Canvas (Zen Composer target) -->
                    <div id="fable-editor-canvas" style="margin-top: 1rem; width: 100%;"></div>
                    
                    <!-- Content Canvas Textarea (Hidden to preserve post data submission) -->
                    <textarea name="content" 
                              id="content-field"
                              style="display: none;"><?php echo htmlspecialchars($post['content'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Sidebar Panel for Publishing Options (Screen B SPEC) -->
            <div class="editor-sidebar">
                <!-- Sticking-out toggle tab -->
                <button type="button" id="sidebar-toggle-tab" class="sidebar-toggle-tab" title="Close Settings Sidebar">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>

                <!-- Inner content wrapper for clean padding & visibility toggle -->
                <div class="sidebar-content">
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
            </div>
        </form>
    </div>

    <!-- Editor JS dependencies -->
    <!-- Zen Composer Editor Core JS -->
    <script src="/assets/vendor/fablepress-editor/zen-composer-all.min.js"></script>
    <!-- Color picker JS (Color syntax plugin dependency) -->
    <script src="/assets/vendor/fablepress-editor/tui-color-picker.min.js"></script>
    <!-- Zen Composer Plugins -->
    <script src="/assets/vendor/fablepress-editor/zen-composer-plugin-color-syntax.min.js"></script>
    <script src="/assets/vendor/fablepress-editor/zen-composer-plugin-table-merged-cell.min.js"></script>
    <script src="/assets/vendor/fablepress-editor/zen-composer-plugin-code-syntax-highlight-all.min.js"></script>
    <!-- FablePress Editor Wrapper -->
    <script src="/assets/vendor/fablepress-editor/fablepress-editor.js"></script>
    <!-- ZenImages JS -->
    <script src="/assets/vendor/fablepress-images/zen-images.min.js"></script>

    <script>
        // Collapsible sidebar logic using sticking tab
        const sidebarToggleTab = document.getElementById('sidebar-toggle-tab');
        const editorSidebar = document.querySelector('.editor-sidebar');
        
        sidebarToggleTab.addEventListener('click', function() {
            editorSidebar.classList.toggle('collapsed');
            
            const mainCanvas = document.querySelector('.editor-canvas');
            if (mainCanvas) {
                mainCanvas.classList.toggle('wide-layout');
            }
            
            // Toggle icons based on state
            if (editorSidebar.classList.contains('collapsed')) {
                sidebarToggleTab.innerHTML = '<i class="fa-solid fa-gear"></i><i class="fa-solid fa-chevron-left" style="font-size: 0.75rem; margin-left: 2px;"></i>';
                sidebarToggleTab.title = "Open Settings Sidebar";
            } else {
                sidebarToggleTab.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
                sidebarToggleTab.title = "Close Settings Sidebar";
            }

            // Force Zen Composer editor to redraw/resize by triggering window resize event
            window.dispatchEvent(new Event('resize'));
        });

        // Auto-collapse sidebar on smaller screens on page load
        if (window.innerWidth < 992) {
            editorSidebar.classList.add('collapsed');
            const mainCanvas = document.querySelector('.editor-canvas');
            if (mainCanvas) {
                mainCanvas.classList.add('wide-layout');
            }
            sidebarToggleTab.innerHTML = '<i class="fa-solid fa-gear"></i><i class="fa-solid fa-chevron-left" style="font-size: 0.75rem; margin-left: 2px;"></i>';
            sidebarToggleTab.title = "Open Settings Sidebar";
        }

        // Initialize FablePress Editor
        const textarea = document.getElementById('content-field');
        const editorCanvas = document.getElementById('fable-editor-canvas');
        
        const editor = FableEditor.init({
            el: editorCanvas,
            initialValue: textarea.value,
            onUploadImage: function(blob, callback) {
                const formData = new FormData();
                formData.append('media_file', blob);

                fetch('/admin/media/upload-ajax/', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(err => { throw new Error(err.error || 'Server error'); });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        callback(data.url, blob.name);
                    } else {
                        alert('Upload failed: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(err => {
                    console.error('Upload error:', err);
                    alert('An error occurred during file upload: ' + err.message);
                });
            }
        });

        // ZenImages & Resize Options State
        let selectedImageState = null;
        let imageEditorInstance = null;
        let isAspectLocked = true;
        let currentAspectRatio = 1;

        // Click on image to trigger options modal (Resize vs full edit)
        editorCanvas.addEventListener('click', function(e) {
            const img = e.target.closest('img');
            if (!img) return;
            
            // Verify image is inside editor canvas
            if (!editorCanvas.contains(img)) return;
            
            // Get raw src
            const rawSrc = img.getAttribute('src');
            if (!rawSrc) return;
            
            // Skip UI icons/placeholders
            if (rawSrc.startsWith('data:') || rawSrc.includes('font-awesome') || rawSrc.includes('assets/css')) {
                return;
            }
            
            // Extract clean path and current size
            let cleanSrc = rawSrc;
            let currentWidth = '';
            let currentHeight = '';
            
            const hashIndex = rawSrc.indexOf('#');
            const queryIndex = rawSrc.indexOf('?');
            let queryStr = '';
            
            if (hashIndex !== -1) {
                cleanSrc = rawSrc.substring(0, hashIndex);
                queryStr = rawSrc.substring(hashIndex + 1);
            } else if (queryIndex !== -1) {
                cleanSrc = rawSrc.substring(0, queryIndex);
                queryStr = rawSrc.substring(queryIndex + 1);
            }
            
            if (queryStr) {
                const params = {};
                queryStr.split('&').forEach(part => {
                    const pair = part.split('=');
                    if (pair[0]) params[pair[0]] = pair[1] || '';
                });
                currentWidth = params.width || params.w || '';
                currentHeight = params.height || params.h || '';
                if (!currentWidth) {
                    const match = queryStr.match(/^(\d+)(x(\d+))?$/);
                    if (match) {
                        currentWidth = match[1];
                        if (match[3]) {
                            currentHeight = match[3];
                        }
                    }
                }
            }
            
            // Extract relative pathname if the browser resolved src to an absolute URL
            let cleanPath = cleanSrc;
            if (cleanSrc.startsWith('http://') || cleanSrc.startsWith('https://')) {
                try {
                    const urlObj = new URL(cleanSrc);
                    cleanPath = urlObj.pathname;
                } catch(err) {}
            }

            let displayWidth = currentWidth;
            let displayHeight = currentHeight;

            // Fallback & calculate if one dimension is missing based on current aspect ratio
            const naturalW = img.naturalWidth || img.clientWidth || img.width || 0;
            const naturalH = img.naturalHeight || img.clientHeight || img.height || 0;
            
            if (naturalW && naturalH) {
                currentAspectRatio = naturalW / naturalH;
            } else if (parseFloat(displayWidth) && parseFloat(displayHeight)) {
                currentAspectRatio = parseFloat(displayWidth) / parseFloat(displayHeight);
            } else {
                currentAspectRatio = 1;
            }
            
            // If they are custom sized, calculate from custom sizes
            if (parseFloat(displayWidth) && parseFloat(displayHeight)) {
                currentAspectRatio = parseFloat(displayWidth) / parseFloat(displayHeight);
            }

            if (displayWidth && !displayHeight) {
                displayHeight = Math.round(parseFloat(displayWidth) / currentAspectRatio).toString();
            } else if (displayHeight && !displayWidth) {
                displayWidth = Math.round(parseFloat(displayHeight) * currentAspectRatio).toString();
            } else if (!displayWidth && !displayHeight) {
                displayWidth = naturalW ? naturalW.toString() : '';
                displayHeight = naturalH ? naturalH.toString() : '';
            }

            // Save the state of the clicked image
            selectedImageState = {
                img: img,
                rawSrc: rawSrc,
                cleanSrc: cleanSrc,
                cleanPath: cleanPath,
                currentWidth: currentWidth,
                currentHeight: currentHeight
            };

            // Pre-fill input boxes in the modal
            const widthInput = document.getElementById('img-width-input');
            const heightInput = document.getElementById('img-height-input');
            if (widthInput && heightInput) {
                widthInput.value = displayWidth;
                heightInput.value = displayHeight;
            }

            // Reset aspect lock styling to locked state when opening modal
            isAspectLocked = true;
            updateAspectLockUI();

            // Show options modal
            openImageActions();
        });

        function openImageActions() {
            document.getElementById('image-actions-modal').style.display = 'flex';
        }

        function closeImageActions() {
            document.getElementById('image-actions-modal').style.display = 'none';
        }

        function updateAspectLockUI() {
            const btn = document.getElementById('img-aspect-lock-btn');
            const icon = document.getElementById('img-aspect-lock-icon');
            if (!btn || !icon) return;
            
            if (isAspectLocked) {
                btn.style.borderColor = 'var(--color-gold)';
                btn.style.color = 'var(--color-gold)';
                icon.className = 'fa-solid fa-link';
                btn.title = "Aspect Ratio Locked (Click to unlock)";
            } else {
                btn.style.borderColor = 'var(--color-border)';
                btn.style.color = 'var(--color-muted)';
                icon.className = 'fa-solid fa-link-slash';
                btn.title = "Aspect Ratio Unlocked (Click to lock)";
            }
        }

        function toggleAspectLock() {
            isAspectLocked = !isAspectLocked;
            updateAspectLockUI();
            
            // Re-calculate the aspect ratio based on the current inputs if locked
            if (isAspectLocked) {
                const widthInput = document.getElementById('img-width-input');
                const heightInput = document.getElementById('img-height-input');
                if (widthInput && heightInput && parseFloat(widthInput.value) && parseFloat(heightInput.value)) {
                    currentAspectRatio = parseFloat(widthInput.value) / parseFloat(heightInput.value);
                }
            }
        }

        function commitResize() {
            if (!selectedImageState) return;
            
            const widthVal = document.getElementById('img-width-input').value.trim();
            const heightVal = document.getElementById('img-height-input').value.trim();
            
            let newSrc = selectedImageState.cleanSrc;
            let suffix = '';
            
            if (widthVal && heightVal) {
                suffix = widthVal + 'x' + heightVal;
            } else if (widthVal) {
                suffix = widthVal;
            } else if (heightVal) {
                suffix = 'x' + heightVal;
            }
            
            if (suffix) {
                newSrc += '#' + suffix;
            }
            
            const currentMarkdown = editor.getMarkdown();
            const filename = selectedImageState.cleanPath.split('/').pop();
            const escapedFilename = filename.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
            const regex = new RegExp('(!\\[[^\\]]*\\]\\()[^)]*' + escapedFilename + '(?:#[^)]*|\\?[^)]*)?(\\))', 'g');
            
            const newMarkdown = currentMarkdown.replace(regex, `$1${newSrc}$2`);
            
            if (newMarkdown !== currentMarkdown) {
                editor.setMarkdown(newMarkdown, false);
            }
            
            closeImageActions();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const widthInput = document.getElementById('img-width-input');
            const heightInput = document.getElementById('img-height-input');
            const lockBtn = document.getElementById('img-aspect-lock-btn');
            
            if (lockBtn) {
                lockBtn.addEventListener('click', toggleAspectLock);
            }
            
            if (widthInput) {
                widthInput.addEventListener('input', function() {
                    if (isAspectLocked && currentAspectRatio) {
                        const val = parseFloat(widthInput.value);
                        if (val && val > 0) {
                            heightInput.value = Math.round(val / currentAspectRatio);
                        } else {
                            heightInput.value = '';
                        }
                    }
                });
            }
            
            if (heightInput) {
                heightInput.addEventListener('input', function() {
                    if (isAspectLocked && currentAspectRatio) {
                        const val = parseFloat(heightInput.value);
                        if (val && val > 0) {
                            widthInput.value = Math.round(val * currentAspectRatio);
                        } else {
                            widthInput.value = '';
                        }
                    }
                });
            }
        });

        const imageEditorTheme = {
            'common.bi.image': '',
            'common.bisize.width': '0px',
            'common.bisize.height': '0px',
            'common.backgroundColor': '#1e1e1e',
            'common.border': '0px',
            'header.backgroundImage': 'none',
            'header.backgroundColor': 'transparent',
            'header.border': '0px',
            'loadButton.display': 'none',
            'downloadButton.display': 'none',
            'menu.normalIcon.path': '/assets/vendor/fablepress-images/svg/icon-d.svg',
            'menu.normalIcon.name': 'icon-d',
            'menu.activeIcon.path': '/assets/vendor/fablepress-images/svg/icon-b.svg',
            'menu.activeIcon.name': 'icon-b',
            'menu.disabledIcon.path': '/assets/vendor/fablepress-images/svg/icon-a.svg',
            'menu.disabledIcon.name': 'icon-a',
            'menu.hoverIcon.path': '/assets/vendor/fablepress-images/svg/icon-c.svg',
            'menu.hoverIcon.name': 'icon-c',
            'submenu.normalIcon.path': '/assets/vendor/fablepress-images/svg/icon-d.svg',
            'submenu.normalIcon.name': 'icon-d',
            'submenu.activeIcon.path': '/assets/vendor/fablepress-images/svg/icon-b.svg',
            'submenu.activeIcon.name': 'icon-b',
            'submenu.backgroundColor': '#2e2e2e',
            'submenu.partition.color': '#444444',
            'submenu.label.normal.color': '#aaaaaa',
            'submenu.label.active.color': '#c99b3b',
            'checkbox.border': '1px solid #ccc',
            'checkbox.backgroundColor': '#fff',
            'range.pointer.color': '#c99b3b',
            'range.bar.color': '#666',
            'range.subbar.color': '#d1d1d1',
            'range.value.color': '#fff',
            'range.value.backgroundColor': '#151515',
            'range.title.color': '#fff',
            'colorpicker.button.border': '1px solid #1e1e1e',
            'colorpicker.title.color': '#fff'
        };

        function triggerEditOption() {
            closeImageActions();
            if (!selectedImageState) return;

            document.getElementById('image-editor-modal').style.display = 'flex';

            // Destroy existing instance if any
            if (imageEditorInstance) {
                imageEditorInstance.destroy();
                imageEditorInstance = null;
            }

            let filename = selectedImageState.cleanPath.split('/').pop() || 'image.png';

            // Initialize Toast UI Image Editor
            imageEditorInstance = new tui.ImageEditor('#tui-image-editor-container', {
                includeUI: {
                    loadImage: {
                        path: selectedImageState.cleanSrc,
                        name: filename
                    },
                    theme: imageEditorTheme,
                    menu: ['crop', 'flip', 'rotate', 'draw', 'filter'],
                    initMenu: 'crop',
                    uiSize: {
                        width: '100%',
                        height: '100%'
                    },
                    menuBarPosition: 'bottom'
                },
                cssMaxWidth: 800,
                cssMaxHeight: 600
            });
            
            // Hide standard header inside the Toast UI editor container
            setTimeout(() => {
                const header = document.querySelector('.tui-image-editor-header');
                if (header) {
                    header.style.display = 'none';
                }
            }, 50);
        }

        function closeImageEditor() {
            document.getElementById('image-editor-modal').style.display = 'none';
            if (imageEditorInstance) {
                imageEditorInstance.destroy();
                imageEditorInstance = null;
            }
        }

        function saveEditedImage() {
            if (!imageEditorInstance || !selectedImageState) return;

            const dataUrl = imageEditorInstance.toDataURL();
            const blob = dataURLtoBlob(dataUrl);

            let origFilename = selectedImageState.cleanPath.split('/').pop() || 'image.png';
            let filenameParts = origFilename.split('.');
            let ext = filenameParts.pop();
            let baseName = filenameParts.join('.');
            let newFilename = baseName + '_edited.' + ext;

            const formData = new FormData();
            formData.append('media_file', blob, newFilename);

            const saveBtn = document.querySelector('#image-editor-modal button[onclick="saveEditedImage()"]');
            const origHtml = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Saving...';

            fetch('/admin/media/upload-ajax/', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw new Error(err.error || 'Server error'); });
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    const newUrl = data.url;
                    const newFilenameFromServer = newUrl.split('/').pop();
                    
                    // Update markdown content with new URL and update auto-generated filename in alt text
                    const currentMarkdown = editor.getMarkdown();
                    const filename = selectedImageState.cleanPath.split('/').pop();
                    const escapedFilename = filename.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                    const regex = new RegExp('(!\\[([^\\]]*)\\]\\()[^)]*' + escapedFilename + '(?:#[^)]*|\\?[^)]*)?(\\))', 'g');
                    
                    // For a cropped/edited image, reset the size hash so it defaults to the new cropped dimensions
                    let finalUrl = newUrl;
                    
                    const newMarkdown = currentMarkdown.replace(regex, function(match, g1, altText) {
                        let newAlt = altText;
                        const lowerAlt = altText.toLowerCase();
                        if (altText === filename || 
                            lowerAlt.endsWith('.png') || 
                            lowerAlt.endsWith('.jpg') || 
                            lowerAlt.endsWith('.jpeg') || 
                            lowerAlt.endsWith('.gif') || 
                            lowerAlt.endsWith('.webp') || 
                            lowerAlt.endsWith('.svg')) {
                            newAlt = newFilenameFromServer;
                        }
                        return '![' + newAlt + '](' + finalUrl + ')';
                    });
                    
                    if (newMarkdown !== currentMarkdown) {
                        editor.setMarkdown(newMarkdown, false);
                    }
                    
                    closeImageEditor();
                } else {
                    alert('Save failed: ' + (data.error || 'Unknown error'));
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = origHtml;
                }
            })
            .catch(err => {
                console.error('Save error:', err);
                alert('An error occurred during save: ' + err.message);
                saveBtn.disabled = false;
                saveBtn.innerHTML = origHtml;
            });
        }

        function dataURLtoBlob(dataurl) {
            const arr = dataurl.split(',');
            const mime = arr[0].match(/:(.*?);/)[1];
            const bstr = atob(arr[1]);
            let n = bstr.length;
            const u8arr = new Uint8Array(n);
            while (n--) {
                u8arr[n] = bstr.charCodeAt(n);
            }
            return new Blob([u8arr], {type: mime});
        }

        // Layout toggle button logic (Split vs Tab preview in Markdown mode)
        const toggleLayoutBtn = document.getElementById('toggle-layout-btn');

        function updateLayoutButtonVisibility() {
            if (!toggleLayoutBtn) return;
            
            if (editor.isWysiwygMode()) {
                toggleLayoutBtn.style.display = 'none';
            } else {
                toggleLayoutBtn.style.display = '';
                const style = editor.getCurrentPreviewStyle();
                const icon = toggleLayoutBtn.querySelector('i');
                const text = toggleLayoutBtn.querySelector('.btn-text');
                
                if (style === 'vertical') {
                    icon.className = 'fa-solid fa-columns';
                    text.textContent = ' Split: On';
                    toggleLayoutBtn.classList.add('active');
                    toggleLayoutBtn.style.borderColor = 'var(--color-gold)';
                    toggleLayoutBtn.style.color = 'var(--color-gold)';
                } else {
                    icon.className = 'fa-solid fa-columns';
                    text.textContent = ' Split: Off';
                    toggleLayoutBtn.classList.remove('active');
                    toggleLayoutBtn.style.borderColor = '';
                    toggleLayoutBtn.style.color = '';
                }
            }
        }

        // Initialize visibility
        updateLayoutButtonVisibility();

        // Listen for layout button click
        toggleLayoutBtn.addEventListener('click', function() {
            const currentStyle = editor.getCurrentPreviewStyle();
            const newStyle = currentStyle === 'vertical' ? 'tab' : 'vertical';
            editor.changePreviewStyle(newStyle);
            updateLayoutButtonVisibility();
            window.dispatchEvent(new Event('resize'));
        });

        // Listen for mode switcher tabs at the bottom
        editorCanvas.addEventListener('click', function(e) {
            if (e.target.closest('.zen-composer-mode-switch .tab-item')) {
                // Delay slightly to let the editor update its internal state
                setTimeout(updateLayoutButtonVisibility, 50);
            }
        });

        // Unsaved changes warning logic
        let initialContent = editor.getMarkdown();
        let initialTitle = document.getElementById('title-field') ? document.getElementById('title-field').value : '';
        let isSubmitting = false;

        window.addEventListener('beforeunload', function(e) {
            if (isSubmitting) return;
            const currentContent = editor.getMarkdown();
            const currentTitle = document.getElementById('title-field') ? document.getElementById('title-field').value : '';
            if (currentContent !== initialContent || currentTitle !== initialTitle) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                return e.returnValue;
            }
        });

        // Form submit status toggler
        function submitForm(status) {
            isSubmitting = true;
            // Sync editor content back to standard textarea before submit
            textarea.value = editor.getMarkdown();
            
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
    <!-- Image Actions Modal Overlay -->
    <div id="image-actions-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: rgba(30, 36, 43, 0.4); z-index: 10005; justify-content: center; align-items: center; backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);">
        <div class="admin-card" style="width: 340px; background-color: var(--color-white); border-radius: var(--radius-md); padding: 1.75rem; box-shadow: var(--shadow-md); border: 1px solid var(--color-border); position: relative;">
            <h4 style="margin-top: 0; margin-bottom: 1.25rem; font-family: var(--font-serif); font-size: 1.25rem; font-weight: 700; color: var(--color-ink); text-align: center;">Image Options</h4>
            
            <!-- Sizing Input Section -->
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 1.5rem;">
                <div style="flex: 1; text-align: left;">
                    <label for="img-width-input" style="display: block; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem; color: var(--color-muted); font-family: var(--font-sans);">Width (px)</label>
                    <input type="number" id="img-width-input" class="form-control" style="padding: 0.5rem; text-align: center; font-family: var(--font-sans); font-size: 0.9rem;" placeholder="auto" min="1">
                </div>
                <div style="display: flex; align-items: center; justify-content: center; padding-top: 1.25rem;">
                    <button type="button" id="img-aspect-lock-btn" title="Lock/Unlock Aspect Ratio" style="background: none; border: 1px solid var(--color-gold); border-radius: var(--radius-sm); width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; color: var(--color-gold); cursor: pointer; transition: all 0.2s ease; outline: none;">
                        <i class="fa-solid fa-link" id="img-aspect-lock-icon"></i>
                    </button>
                </div>
                <div style="flex: 1; text-align: left;">
                    <label for="img-height-input" style="display: block; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem; color: var(--color-muted); font-family: var(--font-sans);">Height (px)</label>
                    <input type="number" id="img-height-input" class="form-control" style="padding: 0.5rem; text-align: center; font-family: var(--font-sans); font-size: 0.9rem;" placeholder="auto" min="1">
                </div>
            </div>

            <!-- Action buttons -->
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <button type="button" class="btn btn-primary" onclick="commitResize()" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <i class="fa-solid fa-check"></i> Apply Size
                </button>
                <button type="button" class="btn btn-primary" onclick="triggerEditOption()" style="width: 100%; background-color: var(--color-forest); border-color: var(--color-forest); display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <i class="fa-solid fa-crop-simple"></i> Edit Image (Crop / Filters)
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeImageActions()" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Fullscreen Image Editor Modal Overlay -->
    <div id="image-editor-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: #1e1e1e; z-index: 10010; flex-direction: column;">
        <div style="height: 60px; display: flex; justify-content: space-between; align-items: center; padding: 0 2rem; background-color: #2e2e2e; border-bottom: 1px solid #444; color: #fff;">
            <h3 id="image-editor-title" style="margin: 0; font-family: var(--font-serif); font-size: 1.15rem; color: #fff; display: flex; align-items: center; gap: 0.5rem; font-weight: 700;">
                <i class="fa-solid fa-crop-simple" style="color: var(--color-gold);"></i> Edit Image
            </h3>
            <div style="display: flex; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeImageEditor()" style="background-color: #444; border-color: #555; color: #fff;">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="saveEditedImage()">
                    <i class="fa-solid fa-check"></i> Save Changes
                </button>
            </div>
        </div>
        <div id="tui-image-editor-container" style="flex-grow: 1; width: 100%; height: calc(100vh - 60px);"></div>
    </div>
</body>
</html>
