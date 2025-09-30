<?php require_once '../bootstrap.php'; 
require_admin();

$analytics = new \App\Analytics();

$totalUsers = $analytics->getTotalUsers();
$activeUsers = $analytics->getActiveUsers();
$adminUsers = $analytics->getAdminUsers();
$totalMedications = $analytics->getTotalMedications();
$totalHealthRecords = $analytics->getTotalHealthRecords();
$totalReminders = $analytics->getTotalReminders();
$recentUsers = $analytics->getRecentUsers(7);
$recentUsers30 = $analytics->getRecentUsers(30);
$dbSize = $analytics->getDatabaseSize();
$healthDataByType = $analytics->getHealthDataByType();
$topMedications = $analytics->getTopMedications(10);
$userGrowth = $analytics->getUserGrowth(6);
$logStats = $analytics->getLogStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Admin - CAFFEINECRASH</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <?php include '../includes/admin-header.php'; ?>
    
    <div class="container">
        <h1>Analytics Dashboard</h1>
        
        <div class="admin-grid">
            <div class="admin-card">
                <h3>Total Users</h3>
                <p class="admin-stat"><?= $totalUsers ?></p>
                <p class="admin-subtext"><?= $activeUsers ?> active</p>
            </div>
            
            <div class="admin-card">
                <h3>New Users (7d)</h3>
                <p class="admin-stat"><?= $recentUsers ?></p>
            </div>
            
            <div class="admin-card">
                <h3>New Users (30d)</h3>
                <p class="admin-stat"><?= $recentUsers30 ?></p>
            </div>
            
            <div class="admin-card">
                <h3>Admin Users</h3>
                <p class="admin-stat"><?= $adminUsers ?></p>
            </div>
            
            <div class="admin-card">
                <h3>Total Medications</h3>
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
                <h3>Database Size</h3>
                <p class="admin-stat-small"><?= $dbSize ?></p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <h2>User Growth (Last 6 Months)</h2>
                    <?php if (count($userGrowth) > 0): ?>
                        <table class="analytics-table">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>New Users</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userGrowth as $growth): ?>
                                    <tr>
                                        <td><?= sanitize($growth['month']) ?></td>
                                        <td><?= $growth['count'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No user growth data available.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <h2>Health Data by Type</h2>
                    <?php if (count($healthDataByType) > 0): ?>
                        <table class="analytics-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($healthDataByType as $data): ?>
                                    <tr>
                                        <td><?= sanitize($data['data_type']) ?></td>
                                        <td><?= $data['count'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No health data recorded yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <h2>Top Medications</h2>
                    <?php if (count($topMedications) > 0): ?>
                        <table class="analytics-table">
                            <thead>
                                <tr>
                                    <th>Medication</th>
                                    <th>Users</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topMedications as $med): ?>
                                    <tr>
                                        <td><?= sanitize($med['name']) ?></td>
                                        <td><?= $med['count'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No medications recorded yet.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <h2>Log Statistics</h2>
                    <?php if (count($logStats) > 0): ?>
                        <table class="analytics-table">
                            <thead>
                                <tr>
                                    <th>Level</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logStats as $stat): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-<?= sanitize($stat['level']) ?>">
                                                <?= strtoupper(sanitize($stat['level'])) ?>
                                            </span>
                                        </td>
                                        <td><?= $stat['count'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No logs available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="/js/app.js"></script>
</body>
</html>
