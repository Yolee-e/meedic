<?php 
include './config/connection.php';
include './common_service/common_functions.php';

$patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
$isDetail = $patientId > 0;

$patientRow = null;
if ($isDetail) {
  try {
    $stmtP = $con->prepare("SELECT id, patient_name, phone_number, address, date_of_birth, gender, cnic FROM patients WHERE id = :id LIMIT 1");
    $stmtP->bindParam(':id', $patientId, PDO::PARAM_INT);
    $stmtP->execute();
    $patientRow = $stmtP->fetch(PDO::FETCH_ASSOC);
  } catch(PDOException $ex) {
    $patientRow = null;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <?php include './config/site_css_links.php';?>
 <?php include './config/data_tables_css.php';?>
 <title>Patient History - Medical Care and Information system</title>
 <style>
        /* Enhanced Patient History Styling */
        .patient-history-container {
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
            padding: 25px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }
        
        /* Enhanced Table Styling */
        .table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .table thead th {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border: none;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            padding: 15px 10px;
            text-align: center;
        }
        
        .table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #e9ecef;
        }
        
        .table tbody tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .table tbody td {
            padding: 15px 10px;
            vertical-align: middle;
            border: none;
        }
        
        /* Enhanced Buttons */
        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 8px 16px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            border: none;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
        }
        
        .btn-outline-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            border: none;
        }
        
        .btn-outline-secondary:hover {
            background: linear-gradient(135deg, #495057 0%, #6c757d 100%);
            color: white;
        }
        
        .btn-outline-primary {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            border: none;
        }
        
        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #138496 0%, #17a2b8 100%);
            color: white;
        }
        
        /* Patient Info Card */
        .patient-info-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .patient-info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .patient-info-card .row > div {
            margin-bottom: 15px;
        }
        
        .patient-info-card strong {
            color: #495057;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.9em;
            letter-spacing: 0.5px;
        }
        
        /* Prescription Cards */
        .prescription-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            overflow: hidden;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .prescription-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .prescription-card .card-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-bottom: none;
            padding: 20px 25px;
        }
        
        .prescription-card .card-header h3 {
            margin: 0;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .prescription-card .card-body {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            padding: 25px;
        }
        
        /* Legacy Visit Cards */
        .legacy-visit-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            overflow: hidden;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .legacy-visit-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .legacy-visit-card .card-header {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            border-bottom: none;
            padding: 20px 25px;
        }
        
        .legacy-visit-card .card-header h3 {
            margin: 0;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .legacy-visit-card .card-body {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            padding: 25px;
        }
        
        /* File Upload Section */
        .file-upload-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            overflow: hidden;
            position: relative;
        }
        
        .file-upload-card .card-header {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            border-bottom: none;
            padding: 20px 25px;
        }
        
        .file-upload-card .card-header h3 {
            margin: 0;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .file-upload-card .card-body {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            padding: 25px;
        }
        
        /* Form Controls */
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }
        
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
            transform: translateY(-1px);
        }
        
        .form-control-file {
            border-radius: 8px;
            padding: 12px 15px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control-file:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }
        
        /* Medicine Tables */
        .medicine-table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-top: 15px;
        }
        
        .medicine-table thead th {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
            border: none;
            font-weight: 700;
            padding: 15px 10px;
            text-align: center;
        }
        
        .medicine-table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #e9ecef;
        }
        
        .medicine-table tbody tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transform: translateY(-1px);
        }
        
        .medicine-table tbody td {
            padding: 12px 10px;
            vertical-align: middle;
            border: none;
        }
        
        /* Assessment Plan */
        .assessment-plan {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin-top: 15px;
            box-shadow: 0 2px 8px rgba(255,193,7,0.2);
        }
        
        .assessment-plan strong {
            color: #856404;
            font-weight: 700;
        }
        
        /* Alerts */
        .alert {
            border-radius: 8px;
            border: none;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .alert-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .card-body {
                padding: 20px;
            }
            
            .patient-info-card {
                padding: 20px;
            }
            
            .table-responsive {
                border-radius: 8px;
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
        
        /* Staggered Animation */
        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }
        .card:nth-child(4) { animation-delay: 0.4s; }
        .card:nth-child(5) { animation-delay: 0.5s; }
        .card:nth-child(6) { animation-delay: 0.6s; }
        
        /* Prescription File Upload Styling */
        .prescription-file-upload {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .prescription-file-upload .form-control-file {
            border: 2px dashed #6c757d;
            border-radius: 8px;
            padding: 10px;
            background: white;
            transition: all 0.3s ease;
        }
        
        .prescription-file-upload .form-control-file:hover {
            border-color: #007bff;
            background: #f8f9fa;
        }
        
        .prescription-files-table {
            margin-top: 10px;
        }
        
        .prescription-files-table .btn-xs {
            padding: 4px 8px;
            font-size: 0.75rem;
            margin: 2px;
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
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
          
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">

      <!-- Default box -->
      <div class="card card-outline card-primary shadow">
        <div class="card-header">
          <?php if(!$isDetail) { ?>
          <h3 class="card-title">Patients</h3>
          <?php } else { ?>
          <h3 class="card-title">Patient History - <?php echo $patientRow ? htmlspecialchars($patientRow['patient_name']) : 'Unknown'; ?></h3>
          <div class="card-tools">
            <a href="patient_history.php" class="btn btn-sm btn-outline-secondary">&laquo; Back to Patients</a>
          </div>
          <?php } ?>

          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
            
          </div>
        </div>
        <div class="card-body">
          <?php if(!$isDetail) { ?>
          <div class="row">
            <div class="col-md-12 table-responsive">
              <table id="tbl_patients" class="table table-striped table-bordered">
                <thead>
                  <tr class="bg-gradient-primary text-light">
                    <th class="p-1 text-center">S.No</th>
                    <th class="p-1">Patient</th>
                    <th class="p-1">Phone</th>
                    <th class="p-1">DOB</th>
                    <th class="p-1">Last Visit</th>
                    <th class="p-1 text-center">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  try {
                    $stmt = $con->prepare("SELECT p.id, p.patient_name, p.phone_number, p.date_of_birth,
                                            (SELECT MAX(visit_date) FROM patient_visits v WHERE v.patient_id = p.id) AS last_visit
                                            FROM patients p ORDER BY p.patient_name ASC");
                    $stmt->execute();
                    $sno = 0;
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { $sno++; ?>
                      <tr>
                        <td class="text-center"><?php echo $sno; ?></td>
                        <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['date_of_birth']); ?></td>
                        <td><?php echo $row['last_visit'] ? htmlspecialchars($row['last_visit']) : '-'; ?></td>
                        <td class="text-center">
                          <a class="btn btn-sm btn-primary" href="patient_history.php?patient_id=<?php echo (int)$row['id']; ?>">View Medical Record</a>
                        </td>
                      </tr>
                  <?php }
                  } catch(PDOException $ex) { }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
          <?php } else { ?>
          <div class="row">
            <div class="col-md-12">
              <?php 
              if (!$patientRow) {
                echo '<div class="alert alert-warning">Patient not found.</div>';
              } else {
                $ageYears = '';
                if (!empty($patientRow['date_of_birth'])) {
                  try {
                    $dob = new DateTime($patientRow['date_of_birth']);
                    $now = new DateTime();
                    $ageYears = $dob->diff($now)->y;
                  } catch (Exception $e) { $ageYears = ''; }
                }
                echo '<div class="patient-info-card">'
                  .'<div class="row">'
                  .'<div class="col-md-6 mb-3"><strong>Patient:</strong> '.htmlspecialchars($patientRow['patient_name']).'</div>'
                  .'<div class="col-md-3 mb-3"><strong>Gender:</strong> '.htmlspecialchars($patientRow['gender']).'</div>'
                  .'<div class="col-md-3 mb-3"><strong>Phone:</strong> '.htmlspecialchars($patientRow['phone_number']).'</div>'
                  .'<div class="col-md-6 mb-3"><strong>Address:</strong> '.htmlspecialchars($patientRow['address']).'</div>'
                  .'<div class="col-md-3 mb-3"><strong>DOB:</strong> '.htmlspecialchars($patientRow['date_of_birth']).($ageYears!==''?' <span class="text-muted">('.$ageYears.' yrs)</span>':'').'</div>'
                  .'</div>'
                  .'</div>';
                // Load prescriptions from new prescription system
                try {
                  $stmtPrescriptions = $con->prepare("SELECT p.id, p.consultation_date, p.chief_complaint, p.subjective_objective, p.assessment_plan, p.status, p.created_at, u.display_name as doctor_name
                                                      FROM prescriptions p
                                                      LEFT JOIN users u ON u.id = p.doctor_id
                                                      WHERE p.patient_id = :pid 
                                                      ORDER BY p.consultation_date DESC, p.created_at DESC");
                  $stmtPrescriptions->bindParam(':pid', $patientId, PDO::PARAM_INT);
                  $stmtPrescriptions->execute();
                  $hasPrescriptions = false;
                  
                  while($prescription = $stmtPrescriptions->fetch(PDO::FETCH_ASSOC)) {
                    $hasPrescriptions = true;
                    
                    // Load medicines for this prescription
                    $stmtMeds = $con->prepare("SELECT medicine_name, quantity, dosage, schedule, notes 
                                               FROM prescription_medicines 
                                               WHERE prescription_id = :prescription_id 
                                               ORDER BY id ASC");
                    $stmtMeds->bindParam(':prescription_id', $prescription['id'], PDO::PARAM_INT);
                    $stmtMeds->execute();
                    $medicines = $stmtMeds->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo '<div class="prescription-card">';
                    echo '<div class="card-header">';
                    echo '<h3 class="card-title mb-0">';
                    echo '<i class="fas fa-prescription-bottle-alt"></i> ';
                    echo 'Prescription - '.htmlspecialchars($prescription['consultation_date']);
                    
                    // Add status badge
                    $status = $prescription['status'] ?: 'Completed';
                    $statusClass = '';
                    $statusIcon = '';
                    switch($status) {
                        case 'Completed':
                            $statusClass = 'badge-success';
                            $statusIcon = 'fas fa-check-circle';
                            break;
                        case 'Draft':
                            $statusClass = 'badge-warning';
                            $statusIcon = 'fas fa-edit';
                            break;
                        case 'Cancelled':
                            $statusClass = 'badge-danger';
                            $statusIcon = 'fas fa-times-circle';
                            break;
                        default:
                            $statusClass = 'badge-secondary';
                            $statusIcon = 'fas fa-question-circle';
                    }
                    echo '<span class="badge '.$statusClass.' ml-2"><i class="'.$statusIcon.'"></i> '.ucfirst($status).'</span>';
                    echo '</h3>';
                    echo '<div class="card-tools">';
                    echo '<a href="print_prescription.php?id='.$prescription['id'].'" class="btn btn-outline-primary" target="_blank">';
                    echo '<i class="fas fa-print"></i> Print</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="card-body">';
                    
                    // Prescription details
                    echo '<div class="row mb-3">';
                    echo '<div class="col-md-6">';
                    echo '<strong>Doctor:</strong> '.htmlspecialchars($prescription['doctor_name'] ?: 'Unknown').'<br>';
                    echo '<strong>Date:</strong> '.htmlspecialchars($prescription['consultation_date']).'<br>';
                    echo '<strong>Chief Complaint:</strong> '.htmlspecialchars($prescription['chief_complaint'] ?: 'Not specified');
                    echo '</div>';
                    echo '<div class="col-md-6">';
                    if ($prescription['subjective_objective']) {
                      echo '<strong>S/O:</strong> '.htmlspecialchars($prescription['subjective_objective']).'<br>';
                    }
                    echo '</div>';
                    echo '</div>';
                    
                    if ($prescription['assessment_plan']) {
                      echo '<div class="assessment-plan">';
                      echo '<strong>Assessment & Plan:</strong><br>';
                      echo '<div class="mt-2">'.nl2br(htmlspecialchars($prescription['assessment_plan'])).'</div>';
                      echo '</div>';
                    }
                    
                    // Medicines table
                    if (!empty($medicines)) {
                      echo '<h5><i class="fas fa-pills"></i> Prescribed Medicines</h5>';
                      echo '<div class="table-responsive">';
                      echo '<table class="table table-bordered medicine-table">';
                      echo '<thead>';
                      echo '<tr><th>Medicine</th><th>Qty</th><th>Dosage</th><th>Schedule</th>';
                      if (array_filter(array_column($medicines, 'notes'))) {
                        echo '<th>Notes</th>';
                      }
                      echo '</tr></thead><tbody>';
                      
                      foreach($medicines as $med) {
                        echo '<tr>';
                        echo '<td>'.htmlspecialchars($med['medicine_name']).'</td>';
                        echo '<td class="text-center">'.(int)$med['quantity'].'</td>';
                        echo '<td>'.htmlspecialchars($med['dosage']).'</td>';
                        echo '<td>'.htmlspecialchars($med['schedule']).'</td>';
                        if (array_filter(array_column($medicines, 'notes'))) {
                          echo '<td>'.htmlspecialchars($med['notes'] ?: '').'</td>';
                        }
                        echo '</tr>';
                      }
                      echo '</tbody></table>';
                      echo '</div>';
                    }
                    
                    // Laboratory & Diagnostic Files for this prescription
                    echo '<div class="mt-4">';
                    echo '<h5><i class="fas fa-file-medical"></i> Laboratory & Diagnostic Files</h5>';
                    echo '<form class="prescription-file-upload mb-3" enctype="multipart/form-data" data-prescription-id="'.$prescription['id'].'">';
                    echo '<input type="hidden" name="patient_id" value="'.(int)$patientId.'" />';
                    echo '<input type="hidden" name="prescription_id" value="'.$prescription['id'].'" />';
                    echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['csrf_token'].'" />';
                    echo '<div class="form-row">';
                    echo '<div class="col-md-8 mb-2"><input type="file" name="file" class="form-control-file" required /></div>';
                    echo '<div class="col-md-4 mb-2 text-right"><button type="submit" class="btn btn-outline-primary btn-sm">Upload</button></div>';
                    echo '</div>';
                    echo '</form>';
                    echo '<div class="table-responsive">';
                    echo '<table class="table table-bordered medicine-table prescription-files-table" data-prescription-id="'.$prescription['id'].'">';
                    echo '<thead><tr><th>Filename</th><th>Type</th><th>Uploaded</th><th class="text-center">Action</th></tr></thead><tbody></tbody>';
                    echo '</table>';
                    echo '</div>';
                    echo '</div>';
                    
                    echo '</div></div>';
                  }
                  
                  if (!$hasPrescriptions) {
                    echo '<div class="alert alert-info">No prescription history found for this patient.</div>';
                  }
                } catch(PDOException $ex) {
                  echo '<div class="alert alert-warning">Could not load prescription history: '.htmlspecialchars($ex->getMessage()).'</div>';
                }
                
                // Also load old visit history for backward compatibility
                try {
                  $stmtV = $con->prepare("SELECT id, visit_date, next_visit_date, bp, weight, disease FROM patient_visits WHERE patient_id = :pid ORDER BY visit_date DESC, id DESC");
                  $stmtV->bindParam(':pid', $patientId, PDO::PARAM_INT);
                  $stmtV->execute();
                  $hasAny = false;
                  while($v = $stmtV->fetch(PDO::FETCH_ASSOC)) {
                    // Load medications for this visit first; skip visit if none
                    $medRowsHtml = '';
                    $hasRx = false;
                    try {
                      $stmtRx = $con->prepare("SELECT m.medicine_name, md.packing, pmh.quantity, pmh.dosage
                                                FROM patient_medication_history pmh
                                                JOIN medicine_details md ON md.id = pmh.medicine_details_id
                                                JOIN medicines m ON m.id = md.medicine_id
                                                WHERE pmh.patient_visit_id = :vid ORDER BY pmh.id ASC");
                      $stmtRx->bindParam(':vid', $v['id'], PDO::PARAM_INT);
                      $stmtRx->execute();
                      while($r = $stmtRx->fetch(PDO::FETCH_ASSOC)) {
                        $hasRx = true;
                        $medRowsHtml .= '<tr>'
                          .'<td>'.htmlspecialchars($r['medicine_name']).'</td>'
                          .'<td>'.htmlspecialchars($r['packing']).'</td>'
                          .'<td class="text-right">'.(int)$r['quantity'].'</td>'
                          .'<td>'.htmlspecialchars($r['dosage']).'</td>'
                          .'</tr>';
                      }
                    } catch(PDOException $ex) { $hasRx = false; }

                    if (!$hasRx) { continue; }

                    $hasAny = true;
                    echo '<div class="legacy-visit-card">';
                    echo '<div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-calendar-alt"></i> Legacy Visit: '.htmlspecialchars($v['visit_date']).'</h3></div>';
                    echo '<div class="card-body">';
                    echo '<div class="mb-2">'
                      .'<strong>Disease:</strong> '.htmlspecialchars($v['disease']).' &nbsp; '
                      .'<strong>BP:</strong> '.htmlspecialchars($v['bp']).' &nbsp; '
                      .'<strong>Weight:</strong> '.htmlspecialchars($v['weight']).'</div>';
                    echo '<div class="table-responsive">'
                        .'<table class="table table-bordered medicine-table">'
                        .'<thead><tr><th>Medicine</th><th>Packing</th><th class="text-center">Qty</th><th>Dosage</th></tr></thead><tbody>'
                        .$medRowsHtml
                        .'</tbody></table></div>';
                    echo '</div></div>';
                  }
                } catch(PDOException $ex) {
                  // Silently ignore old system errors
                }
              }
              ?>
            </div>
          </div>
          <script>
          (function(){
            function run(){
              // Load files for a specific prescription
              function loadPrescriptionFiles(prescriptionId){
                $.getJSON('ajax/get_prescription_files.php?prescription_id=' + prescriptionId, function(res){
                  const $tb = $('.prescription-files-table[data-prescription-id="' + prescriptionId + '"] tbody');
                  $tb.empty();
                  if(!(res && res.status==='success')) return;
                  (res.data||[]).forEach(function(f){
                    const name = $('<div>').text(f.original_name||f.stored_name).html();
                    const mimeType = f.mime_type || '';
                    const isImage = mimeType.startsWith('image/');
                    const isPDF = mimeType === 'application/pdf';
                    
                    // Get file type icon and description
                    let typeIcon = '📄';
                    let typeDesc = 'File';
                    if (isImage) {
                      typeIcon = '🖼️';
                      typeDesc = 'Image';
                    } else if (isPDF) {
                      typeIcon = '📋';
                      typeDesc = 'PDF';
                    }
                    
                    const row = '<tr>'
                      + '<td>'+name+'</td>'
                      + '<td><span class="badge badge-info">'+typeIcon+' '+typeDesc+'</span></td>'
                      + '<td>'+ (f.created_at||'') +'</td>'
                      + '<td class="text-center">'
                      + '<a class="btn btn-xs btn-outline-primary mr-1" href="download_patient_file.php?id='+f.id+'" target="_blank">'
                      + '<i class="fas fa-download"></i> Download</a>'
                      + (isImage ? '<button class="btn btn-xs btn-outline-success view-image-btn mr-1" data-file-id="'+f.id+'" data-file-name="'+name+'">'
                      + '<i class="fas fa-eye"></i> View</button>' : '')
                      + '<button class="btn btn-xs btn-outline-danger delete-file-btn" data-file-id="'+f.id+'" data-file-name="'+name+'" data-prescription-id="'+prescriptionId+'">'
                      + '<i class="fas fa-trash"></i> Delete</button>'
                      + '</td>'
                      + '</tr>';
                    $tb.append(row);
                  });
                });
              }
              
              // Handle prescription file uploads
              $(document).on('submit', '.prescription-file-upload', function(e){
                e.preventDefault();
                const $form = $(this);
                const prescriptionId = $form.data('prescription-id');
                const fd = new FormData(this);
                
                $.ajax({ 
                  url:'ajax/upload_prescription_file.php', 
                  method:'POST', 
                  data: fd, 
                  contentType:false, 
                  processData:false, 
                  dataType:'json', 
                  success:function(res){
                    if(res && res.status==='success'){ 
                      loadPrescriptionFiles(prescriptionId); 
                      $form[0].reset(); 
                    } else { 
                      alert(res && res.message ? res.message : 'Upload failed'); 
                    }
                  }, 
                  error:function(){ 
                    alert('Upload failed'); 
                  }
                });
              });
              
              // Handle file deletion
              $(document).on('click', '.delete-file-btn', function(){
                const $btn = $(this);
                const fileId = $btn.data('file-id');
                const fileName = $btn.data('file-name');
                const prescriptionId = $btn.data('prescription-id');
                
                // Show confirmation dialog
                if (!confirm('Are you sure you want to delete this file?\n\nFile: ' + fileName + '\n\nThis action cannot be undone.')) {
                  return;
                }
                
                // Disable button and show loading
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
                
                // Send delete request
                $.ajax({
                  url: 'ajax/delete_prescription_file.php',
                  method: 'POST',
                  data: {
                    file_id: fileId,
                    prescription_id: prescriptionId,
                    csrf_token: $('input[name="csrf_token"]').val()
                  },
                  dataType: 'json',
                  success: function(res) {
                    if (res.status === 'success') {
                      // Reload files for this prescription
                      loadPrescriptionFiles(prescriptionId);
                      
                      // Show success message
                      alert('File deleted successfully: ' + fileName);
                    } else {
                      // Show error message
                      alert('Failed to delete file: ' + (res.message || 'Unknown error'));
                    }
                  },
                  error: function() {
                    alert('Error: Failed to communicate with server');
                  },
                  complete: function() {
                    // Re-enable button
                    $btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Delete');
                  }
                });
              });
              
              // Load files for all prescriptions on page load
              $('.prescription-files-table').each(function(){
                const prescriptionId = $(this).data('prescription-id');
                if(prescriptionId) {
                  loadPrescriptionFiles(prescriptionId);
                }
              });
              // Image viewer functionality with better error handling
              $(document).on('click', '.view-image-btn', function(){
                const fileId = $(this).data('file-id');
                const fileName = $(this).data('file-name');
                const imageUrl = 'view_image_safe.php?id=' + fileId;
                const downloadUrl = 'download_patient_file.php?id=' + fileId;
                
                // Create image viewer modal with proper accessibility
                const modalHtml = `
                  <div class="modal fade" id="imageViewerModal" tabindex="-1" role="dialog" aria-labelledby="imageViewerModalLabel">
                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                      <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                          <h5 class="modal-title" id="imageViewerModalLabel">
                            <i class="fas fa-image mr-2"></i>${fileName}
                          </h5>
                          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close Image Viewer">
                            <span>&times;</span>
                          </button>
                        </div>
                        <div class="modal-body text-center" style="background: #f8f9fa;">
                          <div class="image-container" style="max-height: 70vh; overflow: auto;">
                            <div id="imageLoading" class="text-center" style="padding: 20px;">
                              <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                              <p class="mt-2">Loading image...</p>
                            </div>
                            <img id="modalImage" 
                                 src="" 
                                 class="img-fluid rounded shadow" 
                                 style="max-width: 100%; height: auto; display: none;" 
                                 alt="${fileName}">
                            <div id="imageError" class="text-center" style="padding: 20px; display: none;">
                              <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                              <p class="mt-2">Failed to load image</p>
                              <p class="small text-muted">Try downloading the file instead</p>
                            </div>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <a href="${downloadUrl}" class="btn btn-primary" target="_blank" aria-label="Download ${fileName}">
                            <i class="fas fa-download mr-1"></i> Download
                          </a>
                          <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close Image Viewer">Close</button>
                        </div>
                      </div>
                    </div>
                  </div>
                `;
                
                // Remove existing modal if any
                $('#imageViewerModal').remove();
                
                // Add modal to body
                $('body').append(modalHtml);
                
                // Show modal with proper focus management
                $('#imageViewerModal').modal({
                    backdrop: 'static',
                    keyboard: true,
                    focus: true
                });
                
                // Ensure proper focus management
                $('#imageViewerModal').on('shown.bs.modal', function () {
                    // Focus on the close button for accessibility
                    $(this).find('.close').focus();
                    
                    // Load image with timeout and error handling
                    const $img = $('#modalImage');
                    const $loading = $('#imageLoading');
                    const $error = $('#imageError');
                    
                    // Reset states
                    $loading.show();
                    $error.hide();
                    $img.hide();
                    
                    // Test the URL first with a simple fetch
                    fetch(imageUrl)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                            }
                            return response;
                        })
                        .then(() => {
                            // If fetch succeeds, set image source
                            $img.attr('src', imageUrl);
                            
                            // Set up image loading handlers
                            $img.on('load', function() {
                                $loading.hide();
                                $error.hide();
                                $(this).show();
                            });
                            
                            $img.on('error', function() {
                                $loading.hide();
                                $error.show();
                                $(this).hide();
                                console.error('Failed to load image:', imageUrl);
                            });
                        })
                        .catch(error => {
                            console.error('Image URL test failed:', error);
                            $loading.hide();
                            $error.show();
                            $img.hide();
                        });
                    
                    // Timeout after 10 seconds
                    setTimeout(function() {
                        if ($loading.is(':visible')) {
                            $loading.hide();
                            $error.show();
                            $img.hide();
                        }
                    }, 10000);
                });
                
                // Clean up on hide
                $('#imageViewerModal').on('hidden.bs.modal', function () {
                    $(this).remove();
                });
              });
            }
            function start(){
              if (window.jQuery) { run(); return; }
              var iv = setInterval(function(){ if (window.jQuery) { clearInterval(iv); run(); } }, 50);
            }
            if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', start); } else { start(); }
          })();
          </script>
          <?php } ?>
        </div>
        <!-- /.card-body -->
        
      </div>
      <!-- /.card -->

    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<?php include './config/site_js_links.php' ?>
<?php include './config/data_tables_js.php' ?>

<script>
  showMenuSelected("#mnu_patients", "#mi_patient_history");

  $(document).ready(function() {
    function initDataTable(){
      if ($.fn.DataTable.isDataTable('#patient_history')) {
        $('#patient_history').DataTable().destroy();
      }
      $('#patient_history').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "pageLength": 25,
        "order": []
      });
    }
    function loadHistory(){
      $.ajax({
        url: "ajax/get_patient_history.php",
        type: 'GET', 
        cache:false,
        async:false,
        success: function (data, status, xhr) {
            $("#history_data").html(data);
            initDataTable();
        },
        error: function (jqXhr, textStatus, errorMessage) {
          showCustomMessage(errorMessage);
        }
      });
    }

    loadHistory();
  });
</script>

</body>
</html>