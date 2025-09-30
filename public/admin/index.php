<?php require_once '../bootstrap.php'; 
require_admin();

$auth = new \App\Auth();
$analytics = new \App\Analytics();

$totalUsers = $analytics->getTotalUsers();
$activeUsers = $analytics->getActiveUsers();
$adminUsers = $analytics->getAdminUsers();
$totalMedications = $analytics->getTotalMedications();
$totalHealthRecords = $analytics->getTotalHealthRecords();
$totalReminders = $analytics->getTotalReminders();
$recentUsers = $analytics->getRecentUsers(7);
$dbSize = $analytics->getDatabaseSize();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CAFFEINECRASH</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <?php include '../includes/admin-header.php'; ?>
    
    <div class="container">
        <h1>Admin Dashboard</h1>
        
        <div class="admin-grid">
            <div class="admin-card">
                <h3>Total Users</h3>
                <p class="admin-stat"><?= $totalUsers ?></p>
                <p class="admin-subtext"><?= $activeUsers ?> active</p>
            </div>
            
            <div class="admin-card">
                <h3>Admin Users</h3>
                <p class="admin-stat"><?= $adminUsers ?></p>
            </div>
            
            <div class="admin-card">
                <h3>Medications</h3>
                <p class="admin-stat"><?= $totalMedications ?></p>
            </div>
            
            <div class="admin-card">
                <h3>Health Records</h3>
                <p class="admin-stat"><?= $totalHealthRecords ?></p>
            </div>
            
            <div class="admin-card">
                <h3>Reminders</h3>
                <p class="admin-stat"><?= $totalReminders ?></p>
            </div>
            
            <div class="admin-card">
                <h3>New Users (7d)</h3>
                <p class="admin-stat"><?= $recentUsers ?></p>
            </div>
            
            <div class="admin-card">
                <h3>Database Size</h3>
                <p class="admin-stat-small"><?= $dbSize ?></p>
            </div>
        </div>
        
        <div class="admin-sections">
            <div class="card">
                <h2>Quick Actions</h2>
                <div class="admin-actions">
                    <a href="/admin/users.php" class="btn btn-primary">Manage Users</a>
                    <a href="/admin/logs.php" class="btn btn-secondary">View Logs</a>
                    <a href="/admin/analytics.php" class="btn btn-secondary">Analytics</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="/js/app.js"></script>
</body>
</html>
