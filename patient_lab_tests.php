<?php 
include './config/connection.php';
include './common_service/common_functions.php';

$patients = getPatients($con);
$labTests = getLabTests($con);

$message = '';
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
 <?php include './config/site_css_links.php';?>
 <?php include './config/data_tables_css.php';?>
  <title>Patient Lab Tests - Medical Care and Information system</title>

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
            <h1>Patient Lab Tests</h1>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">

      <!-- Default box -->
     <div class="card card-outline card-primary shadow">
        <div class="card-header">
          <h3 class="card-title">Manage Patient Lab Tests</h3>
          
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
              <label for="patient_id">Select Patient</label>
              <select id="patient_id" class="form-control form-control-sm">
                <?php echo $patients;?>
              </select>
            </div>

            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12 mb-3 d-flex align-items-end">
              <button type="button" id="search_patient_lab_tests" 
              class="btn btn-primary btn-sm btn-flat btn-block">Search</button>
            </div>
          </div>

          <div class="clearfix">&nbsp;</div>

          <div class="row">
            <div class="col-md-12 table-responsive">
              <table id="patient_lab_tests_table" class="table table-striped table-bordered">
                <thead>
                  <tr class="bg-gradient-primary text-light">
                    <th class="p-1 text-center">S.No</th>
                    <th class="p-1 text-center">Test Name</th>
                    <th class="p-1 text-center">Ordered Date</th>
                    <th class="p-1 text-center">Sample Collected</th>
                    <th class="p-1 text-center">Result Date</th>
                    <th class="p-1 text-center">Result Value</th>
                    <th class="p-1 text-center">Result Notes</th>
                    <th class="p-1 text-center">Status</th>
                    <th class="p-1 text-center">Ordered By</th>
                    <th class="p-1 text-center">Performed By</th>
                    <th class="p-1 text-center">Action</th>
                  </tr>
                </thead>

                <tbody id="patient_lab_tests_data">
                  
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="card card-outline card-primary shadow">
        <div class="card-header">
          <h3 class="card-title">Order New Lab Test for Patient</h3>
        </div>
        <div class="card-body">
          <form id="order_lab_test_form" method="post" action="ajax/save_patient_lab_test.php">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="patient_id_for_order" id="patient_id_for_order">
            <div class="row">
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <label for="lab_test_id">Lab Test</label>
                <select id="lab_test_id" name="lab_test_id" required="required" class="form-control form-control-sm">
                  <?php echo $labTests;?>
                </select>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <div class="form-group">
                  <label for="ordered_date_input">Ordered Date</label>
                    <div class="input-group date" 
                    id="ordered_date" 
                    data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#ordered_date" name="ordered_date" 
                        data-toggle="datetimepicker" autocomplete="off" required="required" id="ordered_date_input" placeholder="Select ordered date"/>
                        <div class="input-group-append" 
                        data-target="#ordered_date" 
                        data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                <label for="status">Status</label>
                <select id="status" name="status" required="required" class="form-control form-control-sm">
                  <?php echo getLabTestStatusOptions();?>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-right">
                <button type="submit" id="order_test_btn" 
                name="order_test_btn" class="btn btn-primary btn-sm btn-flat">Order Lab Test</button>
              </div>
            </div>
          </form>
        </div>
      </div>

    </section>
  </div>

  <?php include './config/site_js_links.php'; ?>
  <?php include './config/data_tables_js.php'; ?>
  <script src="plugins/moment/moment.min.js"></script>
  <script src="plugins/daterangepicker/daterangepicker.js"></script>
  <script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>

<script>
  showMenuSelected("#mnu_lab_diag", "#mi_patient_lab_tests");

  var message = '<?php echo $message;?>';

  if(message !== '') {
    showCustomMessage(message);
  }

  $(document).ready(function() {

    $('#ordered_date').datetimepicker({
        format: 'L'
    });

    $("#search_patient_lab_tests").click(function() {
      var patientId = $("#patient_id").val();
      if (patientId !== '') {
        $("#patient_id_for_order").val(patientId);
        $.ajax({
          url: "ajax/get_patient_lab_tests.php",
          type: 'GET', 
          data: {
            'patient_id': patientId
          },
          cache:false,
          async:false,
          success: function (data, status, xhr) {
              $("#patient_lab_tests_data").html(data);
              if ($.fn.DataTable.isDataTable('#patient_lab_tests_table')) {
                  $('#patient_lab_tests_table').DataTable().destroy();
              }
              $('#patient_lab_tests_table').DataTable({
                "responsive": true, "lengthChange": false, "autoWidth": false,
                "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
              }).buttons().container().appendTo('#patient_lab_tests_table_wrapper .col-md-6:eq(0)');
          },
          error: function (jqXhr, textStatus, errorMessage) {
            showCustomMessage(errorMessage);
          }
        });
      } else {
        $("#patient_id_for_order").val('');
        $("#patient_lab_tests_data").html('');
        if ($.fn.DataTable.isDataTable('#patient_lab_tests_table')) {
            $('#patient_lab_tests_table').DataTable().destroy();
        }
      }
    });

    // Initial load if patient_id is pre-selected (e.g., from a redirect)
    if ($("#patient_id").val() !== '') {
      $("#search_patient_lab_tests").click();
    }

  });
</script>
</body>
</html>
