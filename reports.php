<?php 
include './config/connection.php';
include './common_service/common_functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <?php include './config/site_css_links.php' ?>
 <?php include './config/data_tables_css.php' ?>
 <title>Reports - Medical Care and Information system</title>
 <style>
   .report-card {
     border: none;
     border-radius: 12px;
     box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
     transition: all 0.3s ease;
     margin-bottom: 2rem;
   }
   .report-card:hover {
     box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
     transform: translateY(-2px);
   }
   .report-card .card-header {
     background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
     color: white;
     border-radius: 12px 12px 0 0;
     border: none;
     padding: 1.5rem;
   }
   .report-card .card-header h3 {
     margin: 0;
     font-weight: 600;
     display: flex;
     align-items: center;
     gap: 0.5rem;
   }
   .report-card .card-body {
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
     border-color: #667eea;
     box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
     background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
     color: white;
   }
   .btn-primary-enhanced:hover {
     background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
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
     border-top: 4px solid #667eea;
     border-radius: 50%;
     animation: spin 1s linear infinite;
   }
   @keyframes spin {
     0% { transform: rotate(0deg); }
     100% { transform: rotate(360deg); }
   }
   .summary-card {
     background: linear-gradient(135deg, #10b981 0%, #059669 100%);
     color: white;
     border-radius: 8px;
     padding: 1.5rem;
     margin-top: 1rem;
   }
   .summary-card h5 {
     margin: 0 0 0.5rem 0;
     font-weight: 600;
   }
   .summary-card .summary-value {
     font-size: 1.5rem;
     font-weight: 700;
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
     color: #667eea;
   }
   .stat-card .stat-label {
     color: #6b7280;
     font-size: 0.9rem;
     margin-top: 0.25rem;
   }
   .no-data {
     text-align: center;
     padding: 3rem;
     color: #6b7280;
   }
   .no-data i {
     font-size: 3rem;
     margin-bottom: 1rem;
     opacity: 0.5;
   }
   @media (max-width: 768px) {
     .report-card .card-body {
       padding: 1rem;
     }
     .stats-grid {
       grid-template-columns: 1fr;
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
    <h1>reports</h1>

    <!-- Main content -->
    <section class="content">

      <!-- Visits Between Dates -->
      <div class="card report-card">
        <div class="card-header">
          <div class="d-flex align-items-center">
            <div class="icon-wrapper">
              <i class="fas fa-calendar-alt"></i>
            </div>
            <div>
              <h3 class="card-title">Patient Visits Report</h3>
              <small class="opacity-75">View patient visits within a specific date range</small>
            </div>
          </div>
          <div class="card-tools">
            <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body position-relative">
          <div class="loading-overlay" id="visits_loading">
            <div class="spinner"></div>
          </div>
          <div class="row">
            <div class="col-lg-3 col-md-4 col-sm-6">
              <div class="form-group-enhanced">
                <label for="visits_from" class="form-label">
                  <i class="fas fa-calendar-day me-2"></i>From Date
                </label>
                <input type="date" id="visits_from" class="form-control form-control-enhanced" />
              </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6">
              <div class="form-group-enhanced">
                <label for="visits_to" class="form-label">
                  <i class="fas fa-calendar-day me-2"></i>To Date
                </label>
                <input type="date" id="visits_to" class="form-control form-control-enhanced" />
              </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6">
              <div class="form-group-enhanced">
                <label class="form-label">&nbsp;</label>
                <button id="load_visits" class="btn btn-primary-enhanced btn-enhanced w-100">
                  <i class="fas fa-search me-2"></i>Generate Report
                </button>
              </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6">
              <div class="form-group-enhanced">
                <label class="form-label">&nbsp;</label>
                <button id="export_visits" class="btn btn-outline-primary btn-enhanced w-100">
                  <i class="fas fa-download me-2"></i>Export Data
                </button>
              </div>
            </div>
          </div>
          <div class="table-responsive mt-4">
            <table class="table table-enhanced" id="tbl_visits">
              <thead>
                <tr>
                  <th><i class="fas fa-calendar me-2"></i>Visit Date</th>
                  <th><i class="fas fa-user me-2"></i>Patient</th>
                  <th><i class="fas fa-map-marker-alt me-2"></i>Address</th>
                  <th><i class="fas fa-phone me-2"></i>Phone</th>
                  <th><i class="fas fa-stethoscope me-2"></i>Disease</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div id="visits_summary" class="summary-card" style="display: none;">
            <h5><i class="fas fa-chart-bar me-2"></i>Summary</h5>
            <div class="row">
              <div class="col-md-6">
                <div class="summary-value" id="visits_count">0</div>
                <small>Total Visits</small>
              </div>
              <div class="col-md-6">
                <div class="summary-value" id="visits_period">-</div>
                <small>Date Range</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Disease-based Visits -->
      <div class="card report-card">
        <div class="card-header">
          <div class="d-flex align-items-center">
            <div class="icon-wrapper">
              <i class="fas fa-virus"></i>
            </div>
            <div>
              <h3 class="card-title">Disease Analysis Report</h3>
              <small class="opacity-75">Filter patient visits by specific disease or condition</small>
            </div>
          </div>
          <div class="card-tools">
            <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body position-relative">
          <div class="loading-overlay" id="disease_loading">
            <div class="spinner"></div>
          </div>
          <div class="row">
            <div class="col-lg-3 col-md-4 col-sm-6">
              <div class="form-group-enhanced">
                <label for="disease_from" class="form-label">
                  <i class="fas fa-calendar-day me-2"></i>From Date
                </label>
                <input type="date" id="disease_from" class="form-control form-control-enhanced" />
              </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6">
              <div class="form-group-enhanced">
                <label for="disease_to" class="form-label">
                  <i class="fas fa-calendar-day me-2"></i>To Date
                </label>
                <input type="date" id="disease_to" class="form-control form-control-enhanced" />
              </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6">
              <div class="form-group-enhanced">
                <label for="disease_name" class="form-label">
                  <i class="fas fa-stethoscope me-2"></i>Disease/Condition
                </label>
                <input type="text" id="disease_name" class="form-control form-control-enhanced" placeholder="e.g., Flu, Diabetes, Hypertension" />
              </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6">
              <div class="form-group-enhanced">
                <label class="form-label">&nbsp;</label>
                <button id="load_disease" class="btn btn-primary-enhanced btn-enhanced w-100">
                  <i class="fas fa-search me-2"></i>Search Disease
                </button>
              </div>
            </div>
          </div>
          <div class="table-responsive mt-4">
            <table class="table table-enhanced" id="tbl_disease">
              <thead>
                <tr>
                  <th><i class="fas fa-calendar me-2"></i>Visit Date</th>
                  <th><i class="fas fa-user me-2"></i>Patient</th>
                  <th><i class="fas fa-map-marker-alt me-2"></i>Address</th>
                  <th><i class="fas fa-phone me-2"></i>Phone</th>
                  <th><i class="fas fa-stethoscope me-2"></i>Disease</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div id="disease_summary" class="summary-card" style="display: none;">
            <h5><i class="fas fa-chart-line me-2"></i>Disease Analysis</h5>
            <div class="row">
              <div class="col-md-4">
                <div class="summary-value" id="disease_count">0</div>
                <small>Cases Found</small>
              </div>
              <div class="col-md-4">
                <div class="summary-value" id="disease_name_display">-</div>
                <small>Disease Searched</small>
              </div>
              <div class="col-md-4">
                <div class="summary-value" id="disease_period">-</div>
                <small>Date Range</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- POS Sales (if available) -->
      <div class="card report-card">
        <div class="card-header">
          <div class="d-flex align-items-center">
            <div class="icon-wrapper">
              <i class="fas fa-cash-register"></i>
            </div>
            <div>
              <h3 class="card-title">Sales & Revenue Report</h3>
              <small class="opacity-75">Track POS sales transactions and revenue analytics</small>
            </div>
          </div>
          <div class="card-tools">
            <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body position-relative">
          <div class="loading-overlay" id="sales_loading">
            <div class="spinner"></div>
          </div>
          <div class="row">
            <div class="col-lg-3 col-md-4 col-sm-6">
              <div class="form-group-enhanced">
                <label for="sales_from" class="form-label">
                  <i class="fas fa-calendar-day me-2"></i>From Date
                </label>
                <input type="date" id="sales_from" class="form-control form-control-enhanced" />
              </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6">
              <div class="form-group-enhanced">
                <label for="sales_to" class="form-label">
                  <i class="fas fa-calendar-day me-2"></i>To Date
                </label>
                <input type="date" id="sales_to" class="form-control form-control-enhanced" />
              </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6">
              <div class="form-group-enhanced">
                <label class="form-label">&nbsp;</label>
                <button id="load_sales" class="btn btn-primary-enhanced btn-enhanced w-100">
                  <i class="fas fa-chart-line me-2"></i>Generate Report
                </button>
              </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6">
              <div class="form-group-enhanced">
                <label class="form-label">&nbsp;</label>
                <button id="export_sales" class="btn btn-outline-primary btn-enhanced w-100">
                  <i class="fas fa-download me-2"></i>Export Data
                </button>
              </div>
            </div>
          </div>
          <div class="table-responsive mt-4">
            <table class="table table-enhanced" id="tbl_sales">
              <thead>
                <tr>
                  <th><i class="fas fa-hashtag me-2"></i>Sale #</th>
                  <th><i class="fas fa-calendar me-2"></i>Date</th>
                  <th><i class="fas fa-user me-2"></i>Patient</th>
                  <th class="text-right"><i class="fas fa-dollar-sign me-2"></i>Total</th>
                  <th><i class="fas fa-credit-card me-2"></i>Payment</th>
                  <th><i class="fas fa-receipt me-2"></i>Receipt</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div id="sales_summary" class="summary-card" style="display: none;">
            <h5><i class="fas fa-chart-pie me-2"></i>Sales Summary</h5>
            <div class="row">
              <div class="col-md-4">
                <div class="summary-value" id="txtSalesTotal">₱0.00</div>
                <small>Total Revenue</small>
              </div>
              <div class="col-md-4">
                <div class="summary-value" id="txtSalesCount">0</div>
                <small>Total Transactions</small>
              </div>
              <div class="col-md-4">
                <div class="summary-value" id="avg_sales">₱0.00</div>
                <small>Average Sale</small>
              </div>
            </div>
          </div>
        </div>
      </div>

    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Footer removed - not needed -->
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<?php include './config/site_js_links.php' ?>
<?php include './config/data_tables_js.php' ?>
<script>
  showMenuSelected("#mnu_reports", "#mi_reports");

  function formatDateForParam(d){
    if(!d) return '';
    // input: yyyy-mm-dd -> mm/dd/yyyy (backend expects mm/dd/yyyy based on converters)
    var p = d.split('-');
    if(p.length !== 3) return '';
    return p[1] + '/' + p[2] + '/' + p[0];
  }

  function initDt(id){
    if ($.fn.DataTable.isDataTable(id)) {
      $(id).DataTable().destroy();
    }
    return $(id).DataTable({
      responsive: true,
      autoWidth: false,
      pageLength: 25,
      order: [],
      dom: 'Bfrtip',
      buttons: [
        { extend: 'copyHtml5', exportOptions: { columns: ':visible' } },
        { extend: 'csvHtml5', exportOptions: { columns: ':visible' } },
        { extend: 'excelHtml5', exportOptions: { columns: ':visible' } },
        { extend: 'pdfHtml5', exportOptions: { columns: ':visible' } },
        { extend: 'print', exportOptions: { columns: ':visible' } },
        { extend: 'colvis' }
      ]
    });
  }

  function loadVisits(){
    const f = formatDateForParam($('#visits_from').val());
    const t = formatDateForParam($('#visits_to').val());
    if(!f || !t){ 
      showCustomMessage('Please select both from and to dates', 'warning'); 
      return; 
    }
    
    // Show loading state
    $('#visits_loading').css('display', 'flex');
    
    $.getJSON('ajax/report_visits.php', { from: f, to: t }, function(res){
      $('#visits_loading').hide();
      
      if(res.status !== 'success'){ 
        showCustomMessage(res.message || 'Error loading visits data', 'error'); 
        return; 
      }
      
      const dt = initDt('#tbl_visits');
      dt.clear();
      
      if(res.data && res.data.length > 0) {
        (res.data || []).forEach(r => {
          dt.row.add([
            r.visit_date,
            r.patient_name,
            r.address,
            r.phone_number,
            r.disease
          ]);
        });
        dt.draw();
        
        // Show summary
        $('#visits_count').text(res.data.length);
        $('#visits_period').text($('#visits_from').val() + ' to ' + $('#visits_to').val());
        $('#visits_summary').show();
        
        showCustomMessage(`Found ${res.data.length} patient visits`, 'success');
      } else {
        // Show no data message
        dt.row.add(['No data found', '', '', '', '']);
        dt.draw();
        $('#visits_summary').hide();
        showCustomMessage('No patient visits found for the selected date range', 'info');
      }
    }).fail(function() {
      $('#visits_loading').hide();
      showCustomMessage('Failed to load visits data. Please try again.', 'error');
    });
  }

  function loadDisease(){
    const f = formatDateForParam($('#disease_from').val());
    const t = formatDateForParam($('#disease_to').val());
    const d = $('#disease_name').val().trim();
    if(!f || !t || !d){ 
      showCustomMessage('Please provide from date, to date, and disease name', 'warning'); 
      return; 
    }
    
    // Show loading state
    $('#disease_loading').css('display', 'flex');
    
    $.getJSON('ajax/report_diseases.php', { from: f, to: t, disease: d }, function(res){
      $('#disease_loading').hide();
      
      if(res.status !== 'success'){ 
        showCustomMessage(res.message || 'Error loading disease data', 'error'); 
        return; 
      }
      
      const dt = initDt('#tbl_disease');
      dt.clear();
      
      if(res.data && res.data.length > 0) {
        (res.data || []).forEach(r => {
          dt.row.add([
            r.visit_date,
            r.patient_name,
            r.address,
            r.phone_number,
            r.disease
          ]);
        });
        dt.draw();
        
        // Show summary
        $('#disease_count').text(res.data.length);
        $('#disease_name_display').text(d);
        $('#disease_period').text($('#disease_from').val() + ' to ' + $('#disease_to').val());
        $('#disease_summary').show();
        
        showCustomMessage(`Found ${res.data.length} cases of "${d}"`, 'success');
      } else {
        // Show no data message
        dt.row.add(['No data found', '', '', '', '']);
        dt.draw();
        $('#disease_summary').hide();
        showCustomMessage(`No cases found for "${d}" in the selected date range`, 'info');
      }
    }).fail(function() {
      $('#disease_loading').hide();
      showCustomMessage('Failed to load disease data. Please try again.', 'error');
    });
  }

  function loadSales(){
    const f = formatDateForParam($('#sales_from').val());
    const t = formatDateForParam($('#sales_to').val());
    if(!f || !t){ 
      showCustomMessage('Please select both from and to dates', 'warning'); 
      return; 
    }
    
    // Show loading state
    $('#sales_loading').css('display', 'flex');
    
    $.getJSON('ajax/report_sales.php', { from: f, to: t }, function(res){
      $('#sales_loading').hide();
      
      if(res.status !== 'success'){ 
        showCustomMessage(res.message || 'Error loading sales data', 'error'); 
        return; 
      }
      
      const dt = initDt('#tbl_sales');
      dt.clear();
      
      if(res.data && res.data.length > 0) {
        (res.data || []).forEach(r => {
          const link = '<a href="print_receipt.php?sale_id=' + r.sale_id + '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-receipt me-1"></i>Receipt</a>';
          dt.row.add([
            r.sale_id,
            r.sale_date,
            (r.patient_name || 'Walk-in'),
            '₱' + parseFloat(r.total_amount || 0).toFixed(2),
            r.payment_method || '',
            link
          ]);
        });
        dt.draw();
        
        // Show summary
        if (res.summary) {
          const total = parseFloat(res.summary.total_amount||0);
          const count = parseInt(res.summary.num_sales||0,10);
          const average = count > 0 ? total / count : 0;
          
          $('#txtSalesTotal').text('₱' + total.toFixed(2));
          $('#txtSalesCount').text(count);
          $('#avg_sales').text('₱' + average.toFixed(2));
          $('#sales_summary').show();
          
          showCustomMessage(`Found ${count} sales transactions totaling ₱${total.toFixed(2)}`, 'success');
        }
      } else {
        // Show no data message
        dt.row.add(['No data found', '', '', '', '', '']);
        dt.draw();
        $('#sales_summary').hide();
        showCustomMessage('No sales transactions found for the selected date range', 'info');
      }
    }).fail(function() {
      $('#sales_loading').hide();
      showCustomMessage('Failed to load sales data. Please try again.', 'error');
    });
  }

  // Export functions
  function exportVisits() {
    if ($.fn.DataTable.isDataTable('#tbl_visits')) {
      $('#tbl_visits').DataTable().button('.buttons-csv').trigger();
    } else {
      showCustomMessage('Please generate a report first', 'warning');
    }
  }

  function exportSales() {
    if ($.fn.DataTable.isDataTable('#tbl_sales')) {
      $('#tbl_sales').DataTable().button('.buttons-csv').trigger();
    } else {
      showCustomMessage('Please generate a report first', 'warning');
    }
  }

  $(document).ready(function(){
    // defaults: last 30 days
    const today = new Date();
    const past = new Date(); past.setDate(today.getDate() - 30);
    function toInput(d){
      const mm = (d.getMonth()+1).toString().padStart(2,'0');
      const dd = d.getDate().toString().padStart(2,'0');
      return d.getFullYear() + '-' + mm + '-' + dd;
    }
    $('#visits_from').val(toInput(past));
    $('#visits_to').val(toInput(today));
    $('#disease_from').val(toInput(past));
    $('#disease_to').val(toInput(today));
    $('#sales_from').val(toInput(past));
    $('#sales_to').val(toInput(today));

    // Event handlers
    $('#load_visits').on('click', loadVisits);
    $('#load_disease').on('click', loadDisease);
    $('#load_sales').on('click', loadSales);
    $('#export_visits').on('click', exportVisits);
    $('#export_sales').on('click', exportSales);

    // Enter key support for disease search
    $('#disease_name').on('keypress', function(e) {
      if (e.which === 13) { // Enter key
        loadDisease();
      }
    });

    // initial loads
    loadVisits();
    loadSales();
  });
</script>
</body>
</html>


