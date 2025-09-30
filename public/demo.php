<?php 
require_once 'bootstrap.php';

$demoData = new \App\DemoData();
$message = '';
$error = '';
$results = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $results = $demoData->createDemoData();
            if ($results['success']) {
                $message = $results['message'];
            } else {
                $error = $results['message'];
            }
        } elseif ($_POST['action'] === 'clear') {
            if ($demoData->clearDemoData()) {
                $message = 'Demo data cleared successfully!';
            } else {
                $error = 'Failed to clear demo data.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Mode - CAFFEINECRASH</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container">
        <h1>🎭 Demo Mode</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?= sanitize($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= sanitize($error) ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Create Demo Data</h2>
            <p>This will create sample users, medications, health data, and reminders to demonstrate the application.</p>
            
            <form method="POST" onsubmit="return confirm('This will create demo users and sample data. Continue?');">
                <input type="hidden" name="action" value="create">
                <button type="submit" class="btn btn-primary">Create Demo Data</button>
            </form>
            
            <?php if ($results && $results['success']): ?>
                <div class="demo-credentials">
                    <h3>Demo User Credentials</h3>
                    <p>Use these credentials to login and explore the application:</p>
                    
                    <?php foreach ($results['users'] as $user): ?>
                        <div class="credential-box">
                            <strong>Username:</strong> <?= sanitize($user['username']) ?><br>
                            <strong>Password:</strong> <?= sanitize($user['password']) ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <p class="info">
                        <strong>What's included:</strong>
                    </p>
                    <ul>
                        <li>✓ Sample medications with dosages and prescriber information</li>
                        <li>✓ Health data entries (weight, blood pressure, heart rate, blood sugar, GAD-7 scores)</li>
                        <li>✓ Upcoming and completed reminders</li>
                        <li>✓ Data spanning the past 30 days for trend analysis</li>
                    </ul>
                    
                    <a href="/login.php" class="btn btn-success">Go to Login</a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>Clear Demo Data</h2>
            <p>Remove all demo users and their associated data from the database.</p>
            
            <form method="POST" onsubmit="return confirm('This will permanently delete all demo users and their data. Continue?');">
                <input type="hidden" name="action" value="clear">
                <button type="submit" class="btn btn-danger">Clear Demo Data</button>
            </form>
        </div>
        
        <div class="card">
            <h2>About Demo Mode</h2>
            <p>Demo mode creates realistic sample data to help you:</p>
            <ul>
                <li>Explore all features of CAFFEINECRASH</li>
                <li>Test the application before adding your own data</li>
                <li>Demonstrate the app to others</li>
                <li>Understand how to use different features</li>
            </ul>
            
            <p><strong>Note:</strong> Demo data is clearly marked with "demo_" prefix in usernames for easy identification.</p>
        </div>
        
        <div class="navigation">
            <a href="/index.php" class="btn">← Back to Home</a>
        </div>
    </div>
    
    <style>
        .demo-credentials {
            margin-top: 20px;
            padding: 20px;
            background: #f0f9ff;
            border-radius: 8px;
            border-left: 4px solid #0066cc;
        }
        
        .credential-box {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-family: monospace;
        }
        
        .info {
            margin-top: 20px;
            font-weight: bold;
            color: #0066cc;
        }
        
        .navigation {
            margin-top: 30px;
            text-align: center;
        }
        
        .card {
            margin-bottom: 30px;
        }
        
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
    </style>
</body>
</html>
