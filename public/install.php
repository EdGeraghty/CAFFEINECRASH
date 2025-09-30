<?php 
require_once 'bootstrap.php';

$installation = new \App\Installation();

// If already installed, redirect to home
if ($installation->isInstalled()) {
    redirect('/index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        $result = $installation->createAdminUser($username, $email, $password);
        
        if ($result['success']) {
            $success = $result['message'];
            // Auto-login the admin user
            $auth = new \App\Auth();
            if ($auth->login($username, $password)) {
                // Redirect to dashboard after a short delay
                header("refresh:2;url=/dashboard.php");
            }
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - CAFFEINECRASH</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4CAF50">
</head>
<body>
    <div class="container">
        <div class="auth-form">
            <h1>🚀 CAFFEINECRASH</h1>
            <h2>Installation Setup</h2>
            
            <?php if ($error): ?>
                <div class="error"><?= sanitize($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success">
                    <?= sanitize($success) ?>
                    <p>Redirecting to dashboard...</p>
                </div>
            <?php else: ?>
                <div class="info-box">
                    <h3>👋 Welcome!</h3>
                    <p>Let's set up your CAFFEINECRASH installation by creating an admin account.</p>
                    <p><strong>Important:</strong> This admin account will remain permanent, even if you use demo mode.</p>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="username">Admin Username</label>
                        <input type="text" id="username" name="username" required 
                               placeholder="Enter admin username" 
                               pattern="[a-zA-Z0-9_-]+" 
                               title="Only letters, numbers, underscores and hyphens allowed">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Admin Email</label>
                        <input type="email" id="email" name="email" required 
                               placeholder="admin@example.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required 
                               minlength="8" 
                               placeholder="Minimum 8 characters">
                        <small>Password must be at least 8 characters long</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               minlength="8" 
                               placeholder="Re-enter password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Complete Installation</button>
                </form>
                
                <div class="info-box" style="margin-top: 20px;">
                    <h4>📋 After Installation</h4>
                    <ul style="text-align: left; margin-left: 20px;">
                        <li>You'll be logged in automatically as the admin user</li>
                        <li>You can manage users via the Admin Panel</li>
                        <li>Demo mode can be used without affecting your admin account</li>
                        <li>Additional users can register if registration is enabled</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <style>
        .info-box {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
        }
        
        .info-box h3, .info-box h4 {
            margin-top: 0;
            color: #1976d2;
        }
        
        .info-box p, .info-box ul {
            margin: 10px 0;
            color: #333;
        }
        
        .info-box ul {
            padding-left: 20px;
        }
        
        .info-box li {
            margin: 5px 0;
        }
        
        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 0.9em;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        
        .success p {
            margin: 10px 0 0 0;
            font-style: italic;
        }
    </style>
</body>
</html>
