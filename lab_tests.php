<?php 
include './config/connection.php';
include './common_service/common_functions.php';

$message = '';

if (isset($_POST['submit'])) {
    $testName = trim($_POST['test_name']);
    $description = trim($_POST['description']);
    $normalRange = trim($_POST['normal_range']);
    $unit = trim($_POST['unit']);

    if ($testName != '' && $normalRange != '' && $unit != '') {
        $query = "INSERT INTO `lab_tests`(`test_name`, `description`, `normal_range`, `unit`) VALUES(:test_name, :description, :normal_range, :unit)";
        
        try {
            $con->beginTransaction();
            $stmt = $con->prepare($query);
            $stmt->bindParam(':test_name', $testName);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':normal_range', $normalRange);
            $stmt->bindParam(':unit', $unit);
            $stmt->execute();
            $con->commit();
            $message = 'Lab test added successfully.';
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
    $query = "SELECT `id`, `test_name`, `description`, `normal_range`, `unit` FROM `lab_tests` ORDER BY `test_name` ASC;";
    $stmtLabTests = $con->prepare($query);
    $stmtLabTests->execute();
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
 <?php include './config/data_tables_css.php';?>
  <title>Lab Tests - Medical Care and Information system</title>

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
            <h1>Lab Tests</h1>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">

      <!-- Default box -->
     <div class="card card-outline card-primary shadow">
        <div class="card-header">
          <h3 class="card-title">Add New Lab Test</h3>
          
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <form method="post">
            <div class="row">
              <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 mb-3">
                <label for="test_name">Test Name</label>
                <input type="text" id="test_name" name="test_name" required="required"
                  class="form-control form-control-sm" placeholder="Enter test name" />
              </div>
              <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 mb-3">
                <label for="description">Description</label> 
                <input type="text" id="description" name="description"
                  class="form-control form-control-sm" placeholder="Enter description" />
              </div>
              <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 mb-3">
                <label for="normal_range">Normal Range</label>
                <input type="text" id="normal_range" name="normal_range" required="required"
                  class="form-control form-control-sm" placeholder="e.g., 70-100" />
              </div>
              <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 mb-3">
                <label for="unit">Unit</label>
                <input type="text" id="unit" name="unit" required="required"
                  class="form-control form-control-sm" placeholder="e.g., mg/dL" />
              </div>
            </div>
              
            <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-right">
                <button type="submit" id="submit" 
                name="submit" class="btn btn-primary btn-sm btn-flat">Save Lab Test</button>
              </div>
            </div>
          </form>
        </div>
      </div>
      
    </section>

     <br/>
     <br/>
     <br/>

 <section class="content">
      <!-- Default box -->
      <div class="card card-outline card-primary shadow">
        <div class="card-header">
          <h3 class="card-title">Total Lab Tests</h3>

          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
            
          </div>
        </div>
        <div class="card-body">
            <div class="row table-responsive">
              <table id="all_lab_tests" 
              class="table table-striped dataTable table-bordered dtr-inline" 
               role="grid" aria-describedby="all_lab_tests_info">
              
                <thead>
                  <tr>
                    <th class="p-1 text-center">S.No</th>
                    <th class="p-1 text-center">Test Name</th>
                    <th class="p-1 text-center">Description</th>
                    <th class="p-1 text-center">Normal Range</th>
                    <th class="p-1 text-center">Unit</th>
                    <th class="p-1 text-center">Action</th>
                  </tr>
                </thead>

                <tbody>
                  <?php 
                  $count = 0;
                  while($row =$stmtLabTests->fetch(PDO::FETCH_ASSOC)){
                    $count++;
                  ?>
                  <tr>
                    <td class="text-center"><?php echo $count; ?></td>
                    <td><?php echo $row['test_name'];?></td>
                    <td><?php echo $row['description'];?></td>
                    <td><?php echo $row['normal_range'];?></td>
                    <td><?php echo $row['unit'];?></td>
                    <td class="text-center">
                      <a href="update_lab_test.php?id=<?php echo $row['id'];?>" class = "btn btn-primary btn-sm btn-flat" title="Edit">
                      <i class="fa fa-edit"></i>
                      </a>
                    </td>
                   
                  </tr>
                <?php
                }
                ?>
                </tbody>
              </table>
            </div>
        </div>
     
      </div>
      <!-- /.card -->

   
    </section>
  </div>
    <!-- /.content -->
  
  <!-- /.content-wrapper -->

  <?php include './config/site_js_links.php'; ?>
  <?php include './config/data_tables_js.php'; ?>


<script>
  showMenuSelected("#mnu_lab_diag", "#mi_lab_tests");

  var message = '<?php echo $message;?>';

  if(message !== '') {
    showCustomMessage(message);
  }
   
   $(function () {
    // Check if DataTable is already initialized to prevent reinitialization
    if (!$.fn.DataTable.isDataTable('#all_lab_tests')) {
      $("#all_lab_tests").DataTable({
        "responsive": true, "lengthChange": false, "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
      }).buttons().container().appendTo('#all_lab_tests_wrapper .col-md-6:eq(0)');
    }
  });

   
</script>
</body>
</html>
