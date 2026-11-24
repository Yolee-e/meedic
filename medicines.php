<?php 
session_start();
include './config/connection.php';
include './config/error_handling.php';
include './common_service/common_functions.php';

$message = '';

// Handle medicine addition
if(isset($_POST['save_medicine'])) {
  $message = '';
  $genericName = trim($_POST['generic_name']);
  $brandName = trim($_POST['brand_name']);
  $dosageForm = trim($_POST['dosage_form']);
  $dosageStrength = trim($_POST['dosage_strength']);
  $packing = trim($_POST['packing']);
  $expiryDate = trim($_POST['expiry_date']);
  $quantityStock = trim($_POST['quantity_stock']);
  $price = isset($_POST['price']) ? trim($_POST['price']) : '';
  $description = trim($_POST['description']);

  // Input validation
  if(empty($genericName) || empty($brandName) || empty($packing) || empty($expiryDate) || $quantityStock === '' || $price === '') {
    $message = 'Please fill all required fields.';
  } elseif (!is_numeric($price) || floatval($price) <= 0) {
    $message = 'Please enter a valid price.';
  } elseif (!is_numeric($quantityStock) || intval($quantityStock) < 0) {
    $message = 'Please enter a valid quantity.';
  } else {
    // CSRF check
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
      $message = 'Invalid security token. Please try again.';
    } else {
      try {
        $con->beginTransaction();

        // Check what columns actually exist in the medicines table
        $checkStmt = $con->prepare("DESCRIBE medicines");
        $checkStmt->execute();
        $availableColumns = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_column($availableColumns, 'Field');

        // Build medicine name from generic and brand names
        $medicineName = $brandName;
        if (!empty($genericName) && $genericName !== $brandName) {
          $medicineName .= ' (' . $genericName . ')';
        }

        // Check for duplicates using available columns
        if (in_array('generic_name', $columnNames) && in_array('brand_name', $columnNames)) {
          $checkQuery = "SELECT COUNT(*) FROM `medicines` WHERE `generic_name` = :generic AND `brand_name` = :brand";
          $checkStmt = $con->prepare($checkQuery);
          $checkStmt->bindParam(':generic', $genericName);
          $checkStmt->bindParam(':brand', $brandName);
        } else {
          $checkQuery = "SELECT COUNT(*) FROM `medicines` WHERE `medicine_name` = :mname";
          $checkStmt = $con->prepare($checkQuery);
          $checkStmt->bindParam(':mname', $medicineName);
        }
        $checkStmt->execute();
        $exists = $checkStmt->fetchColumn();
        
        if($exists > 0) {
          $message = 'This medicine already exists in the system.';
        } else {
          // Build INSERT query based on available columns
          $insertFields = [];
          $insertValues = [];
          $insertParams = [];

          // Always include the basic medicine_name
          $insertFields[] = 'medicine_name';
          $insertValues[] = ':mname';
          $insertParams[':mname'] = $medicineName;

          // Add optional fields if they exist in the database
          if (in_array('generic_name', $columnNames)) {
            $insertFields[] = 'generic_name';
            $insertValues[] = ':generic';
            $insertParams[':generic'] = $genericName;
          }

          if (in_array('brand_name', $columnNames)) {
            $insertFields[] = 'brand_name';
            $insertValues[] = ':brand';
            $insertParams[':brand'] = $brandName;
          }

          if (in_array('dosage_form', $columnNames)) {
            $insertFields[] = 'dosage_form';
            $insertValues[] = ':form';
            $insertParams[':form'] = $dosageForm;
          }

          if (in_array('dosage_strength', $columnNames)) {
            $insertFields[] = 'dosage_strength';
            $insertValues[] = ':strength';
            $insertParams[':strength'] = $dosageStrength;
          }

          if (in_array('stock_quantity', $columnNames)) {
            $insertFields[] = 'stock_quantity';
            $insertValues[] = ':stock';
            $insertParams[':stock'] = $quantityStock;
          }

          if (in_array('price', $columnNames)) {
            $insertFields[] = 'price';
            $insertValues[] = ':price';
            $insertParams[':price'] = $price;
          }

          if (in_array('description', $columnNames)) {
            $insertFields[] = 'description';
            $insertValues[] = ':desc';
            $insertParams[':desc'] = $description;
          }

          // Build and execute the smart query
          $query = "INSERT INTO `medicines`(" . implode(', ', $insertFields) . ") VALUES(" . implode(', ', $insertValues) . ")";
          
          $stmtMedicine = $con->prepare($query);
          foreach ($insertParams as $param => $value) {
            if (in_array($param, [':stock'])) {
              $stmtMedicine->bindValue($param, $value, PDO::PARAM_INT);
            } else {
              $stmtMedicine->bindValue($param, $value, PDO::PARAM_STR);
            }
          }
          $stmtMedicine->execute();
          $medicineId = $con->lastInsertId();

          // Check if medicine_details table exists and has required columns
          $detailsCheckStmt = $con->prepare("DESCRIBE medicine_details");
          $detailsCheckStmt->execute();
          $detailsColumns = $detailsCheckStmt->fetchAll(PDO::FETCH_ASSOC);
          $detailsColumnNames = array_column($detailsColumns, 'Field');

          if (in_array('medicine_id', $detailsColumnNames) && in_array('packing', $detailsColumnNames)) {
            // Insert into medicine_details if table exists
            $detailsFields = ['medicine_id', 'packing'];
            $detailsValues = [':mid', ':pack'];
            $detailsParams = [':mid' => $medicineId, ':pack' => $packing];

            // Add optional detail fields
            if (in_array('expiry_date', $detailsColumnNames)) {
              $detailsFields[] = 'expiry_date';
              $detailsValues[] = ':exp';
              $detailsParams[':exp'] = $expiryDate;
            }

            if (in_array('quantity_stock', $detailsColumnNames)) {
              $detailsFields[] = 'quantity_stock';
              $detailsValues[] = ':qty';
              $detailsParams[':qty'] = $quantityStock;
            }

            if (in_array('price', $detailsColumnNames)) {
              $detailsFields[] = 'price';
              $detailsValues[] = ':price';
              $detailsParams[':price'] = $price;
            }

            $queryDetails = "INSERT INTO `medicine_details`(" . implode(', ', $detailsFields) . ") VALUES(" . implode(', ', $detailsValues) . ")";
            $stmtDetails = $con->prepare($queryDetails);
            
            foreach ($detailsParams as $param => $value) {
              if (in_array($param, [':mid', ':qty'])) {
                $stmtDetails->bindValue($param, $value, PDO::PARAM_INT);
              } else {
                $stmtDetails->bindValue($param, $value, PDO::PARAM_STR);
              }
            }
            $stmtDetails->execute();
          }

          $con->commit();
          $message = '✅ Medicine added successfully!';
          // Redirect to show success message
          header("Location:congratulation.php?goto_page=medicines.php&message=" . urlencode($message));
          exit;
        }
      } catch(PDOException $ex) {
        $con->rollback();
        app_log_exception($ex);
        
        // Provide specific error messages
        if ($ex->getCode() == 23000 || $ex->getCode() == 1062) {
          $message = 'This medicine already exists in the system.';
        } elseif (strpos($ex->getMessage(), 'Unknown column') !== false) {
          $message = 'Database structure mismatch. Please contact administrator.';
        } else {
          $message = 'Error saving medicine: ' . $ex->getMessage();
        }
      }
    }
  }
  
  header("Location:congratulation.php?goto_page=medicines.php&message=" . urlencode($message));
  exit;
}

