<?php require_once '../bootstrap.php'; 
require_admin();

$logger = new \App\Logger();

$page = (int)($_GET['page'] ?? 1);
$perPage = 50;
$level = $_GET['level'] ?? null;
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

$logs = $logger->getLogs($page, $perPage, $level, $userId);
$totalLogs = $logger->getTotalLogs($level, $userId);
$totalPages = ceil($totalLogs / $perPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs - Admin - CAFFEINECRASH</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <?php include '../includes/admin-header.php'; ?>
    
    <div class="container">
        <h1>System Logs</h1>
        
        <div class="card">
            <h2>Filters</h2>
            <form method="GET" class="log-filters">
                <div class="form-group">
                    <label for="level">Level</label>
                    <select id="level" name="level">
                        <option value="">All Levels</option>
                        <option value="debug" <?= $level === 'debug' ? 'selected' : '' ?>>Debug</option>
                        <option value="info" <?= $level === 'info' ? 'selected' : '' ?>>Info</option>
                        <option value="warning" <?= $level === 'warning' ? 'selected' : '' ?>>Warning</option>
                        <option value="error" <?= $level === 'error' ? 'selected' : '' ?>>Error</option>
                        <option value="critical" <?= $level === 'critical' ? 'selected' : '' ?>>Critical</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="user_id">User ID</label>
                    <input type="number" id="user_id" name="user_id" value="<?= $userId ?? '' ?>" placeholder="Optional">
                </div>
                
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="/admin/logs.php" class="btn btn-secondary">Clear</a>
            </form>
        </div>
        
        <div class="card">
            <h2>Logs (<?= $totalLogs ?>)</h2>
            
            <div class="logs-container">
                <?php if (count($logs) > 0): ?>
                    <?php foreach ($logs as $log): ?>
                        <div class="log-entry log-<?= sanitize($log['level']) ?>">
                            <div class="log-header">
                                <span class="log-level badge badge-<?= sanitize($log['level']) ?>">
                                    <?= strtoupper(sanitize($log['level'])) ?>
                                </span>
                                <span class="log-date"><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></span>
                                <?php if ($log['username']): ?>
                                    <span class="log-user">User: <?= sanitize($log['username']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="log-message"><?= sanitize($log['message']) ?></div>
                            <?php if ($log['context']): ?>
                                <div class="log-context">
                                    <details>
                                        <summary>Context</summary>
                                        <pre><?= sanitize($log['context']) ?></pre>
                                    </details>
                                </div>
                            <?php endif; ?>
                            <div class="log-meta">
                                <?php if ($log['ip_address']): ?>
                                    <span>IP: <?= sanitize($log['ip_address']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No logs found.</p>
                <?php endif; ?>
            </div>
            
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php
                    $queryParams = [];
                    if ($level) $queryParams[] = "level=$level";
                    if ($userId) $queryParams[] = "user_id=$userId";
                    $queryString = $queryParams ? '&' . implode('&', $queryParams) : '';
                    ?>
                    <?php for ($i = 1; $i <= min($totalPages, 10); $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="page-current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?><?= $queryString ?>" class="page-link"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($totalPages > 10): ?>
                        <span>...</span>
                        <a href="?page=<?= $totalPages ?><?= $queryString ?>" class="page-link"><?= $totalPages ?></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="/js/app.js"></script>
</body>
</html>
