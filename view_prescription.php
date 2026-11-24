<?php
include './config/connection.php';
include './common_service/common_functions.php';

$prescription_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($prescription_id <= 0) {
    header('Location: prescriptions.php');
    exit;
}

try {
    // Get prescription details
    $stmt = $con->prepare("
        SELECT p.*, pt.patient_name, pt.date_of_birth, pt.gender, pt.phone_number, pt.address,
               u.display_name as doctor_name
        FROM prescriptions p 
        JOIN patients pt ON p.patient_id = pt.id 
        JOIN users u ON p.doctor_id = u.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$prescription_id]);
    $prescription = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$prescription) {
        header('Location: prescriptions.php');
        exit;
    }

    // Get prescription medicines
    $stmt = $con->prepare("SELECT * FROM prescription_medicines WHERE prescription_id = ? ORDER BY id ASC");
    $stmt->execute([$prescription_id]);
    $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate patient age
    $birthDate = new DateTime($prescription['date_of_birth']);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;

} catch (PDOException $ex) {
    die("Error loading prescription: " . $ex->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './config/site_css_links.php'; ?>
    <title>View Prescription - Medical Care and Information System</title>
    <style>
        /* Enhanced View Prescription Styling */
        .prescription-detail-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            overflow: hidden;
            position: relative;
        }
        
        .prescription-detail-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #007bff, #28a745, #ffc107, #dc3545);
        }
        
        .prescription-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 25px;
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
        
        .prescription-header h2 {
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
        }
        
        .prescription-content {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 25px;
            border-radius: 0 0 12px 12px;
        }
        
        .medicine-item {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid #e3f2fd;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .medicine-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #007bff, #28a745);
        }
        
        .medicine-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .medicine-name {
            font-weight: 700;
            color: #007bff;
            font-size: 16px;
            margin-bottom: 8px;
        }
        
        .medicine-details {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .schedule-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }
        
        .schedule-badge {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(25,118,210,0.2);
            border: 1px solid rgba(25,118,210,0.1);
            transition: all 0.3s ease;
        }
        
        .schedule-badge:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(25,118,210,0.3);
        }
        
        .assessment-plan {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #ffc107;
            border-radius: 12px;
            padding: 25px;
            margin-top: 25px;
            box-shadow: 0 4px 15px rgba(255,193,7,0.2);
            transition: all 0.3s ease;
        }
        
        .assessment-plan:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255,193,7,0.3);
        }
        
        .assessment-plan h4 {
            color: #856404;
            font-weight: 700;
            margin-bottom: 15px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .patient-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .info-item {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .info-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .info-label {
            font-weight: 700;
            color: #495057;
            margin-bottom: 8px;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            color: #6c757d;
            font-size: 1.1em;
            font-weight: 500;
        }
        
        /* Action Buttons */
        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 10px 20px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        
        .btn-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border: none;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            border: none;
        }
        
        /* Modal Enhancements */
        .modal-content {
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border-radius: 12px 12px 0 0;
        }
        
        .modal-title {
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .prescription-header {
                padding: 20px;
            }
            
            .prescription-content {
                padding: 20px;
            }
            
            .medicine-item {
                padding: 15px;
            }
            
            .patient-info-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }
        
        /* Loading Animation */
        .prescription-detail-card {
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
            <?php echo getContentHeader(''); ?>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- Action buttons -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#prescriptionPreviewModal">
                                <i class="fas fa-eye"></i> Preview Prescription
                            </button>
                            <a href="print_prescription.php?id=<?php echo $prescription_id; ?>" 
                               class="btn btn-success" target="_blank">
                                <i class="fas fa-print"></i> Print Prescription
                            </a>
                            <a href="prescriptions.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Prescriptions
                            </a>
                        </div>
                    </div>

                    <!-- Prescription Details -->
                    <div class="card prescription-detail-card">
                        <div class="prescription-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="mb-0">
                                        <i class="fas fa-prescription-bottle-alt"></i> 
                                        Prescription #<?php echo str_pad($prescription_id, 6, '0', STR_PAD_LEFT); ?>
                                    </h2>
                                    <p class="mb-0">Created on <?php echo date('F j, Y \a\t g:i A', strtotime($prescription['created_at'])); ?></p>
                                </div>
                                <div class="text-right">
                                    <div class="badge badge-light" style="font-size: 14px;">
                                        <?php echo count($medicines); ?> Medicine<?php echo count($medicines) !== 1 ? 's' : ''; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="prescription-content">
                            <!-- Patient Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4><i class="fas fa-user"></i> Patient Information</h4>
                                    <div class="patient-info-grid">
                                        <div class="info-item">
                                            <div class="info-label">Patient Name</div>
                                            <div class="info-value"><?php echo htmlspecialchars($prescription['patient_name']); ?></div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Age</div>
                                            <div class="info-value"><?php echo $age; ?> years</div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Gender</div>
                                            <div class="info-value"><?php echo htmlspecialchars($prescription['gender']); ?></div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Phone Number</div>
                                            <div class="info-value"><?php echo htmlspecialchars($prescription['phone_number']); ?></div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Address</div>
                                            <div class="info-value"><?php echo htmlspecialchars($prescription['address']); ?></div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Consultation Date</div>
                                            <div class="info-value"><?php echo date('F j, Y', strtotime($prescription['consultation_date'])); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Chief Complaint and S/O -->
                            <?php if (!empty($prescription['chief_complaint']) || !empty($prescription['subjective_objective'])): ?>
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4><i class="fas fa-clipboard-list"></i> Consultation Details</h4>
                                    <?php if (!empty($prescription['chief_complaint'])): ?>
                                    <div class="info-item mb-3">
                                        <div class="info-label">Chief Complaint (CC)</div>
                                        <div class="info-value"><?php echo nl2br(htmlspecialchars($prescription['chief_complaint'])); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($prescription['subjective_objective'])): ?>
                                    <div class="info-item">
                                        <div class="info-label">S/O (Subjective/Objective Notes)</div>
                                        <div class="info-value"><?php echo nl2br(htmlspecialchars($prescription['subjective_objective'])); ?></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Prescribed Medicines -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4><i class="fas fa-pills"></i> Prescribed Medicines</h4>
                                    <?php if (empty($medicines)): ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> No medicines prescribed.
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($medicines as $index => $medicine): ?>
                                        <?php 
                                        // Get medicine details from medicines table
                                        $medicineStmt = $con->prepare("SELECT generic_name, brand_name FROM medicines WHERE medicine_name = ?");
                                        $medicineStmt->execute([$medicine['medicine_name']]);
                                        $medicineDetails = $medicineStmt->fetch(PDO::FETCH_ASSOC);
                                        $displayName = $medicineDetails ? $medicineDetails['generic_name'] . ' (' . $medicineDetails['brand_name'] . ')' : $medicine['medicine_name'];
                                        ?>
                                        <div class="medicine-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="medicine-name">
                                                        <?php echo $index + 1; ?>. <?php echo htmlspecialchars($displayName); ?>
                                                    </div>
                                                    <div class="medicine-details">
                                                        <strong>Quantity:</strong> <?php echo htmlspecialchars($medicine['quantity']); ?> | 
                                                        <strong>Dosage:</strong> <?php echo htmlspecialchars($medicine['dosage']); ?>
                                                    </div>
                                                    <div class="schedule-badges">
                                                        <?php 
                                                        $schedules = explode(', ', $medicine['schedule']);
                                                        foreach ($schedules as $schedule): 
                                                        ?>
                                                            <span class="schedule-badge"><?php echo htmlspecialchars(trim($schedule)); ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Assessment & Plan -->
                            <div class="assessment-plan">
                                <h4><i class="fas fa-clipboard-check"></i> Assessment & Plan (A/P)</h4>
                                <p><?php echo nl2br(htmlspecialchars($prescription['assessment_plan'])); ?></p>
                            </div>

                            <!-- Doctor Information -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="info-item">
                                        <div class="info-label">Prescribed by</div>
                                        <div class="info-value"><?php echo htmlspecialchars($prescription['doctor_name']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <!-- /.content-wrapper -->

        <?php include './config/site_js_links.php'; ?>

        <!-- Prescription Preview Modal -->
        <div class="modal fade" id="prescriptionPreviewModal" tabindex="-1" role="dialog" aria-labelledby="prescriptionPreviewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="prescriptionPreviewModalLabel">
                            <i class="fas fa-prescription-bottle-alt"></i> Prescription Preview
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="background: white; font-family: 'Times New Roman', serif;">
                        <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px;">
                            <div style="font-size: 18px; font-weight: bold; margin-bottom: 5px;">ST. RITA FAMILY CLINIC</div>
                            <div style="font-size: 11px; color: #666;">
                                Medical Care and Information System<br>
                                Prescription #<?php echo str_pad($prescription_id, 6, '0', STR_PAD_LEFT); ?>
                            </div>
                        </div>

                        <div style="font-size: 16px; font-weight: bold; text-align: center; margin: 20px 0; text-decoration: underline;">PRESCRIPTION</div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div style="border: 1px solid #000; padding: 10px;">
                                <div><strong>Patient:</strong> <?php echo htmlspecialchars($prescription['patient_name']); ?></div>
                                <div><strong>Age:</strong> <?php echo $age; ?> years</div>
                                <div><strong>Gender:</strong> <?php echo htmlspecialchars($prescription['gender']); ?></div>
                            </div>
                            <div style="border: 1px solid #000; padding: 10px;">
                                <div><strong>Date:</strong> <?php echo date('F j, Y', strtotime($prescription['consultation_date'])); ?></div>
                                <div><strong>Phone:</strong> <?php echo htmlspecialchars($prescription['phone_number']); ?></div>
                                <div><strong>Address:</strong> <?php echo htmlspecialchars($prescription['address']); ?></div>
                            </div>
                        </div>

                        <?php if (!empty($prescription['chief_complaint']) || !empty($prescription['subjective_objective'])): ?>
                        <div style="border: 1px solid #000; padding: 10px; margin: 20px 0;">
                            <?php if (!empty($prescription['chief_complaint'])): ?>
                            <div style="font-weight: bold; text-decoration: underline; margin-bottom: 10px;">Chief Complaint (CC):</div>
                            <div><?php echo nl2br(htmlspecialchars($prescription['chief_complaint'])); ?></div>
                            <?php endif; ?>
                            
                            <?php if (!empty($prescription['subjective_objective'])): ?>
                            <div style="font-weight: bold; text-decoration: underline; margin-bottom: 10px;">S/O (Subjective/Objective):</div>
                            <div><?php echo nl2br(htmlspecialchars($prescription['subjective_objective'])); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <div style="margin: 20px 0;">
                            <div style="font-weight: bold; text-decoration: underline; margin-bottom: 10px;">PRESCRIBED MEDICINES:</div>
                            <?php if (empty($medicines)): ?>
                                <div style="text-align: center; font-style: italic; color: #666;">No medicines prescribed.</div>
                            <?php else: ?>
                                <?php foreach ($medicines as $index => $medicine): ?>
                                <?php 
                                $medicineStmt = $con->prepare("SELECT generic_name, brand_name FROM medicines WHERE medicine_name = ?");
                                $medicineStmt->execute([$medicine['medicine_name']]);
                                $medicineDetails = $medicineStmt->fetch(PDO::FETCH_ASSOC);
                                $displayName = $medicineDetails ? $medicineDetails['generic_name'] . ' (' . $medicineDetails['brand_name'] . ')' : $medicine['medicine_name'];
                                ?>
                                <div style="margin-bottom: 15px; padding: 10px; border: 1px solid #ccc;">
                                    <div style="font-weight: bold; font-size: 13px; margin-bottom: 5px;">
                                        <?php echo $index + 1; ?>. <?php echo htmlspecialchars($displayName); ?>
                                    </div>
                                    <div style="font-size: 11px; margin-bottom: 5px;">
                                        <strong>Quantity:</strong> <?php echo htmlspecialchars($medicine['quantity']); ?> | 
                                        <strong>Dosage:</strong> <?php echo htmlspecialchars($medicine['dosage']); ?>
                                    </div>
                                    <div style="font-weight: bold; color: #000;">
                                        <strong>Schedule:</strong> <?php echo htmlspecialchars($medicine['schedule']); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($prescription['assessment_plan'])): ?>
                        <div style="border: 1px solid #000; padding: 10px; margin: 20px 0;">
                            <div style="font-weight: bold; text-decoration: underline; margin-bottom: 10px;">Assessment & Plan (A/P):</div>
                            <div><?php echo nl2br(htmlspecialchars($prescription['assessment_plan'])); ?></div>
                        </div>
                        <?php endif; ?>

                        <div style="margin-top: 40px; text-align: right;">
                            <div><strong>Prescribed by:</strong> <?php echo htmlspecialchars($prescription['doctor_name']); ?></div>
                            <div style="border-bottom: 1px solid #000; width: 200px; margin-left: auto; margin-top: 30px;"></div>
                            <div style="text-align: center; margin-top: 5px; font-size: 10px;">Doctor's Signature</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <a href="print_prescription.php?id=<?php echo $prescription_id; ?>" class="btn btn-success" target="_blank">
                            <i class="fas fa-print"></i> Print Prescription
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <script>
            showMenuSelected("#mnu_prescriptions", "#mi_prescriptions");
        </script>
    </div>
</body>
</html>
