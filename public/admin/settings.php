<?php require_once '../bootstrap.php'; 
require_admin();

$auth = new \App\Auth();
$settings = new \App\Settings();
$logger = new \App\Logger();

$userId = $auth->getCurrentUserId();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'toggle_registration':
                $enabled = isset($_POST['registration_enabled']) && $_POST['registration_enabled'] === '1';
                if ($settings->setRegistrationEnabled($enabled)) {
                    $message = 'Registration setting updated successfully.';
                    $logger->info("Registration " . ($enabled ? 'enabled' : 'disabled'), null, $userId);
                } else {
                    $error = 'Failed to update registration setting.';
                }
                break;
        }
    }
}

$registrationEnabled = $settings->isRegistrationEnabled();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Admin - CAFFEINECRASH</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <?php include '../includes/admin-header.php'; ?>
    
    <div class="container">
        <h1>System Settings</h1>
        
        <?php if ($message): ?>
            <div class="success"><?= sanitize($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?= sanitize($error) ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Registration Settings</h2>
            <p>Control whether new users can register accounts on the system.</p>
            
            <form method="POST">
                <input type="hidden" name="action" value="toggle_registration">
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="registration_enabled" value="1" <?= $registrationEnabled ? 'checked' : '' ?>>
                        Allow new user registration
                    </label>
                    <p class="form-help">When disabled, the registration page will display a message that registration is currently disabled.</p>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>
    
    <script src="/js/app.js"></script>
</body>
</html>
