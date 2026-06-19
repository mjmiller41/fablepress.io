<?php
$page_active = 'media';
require_once __DIR__ . '/header.php';

$db = get_db_connection();

$message = '';
$message_type = 'success';

// Ensure uploads directory exists
$upload_dir = __DIR__ . '/../uploads';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Handle File Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    $file = $_FILES['media_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $filename = basename($file['name']);
        // Sanitize filename to avoid folder traversals and issues
        $filename = preg_replace('/[^a-zA-Z0-9\._-]/', '', $filename);
        $filetype = $file['type'];
        $filesize = $file['size'];
        
        // Allowed formats
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
        
        if (!in_array($filetype, $allowed_types)) {
            $message = 'Error: Only images (JPG, PNG, GIF, WEBP, SVG) are allowed.';
            $message_type = 'danger';
        } elseif ($filesize > 5 * 1024 * 1024) { // 5MB limit
            $message = 'Error: File size exceeds the 5MB limit.';
            $message_type = 'danger';
        } else {
            // Check for duplicates and rename if needed
            $target_path = $upload_dir . '/' . $filename;
            $path_info = pathinfo($filename);
            $counter = 1;
            
            while (file_exists($target_path)) {
                $filename = $path_info['filename'] . '_' . $counter . '.' . $path_info['extension'];
                $target_path = $upload_dir . '/' . $filename;
                $counter++;
            }
            
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                // Save to database
                // Relative filepath for public access
                $relative_path = 'uploads/' . $filename;
                
                try {
                    $stmt = $db->prepare("INSERT INTO media (filename, filepath, filetype, filesize, uploaded_by) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$filename, $relative_path, $filetype, $filesize, $current_user['id']]);
                    $message = 'File uploaded successfully.';
                } catch (Exception $e) {
                    $message = 'Database error: ' . $e->getMessage();
                    $message_type = 'danger';
                }
            } else {
                $message = 'Error: Failed to move uploaded file.';
                $message_type = 'danger';
            }
        }
    } else {
        $message = 'Error: File upload error code ' . $file['error'];
        $message_type = 'danger';
    }
}

// Handle File Deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = (int)$_GET['id'];
    
    // Fetch file details
    $stmt = $db->prepare("SELECT * FROM media WHERE id = ?");
    $stmt->execute([$delete_id]);
    $media = $stmt->fetch();
    
    if ($media) {
        // Authorization check: Contributors cannot delete media
        if ($current_user['role'] === 'contributor') {
            $message = 'Unauthorized: Contributors cannot delete media files.';
            $message_type = 'danger';
        } else {
            // Remove file from disk
            $disk_path = __DIR__ . '/../' . $media['filepath'];
            if (file_exists($disk_path)) {
                unlink($disk_path);
            }
            
            // Remove from database
            $del_stmt = $db->prepare("DELETE FROM media WHERE id = ?");
            $del_stmt->execute([$delete_id]);
            $message = 'Media file deleted successfully.';
        }
    } else {
        $message = 'Error: File not found in database.';
        $message_type = 'danger';
    }
}

// Fetch all media items
try {
    $media_stmt = $db->query("SELECT m.*, u.username FROM media m LEFT JOIN users u ON m.uploaded_by = u.id ORDER BY m.created_at DESC");
    $media_items = $media_stmt->fetchAll();
} catch (Exception $e) {
    $media_items = [];
    $message = 'Database error: ' . $e->getMessage();
    $message_type = 'danger';
}

// Helper to format bytes
function format_bytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
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
        
        <form action="media.php" method="POST" enctype="multipart/form-data">
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
                                <img src="../<?php echo htmlspecialchars($item['filepath']); ?>" alt="<?php echo htmlspecialchars($item['filename']); ?>">
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
                                        onclick="copyLink('![Image](<?php echo $item['filepath']; ?>)')">
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
require_once __DIR__ . '/footer.php';
?>
