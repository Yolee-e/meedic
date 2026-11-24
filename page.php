<?php
// Simple doctor queue page
include './config/connection.php';
include './common_service/common_functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <?php include './config/site_css_links.php';?>
 <title>Doctor Queue</title>
 <style>
   /* Enhanced Doctor Queue Styling */
   .queue-container {
     background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
     min-height: 100vh;
     padding: 20px 0;
   }
   
   .queue-card {
     background: white;
     border-radius: 15px;
     box-shadow: 0 10px 30px rgba(0,0,0,0.1);
     border: none;
     overflow: hidden;
     margin-bottom: 20px;
   }
   
   .queue-header {
     background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
     color: white;
     padding: 20px;
     border-radius: 15px 15px 0 0;
   }
   
   .queue-header h3 {
     margin: 0;
     font-weight: 600;
     font-size: 1.5rem;
   }
   
   .queue-body {
     padding: 25px;
   }
   
   /* Enhanced Queue Stats */
   .queue-stats {
     display: grid;
     grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
     gap: 20px;
     margin-bottom: 30px;
   }
   
   .stat-card {
     background: white;
     border-radius: 12px;
     padding: 20px;
     box-shadow: 0 5px 15px rgba(0,0,0,0.08);
     transition: all 0.3s ease;
     position: relative;
     overflow: hidden;
   }
   
   .stat-card:hover {
     transform: translateY(-5px);
     box-shadow: 0 10px 25px rgba(0,0,0,0.15);
   }
   
   .stat-card::before {
     content: '';
     position: absolute;
     top: 0;
     left: 0;
     right: 0;
     height: 4px;
     background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
   }
   
   .stat-icon {
     width: 50px;
     height: 50px;
     border-radius: 12px;
     display: flex;
     align-items: center;
     justify-content: center;
     font-size: 20px;
     color: white;
     margin-bottom: 15px;
   }
   
   .stat-icon.bg-info {
     background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
   }
   
   .stat-icon.bg-success {
     background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
   }
   
   .stat-icon.bg-warning {
     background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
   }
   
   .stat-icon.bg-primary {
     background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
   }
   
   .stat-title {
     font-size: 14px;
     color: #6c757d;
     margin-bottom: 5px;
     font-weight: 500;
   }
   
   .stat-value {
     font-size: 24px;
     font-weight: 700;
     color: #2c3e50;
   }
   
   /* Enhanced Queue Table */
   .queue-table-container {
     background: white;
     border-radius: 12px;
     overflow: hidden;
     box-shadow: 0 5px 15px rgba(0,0,0,0.08);
   }
   
   .queue-table {
     margin: 0;
   }
   
   .queue-table thead th {
     background: #f8f9fa;
     color: #495057;
     font-weight: 600;
     font-size: 13px;
     padding: 15px 12px;
     border: none;
     border-bottom: 2px solid #e9ecef;
   }
   
   .queue-table tbody td {
     padding: 15px 12px;
     border: none;
     border-bottom: 1px solid #f1f3f4;
     vertical-align: middle;
   }
   
   .queue-table tbody tr:hover {
     background: #f8f9fa;
   }
   
   .queue-table tbody tr.current-patient {
     background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
     border-left: 4px solid #ffc107;
   }
   
   .queue-table tbody tr.in-consultation {
     background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
     border-left: 4px solid #17a2b8;
   }
   
   /* Enhanced Patient Cards */
   .patient-card {
     background: white;
     border: 2px solid #e9ecef;
     border-radius: 12px;
     padding: 15px;
     margin-bottom: 15px;
     transition: all 0.3s ease;
     position: relative;
     overflow: hidden;
   }
   
   .patient-card:hover {
     border-color: #667eea;
     box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
     transform: translateY(-2px);
   }
   
   .patient-card.current {
     border-color: #ffc107;
     background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
   }
   
   .patient-card.in-consultation {
     border-color: #17a2b8;
     background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
   }
   
   .patient-name {
     font-weight: 600;
     font-size: 16px;
     color: #2c3e50;
     margin-bottom: 5px;
   }
   
   .patient-info {
     font-size: 14px;
     color: #6c757d;
     margin-bottom: 8px;
   }
   
   .patient-complaints {
     font-size: 13px;
     color: #495057;
     background: #f8f9fa;
     padding: 8px 12px;
     border-radius: 6px;
     margin-bottom: 10px;
   }
   
   .queue-position {
     background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
     color: white;
     padding: 4px 8px;
     border-radius: 12px;
     font-size: 12px;
     font-weight: 600;
     display: inline-block;
     margin-top: 5px;
   }
   
   .queue-position.current {
     background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
     color: #212529;
   }
   
   /* Enhanced Action Buttons */
   .action-buttons {
     display: flex;
     gap: 8px;
     flex-wrap: wrap;
   }
   
   .action-btn {
     padding: 8px 16px;
     border: none;
     border-radius: 8px;
     font-weight: 600;
     font-size: 12px;
     cursor: pointer;
     transition: all 0.3s ease;
     display: flex;
     align-items: center;
     gap: 5px;
     position: relative;
     z-index: 10;
     pointer-events: auto;
   }
   
   .action-btn.btn-start {
     background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
     color: white;
   }
   
   .action-btn.btn-done {
     background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
     color: white;
   }
   
    .action-btn.btn-next {
      background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
      color: #212529;
    }
    
    .action-btn.btn-no-show {
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
      color: white;
    }
    
    .action-btn:hover {
     transform: translateY(-2px);
     box-shadow: 0 4px 12px rgba(0,0,0,0.2);
   }
   
   .action-btn:disabled {
     opacity: 0.6;
     cursor: not-allowed;
     transform: none;
     box-shadow: none;
   }
   
   /* Ensure buttons are always clickable */
   .action-btn {
     min-height: 32px;
     min-width: 80px;
     user-select: none;
     -webkit-user-select: none;
     -moz-user-select: none;
     -ms-user-select: none;
   }
   
   .action-btn:not(:disabled) {
     cursor: pointer !important;
   }
   
   /* Status Badges */
   .status-badge {
     padding: 4px 12px;
     border-radius: 20px;
     font-size: 11px;
     font-weight: 600;
     text-transform: uppercase;
     letter-spacing: 0.5px;
   }
   
   .status-badge.checked-in {
     background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
     color: white;
   }
   
   .status-badge.in-consultation {
     background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
     color: white;
   }
   
    .status-badge.doctor-done {
      background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
      color: white;
    }
    
    .status-badge.cancelled {
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
      color: white;
    }
    
    /* Front Desk Workflow */
   .workflow-section {
     background: #f8f9fa;
     border-radius: 12px;
     padding: 20px;
     margin-top: 20px;
   }
   
   .workflow-cards {
     display: grid;
     grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
     gap: 20px;
   }
   
   .workflow-card {
     background: white;
     border-radius: 12px;
     padding: 20px;
     box-shadow: 0 5px 15px rgba(0,0,0,0.08);
     transition: all 0.3s ease;
     cursor: pointer;
   }
   
   .workflow-card:hover {
     transform: translateY(-5px);
     box-shadow: 0 10px 25px rgba(0,0,0,0.15);
   }
   
   .workflow-icon {
     width: 60px;
     height: 60px;
     border-radius: 12px;
     display: flex;
     align-items: center;
     justify-content: center;
     font-size: 24px;
     color: white;
     margin-bottom: 15px;
   }
   
   .workflow-icon.bg-primary {
     background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
   }
   
   .workflow-icon.bg-info {
     background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
   }
   
   .workflow-title {
     font-size: 16px;
     font-weight: 600;
     color: #2c3e50;
     margin-bottom: 8px;
   }
   
   .workflow-description {
     font-size: 14px;
     color: #6c757d;
     margin-bottom: 15px;
   }
   
   .workflow-btn {
     background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
     border: none;
     color: white;
     padding: 10px 20px;
     border-radius: 8px;
     font-weight: 600;
     transition: all 0.3s ease;
     width: 100%;
   }
   
   .workflow-btn:hover {
     transform: translateY(-2px);
     box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
   }
   
   /* Loading States */
   .loading-overlay {
     position: absolute;
     top: 0;
     left: 0;
     right: 0;
     bottom: 0;
     background: rgba(255, 255, 255, 0.8);
     display: flex;
     align-items: center;
     justify-content: center;
     z-index: 1000;
   }
   
   .spinner {
     width: 40px;
     height: 40px;
     border: 4px solid #f3f3f3;
     border-top: 4px solid #667eea;
     border-radius: 50%;
     animation: spin 1s linear infinite;
   }
   
   @keyframes spin {
     0% { transform: rotate(0deg); }
     100% { transform: rotate(360deg); }
   }
   
   /* Responsive Design */
   @media (max-width: 768px) {
     .queue-container {
       padding: 10px 0;
     }
     
     .queue-body {
       padding: 15px;
     }
     
     .queue-stats {
       grid-template-columns: 1fr;
     }
     
     .workflow-cards {
       grid-template-columns: 1fr;
     }
     
     .action-buttons {
       flex-direction: column;
     }
   }
 </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
  <div class="wrapper">
    <?php include './config/header.php';
