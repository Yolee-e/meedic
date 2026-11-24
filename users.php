<?php
include './config/connection.php';
include './config/error_handling.php';
include './common_service/common_functions.php';

// Handle user creation
if(isset($_POST['save_user'])) {
  $firstName = trim($_POST['first_name']);
  $lastName = trim($_POST['last_name']);
  $userName = trim($_POST['user_name']);
  $password = trim($_POST['password']);
  $roleType = isset($_POST['role_type']) ? (int)$_POST['role_type'] : 2;
  $displayName = $firstName . ' ' . $lastName;

  if (empty($firstName) || empty($lastName) || empty($userName) || empty($password)) {
    $message = 'First name, last name, username, and password are required.';
  } else {
    try {
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
      
      // Check what columns actually exist
      $checkStmt = $con->prepare("DESCRIBE users");
      $checkStmt->execute();
      $columns = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
      $columnNames = array_column($columns, 'Field');
      
      // Build INSERT query based on available columns
      $insertFields = [];
      $insertValues = [];
      $insertParams = [];
      
      // Always include basic fields
      $insertFields[] = 'display_name';
      $insertValues[] = '?';
      $insertParams[] = $displayName;
      
      $insertFields[] = 'user_name';
      $insertValues[] = '?';
      $insertParams[] = $userName;
      
      $insertFields[] = 'password';
      $insertValues[] = '?';
      $insertParams[] = $hashedPassword;
      
      // Add optional fields if they exist
      if (in_array('first_name', $columnNames)) {
        $insertFields[] = 'first_name';
        $insertValues[] = '?';
        $insertParams[] = $firstName;
      }
      
      if (in_array('last_name', $columnNames)) {
        $insertFields[] = 'last_name';
        $insertValues[] = '?';
        $insertParams[] = $lastName;
      }
      
      if (in_array('name_extension', $columnNames)) {
        $insertFields[] = 'name_extension';
        $insertValues[] = '?';
        $insertParams[] = '';
      }
      
      if (in_array('type', $columnNames)) {
        $insertFields[] = 'type';
        $insertValues[] = '?';
        $insertParams[] = $roleType;
      }
      
      if (in_array('profile_picture', $columnNames)) {
        $insertFields[] = 'profile_picture';
        $insertValues[] = '?';
        $insertParams[] = '';
      }
      
      // Build and execute the smart query
      $query = "INSERT INTO users(" . implode(', ', $insertFields) . ") VALUES(" . implode(', ', $insertValues) . ")";
      $stmt = $con->prepare($query);
      $stmt->execute($insertParams);
      
      $message = 'User created successfully!';
    } catch(PDOException $ex) {
      $message = 'Failed to save user: ' . $ex->getMessage();
    }
  }
}

