<?php 
include './config/connection.php';
include './common_service/common_functions.php';

$message = '';
$patient_id = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
$patient_data = null;

// Get patient data if patient_id is provided
if ($patient_id > 0) {
    try {
        $stmt = $con->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->execute([$patient_id]);
        $patient_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        $message = "Error loading patient data: " . $ex->getMessage();
    }
}

// Get all patients for dropdown
$patients = getPatients($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './config/site_css_links.php'; ?>
    <?php include './config/data_tables_css.php'; ?>
    <title>New Prescription - Medical Care and Information System</title>
    <style>
        /* Enhanced Prescription UI Styling */
        .medicine-row {
            border: 1px solid #e3f2fd;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .medicine-row::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #007bff, #28a745, #ffc107, #dc3545);
        }
        
        .medicine-row:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
            border-color: #007bff;
        }
        .schedule-checkboxes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 8px;
            padding: 12px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            border: 1px solid #dee2e6;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
        }
        .schedule-checkbox {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 2px solid #e9ecef;
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .schedule-checkbox::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0,123,255,0.1), transparent);
            transition: left 0.5s;
        }
        .schedule-checkbox:hover {
            border-color: #007bff;
            background: linear-gradient(135deg, #e3f2fd 0%, #f8f9ff 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,123,255,0.15);
        }
        
        .schedule-checkbox:hover::before {
            left: 100%;
        }
        .schedule-checkbox input[type="checkbox"] {
            margin-right: 4px;
            transform: scale(0.9);
        }
        .schedule-checkbox input[type="checkbox"]:checked + label {
            color: #007bff;
            font-weight: 500;
        }
        .schedule-checkbox input[type="checkbox"]:checked {
            accent-color: #007bff;
        }
        .schedule-checkbox label {
            margin: 0;
            cursor: pointer;
            font-size: 12px;
            color: #495057;
            transition: color 0.2s ease;
        }
        .schedule-checkbox.checked {
            border-color: #007bff;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            box-shadow: 0 4px 12px rgba(0,123,255,0.2);
            transform: scale(1.02);
        }
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .schedule-checkboxes {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: 4px;
                padding: 6px;
            }
            .schedule-checkbox {
                padding: 3px 6px;
            }
            .schedule-checkbox label {
                font-size: 11px;
            }
        }
        
        @media (max-width: 576px) {
            .schedule-checkboxes {
                padding: 4px;
            }
            .schedule-checkbox {
                padding: 3px 5px;
            }
        }
        .prescription-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 50%, #28a745 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(0,123,255,0.3);
            position: relative;
            overflow: hidden;
        }
        
        .prescription-header::before {
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
        
        .prescription-header h2 {
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .prescription-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
        }
        .patient-info-card {
            background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%);
            border-left: 5px solid #007bff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,123,255,0.1);
            transition: all 0.3s ease;
        }
        
        .patient-info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,123,255,0.15);
        }
        
        .medicine-table {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .medicine-table:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        .btn-add-medicine {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(40,167,69,0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-add-medicine:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40,167,69,0.4);
            background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
        }
        
        .btn-add-medicine:active {
            transform: translateY(0);
        }
        
        .btn-remove-medicine {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(220,53,69,0.3);
            transition: all 0.3s ease;
        }
        
        .btn-remove-medicine:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(220,53,69,0.4);
            background: linear-gradient(135deg, #c82333 0%, #dc3545 100%);
        }
        .assessment-plan-section {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #ffc107;
            border-radius: 12px;
            padding: 25px;
            margin-top: 25px;
            box-shadow: 0 4px 15px rgba(255,193,7,0.2);
            transition: all 0.3s ease;
        }
        
        .assessment-plan-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255,193,7,0.3);
        }
        
        .assessment-plan-section h4 {
            color: #856404;
            font-weight: 700;
            margin-bottom: 15px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        /* Last Prescription Section Styling */
        .last-prescription-medicine {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }
        
        .last-prescription-medicine:hover {
            background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%);
            border-color: #2196f3;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(33, 150, 243, 0.1);
        }
        
        #last_prescription_section .card {
            border: 2px solid #e3f2fd;
            box-shadow: 0 4px 15px rgba(33, 150, 243, 0.1);
            transition: all 0.3s ease;
        }
        
        #last_prescription_section .card:hover {
            box-shadow: 0 8px 25px rgba(33, 150, 243, 0.15);
            transform: translateY(-2px);
        }
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            padding: 10px 15px;
        }
        
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.3rem rgba(0, 123, 255, 0.25);
            transform: translateY(-1px);
        }
        
        .form-control:hover {
            border-color: #007bff;
        }
        
        .form-group label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        .medicine-name-input {
            position: relative;
        }
        .autocomplete-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #007bff;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 15px rgba(0,123,255,0.2);
        }
        .autocomplete-suggestion {
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.2s ease;
            font-weight: 500;
        }
        
        .autocomplete-suggestion:hover {
            background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%);
            transform: translateX(5px);
            border-left: 3px solid #007bff;
        }
        
        .autocomplete-suggestion.active {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            transform: translateX(5px);
            border-left: 3px solid #ffffff;
        }
        
        /* Action Buttons Styling */
        .action-buttons-container {
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        
        .action-btn {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .action-btn:hover::before {
            left: 100%;
        }
        
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        
        .action-btn:active {
            transform: translateY(-1px);
        }
        
        .btn-success.action-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
        }
        
        .btn-success.action-btn:hover {
            background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
        }
        
        .btn-secondary.action-btn {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            border: none;
        }
        
        .btn-secondary.action-btn:hover {
            background: linear-gradient(135deg, #495057 0%, #6c757d 100%);
        }
        
        /* Loading Animation */
        .btn.loading {
            position: relative;
            color: transparent;
        }
        
        .btn.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Card Header Enhancements */
        .card-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border-bottom: none;
            border-radius: 12px 12px 0 0;
            padding: 20px 25px;
        }
        
        .card-header h3 {
            margin: 0;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .card-header .card-tools {
            margin-top: 0;
        }
        
        /* Enhanced Card Body */
        .card-body {
            padding: 25px;
        }
        
        /* Responsive Improvements */
        @media (max-width: 768px) {
            .action-buttons-container {
                padding: 15px;
            }
            
            .action-btn {
                padding: 10px 20px;
                font-size: 14px;
            }
            
            .prescription-header {
                padding: 20px;
            }
            
            .medicine-row {
                padding: 15px;
            }
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
  <div class="wrapper">
    <!-- Navbar -->
    <?php include './config/header.php';
        include './config/sidebar.php'; ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
         

      <!-- Main content -->
      <section class="content">
                <div class="container-fluid">
                    <!-- Prescription Header -->
                    <div class="prescription-header">
                        <h2><i class="fas fa-prescription-bottle-alt"></i> New Prescription</h2>
                        <p class="mb-0">Create a new prescription for patient consultation</p>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-warning alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <h5><i class="icon fas fa-exclamation-triangle"></i> Notice</h5>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form id="prescription_form" method="POST" action="ajax/save_prescription.php">
                        <?php echo csrf_field(); ?>
                        
                        <!-- Patient Information Section -->
                        <div class="card patient-info-card">
          <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-user"></i> Patient Information</h3>
          </div>
          <div class="card-body">
              <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="patient_select">Select Patient</label>
                                            <select class="form-control select2" id="patient_select" name="patient_id" required style="width: 100%;">
                                                <option value="">-- Select Patient --</option>
                                                <?php echo $patients; ?>
                </select>
              </div>
                                    </div>
                                    <div class="col-md-6">
                <div class="form-group">
                                            <label for="consultation_date">Date of Consultation</label>
                                            <input type="date" class="form-control" id="consultation_date" name="consultation_date" 
                                                   value="<?php echo date('Y-m-d'); ?>" readonly required>
              </div>
            </div>
          </div>
          
                                <!-- Auto-filled patient details (read-only) -->
                                <div id="patient_details" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Patient Name</label>
                                                <input type="text" class="form-control" id="patient_name" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Age</label>
                                                <input type="text" class="form-control" id="patient_age" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Sex</label>
                                                <input type="text" class="form-control" id="patient_gender" readonly>
                                            </div>
      </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label>Phone Number</label>
                                                <input type="text" class="form-control" id="patient_phone" readonly>
      </div>
      </div>
    </div>
    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="chief_complaint">Chief Complaint (CC)</label>
                                                <textarea class="form-control" id="chief_complaint" name="chief_complaint" rows="2" 
                                                          placeholder="Enter the main reason for consultation" style="resize: none;"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="subjective_objective">S/O (Subjective/Objective Notes)</label>
                                                <textarea class="form-control" id="subjective_objective" name="subjective_objective" rows="2" 
                                                          placeholder="Enter subjective and objective findings"></textarea>
                                            </div>
        </div>
      </div>
      
      <!-- Assessment & Plan Section -->
      <div class="row">
          <div class="col-md-12">
              <div class="assessment-plan-section">
                  <h4><i class="fas fa-clipboard-list"></i> Assessment & Plan (A/P)</h4>
                  <div class="form-group">
                      <textarea class="form-control" id="assessment_plan" name="assessment_plan" rows="4" 
                                placeholder="Enter assessment and treatment plan" required></textarea>
                  </div>
              </div>
          </div>
      </div>
      </div>
      </div>
      </div>
      
      <!-- Last Prescription Section -->
      <div id="last_prescription_section" style="display: none;">
          <div class="card">
              <div class="card-header" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border: 1px solid #2196f3;">
                  <h3 class="card-title" style="color: #1976d2; font-weight: 600;">
                      <i class="fas fa-history"></i> Last Visit Prescription
                  </h3>
              </div>
              <div class="card-body" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                  <div class="row mb-3">
                      <div class="col-md-6">
                          <strong>Date:</strong> <span id="last_prescription_date"></span>
                      </div>
                      <div class="col-md-6">
                          <strong>Doctor:</strong> <span id="last_prescription_doctor"></span>
                      </div>
                  </div>
                  
                  <div class="row mb-3">
                      <div class="col-md-12">
                          <strong>Chief Complaint:</strong> <span id="last_prescription_cc"></span>
                      </div>
                  </div>
                  
                  <div class="row mb-3">
                      <div class="col-md-12">
                          <strong>S/O:</strong> <span id="last_prescription_so"></span>
                      </div>
                  </div>
                  
                  <div class="row mb-3">
                      <div class="col-md-12">
                          <strong>Assessment & Plan:</strong> <span id="last_prescription_ap"></span>
                      </div>
                  </div>
                  
                  <div class="row">
                      <div class="col-md-12">
                          <strong>Medicines Prescribed:</strong>
                          <div id="last_prescription_medicines" class="mt-2">
                              <!-- Medicines will be loaded here -->
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>

                        <!-- Medicine Entry Section -->
                        <div class="card medicine-table">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-pills"></i> Prescribed Medicines</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-add-medicine" id="add_medicine_btn">
                                        <i class="fas fa-plus"></i> Add Medicine
        </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="medicines_container">
                                    <!-- Medicine rows will be added here dynamically -->
                                </div>
      </div>
    </div>


                        <!-- Action Buttons -->
                        <div class="row mt-5">
                            <div class="col-12 text-center">
                                <div class="action-buttons-container">
                                    <button type="submit" class="btn btn-success btn-lg action-btn" id="save_prescription_btn">
                                        <i class="fas fa-save"></i> Save Prescription
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-lg action-btn ml-3" onclick="window.history.back()">
                                        <i class="fas fa-arrow-left"></i> Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
</form>
</div>
</section>
</div>
<!-- /.content-wrapper -->

        <?php include './config/site_js_links.php'; ?>
        <?php include './config/data_tables_js.php'; ?>

<script>
            $(document).ready(function() {
                // Initialize Select2
                $('.select2').select2({
                    theme: 'default',
                    width: '100%'
                });

                // Add page load animations
                $('.card').each(function(index) {
                    $(this).css({
                        'opacity': '0',
                        'transform': 'translateY(30px)'
                    }).delay(index * 100).animate({
                        'opacity': '1'
                    }, 600, function() {
                        $(this).css('transform', 'translateY(0)');
                    });
                });

                // Handle patient selection
                $('#patient_select').on('change', function() {
                    const patientId = $(this).val();
                    if (patientId) {
                        loadPatientDetails(patientId);
                        loadLastPrescription(patientId);
                    } else {
                        $('#patient_details').hide();
                        $('#last_prescription_section').hide();
                    }
                });

                // Set initial patient if provided in URL
                <?php if ($patient_id > 0 && $patient_data): ?>
                    $('#patient_select').val(<?php echo $patient_id; ?>).trigger('change');
                <?php endif; ?>

                // Add first medicine row
                addMedicineRow();

                // Handle form submission
                $('#prescription_form').on('submit', function(e) {
                    e.preventDefault();
                    savePrescription();
                });
                
                // Add smooth scrolling for better UX
                $('html, body').animate({scrollTop: 0}, 500);
            });

            // Load patient details
            function loadPatientDetails(patientId) {
                $.ajax({
                    url: 'ajax/get_patient_details.php',
                    method: 'GET',
                    data: { patient_id: patientId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            const patient = response.data;
                            $('#patient_name').val(patient.patient_name);
                            $('#patient_age').val(calculateAge(patient.date_of_birth));
                            $('#patient_gender').val(patient.gender);
                            $('#patient_phone').val(patient.phone_number);
                            $('#patient_details').show();
                            
                            // Load appointment CC automatically
                            loadAppointmentCC(patientId);
                        } else {
                            alert('Error loading patient details: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error loading patient details');
                    }
                });
            }
            
            // Load last prescription for the patient
            function loadLastPrescription(patientId) {
                $.ajax({
                    url: 'ajax/get_last_prescription.php',
                    method: 'GET',
                    data: { patient_id: patientId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success' && response.data && response.data.id) {
                            const prescription = response.data;
                            displayLastPrescription(prescription);
                        } else {
                            $('#last_prescription_section').hide();
                        }
                    },
                    error: function() {
                        $('#last_prescription_section').hide();
                    }
                });
            }
            
            // Display last prescription
            function displayLastPrescription(prescription) {
                const medicinesHtml = prescription.medicines.map(med => `
                    <div class="last-prescription-medicine">
                        <div class="row">
                            <div class="col-md-4"><strong>${med.medicine_name}</strong></div>
                            <div class="col-md-2">Qty: ${med.quantity}</div>
                            <div class="col-md-3">Dosage: ${med.dosage}</div>
                            <div class="col-md-3">Schedule: ${med.schedule}</div>
                        </div>
                    </div>
                `).join('');
                
                $('#last_prescription_date').text(prescription.consultation_date);
                $('#last_prescription_doctor').text(prescription.doctor_name || 'Unknown Doctor');
                $('#last_prescription_cc').text(prescription.chief_complaint || 'Not specified');
                $('#last_prescription_so').text(prescription.subjective_objective || 'Not specified');
                $('#last_prescription_ap').text(prescription.assessment_plan || 'Not specified');
                $('#last_prescription_medicines').html(medicinesHtml);
                
                $('#last_prescription_section').show();
            }

            // Load appointment CC (Chief Complaint) from front desk
            function loadAppointmentCC(patientId) {
                console.log('Loading CC for patient:', patientId);
                $.ajax({
                    url: 'ajax/get_patient_appointment_cc.php',
                    method: 'GET',
                    data: { patient_id: patientId },
                    dataType: 'json',
                    success: function(response) {
                        console.log('CC Response:', response);
                        if (response.status === 'success' && response.data.chief_complaint) {
                            $('#chief_complaint').val(response.data.chief_complaint);
                            
                            // Make the field read-only and add visual styling
                            $('#chief_complaint').prop('readonly', true);
                            $('#chief_complaint').addClass('bg-light');
                            
                            // Show appointment details
                            const appointmentTime = response.data.appointment_time || 'Unknown time';
                            const appointmentDate = response.data.appointment_date || 'Today';
                            const status = response.data.status || 'Unknown status';
                            
                            const message = `Patient's reason for visit loaded from front desk appointment (${appointmentDate} at ${appointmentTime}, Status: ${status})`;
                            showNotification(message, 'success');
                        } else {
                            // If no CC found, make field editable
                            $('#chief_complaint').prop('readonly', false);
                            $('#chief_complaint').removeClass('bg-light');
                            console.log('No CC found for patient');
                        }
                    },
                    error: function(xhr, status, error) {
                        // If error, make field editable
                        $('#chief_complaint').prop('readonly', false);
                        $('#chief_complaint').removeClass('bg-light');
                        console.log('Error loading CC:', error);
                        console.log('Response:', xhr.responseText);
                    }
                });
            }

            // Show notification
            function showNotification(message, type = 'info') {
                // Remove existing notifications
                $('.notification-alert').remove();
                
                let alertClass, iconClass;
                switch(type) {
                    case 'success':
                        alertClass = 'alert-success';
                        iconClass = 'fas fa-check-circle';
                        break;
                    case 'error':
                        alertClass = 'alert-danger';
                        iconClass = 'fas fa-exclamation-circle';
                        break;
                    case 'warning':
                        alertClass = 'alert-warning';
                        iconClass = 'fas fa-exclamation-triangle';
                        break;
                    default:
                        alertClass = 'alert-info';
                        iconClass = 'fas fa-info-circle';
                }
                
                const notification = `
                    <div class="alert ${alertClass} alert-dismissible notification-alert" style="margin-top: 10px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <i class="${iconClass}"></i> ${message}
                    </div>
                `;
                $('.prescription-header').after(notification);
                
                // Add animation
                $('.notification-alert').hide().slideDown(300);
                
                // Auto-dismiss after 5 seconds
                setTimeout(function() {
                    $('.notification-alert').slideUp(300, function() {
                        $(this).remove();
                    });
                }, 5000);
            }

            // Calculate age from date of birth
            function calculateAge(dateOfBirth) {
                const today = new Date();
                const birthDate = new Date(dateOfBirth);
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                return age + ' years';
            }

            // Add medicine row
            function addMedicineRow() {
                const rowId = 'medicine_' + Date.now();
                const medicineRow = `
                    <div class="medicine-row" id="${rowId}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Medicine Name</label>
                                    <div class="medicine-name-input">
                                        <input type="text" class="form-control medicine-name" name="medicine_name[]" 
                                               placeholder="Enter medicine name" required autocomplete="off">
                                        <div class="autocomplete-suggestions"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input type="number" class="form-control" name="quantity[]" 
                                           placeholder="Qty" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Dosage</label>
                                    <input type="text" class="form-control" name="dosage[]" 
                                           placeholder="e.g., 1 tablet" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Schedule</label>
                                    <div class="schedule-checkboxes">
                                        <div class="schedule-checkbox">
                                            <input type="checkbox" name="schedule_${rowId}[]" value="Before Breakfast" id="bb_${rowId}">
                                            <label for="bb_${rowId}"><i class="fas fa-coffee text-warning" style="font-size: 10px;"></i> B.Breakfast</label>
                                        </div>
                                        <div class="schedule-checkbox">
                                            <input type="checkbox" name="schedule_${rowId}[]" value="After Breakfast" id="ab_${rowId}">
                                            <label for="ab_${rowId}"><i class="fas fa-coffee text-success" style="font-size: 10px;"></i> A.Breakfast</label>
                                        </div>
                                        <div class="schedule-checkbox">
                                            <input type="checkbox" name="schedule_${rowId}[]" value="Before Lunch" id="bl_${rowId}">
                                            <label for="bl_${rowId}"><i class="fas fa-utensils text-warning" style="font-size: 10px;"></i> B.Lunch</label>
                                        </div>
                                        <div class="schedule-checkbox">
                                            <input type="checkbox" name="schedule_${rowId}[]" value="After Lunch" id="al_${rowId}">
                                            <label for="al_${rowId}"><i class="fas fa-utensils text-success" style="font-size: 10px;"></i> A.Lunch</label>
                                        </div>
                                        <div class="schedule-checkbox">
                                            <input type="checkbox" name="schedule_${rowId}[]" value="Before Dinner" id="bd_${rowId}">
                                            <label for="bd_${rowId}"><i class="fas fa-drumstick-bite text-warning" style="font-size: 10px;"></i> B.Dinner</label>
                                        </div>
                                        <div class="schedule-checkbox">
                                            <input type="checkbox" name="schedule_${rowId}[]" value="After Dinner" id="ad_${rowId}">
                                            <label for="ad_${rowId}"><i class="fas fa-drumstick-bite text-success" style="font-size: 10px;"></i> A.Dinner</label>
                                        </div>
                                        <div class="schedule-checkbox">
                                            <input type="checkbox" name="schedule_${rowId}[]" value="Bedtime" id="bt_${rowId}">
                                            <label for="bt_${rowId}"><i class="fas fa-moon text-info" style="font-size: 10px;"></i> Bedtime</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-remove-medicine btn-block" onclick="removeMedicineRow('${rowId}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                $('#medicines_container').prepend(medicineRow);
                
                // Initialize autocomplete for this medicine name input
                initializeAutocomplete(rowId);
                
                // Initialize schedule checkbox interactions
                initializeScheduleCheckboxes(rowId);
            }

            // Remove medicine row
            function removeMedicineRow(rowId) {
                if ($('#medicines_container .medicine-row').length > 1) {
                    $('#' + rowId).remove();
                } else {
                    alert('At least one medicine is required');
                }
            }

            // Add medicine button click
            $('#add_medicine_btn').on('click', function() {
                addMedicineRow();
            });

            // Initialize autocomplete for medicine names
            function initializeAutocomplete(rowId) {
                const input = $(`#${rowId} .medicine-name`);
                const suggestions = $(`#${rowId} .autocomplete-suggestions`);
                
                input.on('input', function() {
                    const query = $(this).val();
                    if (query.length >= 2) {
                        $.ajax({
                            url: 'ajax/get_medicines.php',
                            method: 'GET',
                            data: { query: query },
        dataType: 'json',
                            success: function(response) {
                                if (response.status === 'success') {
                                    displaySuggestions(suggestions, response.data);
                                }
                            }
                        });
      } else {
                        suggestions.hide();
                    }
                });

                // Handle suggestion selection
                suggestions.on('click', '.autocomplete-suggestion', function() {
                    input.val($(this).text());
                    suggestions.hide();
                });

                // Hide suggestions when clicking outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest(`#${rowId}`).length) {
                        suggestions.hide();
          }
        });
      }

            // Display autocomplete suggestions
            function displaySuggestions(container, suggestions) {
                if (suggestions.length > 0) {
                    let html = '';
                    suggestions.forEach(function(suggestion) {
                        html += `<div class="autocomplete-suggestion">${suggestion.medicine_name}</div>`;
                    });
                    container.html(html).show();
                } else {
                    container.hide();
                }
            }

            // Initialize schedule checkbox interactions
            function initializeScheduleCheckboxes(rowId) {
                $(`#${rowId} .schedule-checkbox input[type="checkbox"]`).on('change', function() {
                    const checkbox = $(this);
                    const scheduleBox = checkbox.closest('.schedule-checkbox');
                    
                    if (checkbox.is(':checked')) {
                        scheduleBox.addClass('checked');
                        // Add visual feedback
                        scheduleBox.css('transform', 'scale(1.02)');
                        setTimeout(() => {
                            scheduleBox.css('transform', 'scale(1)');
                        }, 200);
                    } else {
                        scheduleBox.removeClass('checked');
                    }
                });

                // Add click handler to the entire schedule box for better UX
                $(`#${rowId} .schedule-checkbox`).on('click', function(e) {
                    if (e.target.type !== 'checkbox') {
                        const checkbox = $(this).find('input[type="checkbox"]');
                        checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
                    }
                });
            }

            // Save prescription
            function savePrescription() {
                // Validate form
                if (!validateForm()) {
                    return;
                }

                // Add loading state to save button
                const saveBtn = $('#save_prescription_btn');
                saveBtn.addClass('loading').prop('disabled', true);
                
                // Show loading message
                showNotification('Saving prescription...', 'info');

                const formData = new FormData($('#prescription_form')[0]);
                
                // Process medicine data
                const medicines = [];
                $('.medicine-row').each(function() {
                    const rowId = $(this).attr('id');
                    const medicineName = $(this).find('.medicine-name').val();
                    const quantity = $(this).find('input[name="quantity[]"]').val();
                    const dosage = $(this).find('input[name="dosage[]"]').val();
                    const schedules = [];
                    
                    $(this).find(`input[name="schedule_${rowId}[]"]:checked`).each(function() {
                        schedules.push($(this).val());
                    });

                    if (medicineName && quantity && dosage && schedules.length > 0) {
                        medicines.push({
                            medicine_name: medicineName,
                            quantity: quantity,
                            dosage: dosage,
                            schedule: schedules.join(', ')
                        });
                    }
                });

                // Add medicines to form data
                formData.append('medicines', JSON.stringify(medicines));

                $.ajax({
                    url: 'ajax/save_prescription.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        // Remove loading state
                        saveBtn.removeClass('loading').prop('disabled', false);
                        
                        if (response.status === 'success') {
                            let message = 'Prescription saved successfully!';
                            if (response.out_of_stock_medicines && response.out_of_stock_medicines.length > 0) {
                                message += '\n\n⚠️ Warning: The following medicines are not in stock:\n' + response.out_of_stock_medicines.join('\n');
                                message += '\n\nPlease inform the patient to purchase these medicines externally or restock the inventory.';
                            }
                            
                            // Show success notification
                            showNotification('Prescription saved successfully! Patient marked as done in queue.', 'success');
                            
                            // Add success animation
                            $('body').addClass('success-animation');
                            
                            // Redirect to print prescription after a short delay
                            setTimeout(() => {
                                // Check if we came from queue
                                const urlParams = new URLSearchParams(window.location.search);
                                const fromQueue = urlParams.get('from_queue');
                                const queueParam = fromQueue ? '&from_queue=1' : '';
                                window.location.href = 'print_prescription.php?id=' + response.prescription_id + queueParam;
                            }, 1500);
                        } else {
                            showNotification('Error saving prescription: ' + response.message, 'error');
                        }
                    },
                    error: function() {
                        // Remove loading state
                        saveBtn.removeClass('loading').prop('disabled', false);
                        showNotification('Error saving prescription', 'error');
                    }
                });
            }

            // Validate form
            function validateForm() {
                const patientId = $('#patient_select').val();
                const assessmentPlan = $('#assessment_plan').val();
                const medicineCount = $('.medicine-row').length;

                if (!patientId) {
                    alert('Please select a patient');
                    return false;
                }

                if (!assessmentPlan.trim()) {
                    alert('Assessment & Plan is required');
                    return false;
                }

                if (medicineCount === 0) {
                    alert('Please add at least one medicine');
                    return false;
                }

                // Validate each medicine row
                let validMedicines = 0;
                $('.medicine-row').each(function() {
                    const medicineName = $(this).find('.medicine-name').val();
                    const quantity = $(this).find('input[name="quantity[]"]').val();
                    const dosage = $(this).find('input[name="dosage[]"]').val();
                    const checkedSchedules = $(this).find('input[type="checkbox"]:checked').length;

                    if (medicineName && quantity && dosage && checkedSchedules > 0) {
                        validMedicines++;
                    }
                });

                if (validMedicines === 0) {
                    alert('Please fill in all medicine details and select at least one schedule for each medicine');
                    return false;
                }

                return true;
  }
</script>
    </div>
</body>
</html>