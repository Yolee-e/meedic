<?php 
	include './config/connection.php';

$message = '';

  if(isset($_POST['login'])) {
    $userName = $_POST['user_name'];
    $password = $_POST['password'];
    // CSRF check
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
      $message = 'Invalid request. Please refresh the page and try again.';
      // Log the CSRF failure for debugging
      if (function_exists('app_log_message')) {
        app_log_message("CSRF token mismatch for user: $userName, IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
      }
    } else {

try {
  // Fetch user by username
  $query = "SELECT `id`, `display_name`, `user_name`, `profile_picture`, IFNULL(`type`, 1) as `type`, `password` 
            FROM `users` WHERE `user_name` = :uname LIMIT 1";
  $stmtLogin = $con->prepare($query);
  $stmtLogin->bindParam(':uname', $userName, PDO::PARAM_STR);
  $stmtLogin->execute();

  if($stmtLogin->rowCount() === 1) {
    $row = $stmtLogin->fetch(PDO::FETCH_ASSOC);
    $stored = $row['password'];
    $ok = false;
    if (strpos($stored, '$2y$') === 0 || strpos($stored, '$argon2') === 0) {
      // modern hash
      $ok = password_verify($password, $stored);
    } else {
      // legacy md5 fallback
      $ok = (md5($password) === $stored);
    }
    // Basic login rate limiting using session counters
    if (!isset($_SESSION['login_attempts'])) { $_SESSION['login_attempts'] = 0; }
    if (!isset($_SESSION['login_block_until'])) { $_SESSION['login_block_until'] = 0; }
    $nowTs = time();
    if ($nowTs < (int)$_SESSION['login_block_until']) {
      $message = 'Too many attempts. Please try again later.';
    } elseif (!$ok) {
      $_SESSION['login_attempts'] = (int)$_SESSION['login_attempts'] + 1;
      if ($_SESSION['login_attempts'] >= 5) {
        $_SESSION['login_block_until'] = $nowTs + 300; // block 5 minutes
        $_SESSION['login_attempts'] = 0; // reset
        $message = 'Too many attempts. Please try again in 5 minutes.';
      } else {
      $message = 'Incorrect username or password.';
      }
    } else {
      $_SESSION['login_attempts'] = 0; // reset on success
      // success: set session
      session_regenerate_id(true);

    $_SESSION['user_id'] = $row['id'];
    $_SESSION['display_name'] = $row['display_name'];
    $_SESSION['user_name'] = $row['user_name'];
    $_SESSION['profile_picture'] = $row['profile_picture'];
    $_SESSION['user_type'] = (int)$row['type'];

    // log login
    if (function_exists('log_activity')) { log_activity($con, 'Login', ['user_id'=>$row['id']]); }
    header("location:dashboard.php");
    exit;
      // Upgrade legacy MD5 to bcrypt on successful login
      if (strpos($stored, '$2y$') !== 0 && strpos($stored, '$argon2') !== 0) {
        try {
          $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
          $stmtU = $con->prepare('UPDATE users SET password = :p WHERE id = :id');
          $stmtU->bindParam(':p', $newHash, PDO::PARAM_STR);
          $stmtU->bindParam(':id', $row['id'], PDO::PARAM_INT);
          $stmtU->execute();
        } catch (PDOException $ignored) {}
      }
    }
  } else {
    $message = 'Incorrect username or password.';
  }
}  catch(PDOException $ex) {
      app_log_exception($ex);
      $message = 'Database connection error. Please try again later.';
      // Log additional debugging info
      if (function_exists('app_log_message')) {
        app_log_message("Login DB error for user: $userName, IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . ", Error: " . $ex->getMessage());
      }
    }
  }

		
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - St. Rita Family Clinic</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">

  <style>
    /* Modern Login UI Styles */
    body.login-page {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      background-attachment: fixed;
      min-height: 100vh;
      position: relative;
      overflow-x: hidden;
    }
    
    body.login-page::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: 
        radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.2) 0%, transparent 50%);
      pointer-events: none;
      z-index: 1;
    }
  	
  	.login-box {
      width: 450px;
      position: relative;
      z-index: 2;
    }
    
    
    
    .login-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 20px;
      box-shadow: 
        0 20px 40px rgba(0, 0, 0, 0.1),
        0 0 0 1px rgba(255, 255, 255, 0.2);
      overflow: hidden;
      transition: all 0.3s ease;
    }
    
    .login-card:hover {
      transform: translateY(-5px);
      box-shadow: 
        0 25px 50px rgba(0, 0, 0, 0.15),
        0 0 0 1px rgba(255, 255, 255, 0.3);
    }
    
    .login-card-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 2rem;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .login-card-header::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
      animation: float 6s ease-in-out infinite;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(180deg); }
    }
    
    .login-card-header h3 {
      margin: 0;
      font-weight: 600;
      font-size: 1.5rem;
      position: relative;
      z-index: 2;
    }
    
    .login-card-header p {
      margin: 0.5rem 0 0 0;
      opacity: 0.9;
      font-size: 0.9rem;
      position: relative;
      z-index: 2;
    }
    
    .login-card-body {
      padding: 2rem;
    }
    
    .input-group {
      margin-bottom: 1.5rem;
      position: relative;
    }
    
    .input-group .form-control {
      border: 2px solid #e9ecef;
      border-radius: 12px;
      padding: 0.75rem 1rem 0.75rem 3rem;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: rgba(255, 255, 255, 0.8);
    }
    
    .input-group .form-control:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
      background: white;
      transform: translateY(-2px);
    }
    
    .input-group-text {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      background: transparent;
      border: none;
      color: #667eea;
      z-index: 3;
      font-size: 1.1rem;
    }
    
    .btn-login {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      border-radius: 12px;
      padding: 0.75rem 2rem;
      font-weight: 600;
      font-size: 1rem;
      color: white;
      width: 100%;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .btn-login::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }
    
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
    }
    
    .btn-login:hover::before {
      left: 100%;
    }
    
    .btn-login:active {
      transform: translateY(0);
    }
    
    .alert-message {
      background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
      color: white;
      border: none;
      border-radius: 12px;
      padding: 1rem;
      margin-bottom: 1rem;
      font-weight: 500;
      animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .login-footer {
      text-align: center;
      margin-top: 2rem;
      color: rgba(255, 255, 255, 0.8);
      font-size: 0.9rem;
    }
    
    .login-footer a {
      color: white;
      text-decoration: none;
      font-weight: 500;
    }
    
    .login-footer a:hover {
      text-decoration: underline;
    }
    
    /* Responsive Design */
    @media (max-width: 576px) {
      .login-box {
        width: 90%;
    margin: 0 auto;
      }
      
      .login-card-header,
      .login-card-body {
        padding: 1.5rem;
      }
    }
    
    /* Loading Animation */
    .btn-login.loading {
      pointer-events: none;
      opacity: 0.7;
    }
    
    .btn-login.loading::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 20px;
      height: 20px;
      border: 2px solid transparent;
      border-top: 2px solid white;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      0% { transform: translate(-50%, -50%) rotate(0deg); }
      100% { transform: translate(-50%, -50%) rotate(360deg); }
}
  </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
  
  <div class="login-card">
    <div class="login-card-header">
      <h3><i class="fas fa-user-md"></i> Medical Portal</h3>
      <p>St. Rita Family Clinic</p>
    </div>
    
    <div class="login-card-body">
      <?php if($message != ''): ?>
        <div class="alert-message">
          <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>
      
      <form method="post" id="loginForm">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
        
        <div class="input-group">
          <input type="text" class="form-control" 
                 placeholder="Username" id="user_name" name="user_name" 
                 value="<?php echo isset($_POST['user_name']) ? htmlspecialchars($_POST['user_name']) : ''; ?>" 
                 required autofocus>
            <div class="input-group-text">
              <span class="fas fa-user"></span>
          </div>
        </div>
        
        <div class="input-group">
          <input type="password" class="form-control" 
                 placeholder="Password" id="password" name="password" required>
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
          </div>
        </div>
        
        <button name="login" type="submit" class="btn-login" id="loginBtn">
          <i class="fas fa-sign-in-alt"></i> Sign In
        </button>
      </form>
    </div>
  </div>
  
  <div class="login-footer">
    <p>&copy; <?php echo date('Y'); ?> St. Rita Family Clinic. All rights reserved.</p>
  </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Add loading animation on form submit
    $('#loginForm').on('submit', function() {
        const btn = $('#loginBtn');
        btn.addClass('loading');
        btn.html('<i class="fas fa-spinner fa-spin"></i> Signing In...');
    });
    
    // Add focus effects
    $('.form-control').on('focus', function() {
        $(this).closest('.input-group').addClass('focused');
    }).on('blur', function() {
        $(this).closest('.input-group').removeClass('focused');
    });
    
    // Add enter key support
    $('.form-control').on('keypress', function(e) {
        if (e.which === 13) {
            $('#loginForm').submit();
        }
    });
    
    // Auto-focus username field
    $('#user_name').focus();
    
    // Add smooth animations
    $('.login-card').hide().fadeIn(800);
    $('.login-logo img').hide().fadeIn(1000);
});
</script>

</body>
</html>
