<?php require_once 'bootstrap.php'; 
require_login();

$auth = new \App\Auth();
$healthData = new \App\HealthData();

$userId = $auth->getCurrentUserId();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $dataType = $_POST['data_type'] ?? '';
                $value = (float)($_POST['value'] ?? 0);
                $unit = $_POST['unit'] ?? null;
                $notes = $_POST['notes'] ?? null;
                
                // Special handling for BMI calculation
                if ($dataType === 'BMI' && isset($_POST['weight']) && isset($_POST['height'])) {
                    $weight = (float)$_POST['weight'];
                    $height = (float)$_POST['height'];
                    $value = $healthData->calculateBMI($weight, $height);
                }
                
                if ($healthData->create($userId, $dataType, $value, $unit, $notes)) {
                    $message = 'Health data added successfully';
                } else {
                    $error = 'Failed to add health data';
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                if ($healthData->delete($id, $userId)) {
                    $message = 'Health data deleted successfully';
                } else {
                    $error = 'Failed to delete health data';
                }
                break;
        }
    }
}

$allHealthData = $healthData->getAll($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Data - CAFFEINECRASH</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4CAF50">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Health Data</h1>
        
        <?php if ($message): ?>
            <div class="success"><?= sanitize($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?= sanitize($error) ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <h2>Add Health Data</h2>
                    <form method="POST" id="healthDataForm">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-group">
                            <label for="data_type">Data Type *</label>
                            <select id="data_type" name="data_type" required onchange="toggleBMIFields()">
                                <option value="">Select type...</option>
                                <option value="GAD7">GAD-7 Score (Anxiety)</option>
                                <option value="weight">Weight</option>
                                <option value="BMI">BMI (Body Mass Index)</option>
                                <option value="blood_pressure">Blood Pressure</option>
                                <option value="heart_rate">Heart Rate</option>
                                <option value="temperature">Temperature</option>
                                <option value="blood_sugar">Blood Sugar</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div id="normalValueFields">
                            <div class="form-group">
                                <label for="value">Value *</label>
                                <input type="number" step="0.01" id="value" name="value" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="unit">Unit</label>
                                <input type="text" id="unit" name="unit" placeholder="e.g., kg, lbs, mmHg">
                            </div>
                        </div>
                        
                        <div id="bmiFields" style="display: none;">
                            <div class="form-group">
                                <label for="weight">Weight (kg) *</label>
                                <input type="number" step="0.1" id="weight" name="weight">
                            </div>
                            
                            <div class="form-group">
                                <label for="height">Height (meters) *</label>
                                <input type="number" step="0.01" id="height" name="height" placeholder="e.g., 1.75">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea id="notes" name="notes" placeholder="Additional notes"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Add Data</button>
                    </form>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <h2>Your Health Data</h2>
                    <?php if (count($allHealthData) > 0): ?>
                        <div class="health-data-list">
                            <?php foreach ($allHealthData as $data): ?>
                                <div class="health-data-item">
                                    <div class="data-header">
                                        <strong><?= sanitize($data['data_type']) ?></strong>
                                        <span class="data-date"><?= date('M d, Y', strtotime($data['recorded_at'])) ?></span>
                                    </div>
                                    <p class="data-value">
                                        <?= sanitize($data['value']) ?>
                                        <?php if ($data['unit']): ?>
                                            <?= sanitize($data['unit']) ?>
                                        <?php endif; ?>
                                    </p>
                                    <?php if ($data['notes']): ?>
                                        <p class="data-notes"><?= sanitize($data['notes']) ?></p>
                                    <?php endif; ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this entry?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $data['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No health data recorded yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function toggleBMIFields() {
            const dataType = document.getElementById('data_type').value;
            const normalFields = document.getElementById('normalValueFields');
            const bmiFields = document.getElementById('bmiFields');
            const valueInput = document.getElementById('value');
            const weightInput = document.getElementById('weight');
            const heightInput = document.getElementById('height');
            
            if (dataType === 'BMI') {
                normalFields.style.display = 'none';
                bmiFields.style.display = 'block';
                valueInput.removeAttribute('required');
                weightInput.setAttribute('required', 'required');
                heightInput.setAttribute('required', 'required');
            } else {
                normalFields.style.display = 'block';
                bmiFields.style.display = 'none';
                valueInput.setAttribute('required', 'required');
                weightInput.removeAttribute('required');
                heightInput.removeAttribute('required');
            }
        }
    </script>
    <script src="/js/app.js"></script>
</body>
</html>
