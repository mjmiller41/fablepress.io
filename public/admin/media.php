<?php
$page_active = 'media';
require_once __DIR__ . '/../../includes/admin-header.php';
?>
<!-- ZenImages CSS and JS -->
<link rel="stylesheet" href="/assets/vendor/fablepress-images/zen-images.min.css">
<script src="/assets/vendor/fablepress-images/zen-images.min.js"></script>

<style>
.toast {
    background-color: var(--color-ink);
    color: var(--color-paper);
    padding: 0.75rem 1.25rem;
    border-radius: var(--radius-md);
    font-family: var(--font-sans);
    font-size: 0.85rem;
    font-weight: 500;
    box-shadow: var(--shadow-md);
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.3s, transform 0.3s;
    border: 1px solid var(--color-border);
}
.toast.show {
    opacity: 1;
    transform: translateY(0);
}
.dragover-highlight {
    border-color: var(--color-forest) !important;
    background-color: rgba(28, 78, 53, 0.04) !important;
}
</style>

<div id="toast-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 99999; display: flex; flex-direction: column; gap: 10px;"></div>

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
            <div id="upload-drop-zone" style="border: 2px dashed var(--color-border); border-radius: var(--radius-md); padding: 2.5rem 1.5rem; text-align: center; background-color: var(--color-card); margin-bottom: 1.5rem; position: relative; transition: all 0.2s ease;">
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
                                
                                <?php if ($is_img): ?>
                                    <button type="button" 
                                            class="btn btn-secondary btn-sm" 
                                            style="padding: 0.35rem;" 
                                            title="Edit Image"
                                            onclick="openImageEditor('/<?php echo $item['filepath']; ?>', '<?php echo htmlspecialchars($item['filename']); ?>')">
                                        <i class="fa-solid fa-crop-simple"></i> Edit
                                    </button>
                                <?php endif; ?>
                                
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

<!-- Fullscreen Image Editor Modal -->
<div id="image-editor-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: #1e1e1e; z-index: 10000; flex-direction: column;">
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

<script>
    // Toast notification manager
    function showToast(message) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        container.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        // Auto remove toast
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }

    // File input selection label display & Drag/Drop Highlights
    const fileInput = document.getElementById('media_file');
    const labelDiv = document.getElementById('file-selected-name');
    const dropZone = document.getElementById('upload-drop-zone');
    
    if (fileInput && dropZone) {
        fileInput.addEventListener('change', function() {
            if (fileInput.files.length > 0) {
                labelDiv.textContent = 'Selected: ' + fileInput.files[0].name;
            } else {
                labelDiv.textContent = '';
            }
        });

        fileInput.addEventListener('dragenter', function() {
            dropZone.classList.add('dragover-highlight');
        });
        fileInput.addEventListener('dragover', function() {
            dropZone.classList.add('dragover-highlight');
        });
        fileInput.addEventListener('dragleave', function() {
            dropZone.classList.remove('dragover-highlight');
        });
        fileInput.addEventListener('drop', function() {
            dropZone.classList.remove('dragover-highlight');
        });
    }

    // Copy to clipboard helper
    function copyLink(text) {
        navigator.clipboard.writeText(text).then(function() {
            showToast('Markdown link copied to clipboard!');
        }, function(err) {
            console.error('Could not copy text: ', err);
        });
    }

    // ZenImages Integration
    let imageEditorInstance = null;
    let currentEditingFilename = '';

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

    function openImageEditor(imageUrl, filename) {
        currentEditingFilename = filename;
        const modal = document.getElementById('image-editor-modal');
        modal.style.display = 'flex';

        // Destroy existing instance if any
        if (imageEditorInstance) {
            imageEditorInstance.destroy();
            imageEditorInstance = null;
        }

        // Initialize Toast UI Image Editor
        imageEditorInstance = new tui.ImageEditor('#tui-image-editor-container', {
            includeUI: {
                loadImage: {
                    path: imageUrl,
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
        
        // Hide standard header inside the Toast UI editor container (since we have our own header)
        setTimeout(() => {
            const header = document.querySelector('.tui-image-editor-header');
            if (header) {
                header.style.display = 'none';
            }
        }, 50);
    }

    function closeImageEditor() {
        const modal = document.getElementById('image-editor-modal');
        modal.style.display = 'none';
        if (imageEditorInstance) {
            imageEditorInstance.destroy();
            imageEditorInstance = null;
        }
    }

    function saveEditedImage() {
        if (!imageEditorInstance) return;

        const dataUrl = imageEditorInstance.toDataURL();
        const blob = dataURLtoBlob(dataUrl);

        // Prepare file name for the edited version
        let filenameParts = currentEditingFilename.split('.');
        let ext = filenameParts.pop();
        let baseName = filenameParts.join('.');
        
        // Append _edited suffix to signify it was edited
        let newFilename = baseName + '_edited.' + ext;

        const formData = new FormData();
        formData.append('media_file', blob, newFilename);

        // Show saving state
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
                closeImageEditor();
                location.reload(); // Refresh the media library to show the edited image
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

    // Helper to convert base64 dataURL to Blob
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
</script>

<?php
require_once __DIR__ . '/../../includes/admin-footer.php';
?>
