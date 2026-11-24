<?php
/**
 * System Maintenance Page
 * Provides backup, restore, and system management functions
 */

include 'config/connection.php';
include 'config/error_handling.php';

// Check if user is authenticated and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 1) {
    header('location: index.php');
    exit;
}

$pageTitle = 'System Maintenance';
include 'config/header.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <?php echo getContentHeader($pageTitle); ?>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- System Status -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">System Status</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-database"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Database</span>
                                            <span class="info-box-number" id="dbStatus">Checking...</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success"><i class="fas fa-hdd"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Disk Space</span>
                                            <span class="info-box-number" id="diskSpace">Checking...</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-file-archive"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Backups</span>
                                            <span class="info-box-number" id="backupCount">0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-primary"><i class="fas fa-code"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">PHP Version</span>
                                            <span class="info-box-number"><?php echo PHP_VERSION; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backup Management -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Backup Management</h3>
                            <div class="card-tools">
                                <button class="btn btn-primary btn-sm" id="createBackup">
                                    <i class="fas fa-plus"></i> Create Backup
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="backupsTable">
                                    <thead>
                                        <tr>
                                            <th>Filename</th>
                                            <th>Size</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Backups will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">System Information</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>PHP Version:</strong></td>
                                    <td><?php echo PHP_VERSION; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Server Software:</strong></td>
                                    <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Operating System:</strong></td>
                                    <td><?php echo PHP_OS; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Memory Limit:</strong></td>
                                    <td><?php echo ini_get('memory_limit'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Max Execution Time:</strong></td>
                                    <td><?php echo ini_get('max_execution_time'); ?>s</td>
                                </tr>
                                <tr>
                                    <td><strong>Upload Max Filesize:</strong></td>
                                    <td><?php echo ini_get('upload_max_filesize'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Database Information</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Database Name:</strong></td>
                                    <td><?php echo getenv('DB_NAME') ?: 'pms_db'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Host:</strong></td>
                                    <td><?php echo getenv('DB_HOST') ?: 'localhost'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Charset:</strong></td>
                                    <td>utf8mb4</td>
                                </tr>
                                <tr>
                                    <td><strong>Tables:</strong></td>
                                    <td id="tableCount">Loading...</td>
                                </tr>
                                <tr>
                                    <td><strong>Database Size:</strong></td>
                                    <td id="dbSize">Loading...</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Log Files -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Log Files</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Application Logs</h5>
                                    <div id="appLogs" style="max-height: 200px; overflow-y: auto; background: #f8f9fa; padding: 10px; border-radius: 4px;">
                                        <small class="text-muted">Loading logs...</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5>Error Logs</h5>
                                    <div id="errorLogs" style="max-height: 200px; overflow-y: auto; background: #f8f9fa; padding: 10px; border-radius: 4px;">
                                        <small class="text-muted">Loading logs...</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- Restore Backup Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Restore Backup</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to restore this backup? This will replace all current data.</p>
                <div class="alert alert-warning">
                    <strong>Warning:</strong> This action cannot be undone. Make sure you have a current backup before proceeding.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRestore">Restore Backup</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load system status
    loadSystemStatus();
    loadBackups();
    loadLogs();
    
    // Create backup
    $('#createBackup').click(function() {
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');
        
        $.post('ajax/backup_system.php', {
            action: 'create_backup',
            csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
        }, function(response) {
            if (response.status === 'success') {
                toastr.success('Backup created successfully');
                loadBackups();
            } else {
                toastr.error(response.message);
            }
        }, 'json').always(function() {
            $('#createBackup').prop('disabled', false).html('<i class="fas fa-plus"></i> Create Backup');
        });
    });
    
    // Load system status
    function loadSystemStatus() {
        // Database status
        $('#dbStatus').text('Connected').removeClass('text-danger').addClass('text-success');
        
        // Disk space
        $.get('ajax/get_system_info.php', function(response) {
            if (response.status === 'success') {
                $('#diskSpace').text(response.diskSpace);
                $('#tableCount').text(response.tableCount);
                $('#dbSize').text(response.dbSize);
            }
        }, 'json');
        
        // Backup count
        $.get('ajax/backup_system.php?action=list_backups', function(response) {
            if (response.status === 'success') {
                $('#backupCount').text(response.backups.length);
            }
        }, 'json');
    }
    
    // Load backups
    function loadBackups() {
        $.get('ajax/backup_system.php?action=list_backups', function(response) {
            if (response.status === 'success') {
                let tbody = '';
                response.backups.forEach(function(backup) {
                    tbody += '<tr>' +
                        '<td>' + backup.filename + '</td>' +
                        '<td>' + backup.size + '</td>' +
                        '<td>' + backup.created + '</td>' +
                        '<td>' +
                            '<button class="btn btn-sm btn-info restore-btn" data-file="' + backup.filename + '">Restore</button> ' +
                            '<button class="btn btn-sm btn-danger delete-btn" data-file="' + backup.filename + '">Delete</button>' +
                        '</td>' +
                    '</tr>';
                });
                $('#backupsTable tbody').html(tbody);
            }
        }, 'json');
    }
    
    // Load logs
    function loadLogs() {
        $.get('ajax/get_logs.php', function(response) {
            if (response.status === 'success') {
                $('#appLogs').html('<pre>' + response.appLogs + '</pre>');
                $('#errorLogs').html('<pre>' + response.errorLogs + '</pre>');
            }
        }, 'json');
    }
    
    // Restore backup
    $(document).on('click', '.restore-btn', function() {
        const filename = $(this).data('file');
        $('#restoreModal').data('filename', filename).modal('show');
    });
    
    $('#confirmRestore').click(function() {
        const filename = $('#restoreModal').data('filename');
        $(this).prop('disabled', true).text('Restoring...');
        
        $.post('ajax/backup_system.php', {
            action: 'restore_backup',
            filename: filename,
            csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
        }, function(response) {
            if (response.status === 'success') {
                toastr.success('Backup restored successfully');
                $('#restoreModal').modal('hide');
            } else {
                toastr.error(response.message);
            }
        }, 'json').always(function() {
            $('#confirmRestore').prop('disabled', false).text('Restore Backup');
        });
    });
    
    // Delete backup
    $(document).on('click', '.delete-btn', function() {
        const filename = $(this).data('file');
        
        if (confirm('Are you sure you want to delete this backup?')) {
            $.post('ajax/backup_system.php', {
                action: 'delete_backup',
                filename: filename,
                csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
            }, function(response) {
                if (response.status === 'success') {
                    toastr.success('Backup deleted successfully');
                    loadBackups();
                } else {
                    toastr.error(response.message);
                }
            }, 'json');
        }
    });
});
</script>

<?php include 'config/sidebar.php'; ?>
<?php include 'config/site_js_links.php'; ?>
