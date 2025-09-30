<?php require_once 'bootstrap.php'; 
require_login();

$auth = new \App\Auth();
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
                    'name' => $_POST['name'] ?? '',
                    'dosage' => $_POST['dosage'] ?? '',
                    'frequency' => $_POST['frequency'] ?? '',
                    'prescriber' => $_POST['prescriber'] ?? '',
                    'prescribed_for' => $_POST['prescribed_for'] ?? '',
                    'notes' => $_POST['notes'] ?? ''
                ];
                
                if ($medication->create($userId, $data)) {
                    $message = 'Medication added successfully';
                } else {
                    $error = 'Failed to add medication';
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $data = [
                    'name' => $_POST['name'] ?? '',
                    'dosage' => $_POST['dosage'] ?? '',
                    'frequency' => $_POST['frequency'] ?? '',
                    'prescriber' => $_POST['prescriber'] ?? '',
                    'prescribed_for' => $_POST['prescribed_for'] ?? '',
                    'notes' => $_POST['notes'] ?? ''
                ];
                
                if ($medication->update($id, $userId, $data)) {
                    $message = 'Medication updated successfully';
                } else {
                    $error = 'Failed to update medication';
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                if ($medication->delete($id, $userId)) {
                    $message = 'Medication deleted successfully';
                } else {
                    $error = 'Failed to delete medication';
                }
                break;
        }
    }
}

$medications = $medication->getAll($userId);
$editMed = null;
if (isset($_GET['edit'])) {
    $editMed = $medication->getById((int)$_GET['edit'], $userId);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medications - CAFFEINECRASH</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4CAF50">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Medications</h1>
        
        <?php if ($message): ?>
            <div class="success"><?= sanitize($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?= sanitize($error) ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <h2><?= $editMed ? 'Edit Medication' : 'Add Medication' ?></h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="<?= $editMed ? 'edit' : 'add' ?>">
                        <?php if ($editMed): ?>
                            <input type="hidden" name="id" value="<?= $editMed['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="name">Medication Name *</label>
                            <input type="text" id="name" name="name" value="<?= $editMed ? sanitize($editMed['name']) : '' ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="dosage">Dosage</label>
                            <input type="text" id="dosage" name="dosage" value="<?= $editMed ? sanitize($editMed['dosage']) : '' ?>" placeholder="e.g., 10mg">
                        </div>
                        
                        <div class="form-group">
                            <label for="frequency">Frequency</label>
                            <input type="text" id="frequency" name="frequency" value="<?= $editMed ? sanitize($editMed['frequency']) : '' ?>" placeholder="e.g., Twice daily">
                        </div>
                        
                        <div class="form-group">
                            <label for="prescriber">Prescriber</label>
                            <input type="text" id="prescriber" name="prescriber" value="<?= $editMed ? sanitize($editMed['prescriber']) : '' ?>" placeholder="Doctor's name">
                        </div>
                        
                        <div class="form-group">
                            <label for="prescribed_for">Prescribed For</label>
                            <textarea id="prescribed_for" name="prescribed_for" placeholder="What is this medication for?"><?= $editMed ? sanitize($editMed['prescribed_for']) : '' ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea id="notes" name="notes" placeholder="Additional notes"><?= $editMed ? sanitize($editMed['notes']) : '' ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary"><?= $editMed ? 'Update' : 'Add' ?> Medication</button>
                        <?php if ($editMed): ?>
                            <a href="/medications.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <h2>Your Medications</h2>
                    <?php if (count($medications) > 0): ?>
                        <div class="medication-list">
                            <?php foreach ($medications as $med): ?>
                                <div class="medication-item">
                                    <h3><?= sanitize($med['name']) ?></h3>
                                    <?php if ($med['dosage']): ?>
                                        <p><strong>Dosage:</strong> <?= sanitize($med['dosage']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($med['frequency']): ?>
                                        <p><strong>Frequency:</strong> <?= sanitize($med['frequency']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($med['prescriber']): ?>
                                        <p><strong>Prescriber:</strong> <?= sanitize($med['prescriber']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($med['prescribed_for']): ?>
                                        <p><strong>Prescribed For:</strong> <?= sanitize($med['prescribed_for']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($med['notes']): ?>
                                        <p><strong>Notes:</strong> <?= sanitize($med['notes']) ?></p>
                                    <?php endif; ?>
                                    <div class="medication-actions">
                                        <a href="/medications.php?edit=<?= $med['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this medication?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $med['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No medications added yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="/js/app.js"></script>
</body>
</html>
