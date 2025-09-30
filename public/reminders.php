<?php require_once 'bootstrap.php'; 
require_login();

$auth = new \App\Auth();
$reminder = new \App\Reminder();
$medication = new \App\Medication();

$userId = $auth->getCurrentUserId();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $data = [
                    'medication_id' => $_POST['medication_id'] ?: null,
                    'title' => $_POST['title'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'remind_at' => $_POST['remind_at'] ?? ''
                ];
                
                if ($reminder->create($userId, $data)) {
                    $message = 'Reminder added successfully';
                } else {
                    $error = 'Failed to add reminder';
                }
                break;
                
            case 'complete':
                $id = (int)$_POST['id'];
                if ($reminder->markCompleted($id, $userId)) {
                    $message = 'Reminder marked as completed';
                } else {
                    $error = 'Failed to mark reminder as completed';
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                if ($reminder->delete($id, $userId)) {
                    $message = 'Reminder deleted successfully';
                } else {
                    $error = 'Failed to delete reminder';
                }
                break;
        }
    }
}

$reminders = $reminder->getAll($userId);
$medications = $medication->getAll($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminders - CAFFEINECRASH</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4CAF50">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Reminders</h1>
        
        <?php if ($message): ?>
            <div class="success"><?= sanitize($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?= sanitize($error) ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <h2>Add Reminder</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-group">
                            <label for="title">Title *</label>
                            <input type="text" id="title" name="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="medication_id">Medication (optional)</label>
                            <select id="medication_id" name="medication_id">
                                <option value="">No medication</option>
                                <?php foreach ($medications as $med): ?>
                                    <option value="<?= $med['id'] ?>"><?= sanitize($med['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="remind_at">Remind At *</label>
                            <input type="datetime-local" id="remind_at" name="remind_at" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" placeholder="Additional details"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Add Reminder</button>
                    </form>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <h2>Your Reminders</h2>
                    <?php if (count($reminders) > 0): ?>
                        <div class="reminder-list">
                            <?php foreach ($reminders as $rem): ?>
                                <div class="reminder-item <?= $rem['is_completed'] ? 'completed' : '' ?>">
                                    <h3><?= sanitize($rem['title']) ?></h3>
                                    <?php if ($rem['medication_name']): ?>
                                        <p><strong>Medication:</strong> <?= sanitize($rem['medication_name']) ?></p>
                                    <?php endif; ?>
                                    <p><strong>When:</strong> <?= date('M d, Y H:i', strtotime($rem['remind_at'])) ?></p>
                                    <?php if ($rem['description']): ?>
                                        <p><?= sanitize($rem['description']) ?></p>
                                    <?php endif; ?>
                                    <div class="reminder-actions">
                                        <?php if (!$rem['is_completed']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="complete">
                                                <input type="hidden" name="id" value="<?= $rem['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-secondary">Mark Complete</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this reminder?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $rem['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No reminders set yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="/js/app.js"></script>
</body>
</html>
