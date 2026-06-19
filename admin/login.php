<?php
require_once __DIR__ . '/../config.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    header('Location: /admin/');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if ($username !== '' && $password !== '') {
        $db = get_db_connection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            
            header('Location: /admin/');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FablePress.io Admin</title>
    <link rel="stylesheet" href="/admin/admin.css">
    <style>
        .credentials-hint {
            margin-top: 1.5rem;
            padding: 1rem;
            background-color: var(--color-card);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            font-size: 0.75rem;
            line-height: 1.5;
        }
        .credentials-hint strong {
            display: block;
            margin-bottom: 0.25rem;
            color: var(--color-ink);
        }
    </style>
</head>
<body class="login-body">

    <div class="login-card">
        <div class="login-logo">
            <div class="logo-icon">F</div>
            <h2>FablePress.io</h2>
            <p>Admin Control Panel</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form action="/admin/login/" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="e.g. developer" required autofocus>
            </div>
            
            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.85rem;">Sign In</button>
        </form>
        
        <div class="credentials-hint">
            <strong>Evaluation Default Credentials:</strong>
            • Developer: <code>developer</code> / <code>developer123</code><br>
            • Content Manager: <code>manager</code> / <code>manager123</code><br>
            • Contributor: <code>writer</code> / <code>writer123</code>
        </div>
        
        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.8rem;">
            <a href="/">&larr; Back to Public Homepage</a>
        </div>
    </div>

</body>
</html>
