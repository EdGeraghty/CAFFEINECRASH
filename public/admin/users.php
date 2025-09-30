<?php require_once '../bootstrap.php'; 
require_admin();

$auth = new \App\Auth();
$adminUser = new \App\AdminUser();
$logger = new \App\Logger();

$currentUserId = $auth->getCurrentUserId();
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $userId = (int)($_POST['user_id'] ?? 0);
        
        switch ($_POST['action']) {
            case 'toggle_admin':
                if ($userId !== $currentUserId) {
                    if ($adminUser->toggleAdmin($userId)) {
                        $message = 'User admin status updated';
                        $logger->info("Admin status toggled for user ID: $userId", null, $currentUserId);
                    } else {
                        $error = 'Failed to update admin status';
                    }
                } else {
                    $error = 'Cannot modify your own admin status';
                }
                break;
                
            case 'toggle_active':
                if ($userId !== $currentUserId) {
                    if ($adminUser->toggleActive($userId)) {
                        $message = 'User active status updated';
                        $logger->info("Active status toggled for user ID: $userId", null, $currentUserId);
                    } else {
                        $error = 'Failed to update active status';
                    }
                } else {
                    $error = 'Cannot deactivate your own account';
                }
                break;
                
            case 'reset_password':
                $newPassword = $_POST['new_password'] ?? '';
                if (strlen($newPassword) >= 8) {
                    if ($adminUser->resetPassword($userId, $newPassword)) {
                        $message = 'Password reset successfully';
                        $logger->warning("Password reset for user ID: $userId", null, $currentUserId);
                    } else {
                        $error = 'Failed to reset password';
                    }
                } else {
                    $error = 'Password must be at least 8 characters';
                }
                break;
                
            case 'delete_user':
                if ($userId !== $currentUserId) {
                    if ($adminUser->deleteUser($userId)) {
                        $message = 'User deleted successfully';
                        $logger->warning("User deleted: ID $userId", null, $currentUserId);
                    } else {
                        $error = 'Failed to delete user';
                    }
                } else {
                    $error = 'Cannot delete your own account';
                }
                break;
        }
    }
}

$page = (int)($_GET['page'] ?? 1);
$perPage = 20;
$users = $adminUser->getAllUsers($page, $perPage);
$totalUsers = $adminUser->getTotalUsers();
$totalPages = ceil($totalUsers / $perPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin - CAFFEINECRASH</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <?php include '../includes/admin-header.php'; ?>
    
    <div class="container">
        <h1>User Management</h1>
        
        <?php if ($message): ?>
            <div class="success"><?= sanitize($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?= sanitize($error) ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Users (<?= $totalUsers ?>)</h2>
            
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Admin</th>
                            <th>Active</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= sanitize($user['username']) ?></td>
                                <td><?= sanitize($user['email']) ?></td>
                                <td>
                                    <?php if ($user['is_admin']): ?>
                                        <span class="badge badge-success">Yes</span>
                                    <?php else: ?>
                                        <span class="badge badge-default">No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['is_active']): ?>
                                        <span class="badge badge-success">Yes</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">No</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($user['id'] !== $currentUserId): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_admin">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Toggle admin status?')">
                                                    <?= $user['is_admin'] ? 'Remove Admin' : 'Make Admin' ?>
                                                </button>
                                            </form>
                                            
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_active">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Toggle active status?')">
                                                    <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                                                </button>
                                            </form>
                                            
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="showPasswordReset(<?= $user['id'] ?>)">
                                                Reset Password
                                            </button>
                                            
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                    Delete
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <em>Current User</em>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="page-current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?>" class="page-link"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div id="passwordResetModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Reset Password</h2>
            <form method="POST">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="user_id" id="reset_user_id">
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                    <button type="button" class="btn btn-secondary" onclick="closePasswordReset()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="/js/app.js"></script>
    <script>
        function showPasswordReset(userId) {
            document.getElementById('reset_user_id').value = userId;
            document.getElementById('passwordResetModal').style.display = 'flex';
        }
        
        function closePasswordReset() {
            document.getElementById('passwordResetModal').style.display = 'none';
        }
    </script>
</body>
</html>
