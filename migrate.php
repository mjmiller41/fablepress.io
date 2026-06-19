<?php
// Load configuration and core function definitions
require_once __DIR__ . '/app/Config/config.php';

// Security Checks
// 1. Check if fablepress.db exists in the root directory
$db_file = __DIR__ . '/app/Database/fablepress.db';
$db_exists = file_exists($db_file);

// 2. Check if the configuration is set to MySQL mode
$is_mysql = (defined('DB_MODE') && DB_MODE === 'mysql');

// Functions to render clean, premium layout blocks aligning with Design System
function render_header($title) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?> - FablePress.io</title>
        <link rel="stylesheet" href="/admin/assets/admin.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root {
                --color-ink: #1E242B;
                --color-paper: #FAF6F0;
                --color-forest: #1C4E35;
                --color-gold: #C99B3B;
                --color-card: #F4F0E8;
                --color-muted: #606870;
                --font-serif: "Lora", "Merriweather", serif;
                --font-sans: "Inter", "Outfit", sans-serif;
                --radius-sm: 6px;
                --radius-md: 10px;
                --radius-lg: 16px;
            }
            body {
                background-color: var(--color-paper);
                color: var(--color-ink);
                font-family: var(--font-sans);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0;
                padding: 2rem;
            }
            .migration-card {
                background: #ffffff;
                border: 1px solid #E6DFD5;
                border-radius: var(--radius-lg);
                padding: 3rem;
                max-width: 650px;
                width: 100%;
                box-shadow: 0 8px 24px rgba(30, 36, 43, 0.05);
            }
            .logo-header {
                text-align: center;
                margin-bottom: 2rem;
            }
            .logo-icon {
                background: var(--color-forest);
                color: #ffffff;
                width: 50px;
                height: 50px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-family: var(--font-serif);
                font-size: 1.8rem;
                font-weight: bold;
                border-radius: 50%;
                margin-bottom: 0.5rem;
            }
            h1, h2, h3 {
                font-family: var(--font-serif);
                color: var(--color-ink);
            }
            .btn-action {
                background-color: var(--color-forest);
                color: #ffffff;
                border: none;
                padding: 0.85rem 1.5rem;
                font-size: 1rem;
                border-radius: var(--radius-md);
                cursor: pointer;
                font-family: var(--font-sans);
                font-weight: 600;
                width: 100%;
                transition: opacity 0.2s ease;
                display: inline-block;
                text-align: center;
                text-decoration: none;
            }
            .btn-action:hover {
                opacity: 0.9;
            }
            .info-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 1.5rem;
                margin-top: 1rem;
            }
            .info-table th, .info-table td {
                padding: 0.75rem;
                text-align: left;
                border-bottom: 1px solid #E6DFD5;
            }
            .info-table th {
                font-weight: 600;
                color: var(--color-muted);
                width: 40%;
                font-size: 0.9rem;
            }
            .info-table td {
                font-size: 0.9rem;
            }
            .warning-banner {
                background-color: #FFFDF5;
                border-left: 4px solid var(--color-gold);
                padding: 1.25rem;
                border-radius: 0 var(--radius-md) var(--radius-md) 0;
                margin-bottom: 1.5rem;
            }
            .success-banner {
                background-color: #F4FAF6;
                border-left: 4px solid var(--color-forest);
                padding: 1.25rem;
                border-radius: 0 var(--radius-md) var(--radius-md) 0;
                margin-bottom: 1.5rem;
            }
            .error-banner {
                background-color: #FFF5F5;
                border-left: 4px solid #D93838;
                padding: 1.25rem;
                border-radius: 0 var(--radius-md) var(--radius-md) 0;
                margin-bottom: 1.5rem;
                color: #B32424;
            }
        </style>
    </head>
    <body>
        <div class="migration-card">
            <div class="logo-header">
                <div class="logo-icon">F</div>
                <h2 style="margin: 0.5rem 0 0.25rem 0; font-family: var(--font-serif);">FablePress.io</h2>
                <p style="color: var(--color-muted); margin: 0; font-size: 0.9rem;">Database Migration Utility</p>
            </div>
    <?php
}

