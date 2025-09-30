<?php require_once 'bootstrap.php'; 
require_login();

$auth = new \App\Auth();
$medication = new \App\Medication();
$healthData = new \App\HealthData();
$reminder = new \App\Reminder();

$userId = $auth->getCurrentUserId();
$username = $auth->getCurrentUsername();

$medications = $medication->getAll($userId);
$healthRecords = $healthData->getAll($userId);
$reminders = $reminder->getPending($userId);

// Get latest health data
$latestGAD7 = $healthData->getLatest($userId, 'GAD7');
$latestWeight = $healthData->getLatest($userId, 'weight');
$latestBMI = $healthData->getLatest($userId, 'BMI');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CAFFEINECRASH</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4CAF50">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Welcome, <?= sanitize($username) ?>!</h1>
        
        <div class="dashboard-grid">
            <div class="card">
                <h2>Medications</h2>
                <p class="stat"><?= count($medications) ?></p>
                <a href="/medications.php" class="btn btn-secondary">Manage Medications</a>
            </div>
            
            <div class="card">
                <h2>Health Data</h2>
                <p class="stat"><?= count($healthRecords) ?></p>
                <a href="/health.php" class="btn btn-secondary">Track Health</a>
            </div>
            
            <div class="card">
                <h2>Reminders</h2>
                <p class="stat"><?= count($reminders) ?> pending</p>
                <a href="/reminders.php" class="btn btn-secondary">View Reminders</a>
            </div>
            
            <div class="card">
                <h2>Share</h2>
                <p>Share your schedule with others</p>
                <a href="/share.php" class="btn btn-secondary">Manage Sharing</a>
            </div>
        </div>
        
        <div class="health-summary">
            <h2>Latest Health Metrics</h2>
            <div class="metrics-grid">
                <?php if ($latestGAD7): ?>
                    <div class="metric">
                        <span class="metric-label">GAD-7 Score</span>
                        <span class="metric-value"><?= sanitize($latestGAD7['value']) ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($latestWeight): ?>
                    <div class="metric">
                        <span class="metric-label">Weight</span>
                        <span class="metric-value"><?= sanitize($latestWeight['value']) ?> <?= sanitize($latestWeight['unit'] ?? 'kg') ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($latestBMI): ?>
                    <div class="metric">
                        <span class="metric-label">BMI</span>
                        <span class="metric-value"><?= sanitize($latestBMI['value']) ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!$latestGAD7 && !$latestWeight && !$latestBMI): ?>
                    <p>No health data recorded yet. <a href="/health.php">Add your first entry</a></p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (count($reminders) > 0): ?>
            <div class="upcoming-reminders">
                <h2>Upcoming Reminders</h2>
                <ul class="reminder-list">
                    <?php foreach (array_slice($reminders, 0, 5) as $rem): ?>
                        <li>
                            <strong><?= sanitize($rem['title']) ?></strong>
                            <?php if ($rem['medication_name']): ?>
                                - <?= sanitize($rem['medication_name']) ?>
                            <?php endif; ?>
                            <br>
                            <small><?= date('M d, Y H:i', strtotime($rem['remind_at'])) ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="/js/app.js"></script>
</body>
</html>
