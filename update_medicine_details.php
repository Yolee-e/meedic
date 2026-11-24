<?php 
include './config/connection.php';
include './common_service/common_functions.php';

$message = '';

if(isset($_POST['submit'])) {
  csrf_validate_post();

  $medicineId = $_POST['medicine'];
  $medicineDetailId = $_POST['hidden_id'];
  $packing = $_POST['packing'];  
  $dosage = trim($_POST['dosage']);
  $expiryDate = trim($_POST['expiry_date']);
  $quantityStock = trim($_POST['quantity_stock']);

  $query = "UPDATE `medicine_details` 
  SET `medicine_id` = :mid, 
      `packing` = :pack, 
      `dosage` = :dos, 
      `expiry_date` = :exp, 
      `quantity_stock` = :qty 
  WHERE `id` = :id";

  try {

    $con->beginTransaction();

    $stmtUpdate = $con->prepare($query);
    $stmtUpdate->bindParam(':mid', $medicineId, PDO::PARAM_INT);
    $stmtUpdate->bindParam(':pack', $packing);
    $stmtUpdate->bindParam(':dos', $dosage);
    $stmtUpdate->bindParam(':exp', $expiryDate);
    $stmtUpdate->bindParam(':qty', $quantityStock, PDO::PARAM_INT);
    $stmtUpdate->bindParam(':id', $medicineDetailId, PDO::PARAM_INT);
    $stmtUpdate->execute();

    $con->commit();

    $message = 'medicine details updated successfully.';

  }  catch(PDOException $ex) {
    $con->rollback();

    echo $ex->getMessage();
    echo $ex->getTraceAsString();
    exit;
  }
  header("location:congratulation.php?goto_page=medicine_details.php&message=$message");
  exit;
}

$medicineId = $_GET['medicine_id'];
$medicineDetailId = $_GET['medicine_detail_id'];
$packing = $_GET['packing'];
$dosage = $_GET['dosage'];
$expiryDate = $_GET['expiry_date'];
$quantityStock = $_GET['quantity_stock'];

$medicines = getMedicines($con, $medicineId);

