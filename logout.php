<?php 
  include './config/connection.php';
  if (function_exists('log_activity')) { log_activity($con, 'Logout'); }
  
  session_destroy();
  
  header("Location:index.php");
?>