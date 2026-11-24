<?php
include './config/connection.php';
include './common_service/common_functions.php';

$message = '';



try {

  $query = "SELECT `id`, `patient_name`, `address`, 
date_format(`date_of_birth`, '%d %b %Y') as `date_of_birth`, 
  `phone_number`, `gender` 
  FROM `patients` order by `patient_name` asc;";

  $stmtPatient1 = $con->prepare($query);
  $stmtPatient1->execute();

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
  <title>Patients - Medical Care and Information system</title>
  <style>
        /* Enhanced Patients Page Styling */
        .patients-container {
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
            margin-bottom: 0;
        }
        
        .table thead th {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border: none;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            padding: 15px 10px;
            text-align: center;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
            font-size: 0.9em;
        }
        
        .table tbody td:first-child {
            font-weight: 600;
            color: #007bff;
        }
        
        .table tbody td:nth-child(2) {
            font-weight: 600;
            color: #495057;
        }
        
        /* Enhanced Buttons */
        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 8px 16px;
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
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8em;
        }
        
        /* Enhanced Action Buttons */
        .btn-edit {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .btn-edit:hover {
            background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
            color: white;
        }
        
        /* Enhanced Add Patient Button */
        .btn-add-patient {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 12px 24px;
            font-size: 1em;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(0,123,255,0.3);
        }
        
        .btn-add-patient:hover {
            background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,123,255,0.4);
        }
        
        /* Enhanced DataTable Styling */
        .dataTables_wrapper {
            padding: 0;
        }
        
        .dataTables_filter {
            margin-bottom: 20px;
        }
        
        .dataTables_filter input {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 8px 12px;
            transition: all 0.3s ease;
        }
        
        .dataTables_filter input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }
        
        .dataTables_length select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 6px 10px;
            transition: all 0.3s ease;
        }
        
        .dataTables_length select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }
        
        .dataTables_info {
            color: #6c757d;
            font-weight: 500;
        }
        
        .dataTables_paginate .paginate_button {
            border-radius: 6px;
            margin: 0 2px;
            transition: all 0.3s ease;
        }
        
        .dataTables_paginate .paginate_button:hover {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            transform: translateY(-1px);
        }
        
        .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border: none;
        }
        
        /* Enhanced Gender Badges */
        .gender-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .gender-male {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
        }
        
        .gender-female {
            background: linear-gradient(135deg, #fce4ec 0%, #f8bbd9 100%);
            color: #c2185b;
        }
        
        /* Enhanced Phone Number Styling */
        .phone-number {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #007bff;
        }
        
        /* Enhanced Address Styling */
        .patient-address {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Enhanced Patient Name Styling */
        .patient-name {
            font-weight: 700;
            color: #495057;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .card-body {
                padding: 15px;
            }
            
            .table-responsive {
                border-radius: 8px;
            }
            
            .btn-add-patient {
                width: 100%;
                margin-bottom: 10px;
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
        
        /* Enhanced Table Responsive */
        .table-responsive {
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        /* Enhanced Action Column */
        .action-column {
            min-width: 80px;
        }
        
        /* Enhanced S.No Column */
        .sno-column {
            min-width: 60px;
            text-align: center;
        }
        
        /* Enhanced Patient Name Column */
        .patient-name-column {
            min-width: 150px;
        }
        
        /* Enhanced Address Column */
        .address-column {
            min-width: 200px;
        }
        
        /* Enhanced Phone Column */
        .phone-column {
            min-width: 120px;
        }
        
        /* Enhanced Gender Column */
        .gender-column {
            min-width: 80px;
            text-align: center;
        }
        
        /* Enhanced DOB Column */
        .dob-column {
            min-width: 100px;
            text-align: center;
        }
        
        /* Enhanced CNIC Column */
        .cnic-column {
            min-width: 120px;
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
   <h1>patients</h1>

    <!-- Main content -->
    <section class="content">
      <!-- Action buttons -->
      <div class="row mb-3">
        <div class="col-12">
          <a href="add_patient.php" class="btn btn-primary btn-add-patient">
            <i class="fas fa-plus"></i> Add New Patient
          </a>
        </div>
      </div>



 <section class="content">
      <!-- Default box -->
      <div class="card card-outline card-primary shadow">
        <div class="card-header">
          <h3 class="card-title">Total Patients</h3>

          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
            
          </div>
        </div>
        <div class="card-body">
            <div class="row table-responsive">
              <table id="all_patients" 
              class="table table-striped dataTable table-bordered dtr-inline" 
               role="grid" aria-describedby="all_patients_info">
              
                <thead>
                  <tr>
                    <th class="p-1 text-center sno-column">S.No</th>
                    <th class="p-1 text-center patient-name-column">Patient Name</th>
                    <th class="p-1 text-center address-column">Address</th>
                    <th class="p-1 text-center dob-column">Date Of Birth</th>
                    <th class="p-1 text-center phone-column">Phone Number</th>
                    <th class="p-1 text-center gender-column">Gender</th>
                    <th class="p-1 text-center action-column">Action</th>
                  </tr>
                </thead>

                <tbody>
                  <?php 
                  $count = 0;
                  while($row =$stmtPatient1->fetch(PDO::FETCH_ASSOC)){
                    $count++;
                  ?>
                  <tr>
                    <td class="text-center"><?php echo $count; ?></td>
                    <td class="patient-name"><?php echo htmlspecialchars($row['patient_name']);?></td>
                    <td class="patient-address" title="<?php echo htmlspecialchars($row['address']);?>"><?php echo htmlspecialchars($row['address']);?></td>
                    <td class="text-center"><?php echo htmlspecialchars($row['date_of_birth']);?></td>
                    <td class="phone-number"><?php echo htmlspecialchars($row['phone_number']);?></td>
                    <td class="text-center">
                      <span class="gender-badge <?php echo strtolower($row['gender']) == 'male' ? 'gender-male' : 'gender-female'; ?>">
                        <?php echo htmlspecialchars($row['gender']);?>
                      </span>
                    </td>
                    <td class="text-center">
                      <a href="update_patient.php?id=<?php echo $row['id'];?>" class="btn btn-edit btn-sm" title="Edit Patient">
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


<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>

<script>
  showMenuSelected("#mnu_patients", "#mi_patients");

  var message = '<?php echo $message;?>';

  if(message !== '') {
    showCustomMessage(message);
  }
    
   $(function () {
    // Check if DataTable is already initialized to prevent reinitialization
    if (!$.fn.DataTable.isDataTable('#all_patients')) {
      $("#all_patients").DataTable({
        "responsive": true, "lengthChange": false, "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
      }).buttons().container().appendTo('#all_patients_wrapper .col-md-6:eq(0)');
    }
  });
</script>
</body>
</html>