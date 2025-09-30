<?php require_once 'bootstrap.php'; 

// Check if installation is complete
$installation = new \App\Installation();
if (!$installation->isInstalled()) {
    redirect('/install.php');
}

$auth = new \App\Auth();
if ($auth->isLoggedIn()) {
    redirect('/dashboard.php');
}

$settings = new \App\Settings();
$registrationEnabled = $settings->isRegistrationEnabled();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$registrationEnabled) {
        $error = 'Registration is currently disabled by the administrator';
    } else {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters';
        } elseif ($auth->register($username, $email, $password)) {
            $success = 'Registration successful! You can now login.';
        } else {
            $error = 'Username or email already exists';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CAFFEINECRASH</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4CAF50">
</head>
<body>
    <div class="container">
        <div class="auth-form">
            <h1>CAFFEINECRASH</h1>
            <h2>Register</h2>
            <?php if ($error): ?>
                <div class="error"><?= sanitize($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success"><?= sanitize($success) ?></div>
            <?php endif; ?>
            <?php if (!$registrationEnabled): ?>
                <div class="error">Registration is currently disabled by the administrator. Please contact the system administrator if you need an account.</div>
            <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
            <?php endif; ?>
            <p class="text-center">Already have an account? <a href="/login.php">Login</a></p>
            <p class="text-center">Want to try a demo first? <a href="/demo.php">Demo Mode</a></p>
        </div>
    </div>
</body>
</html>
