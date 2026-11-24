<?php
include './config/connection.php';
include './common_service/common_functions.php';

$message = '';
if (isset($_POST['save_Patient'])) {
    csrf_validate_post();
  
    $hiddenId = $_POST['hidden_id'];

    $patientName = trim($_POST['patient_name']);
    $address = trim($_POST['address']);
    $cnic = trim($_POST['cnic']);
    
    $dateBirth = trim($_POST['date_of_birth']);
    $dateArr = explode("/", $dateBirth);

    $dateBirth = $dateArr[2].'-'.$dateArr[0].'-'.$dateArr[1];

    $phoneNumber = trim($_POST['phone_number']);

    $patientName = ucwords(strtolower($patientName));
    $address = ucwords(strtolower($address));

    $gender = $_POST['gender'];
if ($patientName != '' && $address != '' && 
  $cnic != '' && $dateBirth != '' && $phoneNumber != '' && $gender != '') {
      $query = "UPDATE `patients` 
        SET `patient_name` = :name,
            `address` = :addr,
            `cnic` = :cnic,
            `date_of_birth` = :dob,
            `phone_number` = :phone,
            `gender` = :gender
        WHERE `id` = :id";
try {

  $con->beginTransaction();

  $stmtPatient = $con->prepare($query);
  $stmtPatient->bindParam(':name', $patientName);
  $stmtPatient->bindParam(':addr', $address);
  $stmtPatient->bindParam(':cnic', $cnic);
  $stmtPatient->bindParam(':dob', $dateBirth);
  $stmtPatient->bindParam(':phone', $phoneNumber);
  $stmtPatient->bindParam(':gender', $gender);
  $stmtPatient->bindParam(':id', $hiddenId, PDO::PARAM_INT);
  $stmtPatient->execute();

  $con->commit();

  $message = 'Patient updated successfully.';

} catch(PDOException $ex) {
  $con->rollback();

  echo $ex->getMessage();
  echo $ex->getTraceAsString();
  exit;
}
}
  header("Location:congratulation.php?goto_page=patients.php&message=$message");
  exit;
}



try {
$id = $_GET['id'];
$query = "SELECT `id`, `patient_name`, `address`, 
`cnic`, date_format(`date_of_birth`, '%m/%d/%Y') as `date_of_birth`,  `phone_number`, `gender` 
FROM `patients` where `id` = $id;";

  $stmtPatient1 = $con->prepare($query);
  $stmtPatient1->execute();
  $row = $stmtPatient1->fetch(PDO::FETCH_ASSOC);

  $gender = $row['gender'];

$dob = $row['date_of_birth']; 
} catch(PDOException $ex) {

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

  <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <title>Update Pateint Details - Medical Care and Information system</title>

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
            <h1>Update Patient</h1>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">

      <!-- Default box -->
     <div class="card card-outline card-primary shadow">
        <div class="card-header">
          <h3 class="card-title">Update Patient Details</h3>
          
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
            
          </div>
        </div>
        <div class="card-body">
          <form method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="hidden_id" 
            value="<?php echo (int)$row['id'];?>">
            <div class="row">
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <label for="patient_name">Patient Name</label>
                <input type="text" id="patient_name" name="patient_name" required="required"
                  class="form-control form-control-sm" value="<?php echo htmlspecialchars($row['patient_name']);?>" placeholder="Enter patient name"/>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <label for="address">Address</label> 
                <input type="text" id="address" name="address" required="required"
                  class="form-control form-control-sm" value="<?php echo htmlspecialchars($row['address']);?>" placeholder="Enter address"/>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
               
              </div>
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <div class="form-group">
                  <label for="date_of_birth_input">Date of Birth</label>
                    <div class="input-group date" 
                    id="date_of_birth" 
                    data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#date_of_birth" name="date_of_birth" 
                        value="<?php echo htmlspecialchars($dob);?>" id="date_of_birth_input" placeholder="Select date of birth"/>
                        <div class="input-group-append" 
                        data-target="#date_of_birth" 
                        data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
              
              </div>
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <label for="phone_number">Phone Number</label>
                <input type="text" id="phone_number" name="phone_number" required="required"
                  class="form-control form-control-sm" value="<?php echo htmlspecialchars($row['phone_number']);?>" placeholder="Enter phone number"/>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <label for="gender">Gender</label>
                <!-- $gender -->

                <select class="form-control form-control-sm" id="gender" 
                name="gender" required="required">
                 <?php echo getGender($gender);?>
                </select>
                
              </div>
            </div>
              
            <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-right">
                <button type="submit" id="save_Patient" 
                name="save_Patient" class="btn btn-primary btn-sm btn-flat">Update Patient</button>
              </div>
            </div>
          </form>
        </div>
        
      </div>
      
    </section>
     <br/>
     <br/>
     <br/>

 
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <?php include './config/site_js_links.php'; ?>
  <?php include './config/data_tables_js.php'; ?>


<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>

<script>
  showMenuSelected("#mnu_patients", "#mi_patients");

  var message = '<?php echo $message;?>';

  if(message !== '') {
    showCustomMessage(message);
  }
  $('#date_of_birth').datetimepicker({
        format: 'L'
    });
      
    
   // DataTable initialization removed - this page doesn't have all_patients table

</script>
</body>
</html>