// Get users with roles
try {
  // Check if role column exists
  $checkStmt = $con->prepare("DESCRIBE users");
  $checkStmt->execute();
  $columns = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
  $hasRoleColumn = false;
  
  foreach ($columns as $column) {
    if ($column['Field'] === 'type') {
      $hasRoleColumn = true;
      break;
    }
  }
  
  if ($hasRoleColumn) {
    $stmt = $con->prepare("SELECT id, display_name, user_name, type FROM users ORDER BY id DESC");
  } else {
    $stmt = $con->prepare("SELECT id, display_name, user_name FROM users ORDER BY id DESC");
  }
  $stmt->execute();
  $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $ex) {
  $users = [];
  $message = 'Failed to load users.';
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Users - Working Version</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
    background: #f4f6f9;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  .main-sidebar {
    background: #343a40;
    min-height: calc(100vh - 57px);
  }
  .navbar-dark {
    background: #343a40 !important;
  }
  .content-wrapper {
    background: #f4f6f9;
    min-height: calc(100vh - 57px);
  }
  .navbar-nav .nav-link {
    color: rgba(255,255,255,.8);
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
  }
  .navbar-nav .nav-link:hover {
    background: rgba(255,255,255,.1);
    color: white;
  }
  
  /* User-friendly improvements */
  .page-header {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
  }
  .page-header h1 {
    margin: 0;
    color: #333;
    font-size: 1.8rem;
    font-weight: 600;
  }
  .page-header .breadcrumb {
    background: none;
    padding: 0;
    margin: 0.5rem 0 0 0;
  }
  .form-section {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
    overflow: hidden;
  }
  .form-section-header {
    background: #007bff;
    color: white;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #0056b3;
  }
  .form-section-header h3 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 600;
  }
  .form-section-body {
    padding: 1.5rem;
  }
  .form-group {
    margin-bottom: 1.25rem;
  }
  .form-group label {
    font-weight: 600;
    color: #555;
    margin-bottom: 0.5rem;
    display: block;
  }
  .form-control {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 0.75rem;
    font-size: 0.95rem;
    transition: border-color 0.3s ease;
  }
  .form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
    outline: none;
  }
  .btn-primary {
    background: #007bff;
    border-color: #007bff;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 4px;
    transition: all 0.3s ease;
  }
  .btn-primary:hover {
    background: #0056b3;
    border-color: #0056b3;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }
  .table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
  }
  .table-container .card-header {
    background: #6c757d;
    color: white;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #545b62;
  }
  .table-container .card-header h3 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 600;
  }
  .table {
    margin: 0;
  }
  .table thead th {
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #495057;
    padding: 1rem;
  }
  .table tbody td {
    padding: 1rem;
    vertical-align: middle;
  }
  .table tbody tr:hover {
    background-color: #f8f9fa;
  }
  .btn-action {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 4px;
    margin-right: 0.25rem;
  }
  .alert {
    border-radius: 4px;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
  }
  .help-text {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
  }
  .required-star {
    color: #dc3545;
  }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
  <div class="wrapper">
    <!-- Navbar -->
    <?php 
    if (file_exists('./config/header.php')) {
      include './config/header.php';
    } else {
      echo '<nav class="navbar navbar-expand-lg navbar-dark bg-dark"><div class="container-fluid"><a class="navbar-brand" href="dashboard.php">PMS</a><div class="collapse navbar-collapse"><ul class="navbar-nav"><li class="nav-item"><a class="nav-link" href="users.php">Users</a></li><li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li></ul></div></div></nav>';
    }
    include './config/sidebar.php'; ?>  
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Page Header -->
    <br>
    <br>
      
      <!-- Main content -->
      <div class="container-fluid">
        <?php if(isset($message)): ?>
          <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <!-- Add User Form -->
        <div class="form-section">
          <div class="form-section-header">
            <h3><i class="fas fa-user-plus me-2"></i>Add New User</h3>
          </div>
          <div class="form-section-body">
            <form method="post">
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="first_name">First Name <span class="required-star">*</span></label>
                    <input type="text" id="first_name" name="first_name" class="form-control" required placeholder="Enter first name">
                    <small class="help-text">User's first name as it should appear in the system</small>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="last_name">Last Name <span class="required-star">*</span></label>
                    <input type="text" id="last_name" name="last_name" class="form-control" required placeholder="Enter last name">
                    <small class="help-text">User's last name or family name</small>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="user_name">Username <span class="required-star">*</span></label>
                    <input type="text" id="user_name" name="user_name" class="form-control" required placeholder="Choose unique username">
                    <small class="help-text">Unique username for login (no spaces)</small>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="password">Password <span class="required-star">*</span></label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Enter secure password">
                    <small class="help-text">Minimum 6 characters, mix of letters and numbers</small>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="role_type">User Role <span class="required-star">*</span></label>
                    <select id="role_type" name="role_type" class="form-control" required>
                      <option value="">Select Role</option>
                      <option value="1">Administrator</option>
                      <option value="2">Doctor</option>
                      <option value="3" selected>Front Desk</option>
                      <option value="4">Cashier</option>
                    </select>
                    <small class="help-text">Select appropriate role for system access</small>
                  </div>
                </div>
                <div class="col-md-9">
                  <div class="form-group">
                    <label>&nbsp;</label><br>
                    <button type="submit" name="save_user" class="btn btn-primary">
                      <i class="fas fa-save me-2"></i>Create User Account
                    </button>
                    <button type="reset" class="btn btn-secondary ms-2">
                      <i class="fas fa-redo me-2"></i>Clear Form
                    </button>
                    <small class="help-text d-block mt-2">All fields marked with <span class="required-star">*</span> are required</small>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Users Table -->
        <div class="table-container">
          <div class="card-header">
            <h3><i class="fas fa-users me-2"></i>Existing Users</h3>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th><i class="fas fa-hashtag me-1"></i>ID</th>
                    <th><i class="fas fa-user me-1"></i>Name</th>
                    <th><i class="fas fa-at me-1"></i>Username</th>
                    <th><i class="fas fa-user-tag me-1"></i>Role</th>
                    <th><i class="fas fa-cogs me-1"></i>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($users as $user): ?>
                    <tr>
                      <td><?php echo $user['id']; ?></td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="avatar-circle me-2">
                            <i class="fas fa-user"></i>
                          </div>
                          <span class="fw-semibold"><?php echo htmlspecialchars($user['display_name']); ?></span>
                        </div>
                      </td>
                      <td><code><?php echo htmlspecialchars($user['user_name']); ?></code></td>
                      <td>
                        <?php 
                        $roleNames = [
                          1 => ['Administrator', 'danger'],
                          2 => ['Doctor', 'primary'],
                          3 => ['Front Desk', 'info'],
                          4 => ['Cashier', 'success']
                        ];
                        
                        if (isset($user['type'])) {
                          $roleInfo = $roleNames[$user['type']] ?? ['Unknown', 'secondary'];
                          echo '<span class="badge bg-' . $roleInfo[1] . '">' . htmlspecialchars($roleInfo[0]) . '</span>';
                        } else {
                          echo '<span class="badge bg-secondary">Not Set</span>';
                        }
                        ?>
                      </td>
                      <td>
                        <a href="update_user.php?user_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary btn-action" title="Edit User">
                          <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="maintenance/smart_user_creation.php" class="btn btn-sm btn-success btn-action" title="Create New User">
                          <i class="fas fa-plus"></i> New
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
</body>
</html>
