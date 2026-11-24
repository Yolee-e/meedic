<?php 
include './config/connection.php';
include './common_service/common_functions.php';

$__ut = isset($_SESSION['user_type']) ? (int)$_SESSION['user_type'] : 0; // 1=Admin, 2=Doctor, 3=Front Desk, 4=Cashier
$isFrontDesk = ($__ut === 3);

$totalPatients = 0;
$totalMedicines = 0;
$appointmentsToday = 0;
$salesToday = 0.00;
$lowStockCount = 0;
$expiringCount = 0;
$expiredCount = 0;

try {
  $stmt = $con->prepare("SELECT COUNT(*) FROM patients");
  $stmt->execute();
  $totalPatients = (int)$stmt->fetchColumn();

  $stmt = $con->prepare("SELECT COUNT(*) FROM medicines");
  $stmt->execute();
  $totalMedicines = (int)$stmt->fetchColumn();

  $stmt = $con->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()");
  $stmt->execute();
  $appointmentsToday = (int)$stmt->fetchColumn();
  // Include doctor-only appointments if table exists
  $hasDocAppts = $con->query("SHOW TABLES LIKE 'doctor_appointments'")->rowCount() > 0;
  if ($hasDocAppts) {
    $stmt = $con->prepare("SELECT COUNT(*) FROM doctor_appointments WHERE appointment_date = CURDATE()");
    $stmt->execute();
    $appointmentsToday += (int)$stmt->fetchColumn();
  }

  // Sales today if sales table exists
  $hasSales = $con->query("SHOW TABLES LIKE 'sales'")->rowCount() > 0;
  if ($hasSales) {
    $stmt = $con->prepare("SELECT IFNULL(SUM(total_amount),0) FROM sales WHERE DATE(sale_date) = CURDATE()");
    $stmt->execute();
    $salesToday = (float)$stmt->fetchColumn();
  }

  // Medicine alerts
  if (!$isFrontDesk) {
    // Low stock medicines (quantity <= 10)
    $stmt = $con->prepare("SELECT COUNT(*) FROM medicine_details WHERE quantity_stock <= 10 AND quantity_stock >= 0");
    $stmt->execute();
    $lowStockCount = (int)$stmt->fetchColumn();

    // Medicines expiring within 30 days
    $stmt = $con->prepare("SELECT COUNT(*) FROM medicine_details WHERE expiry_date IS NOT NULL AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND expiry_date >= CURDATE()");
    $stmt->execute();
    $expiringCount = (int)$stmt->fetchColumn();

    // Expired medicines
    $stmt = $con->prepare("SELECT COUNT(*) FROM medicine_details WHERE expiry_date IS NOT NULL AND expiry_date < CURDATE()");
    $stmt->execute();
    $expiredCount = (int)$stmt->fetchColumn();
  }
} catch(PDOException $ex) {
  if (function_exists('app_log_exception')) { app_log_exception($ex); }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <?php include './config/site_css_links.php';?>
 <title>Dashboard - Medical Care and Information system</title>
 <style>
 /* Enhanced Dashboard Styling */
 
 /* Medicine Alert Cards Animations */
 .medicine-alert-card {
   transition: all 0.3s ease;
   cursor: pointer;
 }
 
 .medicine-alert-card:hover {
   transform: translateY(-5px) scale(1.02);
   box-shadow: 0 8px 25px rgba(0,0,0,0.2) !important;
 }
 
 .medicine-alert-card .inner h3 {
   transition: all 0.3s ease;
 }
 
 .medicine-alert-card:hover .inner h3 {
   transform: scale(1.1);
 }
 
 .medicine-alert-card .icon {
   transition: all 0.3s ease;
 }
 
 .medicine-alert-card:hover .icon {
   transform: scale(1.2) rotate(5deg);
   opacity: 0.5 !important;
 }
 
 /* Pulse animation for urgent alerts */
 .medicine-alert-card.bg-gradient-danger:has(.inner h3:not(:empty)) {
   animation: pulse-urgent 2s infinite;
 }
 
 @keyframes pulse-urgent {
   0% { box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3); }
   50% { box-shadow: 0 4px 25px rgba(220, 53, 69, 0.6); }
   100% { box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3); }
 }
 
 /* Glow effect for warning alerts */
 .medicine-alert-card.bg-gradient-warning:has(.inner h3:not(:empty)) {
   animation: glow-warning 3s infinite;
 }
 
 @keyframes glow-warning {
   0% { box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3); }
   50% { box-shadow: 0 4px 20px rgba(255, 193, 7, 0.5); }
   100% { box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3); }
 }
 .small-box .inner h3 { 
   font-size: 2.6rem; 
   font-weight: 700;
   text-shadow: 0 2px 4px rgba(0,0,0,0.1);
   transition: all 0.3s ease;
 }
 .small-box .inner p { 
   font-size: 1.05rem; 
   font-weight: 500;
   margin-bottom: 0;
   transition: all 0.3s ease;
 }
 
 /* Enhanced Small Box Styling */
 .small-box {
   border-radius: 12px;
   box-shadow: 0 4px 15px rgba(0,0,0,0.1);
   transition: all 0.3s ease;
   overflow: hidden;
   position: relative;
 }
 
 .small-box:hover {
   transform: translateY(-5px);
   box-shadow: 0 8px 25px rgba(0,0,0,0.15);
 }
 
 .small-box .inner {
   padding: 1.5rem;
   position: relative;
   z-index: 2;
 }
 
 .small-box .icon {
   position: absolute;
   top: 1rem;
   right: 1rem;
   font-size: 3rem;
   opacity: 0.3;
   transition: all 0.3s ease;
   z-index: 1;
 }
 
 .small-box:hover .icon {
   opacity: 0.5;
   transform: scale(1.1);
 }
 
 .small-box-footer {
   background: rgba(0,0,0,0.1);
   padding: 0.75rem;
   display: flex;
   align-items: center;
   justify-content: space-between;
   font-weight: 500;
   transition: all 0.3s ease;
 }
 
 .small-box-footer:hover {
   background: rgba(0,0,0,0.2);
   text-decoration: none;
 }
 
 /* Enhanced Card Styling */
 .card {
   border-radius: 12px;
   box-shadow: 0 4px 15px rgba(0,0,0,0.08);
   transition: all 0.3s ease;
   border: none;
   overflow: hidden;
 }
 
 .card:hover {
   box-shadow: 0 8px 25px rgba(0,0,0,0.12);
   transform: translateY(-2px);
 }
 
 .card-header {
   background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
   color: white;
   border-bottom: none;
   padding: 1.25rem 1.5rem;
   position: relative;
 }
 
 .card-header::before {
   content: '';
   position: absolute;
   top: 0;
   left: 0;
   right: 0;
   bottom: 0;
   background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
   pointer-events: none;
 }
 
 .card-header h3 {
   margin: 0;
   font-weight: 600;
   font-size: 1.1rem;
   position: relative;
   z-index: 1;
 }
 
 .card-body {
   padding: 1.5rem;
   background: #fff;
 }
 
 /* Enhanced List Group Styling */
 .list-group-item {
   border: none;
   border-bottom: 1px solid #f0f0f0;
   padding: 1rem 1.25rem;
   transition: all 0.3s ease;
   background: #fff;
 }
 
 .list-group-item:hover {
   background: #f8f9fa;
   transform: translateX(5px);
   box-shadow: 0 2px 8px rgba(0,0,0,0.1);
 }
 
 .list-group-item:last-child {
   border-bottom: none;
 }
 
 .badge {
   font-size: 0.85rem;
   padding: 0.5rem 0.75rem;
   border-radius: 20px;
   font-weight: 600;
   box-shadow: 0 2px 4px rgba(0,0,0,0.1);
 }
 
 /* Enhanced Chart Containers */
 canvas {
   border-radius: 8px;
 }
 
 /* Gradient Backgrounds for Cards */
 .card-outline.card-primary .card-header {
   background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
 }
 
 .card-outline.card-success .card-header {
   background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
 }
 
 .card-outline.card-warning .card-header {
   background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
 }
 
 .card-outline.card-danger .card-header {
   background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
 }
 
 .card-outline.card-info .card-header {
   background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
 }
 
 .card-outline.card-teal .card-header {
   background: linear-gradient(135deg, #20c997 0%, #1a9b7a 100%);
 }
 
 .card-outline.card-secondary .card-header {
   background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
 }
 
 .card-outline.card-purple .card-header {
   background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
 }
 
 .card-outline.card-orange .card-header {
   background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%);
 }
 
 /* Enhanced Statistics Cards */
 .small-box.bg-primary {
   background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
 }
 
 .small-box.bg-success {
   background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
 }
 
 .small-box.bg-info {
   background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
 }
 
 .small-box.bg-warning {
   background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
 }
 
 /* Loading Animation */
 .card-body {
   position: relative;
 }
 
 .card-body::before {
   content: '';
   position: absolute;
   top: 0;
   left: -100%;
   width: 100%;
   height: 100%;
   background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
   animation: shimmer 2s infinite;
 }
 
 @keyframes shimmer {
   0% { left: -100%; }
   100% { left: 100%; }
 }
 
 /* Responsive Improvements */
 @media (max-width: 768px) {
   .small-box .inner h3 {
     font-size: 2rem;
   }
   
   .card-header {
     padding: 1rem;
   }
   
   .card-body {
     padding: 1rem;
   }
   
   .list-group-item {
     padding: 0.75rem 1rem;
   }
 }
 
 /* Enhanced Welcome Card */
 .card-outline.card-primary:last-child {
   background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
   border: 1px solid #dee2e6;
 }
 
 .card-outline.card-primary:last-child .card-header {
   background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
 }
 
 /* Smooth Transitions */
 * {
   transition: all 0.3s ease;
 }
 
 /* Enhanced Focus States */
 .small-box:focus-within {
   outline: 2px solid #007bff;
   outline-offset: 2px;
 }
 
 /* Improved Typography */
 .card-title {
   font-family: 'Source Sans Pro', sans-serif;
   font-weight: 600;
 }
 
 .small-box .inner h3 {
   font-family: 'Source Sans Pro', sans-serif;
 }
 
 .small-box .inner p {
   font-family: 'Source Sans Pro', sans-serif;
 }
 
 /* Pulse Animation */
 .pulse {
   animation: pulse 0.2s ease-in-out;
 }
 
 @keyframes pulse {
   0% { transform: scale(1); }
   50% { transform: scale(1.05); }
   100% { transform: scale(1); }
 }
 
 /* Enhanced List Items */
 .list-group-item {
   position: relative;
   overflow: hidden;
 }
 
 .list-group-item::before {
   content: '';
   position: absolute;
   top: 0;
   left: -100%;
   width: 100%;
   height: 100%;
   background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
   transition: left 0.5s;
 }
 
 .list-group-item:hover::before {
   left: 100%;
 }
 
 /* Enhanced Badge Styling */
 .badge-primary {
   background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
 }
 
 .badge-danger {
   background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
 }
 
 .badge-info {
   background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
 }
 
 /* Chart Container Enhancements */
 .card-body canvas {
   max-height: 300px;
   transition: all 0.3s ease;
 }
 
 .card:hover canvas {
   transform: scale(1.02);
 }
 
 /* Loading States */
 .card-body.loading {
   position: relative;
 }
 
 .card-body.loading::after {
   content: '';
   position: absolute;
   top: 50%;
   left: 50%;
   width: 40px;
   height: 40px;
   margin: -20px 0 0 -20px;
   border: 4px solid #f3f3f3;
   border-top: 4px solid #007bff;
   border-radius: 50%;
   animation: spin 1s linear infinite;
 }
 
 @keyframes spin {
   0% { transform: rotate(0deg); }
   100% { transform: rotate(360deg); }
 }
 
 /* Enhanced Peak Times Card */
 .card-outline.card-secondary .card-body p {
   font-size: 1.1rem;
   margin-bottom: 1rem;
   padding: 0.75rem;
   background: rgba(108, 117, 125, 0.1);
   border-radius: 8px;
   border-left: 4px solid #6c757d;
 }
 
 .card-outline.card-secondary .card-body span {
   font-weight: 600;
   color: #495057;
 }
 
 /* Responsive Chart Improvements */
 @media (max-width: 768px) {
   .card-body canvas {
     max-height: 250px;
   }
   
   .small-box .icon {
     font-size: 2.5rem;
   }
 }
 
 /* Enhanced Welcome Card */
 .card-outline.card-primary:last-child .card-body {
   background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
   border-radius: 0 0 12px 12px;
 }
 
 .card-outline.card-primary:last-child .card-body p {
   font-size: 1.1rem;
   color: #495057;
   line-height: 1.6;
 }
 </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
  <div class="wrapper">
    <?php include './config/header.php';
