<?php 
include './config/connection.php';
include './common_service/common_functions.php';
$__ut = isset($_SESSION['user_type']) ? (int)$_SESSION['user_type'] : 0;
$isAdmin = ($__ut === 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <?php include './config/site_css_links.php';?>
 <?php include './config/data_tables_css.php';?>
 <title>Activity Log</title>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
  <div class="wrapper">
    <?php include './config/header.php'; include './config/sidebar.php';?>  
    <div class="content-wrapper">
     <h1>Activity Log</h1>
      <section class="content">
        <div class="card card-outline card-primary shadow">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="h6 mb-0">Recent Activity</div>
              <div class="input-group input-group-sm" style="max-width: 320px;">
                <input type="text" id="log_q" class="form-control" placeholder="Search actions, users, context" />
                <div class="input-group-append">
                  <button class="btn btn-primary" id="btnSearch">Search</button>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table table-bordered" id="tblLogs">
                <thead><tr><th>Date</th><th>User</th><th>Action</th><th>Context</th><th>IP</th><th>UA</th></tr></thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
  <?php include './config/site_js_links.php'; ?>
  <?php include './config/data_tables_js.php'; ?>
  <script>
    showMenuSelected('#mnu_activity_log','');
    
    function loadLogs(){
      const q = { q: document.getElementById('log_q').value };
      console.log('Loading activity logs with query:', q);
      
      // Show loading indicator
      const $tb = $('#tblLogs tbody');
      $tb.html('<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading activity logs...</td></tr>');
      
      // Add cache-busting parameter
      const timestamp = new Date().getTime();
      const url = 'ajax/get_activity_logs.php?t=' + timestamp;
      
      $.getJSON(url, q, function(res){
        console.log('Activity logs response:', res);
        $tb.empty();
        
        if(!(res && res.status==='success')) {
          console.error('Activity logs API error:', res);
          $tb.append('<tr><td colspan="6" class="text-center text-danger">Error loading activity logs: ' + (res.message || 'Unknown error') + '</td></tr>');
          return;
        }
        
        if (!res.data || res.data.length === 0) {
          $tb.append('<tr><td colspan="6" class="text-center text-muted">No activity logs found</td></tr>');
          return;
        }
        
        console.log('Processing', res.data.length, 'activity logs');
        
        // Clear any existing DataTable first
        if ($.fn.DataTable.isDataTable('#tblLogs')) { 
          $('#tblLogs').DataTable().clear().destroy(); 
        }
        
        // Build table rows
        const rows = [];
        (res.data||[]).forEach(function(r){
          let ctxHtml = '';
          if (r.context) {
            try {
              const ctx = JSON.parse(r.context);
              const parts = [];
              if (ctx.sale_id) {
                parts.push('sale_id: '+ctx.sale_id+' <a href="print_receipt.php?sale_id='+ctx.sale_id+'" target="_blank" class="btn btn-xs btn-outline-primary">Receipt</a>');
              }
              if (ctx.patient_id !== undefined) parts.push('patient_id: '+ctx.patient_id);
              if (ctx.items !== undefined) parts.push('items: '+ctx.items);
              if (ctx.total !== undefined) parts.push('total: '+ctx.total);
              ctxHtml = parts.length ? parts.join(' | ') : $('<div>').text(r.context).html();
            } catch(e) {
              ctxHtml = $('<div>').text(r.context).html();
            }
          }
          const ua = r.user_agent ? $('<div>').text(r.user_agent).html() : '';
          const row = '<tr>'
            + '<td>'+ (r.created_at||'') +'</td>'
            + '<td>'+ (r.user_name||('ID '+(r.user_id||''))) +'</td>'
            + '<td>'+ (r.action||'') +'</td>'
            + '<td>'+ ctxHtml +'</td>'
            + '<td>'+ (r.ip_address||'') +'</td>'
            + '<td><div class="small" style="white-space:normal; word-break:break-word;">'+ ua +'</div></td>'
            + '</tr>';
          rows.push(row);
        });
        
        // Add all rows at once
        $tb.html(rows.join(''));
        
        // Initialize DataTable with proper options
        setTimeout(function() {
          $('#tblLogs').DataTable({
            responsive: true, 
            lengthChange: false, 
            autoWidth: false,
            order: [[0,'desc']],
            dom: 'Bfrtip', 
            buttons: ['copy','csv','excel','pdf','print','colvis'],
            pageLength: 25,
            destroy: true, // Allow re-initialization
            language: {
              "search": "Search:",
              "lengthMenu": "Show _MENU_ entries",
              "info": "Showing _START_ to _END_ of _TOTAL_ entries",
              "infoEmpty": "Showing 0 to 0 of 0 entries",
              "infoFiltered": "(filtered from _MAX_ total entries)"
            }
          });
        }, 100);
        
      }).fail(function(xhr, status, error) {
        console.error('AJAX Error:', status, error);
        $('#tblLogs tbody').html('<tr><td colspan="6" class="text-center text-danger">Failed to load activity logs. Please refresh the page.</td></tr>');
      });
    }
    
    $('#btnSearch').on('click', loadLogs);
    $('#log_q').on('keypress', function(e) {
      if (e.which === 13) { // Enter key
        loadLogs();
      }
    });
    
    // Load logs on page ready
    $(document).ready(function() {
      loadLogs();
    });
  </script>
</body>
</html>