// Fetch existing medicines with smart column detection
try {
  // Check what columns exist in medicines table
  $checkStmt = $con->prepare("DESCRIBE medicines");
  $checkStmt->execute();
  $availableColumns = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
  $columnNames = array_column($availableColumns, 'Field');

  // Build SELECT query based on available columns
  $selectFields = ['m.`id`', 'm.`medicine_name`'];
  
  if (in_array('generic_name', $columnNames)) {
    $selectFields[] = 'm.`generic_name`';
  }
  if (in_array('brand_name', $columnNames)) {
    $selectFields[] = 'm.`brand_name`';
  }
  if (in_array('dosage_form', $columnNames)) {
    $selectFields[] = 'm.`dosage_form`';
  }
  if (in_array('dosage_strength', $columnNames)) {
    $selectFields[] = 'm.`dosage_strength`';
  }

  // Check if medicine_details table exists and join if available
  try {
    $detailsCheckStmt = $con->prepare("DESCRIBE medicine_details");
    $detailsCheckStmt->execute();
    $detailsColumnNames = array_column($detailsCheckStmt->fetchAll(PDO::FETCH_ASSOC), 'Field');

    if (in_array('medicine_id', $detailsColumnNames)) {
      $selectFields[] = 'md.`id` AS mdid';
      if (in_array('packing', $detailsColumnNames)) {
        $selectFields[] = 'md.`packing`';
      }
      if (in_array('expiry_date', $detailsColumnNames)) {
        $selectFields[] = 'md.`expiry_date`';
      }
      if (in_array('quantity_stock', $detailsColumnNames)) {
        $selectFields[] = 'md.`quantity_stock`';
      }
      if (in_array('price', $detailsColumnNames)) {
        $selectFields[] = 'md.`price`';
      }

      $query = "SELECT " . implode(', ', $selectFields) . "
               FROM `medicines` AS m
               LEFT JOIN `medicine_details` AS md ON m.`id` = md.`medicine_id`
               ORDER BY m.`medicine_name` ASC";
    } else {
      throw new Exception("medicine_details table not available");
    }
  } catch(Exception $e) {
    // Fallback to medicines only
    $query = "SELECT " . implode(', ', $selectFields) . " FROM `medicines` AS m ORDER BY m.`medicine_name` ASC";
  }

  $stmt = $con->prepare($query);
  $stmt->execute();

} catch(PDOException $ex) {
  app_log_exception($ex);
  $message = 'Error loading medicines: ' . $ex->getMessage();
  $stmt = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <?php 
 // Use basic includes that always work
if (file_exists('./config/site_css_links.php')) {
    include './config/site_css_links.php';
} else {
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">';
}
if (file_exists('./config/data_tables_css.php')) {
    include './config/data_tables_css.php';
} else {
    echo '<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">';
}
?>
 <title>Medicines - Medical Care and Information system</title>
 
 <style>
   /* Enhanced UI Styles for Medicines */
   .medicine-card {
     border: none;
     border-radius: 12px;
     box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
     transition: all 0.3s ease;
     margin-bottom: 2rem;
   }
   .medicine-card:hover {
     box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
     transform: translateY(-2px);
   }
   .medicine-card .card-header {
     background: linear-gradient(135deg, #10b981 0%, #059669 100%);
     color: white;
     border-radius: 12px 12px 0 0;
     border: none;
     padding: 1.5rem;
   }
   .medicine-card .card-header h3 {
     margin: 0;
     font-weight: 600;
     display: flex;
     align-items: center;
     gap: 0.5rem;
   }
   .medicine-card .card-body {
     padding: 2rem;
   }
   .form-group-enhanced {
     margin-bottom: 1.5rem;
   }
   .form-group-enhanced label {
     font-weight: 600;
     color: #374151;
     margin-bottom: 0.5rem;
     display: block;
   }
   .form-control-enhanced {
     border: 2px solid #e5e7eb;
     border-radius: 8px;
     padding: 0.5rem 1rem;
     transition: all 0.3s ease;
     font-size: 0.95rem;
   }
   .form-control-enhanced:focus {
     border-color: #10b981;
     box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
     outline: none;
   }
   .btn-enhanced {
     border-radius: 8px;
     padding: 0.75rem 1.5rem;
     font-weight: 600;
     transition: all 0.3s ease;
     border: none;
   }
   .btn-enhanced:hover {
     transform: translateY(-1px);
     box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
   }
   .btn-primary-enhanced {
     background: linear-gradient(135deg, #10b981 0%, #059669 100%);
     color: white;
   }
   .btn-primary-enhanced:hover {
     background: linear-gradient(135deg, #059669 0%, #047857 100%);
     color: white;
   }
   .table-enhanced {
     border-radius: 8px;
     overflow: hidden;
     box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
   }
   .table-enhanced thead th {
     background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
     border: none;
     font-weight: 600;
     color: #374151;
     padding: 1rem;
   }
   .table-enhanced tbody td {
     padding: 1rem;
     border-color: #e5e7eb;
     vertical-align: middle;
   }
   .table-enhanced tbody tr:hover {
     background-color: #f8fafc;
   }
   .stock-indicator {
     display: inline-block;
     padding: 0.25rem 0.75rem;
     border-radius: 20px;
     font-size: 0.8rem;
     font-weight: 600;
     text-transform: uppercase;
     letter-spacing: 0.5px;
   }
   .stock-high {
     background: linear-gradient(135deg, #10b981 0%, #059669 100%);
     color: white;
   }
   .stock-medium {
     background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
     color: white;
   }
   .stock-low {
     background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
     color: white;
   }
   .stock-out {
     background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
     color: white;
   }
   .action-buttons {
     display: flex;
     gap: 0.5rem;
     justify-content: center;
   }
   .btn-action {
     border-radius: 6px;
     padding: 0.5rem 0.75rem;
     font-size: 0.85rem;
     transition: all 0.3s ease;
   }
   .btn-action:hover {
     transform: translateY(-1px);
     box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
   }
   .loading-overlay {
     position: absolute;
     top: 0;
     left: 0;
     right: 0;
     bottom: 0;
     background: rgba(255, 255, 255, 0.9);
     display: none;
     align-items: center;
     justify-content: center;
     z-index: 1000;
     border-radius: 8px;
   }
   .spinner {
     width: 40px;
     height: 40px;
     border: 4px solid #e5e7eb;
     border-top: 4px solid #10b981;
     border-radius: 50%;
     animation: spin 1s linear infinite;
   }
   @keyframes spin {
     0% { transform: rotate(0deg); }
     100% { transform: rotate(360deg); }
   }
   .icon-wrapper {
     width: 40px;
     height: 40px;
     border-radius: 50%;
     background: rgba(255, 255, 255, 0.2);
     display: flex;
     align-items: center;
     justify-content: center;
     margin-right: 1rem;
   }
   .form-section {
     background: #f8fafc;
     border-radius: 10px;
     padding: 1.5rem;
     margin-bottom: 1rem;
   }
   .medicine-icon {
     font-size: 1.2rem;
     margin-right: 0.5rem;
   }
   .price-display {
     font-weight: 700;
     color: #10b981;
   }
   .expiry-warning {
     color: #f59e0b;
     font-weight: 600;
   }
   .expiry-danger {
     color: #ef4444;
     font-weight: 600;
   }
   .form-validation {
     margin-top: 0.25rem;
     font-size: 0.8rem;
   }
   .validation-error {
     color: #ef4444;
   }
   .validation-success {
     color: #10b981;
   }
   .medicine-check {
     display: flex;
     align-items: center;
     gap: 0.5rem;
   }
   .check-icon {
     font-size: 0.8rem;
   }
 </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
  <!-- Site wrapper -->
  <div class="wrapper">
    <!-- Navbar -->
    <?php 
if (file_exists('./config/header.php')) {
    include './config/header.php';
} else {
    echo '<nav class="navbar navbar-expand-lg navbar-dark bg-dark"><div class="container-fluid"><a class="navbar-brand" href="dashboard.php">PMS</a><div class="collapse navbar-collapse"><ul class="navbar-nav"><li class="nav-item"><a class="nav-link" href="medicines.php">Medicines</a></li><li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li></ul></div></div></nav>';
}
include './config/sidebar.php'; ?>  
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
      <!-- Main content -->
      <section class="content">
        <!-- Add Medicine Form -->
        <div class="card medicine-card">
          <div class="card-header">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper">
                <i class="fas fa-pills"></i>
              </div>
              <div>
                <h3 class="card-title">Add New Medicine</h3>
                <small class="opacity-75">Add a new medicine to the pharmacy inventory</small>
              </div>
            </div>
            <div class="card-tools">
              <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body position-relative">
            <div class="loading-overlay" id="form_loading">
              <div class="spinner"></div>
            </div>
            <form method="post" id="medicine_form" action="medicines.php">
             <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
             
             <div class="form-section">
               <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Medicine Information</h5>
               <div class="row">
                 <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                   <div class="form-group-enhanced">
                     <label for="generic_name">
                       <i class="fas fa-flask me-2"></i>Generic Name <span class="text-danger">*</span>
                     </label>
                     <input type="text" id="generic_name" name="generic_name" required="required"
                     class="form-control form-control-enhanced" placeholder="Enter active ingredient (e.g., Paracetamol)"/>
                     <small class="text-muted">The active pharmaceutical ingredient</small>
                   </div>
                 </div>
                 
                 <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                   <div class="form-group-enhanced">
                     <label for="brand_name">
                       <i class="fas fa-tag me-2"></i>Brand Name <span class="text-danger">*</span>
                     </label>
                     <input type="text" id="brand_name" name="brand_name" required="required"
                     class="form-control form-control-enhanced" placeholder="Enter brand name (e.g., Biogesic)"/>
                     <small class="text-muted">Commercial name of the medicine</small>
                   </div>
                 </div>
               </div>
             </div>

             <div class="form-section">
               <h5 class="mb-3"><i class="fas fa-capsules me-2"></i>Dosage & Form</h5>
               <div class="row">
                 <div class="col-lg-4 col-md-4 col-sm-6 mb-3">
                   <div class="form-group-enhanced">
                     <label for="dosage_form">
                       <i class="fas fa-shapes me-2"></i>Dosage Form
                     </label>
                     <select id="dosage_form" name="dosage_form" class="form-control form-control-enhanced">
                       <option value="">Select Form</option>
                       <option value="Tablet">💊 Tablet</option>
                       <option value="Capsule">💊 Capsule</option>
                       <option value="Syrup">🧪 Syrup</option>
                       <option value="Injection">💉 Injection</option>
                       <option value="Cream">🧴 Cream</option>
                       <option value="Ointment">🧴 Ointment</option>
                       <option value="Drops">💧 Drops</option>
                       <option value="Powder">⚗️ Powder</option>
                     </select>
                   </div>
                 </div>

                 <div class="col-lg-4 col-md-4 col-sm-6 mb-3">
                   <div class="form-group-enhanced">
                     <label for="dosage_strength">
                       <i class="fas fa-weight me-2"></i>Dosage Strength
                     </label>
                     <input type="text" id="dosage_strength" name="dosage_strength"
                     class="form-control form-control-enhanced" placeholder="e.g., 500mg, 250mg"/>
                   </div>
                 </div>
                 
                 <div class="col-lg-4 col-md-4 col-sm-6 mb-3">
                   <div class="form-group-enhanced">
                     <label for="packing">
                       <i class="fas fa-box me-2"></i>Packing <span class="text-danger">*</span>
                     </label>
                     <input type="text" id="packing" name="packing" required="required"
                     class="form-control form-control-enhanced" placeholder="e.g., 100ml, 10 caps"/>
                   </div>
                 </div>
               </div>
             </div>

             <div class="form-section">
               <h5 class="mb-3"><i class="fas fa-warehouse me-2"></i>Inventory & Pricing</h5>
               <div class="row">
                 <div class="col-lg-4 col-md-4 col-sm-6 mb-3">
                   <div class="form-group-enhanced">
                     <label for="expiry_date">
                       <i class="fas fa-calendar-times me-2"></i>Expiry Date <span class="text-danger">*</span>
                     </label>
                     <input type="date" id="expiry_date" name="expiry_date" required="required"
                     class="form-control form-control-enhanced"/>
                   </div>
                 </div>
                 
                 <div class="col-lg-4 col-md-4 col-sm-6 mb-3">
                   <div class="form-group-enhanced">
                     <label for="price">
                       <i class="fas fa-dollar-sign me-2"></i>Price <span class="text-danger">*</span>
                     </label>
                     <input type="number" step="0.01" id="price" name="price" required="required"
                     class="form-control form-control-enhanced" placeholder="e.g., 120.00"/>
                   </div>
                 </div>
                 
                 <div class="col-lg-4 col-md-4 col-sm-6 mb-3">
                   <div class="form-group-enhanced">
                     <label for="quantity_stock">
                       <i class="fas fa-boxes me-2"></i>Quantity/Stock <span class="text-danger">*</span>
                     </label>
                     <input type="number" id="quantity_stock" name="quantity_stock" required="required"
                     class="form-control form-control-enhanced" placeholder="e.g., 100"/>
                   </div>
                 </div>
               </div>
             </div>

             <div class="form-section">
               <h5 class="mb-3"><i class="fas fa-sticky-note me-2"></i>Additional Information</h5>
               <div class="row">
                 <div class="col-lg-12 mb-3">
                   <div class="form-group-enhanced">
                     <label for="description">
                       <i class="fas fa-file-alt me-2"></i>Description / Notes
                     </label>
                     <textarea id="description" name="description" rows="3"
                     class="form-control form-control-enhanced" placeholder="Optional details, usage instructions, or special notes"></textarea>
                   </div>
                 </div>
               </div>
             </div>

             <div class="row mt-4">
               <div class="col-12 text-right">
                 <button type="submit" id="save_medicine" name="save_medicine" 
                 class="btn btn-primary-enhanced btn-enhanced">
                   <i class="fas fa-save me-2"></i>Add Medicine to Inventory
                 </button>
               </div>
             </div>
            </form>
          </div>
        </div>

      </div>
      <!-- /.card -->
    </section>
    <section class="content">
      <!-- Default box -->
      <div class="card medicine-card">
        <div class="card-header">
          <div class="d-flex align-items-center">
            <div class="icon-wrapper">
              <i class="fas fa-list-alt"></i>
            </div>
            <div>
              <h3 class="card-title">Medicine Inventory</h3>
              <small class="opacity-75">View and manage all medicines in the pharmacy inventory</small>
            </div>
          </div>
          <div class="card-tools">
            <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body position-relative">
          <div class="loading-overlay" id="table_loading">
            <div class="spinner"></div>
          </div>
          
          <div class="table-responsive">
            <table id="all_medicines" class="table table-enhanced" role="grid">
              <thead>
                <tr>
                 <th><i class="fas fa-hashtag me-2"></i>#</th>
                 <th><i class="fas fa-pills me-2"></i>Medicine Name</th>
                 <?php if ($stmt && in_array('generic_name', $columnNames)): ?>
                 <th><i class="fas fa-flask me-2"></i>Generic Name</th>
                 <?php endif; ?>
                 <?php if ($stmt && in_array('brand_name', $columnNames)): ?>
                 <th><i class="fas fa-tag me-2"></i>Brand Name</th>
                 <?php endif; ?>
                 <?php if ($stmt && in_array('dosage_form', $columnNames)): ?>
                 <th><i class="fas fa-shapes me-2"></i>Form</th>
                 <?php endif; ?>
                 <?php if ($stmt && in_array('dosage_strength', $columnNames)): ?>
                 <th><i class="fas fa-weight me-2"></i>Strength</th>
                 <?php endif; ?>
                 <?php if ($stmt && isset($detailsColumnNames) && in_array('packing', $detailsColumnNames)): ?>
                 <th><i class="fas fa-box me-2"></i>Packing</th>
                 <?php endif; ?>
                 <?php if ($stmt && isset($detailsColumnNames) && in_array('quantity_stock', $detailsColumnNames)): ?>
                 <th><i class="fas fa-boxes me-2"></i>Stock</th>
                 <?php endif; ?>
                 <?php if ($stmt && isset($detailsColumnNames) && in_array('price', $detailsColumnNames)): ?>
                 <th><i class="fas fa-dollar-sign me-2"></i>Price</th>
                 <?php endif; ?>
                 <th><i class="fas fa-cogs me-2"></i>Actions</th>
               </tr>
             </thead>

             <tbody>
              <?php 
              if ($stmt) {
                $serial = 0;
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                 $serial++;
                 $stock = isset($row['quantity_stock']) ? (int)$row['quantity_stock'] : 0;
                 $price = isset($row['price']) ? (float)$row['price'] : 0;
                 $expiryDate = isset($row['expiry_date']) ? $row['expiry_date'] : '';
                 
                 // Determine stock status
                 $stockClass = 'stock-high';
                 $stockText = 'In Stock';
                 if ($stock == 0) {
                   $stockClass = 'stock-out';
                   $stockText = 'Out of Stock';
                 } elseif ($stock <= 10) {
                   $stockClass = 'stock-low';
                   $stockText = 'Low Stock';
                 } elseif ($stock <= 50) {
                   $stockClass = 'stock-medium';
                   $stockText = 'Medium Stock';
                 }
                 
                 // Check expiry date
                 $expiryClass = '';
                 $expiryIcon = '';
                 if ($expiryDate) {
                   $expiryTimestamp = strtotime($expiryDate);
                   $currentTimestamp = time();
                   $daysUntilExpiry = ($expiryTimestamp - $currentTimestamp) / (60 * 60 * 24);
                   
                   if ($daysUntilExpiry < 0) {
                     $expiryClass = 'expiry-danger';
                     $expiryIcon = '⚠️';
                   } elseif ($daysUntilExpiry <= 30) {
                     $expiryClass = 'expiry-warning';
                     $expiryIcon = '⚠️';
                   }
                 }
                 ?>
                 <tr>
                   <td class="text-center"><?php echo $serial;?></td>
                   
                   <td>
                     <div class="d-flex align-items-center">
                       <div class="medicine-icon">💊</div>
                       <div>
                         <strong><?php echo htmlspecialchars($row['medicine_name']);?></strong>
                         <?php if ($expiryIcon): ?>
                           <br><small class="<?php echo $expiryClass; ?>"><?php echo $expiryIcon; ?> Expires: <?php echo date('M Y', strtotime($expiryDate)); ?></small>
                         <?php endif; ?>
                       </div>
                     </div>
                   </td>
                   
                   <?php if (in_array('generic_name', $columnNames)): ?>
                   <td>
                     <span class="fw-semibold"><?php echo htmlspecialchars($row['generic_name'] ?: '-');?></span>
                   </td>
                   <?php endif; ?>
                   
                   <?php if (in_array('brand_name', $columnNames)): ?>
                   <td>
                     <span class="fw-semibold"><?php echo htmlspecialchars($row['brand_name'] ?: '-');?></span>
                   </td>
                   <?php endif; ?>
                   
                   <?php if (in_array('dosage_form', $columnNames)): ?>
                   <td>
                     <span class="badge bg-light text-dark">
                       <?php echo htmlspecialchars($row['dosage_form'] ?: '-');?>
                     </span>
                   </td>
                   <?php endif; ?>
                   
                   <?php if (in_array('dosage_strength', $columnNames)): ?>
                   <td>
                     <span class="text-muted"><?php echo htmlspecialchars($row['dosage_strength'] ?: '-');?></span>
                   </td>
                   <?php endif; ?>
                   
                   <?php if (isset($detailsColumnNames) && in_array('packing', $detailsColumnNames)): ?>
                   <td>
                     <span class="text-muted"><?php echo htmlspecialchars($row['packing'] ?: '-');?></span>
                   </td>
                   <?php endif; ?>
                   
                   <?php if (isset($detailsColumnNames) && in_array('quantity_stock', $detailsColumnNames)): ?>
                   <td>
                     <div class="d-flex align-items-center">
                       <span class="stock-indicator <?php echo $stockClass; ?>">
                         <?php echo $stockText; ?>
                       </span>
                       <small class="ms-2 text-muted">(<?php echo $stock; ?>)</small>
                     </div>
                   </td>
                   <?php endif; ?>
                   
                   <?php if (isset($detailsColumnNames) && in_array('price', $detailsColumnNames)): ?>
                   <td>
                     <span class="price-display">₱<?php echo number_format($price, 2);?></span>
                   </td>
                   <?php endif; ?>
                   
                   <td>
                     <div class="action-buttons">
                       <a href="update_medicine.php?id=<?php echo (int)$row['id'];?>" 
                          class="btn btn-primary btn-action" title="Edit Medicine">
                         <i class="fas fa-edit"></i>
                       </a>
                       <button type="button" class="btn btn-danger btn-action" title="Delete Medicine"
                               onclick="deleteMedicine(<?php echo (int)$row['id'];?>)">
                         <i class="fas fa-trash"></i>
                       </button>
                     </div>
                   </td>
                 </tr>
               <?php } 
              } else { ?>
                <tr>
                  <td colspan="10" class="text-center">
                    <div class="alert alert-warning">
                      <i class="fas fa-exclamation-triangle me-2"></i>
                      <?php echo $message ?: 'No medicines found or unable to load medicines.'; ?>
                    </div>
                  </td>
                </tr>
              <?php } ?>
           </tbody>
         </table>
       </div>
     </div>

</div>
<!-- /.card -->

</section>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php 
// include './config/footer.php';

$message = '';
if(isset($_GET['message'])) {
  $message = $_GET['message'];
}
?>  
<!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<?php 
// Use basic JS includes that always work
if (file_exists('./config/site_js_links.php')) {
    include './config/site_js_links.php';
} else {
    echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
}
if (file_exists('./config/data_tables_js.php')) {
    include './config/data_tables_js.php';
} else {
    echo '<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>';
    echo '<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>';
}
?>

<script>
  showMenuSelected("#mnu_medicines", "#mi_medicines");

  var message = '<?php echo $message;?>';

  if(message !== '') {
    showCustomMessage(message);
  }

  $(function () {
    // Check if DataTable is already initialized to prevent reinitialization
    if (!$.fn.DataTable.isDataTable('#all_medicines')) {
      $("#all_medicines").DataTable({
        "responsive": true, "lengthChange": false, "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
      }).buttons().container().appendTo('#all_medicines_wrapper .col-md-6:eq(0)');
    }
  });

  
  
  // Simple form submission without JavaScript interference
  // The form will submit naturally to the server

  function deleteMedicine(id) {
    if (confirm("Are you sure you want to delete this medicine?\n\nThis action cannot be undone.")) {
      var adminPwd = prompt('Enter admin password to confirm deletion:');
      if (adminPwd === null) { return; }
      adminPwd = adminPwd.trim();
      if (adminPwd === '') { 
        showCustomMessage('Password is required.', 'warning'); 
        return; 
      }
      
      // Show loading state
      $('#table_loading').css('display', 'flex');
      
      $.ajax({
        url: "ajax/delete_medicine.php",
        type: "POST",
        data: {'id': id, 'admin_password': adminPwd, 'csrf_token': '<?php echo $_SESSION['csrf_token']; ?>'},
        success: function(response) {
          $('#table_loading').hide();
          if (response === "success") {
            showCustomMessage("Medicine deleted successfully.", 'success');
            setTimeout(() => location.reload(), 1500);
          } else {
            showCustomMessage("Error deleting medicine: " + response, 'error');
          }
        },
        error: function(jqXhr, textStatus, errorMessage) {
          $('#table_loading').hide();
          showCustomMessage('Failed to delete medicine. Please try again.', 'error');
        }
      });
    }
  }
</script>
</body>
</html>