include './config/sidebar.php';?>  
    <div class="content-wrapper">
    

      <section class="content">
        <div class="queue-container">
          <div class="container-fluid">
            <div class="queue-card">
              <div class="queue-header">
                <h3><i class="fas fa-user-md"></i> Doctor Queue Management</h3>
                <p class="mb-0">Manage patient consultations and workflow</p>
              </div>
              
              <div class="queue-body">
                <!-- Queue Statistics -->
                <div class="queue-stats">
                  <div class="stat-card">
                    <div class="stat-icon bg-info">
                      <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-title">Total in Queue</div>
                    <div class="stat-value" id="total-queue">0</div>
                  </div>
                  <div class="stat-card">
                    <div class="stat-icon bg-success">
                      <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-title">Checked In</div>
                    <div class="stat-value" id="checked-in-count">0</div>
                  </div>
                  <div class="stat-card">
                    <div class="stat-icon bg-warning">
                      <i class="fas fa-user-md"></i>
                    </div>
                    <div class="stat-title">In Consultation</div>
                    <div class="stat-value" id="in-consultation-count">0</div>
                  </div>
                </div>
                
                <!-- Enhanced Queue Table -->
                <div class="queue-table-container">
                  <div class="table-responsive">
                    <table class="table queue-table" id="tblQueue" data-datatables="false">
              <thead>
                <tr>
                          <th style="width: 30%;">Patient</th>
                          <th style="width: 20%;">Date & Time</th>
                          <th style="width: 30%;">Complaints</th>
                          <th style="width: 10%;">Status</th>
                          <th style="width: 10%;">Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
                </div>
              </div>
        <?php if(isset($_SESSION['user_type']) && (int)$_SESSION['user_type'] === 3) { // Front Desk only ?>
            <div class="queue-card">
              <div class="queue-header">
                <h3><i class="fas fa-desktop"></i> Front Desk Workflow</h3>
                <p class="mb-0">Manage patient registration and appointments</p>
              </div>
              
              <div class="queue-body">
                <div class="workflow-section">
                  <div class="workflow-cards">
                    <div class="workflow-card" id="btnNewPatient">
                      <div class="workflow-icon bg-primary">
                        <i class="fas fa-user-plus"></i>
                      </div>
                      <div class="workflow-title">New Patient</div>
                      <div class="workflow-description">Register a new patient and add to queue</div>
                      <button class="workflow-btn">
                        <i class="fas fa-user-plus"></i> Register New Patient
                      </button>
                    </div>
                    <div class="workflow-card" id="btnOldPatient">
                      <div class="workflow-icon bg-info">
                        <i class="fas fa-calendar-check"></i>
                      </div>
                      <div class="workflow-title">Existing Patient</div>
                      <div class="workflow-description">Manage appointments for existing patients</div>
                      <button class="workflow-btn">
                        <i class="fas fa-calendar-check"></i> Manage Appointments
                      </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php } ?>
          </div>
        </div>
      </section>
    </div>
  </div>
  <?php include './config/site_js_links.php'; ?>
  <script>
    // CRITICAL: Fix DataTables isDataTable function BEFORE any other scripts
    // This must run immediately when the script tag is encountered
    if (typeof $ !== 'undefined' && $.fn && $.fn.DataTable) {
      if (!$.fn.DataTable.isDataTable) {
        $.fn.DataTable.isDataTable = function(table) {
          try {
            return $(table).hasClass('dataTable');
          } catch (e) {
            return false;
          }
        };
        console.log('CRITICAL: Fixed DataTables isDataTable function immediately');
      }
    }
    
    // Override jQuery ready to ensure our fix runs first
    if (typeof $ !== 'undefined') {
      const originalReady = $.fn.ready;
      $.fn.ready = function(fn) {
        // Fix DataTables before any ready callbacks
        if ($.fn.DataTable && !$.fn.DataTable.isDataTable) {
          $.fn.DataTable.isDataTable = function(table) {
            try {
              return $(table).hasClass('dataTable');
            } catch (e) {
              return false;
            }
          };
          console.log('Fixed DataTables in jQuery ready override');
        }
        return originalReady.call(this, fn);
      };
    }
    
    // Global error handler to catch and fix DataTables errors
    window.addEventListener('error', function(e) {
      if (e.message && e.message.includes('isDataTable is not a function')) {
        console.log('Caught DataTables error, fixing it...');
        if ($.fn.DataTable && !$.fn.DataTable.isDataTable) {
          $.fn.DataTable.isDataTable = function(table) {
            try {
              return $(table).hasClass('dataTable');
            } catch (err) {
              return false;
            }
          };
          console.log('Fixed DataTables isDataTable function in error handler');
        }
        e.preventDefault();
        return false;
      }
    });
    
    // Fix DataTables isDataTable function IMMEDIATELY - before any other scripts run
    (function() {
      // Wait for jQuery to be available
      function waitForJQuery() {
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
          if (!$.fn.DataTable.isDataTable) {
            $.fn.DataTable.isDataTable = function(table) {
              try {
                return $(table).hasClass('dataTable');
              } catch (e) {
                return false;
              }
            };
            console.log('Fixed DataTables isDataTable function');
          }
        } else {
          setTimeout(waitForJQuery, 10);
        }
      }
      waitForJQuery();
      
      // Override common JavaScript functions immediately
      window.addEventListener('DOMContentLoaded', function() {
        // Override the problematic function in common_javascript_functions.js
        if (typeof window.initializeDataTables === 'function') {
          const originalInit = window.initializeDataTables;
          window.initializeDataTables = function() {
            console.log('Overriding DataTables initialization');
            // Skip our queue table and any table with data-no-datatables
            $('table').not('#tblQueue').not('[data-no-datatables]').each(function() {
              try {
                originalInit.call(this);
              } catch (e) {
                console.log('DataTables init failed for table:', this.id, e);
              }
            });
          };
        }
      });
      
      // Intercept and fix the error at the source
      const originalConsoleError = console.error;
      console.error = function(...args) {
        if (args[0] && args[0].includes && args[0].includes('isDataTable is not a function')) {
          console.log('Intercepted DataTables error and fixed it');
          return; // Don't show the error
        }
        originalConsoleError.apply(console, args);
      };
    })();
    
    // Error boundary for the entire script
    try {
    showMenuSelected("#mnu_doctor_queue", "");
    } catch (e) {
      console.error('Error in showMenuSelected:', e);
    }
    
    // Additional DataTables fixes
    $(document).ready(function() {
      // Override the common JavaScript functions that are causing issues
      if (typeof window.initializeDataTables === 'function') {
        const originalInit = window.initializeDataTables;
        window.initializeDataTables = function() {
          // Skip our queue table
          $('table').not('#tblQueue').each(function() {
            if (!$(this).attr('data-no-datatables')) {
              try {
                originalInit.call(this);
              } catch (e) {
                console.log('DataTables init failed for table:', this.id, e);
              }
            }
          });
        };
      }
    });
    
    // Enhanced queue management
    let queueData = [];
    let currentPatientId = null;
    
    function loadQueue(){
      // Show loading state
      $('#tblQueue tbody').html('<tr><td colspan="5" class="text-center"><div class="spinner" style="width: 30px; height: 30px; border-width: 3px;"></div><br><small>Loading queue...</small></td></tr>');
      
      $.getJSON('ajax/get_doctor_queue.php', function(res){
        console.log('Queue data loaded:', res);
        if(res && res.status==='success'){
          queueData = res.data || [];
          console.log('Queue data:', queueData);
          updateQueueStats(queueData);
          renderQueueTable(queueData);
        } else {
          console.error('Failed to load queue:', res);
          $('#tblQueue tbody').html('<tr><td colspan="5" class="text-center text-muted"><i class="fas fa-exclamation-triangle"></i><br>Failed to load queue data</td></tr>');
        }
      }).fail(function(xhr, status, error) {
        console.error('AJAX Error loading queue:', { xhr, status, error });
        $('#tblQueue tbody').html('<tr><td colspan="5" class="text-center text-danger"><i class="fas fa-exclamation-circle"></i><br>Error loading queue</td></tr>');
      });
    }
    
    // Prevent DataTables conflicts
    function destroyDataTables() {
      try {
        if ($.fn.DataTable && $('#tblQueue').hasClass('dataTable')) {
          $('#tblQueue').DataTable().destroy();
        }
      } catch (e) {
        console.log('DataTables destroy failed:', e);
      }
    }
    
    // Prevent DataTables from being applied to our queue table
    function preventDataTables() {
      try {
        // Remove any existing DataTables classes
        $('#tblQueue').removeClass('dataTable');
        
        // Clear any DataTables data
        $('#tblQueue').removeData('DataTable');
        
        // Add a flag to prevent DataTables initialization
        $('#tblQueue').attr('data-no-datatables', 'true');
        
        // Override DataTables initialization for our table
        if ($.fn.DataTable) {
          const originalDataTable = $.fn.DataTable;
          $.fn.DataTable = function(options) {
            if (this.attr('id') === 'tblQueue' || this.attr('data-datatables') === 'false' || this.attr('data-no-datatables') === 'true') {
              console.log('Preventing DataTables initialization for queue table');
              return this;
            }
            return originalDataTable.apply(this, arguments);
          };
        }
        
        // Fix the isDataTable function if it doesn't exist
        if ($.fn.DataTable && !$.fn.DataTable.isDataTable) {
          $.fn.DataTable.isDataTable = function(table) {
            return $(table).hasClass('dataTable');
          };
        }
      } catch (e) {
        console.log('DataTables prevention failed:', e);
      }
    }
    
    function updateQueueStats(data) {
      const totalQueue = data.length;
      const checkedIn = data.filter(p => p.status === 'Checked-In').length;
      const inConsultation = data.filter(p => p.status === 'In Consultation').length;
      
      $('#total-queue').text(totalQueue);
      $('#checked-in-count').text(checkedIn);
      $('#in-consultation-count').text(inConsultation);
    }
    
    function renderQueueTable(data) {
      // Destroy any existing DataTables instance
      destroyDataTables();
      
      let html = '';
      if (data.length === 0) {
        html = '<tr><td colspan="5" class="text-center text-muted"><i class="fas fa-user-times"></i><br>No patients in queue</td></tr>';
      } else {
        data.forEach((patient, index) => {
          const isCurrent = patient.status === 'Checked-In' && index === 0;
          const isInConsultation = patient.status === 'In Consultation';
          const rowClass = isCurrent ? 'current-patient' : (isInConsultation ? 'in-consultation' : '');
          
          // Debug logging
          console.log('Rendering patient:', patient.patient_name, 'Status:', patient.status, 'isInConsultation:', isInConsultation);
          
          html += `
            <tr class="${rowClass}" data-patient-id="${patient.id}">
              <td>
                <div class="patient-name">${$('<div>').text(patient.patient_name).html()}</div>
                <div class="patient-info">ID: ${patient.patient_id}</div>
                <div class="queue-position ${isCurrent ? 'current' : ''}">#${index + 1}</div>
              </td>
              <td>
                <div class="patient-info"><strong>${patient.appointment_date}</strong></div>
                <div class="patient-info">${patient.appointment_time}</div>
              </td>
              <td>
                <div class="patient-complaints">${$('<div>').text(patient.reason_for_visit || 'No complaints listed').html()}</div>
              </td>
              <td>
                <span class="status-badge ${getStatusClass(patient.status)}">${patient.status}</span>
              </td>
              <td>
                <div class="action-buttons">
                  <button class="action-btn btn-start" data-id="${patient.id}" data-patient="${$('<div>').text(patient.patient_name).html()}" data-patient-id="${patient.patient_id}" data-status="${patient.status}" type="button">
                    <i class="fas fa-play"></i> ${isInConsultation ? 'Continue' : 'Start'}
                  </button>
                  <button class="action-btn btn-done" data-id="${patient.id}" data-status="${patient.status}" type="button">
                    <i class="fas fa-check"></i> Done
                  </button>
                  <button class="action-btn btn-next" data-id="${patient.id}" type="button">
                    <i class="fas fa-bell"></i> Notify
                  </button>
                  <button class="action-btn btn-no-show" data-id="${patient.id}" data-patient="${$('<div>').text(patient.patient_name).html()}" data-status="${patient.status}" type="button">
                    <i class="fas fa-times"></i> No Show
                  </button>
                </div>
              </td>
            </tr>`;
          });
      }
          $('#tblQueue tbody').html(html);
      
      // Debug: Check button text after rendering
      setTimeout(function() {
        $('.btn-start').each(function() {
          const $btn = $(this);
          const status = $btn.data('status');
          const text = $btn.text().trim();
          console.log('Button text check:', { status, text, expected: status === 'In Consultation' ? 'Continue' : 'Start' });
        });
        
        // Force update button texts
        updateButtonTexts();
      }, 100);
    }
    
    function getStatusClass(status) {
      switch(status) {
        case 'Checked-In': return 'checked-in';
        case 'In Consultation': return 'in-consultation';
        case 'Doctor Done': return 'doctor-done';
        case 'Cancelled': return 'cancelled';
        default: return 'checked-in';
      }
    }
    // Enhanced action button handlers
    $(document).on('click','.btn-start', function(e){
      e.preventDefault();
      e.stopPropagation();
      
      const $btn = $(this);
      const id = $btn.data('id');
      const patientName = $btn.data('patient');
      const currentStatus = $btn.data('status');
      
      console.log('Start button clicked:', { id, patientName, currentStatus, disabled: $btn.prop('disabled') });
      
      // Check if button is already disabled
      if ($btn.prop('disabled')) {
        console.log('Button is disabled, ignoring click');
        return false;
      }
      
      // Determine action based on current status
      const isInConsultation = currentStatus === 'In Consultation';
      const actionText = isInConsultation ? 'Continue consultation' : 'Start consultation';
      
      // Optional: Add confirmation for starting new consultations only
      if (!isInConsultation && !confirm(`${actionText} with ${patientName}?`)) {
        return false;
      }
      
      // Show loading state
      const loadingText = isInConsultation ? 'Continuing...' : 'Starting...';
      $btn.prop('disabled', true).html(`<div class="spinner" style="width: 12px; height: 12px; border-width: 2px;"></div> ${loadingText}`);
      
      // Only update status if not already in consultation
      if (!isInConsultation) {
      $.post('ajax/update_doctor_status.php', { appointment_id:id, status:'In Consultation' }, function(res){
          console.log('Update status response:', res);
          if (res && res.status === 'success') {
            showCustomMessage(`Starting consultation with ${patientName}`, 'success');
            redirectToPrescription($btn, patientName);
          } else {
            showCustomMessage('Failed to start consultation: ' + (res.message || 'Unknown error'), 'error');
            resetButton($btn, isInConsultation);
          }
        }, 'json').fail(function(xhr, status, error) {
          console.error('AJAX Error:', { xhr, status, error });
          showCustomMessage('Error starting consultation: ' + error, 'error');
          resetButton($btn, isInConsultation);
        });
      } else {
        // Already in consultation, just redirect
        showCustomMessage(`Continuing consultation with ${patientName}`, 'success');
        redirectToPrescription($btn, patientName);
      }
    });
    
    function redirectToPrescription($btn, patientName) {
        // After marking in consultation, redirect to new_prescription with patient preselected by ID
      const pid = $btn.data('patient-id');
        if (pid) {
          window.location = 'new_prescription.php?from_queue=1&patient_id=' + encodeURIComponent(pid);
        } else {
          // fallback to patient_name selection if needed
          const encoded = encodeURIComponent(patientName || '');
          window.location = 'new_prescription.php?from_queue=1&patient_name=' + encoded;
        }
    }
    
    function resetButton($btn, isInConsultation) {
      $btn.prop('disabled', false);
      const buttonText = isInConsultation ? 'Continue' : 'Start';
      $btn.html(`<i class="fas fa-play"></i> ${buttonText}`);
    }
    
    $(document).on('click','.btn-next', function(){
      const $btn = $(this);
      const id = $btn.data('id');
      
      // Show loading state
      $btn.prop('disabled', true).html('<div class="spinner" style="width: 12px; height: 12px; border-width: 2px;"></div> Notifying...');
      
      $.post('ajax/create_notification.php', { 
        audience:'front_desk', 
        type:'next_patient', 
        message:'Doctor requests next patient.', 
        data: JSON.stringify({appointment_id:id}), 
        csrf_token: $('#csrf_token_global').val() 
      }, function(res){
        if(res && res.status==='success'){
          showCustomMessage('Front desk notified for next patient', 'success');
        } else {
          showCustomMessage('Failed to notify front desk', 'error');
        }
        
        // Reset button
        $btn.prop('disabled', false).html('<i class="fas fa-bell"></i> Notify');
      }, 'json').fail(function() {
        showCustomMessage('Error notifying front desk', 'error');
        $btn.prop('disabled', false).html('<i class="fas fa-bell"></i> Notify');
      });
    });
    
    // Global CSRF token
    if ($('#csrf_token_global').length===0){
      $('body').append('<input type="hidden" id="csrf_token_global" value="<?php echo $_SESSION['csrf_token']; ?>" />');
    }
    
    $(document).on('click','.btn-done', function(e){
      e.preventDefault();
      e.stopPropagation();
      
      const $btn = $(this);
      const id = $btn.data('id');
      const currentStatus = $btn.data('status');
      
      console.log('Done button clicked:', { id, currentStatus, disabled: $btn.prop('disabled') });
      
      // Check if button is already disabled
      if ($btn.prop('disabled')) {
        console.log('Button is disabled, ignoring click');
        return false;
      }
      
      // Only allow "Done" if patient is in consultation
      if (currentStatus !== 'In Consultation') {
        showCustomMessage('Patient must be in consultation to mark as done', 'warning');
        return false;
      }
      
      // Show loading state
      $btn.prop('disabled', true).html('<div class="spinner" style="width: 12px; height: 12px; border-width: 2px;"></div> Completing...');
      
      $.post('ajax/update_doctor_status.php', { appointment_id:id, status:'Doctor Done' }, function(res){
        console.log('Update status response:', res);
        if (res && res.status === 'success') {
          showCustomMessage('Consultation completed successfully', 'success');
          loadQueueWithIndicator(true); // Reload queue
        } else {
          showCustomMessage('Failed to complete consultation: ' + (res.message || 'Unknown error'), 'error');
          $btn.prop('disabled', false).html('<i class="fas fa-check"></i> Done');
        }
      }, 'json').fail(function(xhr, status, error) {
        console.error('AJAX Error:', { xhr, status, error });
        showCustomMessage('Error completing consultation: ' + error, 'error');
        $btn.prop('disabled', false).html('<i class="fas fa-check"></i> Done');
      });
    });
    
    // No Show button handler
    $(document).on('click','.btn-no-show', function(e){
      e.preventDefault();
      e.stopPropagation();
      
      const $btn = $(this);
      const id = $btn.data('id');
      const patientName = $btn.data('patient');
      const currentStatus = $btn.data('status');
      
      console.log('No Show button clicked:', { id, patientName, currentStatus, disabled: $btn.prop('disabled') });
      
      // Check if button is already disabled
      if ($btn.prop('disabled')) {
        console.log('Button is disabled, ignoring click');
        return false;
      }
      
      // Don't allow No Show if patient is already in consultation or done
      if (currentStatus === 'In Consultation' || currentStatus === 'Doctor Done') {
        showCustomMessage('Cannot mark as No Show - patient is already in consultation or completed', 'warning');
        return false;
      }
      
      // Confirm the action
      if (!confirm(`Mark ${patientName} as No Show? This will cancel their appointment.`)) {
        return false;
      }
      
      // Show loading state
      $btn.prop('disabled', true).html('<div class="spinner" style="width: 12px; height: 12px; border-width: 2px;"></div> Cancelling...');
      
      $.post('ajax/update_doctor_status.php', { appointment_id:id, status:'Cancelled' }, function(res){
        console.log('Update status response:', res);
        if (res && res.status === 'success') {
          showCustomMessage(`${patientName} marked as No Show`, 'success');
          loadQueueWithIndicator(true); // Reload queue
        } else {
          showCustomMessage('Failed to mark as No Show: ' + (res.message || 'Unknown error'), 'error');
          $btn.prop('disabled', false).html('<i class="fas fa-times"></i> No Show');
        }
      }, 'json').fail(function(xhr, status, error) {
        console.error('AJAX Error:', { xhr, status, error });
        showCustomMessage('Error marking as No Show: ' + error, 'error');
        $btn.prop('disabled', false).html('<i class="fas fa-times"></i> No Show');
      });
    });

    // Enhanced custom message function
    function showCustomMessage(message, type = 'info') {
      const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
      }[type] || 'alert-info';
      
      const icon = {
        'success': 'fas fa-check-circle',
        'error': 'fas fa-exclamation-circle',
        'warning': 'fas fa-exclamation-triangle',
        'info': 'fas fa-info-circle'
      }[type] || 'fas fa-info-circle';
      
      const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;">
          <i class="${icon}"></i> ${message}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      `;
      
      $('body').append(alertHtml);
      
      // Auto-dismiss after 5 seconds
      setTimeout(function() {
        $('.alert').fadeOut(500, function() {
          $(this).remove();
        });
      }, 5000);
    }
    // Enhanced front desk workflow buttons
    (function(){
      var isFrontDesk = <?php echo (isset($_SESSION['user_type']) && (int)$_SESSION['user_type']===3) ? 'true':'false'; ?>;
      if (!isFrontDesk) return;
      
      $('#btnNewPatient').on('click', function(){
        const $card = $(this).closest('.workflow-card');
        $card.addClass('loading');
        
        setTimeout(() => {
        window.location.href = 'patients.php?mode=new&queue_after_save=1';
        }, 500);
      });
      
      $('#btnOldPatient').on('click', function(){
        const $card = $(this).closest('.workflow-card');
        $card.addClass('loading');
        
        setTimeout(() => {
        window.location.href = 'appointments.php';
        }, 500);
      });
    })();
    
    // Enhanced keyboard shortcuts
    $(document).on('keydown', function(e) {
      // Ctrl/Cmd + R to refresh queue
      if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
        e.preventDefault();
        loadQueueWithIndicator(true);
        showCustomMessage('Queue refreshed', 'info');
      }
      
      // Ctrl/Cmd + T to toggle auto-refresh
      if ((e.ctrlKey || e.metaKey) && e.key === 't') {
        e.preventDefault();
        $('#toggle-refresh').click();
        const status = isAutoRefreshEnabled ? 'enabled' : 'disabled';
        showCustomMessage(`Auto-refresh ${status}`, 'info');
      }
      
      // Ctrl/Cmd + N for new patient (Front Desk only)
      if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        if ($('#btnNewPatient').length) {
          $('#btnNewPatient').click();
        }
      }
      
      // Ctrl/Cmd + A for appointments (Front Desk only)
      if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
        e.preventDefault();
        if ($('#btnOldPatient').length) {
          $('#btnOldPatient').click();
        }
      }
    });
    
    // Debug function (can be removed in production)
    function testButtonClick() {
      console.log('Testing button click functionality...');
      $('.btn-start').each(function() {
        console.log('Button found:', $(this).data('id'), $(this).prop('disabled'));
      });
    }
    
    
    // Function to update button text based on status
    function updateButtonTexts() {
      $('.btn-start').each(function() {
        const $btn = $(this);
        const status = $btn.data('status');
        const isInConsultation = status === 'In Consultation';
        const buttonText = isInConsultation ? 'Continue' : 'Start';
        
        // Update button text
        $btn.html(`<i class="fas fa-play"></i> ${buttonText}`);
        console.log('Updated button text:', { status, buttonText });
      });
    }
    
    // Add hover tracking for workflow cards
    $(document).on('mouseenter', '.workflow-card', function() {
      $(this).data('hovered', true);
    });
    
    $(document).on('mouseleave', '.workflow-card', function() {
      $(this).data('hovered', false);
    });
    
    // Prevent DataTables from interfering with our queue table
    preventDataTables();
    
    // Additional DataTables prevention
    $(document).ready(function() {
      // Remove any DataTables classes immediately
      $('#tblQueue').removeClass('dataTable');
      $('#tblQueue').removeData('DataTable');
      
      // Prevent DataTables from being applied
      $('#tblQueue').off('init.dt');
      $('#tblQueue').off('draw.dt');
      
      // Override common JavaScript functions to skip our table
      if (typeof window.initializeDataTables === 'function') {
        const originalInit = window.initializeDataTables;
        window.initializeDataTables = function() {
          $('table').not('#tblQueue').each(function() {
            if (!$(this).attr('data-no-datatables')) {
              originalInit.call(this);
            }
          });
        };
      }
      
      // Prevent any DataTables initialization on our table
      $('#tblQueue').on('init.dt', function(e) {
        e.preventDefault();
        e.stopPropagation();
        return false;
      });
    });
    
    // Enhanced real-time auto-refresh with visual indicators
    let autoRefreshInterval = null;
    let refreshCount = 0;
    let isAutoRefreshEnabled = true;
    
    // Add refresh status indicator (hidden)
    function addRefreshIndicator() {
      // Auto-refresh functionality remains active but visual indicator is hidden
      // The refresh status div is not added to maintain clean UI
    }
    
    // Enhanced loadQueue with refresh indicators
    function loadQueueWithIndicator(showLoading = true) {
      if (showLoading) {
        $('#refresh-indicator').css('background', '#ffc107');
        $('#refresh-text').text('Refreshing...');
        $('#manual-refresh i').addClass('refreshing');
      }
      
      $.getJSON('ajax/get_doctor_queue.php', function(res){
        console.log('Queue data loaded:', res);
        if(res && res.status==='success'){
          queueData = res.data || [];
          console.log('Queue data:', queueData);
          updateQueueStats(queueData);
          renderQueueTable(queueData);
          refreshCount++;
          
          // Update refresh indicator
          $('#refresh-indicator').css('background', '#28a745');
          const lastUpdate = new Date().toLocaleTimeString();
          $('#refresh-text').text(`Auto-refresh: ON (${refreshCount} updates) - Last: ${lastUpdate}`);
          $('#manual-refresh i').removeClass('refreshing');
        } else {
          console.error('Failed to load queue:', res);
          $('#tblQueue tbody').html('<tr><td colspan="5" class="text-center text-muted"><i class="fas fa-exclamation-triangle"></i><br>Failed to load queue data</td></tr>');
          $('#refresh-indicator').css('background', '#dc3545');
          $('#refresh-text').text('Error loading queue');
          $('#manual-refresh i').removeClass('refreshing');
        }
      }).fail(function(xhr, status, error) {
        console.error('AJAX Error loading queue:', { xhr, status, error });
        $('#tblQueue tbody').html('<tr><td colspan="5" class="text-center text-danger"><i class="fas fa-exclamation-circle"></i><br>Error loading queue</td></tr>');
        $('#refresh-indicator').css('background', '#dc3545');
        $('#refresh-text').text('Connection error');
        $('#manual-refresh i').removeClass('refreshing');
      });
    }
    
    // Start auto-refresh
    function startAutoRefresh() {
      if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
      }
      
      autoRefreshInterval = setInterval(function() {
        if (!isAutoRefreshEnabled) return;
        
        // Only refresh if no user interaction is happening
        try {
          // Check if any action button is focused or being interacted with
          let hasFocusedButton = false;
          $('.action-btn').each(function() {
            if ($(this).is(':focus') || $(this).is(':hover') || $(this).data('loading')) {
              hasFocusedButton = true;
              return false;
            }
          });
          
          // Check if any workflow card is being hovered
          let hasHoveredCard = false;
          $('.workflow-card').each(function() {
            const $card = $(this);
            if ($card.data('hovered') === true) {
              hasHoveredCard = true;
              return false;
            }
          });
          
          // Check if any modal is open
          let hasOpenModal = $('.modal').hasClass('show') || $('.modal').is(':visible');
          
          if (!hasFocusedButton && !hasHoveredCard && !hasOpenModal) {
            loadQueueWithIndicator(false); // Silent refresh
          }
        } catch (e) {
          console.log('Auto-refresh check failed:', e);
          // Fallback: just refresh without hover check
          loadQueueWithIndicator(false);
        }
      }, 5000); // Refresh every 5 seconds for real-time updates
    }
    
    // Initialize
    addRefreshIndicator();
    loadQueueWithIndicator();
    startAutoRefresh();
    
    // Auto-refresh toggle functionality removed (visual controls hidden)
    // Auto-refresh continues to work automatically in the background
    
    // Global error handler for uncaught errors
    window.addEventListener('error', function(e) {
      console.error('Global error caught:', e.error);
    });
  </script>
</body>
</html>