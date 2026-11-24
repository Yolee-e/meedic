<?php 
include './config/connection.php';
include './common_service/common_functions.php';

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_post();
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $nameExtension = trim($_POST['name_extension']);
    $userName = trim($_POST['user_name']);
    $roleType = isset($_POST['role_type']) ? (int)$_POST['role_type'] : 3;
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $passwordConfirm = isset($_POST['password_confirm']) ? trim($_POST['password_confirm']) : '';

    // Build display name from components
    $displayName = $firstName . ' ' . $lastName;
    if (!empty($nameExtension)) {
        $displayName .= ' ' . $nameExtension;
    }

    if ($userId <= 0 || $firstName === '' || $lastName === '' || $userName === '') {
        $message = 'Missing required fields';
    } else {
        if ($password !== '' && $password !== $passwordConfirm) {
            $message = 'Passwords do not match.';
        } else {
        try {
            $con->beginTransaction();
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $con->prepare('UPDATE users SET first_name = :fn, last_name = :ln, name_extension = :ne, display_name = :dn, user_name = :un, password = :pw, type = :t WHERE id = :id');
                $stmt->bindParam(':pw', $hash, PDO::PARAM_STR);
            } else {
                $stmt = $con->prepare('UPDATE users SET first_name = :fn, last_name = :ln, name_extension = :ne, display_name = :dn, user_name = :un, type = :t WHERE id = :id');
            }
            $stmt->bindParam(':fn', $firstName, PDO::PARAM_STR);
            $stmt->bindParam(':ln', $lastName, PDO::PARAM_STR);
            $stmt->bindParam(':ne', $nameExtension, PDO::PARAM_STR);
            $stmt->bindParam(':dn', $displayName, PDO::PARAM_STR);
            $stmt->bindParam(':un', $userName, PDO::PARAM_STR);
            $stmt->bindParam(':t', $roleType, PDO::PARAM_INT);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $con->commit();
            $message = 'User updated successfully';
            header('Location: congratulation.php?goto_page=users.php&message=' . urlencode($message));
            exit;
        } catch (PDOException $ex) {
            $con->rollBack();
            $message = 'Update failed: ' . $ex->getMessage();
        }
        }
    }
}

