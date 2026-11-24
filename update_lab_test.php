<?php 
include './config/connection.php';
include './common_service/common_functions.php';

$message = '';
$id = $_GET['id'];

if (isset($_POST['submit'])) {
    csrf_validate_post();
    $testName = trim($_POST['test_name']);
    $description = trim($_POST['description']);
    $normalRange = trim($_POST['normal_range']);
    $unit = trim($_POST['unit']);

    if ($testName != '' && $normalRange != '' && $unit != '') {
        $query = "UPDATE `lab_tests` SET `test_name` = :test_name, `description` = :description, `normal_range` = :normal_range, `unit` = :unit WHERE `id` = :id";
        
        try {
            $con->beginTransaction();
            $stmt = $con->prepare($query);
            $stmt->bindParam(':test_name', $testName);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':normal_range', $normalRange);
            $stmt->bindParam(':unit', $unit);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $con->commit();
            $message = 'Lab test updated successfully.';
        } catch (PDOException $ex) {
            $con->rollback();
            echo $ex->getMessage();
            echo $ex->getTraceAsString();
            exit;
        }
    } else {
        $message = 'Please fill all required fields.';
    }
    header("Location: congratulation.php?goto_page=lab_tests.php&message=$message");
    exit;
}

try {
    $query = "SELECT `id`, `test_name`, `description`, `normal_range`, `unit` FROM `lab_tests` WHERE `id` = :id;";
    $stmtLabTest = $con->prepare($query);
    $stmtLabTest->bindParam(':id', $id);
    $stmtLabTest->execute();
    $labTest = $stmtLabTest->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    echo $ex->getMessage();
    echo $ex->getTraceAsString();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <?php include './config/site_css_links.php';?>
  <title>Update Lab Test - Medical Care and Information system</title>

</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
<!-- Site wrapper -->
<div class="wrapper">
  <!-- Navbar -->

<?php include './config/header.php';
include './config/sidebar.php';?>  
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Update Lab Test</h1>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">

      <!-- Default box -->
     <div class="card card-outline card-primary shadow">
        <div class="card-header">
          <h3 class="card-title">Update Lab Test Details</h3>
          
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <form method="post">
            <?php echo csrf_field(); ?>
            <div class="row">
              <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 mb-3">
                <label for="test_name">Test Name</label>
                <input type="text" id="test_name" name="test_name" required="required"
                  class="form-control form-control-sm" value="<?php echo $labTest['test_name'];?>" placeholder="Enter test name"/>
              </div>
              <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 mb-3">
                <label for="description">Description</label> 
                <input type="text" id="description" name="description"
                  class="form-control form-control-sm" value="<?php echo $labTest['description'];?>" placeholder="Enter description"/>
              </div>
              <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 mb-3">
                <label for="normal_range">Normal Range</label>
                <input type="text" id="normal_range" name="normal_range" required="required"
                  class="form-control form-control-sm" value="<?php echo $labTest['normal_range'];?>" placeholder="e.g., 70-100"/>
              </div>
              <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 mb-3">
                <label for="unit">Unit</label>
                <input type="text" id="unit" name="unit" required="required"
                  class="form-control form-control-sm" value="<?php echo $labTest['unit'];?>" placeholder="e.g., mg/dL"/>
              </div>
            </div>
              
            <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-right">
                <button type="submit" id="submit" 
                name="submit" class="btn btn-primary btn-sm btn-flat">Update Lab Test</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </section>
  </div>

  <?php include './config/site_js_links.php'; ?>

<script>
  showMenuSelected("#mnu_lab_diag", "#mi_lab_tests");

  var message = '<?php echo $message;?>';

  if(message !== '') {
    showCustomMessage(message);
  }
   
</script>
</body>
</html>
