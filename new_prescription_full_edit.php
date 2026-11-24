<?php 
include './config/connection.php';
include './common_service/common_functions.php';

$message = '';
$patient_id = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
$patient_data = null;

// Get patient data if patient_id is provided
if ($patient_id > 0) {
	try {
		$stmt = $con->prepare("SELECT * FROM patients WHERE id = ?");
		$stmt->execute([$patient_id]);
		$patient_data = $stmt->fetch(PDO::FETCH_ASSOC);
	} catch (PDOException $ex) {
		$message = "Error loading patient data: " . $ex->getMessage();
	}
}

// Get all patients for dropdown
$patients = getPatients($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './config/site_css_links.php'; ?>
    <?php include './config/data_tables_css.php'; ?>
    <title>New Prescription (Full Edit) - Medical Care and Information System</title>
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
  <div class="wrapper">
    <!-- Navbar -->
    <?php include './config/header.php';
        include './config/sidebar.php'; ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Main content -->
      <section class="content">
        <div class="container-fluid">
          <div class="card card-outline card-primary">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-prescription-bottle-alt"></i> New Prescription <small>(Full Edit)</small></h3>
            </div>
            <div class="card-body">
              <?php if ($message): ?>
                <div class="alert alert-warning alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <?php echo htmlspecialchars($message); ?>
                </div>
              <?php endif; ?>

              <form id="prescription_form" method="POST" action="ajax/save_prescription.php">
                <?php echo csrf_field(); ?>

                <!-- Patient Information -->
                <div class="card patient-info-card">
                  <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user"></i> Patient Information</h3>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="patient_select">Select Patient</label>
                          <select class="form-control select2" id="patient_select" name="patient_id" required style="width: 100%;">
                            <option value="">-- Select Patient --</option>
                            <?php echo $patients; ?>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="consultation_date">Date of Consultation</label>
                          <input type="date" class="form-control" id="consultation_date" name="consultation_date" 
                                 value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                      </div>
                    </div>

                    <!-- Auto-filled patient details (read-only) -->
                    <div id="patient_details" style="display: none;">
                      <div class="row">
                        <div class="col-md-3">
                          <div class="form-group">
                            <label>Patient Name</label>
                            <input type="text" class="form-control" id="patient_name" readonly>
                          </div>
                        </div>
                        <div class="col-md-2">
                          <div class="form-group">
                            <label>Age</label>
                            <input type="text" class="form-control" id="patient_age" readonly>
                          </div>
                        </div>
                        <div class="col-md-2">
                          <div class="form-group">
                            <label>Sex</label>
                            <input type="text" class="form-control" id="patient_gender" readonly>
                          </div>
                        </div>
                        <div class="col-md-5">
                          <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" class="form-control" id="patient_phone" readonly>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="chief_complaint">Chief Complaint (CC)</label>
                            <textarea class="form-control" id="chief_complaint" name="chief_complaint" rows="2" 
                                      placeholder="Enter the main reason for consultation" style="resize: none;"></textarea>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="subjective_objective">S/O (Subjective/Objective Notes)</label>
                            <textarea class="form-control" id="subjective_objective" name="subjective_objective" rows="2" 
                                      placeholder="Enter subjective and objective findings"></textarea>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-12">
                          <div class="assessment-plan-section">
                            <h4><i class="fas fa-clipboard-list"></i> Assessment & Plan (A/P)</h4>
                            <div class="form-group">
                              <textarea class="form-control" id="assessment_plan" name="assessment_plan" rows="4" 
                                        placeholder="Enter assessment and treatment plan" required></textarea>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Last Prescription Section -->
                <div id="last_prescription_section" style="display: none;">
                  <div class="card">
                    <div class="card-header" style="background: #e3f2fd; border: 1px solid #2196f3;">
                      <h3 class="card-title" style="color: #1976d2; font-weight: 600;">
                        <i class="fas fa-history"></i> Last Visit Prescription
                      </h3>
                    </div>
                    <div class="card-body" style="background: #f8f9fa;">
                      <div class="row mb-3">
                        <div class="col-md-6"><strong>Date:</strong> <span id="last_prescription_date"></span></div>
                        <div class="col-md-6"><strong>Doctor:</strong> <span id="last_prescription_doctor"></span></div>
                      </div>
                      <div class="row mb-3">
                        <div class="col-md-12"><strong>Chief Complaint:</strong> <span id="last_prescription_cc"></span></div>
                      </div>
                      <div class="row mb-3">
                        <div class="col-md-12"><strong>S/O:</strong> <span id="last_prescription_so"></span></div>
                      </div>
                      <div class="row mb-3">
                        <div class="col-md-12"><strong>Assessment & Plan:</strong> <span id="last_prescription_ap"></span></div>
                      </div>
                      <div class="row">
                        <div class="col-md-12">
                          <strong>Medicines Prescribed:</strong>
                          <div id="last_prescription_medicines" class="mt-2"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Medicines -->
                <div class="card medicine-table">
                  <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-pills"></i> Prescribed Medicines</h3>
                    <div class="card-tools">
                      <button type="button" class="btn btn-success" id="add_medicine_btn"><i class="fas fa-plus"></i> Add Medicine</button>
                    </div>
                  </div>
                  <div class="card-body">
                    <div id="medicines_container"></div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="text-center mt-3">
                  <button type="submit" class="btn btn-success btn-lg" id="save_prescription_btn"><i class="fas fa-save"></i> Save Prescription</button>
                  <button type="button" class="btn btn-secondary btn-lg ml-2" onclick="window.history.back()"><i class="fas fa-arrow-left"></i> Cancel</button>
                </div>

              </form>
            </div>
          </div>
        </div>
      </section>
    </div>

    <?php include './config/site_js_links.php'; ?>
    <?php include './config/data_tables_js.php'; ?>

<script>
$(function(){
  // Initialize Select2
  $('.select2').select2({ theme: 'default', width: '100%' });

  // Set initial patient if provided
  <?php if ($patient_id > 0 && $patient_data): ?>
    $('#patient_select').val(<?php echo $patient_id; ?>).trigger('change');
  <?php endif; ?>

  // Add first medicine row
  addMedicineRow();

  // Handle patient selection
  $('#patient_select').on('change', function(){
    const patientId = $(this).val();
    if (!patientId) { $('#patient_details').hide(); $('#last_prescription_section').hide(); return; }
    loadPatientDetails(patientId);
    loadLastPrescription(patientId);
  });

  // Form submit
  $('#prescription_form').on('submit', function(e){ e.preventDefault(); savePrescription(); });
});

function loadPatientDetails(patientId){
  $.getJSON('ajax/get_patient_details.php', { patient_id: patientId }, function(res){
    if (res && res.status==='success'){
      const p = res.data;
      $('#patient_name').val(p.patient_name);
      $('#patient_age').val(calculateAge(p.date_of_birth));
      $('#patient_gender').val(p.gender);
      $('#patient_phone').val(p.phone_number);
      $('#patient_details').show();
      // Prefill CC from appointment but keep editable
      loadAppointmentCC(patientId);
    }
  });
}

function loadLastPrescription(patientId){
  $.getJSON('ajax/get_last_prescription.php', { patient_id: patientId }, function(res){
    if (res && res.status==='success' && res.data && res.data.id){
      const pr = res.data;
      const medsHtml = (pr.medicines||[]).map(function(m){
        return '<div class="last-prescription-medicine"><div class="row">'
          +'<div class="col-md-4"><strong>'+m.medicine_name+'</strong></div>'
          +'<div class="col-md-2">Qty: '+m.quantity+'</div>'
          +'<div class="col-md-3">Dosage: '+m.dosage+'</div>'
          +'<div class="col-md-3">Schedule: '+m.schedule+'</div>'
          +'</div></div>';
      }).join('');
      $('#last_prescription_date').text(pr.consultation_date);
      $('#last_prescription_doctor').text(pr.doctor_name||'');
      $('#last_prescription_cc').text(pr.chief_complaint||'');
      $('#last_prescription_so').text(pr.subjective_objective||'');
      $('#last_prescription_ap').text(pr.assessment_plan||'');
      $('#last_prescription_medicines').html(medsHtml);
      $('#last_prescription_section').show();
    } else { $('#last_prescription_section').hide(); }
  });
}

function loadAppointmentCC(patientId){
  $.getJSON('ajax/get_patient_appointment_cc.php', { patient_id: patientId }, function(res){
    if (res && res.status==='success' && res.data && res.data.chief_complaint){
      // Prefill but DO NOT lock; allow editing
      if (!$('#chief_complaint').val()) {
        $('#chief_complaint').val(res.data.chief_complaint);
      }
    }
  });
}

function calculateAge(dobStr){
  const t=new Date(), d=new Date(dobStr); let a=t.getFullYear()-d.getFullYear();
  const m=t.getMonth()-d.getMonth(); if (m<0 || (m===0 && t.getDate()<d.getDate())) a--; return a+' years';
}

function addMedicineRow(){
  const rowId='medicine_'+Date.now();
  const row=`
    <div class="medicine-row" id="${rowId}">
      <div class="row">
        <div class="col-md-3">
          <div class="form-group">
            <label>Medicine Name</label>
            <div class="medicine-name-input">
              <input type="text" class="form-control medicine-name" name="medicine_name[]" placeholder="Enter medicine name" required autocomplete="off">
              <div class="autocomplete-suggestions"></div>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label>Quantity</label>
            <input type="number" class="form-control" name="quantity[]" placeholder="Qty" min="1" required>
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label>Dosage</label>
            <input type="text" class="form-control" name="dosage[]" placeholder="e.g., 1 tablet" required>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Schedule</label>
            <div class="schedule-checkboxes">
              <div class="schedule-checkbox">
                <input type="checkbox" name="schedule_${rowId}[]" value="Before Breakfast" id="bb_${rowId}">
                <label for="bb_${rowId}">B.Breakfast</label>
              </div>
              <div class="schedule-checkbox">
                <input type="checkbox" name="schedule_${rowId}[]" value="After Breakfast" id="ab_${rowId}">
                <label for="ab_${rowId}">A.Breakfast</label>
              </div>
              <div class="schedule-checkbox">
                <input type="checkbox" name="schedule_${rowId}[]" value="Before Lunch" id="bl_${rowId}">
                <label for="bl_${rowId}">B.Lunch</label>
              </div>
              <div class="schedule-checkbox">
                <input type="checkbox" name="schedule_${rowId}[]" value="After Lunch" id="al_${rowId}">
                <label for="al_${rowId}">A.Lunch</label>
              </div>
              <div class="schedule-checkbox">
                <input type="checkbox" name="schedule_${rowId}[]" value="Before Dinner" id="bd_${rowId}">
                <label for="bd_${rowId}">B.Dinner</label>
              </div>
              <div class="schedule-checkbox">
                <input type="checkbox" name="schedule_${rowId}[]" value="After Dinner" id="ad_${rowId}">
                <label for="ad_${rowId}">A.Dinner</label>
              </div>
              <div class="schedule-checkbox">
                <input type="checkbox" name="schedule_${rowId}[]" value="Bedtime" id="bt_${rowId}">
                <label for="bt_${rowId}">Bedtime</label>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-1">
          <div class="form-group">
            <label>&nbsp;</label>
            <button type="button" class="btn btn-danger btn-block" onclick="removeMedicineRow('${rowId}')"><i class="fas fa-trash"></i></button>
          </div>
        </div>
      </div>
    </div>`;
  $('#medicines_container').prepend(row);
  initializeAutocomplete(rowId);
  initializeScheduleCheckboxes(rowId);
}

function removeMedicineRow(id){ if ($('#medicines_container .medicine-row').length>1) $('#'+id).remove(); else alert('At least one medicine is required'); }

function initializeAutocomplete(rowId){
  const input=$(`#${rowId} .medicine-name`), sug=$(`#${rowId} .autocomplete-suggestions`);
  input.on('input', function(){
    const q=$(this).val(); if (q.length<2){ sug.hide(); return; }
    $.getJSON('ajax/get_medicines.php', { query:q }, function(res){ if (res && res.status==='success'){ displaySuggestions(sug, res.data); }});
  });
  sug.on('click', '.autocomplete-suggestion', function(){ input.val($(this).text()); sug.hide(); });
  $(document).on('click', function(e){ if (!$(e.target).closest(`#${rowId}`).length) { sug.hide(); } });
}

function displaySuggestions(container, list){
  if (!list || !list.length){ container.hide(); return; }
  let html=''; list.forEach(function(s){ html += '<div class="autocomplete-suggestion">'+s.medicine_name+'</div>'; });
  container.html(html).show();
}

function initializeScheduleCheckboxes(rowId){
  $(`#${rowId} .schedule-checkbox input[type="checkbox"]`).on('change', function(){
    const box=$(this).closest('.schedule-checkbox');
    if ($(this).is(':checked')){ box.addClass('checked'); } else { box.removeClass('checked'); }
  });
  $(`#${rowId} .schedule-checkbox`).on('click', function(e){ if (e.target.type!=='checkbox'){ const cb=$(this).find('input[type="checkbox"]'); cb.prop('checked', !cb.prop('checked')).trigger('change'); }});
}

function validateForm(){
  if (!$('#patient_select').val()){ alert('Please select a patient'); return false; }
  if (!$('#consultation_date').val()){ alert('Consultation date is required'); return false; }
  if (!$('#assessment_plan').val().trim()){ alert('Assessment & Plan is required'); return false; }
  let valid=0; $('.medicine-row').each(function(){ const n=$(this).find('.medicine-name').val(); const q=$(this).find('input[name="quantity[]"]').val(); const d=$(this).find('input[name="dosage[]"]').val(); const c=$(this).find('input[type="checkbox"]:checked').length; if (n && q && d && c>0) valid++; });
  if (valid===0){ alert('Please complete medicine details with at least one schedule'); return false; }
  return true;
}

function savePrescription(){
  if (!validateForm()) return;
  const btn=$('#save_prescription_btn'); btn.prop('disabled', true);
  const formData=new FormData($('#prescription_form')[0]);
  const meds=[]; $('.medicine-row').each(function(){ const id=$(this).attr('id'); const name=$(this).find('.medicine-name').val(); const qty=$(this).find('input[name="quantity[]"]').val(); const dosage=$(this).find('input[name="dosage[]"]').val(); const schedules=[]; $(this).find(`input[name="schedule_${id}[]"]:checked`).each(function(){ schedules.push($(this).val()); }); if (name && qty && dosage && schedules.length>0){ meds.push({ medicine_name:name, quantity:qty, dosage:dosage, schedule:schedules.join(', ') }); }});
  formData.append('medicines', JSON.stringify(meds));
  $.ajax({ url:'ajax/save_prescription.php', method:'POST', data:formData, processData:false, contentType:false, dataType:'json' })
    .done(function(resp){
      if (resp && resp.status==='success'){
        setTimeout(function(){ window.location.href='print_prescription.php?id='+resp.prescription_id; }, 800);
      } else { alert('Error: '+(resp && resp.message ? resp.message : 'Failed to save')); }
    })
    .fail(function(){ alert('Error saving prescription'); })
    .always(function(){ btn.prop('disabled', false); });
}
</script>

  </div>
</body>
</html>


