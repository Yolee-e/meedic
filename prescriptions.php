<?php
include './config/connection.php';
include './common_service/common_functions.php';

$message = '';
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Get user type for permission checking
$userType = isset($_SESSION['user_type']) ? (int)$_SESSION['user_type'] : 1;
$canCreatePrescription = ($userType == 1 || $userType == 2); // Admin or Doctor only

// Handle search functionality
$searchTerm = '';
$whereConditions = [];
$params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = trim($_GET['search']);
    $searchParam = '%' . $searchTerm . '%';
    
    // Add search conditions for patient name, doctor name, and other details
    $whereConditions[] = "
        (pt.patient_name LIKE ? OR 
         u.display_name LIKE ? OR 
         p.chief_complaint LIKE ? OR 
         p.assessment_plan LIKE ?)
    ";
    $params = array_fill(0, 4, $searchParam);
}

try {
    // Build the query with optional search conditions
    $baseQuery = "
        SELECT p.*, pt.patient_name, u.display_name as doctor_name
        FROM prescriptions p 
        JOIN patients pt ON p.patient_id = pt.id 
        JOIN users u ON p.doctor_id = u.id
    ";
    
    if (!empty($whereConditions)) {
        $baseQuery .= " WHERE " . implode(' OR ', $whereConditions);
    }
    
    $baseQuery .= " ORDER BY p.created_at DESC";
    
    $stmt = $con->prepare($baseQuery);
    $stmt->execute($params);
    $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $ex) {
    $message = "Error loading prescriptions: " . $ex->getMessage();
    $prescriptions = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './config/site_css_links.php'; ?>
    <?php include './config/data_tables_css.php'; ?>
    <title>Prescriptions - Medical Care and Information System</title>
    <style>
        /* Enhanced Prescriptions List Styling */
        .prescription-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }
        
        /* Search Functionality Styling */
        .search-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .search-input {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: 0;
        }
        
        .search-btn {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            color: white;
            padding: 1px 4px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .search-btn:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }
        
        .clear-btn {
            background: #6c757d;
            border: none;
            color: white;
            padding: 1px 4px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .clear-btn:hover {
            background: #5a6268;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        }
        
        .search-results-alert {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            border: 1px solid #b8daff;
            color: #0c5460;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        
        .search-highlight {
            background: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: 600;
        }
        
        .prescription-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #007bff, #28a745, #ffc107, #dc3545);
        }
        
        .prescription-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .prescription-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 20px;
            border-radius: 12px 12px 0 0;
            position: relative;
            overflow: hidden;
        }
        
        .prescription-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
        }
        
        @keyframes shimmer {
            0%, 100% { transform: translateX(-100%) translateY(-100%) rotate(0deg); }
            50% { transform: translateX(0%) translateY(0%) rotate(180deg); }
        }
        
        .prescription-header h5 {
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
        }
        
        .btn-view-prescription {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            border-radius: 6px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(40,167,69,0.3);
            transition: all 0.3s ease;
        }
        
        .btn-view-prescription:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40,167,69,0.4);
            background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
        }
        
        .btn-print-prescription {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border: none;
            color: white;
            border-radius: 6px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(23,162,184,0.3);
            transition: all 0.3s ease;
        }
        
        .btn-print-prescription:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(23,162,184,0.4);
            background: linear-gradient(135deg, #138496 0%, #17a2b8 100%);
        }
        
        .prescription-info {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 0 0 12px 12px;
        }
        
        .medicine-count {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 700;
            box-shadow: 0 2px 4px rgba(25,118,210,0.2);
            border: 1px solid rgba(25,118,210,0.1);
        }
        
        .prescription-date {
            color: #6c757d;
            font-size: 0.9em;
            font-weight: 500;
        }
        
        .prescription-doctor {
            color: #495057;
            font-weight: 600;
        }
        
        .chief-complaint-preview {
            background: rgba(0,123,255,0.1);
            border-left: 3px solid #007bff;
            padding: 8px 12px;
            border-radius: 4px;
            margin-top: 10px;
            font-size: 0.9em;
            color: #495057;
        }
        
        .btn-group .btn {
            flex: 1;
            margin: 0 2px;
        }
        
        .btn-group .btn:first-child {
            margin-left: 0;
        }
        
        .btn-group .btn:last-child {
            margin-right: 0;
        }
        
        /* Enhanced Card Header */
        .card-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border-bottom: none;
            border-radius: 12px 12px 0 0;
            padding: 20px 25px;
        }
        
        .card-header h3 {
            margin: 0;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .card-header .card-tools {
            margin-top: 0;
        }
        
        /* Enhanced Card Body */
        .card-body {
            padding: 25px;
        }
        
        /* New Prescription Button */
        .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0,123,255,0.3);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,123,255,0.4);
            background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
        }
        
        /* Empty State Styling */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            margin: 20px 0;
        }
        
        .empty-state i {
            color: #6c757d;
            margin-bottom: 20px;
        }
        
        .empty-state h4 {
            color: #495057;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #6c757d;
            margin-bottom: 25px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .prescription-card {
                margin-bottom: 15px;
            }
            
            .prescription-header {
                padding: 15px;
            }
            
            .prescription-info {
                padding: 15px;
            }
            
            .btn-group .btn {
                font-size: 0.8em;
                padding: 6px 8px;
            }
        }
        
        /* Loading Animation */
        .prescription-card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Staggered Animation */
        .prescription-card:nth-child(1) { animation-delay: 0.1s; }
        .prescription-card:nth-child(2) { animation-delay: 0.2s; }
        .prescription-card:nth-child(3) { animation-delay: 0.3s; }
        .prescription-card:nth-child(4) { animation-delay: 0.4s; }
        .prescription-card:nth-child(5) { animation-delay: 0.5s; }
        .prescription-card:nth-child(6) { animation-delay: 0.6s; }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <?php include './config/header.php';
        include './config/sidebar.php'; ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
           <h1>prescriptions</h1>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- Action buttons and Search -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php if ($canCreatePrescription): ?>
                                <a href="new_prescription.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> New Prescription
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <form method="GET" class="d-flex">
                                <input type="text" name="search" class="form-control search-input me-2" 
                                       placeholder="Search prescriptions..." 
                                       value="<?php echo htmlspecialchars($searchTerm); ?>">
                                <button type="submit" class="btn search-btn">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <?php if (!empty($searchTerm)): ?>
                                    <a href="prescriptions.php" class="btn clear-btn ms-2">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                    
                    <?php if (!empty($searchTerm)): ?>
                        <div class="search-results-alert alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <h5><i class="fas fa-search"></i> Search Results</h5>
                            Showing prescriptions matching: <strong class="search-highlight"><?php echo htmlspecialchars($searchTerm); ?></strong>
                            (<?php echo count($prescriptions); ?> results found)
                        </div>
                    <?php endif; ?>

                    <?php if ($message): ?>
                        <div class="alert alert-info alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <h5><i class="icon fas fa-info"></i> Notice</h5>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <div class="card card-outline card-primary shadow">
                        <div class="card-header">
                            <h3 class="card-title">All Prescriptions</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($prescriptions)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-prescription-bottle-alt fa-3x mb-3"></i>
                                    <h4>No Prescriptions Found</h4>
                                    <?php if ($canCreatePrescription): ?>
                                        <p>Start by creating a new prescription for a patient.</p>
                                        <a href="new_prescription.php" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Create First Prescription
                                        </a>
                                    <?php else: ?>
                                        <p>No prescriptions have been created yet.</p>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($prescriptions as $prescription): ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card prescription-card">
                                                <div class="prescription-header">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h5 class="mb-0">
                                                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($prescription['patient_name']); ?>
                                                            </h5>
                                                            <small>Prescription #<?php echo str_pad($prescription['id'], 6, '0', STR_PAD_LEFT); ?></small>
                                                        </div>
                                                        <div class="text-right">
                                                            <div class="medicine-count">
                                                                <i class="fas fa-pills"></i> 
                                                                <?php
                                                                // Count medicines for this prescription
                                                                $stmt = $con->prepare("SELECT COUNT(*) as count FROM prescription_medicines WHERE prescription_id = ?");
                                                                $stmt->execute([$prescription['id']]);
                                                                $medicine_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                                                                echo $medicine_count;
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="prescription-info">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <strong>Date:</strong><br>
                                                            <span class="prescription-date"><?php echo date('M j, Y', strtotime($prescription['consultation_date'])); ?></span>
                                                        </div>
                                                        <div class="col-6">
                                                            <strong>Doctor:</strong><br>
                                                            <span class="prescription-doctor"><?php echo htmlspecialchars($prescription['doctor_name']); ?></span>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if (!empty($prescription['chief_complaint'])): ?>
                                                    <div class="chief-complaint-preview">
                                                        <strong>Chief Complaint:</strong><br>
                                                        <span><?php echo htmlspecialchars(substr($prescription['chief_complaint'], 0, 100)); ?><?php echo strlen($prescription['chief_complaint']) > 100 ? '...' : ''; ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="mt-3">
                                                        <div class="btn-group w-100" role="group">
                                                            <a href="view_prescription.php?id=<?php echo $prescription['id']; ?>" 
                                                               class="btn btn-view-prescription btn-sm">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                            <a href="print_prescription.php?id=<?php echo $prescription['id']; ?>" 
                                                               class="btn btn-print-prescription btn-sm" target="_blank">
                                                                <i class="fas fa-print"></i> Print
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <!-- /.content-wrapper -->

        <?php include './config/site_js_links.php'; ?>
        <?php include './config/data_tables_js.php'; ?>

        <script>
            showMenuSelected("#mnu_prescriptions", "#mi_prescriptions");

            var message = '<?php echo $message; ?>';
            if(message !== '') {
                showCustomMessage(message);
            }
        </script>
    </div>
</body>
</html>
