<?php require_once '../bootstrap.php'; 
require_login();

$auth = new \App\Auth();

if (!$auth->isAdmin()) {
    redirect('/dashboard.php');
}

if ($auth->isTOTPVerified()) {
    redirect('/admin/');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    
    if ($auth->verifyTOTP($code)) {
        redirect('/admin/');
    } else {
        $error = 'Invalid verification code. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin MFA Verification - CAFFEINECRASH</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="auth-form">
        <h1>CAFFEINECRASH</h1>
        <h2>Admin MFA Verification</h2>
        
        <?php if ($error): ?>
            <div class="error"><?= sanitize($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="code">Enter your 6-digit code</label>
                <input type="text" id="code" name="code" required pattern="[0-9]{6}" maxlength="6" autofocus>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Verify</button>
        </form>
        
        <div class="text-center">
            <a href="/dashboard.php">Back to Dashboard</a>
        </div>
    </div>
    
    <script src="/js/app.js"></script>
</body>
</html>
