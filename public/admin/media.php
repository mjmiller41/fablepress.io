<?php
$page_active = 'media';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="margin-bottom: 0.25rem;">Media Library</h2>
        <p class="text-muted" style="margin-bottom: 0; font-family: var(--font-sans); font-size: 0.9rem;">
            Upload images and graphics to embed in your stories and pages.
        </p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="admin-grid" style="grid-template-columns: 1fr 2fr;">
    <!-- Upload Section -->
    <div class="admin-card">
        <h3 style="margin-bottom: 1rem; font-size: 1.15rem;">Upload New File</h3>
        
        <form action="/admin/media/" method="POST" enctype="multipart/form-data">
            <div style="border: 2px dashed var(--color-border); border-radius: var(--radius-md); padding: 2.5rem 1.5rem; text-align: center; background-color: var(--color-card); margin-bottom: 1.5rem; position: relative;">
                <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2.5rem; color: var(--color-forest); margin-bottom: 1rem; display: block;"></i>
                <span style="font-family: var(--font-sans); font-size: 0.85rem; font-weight: 500; display: block; margin-bottom: 0.5rem;">Choose an image file</span>
                <span style="font-size: 0.75rem; color: var(--color-muted); display: block; margin-bottom: 1.5rem;">PNG, JPG, GIF, WEBP, SVG (Max 5MB)</span>
                
                <input type="file" name="media_file" id="media_file" required style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;">
                <div id="file-selected-name" style="font-weight: 600; font-size: 0.8rem; color: var(--color-forest); word-break: break-all;"></div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fa-solid fa-upload"></i> Start Upload
            </button>
        </form>
    </div>
    
    <!-- Library Grid -->
    <div class="admin-card">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.15rem;">Uploaded Files</h3>
        
        <?php if (empty($media_items)): ?>
            <div style="text-align: center; padding: 4rem 2rem; color: var(--color-muted);">
                <i class="fa-regular fa-image" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <p>No media files uploaded yet.</p>
            </div>
        <?php else: ?>
            <div class="media-grid">
                <?php foreach ($media_items as $item): ?>
                    <div class="media-item">
                        <div class="media-preview">
                            <?php 
                            // Determine display preview
                            $ext = strtolower(pathinfo($item['filename'], PATHINFO_EXTENSION));
                            $is_img = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']);
                            ?>
                            
                            <?php if ($is_img): ?>
                                <img src="/<?php echo htmlspecialchars($item['filepath']); ?>" alt="<?php echo htmlspecialchars($item['filename']); ?>">
                            <?php else: ?>
                                <i class="fa-solid fa-file file-icon"></i>
                            <?php endif; ?>
                        </div>
                        
                        <div class="media-info">
                            <div>
                                <div class="media-name" title="<?php echo htmlspecialchars($item['filename']); ?>">
                                    <?php echo htmlspecialchars($item['filename']); ?>
                                </div>
                                <div class="media-size">
                                    Size: <?php echo format_bytes($item['filesize']); ?><br>
                                    By: <?php echo htmlspecialchars($item['username'] ?? 'System'); ?>
                                </div>
                            </div>
                            
                            <div style="margin-top: 0.75rem; display: flex; gap: 0.25rem;">
                                <button type="button" 
                                        class="btn btn-secondary btn-sm" 
                                        style="flex-grow: 1; padding: 0.35rem;" 
                                        title="Copy Image Markdown Link"
                                        onclick="copyLink('![Image](/<?php echo $item['filepath']; ?>)')">
                                    <i class="fa-solid fa-link"></i> Markdown
                                </button>
                                
                                <?php if ($current_user['role'] !== 'contributor'): ?>
                                    <a href="/admin/media/?action=delete&id=<?php echo $item['id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       style="padding: 0.35rem;" 
                                       onclick="return confirm('Delete this file permanently?');"
                                       title="Delete file">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-danger btn-sm" disabled style="opacity: 0.4; cursor: not-allowed; padding: 0.35rem;" title="Delete restricted">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // File input selection label display
    const fileInput = document.getElementById('media_file');
    const labelDiv = document.getElementById('file-selected-name');
    
    fileInput.addEventListener('change', function() {
        if (fileInput.files.length > 0) {
            labelDiv.textContent = 'Selected: ' + fileInput.files[0].name;
        } else {
            labelDiv.textContent = '';
        }
    });

    // Copy to clipboard helper
    function copyLink(text) {
        navigator.clipboard.writeText(text).then(function() {
            alert('Markdown image code copied to clipboard!\nYou can paste it directly into your story content.');
        }, function(err) {
            console.error('Could not copy text: ', err);
        });
    }
</script>

<?php
require_once __DIR__ . '/../../includes/admin-footer.php';
?>
