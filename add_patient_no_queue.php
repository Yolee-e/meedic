<?php
include './config/connection.php';
include './common_service/common_functions.php';

$message = '';
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
				header("Location:congratulation.php?goto_page=patients.php&message=".urlencode($message));
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
			if (function_exists('log_activity')) { log_activity($con, 'Add Patient (No Queue)', ['patient_id'=>$newPatientId, 'patient_name'=>$patientName]); }

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
			error_log("Patient save error (no queue): " . $ex->getMessage());

			// Set user-friendly error message
			$message = 'Failed to save patient. Please check your data and try again.';
			$insertOk = false;
		} catch(Exception $ex) {
			if ($con->inTransaction()) {
				$con->rollback();
			}

			// Log the error for debugging
			error_log("Patient save general error (no queue): " . $ex->getMessage());

			// Set user-friendly error message
			$message = 'An unexpected error occurred. Please try again.';
			$insertOk = false;
		}
	} else {
		// Validation failed - show error message to user
		$insertOk = false;
	}

	if ($insertOk) {
		header("Location:congratulation.php?goto_page=patients.php&message=$message");
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
 <title>Add Patient (No Queue) - Medical Care and Information system</title>
 <style>
		/* Keep styling consistent with add_patient, minor title tweak only */
		.card-title small { font-weight: 400; font-size: 70%; opacity: .9; }
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
          <h3 class="card-title">Add New Patient <small>(No Auto-Queue)</small></h3>
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


