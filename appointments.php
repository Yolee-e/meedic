<?php
include './config/connection.php';
include './common_service/common_functions.php';

$message = '';
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

$patients = getPatients($con);
$users = getUsers($con); // Get users for dropdown
$appointmentStatusOptions = getAppointmentStatus(); // For dropdown to filter appointments
?>

<!DOCTYPE html>
<html>

<head>
    <?php include './config/site_css_links.php'; ?>
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <title>Patient Queue</title>
    <style>
        /* Enhanced Appointments Styling */
        .appointments-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        
        .appointment-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: none;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .appointment-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
        }
        
        .appointment-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        /* Edit Modal Styling */
        .modal-header .btn-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            opacity: 0.8;
            transition: opacity 0.3s;
        }
        
        .modal-header .btn-close:hover {
            opacity: 1;
        }
        
        .modal-title {
            color: white;
            font-weight: 600;
        }
        
        .modal-body {
            padding: 2rem;
        }
        
        .modal-body .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .modal-body .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .modal-body .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0.2rem rgba(102, 126, 234, 0.25);
            outline: 0;
        }
        
        .modal-footer {
            background: #f8f9fa;
            padding: 1rem 2rem;
            border-top: 1px solid #dee2e6;
        }
        
        .modal-footer .btn {
            padding: 0.5rem 1rem;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.2s;
        }
        
        .modal-footer .btn-primary {
            background: #667eea;
            border-color: #667eea;
            color: white;
        }
        
        .modal-footer .btn-primary:hover {
            background: #5a67d8;
            border-color: #5a67d8;
            transform: translateY(-1px);
        }
        
        .modal-footer .btn-secondary {
            background: #6c757d;
            border-color: #6c757d;
            color: white;
        }
        
        .modal-footer .btn-secondary:hover {
            background: #5a6268;
            border-color: #5a6268;
            transform: translateY(-1px);
        }
        
        .action-btn {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 0.375rem;
            transition: all 0.15s ease-in-out;
        }
        
        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .appointment-body {
            padding: 25px;
        }
        
        /* Enhanced Form Styling */
        .form-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .form-section h4 {
            color: #495057;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        
        /* Enhanced Date/Time Pickers */
        .datetime-picker-group {
            position: relative;
        }
        
        .datetime-picker-group .input-group-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .datetime-picker-group .input-group-text:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .datetime-picker-group .form-control {
            border-right: none;
        }
        
        .datetime-picker-group .input-group-text {
            border-left: none;
        }
        
        /* Quick Schedule Buttons */
        .quick-schedule-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .quick-schedule-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .quick-schedule-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        .quick-schedule-btn.btn-outline-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        }
        
        .quick-schedule-btn.btn-outline-info:hover {
            box-shadow: 0 5px 15px rgba(23, 162, 184, 0.3);
        }
        
        /* Enhanced Dashboard Widgets */
        .dashboard-widgets {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .dashboard-widget {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-widget:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .dashboard-widget::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .widget-icon {
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
        
        .widget-icon.bg-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        }
        
        .widget-icon.bg-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        
        .widget-icon.bg-warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        }
        
        .widget-icon.bg-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        }
        
        .widget-title {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .widget-value {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
        }
        
        /* Enhanced Table Styling */
        .appointments-table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .table-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }
        
        .table-header h3 {
            margin: 0;
            font-weight: 600;
        }
        
        .table-filters {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-group label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
            font-size: 13px;
        }
        
        .filter-group .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 13px;
        }
        
        .clear-filters-btn {
            background: #dc3545;
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .clear-filters-btn:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        
        /* Enhanced Table */
        .appointments-table {
            margin: 0;
        }
        
        .appointments-table thead th {
            background: #f8f9fa;
            color: #495057;
            font-weight: 600;
            font-size: 13px;
            padding: 15px 12px;
            border: none;
            border-bottom: 2px solid #e9ecef;
        }
        
        .appointments-table tbody td {
            padding: 15px 12px;
            border: none;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }
        
        .appointments-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .appointments-table tbody tr.table-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-left: 4px solid #ffc107;
        }
        
        /* Enhanced Status Badges */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-badge.badge-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }
        
        .status-badge.badge-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .status-badge.badge-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
        }
        
        .status-badge.badge-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
        }
        
        .status-badge.badge-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        
        .status-badge.badge-warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: #212529;
        }
        
        /* Enhanced Action Buttons */
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 12px;
        }
        
        .action-btn.btn-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
        }
        
        .action-btn.btn-warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: #212529;
        }
        
        .action-btn.btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        
        .action-btn.btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        /* Enhanced Patient Links */
        .patient-link {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .patient-link:hover {
            color: #764ba2;
            text-decoration: none;
        }
        
        /* Enhanced Date/Time Display */
        .appointment-datetime {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .appointment-time {
            font-size: 12px;
            color: #6c757d;
        }
        
        .upcoming-badge {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
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
            .appointments-container {
                padding: 10px 0;
            }
            
            .appointment-body {
                padding: 15px;
            }
            
            .dashboard-widgets {
                grid-template-columns: 1fr;
            }
            
            .filter-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .quick-schedule-buttons {
                flex-direction: column;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
        
        /* DataTables Customization */
        .dataTables_wrapper .dataTables_paginate {
            float: right;
            text-align: right;
            padding-top: 0.25em;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            box-sizing: border-box;
            display: inline-block;
            min-width: 1.5em;
            padding: 0.5em 1em;
            margin-left: 2px;
            text-align: center;
            text-decoration: none !important;
            cursor: pointer;
            border: 1px solid transparent;
            border-radius: 6px;
            background: transparent;
            transition: all 0.3s ease;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            color: white !important;
            border: 1px solid #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            color: white !important;
            border: 1px solid #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            color: #666 !important;
            border: 1px solid transparent;
            background: transparent;
            cursor: default;
        }
        
        /* Remove DataTables sorting arrows */
        #appointments_table thead th.sorting,
        #appointments_table thead th.sorting_asc,
        #appointments_table thead th.sorting_desc {
            background-image: none !important;
            cursor: default !important;
        }
        
        #appointments_table thead th {
            position: relative;
            background-image: none !important;
        }
        
        #appointments_table thead th:hover {
            background-color: #f8f9fa !important;
            color: #495057 !important;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <?php include './config/header.php';
        include './config/sidebar.php'; ?>
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
         

            <!-- Main content -->
            <section class="content">
                <div class="appointments-container">
                    <div class="container-fluid">
                        <!-- Enhanced Appointment Form -->
                        <div class="appointment-card">
                            <div class="appointment-header">
                                <h3><i class="fas fa-calendar-plus"></i> Schedule New Queue</h3>
                                
                            </div>
                            
                            <div class="appointment-body">
                                <form id="schedule_appointment_form">
                                    <?php echo csrf_field(); ?>
                                    
                                    <!-- Patient Selection Section -->
                                    <div class="form-section">
                                        <h4><i class="fas fa-user"></i> Patient Information</h4>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="patient_id" class="form-label">Select Patient</label>
                                                    <select class="form-control select2" id="patient_id" name="patient_id" style="width: 100%;" required>
                                                        <option value="">-- Select Patient --</option>
                                                        <?php echo $patients; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="reason" class="form-label">Complaint </label>
                                                    <textarea class="form-control" id="reason" name="reason_for_visit" rows="3" placeholder="Enter the reason for the Patients Visit"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Date & Time Selection Section -->
                                    <div class="form-section">
                                        <h4><i class="fas fa-calendar-alt"></i>Schedule</h4>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Date</label>
                                                    <div class="datetime-picker-group">
                                                        <div class="input-group date" id="appointment_date_wrapper" data-target-input="nearest">
                                                            <input type="text" id="appointment_date" class="form-control datetimepicker-input" data-target="#appointment_date_wrapper" name="appointment_date" autocomplete="off" required />
                                                            <div class="input-group-append" data-target="#appointment_date_wrapper" data-toggle="datetimepicker">
                                                                <div class="input-group-text" title="Click to open date picker">
                                                                    <i class="fa fa-calendar"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Time</label>
                                                    <div class="datetime-picker-group">
                                                        <div class="input-group date" id="appointment_time_wrapper" data-target-input="nearest">
                                                            <input type="text" id="appointment_time" class="form-control datetimepicker-input" data-target="#appointment_time_wrapper" name="appointment_time" autocomplete="off" required />
                                                            <div class="input-group-append" data-target="#appointment_time_wrapper" data-toggle="datetimepicker">
                                                                <div class="input-group-text" title="Click to open time picker">
                                                                    <i class="fa fa-clock"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Quick Schedule Options -->
                                        <div class="form-group">
                                            <label class="form-label"></label>
                                            <div class="quick-schedule-buttons">
                                                <button type="button" class="quick-schedule-btn" id="schedule-now-btn">
                                                    <i class="fas fa-clock"></i>Now
                                                </button>
                                                <!--button type="button" class="quick-schedule-btn btn-outline-info" id="schedule-today-btn">
                                                    <i class="fas fa-calendar-day"></i> Set an Appointment
                                                </button-->
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                         Confirm
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Enhanced Appointments Management -->
                        <div class="appointment-card">
                            <div class="appointment-header">
                                <h3><i class="fas fa-calendar-alt"></i>Patients Queue</h3>
                                <p class="mb-0">View and manage all Patients</p>
                                <div class="mt-2">
                                    <span class="badge badge-info" id="user-role-badge">
                                        <?php 
                                        $roleNames = [1 => 'Admin', 2 => 'Doctor', 3 => 'Front Desk', 4 => 'Cashier'];
                                        $userRole = isset($_SESSION['user_type']) ? (int)$_SESSION['user_type'] : 1;
                                        echo $roleNames[$userRole] ?? 'User';
                                        ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="appointment-body">
                                <!-- User Role Information -->
                                <?php if (isset($_SESSION['user_type']) && (int)$_SESSION['user_type'] === 3): ?>
                                <div class="alert alert-info alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <h5><i class="icon fas fa-info"></i> Front Desk Permissions</h5>
                                    As a Front Desk user, you can view all appointments and cancel them if needed. You cannot edit or delete appointments.
                                </div>
                                <?php endif; ?>
                                
                                <!-- Enhanced Dashboard Widgets -->
                                <div class="dashboard-widgets">
                                    <div class="dashboard-widget">
                                        <div class="widget-icon bg-info">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                        <div class="widget-title">Today's Patients</div>
                                        <div class="widget-value" id="today-count">0</div>
                                    </div>
                                    <div class="dashboard-widget">
                                        <div class="widget-icon bg-success">
                                            <i class="fas fa-user-check"></i>
                                        </div>
                                        <div class="widget-title">Checked In</div>
                                        <div class="widget-value" id="checkedin-count">0</div>
                                    </div>
                                    <div class="dashboard-widget">
                                        <div class="widget-icon bg-warning">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div class="widget-title">Pending</div>
                                        <div class="widget-value" id="pending-count">0</div>
                                    </div>
                                    <div class="dashboard-widget">
                                        <div class="widget-icon bg-primary">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="widget-title">Completed</div>
                                        <div class="widget-value" id="completed-count">0</div>
                                    </div>
                                </div>
                                
                                <!-- Enhanced Search and Filter Controls -->
                                <div class="appointments-table-container">
                                    <div class="table-header">
                                        <h3><i class="fas fa-search"></i> Search & Filter Patients</h3>
                                    </div>
                                    
                                    <div class="table-filters">
                                        <div class="filter-row">
                                            <div class="filter-group">
                                                <label for="search_appointments">Search Patients</label>
                                                <input type="text" class="form-control" id="search_appointments" placeholder="Search by patient name, date, or status...">
                                            </div>
                                            <div class="filter-group">
                                                <label for="status_filter">Filter by Status</label>
                                                <select class="form-control" id="status_filter">
                                                    <option value="">All Statuses</option>
                                                    <option value="Scheduled">Scheduled</option>
                                                    <option value="Checked-In">Checked-In</option>
                                                    <option value="In Consultation">In Consultation</option>
                                                    <option value="Doctor Done">Doctor Done</option>
                                                    <option value="Completed">Completed</option>
                                                    <option value="Cancelled">Cancelled</option>
                                                    <option value="Rescheduled">Rescheduled</option>
                                                </select>
                                            </div>
                                            <div class="filter-group">
                                                <label for="date_filter">Filter by Date</label>
                                                <input type="date" class="form-control" id="date_filter">
                                            </div>
                                            <div class="filter-group">
                                                <label>&nbsp;</label>
                                                <button type="button" class="clear-filters-btn" id="clear_filters">
                                                    <i class="fas fa-times"></i> Clear Filters
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Enhanced Appointments Table -->
                                    <div class="table-responsive">
                                        <table id="appointments_table" class="table appointments-table">
                                            <thead>
                                                <tr>
                                                    <th>Patient Name</th>
                                                    <th>Date & Time</th>
                                                    <th>Complaints</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Appointments will be loaded here via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                </div><!-- /.container-fluid -->
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <?php include './config/site_js_links.php'; ?>

        <!-- DataTables  & Plugins -->
        <script src="plugins/datatables/jquery.dataTables.min.js"></script>
        <script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
        <script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
        <script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
        <script src="plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
        <script src="plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
        <script src="plugins/jszip/jszip.min.js"></script>
        <script src="plugins/pdfmake/pdfmake.min.js"></script>
        <script src="plugins/pdfmake/vfs_fonts.js"></script>
        <script src="plugins/datatables-buttons/js/buttons.html5.min.js"></script>
        <script src="plugins/datatables-buttons/js/buttons.print.min.js"></script>
        <script src="plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
        <!-- date-range-picker -->
        <script src="plugins/moment/moment.min.js"></script>
        <script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>


        <script>
            var isAdmin = <?php echo (isset($_SESSION['user_type']) && (int)$_SESSION['user_type']===1) ? 'true' : 'false'; ?>;
            var userType = <?php echo isset($_SESSION['user_type']) ? (int)$_SESSION['user_type'] : 1; ?>;
            var isFrontDesk = (userType === 3);
            var appointmentsData = []; // Store appointments data for filtering
            var dataTable = null;
            var dataTableInitialized = false;

            $(document).ready(function() {
                // Initialize Select2
                $('.select2').select2();

                // Initialize Date picker
                $('#appointment_date_wrapper').datetimepicker({
                    format: 'YYYY-MM-DD',
                    minDate: moment().subtract(1, 'day'), // Allow today and future dates
                    useCurrent: false,
                    allowInputToggle: true, // Allow toggle on icon click
                    showTodayButton: true,
                    showClear: true,
                    showClose: true,
                    icons: {
                        time: 'fa fa-time',
                        date: 'fa fa-calendar',
                        up: 'fa fa-chevron-up',
                        down: 'fa fa-chevron-down',
                        previous: 'fa fa-chevron-left',
                        next: 'fa fa-chevron-right',
                        today: 'fa fa-screenshot',
                        clear: 'fa fa-trash',
                        close: 'fa fa-remove'
                    }
                });
                
                // Initialize Time picker
                $('#appointment_time_wrapper').datetimepicker({
                    format: 'HH:mm',
                    useCurrent: false,
                    stepping: 1, // 1-minute intervals
                    allowInputToggle: true, // Allow toggle on icon click
                    showTodayButton: true,
                    showClear: true,
                    showClose: true,
                    icons: {
                        time: 'fa fa-time',
                        date: 'fa fa-calendar',
                        up: 'fa fa-chevron-up',
                        down: 'fa fa-chevron-down',
                        previous: 'fa fa-chevron-left',
                        next: 'fa fa-chevron-right',
                        today: 'fa fa-screenshot',
                        clear: 'fa fa-trash',
                        close: 'fa fa-remove'
                    }
                });
                
                // Add event handlers for date/time selection
                $('#appointment_date_wrapper').on('change.datetimepicker', function(e) {
                    const selectedDate = e.date;
                    if (selectedDate) {
                        console.log('Date selected:', selectedDate.format('YYYY-MM-DD'));
                    }
                });
                
                $('#appointment_time_wrapper').on('change.datetimepicker', function(e) {
                    const selectedTime = e.date;
                    if (selectedTime) {
                        console.log('Time selected:', selectedTime.format('HH:mm'));
                    }
                });
                
                // Show date picker when clicking date icon
                $('#appointment_date_wrapper .input-group-text').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    console.log('Date icon clicked - showing date picker');
                    $('#appointment_date_wrapper').datetimepicker('toggle');
                    
                    // Visual feedback
                    $(this).addClass('bg-primary text-white');
                    setTimeout(() => {
                        $(this).removeClass('bg-primary text-white');
                    }, 1000);
                });
                
                // Show time picker when clicking time icon
                $('#appointment_time_wrapper .input-group-text').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    console.log('Time icon clicked - showing time picker');
                    $('#appointment_time_wrapper').datetimepicker('toggle');
                    
                    // Visual feedback
                    $(this).addClass('bg-primary text-white');
                    setTimeout(() => {
                        $(this).removeClass('bg-primary text-white');
                    }, 1000);
                });
                
                // Show date picker when clicking date input field
                $('#appointment_date').on('click', function() {
                    console.log('Date input clicked - showing date picker');
                    $('#appointment_date_wrapper').datetimepicker('toggle');
                });
                
                // Show time picker when clicking time input field
                $('#appointment_time').on('click', function() {
                    console.log('Time input clicked - showing time picker');
                    $('#appointment_time_wrapper').datetimepicker('toggle');
                });
                
                // Validate appointment date and time
                function validateAppointmentDateTime() {
                    const selectedDate = $('#appointment_date').val();
                    const selectedTime = $('#appointment_time').val();
                    const now = moment();
                    const appointmentDateTime = moment(selectedDate + ' ' + selectedTime, 'YYYY-MM-DD HH:mm');
                    
                    // Allow current time and future times, but not past times
                    if (appointmentDateTime.isBefore(now, 'minute')) {
                        alert('Cannot schedule appointments in the past. Please select current time or a future time.');
                        return false;
                    }
                    return true;
                }

                // Function to get status badge HTML
                function getStatusBadge(status) {
                    const statusClasses = {
                        'Scheduled': 'badge-secondary',
                        'Checked-In': 'badge-success',
                        'In Consultation': 'badge-info',
                        'Doctor Done': 'badge-primary',
                        'Completed': 'badge-primary',
                        'Cancelled': 'badge-danger',
                        'Rescheduled': 'badge-warning',
                        'Follow-up check-up': 'badge-info'
                    };
                    const className = statusClasses[status] || 'badge-secondary';
                    
                    // Display "Doctor Done" as "Completed" but keep the same styling
                    const displayStatus = status === 'Doctor Done' ? 'Completed' : status;
                    
                    return `<span class="badge ${className}">${displayStatus}</span>`;
                }

                // Function to check if appointment is today or within the hour
                function isTodayOrUpcoming(appointmentDate, appointmentTime) {
                    const now = new Date();
                    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                    const appointmentDateTime = new Date(appointmentDate + 'T' + appointmentTime);
                    const appointmentDateOnly = new Date(appointmentDateTime.getFullYear(), appointmentDateTime.getMonth(), appointmentDateTime.getDate());
                    
                    // Check if it's today
                    if (appointmentDateOnly.getTime() === today.getTime()) {
                        return true;
                    }
                    
                    // Check if it's within the next hour
                    const oneHourFromNow = new Date(now.getTime() + (60 * 60 * 1000));
                    return appointmentDateTime <= oneHourFromNow && appointmentDateTime >= now;
                }

                // Function to load appointments
                function loadAppointments() {
                    // Only prevent if DataTable is already initialized AND has data
                    if (dataTableInitialized && $.fn.DataTable.isDataTable('#appointments_table') && $('#appointments_table tbody tr').length > 0) {
                        console.log('DataTable already initialized with data, skipping...');
                        return;
                    }
                    
                    console.log('Loading appointments...');
                    $.ajax({
                        url: 'ajax/get_appointments.php?t=' + Date.now(),
                        method: 'GET',
                        dataType: 'json',
                        cache: false, // Disable caching
                        success: function(response) {
                            console.log('Appointments API response:', response);
                            if (response.status === 'success') {
                                appointmentsData = Array.isArray(response.data) ? response.data : [];
                                console.log('Appointments data loaded:', appointmentsData.length, 'appointments');
                                console.log('Debug info:', response.debug);
                                
                                // Show debug info in the page
                                if (appointmentsData.length === 0) {
                                    console.warn('No appointments found. Debug info:', response.debug);
                                    $('#appointments_table tbody').html('<tr><td colspan="5" class="text-center text-warning"><i class="fas fa-exclamation-triangle"></i> No appointments found.</td></tr>');
                                }
                                
                                renderAppointmentsTable(appointmentsData);
                            } else {
                                console.error('API Error:', response.message);
                                alert('Error loading appointments: ' + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                            console.error('Response text:', xhr.responseText);
                            $('#appointments_table tbody').html('<tr><td colspan="5" class="text-center text-danger">Failed to load appointments. Check console for details.</td></tr>');
                        }
                    });
                }

                // Function to update dashboard widgets
                function updateDashboardWidgets(data) {
                    const today = new Date().toISOString().split('T')[0];
                    let todayCount = 0;
                    let checkedInCount = 0;
                    let pendingCount = 0;
                    let completedCount = 0;
                    
                    data.forEach(appointment => {
                        if (appointment.appointment_date === today) {
                            todayCount++;
                        }
                        
                        switch (appointment.status) {
                            case 'Checked-In':
                                checkedInCount++;
                                break;
                            case 'Scheduled':
                                pendingCount++;
                                break;
                            case 'Doctor Done':
                                completedCount++;
                                break;
                        }
                    });
                    
                    $('#today-count').text(todayCount);
                    $('#checkedin-count').text(checkedInCount);
                    $('#pending-count').text(pendingCount);
                    $('#completed-count').text(completedCount);
                }

                // Function to render appointments table
                function renderAppointmentsTable(data) {
                    console.log('Rendering appointments table with data:', data);
                    console.log('Data length:', data ? data.length : 0);
                    
                    // Update dashboard widgets
                    updateDashboardWidgets(data);
                    
                    let tableBody = '';
                    if (data.length === 0) {
                        tableBody = '<tr><td colspan="5" class="text-center text-muted"><i class="fas fa-calendar-times"></i><br>No appointments found.</td></tr>';
                    } else {
                        // Sort by date and time (latest first - most recent appointments at top)
                        data.sort(function(a, b) {
                            const dateA = new Date(a.appointment_date + 'T' + a.appointment_time);
                            const dateB = new Date(b.appointment_date + 'T' + b.appointment_time);
                            return dateB - dateA; // Reverse order - latest first
                        });

                        $.each(data, function(index, appointment) {
                            const rowId = (appointment.id || appointment.appointment_id || '');
                            const tableName = appointment.table_name || 'appointments';
                            const isUpcoming = isTodayOrUpcoming(appointment.appointment_date, appointment.appointment_time);
                            const isCompleted = appointment.status && (
                                appointment.status.toLowerCase() === 'completed' || 
                                appointment.status.toLowerCase() === 'doctor done' ||
                                appointment.status.toLowerCase() === 'cancelled'
                            );
                            const showUpcoming = isUpcoming && !isCompleted;
                            const rowClass = showUpcoming ? 'table-warning' : '';
                            const patientName = appointment.patient_name || '(Unknown)';
                            
                            tableBody += `
                                <tr class="${rowClass}" data-appointment-id="${rowId}" data-table-name="${tableName}">
                                    <td>
                                        <a href="patients.php?view=${appointment.patient_id}" class="patient-link">
                                            ${patientName}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="appointment-datetime">${appointment.appointment_date || ''}</div>
                                        <div class="appointment-time">${appointment.appointment_time || ''}</div>
                                        ${showUpcoming ? '<div class="upcoming-badge">Upcoming</div>' : ''}
                                    </td>
                                    <td>${appointment.reason_for_visit || 'N/A'}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            ${getStatusBadge(appointment.status || 'Scheduled')}
                                            <select class="form-control form-control-sm update-status ml-2" data-id="${rowId}" data-table="${tableName}" ${(!isAdmin && appointment.status === 'Completed') ? 'disabled' : ''} style="display: none;">
                                                ${populateStatusOptions(appointment.status || 'Scheduled')}
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="action-btn btn-info view-appointment" data-id="${rowId}" data-table="${tableName}" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            ${!isFrontDesk ? `
                                                <button class="action-btn btn-danger delete-appointment" data-id="${rowId}" data-table="${tableName}" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            ` : `
                                                <button class="action-btn btn-secondary cancel-appointment" data-id="${rowId}" data-table="${tableName}" title="Cancel Appointment">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            `}
                                        </div>
                                    </td>
                                </tr>`;
                        });
                    }
                    
                    // Check if DataTable already exists and destroy it properly
                    if ($.fn.DataTable.isDataTable('#appointments_table')) {
                        $('#appointments_table').DataTable().destroy();
                        dataTable = null;
                        dataTableInitialized = false;
                    }
                    
                    // Set the table body content
                    $('#appointments_table tbody').html(tableBody);
                    console.log('Table body set, rows:', $('#appointments_table tbody tr').length);
                    
                    // Initialize DataTable with proper configuration
                    dataTable = $('#appointments_table').DataTable({
                        "responsive": true,
                        "lengthChange": true,
                        "autoWidth": false,
                        "pageLength": 25,
                        "paging": true,
                        "pagingType": "full_numbers",
                        "order": [[2, "desc"]], // Sort by date/time column (latest first)
                        "ordering": false, // Disable sorting to remove arrows
                        "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
                        "dom": '<"row"<"col-sm-6"l><"col-sm-6"f>>' +
                               '<"row"<"col-sm-12"tr>>' +
                               '<"row"<"col-sm-5"i><"col-sm-7"p>>',
                        "language": {
                            "search": "Search:",
                            "lengthMenu": "Show _MENU_ entries",
                            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                            "infoEmpty": "Showing 0 to 0 of 0 entries",
                            "infoFiltered": "(filtered from _MAX_ total entries)",
                            "paginate": {
                                "first": "First",
                                "last": "Last",
                                "next": "Next",
                                "previous": "Previous"
                            }
                        },
                        "drawCallback": function(settings) {
                            // Ensure pagination controls are properly rendered
                            console.log('DataTable redrawn');
                        }
                    });
                    
                    // Add buttons to the table
                    dataTable.buttons().container().appendTo('#appointments_table_wrapper .col-md-6:eq(0)');
                    
                    // Mark as initialized
                    dataTableInitialized = true;
                    console.log('DataTable initialized successfully. Final row count:', $('#appointments_table tbody tr').length);
                }

                // Helper function to populate status options for dropdown
                function populateStatusOptions(currentStatus) {
                    let statuses;
                    if (isFrontDesk) {
                        // Front desk can only cancel appointments
                        statuses = ['Scheduled', 'Cancelled'];
                    } else {
                        // Admin and other roles can use all statuses
                        statuses = ['Scheduled', 'Checked-In', 'In Consultation', 'Doctor Done', 'Completed', 'Cancelled', 'Rescheduled', 'Follow-up check-up'];
                    }
                    
                    let options = '';
                    statuses.forEach(status => {
                        // Display "Doctor Done" as "Completed" in dropdown but keep the value as "Doctor Done"
                        const displayText = status === 'Doctor Done' ? 'Completed' : status;
                        options += `<option value="${status}" ${currentStatus === status ? 'selected' : ''}>${displayText}</option>`;
                    });
                    return options;
                }

                // Search and filter functionality
                function filterAppointments() {
                    const searchTerm = $('#search_appointments').val().toLowerCase();
                    const statusFilter = $('#status_filter').val();
                    const dateFilter = $('#date_filter').val();
                    
                    let filteredData = appointmentsData.filter(function(appointment) {
                        // Search filter
                        const matchesSearch = !searchTerm || 
                            (appointment.patient_name && appointment.patient_name.toLowerCase().includes(searchTerm)) ||
                            (appointment.appointment_date && appointment.appointment_date.includes(searchTerm)) ||
                            (appointment.status && appointment.status.toLowerCase().includes(searchTerm)) ||
                            (appointment.reason_for_visit && appointment.reason_for_visit.toLowerCase().includes(searchTerm));
                        
                        // Status filter
                        const matchesStatus = !statusFilter || appointment.status === statusFilter;
                        
                        // Date filter
                        const matchesDate = !dateFilter || appointment.appointment_date === dateFilter;
                        
                        return matchesSearch && matchesStatus && matchesDate;
                    });
                    
                    // Always re-render the table with filtered data
                    renderAppointmentsTable(filteredData);
                }

                // Event handlers for search and filters
                $('#search_appointments').on('keyup', function() {
                    filterAppointments();
                });

                $('#status_filter, #date_filter').on('change', function() {
                    filterAppointments();
                });

                $('#clear_filters').on('click', function() {
                    $('#search_appointments').val('');
                    $('#status_filter').val('');
                    $('#date_filter').val('');
                    filterAppointments();
                });


                // Handle form submission for scheduling appointments
                $('#schedule_appointment_form').on('submit', function(e) {
                    e.preventDefault();

                    // Validate date and time before submission
                    if (!validateAppointmentDateTime()) {
                        return;
                    }

                    const $form = $(this);
                    const $submitBtn = $form.find('button[type="submit"]');
                    const originalText = $submitBtn.html();
                    
                    // Show loading state
                    $submitBtn.prop('disabled', true).html('<div class="spinner" style="width: 16px; height: 16px; border-width: 2px;"></div> Processing...');
                    
                    const formData = $form.serialize();

                    $.ajax({
                        url: 'ajax/add_appointment.php',
                        method: 'POST',
                        dataType: 'json',
                        data: formData,
                        success: function(response) {
                            if (response.status === 'success') {
                                showCustomMessage('Appointment scheduled successfully!', 'success');
                                // Reload appointments without page refresh
                                loadAppointments();
                                // Reset form
                                $form[0].reset();
                                $('.select2').val(null).trigger('change');
                            } else {
                                showCustomMessage('Error scheduling appointment: ' + response.message, 'error');
                            }
                            
                            // Reset button
                            $submitBtn.prop('disabled', false).html(originalText);
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                            showCustomMessage('An error occurred while scheduling the appointment.', 'error');
                            
                            // Reset button
                            $submitBtn.prop('disabled', false).html(originalText);
                        }
                    });
                });

                // Handle status update
                $('#appointments_table').on('change', '.update-status', function() {
                    const appointmentId = $(this).data('id');
                    const newStatus = $(this).val();

                    // Front desk can only cancel appointments
                    if (isFrontDesk && newStatus !== 'Cancelled') {
                        alert('Front desk can only cancel appointments. Please select "Cancelled" status.');
                        $(this).val('Scheduled'); // Reset to original status
                        return;
                    }

                    $.ajax({
                        url: 'ajax/update_appointment_status.php',
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            appointment_id: appointmentId,
                            status: newStatus,
                            csrf_token: $('input[name="csrf_token"]').val()
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                alert('Appointment status updated successfully!');
                                loadAppointments(); // Reload appointments to reflect changes
                            } else {
                                alert('Error updating status: ' + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                            alert('An error occurred while updating the appointment status.');
                        }
                    });
                });

                // Handle view appointment
                $('#appointments_table').on('click', '.view-appointment', function() {
                    const appointmentId = $(this).data('id');
                    const appointment = appointmentsData.find(apt => (apt.id || apt.appointment_id) == appointmentId);
                    
                    if (appointment) {
                        const modal = `
                            <div class="modal fade" id="viewAppointmentModal" tabindex="-1" role="dialog">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Appointment Details</h5>
                                            <button type="button" class="close" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <strong>Patient Name:</strong><br>
                                                    <span>${appointment.patient_name || 'N/A'}</span>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Appointment Date:</strong><br>
                                                    <span>${appointment.appointment_date || 'N/A'}</span>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <strong>Appointment Time:</strong><br>
                                                    <span>${appointment.appointment_time || 'N/A'}</span>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Status:</strong><br>
                                                    ${getStatusBadge(appointment.status || 'Scheduled')}
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-12">
                                                    <strong>Reason for Visit:</strong><br>
                                                    <span>${appointment.reason_for_visit || 'N/A'}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        // Remove existing modal if any
                        $('#viewAppointmentModal').remove();
                        $('body').append(modal);
                        $('#viewAppointmentModal').modal('show');
                    }
                });

                // Handle edit appointment
                $('#appointments_table').on('click', '.edit-appointment', function() {
                    const appointmentId = $(this).data('id');
                    const appointment = appointmentsData.find(apt => (apt.id || apt.appointment_id) == appointmentId);
                    
                    if (appointment) {
                        // Create edit modal with form fields
                        const editModal = `
                            <div class="modal fade" id="editAppointmentModal" tabindex="-1" role="dialog">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="fas fa-edit me-2"></i>
                                                Edit Appointment
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </h5>
                                        </div>
                                        <div class="modal-body">
                                            <form id="editAppointmentForm">
                                                <input type="hidden" name="appointment_id" value="${appointment.id || appointment.appointment_id}">
                                                <input type="hidden" name="csrf_token" value="${$('input[name="csrf_token"]').val()}">
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="edit_patient_name" class="form-label">Patient Name</label>
                                                        <input type="text" id="edit_patient_name" name="patient_name" 
                                                               class="form-control" value="${appointment.patient_name || ''}" readonly>
                                                        <small class="form-text text-muted">Original patient for this appointment</small>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="edit_appointment_date" class="form-label">Appointment Date</label>
                                                        <input type="datetime-local" id="edit_appointment_date" name="appointment_date" 
                                                               class="form-control" value="${appointment.appointment_date || ''}">
                                                    </div>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="edit_appointment_time" class="form-label">Time</label>
                                                        <input type="time" id="edit_appointment_time" name="appointment_time" 
                                                               class="form-control" value="${appointment.appointment_time || ''}">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="edit_appointment_type" class="form-label">Type</label>
                                                        <select id="edit_appointment_type" name="appointment_type" class="form-control">
                                                            <option value="Consultation" ${appointment.appointment_type === 'Consultation' ? 'selected' : ''}>Consultation</option>
                                                            <option value="Follow-up" ${appointment.appointment_type === 'Follow-up' ? 'selected' : ''}>Follow-up</option>
                                                            <option value="Emergency" ${appointment.appointment_type === 'Emergency' ? 'selected' : ''}>Emergency</option>
                                                            <option value="Surgery" ${appointment.appointment_type === 'Surgery' ? 'selected' : ''}>Surgery</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="edit_doctor" class="form-label">Doctor</label>
                                                        <select id="edit_doctor" name="doctor" class="form-control">
                                                            <option value="">Select Doctor</option>
                                                            ${users.map(user => `<option value="${user.display_name}" ${appointment.doctor === user.display_name ? 'selected' : ''}>${user.display_name}</option>`).join('')}
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="edit_status" class="form-label">Status</label>
                                                        <select id="edit_status" name="status" class="form-control">
                                                            <option value="Scheduled" ${appointment.status === 'Scheduled' ? 'selected' : ''}>Scheduled</option>
                                                            <option value="Completed" ${appointment.status === 'Completed' ? 'selected' : ''}>Completed</option>
                                                            <option value="Cancelled" ${appointment.status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                                                            <option value="No-show" ${appointment.status === 'No-show' ? 'selected' : ''}>No-show</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-12 mb-3">
                                                        <label for="edit_notes" class="form-label">Notes</label>
                                                        <textarea id="edit_notes" name="notes" rows="3" class="form-control" 
                                                                  placeholder="Add any notes about this appointment">${appointment.notes || ''}</textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <button type="submit" class="btn btn-primary me-2">
                                                            <i class="fas fa-save me-2"></i>
                                                            Save Changes
                                                        </button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            <i class="fas fa-times me-2"></i>
                                                            Cancel
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                <i class="fas fa-times me-2"></i>
                                                Close
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        // Remove existing modal if any
                        $('#editAppointmentModal, #viewAppointmentModal').remove();
                        $('body').append(editModal);
                        $('#editAppointmentModal').modal('show');
                        
                        // Handle form submission
                        $('#editAppointmentForm').on('submit', function(e) {
                            e.preventDefault();
                            
                            const formData = $(this).serialize();
                            
                            $.ajax({
                                url: 'ajax/update_appointment.php',
                                method: 'POST',
                                data: formData,
                                dataType: 'json',
                                success: function(response) {
                                    if (response.status === 'success') {
                                        alert('Appointment updated successfully!');
                                        $('#editAppointmentModal').modal('hide');
                                        loadAppointments(); // Reload appointments
                                    } else {
                                        alert('Error updating appointment: ' + response.message);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    alert('Error updating appointment. Please try again.');
                                    console.error('AJAX Error:', error);
                                }
                            });
                        });
                    } else {
                        alert('Appointment not found!');
                    }
                });

                // Handle cancel appointment (Front Desk only)
                $('#appointments_table').on('click', '.cancel-appointment', function() {
                    const appointmentId = $(this).data('id');
                    const tableName = $(this).data('table');
                    const appointment = appointmentsData.find(apt => (apt.id || apt.appointment_id) == appointmentId);
                    
                    console.log('Cancel clicked - Appointment ID:', appointmentId, 'Table:', tableName);
                    console.log('Found appointment:', appointment);
                    
                    if (appointment) {
                        const patientName = appointment.patient_name || 'Unknown Patient';
                        if (confirm(`Are you sure you want to cancel the appointment for ${patientName}?`)) {
                            console.log('Sending cancel request for appointment ID:', appointmentId, 'in table:', tableName);
                            $.ajax({
                                url: 'ajax/update_appointment_status.php',
                                method: 'POST',
                                dataType: 'json',
                                data: {
                                    appointment_id: appointmentId,
                                    table_name: tableName,
                                    status: 'Cancelled',
                                    csrf_token: $('input[name="csrf_token"]').val()
                                },
                                success: function(response) {
                                    console.log('Cancel response:', response);
                                    if (response.status === 'success') {
                                        alert(`Appointment for ${patientName} cancelled successfully!`);
                                        loadAppointments(); // Reload appointments to reflect changes
                                    } else {
                                        alert('Error cancelling appointment: ' + response.message);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('AJAX Error:', status, error);
                                    alert('An error occurred while cancelling the appointment.');
                                }
                            });
                        }
                    } else {
                        alert('Appointment not found. Please refresh the page and try again.');
                    }
                });

                // Handle delete appointment
                $('#appointments_table').on('click', '.delete-appointment', function() {
                    const appointmentId = $(this).data('id');
                    if (!confirm('Are you sure you want to delete this appointment?')) return;
                    let adminPwd = prompt('Enter admin password to confirm deletion:');
                    if (adminPwd === null) return;
                    adminPwd = adminPwd.trim();
                    if (adminPwd === '') { alert('Password is required.'); return; }
                    $.ajax({
                        url: 'ajax/delete_appointment.php',
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            id: appointmentId,
                            admin_password: adminPwd,
                            csrf_token: $('input[name="csrf_token"]').val()
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                alert('Appointment deleted successfully!');
                                loadAppointments(); // Reload appointments list
                            } else {
                                alert('Error deleting appointment: ' + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                            alert('An error occurred while deleting the appointment.');
                        }
                    });
                });

                // Auto-check-in appointments function
                function autoCheckInAppointments() {
                    console.log('Running auto check-in...');
                    $.ajax({
                        url: 'ajax/auto_checkin_appointments.php',
                        method: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            console.log('Auto check-in response:', response);
                            if (response.status === 'success' && response.checked_in_count > 0) {
                                // Show notifications for auto-checked-in patients
                                response.notifications.forEach(function(notification) {
                                    showCustomMessage(notification.message, 'success');
                                });
                                // Reload appointments to show updated status
                                loadAppointments();
                                
                                // Update the auto-checkin status badge
                                $('#auto-checkin-status').removeClass('badge-success').addClass('badge-warning')
                                    .html('<i class="fas fa-sync-alt fa-spin"></i> Auto Check-in Active - ' + response.checked_in_count + ' patient(s) checked in');
                                
                                // Reset badge after 5 seconds
                                setTimeout(function() {
                                    $('#auto-checkin-status').removeClass('badge-warning').addClass('badge-success')
                                        .html('<i class="fas fa-clock"></i> Auto Check-in Active');
                                }, 5000);
                            } else {
                                console.log('No appointments to check in. Debug info:', response.debug_info);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Auto check-in error:', error);
                            console.error('Response:', xhr.responseText);
                            $('#auto-checkin-status').removeClass('badge-success').addClass('badge-danger')
                                .html('<i class="fas fa-exclamation-triangle"></i> Auto Check-in Error');
                        }
                    });
                }
                
                // Enhanced auto-refresh with multiple intervals
                // Check every 10 seconds for appointments that should be checked in
                setInterval(function() {
                    // Only run if no user interaction is happening
                    if (!$('#search_appointments').is(':focus') && !$('#status_filter').is(':focus') && !$('#date_filter').is(':focus')) {
                        autoCheckInAppointments();
                    }
                }, 10000); // 10 seconds for auto-check-in
                
                // Refresh appointments display every 30 seconds
                setInterval(function() {
                    // Only refresh if no user interaction is happening
                    if (!$('#search_appointments').is(':focus') && !$('#status_filter').is(':focus') && !$('#date_filter').is(':focus')) {
                        loadAppointments();
                    }
                }, 30000); // 30 seconds for display refresh

                // Update status filter dropdown based on user role
                function updateStatusFilter() {
                    const statusFilter = $('#status_filter');
                    if (isFrontDesk) {
                        // Front desk can only see Scheduled and Cancelled
                        statusFilter.html(`
                            <option value="">All Statuses</option>
                            <option value="Scheduled">Scheduled</option>
                            <option value="Cancelled">Cancelled</option>
                        `);
                    }
                }

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

                // Manual check-in removed by request

                // Enhanced quick schedule buttons
                $('#schedule-now-btn').on('click', function() {
                    const $btn = $(this);
                    const now = moment();
                    const currentDate = now.format('YYYY-MM-DD');
                    const currentTime = now.format('HH:mm');
                    
                    // Add loading state
                    $btn.prop('disabled', true).html('<div class="spinner" style="width: 12px; height: 12px; border-width: 2px;"></div> Setting...');
                    
                    setTimeout(() => {
                        $('#appointment_date').val(currentDate);
                        $('#appointment_time').val(currentTime);
                        
                        // Update datetimepickers
                        $('#appointment_date_wrapper').datetimepicker('date', now);
                        $('#appointment_time_wrapper').datetimepicker('date', now);
                        
                        // Success feedback
                        $btn.html('<i class="fas fa-check"></i> Set for Now');
                        $btn.removeClass('quick-schedule-btn').addClass('btn-success');
                        
                        setTimeout(() => {
                            $btn.prop('disabled', false).html('<i class="fas fa-clock"></i> Schedule for Now');
                            $btn.removeClass('btn-success').addClass('quick-schedule-btn');
                        }, 2000);
                    }, 500);
                });
                
                $('#schedule-today-btn').on('click', function() {
                    const $btn = $(this);
                    const today = moment();
                    const currentDate = today.format('YYYY-MM-DD');
                    
                    // Add loading state
                    $btn.prop('disabled', true).html('<div class="spinner" style="width: 12px; height: 12px; border-width: 2px;"></div> Setting...');
                    
                    setTimeout(() => {
                        $('#appointment_date').val(currentDate);
                        $('#appointment_date_wrapper').datetimepicker('date', today);
                        
                        // Success feedback
                        $btn.html('<i class="fas fa-check"></i> Set for Today');
                        $btn.removeClass('quick-schedule-btn btn-outline-info').addClass('btn-info');
                        
                        setTimeout(() => {
                            $btn.prop('disabled', false).html('<i class="fas fa-calendar-day"></i> Schedule for Today');
                            $btn.removeClass('btn-info').addClass('quick-schedule-btn btn-outline-info');
                        }, 2000);
                    }, 500);
                });

                // Enhanced keyboard shortcuts
                $(document).on('keydown', function(e) {
                    // Ctrl/Cmd + N to focus patient selection
                    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                        e.preventDefault();
                        $('#patient_id').select2('open');
                    }
                    
                    // Ctrl/Cmd + D to focus date picker
                    if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
                        e.preventDefault();
                        $('#appointment_date').focus();
                        $('#appointment_date_wrapper').datetimepicker('toggle');
                    }
                    
                    // Ctrl/Cmd + T to focus time picker
                    if ((e.ctrlKey || e.metaKey) && e.key === 't') {
                        e.preventDefault();
                        $('#appointment_time').focus();
                        $('#appointment_time_wrapper').datetimepicker('toggle');
                    }
                    
                    // Ctrl/Cmd + S to submit form
                    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                        e.preventDefault();
                        if ($('#schedule_appointment_form').length) {
                            $('#schedule_appointment_form').submit();
                        }
                    }
                    
                    // Escape to clear search
                    if (e.key === 'Escape') {
                        $('#search_appointments').val('').blur();
                        filterAppointments();
                    }
                });

                // Enhanced form validation with real-time feedback
                $('#patient_id').on('change', function() {
                    const $this = $(this);
                    if ($this.val()) {
                        $this.removeClass('is-invalid').addClass('is-valid');
                    } else {
                        $this.removeClass('is-valid').addClass('is-invalid');
                    }
                });
                
                $('#appointment_date, #appointment_time').on('change', function() {
                    const $this = $(this);
                    if ($this.val()) {
                        $this.removeClass('is-invalid').addClass('is-valid');
                    } else {
                        $this.removeClass('is-valid').addClass('is-invalid');
                    }
                });

                // Enhanced loading states for appointments
                function showLoadingState() {
                    $('#appointments_table tbody').html('<tr><td colspan="5" class="text-center"><div class="spinner"></div><br><small>Loading appointments...</small></td></tr>');
                }

                // Enhanced real-time auto-refresh for appointments
                let autoRefreshInterval = null;
                let refreshCount = 0;
                let isAutoRefreshEnabled = true;
                
                // Add refresh status indicator (hidden)
                function addRefreshIndicator() {
                    // Auto-refresh functionality remains active but visual indicator is hidden
                    // The refresh status div is not added to maintain clean UI
                }
                
                // Enhanced loadAppointments with refresh indicators
                function loadAppointmentsWithIndicator(showLoading = true) {
                    if (showLoading) {
                        $('#refresh-indicator').css('background', '#ffc107');
                        $('#refresh-text').text('Refreshing...');
                        $('#manual-refresh i').addClass('refreshing');
                    }
                    
                    // Only prevent if DataTable is already initialized AND has data
                    if (dataTableInitialized && $.fn.DataTable.isDataTable('#appointments_table') && $('#appointments_table tbody tr').length > 0 && !showLoading) {
                        console.log('DataTable already initialized with data, skipping silent refresh...');
                        return;
                    }
                    
                    $.ajax({
                        url: 'ajax/get_appointments.php',
                        method: 'GET',
                        dataType: 'json',
                        cache: false,
                        success: function(response) {
                            console.log('Appointments API response:', response);
                            if (response.status === 'success') {
                                appointmentsData = Array.isArray(response.data) ? response.data : [];
                                console.log('Appointments data loaded:', appointmentsData);
                                updateDashboardWidgets(appointmentsData);
                                renderAppointmentsTable(appointmentsData);
                                refreshCount++;
                                
                                // Update refresh indicator
                                $('#refresh-indicator').css('background', '#28a745');
                                const lastUpdate = new Date().toLocaleTimeString();
                                $('#refresh-text').text(`Auto-refresh: ON (${refreshCount} updates) - Last: ${lastUpdate}`);
                                $('#manual-refresh i').removeClass('refreshing');
                            } else {
                                console.error('Error loading appointments:', response.message);
                                $('#appointments_table tbody').html('<tr><td colspan="5" class="text-center text-muted"><i class="fas fa-exclamation-triangle"></i><br>Failed to load appointments</td></tr>');
                                $('#refresh-indicator').css('background', '#dc3545');
                                $('#refresh-text').text('Error loading appointments');
                                $('#manual-refresh i').removeClass('refreshing');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                            $('#appointments_table tbody').html('<tr><td colspan="5" class="text-center text-danger">Failed to load appointments. Check console for details.</td></tr>');
                            $('#refresh-indicator').css('background', '#dc3545');
                            $('#refresh-text').text('Connection error');
                            $('#manual-refresh i').removeClass('refreshing');
                        }
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
                            // Check if any form is being filled
                            let hasActiveForm = false;
                            $('input, select, textarea').each(function() {
                                if ($(this).is(':focus') || $(this).val() !== '') {
                                    hasActiveForm = true;
                                    return false;
                                }
                            });
                            
                            // Check if any modal is open
                            let hasOpenModal = $('.modal').hasClass('show') || $('.modal').is(':visible');
                            
                            // Check if any button is being interacted with
                            let hasActiveButton = false;
                            $('.btn').each(function() {
                                if ($(this).is(':focus') || $(this).is(':hover') || $(this).data('loading')) {
                                    hasActiveButton = true;
                                    return false;
                                }
                            });
                            
                            if (!hasActiveForm && !hasOpenModal && !hasActiveButton) {
                                loadAppointmentsWithIndicator(false); // Silent refresh
                            }
                        } catch (e) {
                            console.log('Auto-refresh check failed:', e);
                            // Fallback: just refresh without interaction check
                            loadAppointmentsWithIndicator(false);
                        }
                    }, 8000); // Refresh every 8 seconds for appointments
                }
                
                // Initialize auto-refresh
                addRefreshIndicator();
                startAutoRefresh();
                
                // Auto-refresh toggle functionality removed (visual controls hidden)
                // Auto-refresh continues to work automatically in the background
                
                // Enhanced keyboard shortcuts for appointments
                $(document).on('keydown', function(e) {
                    // Ctrl/Cmd + R to refresh appointments
                    if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                        e.preventDefault();
                        loadAppointmentsWithIndicator(true);
                        showCustomMessage('Appointments refreshed', 'info');
                    }
                    
                    // Ctrl/Cmd + T to toggle auto-refresh
                    if ((e.ctrlKey || e.metaKey) && e.key === 't') {
                        e.preventDefault();
                        $('#toggle-refresh').click();
                        const status = isAutoRefreshEnabled ? 'enabled' : 'disabled';
                        showCustomMessage(`Auto-refresh ${status}`, 'info');
                    }
                });

                // Initial load of appointments when the page is ready
                updateStatusFilter();
                // Add a small delay to ensure DOM is fully ready
                setTimeout(function() {
                    showLoadingState();
                    loadAppointmentsWithIndicator(true);
                }, 200);
            });
        </script>

    </div>
</body>

</html>