function render_footer() {
    ?>
        </div>
    </body>
    </html>
    <?php
}

// Perform Security Checks on Initial Page Load
if (!$db_exists) {
    render_header("Migration Error");
    ?>
    <div class="error-banner">
        <i class="fa-solid fa-triangle-exclamation" style="margin-right: 0.5rem;"></i>
        <strong>Migration SQLite database (fablepress.db) not found.</strong>
        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; line-height: 1.4;">
            Please upload your local <code>fablepress.db</code> to the root directory first.
        </p>
    </div>
    <div style="text-align: center; margin-top: 1.5rem;">
        <a href="/" class="btn-action" style="background-color: var(--color-muted);">Back to Homepage</a>
    </div>
    <?php
    render_footer();
    exit;
}

if (!$is_mysql) {
    render_header("Migration Configuration Error");
    ?>
    <div class="error-banner">
        <i class="fa-solid fa-triangle-exclamation" style="margin-right: 0.5rem;"></i>
        <strong>Target database mode must be MySQL.</strong>
        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; line-height: 1.4;">
            The active database mode is currently set to <strong>'<?php echo htmlspecialchars(DB_MODE); ?>'</strong>. 
            The target must be <strong>'mysql'</strong> to execute this migration. 
            Please configure <code>DB_MODE=mysql</code> in your environment variables or <code>config.php</code>.
        </p>
    </div>
    <div style="text-align: center; margin-top: 1.5rem;">
        <a href="/" class="btn-action" style="background-color: var(--color-muted);">Back to Homepage</a>
    </div>
    <?php
    render_footer();
    exit;
}

$error = '';
$success = false;
$summary = [];
$deleted_db = false;

// Handle the migration POST action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_migration'])) {
    try {
        // Connect to the local SQLite database
        $sqlite = new PDO('sqlite:' . $db_file);
        $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Connect to the production MySQL database
        $mysql = get_db_connection();
        
        // Disable foreign key checks to allow clearing of tables
        $mysql->exec("SET FOREIGN_KEY_CHECKS = 0;");
        
        // Ordered list of tables to clear and migrate
        $tables = ['users', 'posts', 'media', 'navigation', 'role_permissions'];
        
        // Truncate existing tables in MySQL
        foreach ($tables as $table) {
            $mysql->exec("TRUNCATE TABLE `$table`");
        }
        
        // Copy data from SQLite to MySQL
        foreach ($tables as $table) {
            // Read rows from SQLite table
            $stmt = $sqlite->query("SELECT * FROM `$table`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $copied = 0;
            
            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                // Wrap columns in backticks to avoid SQL keyword conflicts
                $colList = implode(', ', array_map(function($col) { return "`$col`"; }, $columns));
                $placeholders = implode(', ', array_fill(0, count($columns), '?'));
                
                $insertSql = "INSERT INTO `$table` ($colList) VALUES ($placeholders)";
                $insertStmt = $mysql->prepare($insertSql);
                
                foreach ($rows as $row) {
                    $insertStmt->execute(array_values($row));
                    $copied++;
                }
            }
            $summary[$table] = $copied;
        }
        
        // Re-enable foreign key checks
        $mysql->exec("SET FOREIGN_KEY_CHECKS = 1;");
        
        // Clean up the uploaded database file so it isn't left exposed
        if (file_exists($db_file)) {
            if (unlink($db_file)) {
                $deleted_db = true;
            } else {
                throw new Exception("Unable to delete fablepress.db. Please check directory permissions.");
            }
        } else {
            $deleted_db = true;
        }
        
        $success = true;
    } catch (Exception $e) {
        // Safe check to restore foreign keys on exception
        try {
            $mysql = get_db_connection();
            $mysql->exec("SET FOREIGN_KEY_CHECKS = 1;");
        } catch (Exception $ex) {}
        
        $error = $e->getMessage();
    }
}

