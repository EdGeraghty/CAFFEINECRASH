<?php require_once 'bootstrap.php'; 
require_login();

$auth = new \App\Auth();
$share = new \App\Share();
$medication = new \App\Medication();
$reminder = new \App\Reminder();

$userId = $auth->getCurrentUserId();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'share':
                $username = $_POST['username'] ?? '';
                $shareType = $_POST['share_type'] ?? '';
                
                // Find user by username
                $users = $auth->searchUsers($username);
                $targetUser = null;
                foreach ($users as $user) {
                    if ($user['username'] === $username) {
                        $targetUser = $user;
                        break;
                    }
                }
                
                if (!$targetUser) {
                    $error = 'User not found';
                } elseif ($targetUser['id'] == $userId) {
                    $error = 'You cannot share with yourself';
                } else {
                    // Prepare data to share
                    $shareData = [];
                    
                    switch ($shareType) {
                        case 'medications':
                            $shareData['medications'] = $medication->getAll($userId);
                            break;
                        case 'schedule':
                            $shareData['reminders'] = $reminder->getAll($userId);
                            break;
                        case 'summary':
                            $shareData['medications'] = $medication->getAll($userId);
                            $shareData['reminders'] = $reminder->getAll($userId);
                            break;
                    }
                    
                    if ($share->create($userId, $targetUser['id'], $shareType, $shareData)) {
                        $message = 'Successfully shared with ' . $username;
                    } else {
                        $error = 'Failed to share';
                    }
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                if ($share->delete($id, $userId)) {
                    $message = 'Share deleted successfully';
                } else {
                    $error = 'Failed to delete share';
                }
                break;
        }
    }
}

$sharedByMe = $share->getSharedByMe($userId);
$sharedWithMe = $share->getSharedWithMe($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share - CAFFEINECRASH</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4CAF50">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Share</h1>
        
        <?php if ($message): ?>
            <div class="success"><?= sanitize($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?= sanitize($error) ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Share Your Data</h2>
            <form method="POST">
                <input type="hidden" name="action" value="share">
                
                <div class="form-group">
                    <label for="username">Share With User *</label>
                    <input type="text" id="username" name="username" placeholder="Enter username" required>
                </div>
                
                <div class="form-group">
                    <label for="share_type">What to Share *</label>
                    <select id="share_type" name="share_type" required>
                        <option value="">Select...</option>
                        <option value="medications">Medications List</option>
                        <option value="schedule">Reminder Schedule</option>
                        <option value="summary">Complete Summary (Medications + Schedule)</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Share</button>
            </form>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <h2>Shared by Me</h2>
                    <?php if (count($sharedByMe) > 0): ?>
                        <div class="share-list">
                            <?php foreach ($sharedByMe as $s): ?>
                                <div class="share-item">
                                    <h3><?= ucfirst(sanitize($s['share_type'])) ?></h3>
                                    <p><strong>Shared with:</strong> <?= sanitize($s['shared_with_username']) ?></p>
                                    <p><strong>Date:</strong> <?= date('M d, Y', strtotime($s['created_at'])) ?></p>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this share?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>You haven't shared anything yet.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <h2>Shared with Me</h2>
                    <?php if (count($sharedWithMe) > 0): ?>
                        <div class="share-list">
                            <?php foreach ($sharedWithMe as $s): ?>
                                <div class="share-item">
                                    <h3><?= ucfirst(sanitize($s['share_type'])) ?></h3>
                                    <p><strong>From:</strong> <?= sanitize($s['owner_username']) ?></p>
                                    <p><strong>Date:</strong> <?= date('M d, Y', strtotime($s['created_at'])) ?></p>
                                    
                                    <?php if ($s['share_type'] === 'medications' && isset($s['data']['medications'])): ?>
                                        <div class="shared-content">
                                            <h4>Medications:</h4>
                                            <ul>
                                                <?php foreach ($s['data']['medications'] as $med): ?>
                                                    <li><?= sanitize($med['name']) ?> - <?= sanitize($med['dosage'] ?? 'No dosage') ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($s['share_type'] === 'schedule' && isset($s['data']['reminders'])): ?>
                                        <div class="shared-content">
                                            <h4>Reminders:</h4>
                                            <ul>
                                                <?php foreach ($s['data']['reminders'] as $rem): ?>
                                                    <li><?= sanitize($rem['title']) ?> - <?= date('M d, Y H:i', strtotime($rem['remind_at'])) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($s['share_type'] === 'summary'): ?>
                                        <div class="shared-content">
                                            <?php if (isset($s['data']['medications'])): ?>
                                                <h4>Medications:</h4>
                                                <ul>
                                                    <?php foreach ($s['data']['medications'] as $med): ?>
                                                        <li><?= sanitize($med['name']) ?> - <?= sanitize($med['dosage'] ?? 'No dosage') ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                            
                                            <?php if (isset($s['data']['reminders'])): ?>
                                                <h4>Reminders:</h4>
                                                <ul>
                                                    <?php foreach ($s['data']['reminders'] as $rem): ?>
                                                        <li><?= sanitize($rem['title']) ?> - <?= date('M d, Y H:i', strtotime($rem['remind_at'])) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Nothing has been shared with you yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="/js/app.js"></script>
</body>
</html>
