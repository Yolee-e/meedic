<?php
include './config/connection.php';
include './common_service/common_functions.php';

 $message = '';
if(isset($_POST['save_medicine'])) {
    csrf_validate_post();
    $medicineName = trim($_POST['medicine_name']);
    $medicineName = ucwords(strtolower($medicineName));
   
    $packing = trim($_POST['packing']);
    $dosage = trim($_POST['dosage']);
    $expiryDate = trim($_POST['expiry_date']);
    $quantityStock = trim($_POST['quantity_stock']);

   $id = $_POST['hidden_id'];
    if($medicineName !== '' && $packing !== '' && $dosage !== '' && $expiryDate !== '' && $quantityStock !== '') {
      
        $query = "UPDATE `medicines` SET `medicine_name` = :mname WHERE `id` = :id";   
        
        $queryDetails = "UPDATE `medicine_details` 
        SET `packing` = :pack, 
            `dosage` = :dos, 
            `expiry_date` = :exp, 
            `quantity_stock` = :qty 
        WHERE `medicine_id` = :id";

    try{
    	$con->beginTransaction();

    	$stmtMedicine = $con->prepare($query);
	    $stmtMedicine->bindParam(':mname', $medicineName);
	    $stmtMedicine->bindParam(':id', $id, PDO::PARAM_INT);
	    $stmtMedicine->execute();
	    
	    $stmtDetails = $con->prepare($queryDetails);
	    $stmtDetails->bindParam(':pack', $packing);
	    $stmtDetails->bindParam(':dos', $dosage);
	    $stmtDetails->bindParam(':exp', $expiryDate);
	    $stmtDetails->bindParam(':qty', $quantityStock, PDO::PARAM_INT);
	    $stmtDetails->bindParam(':id', $id, PDO::PARAM_INT);
	    $stmtDetails->execute();

	   $con->commit();
       
	   $message = "Record updated sucessfully.";

    }catch(PDOException $ex){
    	$con->rollback();
	    echo $ex->getMessage();
	    echo $ex->getTraceAsString();
        exit;
    }

}
header("Location:congratulation.php?goto_page=medicines.php&message=$message");
exit;
}

try {

 $id = $_GET['id'];
	$query = "SELECT m.`id`, m.`medicine_name`, md.`packing`, md.`dosage`, md.`expiry_date`, md.`quantity_stock` 
	          FROM `medicines` AS m
	          JOIN `medicine_details` AS md ON m.`id` = md.`medicine_id`
	          WHERE m.`id` = $id";
	$stmt = $con->prepare($query);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

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
 <title>Update Medicine - Medical Care and Information system</title>

 <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
 <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
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
            <h1>Update Medicine</h1>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">

      <!-- Default box -->
      <div class="card card-outline card-primary shadow">
        <div class="card-header">
          <h3 class="card-title">Update Medicine Details</h3>

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
              <input type="hidden" name="hidden_id" 
              id="hidden_id" value="<?php echo (int)$id;?>" />

          		<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                    <label for="medicine_name">Medicine Name</label>
          			<input type="text" id="medicine_name" name="medicine_name" required="required"
          			class="form-control form-control-sm" value="<?php echo htmlspecialchars($row['medicine_name']);?>" placeholder="Enter medicine name"/>
          		</div>
          		<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                    <label for="packing">Packing</label>
                    <input type="text" id="packing" name="packing" required="required"
                    class="form-control form-control-sm" value="<?php echo htmlspecialchars($row['packing']);?>" placeholder="Enter packing e.g 100ml, 10 kapsul"/>
          		</div>
          		<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                    <label for="dosage">Dosage</label>
                    <input type="text" id="dosage" name="dosage" required="required"
                    class="form-control form-control-sm" value="<?php echo htmlspecialchars($row['dosage']);?>" placeholder="Enter dosage e.g 250mg"/>
          		</div>
          		<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                    <label for="expiry_date">Expiry Date</label>
                    <input type="date" id="expiry_date" name="expiry_date" required="required"
                    class="form-control form-control-sm" value="<?php echo htmlspecialchars($row['expiry_date']);?>"/>
          		</div>
          		<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                    <label for="quantity_stock">Quantity/Stock</label>
                    <input type="number" id="quantity_stock" name="quantity_stock" required="required"
                    class="form-control form-control-sm" value="<?php echo htmlspecialchars($row['quantity_stock']);?>" placeholder="Enter quantity or stock"/>
          		</div>
          		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-right">
          			<button type="submit" id="save_medicine" 
          			name="save_medicine" class="btn btn-primary btn-sm btn-flat">Update Medicine</button>
          		</div>
          	</div>
          </form>
        </div>
    
        
      </div>
    </section>	
  </div>
  <!-- /.content-wrapper -->
<?php 
 include './config/site_js_links.php'; ?>

</body>
</html>