$row = null;
if ($userId > 0) {
    try {
        $stmt = $con->prepare('SELECT id, first_name, last_name, name_extension, display_name, user_name, IFNULL(type,3) as type FROM users WHERE id = :id LIMIT 1');
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        $row = null;
        $message = 'Failed to load user';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <?php include './config/site_css_links.php';?>
 <?php include './config/data_tables_css.php';?>
 <title>Edit User</title>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
  <div class="wrapper">
    <?php include './config/header.php'; include './config/sidebar.php';?>  
    <div class="content-wrapper">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6"><h1>Edit User</h1></div>
          </div>
        </div>
      </section>
      <section class="content">
        <div class="card card-outline card-primary shadow">
          <div class="card-body">
            <?php if ($message !== '') { ?>
              <div class="alert alert-<?php echo strpos($message,'success')!==false ? 'success' : 'danger'; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php } ?>
            <?php if (!$row) { ?>
              <div class="alert alert-warning mb-0">User not found.</div>
            <?php } else { ?>
            <form method="post">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="user_id" value="<?php echo (int)$row['id']; ?>" />
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label>First Name</label>
                  <input type="text" class="form-control form-control-sm" name="first_name" value="<?php echo htmlspecialchars($row['first_name']); ?>" required />
                </div>
                <div class="col-md-4 mb-3">
                  <label>Last Name</label>
                  <input type="text" class="form-control form-control-sm" name="last_name" value="<?php echo htmlspecialchars($row['last_name']); ?>" required />
                </div>
                <div class="col-md-4 mb-3">
                  <label>Name Extensions</label>
                  <div class="extension-container">
                    <input type="text" class="form-control form-control-sm" name="name_extension" 
                           value="<?php echo htmlspecialchars($row['name_extension']); ?>" 
                           placeholder="e.g., Jr. MD PhD" 
                           title="Enter multiple extensions separated by spaces"/>
                    <small class="text-muted">
                      <strong>Multiple extensions:</strong> Separate with spaces (e.g., "Jr. MD PhD")
                    </small>
                    <div class="extension-quick-select mt-2">
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="addExtension('Jr.')">Jr.</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="addExtension('Sr.')">Sr.</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="addExtension('II')">II</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="addExtension('III')">III</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="addExtension('IV')">IV</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="addExtension('V')">V</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="addExtension('MD')">MD</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="addExtension('PhD')">PhD</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="addExtension('RN')">RN</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="addExtension('CPA')">CPA</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="addExtension('Esq.')">Esq.</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="addExtension('Dr.')">Dr.</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="addExtension('Prof.')">Prof.</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="addExtension('Rev.')">Rev.</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="addExtension('Capt.')">Capt.</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="addExtension('Col.')">Col.</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="addExtension('Gen.')">Gen.</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="addExtension('Lt.')">Lt.</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="addExtension('Maj.')">Maj.</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="addExtension('Sgt.')">Sgt.</button>
                      <button type="button" class="btn btn-sm btn-outline-danger me-1 mb-1" onclick="clearExtensions()">Clear</button>
                    </div>
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <label>Username</label>
                  <input type="text" class="form-control form-control-sm" name="user_name" value="<?php echo htmlspecialchars($row['user_name']); ?>" required />
                </div>
                <div class="col-md-4 mb-3">
                  <label>Role</label>
                  <select class="form-control form-control-sm" name="role_type" required>
                    <?php $t=(int)$row['type']; ?>
                    <option value="1" <?php echo $t===1?'selected':''; ?>>Admin</option>
                    <option value="2" <?php echo $t===2?'selected':''; ?>>Doctor</option>
                    <option value="3" <?php echo $t===3?'selected':''; ?>>Front Desk</option>
                    <option value="4" <?php echo $t===4?'selected':''; ?>>Cashier</option>
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <label>New Password (leave blank to keep)</label>
                  <div class="input-group input-group-sm">
                    <input type="password" class="form-control form-control-sm" name="password" id="pw_new" />
                    <div class="input-group-append">
                      <button class="btn btn-outline-secondary" type="button" onclick="togglePw('pw_new')"><i class="fas fa-eye"></i></button>
                    </div>
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <label>Confirm Password</label>
                  <div class="input-group input-group-sm">
                    <input type="password" class="form-control form-control-sm" name="password_confirm" id="pw_confirm" />
                    <div class="input-group-append">
                      <button class="btn btn-outline-secondary" type="button" onclick="togglePw('pw_confirm')"><i class="fas fa-eye"></i></button>
                    </div>
                  </div>
                </div>
              </div>
              <div class="text-right">
                <button class="btn btn-primary btn-sm">Save Changes</button>
                <a href="users.php" class="btn btn-outline-secondary btn-sm">Cancel</a>
              </div>
            </form>
            <?php } ?>
          </div>
        </div>
      </section>
    </div>
  </div>
  <?php include './config/site_js_links.php'; ?>
  <script>
    function togglePw(id){
      var el = document.getElementById(id);
      if (!el) return;
      el.type = (el.type === 'password') ? 'text' : 'password';
    }

    // Multiple extension handling functions
    function addExtension(extension) {
      var currentValue = document.querySelector('input[name="name_extension"]').value.trim();
      var extensions = currentValue ? currentValue.split(' ') : [];
      
      // Check if extension already exists
      if (!extensions.includes(extension)) {
        extensions.push(extension);
        document.querySelector('input[name="name_extension"]').value = extensions.join(' ');
      }
    }

    function clearExtensions() {
      document.querySelector('input[name="name_extension"]').value = '';
    }

    // Auto-format extensions on input
    document.addEventListener('DOMContentLoaded', function() {
      var extensionInput = document.querySelector('input[name="name_extension"]');
      if (extensionInput) {
        extensionInput.addEventListener('blur', function() {
          var value = this.value.trim();
          if (value) {
            // Clean up the input - remove extra spaces and format
            var extensions = value.split(/\s+/).filter(function(ext) {
              return ext.length > 0;
            });
            this.value = extensions.join(' ');
          }
        });
      }
    });

    // Add CSS for extension styling
    var style = document.createElement('style');
    style.textContent = `
      .extension-container {
        position: relative;
      }
      
      .extension-quick-select {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
        margin-top: 0.5rem;
      }
      
      .extension-quick-select .btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
      }
      
      .extension-quick-select .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      }
      
      .extension-quick-select .btn-outline-secondary {
        border-color: #6c757d;
        color: #6c757d;
      }
      
      .extension-quick-select .btn-outline-secondary:hover {
        background-color: #6c757d;
        border-color: #6c757d;
        color: white;
      }
      
      .extension-quick-select .btn-outline-danger {
        border-color: #dc3545;
        color: #dc3545;
      }
      
      .extension-quick-select .btn-outline-danger:hover {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
      }
    `;
    document.head.appendChild(style);
  </script>
</body>
</html>
