<?php require_once '../bootstrap.php'; 
require_admin();

$auth = new \App\Auth();
$logger = new \App\Logger();

$userId = $auth->getCurrentUserId();
$user = $auth->getUserById($userId);

$message = '';
$error = '';
$qrCodeUrl = '';
$secret = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'enable':
                if (!$user['totp_secret']) {
                    $secret = $auth->enableTOTP($userId);
                    $qrCodeUrl = \App\TOTP::getQRCodeUrl($secret, 'CAFFEINECRASH', $user['username']);
                    $message = 'TOTP enabled. Scan the QR code with your authenticator app.';
                    $logger->info("TOTP enabled for user", null, $userId);
                    $user['totp_secret'] = $secret;
                } else {
                    $error = 'TOTP is already enabled.';
                }
                break;
                
            case 'disable':
                $code = $_POST['code'] ?? '';
                if ($auth->verifyTOTP($code)) {
                    $auth->disableTOTP($userId);
                    $message = 'TOTP disabled successfully.';
                    $logger->warning("TOTP disabled for user", null, $userId);
                    $user['totp_secret'] = null;
                    $_SESSION['totp_required'] = false;
                    $_SESSION['totp_verified'] = true;
                } else {
                    $error = 'Invalid code. TOTP not disabled.';
                }
                break;
        }
    }
}

if ($user['totp_secret'] && !$qrCodeUrl) {
    $qrCodeUrl = \App\TOTP::getQRCodeUrl($user['totp_secret'], 'CAFFEINECRASH', $user['username']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFA Settings - Admin - CAFFEINECRASH</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <?php include '../includes/admin-header.php'; ?>
    
    <div class="container">
        <h1>Multi-Factor Authentication (MFA) Settings</h1>
        
        <?php if ($message): ?>
            <div class="success"><?= sanitize($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?= sanitize($error) ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>TOTP Status</h2>
            <?php if ($user['totp_secret']): ?>
                <p><strong>Status:</strong> <span class="badge badge-success">Enabled</span></p>
                
                <?php if ($qrCodeUrl): ?>
                    <h3>QR Code</h3>
                    <p>Scan this QR code with your authenticator app (e.g., Google Authenticator, Authy):</p>
                    <div class="qr-code">
                        <img src="<?= sanitize($qrCodeUrl) ?>" alt="TOTP QR Code">
                    </div>
                    <p><strong>Secret Key:</strong> <code><?= sanitize($user['totp_secret']) ?></code></p>
                    <p class="text-muted">Save this secret key in a safe place. You can use it to manually add the account to your authenticator app.</p>
                <?php endif; ?>
                
                <h3>Disable TOTP</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="disable">
                    <div class="form-group">
                        <label for="code">Enter current TOTP code to disable</label>
                        <input type="text" id="code" name="code" required pattern="[0-9]{6}" maxlength="6">
                    </div>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to disable MFA?')">
                        Disable TOTP
                    </button>
                </form>
            <?php else: ?>
                <p><strong>Status:</strong> <span class="badge badge-default">Disabled</span></p>
                <p>Enable TOTP to add an extra layer of security to your admin account.</p>
                
                <form method="POST">
                    <input type="hidden" name="action" value="enable">
                    <button type="submit" class="btn btn-primary">Enable TOTP</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="/js/app.js"></script>
</body>
</html>
