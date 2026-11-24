<?php 
include './config/connection.php';
include './common_service/common_functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <?php include './config/site_css_links.php';?>
 <?php include './config/data_tables_css.php';?>
 <title>Notifications</title>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
  <div class="wrapper">
    <?php include './config/header.php';
include './config/sidebar.php';?>  
    <div class="content-wrapper">
      <?php echo getContentHeader(''); ?>

      <section class="content">
        <div class="card card-outline card-primary shadow">
          <div class="card-header">
            <form id="frmFilters" class="form-inline">
              <label class="mr-2">Audience</label>
              <select class="form-control form-control-sm mr-3" name="audience" id="audience">
                <option value="">All</option>
                <option value="front_desk">Front Desk</option>
                <option value="doctor">Doctor</option>
                <option value="admin">Admin</option>
              </select>
              <label class="mr-2">Status</label>
              <select class="form-control form-control-sm mr-3" name="resolved" id="resolved">
                <option value="">All</option>
                <option value="0">Unread</option>
                <option value="1">Resolved</option>
              </select>
              <button type="submit" class="btn btn-sm btn-primary">Apply</button>
              <button type="button" id="btnCheckMedicineAlerts" class="btn btn-sm btn-warning ml-2" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); border: none; box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3); font-weight: 600; border-radius: 6px; transition: all 0.3s ease;">
                <i class="fas fa-pills mr-1"></i> Check Medicine Alerts
              </button>
            </form>
          </div>
          <div class="card-body">
            <table class="table table-bordered table-striped" id="tblNotifications">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Audience</th>
                  <th>Type</th>
                  <th>Message</th>
                  <th>Created</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </section>
    </div>
  </div>
  <?php include './config/site_js_links.php'; ?>
  <?php include './config/data_tables_js.php'; ?>
  <script>
    showMenuSelected('#mnu_notifications','');
    let dt;
    
    function loadData(){
      const qs = $('#frmFilters').serialize();
      $.getJSON('ajax/get_notifications.php?' + qs, function(res){
        if(!(res && res.status==='success')){ alert('Failed to load'); return; }
        const rows = res.data||[];
        const $tb = $('#tblNotifications tbody');
        $tb.empty();
        rows.forEach((r,i)=>{
          const msg = $('<div>').text(r.message||'').html();
          const tr = $('<tr>');
          tr.append('<td>'+(i+1)+'</td>');
          tr.append('<td>'+ (r.audience||'') +'</td>');
          tr.append('<td>'+ (r.type||'') +'</td>');
          tr.append('<td>'+ msg +'</td>');
          tr.append('<td>'+ (r.created_at||'') +'</td>');
          const act = $('<td>');
          if (parseInt(r.resolved) !== 1) {
            act.append('<button class="btn btn-xs btn-success btn-resolve" data-id="'+r.id+'">Mark Read</button>');
          }
          tr.append(act);
          $tb.append(tr);
        });
        
        // Check if DataTable is already initialized using DataTables API
        if ($.fn.DataTable.isDataTable('#tblNotifications')) {
          // DataTable exists, just redraw
          dt.draw();
        } else {
          // DataTable doesn't exist, initialize it
          dt = $('#tblNotifications').DataTable({
            responsive:true, 
            lengthChange:false, 
            autoWidth:false
          });
        }
      });
    }
    $('#frmFilters').on('submit', function(e){ e.preventDefault(); loadData(); });
    $(document).on('click', '.btn-resolve', function(){
      const id = $(this).data('id');
      $.post('ajax/resolve_notification.php', { id:id, csrf_token:'<?php echo $_SESSION['csrf_token']; ?>' }, function(res){
        if(res && res.status==='success'){ loadData(); } else { alert(res && res.message ? res.message : 'Failed'); }
      }, 'json');
    });
    
    // Enhanced Medicine alerts checker with better UI
    $('#btnCheckMedicineAlerts').on('click', function(){
      const $btn = $(this);
      $btn.prop('disabled', true)
          .css('transform', 'scale(0.95)')
          .html('<i class="fas fa-spinner fa-spin mr-1"></i> Checking...');
      
      $.getJSON('ajax/check_medicine_notifications.php', function(res){
        if(res && res.status === 'success'){
          // Create a beautiful notification popup
          const totalAlerts = res.low_stock_count + res.expiring_count + res.expired_count;
          let alertClass = 'alert-success';
          let alertIcon = '✅';
          let alertTitle = 'All Good!';
          
          if (totalAlerts > 0) {
            alertClass = 'alert-warning';
            alertIcon = '⚠️';
            alertTitle = 'Medicine Alerts Found';
          }
          
          // Create custom alert with better styling
          const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" style="border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
              <h4 class="alert-heading" style="font-weight: 700;">
                ${alertIcon} ${alertTitle}
              </h4>
              <hr>
              <p class="mb-2"><strong>Medicine Alert Summary:</strong></p>
              <ul class="mb-2">
                <li><i class="fas fa-exclamation-triangle text-danger mr-1"></i> Low Stock: <strong>${res.low_stock_count}</strong> medicines</li>
                <li><i class="fas fa-clock text-warning mr-1"></i> Expiring Soon: <strong>${res.expiring_count}</strong> medicines</li>
                <li><i class="fas fa-ban text-dark mr-1"></i> Expired: <strong>${res.expired_count}</strong> medicines</li>
              </ul>
              <p class="mb-0"><strong>Notifications Created:</strong> ${res.notifications_created}</p>
            </div>
          `;
          
          // Show the alert
          $('body').append(alertHtml);
          
          // Auto-remove after 5 seconds
          setTimeout(function(){
            $('.alert').fadeOut(500, function(){
              $(this).remove();
            });
          }, 5000);
          
          loadData(); // Refresh the notifications table
        } else {
          // Error alert
          const errorHtml = `
            <div class="alert alert-danger alert-dismissible fade show" style="border-radius: 10px; box-shadow: 0 4px 15px rgba(220,53,69,0.3);">
              <h4 class="alert-heading" style="font-weight: 700;">
                ❌ Error Checking Medicine Alerts
              </h4>
              <p class="mb-0">${res.message || 'Unknown error occurred'}</p>
            </div>
          `;
          $('body').append(errorHtml);
          
          setTimeout(function(){
            $('.alert').fadeOut(500, function(){
              $(this).remove();
            });
          }, 5000);
        }
      }).fail(function(){
        const errorHtml = `
          <div class="alert alert-danger alert-dismissible fade show" style="border-radius: 10px; box-shadow: 0 4px 15px rgba(220,53,69,0.3);">
            <h4 class="alert-heading" style="font-weight: 700;">
              ❌ Connection Error
            </h4>
            <p class="mb-0">Failed to check medicine alerts. Please try again.</p>
          </div>
        `;
        $('body').append(errorHtml);
        
        setTimeout(function(){
          $('.alert').fadeOut(500, function(){
            $(this).remove();
          });
        }, 5000);
      }).always(function(){
        $btn.prop('disabled', false)
            .css('transform', 'scale(1)')
            .html('<i class="fas fa-pills mr-1"></i> Check Medicine Alerts');
      });
    });
    
    loadData();
  </script>
</body>
</html>