?>
<!DOCTYPE html>
<html lang="en">
<head>
 <?php include './config/site_css_links.php';?>
 <?php include './config/data_tables_css.php';?>
 <title>Update Medicine Details - Medical Care and Information system</title>

 <style>
   /* Enhanced UI Styles for Update Medicine Details */
   .update-medicine-card {
     border: none;
     border-radius: 12px;
     box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
     transition: all 0.3s ease;
     margin-bottom: 2rem;
   }
   .update-medicine-card:hover {
     box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
     transform: translateY(-2px);
   }
   .update-medicine-card .card-header {
     background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
     color: white;
     border-radius: 12px 12px 0 0;
     border: none;
     padding: 1.5rem;
   }
   .update-medicine-card .card-header h3 {
     margin: 0;
     font-weight: 600;
     display: flex;
     align-items: center;
     gap: 0.5rem;
   }
   .update-medicine-card .card-body {
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
     padding: 0.75rem 1rem;
     transition: all 0.3s ease;
     font-size: 0.95rem;
   }
   .form-control-enhanced:focus {
     border-color: #f59e0b;
     box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
     outline: none;
   }
   .btn-enhanced {
     border-radius: 8px;
     padding: 0.75rem 1.5rem;
     font-weight: 600;
     transition: all 0.3s ease;
     border: none;
     position: relative;
     overflow: hidden;
   }
   .btn-enhanced:hover {
     transform: translateY(-1px);
     box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
   }
   .btn-primary-enhanced {
     background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
     color: white;
   }
   .btn-primary-enhanced:hover {
     background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
     color: white;
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
     border-top: 4px solid #f59e0b;
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
     border-radius: 8px;
     padding: 1.5rem;
     margin-bottom: 1rem;
   }
   .medicine-icon {
     font-size: 1.2rem;
     margin-right: 0.5rem;
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
   .expiry-warning {
     color: #f59e0b;
     font-weight: 600;
   }
   .expiry-danger {
     color: #ef4444;
     font-weight: 600;
   }
   .current-values {
     background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
     border-radius: 8px;
     padding: 1rem;
     margin-bottom: 1.5rem;
   }
   .current-values h6 {
     color: #1e40af;
     font-weight: 600;
     margin-bottom: 0.5rem;
   }
   .value-item {
     display: flex;
     justify-content: space-between;
     padding: 0.25rem 0;
     border-bottom: 1px solid rgba(30, 64, 175, 0.1);
   }
   .value-item:last-child {
     border-bottom: none;
   }
   .value-label {
     font-weight: 500;
     color: #374151;
   }
   .value-content {
     color: #1e40af;
     font-weight: 600;
   }
   .medicine-selector {
     background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
     border: 2px solid #e5e7eb;
     border-radius: 12px;
     padding: 1.5rem;
     margin-top: 1rem;
   }
   .medicine-selector:hover {
     border-color: #f59e0b;
     box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
   }
   .medicine-option {
     display: flex;
     align-items: center;
     padding: 0.75rem;
     margin: 0.5rem 0;
     border-radius: 8px;
     background: white;
     border: 1px solid #e5e7eb;
     transition: all 0.3s ease;
     cursor: pointer;
   }
   .medicine-option:hover {
     background: #f8fafc;
     border-color: #f59e0b;
     transform: translateY(-1px);
     box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
   }
   .medicine-option.selected {
     background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
     color: white;
     border-color: #d97706;
   }
   .medicine-icon {
     font-size: 1.5rem;
     margin-right: 1rem;
     width: 40px;
     text-align: center;
   }
   .medicine-details h6 {
     margin: 0;
     font-weight: 600;
     font-size: 1rem;
   }
   .medicine-details small {
     opacity: 0.8;
     font-size: 0.85rem;
   }
   .dosage-form-selector {
     background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
     border: 2px solid #e5e7eb;
     border-radius: 12px;
     padding: 1.5rem;
     margin-top: 1rem;
   }
   .dosage-form-selector:hover {
     border-color: #f59e0b;
     box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
   }
   .dosage-option {
     display: flex;
     align-items: center;
     padding: 0.75rem;
     margin: 0.25rem 0;
     border-radius: 8px;
     background: white;
     border: 1px solid #e5e7eb;
     transition: all 0.3s ease;
     cursor: pointer;
     text-align: center;
     flex-direction: column;
   }
   .dosage-option:hover {
     background: #f8fafc;
     border-color: #f59e0b;
     transform: translateY(-1px);
     box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
   }
   .dosage-option.selected {
     background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
     color: white;
     border-color: #d97706;
   }
   .dosage-icon {
     font-size: 1.5rem;
     margin-bottom: 0.5rem;
     width: 100%;
     text-align: center;
   }
   .dosage-details h6 {
     margin: 0;
     font-weight: 600;
     font-size: 0.9rem;
   }
   .dosage-details small {
     opacity: 0.8;
     font-size: 0.75rem;
   }
   .dosage-textarea {
     background: white;
     border: 2px solid #e5e7eb;
     border-radius: 8px;
     padding: 1rem;
     font-size: 1rem;
     line-height: 1.5;
     transition: all 0.3s ease;
     resize: vertical;
     min-height: 80px;
   }
   .dosage-textarea:focus {
     border-color: #f59e0b;
     box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
     outline: none;
   }
   .dosage-textarea:hover {
     border-color: #d1d5db;
   }
   .dosage-dropdown-container {
     background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
     border: 3px solid #e5e7eb;
     border-radius: 15px;
     padding: 2rem;
     margin-top: 1rem;
     overflow: visible;
   }
   .dosage-dropdown-container:hover {
     border-color: #f59e0b;
     box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
   }
   .dosage-dropdown {
     background: white;
     border: 4px solid #e5e7eb;
     border-radius: 15px;
     font-size: 1.5rem;
     font-weight: 600;
     padding: 2rem;
     min-height: 100px;
     line-height: 1.8;
     transition: all 0.3s ease;
     box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
     width: 100%;
   }
   .dosage-dropdown:focus {
     border-color: #f59e0b;
     box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
     outline: none;
   }
   .dosage-dropdown:hover {
     border-color: #d1d5db;
   }
   .dosage-dropdown option {
     padding: 1.5rem;
     font-size: 1.4rem;
     font-weight: 600;
     line-height: 1.6;
     min-height: 60px;
     background: white;
     color: #374151;
   }
   .custom-dosage-input {
     background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
     border: 3px solid #f59e0b;
     border-radius: 12px;
     font-size: 1.6rem;
     font-weight: 700;
     padding: 2rem;
     min-height: 100px;
     line-height: 1.8;
     transition: all 0.3s ease;
     box-shadow: 0 4px 8px rgba(245, 158, 11, 0.2);
   }
   .custom-dosage-input:focus {
     border-color: #d97706;
     box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.3);
     outline: none;
   }
   .custom-dosage-input:hover {
     border-color: #d97706;
     box-shadow: 0 6px 12px rgba(245, 158, 11, 0.3);
   }
   @media (max-width: 768px) {
     .update-medicine-card .card-body {
       padding: 1rem;
     }
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
              <h1>Update Medicine Detail</h1>
            </div>
          </div>
        </div><!-- /.container-fluid -->
      </section>

      <!-- Main content -->
      <section class="content">

        <!-- Update Medicine Details Form -->
        <div class="card update-medicine-card">
          <div class="card-header">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper">
                <i class="fas fa-edit"></i>
              </div>
              <div>
                <h3 class="card-title">Update Medicine Details</h3>
                <small class="opacity-75">Modify medicine details and inventory information</small>
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
            
            <!-- Current Values Display -->
            <div class="current-values">
              <h6><i class="fas fa-info-circle me-2"></i>Current Values</h6>
              <div class="value-item">
                <span class="value-label">Packing:</span>
                <span class="value-content"><?php echo htmlspecialchars($packing);?></span>
              </div>
              <div class="value-item">
                <span class="value-label">Dosage:</span>
                <span class="value-content"><?php echo htmlspecialchars($dosage);?></span>
              </div>
              <div class="value-item">
                <span class="value-label">Expiry Date:</span>
                <span class="value-content"><?php echo date('M d, Y', strtotime($expiryDate));?></span>
              </div>
              <div class="value-item">
                <span class="value-label">Stock Quantity:</span>
                <span class="value-content"><?php echo htmlspecialchars($quantityStock);?></span>
              </div>
            </div>
            
            <form method="post" id="update_medicine_form">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="hidden_id" value="<?php echo (int)$medicineDetailId;?>" />

              <div class="form-section">
                <h5 class="mb-3"><i class="fas fa-pills me-2"></i>Medicine Selection</h5>
                <div class="row">
                  <div class="col-lg-12 mb-3">
                    <div class="form-group-enhanced">
                      <label for="medicine">
                        <i class="fas fa-list me-2"></i>Select Medicine to Update
                      </label>
                      
                      <!-- Hidden select for form submission -->
                      <select id="medicine" name="medicine" class="form-control form-control-enhanced" required="required" style="display: none;">
                        <?php echo $medicines;?>
                      </select>
                      
                      <!-- Enhanced Medicine Selector -->
                      <div class="medicine-selector">
              <div class="row">
                          <div class="col-md-6 mb-3">
                            <div class="medicine-option" data-medicine="<?php echo $medicineId; ?>">
                              <div class="medicine-icon">💊</div>
                              <div class="medicine-details">
                                <h6>Current Medicine</h6>
                                <small>Currently selected medicine</small>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-6 mb-3">
                            <div class="medicine-option" data-medicine="other">
                              <div class="medicine-icon">🔄</div>
                              <div class="medicine-details">
                                <h6>Change Medicine</h6>
                                <small>Select a different medicine</small>
                              </div>
                            </div>
                          </div>
                        </div>
                        
                        <!-- Medicine Selection Dropdown (shown when "Change Medicine" is selected) -->
                        <div id="medicine_dropdown" style="display: none;">
                          <label class="form-label mt-3 mb-2">
                            <i class="fas fa-search me-2"></i>Search and Select Medicine
                          </label>
                          <select id="medicine_select" class="form-control form-control-enhanced" style="font-size: 1rem; padding: 0.75rem;">
                    <?php echo $medicines;?>
                  </select>
                </div>
                </div>

                      <div class="mt-2">
                        <small class="text-muted">
                          <i class="fas fa-info-circle me-1"></i>
                          Choose the medicine you want to update details for. This will affect the medicine details record.
                        </small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-section">
                <h5 class="mb-3"><i class="fas fa-capsules me-2"></i>Medicine Specifications</h5>
                <div class="row">
                  <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                    <div class="form-group-enhanced">
                      <label for="packing">
                        <i class="fas fa-box me-2"></i>Packing
                      </label>
                      <input id="packing" name="packing" class="form-control form-control-enhanced" required="required" 
                             value="<?php echo htmlspecialchars($packing);?>" placeholder="e.g., 100mg, 500ml"/>
                      <small class="text-muted">Package size or volume</small>
                    </div>
                  </div>

                  <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                    <div class="form-group-enhanced">
                      <label for="dosage">
                        <i class="fas fa-weight me-2"></i>Dosage Strength
                      </label>
                      
                      <!-- Enhanced Dosage Form Dropdown -->
                      <div class="dosage-dropdown-container">
                        <select id="dosage" name="dosage" class="form-control form-control-enhanced dosage-dropdown" required="required" 
                                style="font-size: 1.5rem; padding: 2rem; min-height: 100px; line-height: 1.8; width: 100%;">
                          <option value="">Select Dosage Form</option>
                          <option value="tablet" <?php echo ($dosage == 'tablet') ? 'selected' : ''; ?>>💊 Tablet</option>
                          <option value="capsule" <?php echo ($dosage == 'capsule') ? 'selected' : ''; ?>>🔵 Capsule</option>
                          <option value="syrup" <?php echo ($dosage == 'syrup') ? 'selected' : ''; ?>>🧪 Syrup</option>
                          <option value="injection" <?php echo ($dosage == 'injection') ? 'selected' : ''; ?>>💉 Injection</option>
                          <option value="cream" <?php echo ($dosage == 'cream') ? 'selected' : ''; ?>>🧴 Cream</option>
                          <option value="drops" <?php echo ($dosage == 'drops') ? 'selected' : ''; ?>>💧 Drops</option>
                          <option value="powder" <?php echo ($dosage == 'powder') ? 'selected' : ''; ?>>🥄 Powder</option>
                          <option value="ointment" <?php echo ($dosage == 'ointment') ? 'selected' : ''; ?>>🧴 Ointment</option>
                          <option value="spray" <?php echo ($dosage == 'spray') ? 'selected' : ''; ?>>💨 Spray</option>
                          <option value="patch" <?php echo ($dosage == 'patch') ? 'selected' : ''; ?>>🩹 Patch</option>
                          <option value="suppository" <?php echo ($dosage == 'suppository') ? 'selected' : ''; ?>>💊 Suppository</option>
                          <option value="inhaler" <?php echo ($dosage == 'inhaler') ? 'selected' : ''; ?>>💨 Inhaler</option>
                        </select>
                        
                        <!-- Custom Dosage Input -->
                        <div class="mt-3">
                          <label class="form-label mb-2">
                            <i class="fas fa-edit me-2"></i>Or Enter Custom Dosage Form
                          </label>
                          <input type="text" id="custom_dosage" class="form-control form-control-enhanced custom-dosage-input" 
                                 placeholder="Enter custom dosage form (e.g., Extended Release Tablet, Enteric Coated Capsule)"/>
                          <div class="mt-2">
                            <small class="text-muted">
                              <i class="fas fa-info-circle me-1"></i>
                              Enter a custom dosage form if not listed above
                            </small>
                          </div>
                        </div>
                </div>
                
                      <small class="text-muted">Select the dosage form for this medicine</small>
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-section">
                <h5 class="mb-3"><i class="fas fa-warehouse me-2"></i>Inventory Information</h5>
                <div class="row">
                  <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                    <div class="form-group-enhanced">
                      <label for="expiry_date">
                        <i class="fas fa-calendar-times me-2"></i>Expiry Date
                      </label>
                      <input type="date" id="expiry_date" name="expiry_date" class="form-control form-control-enhanced" required="required" 
                             value="<?php echo htmlspecialchars($expiryDate);?>"/>
                      <div class="form-validation" id="expiry_validation"></div>
                    </div>
                </div>
                
                  <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                    <div class="form-group-enhanced">
                      <label for="quantity_stock">
                        <i class="fas fa-boxes me-2"></i>Quantity/Stock
                      </label>
                      <input type="number" id="quantity_stock" name="quantity_stock" class="form-control form-control-enhanced" required="required" 
                             value="<?php echo htmlspecialchars($quantityStock);?>" placeholder="Enter quantity or stock"/>
                      <small class="text-muted">Current stock quantity</small>
                    </div>
                  </div>
                </div>
                </div>

              <div class="row mt-4">
                <div class="col-12 text-right">
                  <button type="submit" id="submit" name="submit" class="btn btn-primary-enhanced btn-enhanced">
                    <i class="fas fa-save me-2"></i>Update Medicine Details
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
        <!-- /.card -->

      </section>



      <!-- /.content-wrapper -->
    </div>

    <?php // include './config/footer.php';

    $message = '';
    if(isset($_GET['message'])) {
      $message = $_GET['message'];
    }
    ?>  
    <!-- /.control-sidebar -->
  </div>
  <!-- ./wrapper -->

  <?php include './config/site_js_links.php'; ?>
  <?php include './config/data_tables_js.php'; ?>
  <script>
    showMenuSelected("#mnu_medicines", "#mi_medicine_details");

    var message = '<?php echo $message;?>';

    if(message !== '') {
      showCustomMessage(message);
    }

    // Form validation
    function validateUpdateForm() {
      let isValid = true;
      let errors = [];
      
      const medicine = $('#medicine').val();
      const packing = $('#packing').val().trim();
      const dosage = $('#dosage').val().trim();
      const expiryDate = $('#expiry_date').val();
      const quantityStock = $('#quantity_stock').val();
      
      if (!medicine) {
        errors.push('Please select a medicine');
        isValid = false;
      }
      
      if (!packing) {
        errors.push('Packing is required');
        isValid = false;
      }
      
      if (!dosage) {
        errors.push('Dosage is required');
        isValid = false;
      }
      
      if (!expiryDate) {
        errors.push('Expiry date is required');
        isValid = false;
      } else {
        // Check if expiry date is in the past
        const expiryTimestamp = new Date(expiryDate).getTime();
        const currentTimestamp = new Date().getTime();
        if (expiryTimestamp < currentTimestamp) {
          errors.push('Expiry date cannot be in the past');
          isValid = false;
        }
      }
      
      if (!quantityStock || parseInt(quantityStock) < 0) {
        errors.push('Valid quantity is required');
        isValid = false;
      }
      
      return { isValid, errors };
    }

    // Check expiry date in real-time
    function checkExpiryDate() {
      const expiryDate = $('#expiry_date').val();
      if (expiryDate) {
        const expiryTimestamp = new Date(expiryDate).getTime();
        const currentTimestamp = new Date().getTime();
        const daysUntilExpiry = (expiryTimestamp - currentTimestamp) / (1000 * 60 * 60 * 24);
        
        const $validation = $('#expiry_validation');
        
        if (daysUntilExpiry < 0) {
          $validation.html('<i class="fas fa-exclamation-triangle me-1"></i>Expiry date is in the past').addClass('validation-error');
          $('#expiry_date').addClass('is-invalid');
        } else if (daysUntilExpiry <= 30) {
          $validation.html('<i class="fas fa-exclamation-triangle me-1"></i>Medicine expires within 30 days').addClass('validation-error');
          $('#expiry_date').addClass('is-invalid');
        } else {
          $validation.html('<i class="fas fa-check-circle me-1"></i>Valid expiry date').addClass('validation-success');
          $('#expiry_date').removeClass('is-invalid').addClass('is-valid');
        }
      }
    }

    $(document).ready(function() {
      // Enhanced Medicine Selection
      $('.medicine-option').on('click', function() {
        $('.medicine-option').removeClass('selected');
        $(this).addClass('selected');
        
        const medicineId = $(this).data('medicine');
        
        if (medicineId === 'other') {
          $('#medicine_dropdown').slideDown(300);
          $('#medicine_select').focus();
        } else {
          $('#medicine_dropdown').slideUp(300);
          $('#medicine').val(medicineId);
        }
      });
      
      // Handle medicine selection from dropdown
      $('#medicine_select').on('change', function() {
        const selectedMedicine = $(this).val();
        $('#medicine').val(selectedMedicine);
        
        // Update the "Current Medicine" option to show selected medicine
        const selectedText = $(this).find('option:selected').text();
        $('.medicine-option[data-medicine="' + medicineId + '"] .medicine-details h6').text(selectedText);
      });
      
      // Initialize with current medicine selected
      $('.medicine-option[data-medicine="' + medicineId + '"]').addClass('selected');
      
      // Handle dosage dropdown selection
      $('#dosage').on('change', function() {
        const selectedValue = $(this).val();
        if (selectedValue) {
          // Clear custom input when dropdown option is selected
          $('#custom_dosage').val('');
        }
      });
      
      // Handle custom dosage input
      $('#custom_dosage').on('input', function() {
        const customValue = $(this).val().trim();
        if (customValue) {
          // Set the hidden field to custom value
          $('#dosage').val(customValue);
        }
      });
      
      // Initialize with current dosage
      const currentDosage = '<?php echo htmlspecialchars($dosage);?>';
      if (currentDosage) {
        // Check if current dosage matches any dropdown option
        const matchingOption = $('#dosage option[value="' + currentDosage.toLowerCase() + '"]');
        if (matchingOption.length > 0) {
          $('#dosage').val(currentDosage.toLowerCase());
        } else {
          // If current dosage doesn't match dropdown options, set custom input
          $('#custom_dosage').val(currentDosage);
          $('#dosage').val(currentDosage);
        }
      }

      // Form submission with validation
      $('#update_medicine_form').on('submit', function(e) {
        const validation = validateUpdateForm();
        
        if (!validation.isValid) {
          e.preventDefault();
          showCustomMessage(validation.errors.join('<br>'), 'error');
          return false;
        }
        
        // Show loading state
        $('#form_loading').css('display', 'flex');
        $('#submit').prop('disabled', true);
      });

      // Real-time expiry date checking
      $('#expiry_date').on('change', checkExpiryDate);

      // Real-time form validation
      $('input[required], select[required]').on('blur', function() {
        const $field = $(this);
        const value = $field.val().trim();
        const $container = $field.closest('.form-group-enhanced');
        let $feedback = $container.find('.form-validation');
        
        if ($feedback.length === 0 && $field.attr('id') !== 'expiry_date') {
          $feedback = $('<div class="form-validation"></div>');
          $container.append($feedback);
        }
        
        if (!value) {
          if ($field.attr('id') !== 'expiry_date') {
            $feedback.html('<i class="fas fa-exclamation-circle me-1"></i>This field is required').addClass('validation-error');
          }
          $field.addClass('is-invalid');
        } else {
          if ($field.attr('id') !== 'expiry_date') {
            $feedback.html('<i class="fas fa-check-circle me-1"></i>Valid').addClass('validation-success');
          }
          $field.removeClass('is-invalid').addClass('is-valid');
        }
      });

      // Initial expiry date check
      checkExpiryDate();
    });
  </script>
</body>
</html>