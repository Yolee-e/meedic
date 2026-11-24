<?php 
include './config/connection.php';
include './common_service/common_functions.php';

$preselectId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
$patients = getPatients($con, $preselectId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <?php include './config/site_css_links.php';?>
 <title>Doctor Appointments</title>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
  <div class="wrapper">
    <?php include './config/header.php';
include './config/sidebar.php';?>  
    <div class="content-wrapper">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Doctor Appointments</h1>
            </div>
          </div>
        </div>
      </section>

      <section class="content">
        <div class="card card-outline card-primary shadow">
          <div class="card-header"><h3 class="card-title mb-0">Schedule Personal Appointment</h3></div>
          <div class="card-body">
            <form id="frmDocAppt">
              <?php echo csrf_field(); ?>
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label>Patient</label>
                  <select class="form-control" id="patient_id" name="patient_id" required>
                    <option value="">-- Select Patient --</option>
                    <?php echo $patients; ?>
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <label>Date</label>
                  <input type="date" class="form-control" name="appointment_date" required />
                </div>
                <div class="col-md-4 mb-3">
                  <label>Time</label>
                  <input type="time" class="form-control" name="appointment_time" required />
                </div>
              </div>
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label>Reason (Optional)</label>
                  <textarea class="form-control" name="reason_for_visit" rows="2"></textarea>
                </div>
              </div>
              <div class="text-right">
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
              </div>
            </form>
          </div>
        </div>

        <div class="card card-outline card-info shadow">
          <div class="card-header"><h3 class="card-title mb-0">Recent Personal Appointments</h3></div>
          <div class="card-body">
            <table class="table table-bordered" id="tblDocAppts">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Patient</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Reason</th>
                  <th>Status</th>
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
  <script>
    showMenuSelected('#mnu_doctor_appointments','');
    function loadDocAppts(){
      const pid = $('#patient_id').val();
      const qs = pid ? ('?patient_id='+encodeURIComponent(pid)) : '';
      $.getJSON('ajax/get_doctor_appointments.php'+qs, function(res){
        if(res && res.status==='success'){
          const rows = res.data||[];
          let html='';
          rows.forEach((r,i)=>{
            html += `<tr>
              <td>${i+1}</td>
              <td>${$('<div>').text(r.patient_name).html()}</td>
              <td>${r.appointment_date}</td>
              <td>${r.appointment_time}</td>
              <td>${$('<div>').text(r.reason_for_visit||'').html()}</td>
              <td>${r.status}</td>
            </tr>`;
          });
          $('#tblDocAppts tbody').html(html);
        }
      });
    }
    $('#frmDocAppt').on('submit', function(e){
      e.preventDefault();
      $.post('ajax/add_doctor_appointment.php', $(this).serialize(), function(res){
        if(res && res.status==='success'){
          loadDocAppts();
          alert('Doctor appointment saved.');
        } else {
          alert('Failed: '+(res && res.message ? res.message : 'Unknown error'));
        }
      }, 'json');
    });
    loadDocAppts();
  </script>
</body>
</html>