include './config/sidebar.php';?>  

    <div class="content-wrapper">
     <h1>Dashboard</h1>

      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-3 col-6">
              <div class="small-box bg-primary">
                <div class="inner">
                  <h3><?php echo $totalPatients; ?></h3>
                  <p>Patients</p>
                </div>
                <div class="icon"><i class="fas fa-user-injured"></i></div>
                <a href="patients.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
              </div>
            </div>

            <?php if(!$isFrontDesk) { ?>
            <div class="col-lg-3 col-6">
              <div class="small-box bg-success">
                <div class="inner">
                  <h3><?php echo $totalMedicines; ?></h3>
                  <p>Medicines</p>
                </div>
                <div class="icon"><i class="fas fa-pills"></i></div>
                <a href="medicines.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
              </div>
            </div>
            <?php } ?>

            <div class="col-lg-3 col-6">
              <div class="small-box bg-info">
                <div class="inner">
                  <h3><?php echo $appointmentsToday; ?></h3>
                  <p>Patients Today</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-check"></i></div>
                <a href="appointments.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
              </div>
            </div>

            <?php if(!$isFrontDesk) { ?>
            <div class="col-lg-3 col-6">
              <div class="small-box bg-warning">
                <div class="inner">
                  <h3><?php echo number_format($salesToday, 2); ?></h3>
                  <p>Sales Today</p>
                </div>
                <div class="icon"><i class="fas fa-cash-register"></i></div>
                <a href="reports.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
              </div>
            </div>
            <?php } ?>
          </div>

          <?php if(!$isFrontDesk) { ?>
          <!-- Enhanced Medicine Alerts Row -->
          <div class="row">
            <div class="col-lg-4 col-6">
              <div class="small-box bg-gradient-danger medicine-alert-card" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3); border-radius: 10px; position: relative; overflow: hidden;">
                <div class="inner" style="position: relative; z-index: 2;">
                  <h3 style="font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.3);"><?php echo $lowStockCount; ?></h3>
                  <p style="font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">Low Stock Medicines</p>
                  <?php if($lowStockCount > 0): ?>
                  <small style="opacity: 0.9; font-weight: 500;">⚠️ Immediate attention required</small>
                  <?php else: ?>
                  <small style="opacity: 0.9; font-weight: 500;">✅ All medicines in stock</small>
                  <?php endif; ?>
                </div>
                <div class="icon" style="font-size: 3rem; opacity: 0.3; position: absolute; top: 15px; right: 15px; z-index: 1;">
                  <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="small-box-footer" style="background: rgba(0,0,0,0.1); border-top: 1px solid rgba(255,255,255,0.2);">
                  <a href="medicines.php" style="color: white; text-decoration: none; font-weight: 600;">
                    <i class="fas fa-pills mr-1"></i>View Details <i class="fas fa-arrow-circle-right ml-1"></i>
                  </a>
                </div>
                <?php if($lowStockCount > 0): ?>
                <div style="position: absolute; top: 0; right: 0; background: rgba(255,255,255,0.2); padding: 5px 10px; border-radius: 0 10px 0 10px; font-size: 0.8rem; font-weight: bold;">
                  URGENT
                </div>
                <?php endif; ?>
              </div>
            </div>

            <div class="col-lg-4 col-6">
              <div class="small-box bg-gradient-warning medicine-alert-card" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3); border-radius: 10px; position: relative; overflow: hidden;">
                <div class="inner" style="position: relative; z-index: 2;">
                  <h3 style="font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.3); color: #212529;"><?php echo $expiringCount; ?></h3>
                  <p style="font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,0.3); color: #212529;">Expiring Soon</p>
                  <small style="opacity: 0.8; font-weight: 500; color: #212529;">⏰ Within 30 days</small>
                </div>
                <div class="icon" style="font-size: 3rem; opacity: 0.3; position: absolute; top: 15px; right: 15px; z-index: 1; color: #212529;">
                  <i class="fas fa-clock"></i>
                </div>
                <div class="small-box-footer" style="background: rgba(0,0,0,0.1); border-top: 1px solid rgba(0,0,0,0.1);">
                  <a href="medicines.php" style="color: #212529; text-decoration: none; font-weight: 600;">
                    <i class="fas fa-calendar-alt mr-1"></i>View Details <i class="fas fa-arrow-circle-right ml-1"></i>
                  </a>
                </div>
                <?php if($expiringCount > 0): ?>
                <div style="position: absolute; top: 0; right: 0; background: rgba(0,0,0,0.1); padding: 5px 10px; border-radius: 0 10px 0 10px; font-size: 0.8rem; font-weight: bold; color: #212529;">
                  WARNING
                </div>
                <?php endif; ?>
              </div>
            </div>

            <div class="col-lg-4 col-6">
              <div class="small-box bg-gradient-dark medicine-alert-card" style="background: linear-gradient(135deg, #343a40 0%, #23272b 100%); box-shadow: 0 4px 15px rgba(52, 58, 64, 0.3); border-radius: 10px; position: relative; overflow: hidden;">
                <div class="inner" style="position: relative; z-index: 2;">
                  <h3 style="font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.3);"><?php echo $expiredCount; ?></h3>
                  <p style="font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">Expired Medicines</p>
                  <small style="opacity: 0.9; font-weight: 500;">🚫 Remove from inventory</small>
                </div>
                <div class="icon" style="font-size: 3rem; opacity: 0.3; position: absolute; top: 15px; right: 15px; z-index: 1;">
                  <i class="fas fa-ban"></i>
                </div>
                <div class="small-box-footer" style="background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.1);">
                  <a href="medicines.php" style="color: white; text-decoration: none; font-weight: 600;">
                    <i class="fas fa-trash-alt mr-1"></i>View Details <i class="fas fa-arrow-circle-right ml-1"></i>
                  </a>
                </div>
                <?php if($expiredCount > 0): ?>
                <div style="position: absolute; top: 0; right: 0; background: rgba(255,255,255,0.1); padding: 5px 10px; border-radius: 0 10px 0 10px; font-size: 0.8rem; font-weight: bold;">
                  EXPIRED
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php } ?>

          <div class="row">
            <div class="col-lg-8">
              <div class="card card-outline card-primary shadow">
                <div class="card-header"><h3 class="card-title mb-0">Patient(Last 7 Days)</h3></div>
                <div class="card-body">
                  <canvas id="chartAppointments" height="180"></canvas>
                </div>
              </div>
            </div>
            <?php if(!$isFrontDesk) { ?>
            <div class="col-lg-4">
              <div class="card card-outline card-warning shadow">
                <div class="card-header"><h3 class="card-title mb-0">Sales (Last 7 Days)</h3></div>
                <div class="card-body">
                  <canvas id="chartSales" height="180"></canvas>
                </div>
              </div>
            </div>
            <?php } ?>
          </div>
          <div class="row">
            <?php if(!$isFrontDesk) { ?>
            <div class="col-lg-6">
              <div class="card card-outline card-success shadow">
                <div class="card-header"><h3 class="card-title mb-0">Top 5 Medicines (Qty Sold)</h3></div>
                <div class="card-body">
                  <ul class="list-group" id="listTopMedicines"></ul>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="card card-outline card-danger shadow">
                <div class="card-header"><h3 class="card-title mb-0">Low Stock Items</h3></div>
                <div class="card-body">
                  <ul class="list-group" id="listLowStock"></ul>
                </div>
              </div>
            </div>
            <?php } ?>
          </div>

          <div class="row">
            <div class="col-lg-6">
              <div class="card card-outline card-teal shadow">
                <div class="card-header"><h3 class="card-title mb-0">Top Services (Consults) - Last 30 Days</h3></div>
                <div class="card-body">
                  <ul class="list-group" id="listTopServices"></ul>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="card card-outline card-orange shadow">
                <div class="card-header"><h3 class="card-title mb-0">Medicines Expiring Soon (Next 30 Days)</h3></div>
                <div class="card-body">
                  <ul class="list-group" id="listExpiringMedicines"></ul>
                </div>
              </div>
            </div>
          </div>

          <?php if(!$isFrontDesk) { ?>
          <div class="row">
            <div class="col-lg-6">
              <div class="card card-outline card-info shadow">
                <div class="card-header"><h3 class="card-title mb-0">Sales by Hour (Last 30 Days)</h3></div>
                <div class="card-body">
                  <canvas id="chartSalesHour" height="180"></canvas>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="card card-outline card-purple shadow">
                <div class="card-header"><h3 class="card-title mb-0">Sales by Weekday (Last 30 Days)</h3></div>
                <div class="card-body">
                  <canvas id="chartSalesDow" height="180"></canvas>
                </div>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-lg-4">
              <div class="card card-outline card-secondary shadow">
                <div class="card-header"><h3 class="card-title mb-0">Peak Times</h3></div>
                <div class="card-body">
                  <p class="mb-2"><strong>Peak Hour:</strong> <span id="txtPeakHour">-</span></p>
                  <p class="mb-0"><strong>Peak Day:</strong> <span id="txtPeakDay">-</span></p>
                </div>
              </div>
            </div>
          </div>
          <?php } ?>

          <div class="row">
            <div class="col-12">
              <div class="card card-outline card-primary shadow">
                <div class="card-header">
                  <h3 class="card-title">Welcome</h3>
                </div>
                <div class="card-body">
                  <p class="mb-0">Use the sidebar to navigate Patients, Medicines, POS, Patient Queue, Lab & Reports.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

  </div>
  <?php include './config/site_js_links.php'; ?>
  <script src="plugins/chart.js/Chart.min.js"></script>
  <script>
    showMenuSelected("#mnu_dashboard", "");
    
    // Enhanced Dashboard Animations and Interactions
    $(document).ready(function() {
      // Animate statistics cards on load
      $('.small-box').each(function(index) {
        $(this).css({
          'opacity': '0',
          'transform': 'translateY(30px)'
        }).delay(index * 100).animate({
          'opacity': '1'
        }, 600, function() {
          $(this).css('transform', 'translateY(0)');
        });
      });
      
      // Animate cards on load
      $('.card').each(function(index) {
        $(this).css({
          'opacity': '0',
          'transform': 'translateY(20px)'
        }).delay((index + 4) * 100).animate({
          'opacity': '1'
        }, 600, function() {
          $(this).css('transform', 'translateY(0)');
        });
      });
      
      // Add hover effects to list items
      $('.list-group-item').hover(
        function() {
          $(this).addClass('shadow-sm');
        },
        function() {
          $(this).removeClass('shadow-sm');
        }
      );
      
      // Add click effects to small boxes
      $('.small-box').on('click', function() {
        $(this).addClass('pulse');
        setTimeout(() => {
          $(this).removeClass('pulse');
        }, 200);
      });
    });
    
    (function(){
      $.getJSON('ajax/dashboard_metrics.php', function(res){
        if (res && res.status === 'success') {
          const labels = res.labels || [];
          const appts = res.appointments || [];
          const sales = res.sales || [];
          const salesByHour = res.sales_by_hour || new Array(24).fill(0);
          const salesByWeekday = res.sales_by_weekday || new Array(7).fill(0);
          const peakHour = (typeof res.peak_hour === 'number') ? res.peak_hour : null;
          const peakDayName = res.peak_day_name || '';
          const topServices = res.top_services || [];
          const expiringMedicines = res.expiring_medicines || [];

          const ctxA = document.getElementById('chartAppointments').getContext('2d');
          new Chart(ctxA, {
            type: 'line',
            data: { 
              labels: labels, 
              datasets: [{ 
                label: 'Patient Queue', 
                data: appts, 
                borderColor: '#007bff', 
                backgroundColor: 'rgba(0,123,255,0.1)', 
                fill: true, 
                tension: 0.4,
                borderWidth: 3,
                pointBackgroundColor: '#007bff',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
              }] 
            },
            options: { 
              plugins: { 
                legend: { display: false } 
              }, 
              scales: { 
                y: { 
                  beginAtZero: true,
                  grid: {
                    color: 'rgba(0,0,0,0.1)',
                    drawBorder: false
                  },
                  ticks: {
                    color: '#666'
                  }
                },
                x: {
                  grid: {
                    display: false
                  },
                  ticks: {
                    color: '#666'
                  }
                }
              },
              animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
              },
              interaction: {
                intersect: false,
                mode: 'index'
              }
            }
          });

          const ctxS = document.getElementById('chartSales').getContext('2d');
          new Chart(ctxS, {
            type: 'bar',
            data: { 
              labels: labels, 
              datasets: [{ 
                label: 'Sales', 
                data: sales, 
                backgroundColor: 'rgba(255, 193, 7, 0.8)',
                borderColor: '#ffc107',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false
              }] 
            },
            options: { 
              plugins: { 
                legend: { display: false } 
              }, 
              scales: { 
                y: { 
                  beginAtZero: true,
                  grid: {
                    color: 'rgba(0,0,0,0.1)',
                    drawBorder: false
                  },
                  ticks: {
                    color: '#666'
                  }
                },
                x: {
                  grid: {
                    display: false
                  },
                  ticks: {
                    color: '#666'
                  }
                }
              },
              animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
              },
              interaction: {
                intersect: false,
                mode: 'index'
              }
            }
          });

          // Sales by Hour chart
          const hourLabels = Array.from({length:24}, (_,i)=> (i<10?('0'+i):i)+':00');
          const ctxH = document.getElementById('chartSalesHour').getContext('2d');
          new Chart(ctxH, {
            type: 'bar',
            data: { 
              labels: hourLabels, 
              datasets: [{ 
                label: 'Sales', 
                data: salesByHour, 
                backgroundColor: 'rgba(23, 162, 184, 0.8)',
                borderColor: '#17a2b8',
                borderWidth: 2,
                borderRadius: 4,
                borderSkipped: false
              }] 
            },
            options: { 
              plugins: { 
                legend: { display: false } 
              }, 
              scales: { 
                y: { 
                  beginAtZero: true,
                  grid: {
                    color: 'rgba(0,0,0,0.1)',
                    drawBorder: false
                  },
                  ticks: {
                    color: '#666'
                  }
                },
                x: {
                  grid: {
                    display: false
                  },
                  ticks: {
                    color: '#666',
                    maxTicksLimit: 12
                  }
                }
              },
              animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
              },
              interaction: {
                intersect: false,
                mode: 'index'
              }
            }
          });

          // Sales by Weekday chart
          const dowLabels = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
          const ctxD = document.getElementById('chartSalesDow').getContext('2d');
          new Chart(ctxD, {
            type: 'bar',
            data: { 
              labels: dowLabels, 
              datasets: [{ 
                label: 'Sales', 
                data: salesByWeekday, 
                backgroundColor: 'rgba(111, 66, 193, 0.8)',
                borderColor: '#6f42c1',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false
              }] 
            },
            options: { 
              plugins: { 
                legend: { display: false } 
              }, 
              scales: { 
                y: { 
                  beginAtZero: true,
                  grid: {
                    color: 'rgba(0,0,0,0.1)',
                    drawBorder: false
                  },
                  ticks: {
                    color: '#666'
                  }
                },
                x: {
                  grid: {
                    display: false
                  },
                  ticks: {
                    color: '#666'
                  }
                }
              },
              animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
              },
              interaction: {
                intersect: false,
                mode: 'index'
              }
            }
          });

          // Peak labels
          if (peakHour !== null) {
            const ph = (peakHour < 10 ? ('0'+peakHour) : peakHour) + ':00';
            $('#txtPeakHour').text(ph);
          }
          if (peakDayName) {
            $('#txtPeakDay').text(peakDayName);
          }

          // Lists
          const top = res.top_medicines || [];
          const low = res.low_stock || [];
          const $top = $('#listTopMedicines');
          const $low = $('#listLowStock');
          const $srv = $('#listTopServices');
          const $exp = $('#listExpiringMedicines');
          $top.empty();
          $low.empty();
          $srv.empty();
          $exp.empty();
          if (top.length === 0) { $top.append('<li class="list-group-item">No data</li>'); }
          top.forEach(r => {
            $top.append('<li class="list-group-item d-flex justify-content-between align-items-center">'
              + $('<div>').text(r.medicine_name).html() +
              '<span class="badge badge-primary badge-pill">' + (r.qty || 0) + '</span></li>');
          });
          if (low.length === 0) { $low.append('<li class="list-group-item">No low stock items</li>'); }
          low.forEach(r => {
            const name = (r.medicine_name || '') + ' (' + (r.packing || '') + ')';
            $low.append('<li class="list-group-item d-flex justify-content-between align-items-center">'
              + $('<div>').text(name).html() +
              '<span class="badge badge-danger badge-pill">' + (r.stock || 0) + '</span></li>');
          });
          if (topServices.length === 0) { $srv.append('<li class="list-group-item">No services</li>'); }
          topServices.forEach(s => {
            const label = s.reason_label || '(unspecified)';
            $srv.append('<li class="list-group-item d-flex justify-content-between align-items-center">'
              + $('<div>').text(label).html() +
              '<span class="badge badge-info badge-pill">' + (s.cnt || 0) + '</span></li>');
          });
          
          // Populate expiring medicines
          if (expiringMedicines.length === 0) { 
            $exp.append('<li class="list-group-item">No medicines expiring in the next 30 days</li>'); 
          }
          expiringMedicines.forEach(med => {
            const medicineName = med.medicine_name || 'Unknown Medicine';
            const packing = med.packing || '';
            const expiryDate = med.expiry_date || '';
            const stock = med.quantity_stock || 0;
            
            // Calculate days until expiry
            const today = new Date();
            const expiry = new Date(expiryDate);
            const daysUntilExpiry = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));
            
            // Determine badge color based on urgency
            let badgeClass = 'badge-warning';
            if (daysUntilExpiry <= 7) {
              badgeClass = 'badge-danger';
            } else if (daysUntilExpiry <= 14) {
              badgeClass = 'badge-warning';
            } else {
              badgeClass = 'badge-info';
            }
            
            const displayName = packing ? `${medicineName} (${packing})` : medicineName;
            const expiryText = daysUntilExpiry <= 0 ? 'Expired' : `${daysUntilExpiry} days`;
            
            $exp.append('<li class="list-group-item d-flex justify-content-between align-items-center">'
              + '<div><div class="font-weight-bold">' + $('<div>').text(displayName).html() + '</div>'
              + '<small class="text-muted">Stock: ' + stock + ' | Expires: ' + expiryDate + '</small></div>'
              + '<span class="badge ' + badgeClass + ' badge-pill">' + expiryText + '</span></li>');
          });
        }
      });
    })();
  </script>
</body>
</html>