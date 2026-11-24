<?php 
include './config/connection.php';
include './common_service/common_functions.php';

$message = '';




//$medicines = getMedicines($con);

$query = "select `m`.`medicine_name`, `m`.`dosage_strength`, `m`.`dosage_form`,
`md`.`id`, `md`.`packing`,  `md`.`medicine_id`, `md`.`expiry_date`, `md`.`quantity_stock`, `md`.`price` 
from `medicines` as `m`, 
`medicine_details` as `md` 
where `m`.`id` = `md`.`medicine_id` 
order by `m`.`id` asc, `md`.`id` asc;";

 try {
  
    $stmtDetails = $con->prepare($query);
    $stmtDetails->execute();

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
 <title>Medicine Details - Medical Care and Information system</title>
 
 <style>
   /* Enhanced UI Styles for Medicine Details */
   .medicine-details-card {
     border: none;
     border-radius: 12px;
     box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
     transition: all 0.3s ease;
     margin-bottom: 2rem;
   }
   .medicine-details-card:hover {
     box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
     transform: translateY(-2px);
   }
   .medicine-details-card .card-header {
     background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
     color: white;
     border-radius: 12px 12px 0 0;
     border: none;
     padding: 1.5rem;
   }
   .medicine-details-card .card-header h3 {
     margin: 0;
     font-weight: 600;
     display: flex;
     align-items: center;
     gap: 0.5rem;
   }
   .medicine-details-card .card-body {
     padding: 2rem;
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
     border-top: 4px solid #8b5cf6;
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
   .medicine-icon {
     font-size: 1.2rem;
     margin-right: 0.5rem;
   }
   .price-display {
     font-weight: 700;
     color: #8b5cf6;
   }
   .expiry-warning {
     color: #f59e0b;
     font-weight: 600;
   }
   .expiry-danger {
     color: #ef4444;
     font-weight: 600;
   }
   .stock-cell {
     position: relative;
   }
   .stock-updating {
     opacity: 0.6;
   }
   .stock-updated {
     background-color: #dbeafe;
     border-radius: 4px;
     transition: background-color 1s ease;
   }
   .stats-grid {
     display: grid;
     grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
     gap: 1rem;
     margin-bottom: 2rem;
   }
   .stat-card {
     background: white;
     border-radius: 8px;
     padding: 1.5rem;
     box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
     text-align: center;
     transition: transform 0.3s ease;
   }
   .stat-card:hover {
     transform: translateY(-2px);
   }
   .stat-card .stat-icon {
     font-size: 2rem;
     margin-bottom: 0.5rem;
   }
   .stat-card .stat-value {
     font-size: 1.5rem;
     font-weight: 700;
     color: #8b5cf6;
   }
   .stat-card .stat-label {
     color: #6b7280;
     font-size: 0.9rem;
     margin-top: 0.25rem;
   }
   .live-indicator {
     display: inline-flex;
     align-items: center;
     gap: 0.25rem;
     font-size: 0.75rem;
     color: #10b981;
     font-weight: 600;
   }
   .live-dot {
     width: 8px;
     height: 8px;
     background: #10b981;
     border-radius: 50%;
     animation: pulse 2s infinite;
   }
   @keyframes pulse {
     0% { opacity: 1; }
     50% { opacity: 0.5; }
     100% { opacity: 1; }
   }
   @media (max-width: 768px) {
     .medicine-details-card .card-body {
       padding: 1rem;
     }
     .stats-grid {
       grid-template-columns: 1fr;
     }
     .action-buttons {
       flex-direction: column;
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
              <h1></h1>
            </div>
          </div>
        </div><!-- /.container-fluid -->
      </section>

      <!-- Main content -->
      
  <section class="content">
      <!-- Default box -->
      <div class="card medicine-details-card">
        <div class="card-header">
          <div class="d-flex align-items-center">
            <div class="icon-wrapper">
              <i class="fas fa-list-ul"></i>
            </div>
            <div>
              <h3 class="card-title">Medicine Details Inventory</h3>
              <small class="opacity-75">Detailed view of all medicine variants with live stock updates</small>
            </div>
          </div>
          <div class="card-tools">
            <div class="live-indicator">
              <div class="live-dot"></div>
              <span>Live Stock Updates</span>
            </div>
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
            <table id="medicine_details" class="table table-enhanced" role="grid">
              <thead>
                <tr>
                  <th><i class="fas fa-hashtag me-2"></i>#</th>
                  <th><i class="fas fa-pills me-2"></i>Medicine Name</th>
                  <th><i class="fas fa-box me-2"></i>Packing</th>
                  <th><i class="fas fa-weight me-2"></i>Dosage</th>
                  <th><i class="fas fa-calendar-times me-2"></i>Expiry Date</th>
                  <th><i class="fas fa-boxes me-2"></i>Stock</th>
                  <th><i class="fas fa-dollar-sign me-2"></i>Price</th>
                  <th><i class="fas fa-cogs me-2"></i>Actions</th>
                </tr>
              </thead>

              <tbody>
                <?php 
                $serial = 0;
                while($row = $stmtDetails->fetch(PDO::FETCH_ASSOC)){
                  $serial++;
                  $stock = (int)$row['quantity_stock'];
                  $price = (float)$row['price'];
                  $expiryDate = $row['expiry_date'];
                  
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
                  <td class="text-center"><?php echo $serial; ?></td>
                  
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
                  
                  <td>
                    <span class="badge bg-light text-dark">
                      <?php echo htmlspecialchars($row['packing']);?>
                    </span>
                  </td>
                  
                  <td>
                    <span class="text-muted"><?php echo htmlspecialchars($row['dosage_strength'] ?: '-');?></span>
                  </td>
                  
                  <td>
                    <span class="<?php echo $expiryClass; ?>">
                      <?php echo $expiryIcon; ?> <?php echo date('M d, Y', strtotime($expiryDate)); ?>
                    </span>
                  </td>
                  
                  <td>
                    <div class="d-flex align-items-center">
                      <span class="stock-indicator <?php echo $stockClass; ?>">
                        <?php echo $stockText; ?>
                      </span>
                      <small class="ms-2 text-muted">(<?php echo $stock; ?>)</small>
                    </div>
                  </td>
                  
                  <td>
                    <span class="price-display">₱<?php echo number_format($price, 2);?></span>
                  </td>
                  
                  <td>
                    <div class="action-buttons">
                      <a href="update_medicine_details.php?medicine_id=<?php echo $row['medicine_id'];?>&medicine_detail_id=<?php echo $row['id'];?>&packing=<?php echo $row['packing'];?>&dosage_strength=<?php echo $row['dosage_strength'];?>&expiry_date=<?php echo $row['expiry_date'];?>&quantity_stock=<?php echo $row['quantity_stock'];?>" 
                         class="btn btn-primary btn-action" title="Edit Details">
                        <i class="fas fa-edit"></i>
                      </a>
                      <button type="button" class="btn btn-danger btn-action" title="Delete Details"
                              onclick="deleteMedicineDetail(<?php echo $row['id'];?>)">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      
    </section>
  <!-- /.content-wrapper -->
 </div>

  <?php
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
  $(function () {
    // Check if DataTable is already initialized to prevent reinitialization
    if (!$.fn.DataTable.isDataTable('#medicine_details')) {
      $("#medicine_details").DataTable({
        "responsive": true, "lengthChange": false, "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
      }).buttons().container().appendTo('#medicine_details_wrapper .col-md-6:eq(0)');
    }
  });

  function deleteMedicineDetail(id) {
    if (confirm("Are you sure you want to delete this medicine detail?\n\nThis action cannot be undone.")) {
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
        url: "ajax/delete_medicine_detail.php",
        type: "POST",
        data: {'id': id, 'admin_password': adminPwd, 'csrf_token': '<?php echo $_SESSION['csrf_token']; ?>'},
        success: function(response) {
          $('#table_loading').hide();
          if (response === "success") {
            showCustomMessage("Medicine detail deleted successfully.", 'success');
            setTimeout(() => location.reload(), 1500);
          } else {
            showCustomMessage("Error deleting medicine detail: " + response, 'error');
          }
        },
        error: function(jqXhr, textStatus, errorMessage) {
          $('#table_loading').hide();
          showCustomMessage('Failed to delete medicine detail. Please try again.', 'error');
        }
      });
    }
  }

  // Enhanced live stock polling for details page
  function pollStock() {
    var ids = [];
    $("#medicine_details .stock-cell").each(function(){ 
      ids.push(parseInt($(this).data('mdid'))); 
    });
    if (ids.length === 0) { return; }
    
    // Add updating indicator
    $("#medicine_details .stock-cell").addClass('stock-updating');
    
    $.ajax({
      url: 'ajax/pos_actions.php',
      type: 'POST',
      data: { action: 'get_stock_levels', ids: JSON.stringify(ids) },
      success: function(resp){
        try { 
          var res = JSON.parse(resp); 
        } catch(e) { 
          $("#medicine_details .stock-cell").removeClass('stock-updating');
          return; 
        }
        if (res.status === 'success') {
          var map = res.data || {};
          $("#medicine_details .stock-cell").each(function(){
            var $cell = $(this);
            var id = parseInt($cell.data('mdid'));
            if (map.hasOwnProperty(id)) {
              var newStock = map[id];
              var oldStock = parseInt($cell.text());
              
              // Update the stock number
              $cell.text(newStock);
              
              // Update stock indicator if it exists
              var $row = $cell.closest('tr');
              var $indicator = $row.find('.stock-indicator');
              if ($indicator.length > 0) {
                updateStockIndicator($indicator, newStock);
              }
              
              // Show update animation if stock changed
              if (newStock !== oldStock) {
                $cell.addClass('stock-updated');
                setTimeout(() => $cell.removeClass('stock-updated'), 1000);
              }
            }
          });
        }
        $("#medicine_details .stock-cell").removeClass('stock-updating');
      },
      error: function() {
        $("#medicine_details .stock-cell").removeClass('stock-updating');
      }
    });
  }

  // Update stock indicator based on stock level
  function updateStockIndicator($indicator, stock) {
    $indicator.removeClass('stock-high stock-medium stock-low stock-out');
    
    if (stock == 0) {
      $indicator.addClass('stock-out').text('Out of Stock');
    } else if (stock <= 10) {
      $indicator.addClass('stock-low').text('Low Stock');
    } else if (stock <= 50) {
      $indicator.addClass('stock-medium').text('Medium Stock');
    } else {
      $indicator.addClass('stock-high').text('In Stock');
    }
  }

  // Start polling every 5 seconds
  setInterval(pollStock, 5000);
  
  // Initial poll after page load
  $(document).ready(function() {
    setTimeout(pollStock, 1000);
  });
</script>
</body>
</html>
</html>