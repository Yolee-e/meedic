<?php
/**
 * Database Backup Management Interface
 * 
 * This page provides an admin interface for managing database backups
 * including manual backup creation and download functionality.
 */

session_start();

// Check if user is logged in - redirect to main login page
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Include the backup class
require_once __DIR__ . '/maintenance/database_backup.php';

$backup = new DatabaseBackup();
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_backup'])) {
        $force = isset($_POST['force_backup']);
        $result = $backup->createBackup($force);
        
        if ($result['success']) {
            $message = "Backup created successfully: {$result['filename']} ({$result['size']})";
        } else {
            $error = "Backup failed: {$result['message']}";
        }
    }
}

// Get current status
$isBackupNeeded = $backup->isBackupNeeded();
$backups = $backup->getBackupList();
$recentLogs = $backup->getLogContent(20);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .backup-card {
            transition: transform 0.2s;
        }
        .backup-card:hover {
            transform: translateY(-2px);
        }
        .log-container {
            max-height: 400px;
            overflow-y: auto;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
        }
        .log-entry {
            margin-bottom: 0.5rem;
            padding: 0.25rem;
            border-radius: 0.25rem;
        }
        .log-info { background-color: #d1ecf1; }
        .log-warning { background-color: #fff3cd; }
        .log-error { background-color: #f8d7da; }
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .status-needed { background-color: #dc3545; }
        .status-ok { background-color: #28a745; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-database"></i> Database Backup Management</h1>
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <!-- Status Alert -->
                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Backup Status -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-info-circle"></i> Backup Status
                                </h5>
                                <div class="d-flex align-items-center">
                                    <span class="status-indicator <?php echo $isBackupNeeded ? 'status-needed' : 'status-ok'; ?>"></span>
                                    <span>
                                        <?php if ($isBackupNeeded): ?>
                                            Backup needed (last backup was more than 3 days ago)
                                        <?php else: ?>
                                            Backup up to date
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="mt-3">
                                    <form method="POST" class="d-inline">
                                        <button type="submit" name="create_backup" class="btn btn-primary">
                                            <i class="fas fa-download"></i> Create Backup Now
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline ms-2">
                                        <button type="submit" name="create_backup" class="btn btn-warning">
                                            <i class="fas fa-sync"></i> Force Backup
                                        </button>
                                        <input type="hidden" name="force_backup" value="1">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-chart-pie"></i> Backup Statistics
                                </h5>
                                <p class="card-text">
                                    <strong>Total Backups:</strong> <?php echo count($backups); ?><br>
                                    <strong>Storage Used:</strong> 
                                    <?php 
                                    $totalSize = 0;
                                    foreach ($backups as $backupItem) {
                                        $totalSize += filesize($backupItem['filepath']);
                                    }
                                    echo $backup->formatFileSize($totalSize);
                                    ?><br>
                                    <strong>Retention:</strong> Latest 5 backups
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Backups -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-archive"></i> Available Backups
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($backups)): ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No backups available yet.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($backups as $backupItem): ?>
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="card backup-card h-100">
                                                    <div class="card-body">
                                                        <h6 class="card-title">
                                                            <i class="fas fa-file-archive"></i>
                                                            <?php echo htmlspecialchars($backupItem['filename']); ?>
                                                        </h6>
                                                        <p class="card-text">
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar"></i> 
                                                                <?php echo $backupItem['created']; ?><br>
                                                                <i class="fas fa-weight"></i> 
                                                                <?php echo $backupItem['size']; ?>
                                                            </small>
                                                        </p>
                                                    </div>
                                                    <div class="card-footer">
                                                        <a href="maintenance/database_backup.php?action=download&file=<?php echo urlencode($backupItem['filename']); ?>"
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-download"></i> Download
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Logs -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-list"></i> Recent Backup Logs
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="log-container">
                                    <?php if (empty($recentLogs)): ?>
                                        <div class="text-muted">No logs available yet.</div>
                                    <?php else: ?>
                                        <?php foreach ($recentLogs as $log): ?>
                                            <?php
                                            $logClass = 'log-info';
                                            if (strpos($log, '[ERROR]') !== false) {
                                                $logClass = 'log-error';
                                            } elseif (strpos($log, '[WARNING]') !== false) {
                                                $logClass = 'log-warning';
                                            }
                                            ?>
                                            <div class="log-entry <?php echo $logClass; ?>">
                                                <?php echo htmlspecialchars($log); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh logs every 30 seconds
        setInterval(function() {
            fetch('maintenance/database_backup.php?action=logs&lines=20')
                .then(response => response.json())
                .then(logs => {
                    const logContainer = document.querySelector('.log-container');
                    logContainer.innerHTML = logs.map(log => {
                        let logClass = 'log-info';
                        if (log.includes('[ERROR]')) logClass = 'log-error';
                        else if (log.includes('[WARNING]')) logClass = 'log-warning';
                        
                        return `<div class="log-entry ${logClass}">${log}</div>`;
                    }).join('');
                })
                .catch(error => console.error('Error fetching logs:', error));
        }, 30000);

        // Confirm force backup
        document.querySelector('form[action*="force"]').addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to force create a backup? This will create a backup even if one was created recently.')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