if (!$success):
    render_header("Database Migration");
    if ($error):
    ?>
    <div class="error-banner">
        <i class="fa-solid fa-circle-xmark" style="margin-right: 0.5rem;"></i>
        <strong>Migration Failed</strong>
        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; line-height: 1.4;">
            <?php echo htmlspecialchars($error); ?>
        </p>
    </div>
    <?php endif; ?>

    <div class="warning-banner">
        <i class="fa-solid fa-triangle-exclamation" style="margin-right: 0.5rem; color: var(--color-gold);"></i>
        <strong>Caution: Data Overwrite Warning</strong>
        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; line-height: 1.4; color: var(--color-ink);">
            Running this migration will completely wipe the existing records in the target MySQL database tables 
            (<code>users</code>, <code>posts</code>, <code>media</code>, <code>navigation</code>, <code>role_permissions</code>) 
            and replace them with the data from the uploaded <code>fablepress.db</code>.
        </p>
    </div>

    <h3 style="margin-top: 2rem;">Migration Parameters</h3>
    <table class="info-table">
        <tr>
            <th>Target DB Mode</th>
            <td><span style="font-family: var(--font-sans); font-weight: bold; color: var(--color-forest);">MYSQL</span></td>
        </tr>
        <tr>
            <th>MySQL Host</th>
            <td><code><?php echo htmlspecialchars(DB_HOST); ?></code></td>
        </tr>
        <tr>
            <th>MySQL Database</th>
            <td><code><?php echo htmlspecialchars(DB_NAME); ?></code></td>
        </tr>
        <tr>
            <th>MySQL User</th>
            <td><code><?php echo htmlspecialchars(DB_USER); ?></code></td>
        </tr>
        <tr>
            <th>SQLite Source File</th>
            <td><code>fablepress.db</code> (<?php echo round(filesize($db_file) / 1024, 2); ?> KB)</td>
        </tr>
    </table>

    <form method="POST" action="">
        <input type="hidden" name="confirm_migration" value="1">
        <button type="submit" class="btn-action">
            <i class="fa-solid fa-database" style="margin-right: 0.5rem;"></i> Start MySQL Migration
        </button>
    </form>
    
    <div style="text-align: center; margin-top: 1.5rem;">
        <a href="/" style="font-size: 0.85rem; color: var(--color-muted); text-decoration: none;">&larr; Back to Homepage</a>
    </div>
    <?php
    render_footer();
else:
    // Successful migration screen
    render_header("Migration Successful");
    ?>
    <div class="success-banner">
        <i class="fa-solid fa-circle-check" style="margin-right: 0.5rem; color: var(--color-forest);"></i>
        <strong>Data migration completed successfully!</strong>
    </div>

    <h3>Migration Summary</h3>
    <table class="info-table">
        <thead>
            <tr>
                <th style="color: var(--color-ink); text-align: left;">Table Name</th>
                <th style="color: var(--color-ink); text-align: right; width: auto;">Records Copied</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($summary as $table => $count): ?>
            <tr>
                <td style="font-family: var(--font-sans);"><?php echo htmlspecialchars($table); ?></td>
                <td style="font-family: var(--font-sans); text-align: right; font-weight: bold;"><?php echo $count; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="warning-banner" style="background-color: #FFF5F5; border-left: 4px solid #D93838; color: #1E242B;">
        <i class="fa-solid fa-circle-exclamation" style="margin-right: 0.5rem; color: #D93838;"></i>
        <strong>CRITICAL SECURITY ACTION REQUIRED</strong>
        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; line-height: 1.5;">
            1. The temporary source SQLite database <code>fablepress.db</code> has been <strong>successfully deleted</strong> from the server.<br>
            2. <strong>You MUST delete this migration script file (<code>migrate.php</code>)</strong> from your Hostinger hPanel File Manager or FTP client immediately to prevent unauthorized database wipes.
        </p>
    </div>

    <div style="text-align: center; margin-top: 2rem;">
        <a href="/admin/login/" class="btn-action" style="width: auto; padding: 0.85rem 2rem;">Go to Admin Login &rarr;</a>
    </div>
    <?php
    render_footer();
endif;
?>
