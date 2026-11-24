<?php 
session_start();
include './config/connection.php';
include './common_service/common_functions.php';

$message = '';
$patientLabTestId = $_GET['id'];
$patientId = $_GET['patient_id'];

if (isset($_POST['submit'])) {
    $sampleCollectedDate = trim($_POST['sample_collected_date']);
    $resultDate = trim($_POST['result_date']);
    $resultValue = trim($_POST['result_value']);
    $resultNotes = trim($_POST['result_notes']);
    $status = trim($_POST['status']);
    $performedByUserId = $_SESSION['user_id']; // Assuming user_id is stored in session

    $sampleCollectedDateFormatted = !empty($sampleCollectedDate) ? date('Y-m-d', strtotime($sampleCollectedDate)) : null;
    $resultDateFormatted = !empty($resultDate) ? date('Y-m-d', strtotime($resultDate)) : null;

    $query = "UPDATE `patient_lab_tests` SET 
                `sample_collected_date` = :sample_collected_date,
                `result_date` = :result_date,
                `result_value` = :result_value,
                `result_notes` = :result_notes,
                `status` = :status,
                `performed_by_user_id` = :performed_by_user_id
              WHERE `id` = :patient_lab_test_id";

    try {
        $con->beginTransaction();
        $stmt = $con->prepare($query);
        $stmt->bindParam(':sample_collected_date', $sampleCollectedDateFormatted);
        $stmt->bindParam(':result_date', $resultDateFormatted);
        $stmt->bindParam(':result_value', $resultValue);
        $stmt->bindParam(':result_notes', $resultNotes);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':performed_by_user_id', $performedByUserId);
        $stmt->bindParam(':patient_lab_test_id', $patientLabTestId);
        $stmt->execute();
        $con->commit();
        $message = 'Patient lab test updated successfully.';
    } catch (PDOException $ex) {
        $con->rollback();
        echo $ex->getMessage();
        echo $ex->getTraceAsString();
        exit;
    }
    header("Location: congratulation.php?goto_page=patient_lab_tests.php&message=$message&patient_id=$patientId");
    exit;
}

try {
    $query = "SELECT plt.id, p.patient_name, lt.test_name, lt.normal_range, lt.unit,
              plt.ordered_date, plt.sample_collected_date, plt.result_date,
              plt.result_value, plt.result_notes, plt.status,
              ou.display_name AS ordered_by_user, pu.display_name AS performed_by_user
              FROM patient_lab_tests plt
              JOIN patients p ON plt.patient_id = p.id
              JOIN lab_tests lt ON plt.lab_test_id = lt.id
              JOIN users ou ON plt.ordered_by_user_id = ou.id
              LEFT JOIN users pu ON plt.performed_by_user_id = pu.id
              WHERE plt.id = :patient_lab_test_id";
    $stmtPatientLabTest = $con->prepare($query);
    $stmtPatientLabTest->bindParam(':patient_lab_test_id', $patientLabTestId);
    $stmtPatientLabTest->execute();
    $patientLabTest = $stmtPatientLabTest->fetch(PDO::FETCH_ASSOC);
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
  <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <title>Update Patient Lab Test - Medical Care and Information system</title>

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
            <h1>Update Patient Lab Test</h1>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">

      <!-- Default box -->
     <div class="card card-outline card-primary shadow">
        <div class="card-header">
          <h3 class="card-title">Update Patient Lab Test Details</h3>
          
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <form method="post">
            <div class="row">
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <label>Patient Name</label>
                <input type="text" class="form-control form-control-sm" value="<?php echo $patientLabTest['patient_name'];?>" readonly/>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <label>Lab Test Name</label>
                <input type="text" class="form-control form-control-sm" value="<?php echo $patientLabTest['test_name'];?>" readonly/>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <label>Ordered Date</label>
                <input type="text" class="form-control form-control-sm" value="<?php echo date("M d, Y", strtotime($patientLabTest['ordered_date']));?>" readonly/>
              </div>
            </div>
            <div class="clearfix">&nbsp;</div>
            <div class="row">
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <div class="form-group">
                  <label for="sample_collected_date_input">Sample Collected Date</label>
                    <div class="input-group date" 
                    id="sample_collected_date" 
                    data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#sample_collected_date" name="sample_collected_date" 
                        data-toggle="datetimepicker" autocomplete="off" value="<?php echo $patientLabTest['sample_collected_date'] ? date('m/d/Y', strtotime($patientLabTest['sample_collected_date'])) : '';?>" id="sample_collected_date_input" placeholder="Select date"/>
                        <div class="input-group-append" 
                        data-target="#sample_collected_date" 
                        data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <div class="form-group">
                  <label for="result_date_input">Result Date</label>
                    <div class="input-group date" 
                    id="result_date" 
                    data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#result_date" name="result_date" 
                        data-toggle="datetimepicker" autocomplete="off" value="<?php echo $patientLabTest['result_date'] ? date('m/d/Y', strtotime($patientLabTest['result_date'])) : '';?>" id="result_date_input" placeholder="Select date"/>
                        <div class="input-group-append" 
                        data-target="#result_date" 
                        data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <label for="result_value">Result Value</label>
                <input type="text" id="result_value" name="result_value"
                  class="form-control form-control-sm" value="<?php echo $patientLabTest['result_value'];?>" placeholder="Enter result value"/>
              </div>
            </div>
            <div class="clearfix">&nbsp;</div>
            <div class="row">
              <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 mb-3">
                <label for="result_notes">Result Notes</label>
                <textarea id="result_notes" name="result_notes" class="form-control form-control-sm" rows="3" placeholder="Add any relevant notes"><?php echo $patientLabTest['result_notes'];?></textarea>
              </div>
              <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 mb-3">
                <label for="status">Status</label>
                <select id="status" name="status" required="required" class="form-control form-control-sm">
                  <?php echo getLabTestStatusOptions($patientLabTest['status']);?>
                </select>
              </div>
            </div>
            <div class="clearfix">&nbsp;</div>
            <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-right">
                <button type="submit" id="submit" 
                name="submit" class="btn btn-primary btn-sm btn-flat">Update Patient Lab Test</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </section>
  </div>

  <?php include './config/site_js_links.php'; ?>
  <script src="plugins/moment/moment.min.js"></script>
  <script src="plugins/daterangepicker/daterangepicker.js"></script>
  <script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>

<script>
  showMenuSelected("#mnu_lab_diag", "#mi_patient_lab_tests");

  var message = '<?php echo $message;?>';

  if(message !== '') {
    showCustomMessage(message);
  }
   
  $(document).ready(function() {
    $('#sample_collected_date, #result_date').datetimepicker({
        format: 'L'
    });
  });
</script>
</body>
</html>
