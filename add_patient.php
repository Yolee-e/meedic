<?php
include './config/connection.php';
include './common_service/common_functions.php';

$message = '';
$shouldQueue = false;
$insertOk = false;

if (isset($_POST['save_Patient'])) {
    csrf_validate_post();

    $firstName = trim($_POST['first_name']);
    $middleInitial = isset($_POST['middle_initial']) ? trim($_POST['middle_initial']) : '';
    $lastName = trim($_POST['last_name']);
    $address = trim($_POST['address']);
    $cnic = isset($_POST['cnic']) ? trim($_POST['cnic']) : '';
    $dateBirth = trim($_POST['date_of_birth']);
    $dateArr = explode("/", $dateBirth);
    $dateBirth = $dateArr[2].'-'.$dateArr[0].'-'.$dateArr[1];

    $phoneNumber = trim($_POST['phone_number']);
    $phoneNumber = preg_replace('/\D+/', '', $phoneNumber);
    $gender = $_POST['gender'];
    $chiefComplaints = isset($_POST['chief_complaints']) ? trim($_POST['chief_complaints']) : '';

    $firstName = ucwords(strtolower($firstName));
    $lastName = ucwords(strtolower($lastName));
    $middleInitial = strtoupper(substr($middleInitial, 0, 1));
    $patientName = trim($firstName.' '.($middleInitial !== '' ? $middleInitial.'. ' : '').$lastName);
    $address = ucwords(strtolower($address));

    if (!preg_match('/^09\d{9}$/', $phoneNumber)) {
      $message = 'Contact # must be 11 digits and start with 09.';
    }

    // Check if all required fields are filled and no validation errors
    if ($firstName != '' && $lastName != '' && $address != '' && $dateBirth != '' && $phoneNumber != '' && $gender != '' && $message == '') {
      // duplicate check by name+dob or phone
      try {
        $stmtDup = $con->prepare("SELECT id FROM patients WHERE (patient_name = :name AND date_of_birth = :dob) OR phone_number = :phone LIMIT 1");
        $stmtDup->bindParam(':name', $patientName, PDO::PARAM_STR);
        $stmtDup->bindParam(':dob', $dateBirth, PDO::PARAM_STR);
        $stmtDup->bindParam(':phone', $phoneNumber, PDO::PARAM_STR);
        $stmtDup->execute();
        $dupId = $stmtDup->fetchColumn();
        if ($dupId) {
          $message = 'Possible duplicate patient found. Please use Old Patient workflow.';
          header("Location:congratulation.php?goto_page=appointments.php&message=".urlencode($message));
          exit;
        }
      } catch (PDOException $ignored) {}
      
      try {
        // Check if there's already an active transaction
        if (!$con->inTransaction()) {
          $con->beginTransaction();
        }

        $stmtPatient = $con->prepare("INSERT INTO `patients`(`patient_name`,`address`,`cnic`,`date_of_birth`,`phone_number`,`gender`) VALUES (:name,:addr,:cnic,:dob,:phone,:gender)");
        $stmtPatient->bindParam(':name', $patientName);
        $stmtPatient->bindParam(':addr', $address);
        $stmtPatient->bindParam(':cnic', $cnic);
        $stmtPatient->bindParam(':dob', $dateBirth);
        $stmtPatient->bindParam(':phone', $phoneNumber);
        $stmtPatient->bindParam(':gender', $gender);
        $stmtPatient->execute();

        $newPatientId = (int)$con->lastInsertId();
        if (function_exists('log_activity')) { log_activity($con, 'Add Patient', ['patient_id'=>$newPatientId, 'patient_name'=>$patientName]); }
        
        // Create an appointment for the new patient
        $shouldQueue = (isset($_POST['queue_after_save']) && $_POST['queue_after_save'] == '1')
          || (isset($_GET['queue_after_save']) && $_GET['queue_after_save'] == '1');
        try {
          $status = 'Checked-In';
          $stmtAppt = $con->prepare("INSERT INTO appointments (patient_id, appointment_date, appointment_time, reason_for_visit, status) VALUES (:pid, CURDATE(), CURTIME(), :reason, :status)");
          $reason = $chiefComplaints !== '' ? $chiefComplaints : 'New patient registration';
          $stmtAppt->bindParam(':pid', $newPatientId, PDO::PARAM_INT);
          $stmtAppt->bindParam(':reason', $reason, PDO::PARAM_STR);
          $stmtAppt->bindParam(':status', $status, PDO::PARAM_STR);
          $stmtAppt->execute();
          if (function_exists('log_activity')) { log_activity($con, 'Create Appointment', ['patient_id'=>$newPatientId, 'status'=>$status]); }
        } catch (PDOException $ignored) {}

        if ($con->inTransaction()) {
          $con->commit();
        }

        $message = 'Patient added successfully.';
        $insertOk = true;

      } catch(PDOException $ex) {
        if ($con->inTransaction()) {
          $con->rollback();
        }
        
        // Log the error for debugging
        error_log("Patient save error: " . $ex->getMessage());
        error_log("Patient save error details: " . $ex->getMessage());
        
        // Set user-friendly error message
        $message = 'Failed to save patient. Please check your data and try again.';
        $insertOk = false;
      } catch(Exception $ex) {
        if ($con->inTransaction()) {
          $con->rollback();
        }
        
        // Log the error for debugging
        error_log("Patient save general error: " . $ex->getMessage());
        
        // Set user-friendly error message
        $message = 'An unexpected error occurred. Please try again.';
        $insertOk = false;
      }
    } else {
      // Validation failed - show error message to user
      $insertOk = false;
    }
    
    if ($insertOk) {
      if ($shouldQueue) {
        header("Location:congratulation.php?goto_page=page.php&message=$message");
      } else {
        header("Location:congratulation.php?goto_page=patients.php&message=$message");
      }
      exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <?php include './config/site_css_links.php';?>
 <?php include './config/data_tables_css.php';?>
 <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
 <title>Add Patient - Medical Care and Information system</title>
 <style>
        /* Enhanced Add Patient Styling */
        .add-patient-container {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            overflow: hidden;
            position: relative;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #007bff, #28a745, #ffc107, #dc3545);
        }
        
        .card-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border-bottom: none;
            border-radius: 12px 12px 0 0;
            padding: 20px 25px;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
        }
        
        @keyframes shimmer {
            0%, 100% { transform: translateX(-100%) translateY(-100%) rotate(0deg); }
            50% { transform: translateX(0%) translateY(0%) rotate(180deg); }
        }
        
        .card-header h3 {
            margin: 0;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
        }
        
        .card-body {
            padding: 30px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }
        
        /* Enhanced Form Styling */
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            font-weight: 700;
            color: #495057;
            margin-bottom: 8px;
            font-size: 0.95em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            font-size: 0.95em;
        }
        
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
            transform: translateY(-1px);
            background: #ffffff;
        }
        
        .form-control:hover {
            border-color: #007bff;
            transform: translateY(-1px);
        }
        
        .form-control-sm {
            padding: 10px 12px;
            font-size: 0.9em;
        }
        
        /* Enhanced Input Group Styling */
        .input-group {
            position: relative;
        }
        
        .input-group .form-control {
            border-radius: 8px 0 0 8px;
        }
        
        .input-group-append .input-group-text {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: 2px solid #007bff;
            border-left: none;
            border-radius: 0 8px 8px 0;
            color: white;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .input-group-append .input-group-text:hover {
            background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,123,255,0.3);
        }
        
        /* Enhanced Select Styling */
        select.form-control {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 40px;
            color: #495057 !important;
            background-color: #ffffff !important;
            border: 2px solid #e9ecef !important;
        }
        
        select.form-control option {
            color: #495057 !important;
            background-color: #ffffff !important;
            padding: 8px 12px;
        }
        
        select.form-control:focus {
            color: #495057 !important;
            background-color: #ffffff !important;
            border-color: #007bff !important;
        }
        
        select.form-control:hover {
            color: #495057 !important;
            background-color: #ffffff !important;
        }
        
        /* Enhanced Button Styling */
        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 12px 24px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
            color: white;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #495057 0%, #6c757d 100%);
            color: white;
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 0.9em;
        }
        
        /* Enhanced Alert Styling */
        .alert {
            border-radius: 8px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        /* Enhanced Small Text */
        .text-muted {
            color: #6c757d !important;
            font-size: 0.85em;
            font-weight: 500;
        }
        
        /* Enhanced Form Row */
        .row {
            margin-bottom: 0;
        }
        
        .col-lg-4, .col-md-4, .col-sm-6, .col-xs-12 {
            margin-bottom: 20px;
        }
        
        /* Enhanced Age Display */
        #age_display {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border: 2px solid #e3f2fd;
            color: #1976d2;
            font-weight: 600;
        }
        
        #age_display:focus {
            border-color: #1976d2;
            box-shadow: 0 0 0 0.2rem rgba(25,118,210,0.25);
        }
        
        /* Enhanced Duplicate Hint */
        #dup_hint {
            border-radius: 8px;
            border: none;
            padding: 15px 20px;
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        /* Enhanced Action Buttons */
        .text-right {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
        }
        
        .text-right .btn {
            margin-left: 10px;
        }
        
        .text-right .btn:first-child {
            margin-left: 0;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .card-body {
                padding: 20px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .text-right {
                text-align: center !important;
            }
            
            .text-right .btn {
                margin: 5px;
                display: inline-block;
            }
        }
        
        /* Loading Animation */
        .card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Form Validation States */
        .form-control.is-valid {
            border-color: #28a745;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='m2.3 6.73.94-.94 1.06 1.06L6.09 4.5l.94.94L4.3 8.77z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
        }
        
        .form-control.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.6 1.4 1.4 1.4-1.4'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
        }
        
        /* Enhanced Date Picker */
        .datetimepicker-input {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }
        
        .datetimepicker-input:focus {
            background: #ffffff;
        }
        
        /* Enhanced Phone Number Input */
        #phone_number {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            letter-spacing: 1px;
        }
        
        /* Enhanced Chief Complaints */
        #chief_complaints {
            min-height: 80px;
            resize: vertical;
        }
        
        /* Enhanced Gender Select */
        #gender {
            background: #ffffff !important;
            color: #495057 !important;
            border: 2px solid #e9ecef !important;
            padding: 10px 12px !important;
            font-size: 16px !important;
            min-height: 50px !important;
            line-height: 1.5 !important;
        }
        
        #gender:focus {
            background: #ffffff !important;
            color: #495057 !important;
            border-color: #007bff !important;
        }
        
        #gender:hover {
            background: #ffffff !important;
            color: #495057 !important;
        }
        
        #gender option {
            color: #495057 !important;
            background-color: #ffffff !important;
            padding: 12px 15px !important;
            font-size: 16px !important;
        }
        
        /* Override any conflicting styles for gender select */
        select#gender {
            color: #495057 !important;
            background-color: #ffffff !important;
        }
    </style>
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
    <?php echo getContentHeader(''); ?>

    <!-- Main content -->
    <section class="content">
      <!-- Default box -->
      <div class="card card-outline card-primary shadow">
        <div class="card-header">
          <h3 class="card-title">Add New Patient</h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <form method="post">
            <?php echo csrf_field(); ?>
            <?php if (isset($_GET['queue_after_save']) && $_GET['queue_after_save'] == '1') { ?>
            <input type="hidden" name="queue_after_save" value="1" />
            <?php } ?>
            <div class="row">
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <div class="form-group">
                  <label for="first_name">First Name</label>
                  <input type="text" id="first_name" name="first_name" required="required"
                    class="form-control form-control-sm" placeholder="Enter first name"/>
                </div>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <div class="form-group">
                  <label for="middle_initial">Middle Initial</label>
                  <input type="text" id="middle_initial" name="middle_initial" maxlength="1"
                    class="form-control form-control-sm" placeholder="M"/>
                </div>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <div class="form-group">
                  <label for="last_name">Last Name</label>
                  <input type="text" id="last_name" name="last_name" required="required"
                    class="form-control form-control-sm" placeholder="Enter last name"/>
                </div>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <div class="form-group">
                  <label for="address">Address</label> 
                  <input type="text" id="address" name="address" required="required"
                    class="form-control form-control-sm" placeholder="Enter address"/>
                </div>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <div class="form-group">
                  <label for="phone_number">Contact #</label>
                  <input type="text" id="phone_number" name="phone_number" required="required"
                    class="form-control form-control-sm" placeholder="09XXXXXXXXX" inputmode="numeric" pattern="09\d{9}" maxlength="11"/>
                  <small class="text-muted">Must start with 09 and be 11 digits.</small>
                </div>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <div class="form-group">
                  <label for="date_of_birth_input">Date of Birth</label>
                    <div class="input-group date" 
                    id="date_of_birth" 
                    data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#date_of_birth" name="date_of_birth" 
                        data-toggle="datetimepicker" autocomplete="off" id="date_of_birth_input" placeholder="Select date of birth"/>
                        <div class="input-group-append" 
                        data-target="#date_of_birth" 
                        data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                    <div class="mt-1">
                      <label class="mb-0" for="age_display">Age</label>
                      <input type="text" id="age_display" class="form-control form-control-sm" placeholder="" readonly />
                    </div>
                </div>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <div class="form-group">
                  <label for="gender">Gender</label>
                  <select class="form-control form-control-sm" id="gender" 
                  name="gender" required="required">
                    <?php echo getGender();?>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mb-3">
                <div class="form-group">
                  <label for="chief_complaints">Chief Complaints</label>
                  <textarea id="chief_complaints" name="chief_complaints"
                    class="form-control form-control-sm" placeholder="Enter chief complaints" rows="3"></textarea>
                </div>
              </div>
            </div>
              
            <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-right">
                <button type="submit" id="save_Patient" 
                name="save_Patient" class="btn btn-primary btn-sm">
                  <i class="fas fa-save"></i> Save Patient
                </button>
                <a href="patients.php" class="btn btn-secondary btn-sm ml-2">
                  <i class="fas fa-times"></i> Cancel
                </a>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div id="dup_hint" class="alert alert-warning mt-2 d-none"></div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </section>
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
  
  // Initialize the date picker with proper configuration
  $('#date_of_birth').datetimepicker({
    format: 'L',
    useCurrent: false,
    allowInputToggle: true,
    showTodayButton: true,
    showClear: true,
    showClose: true,
    icons: {
      time: 'fa fa-time',
      date: 'fa fa-calendar',
      up: 'fa fa-arrow-up',
      down: 'fa fa-arrow-down',
      previous: 'fa fa-chevron-left',
      next: 'fa fa-chevron-right',
      today: 'fa fa-calendar-check',
      clear: 'fa fa-trash',
      close: 'fa fa-times'
    }
  });
  
  // Ensure the date picker icon is clickable
  $(document).ready(function() {
    // Force re-initialization if needed
    if (!$('#date_of_birth').data('DateTimePicker')) {
      $('#date_of_birth').datetimepicker({
        format: 'L',
        useCurrent: false,
        allowInputToggle: true,
        showTodayButton: true,
        showClear: true,
        showClose: true
      });
    }
    
    // Ensure click events work on the icon
    $('.input-group-text').off('click').on('click', function(e) {
      e.preventDefault();
      $('#date_of_birth').datetimepicker('toggle');
    });
  });
  
  function updateAgeDisplayFromString(dateStr) {
    if (!dateStr) {
      $('#age_display').val('');
      return;
    }
    var dob = moment(dateStr, 'L', true);
    if (!dob.isValid()) {
      $('#age_display').val('');
      return;
    }
    var years = moment().diff(dob, 'years');
    $('#age_display').val(years + ' years');
  }
  
  $('#date_of_birth').on('change.datetimepicker', function(e) {
    if (e.date) {
      var years = moment().diff(e.date, 'years');
      $('#age_display').val(years + ' years');
    } else {
      updateAgeDisplayFromString($('#date_of_birth_input').val());
    }
  });
  
  $('#date_of_birth_input').on('change keyup blur', function() {
    updateAgeDisplayFromString(this.value);
  });
  
  // initialize on load if value present
  updateAgeDisplayFromString($('#date_of_birth_input').val());

  // Duplicate detection on name + age
  function checkDuplicate(){
    var first = ($('#first_name').val()||'').trim();
    var mi = ($('#middle_initial').val()||'').trim();
    var last = ($('#last_name').val()||'').trim();
    var dob = ($('#date_of_birth_input').val()||'').trim();
    if (!first || !last || !dob) { $('#save_Patient').prop('disabled', false); return; }
    $.getJSON('ajax/check_duplicate_patient.php', { first_name:first, middle_initial:mi, last_name:last, date_of_birth:dob }, function(res){
      var $hint = $('#dup_hint');
      if (res && res.status==='success' && res.duplicate) {
        $('#save_Patient').prop('disabled', true);
        var pid = res.patient_id || '';
        var msg = 'Possible duplicate found.';
        if (pid) {
          var linkHist = '<a href="patient_history.php?patient_id='+pid+'" class="alert-link">View History</a>';
          var linkEdit = '<a href="update_patient.php?id='+pid+'" class="alert-link">Edit Patient</a>';
          msg += ' '+linkHist+' or '+linkEdit+'.';
        }
        $hint.removeClass('d-none').html(msg);
      } else {
        $('#save_Patient').prop('disabled', false);
        $hint.addClass('d-none').empty();
      }
    });
  }
  $('#first_name, #middle_initial, #last_name').on('blur change keyup', function(){ checkDuplicate(); });
  $('#date_of_birth').on('change.datetimepicker', function(){ checkDuplicate(); });
  $('#date_of_birth_input').on('blur', function(){ checkDuplicate(); });

  // Phone number enforcement: always start with 09, max 12 digits
  function sanitizePhone(val) {
    var digits = (val||'').replace(/\D+/g, '');
    if (digits.indexOf('09') !== 0) {
      digits = '09' + digits.replace(/^0+/, '');
    }
    if (digits.length > 11) {
      digits = digits.slice(0, 11);
    }
    return digits;
  }
  var $phone = $('#phone_number');
  // initialize default prefix if empty
  if ($phone.val().trim() === '') {
    $phone.val('09');
  } else {
    $phone.val(sanitizePhone($phone.val()))
  }
  $phone.on('input blur change', function() {
    var caretPos = this.selectionStart;
    var beforeLen = this.value.length;
    var sanitized = sanitizePhone(this.value);
    this.value = sanitized;
    // try to keep caret stable
    var afterLen = sanitized.length;
    var delta = afterLen - beforeLen;
    var newPos = Math.max(2, (caretPos||afterLen) + delta);
    this.setSelectionRange(newPos, newPos);
  });
</script>
</body>
</html>
