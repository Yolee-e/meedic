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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription Print - Medical Care System</title>
    <style>
        @media print {
            body { 
                margin: 0; 
                padding: 10px;
                min-height: auto;
                background: white;
                color: #000000 !important;
                font-weight: bold !important;
            }
            .no-print { display: none !important; }
            .print-break { page-break-before: always; }
            .prescription-container {
                max-width: none;
                margin: 0;
                box-shadow: none;
                border-radius: 0;
            }
            /* Force all text to be black and bold when printing */
            * {
                color: #000000 !important;
                font-weight: bold !important;
            }
            .medicine-name,
            .medicine-details,
            .schedule-info,
            .schedule-table td,
            .schedule-table th,
            .info-label,
            .info-section,
            .patient-info,
            .consultation-details,
            .doctor-signature {
                color: #000000 !important;
                font-weight: bold !important;
            }
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #333;
            font-weight: bold;
        }
        
        
        
        
        .prescription-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #007bff, #28a745, #ffc107, #dc3545);
        }
        
        .prescription-header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding: 15px;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative;
        }
        
        .prescription-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #007bff, #28a745, #ffc107, #dc3545);
        }
        
        .clinic-name {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #2c3e50;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .clinic-address {
            font-size: 13px;
            color: #6c757d;
            line-height: 1.5;
            font-weight: 500;
        }
        
        .prescription-title {
            font-size: 18px;
            font-weight: 700;
            text-align: center;
            margin: 25px 0;
            text-decoration: underline;
            color: #2c3e50;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .patient-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
            padding: 0 15px;
        }
        
        .info-section {
            border: 2px solid #e9ecef;
            padding: 15px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .info-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .info-label {
            font-weight: 700;
            display: inline-block;
            width: 80px;
            color: #2c3e50;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .medicines-section {
            margin: 15px 0;
            padding: 0 15px;
        }
        
        .rx-icon {
            font-size: 56px;
            color: #000000;
            margin-right: 10px;
            vertical-align: middle;
            font-weight: bold;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-weight: 700;
            text-decoration: underline;
            margin-bottom: 15px;
            font-size: 16px;
            color: #2c3e50;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .medicine-item {
            margin-bottom: 10px;
            padding: 10px;
            border: 2px solid #e9ecef;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .medicine-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .medicine-name {
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 8px;
            color: #000000;
        }
        
        .medicine-details {
            font-size: 13px;
            margin-bottom: 8px;
            color: #000000;
            font-weight: bold;
        }
        
        .schedule-info {
            font-weight: 700;
            color: #000000;
            font-size: 13px;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 6px 12px;
            border-radius: 20px;
            display: inline-block;
            box-shadow: 0 2px 4px rgba(0,123,255,0.2);
        }
        
        .consultation-details {
            margin: 25px 0;
            border: 2px solid #e9ecef;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .consultation-details:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .doctor-signature {
            margin-top: 40px;
            text-align: right;
            font-size: 14px;
            color: #2c3e50;
            padding: 0 25px 25px;
            font-weight: 600;
        }
        
        .signature-line {
            border-bottom: 3px solid #007bff;
            width: 250px;
            margin-left: auto;
            margin-top: 30px;
            box-shadow: 0 2px 4px rgba(0,123,255,0.2);
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            z-index: 1000;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0,123,255,0.3);
            transition: all 0.3s ease;
        }
        
        .print-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,123,255,0.4);
            background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
        }
        
        /* Schedule Table Styles */
        .schedule-table-container {
            margin: 25px 0;
            padding: 0 25px;
        }
        
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 12px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 2px solid #e9ecef;
        }
        
        .schedule-table th {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 15px 10px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            border: 1px solid #34495e;
        }
        
        .schedule-table td {
            border: 1px solid #dee2e6;
            padding: 12px 8px;
            text-align: center;
            vertical-align: middle;
            background: #ffffff;
            transition: all 0.3s ease;
            font-weight: bold;
            color: #000000;
        }
        
        .schedule-table td:hover {
            background: #f8f9fa;
        }
        
        .schedule-checkbox {
            width: 14px;
            height: 14px;
            cursor: pointer;
            accent-color: #007bff;
        }
        
        .schedule-label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .schedule-icon {
            font-size: 10px;
        }
        
        .checked-schedule {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%) !important;
            border-color: #28a745 !important;
            font-weight: bold;
        }
        
        .schedule-section-title {
            font-weight: 700;
            text-decoration: underline;
            margin-bottom: 15px;
            font-size: 16px;
            color: #2c3e50;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="action-buttons no-print" style="display: flex; gap: 15px; margin-bottom: 20px; justify-content: center; flex-wrap: wrap;">
        <button class="print-button" onclick="window.print()">
            <i class="fas fa-print"></i> Print Prescription
        </button>
        <button type="button" class="followup-button" onclick="openFollowUpModal()" style="
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        ">
            <i class="fas fa-calendar-check"></i> Schedule follow-up
        </button>
        <button class="queue-button" onclick="goToDoctorQueue()" style="
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        ">
            <i class="fas fa-users"></i> Back to Doctor Queue
        </button>
    </div>

    <!-- Follow-up appointment modal -->
    <div id="followup_modal" class="no-print" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 12px; padding: 24px; max-width: 420px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            <h3 style="margin: 0 0 20px 0; color: #2c3e50;"><i class="fas fa-calendar-check"></i> Schedule follow-up check-up</h3>
            <form id="followup_form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="patient_id" value="<?php echo (int)$prescription['patient_id']; ?>">
                <div style="margin-bottom: 14px;">
                    <label style="display: block; margin-bottom: 4px; font-weight: 600; color: #555;">Date</label>
                    <input type="date" name="appointment_date" id="followup_date" required style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" value="<?php
                        $d = new DateTime($prescription['consultation_date']);
                        $d->modify('+1 week');
                        echo $d->format('Y-m-d');
                    ?>">
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; margin-bottom: 4px; font-weight: 600; color: #555;">Time</label>
                    <input type="time" name="appointment_time" id="followup_time" required style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" value="09:00">
                </div>
                <div style="margin-bottom: 18px;">
                    <label style="display: block; margin-bottom: 4px; font-weight: 600; color: #555;">Reason for visit</label>
                    <input type="text" name="reason_for_visit" id="followup_reason" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" value="Follow-up check-up" placeholder="e.g. Follow-up check-up">
                </div>
                <div id="followup_message" style="display: none; margin-bottom: 12px; padding: 10px; border-radius: 6px;"></div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="closeFollowUpModal()" style="padding: 10px 20px; border: 1px solid #ddd; border-radius: 6px; background: #f8f9fa; cursor: pointer;">Cancel</button>
                    <button type="submit" id="followup_submit_btn" style="padding: 10px 20px; border: none; border-radius: 6px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; font-weight: 600; cursor: pointer;"><i class="fas fa-check"></i> Schedule</button>
                </div>
            </form>
        </div>
    </div>

    <div class="prescription-container">
        <div class="prescription-header">
        <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 8px;">
            <img src="./dist/img/st.png" alt="Logo" style="width: 320x; height: 100px;">
        </div>
        <div class="clinic-address">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; font-size: 16px;">
                <div style="text-align: left;">
                    <div style="display: flex; align-items: center; margin-bottom: 4px;">
                        <span style="margin-right: 8px; font-size: 18px;">📍</span>
                        <span style="font-size: 16px; font-weight: bold;">Purok 4, Poblacion</span>
                    </div>
                    <div style="margin-left: 24px; font-size: 16px; font-weight: bold;">Tupi, South Cotabato</div>
                </div>
                <div style="text-align: center;">
                    <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 4px;">
                        <span style="margin-right: 8px; font-size: 18px;">📅</span>
                        <span style="font-size: 16px; font-weight: bold;">Wednesday-Saturday</span>
                    </div>
                    <div style="font-size: 16px; font-weight: bold;">9:00am-5:00pm</div>
                </div>
                <div style="text-align: right;">
                    <div style="display: flex; align-items: center; justify-content: flex-end;">
                        <span style="margin-right: 8px; font-size: 18px;">📞</span>
                        <span style="font-size: 16px; font-weight: bold;">09106616911</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

   

    <div class="patient-info">
        <div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; font-weight: 600;">
            <div style="flex: 1; margin-right: 20px;">
                <div style="display: flex; align-items: baseline; margin-bottom: 10px;">
                    <span style="color: #2c3e50; min-width: 120px;">Patient's Name:</span> 
                    <span style="border-bottom: 2px solid #333; padding: 0 20px; min-width: 200px; display: inline-block;">
                        <?php echo htmlspecialchars($prescription['patient_name']); ?>
                    </span>
                </div>
                <div style="display: flex; align-items: baseline;">
                    <span style="color: #2c3e50; min-width: 120px;">Address:</span> 
                    <span style="border-bottom: 2px solid #333; padding: 0 20px; min-width: 200px; display: inline-block;">
                        <?php echo htmlspecialchars($prescription['address']); ?>
                    </span>
                </div>
            </div>
            <div style="flex: 1;">
                <div style="display: flex; align-items: baseline; margin-bottom: 10px;">
                    <span style="color: #2c3e50; min-width: 60px;">Date:</span> 
                    <span style="border-bottom: 2px solid #333; padding: 0 20px; min-width: 150px; display: inline-block;">
                        <?php echo date('F j, Y', strtotime($prescription['consultation_date'])); ?>
                    </span>
                </div>
                <div style="display: flex; align-items: baseline;">
                    <span style="color: #2c3e50; min-width: 60px;">Age/Sex:</span> 
                    <span style="border-bottom: 2px solid #333; padding: 0 20px; min-width: 150px; display: inline-block;">
                        <?php echo $age; ?> years / <?php echo htmlspecialchars($prescription['gender']); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>


     <div class="medicines-section">
         <div class="section-title">
             <span class="rx-icon">℞</span>
         </div>
        <?php if (empty($medicines)): ?>
            <div style="text-align: center; font-style: italic; color: #666;">
                No medicines prescribed.
            </div>
        <?php else: ?>
             <table style="width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 11px; background: white; border-radius: 4px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                 <thead>
                     <tr style="background-color: #2c3e50; color: white;">
                         <th style="border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 11px; font-weight: bold; width: 25%;">Medicines</th>
                         <th style="border: 1px solid #ddd; padding: 8px; text-align: center; width: 8%; font-size: 11px; font-weight: bold;">Qty</th>
                         <th style="border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 11px; font-weight: bold; width: 15%;">Dosage</th>
                         <th colspan="7" style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 12px; font-weight: bold; background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">SCHEDULE</th>
                     </tr>
                     <tr style="background-color: #34495e; color: white;">
                         <th style="border: 1px solid #ddd; padding: 6px; text-align: left; font-size: 10px; font-weight: bold; width: 25%;"></th>
                         <th style="border: 1px solid #ddd; padding: 6px; text-align: center; width: 8%; font-size: 10px; font-weight: bold;"></th>
                         <th style="border: 1px solid #ddd; padding: 6px; text-align: left; font-size: 10px; font-weight: bold; width: 15%;"></th>
                         <th style="border: 1px solid #ddd; padding: 6px; text-align: center; font-size: 8px; font-weight: bold; width: 8%;">Before<br>Breakfast</th>
                         <th style="border: 1px solid #ddd; padding: 6px; text-align: center; font-size: 8px; font-weight: bold; width: 8%;">After<br>Breakfast</th>
                         <th style="border: 1px solid #ddd; padding: 6px; text-align: center; font-size: 8px; font-weight: bold; width: 8%;">Before<br>Lunch</th>
                         <th style="border: 1px solid #ddd; padding: 6px; text-align: center; font-size: 8px; font-weight: bold; width: 8%;">After<br>Lunch</th>
                         <th style="border: 1px solid #ddd; padding: 6px; text-align: center; font-size: 8px; font-weight: bold; width: 8%;">Before<br>Dinner</th>
                         <th style="border: 1px solid #ddd; padding: 6px; text-align: center; font-size: 8px; font-weight: bold; width: 8%;">After<br>Dinner</th>
                         <th style="border: 1px solid #ddd; padding: 6px; text-align: center; font-size: 8px; font-weight: bold; width: 8%;">Bedtime</th>
                     </tr>
                 </thead>
                 <tbody>
                     <?php foreach ($medicines as $index => $medicine): ?>
                     <?php 
                     // Get medicine details from medicines table
                     $medicineStmt = $con->prepare("SELECT generic_name, brand_name FROM medicines WHERE medicine_name = ?");
                     $medicineStmt->execute([$medicine['medicine_name']]);
                     $medicineDetails = $medicineStmt->fetch(PDO::FETCH_ASSOC);
                     $displayName = $medicineDetails ? $medicineDetails['generic_name'] . ' (' . $medicineDetails['brand_name'] . ')' : $medicine['medicine_name'];
                     
                     // Parse the schedule string to check which schedules are selected
                     $selectedSchedules = array_map('trim', explode(',', $medicine['schedule']));
                     $isBeforeBreakfast = in_array('Before Breakfast', $selectedSchedules);
                     $isAfterBreakfast = in_array('After Breakfast', $selectedSchedules);
                     $isBeforeLunch = in_array('Before Lunch', $selectedSchedules);
                     $isAfterLunch = in_array('After Lunch', $selectedSchedules);
                     $isBeforeDinner = in_array('Before Dinner', $selectedSchedules);
                     $isAfterDinner = in_array('After Dinner', $selectedSchedules);
                     $isBedtime = in_array('Bedtime', $selectedSchedules);
                     ?>
                     <tr style="background-color: <?php echo $index % 2 == 0 ? '#f9f9f9' : 'white'; ?>;">
                         <td style="border: 1px solid #ddd; padding: 8px; font-size: 11px; color: #2c3e50; font-weight: 500;"><?php echo htmlspecialchars($displayName); ?></td>
                         <td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 11px; color: #2c3e50; font-weight: bold;"><?php echo htmlspecialchars($medicine['quantity']); ?></td>
                         <td style="border: 1px solid #ddd; padding: 8px; font-size: 11px; color: #555;"><?php echo htmlspecialchars($medicine['dosage']); ?></td>
                         <td style="border: 1px solid #ddd; padding: 8px; text-align: center; background: <?php echo $isBeforeBreakfast ? 'linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%)' : 'transparent'; ?>;">
                             <?php if ($isBeforeBreakfast): ?>
                                 <span style="font-size: 16px; color: #28a745; font-weight: bold;">✓</span>
                             <?php endif; ?>
                             <i class="fas fa-coffee" style="font-size: 10px; color: #ffc107; margin-left: 3px;"></i>
                         </td>
                         <td style="border: 1px solid #ddd; padding: 8px; text-align: center; background: <?php echo $isAfterBreakfast ? 'linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%)' : 'transparent'; ?>;">
                             <?php if ($isAfterBreakfast): ?>
                                 <span style="font-size: 16px; color: #28a745; font-weight: bold;">✓</span>
                             <?php endif; ?>
                             <i class="fas fa-coffee" style="font-size: 10px; color: #28a745; margin-left: 3px;"></i>
                         </td>
                         <td style="border: 1px solid #ddd; padding: 8px; text-align: center; background: <?php echo $isBeforeLunch ? 'linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%)' : 'transparent'; ?>;">
                             <?php if ($isBeforeLunch): ?>
                                 <span style="font-size: 16px; color: #28a745; font-weight: bold;">✓</span>
                             <?php endif; ?>
                             <i class="fas fa-utensils" style="font-size: 10px; color: #ffc107; margin-left: 3px;"></i>
                         </td>
                         <td style="border: 1px solid #ddd; padding: 8px; text-align: center; background: <?php echo $isAfterLunch ? 'linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%)' : 'transparent'; ?>;">
                             <?php if ($isAfterLunch): ?>
                                 <span style="font-size: 16px; color: #28a745; font-weight: bold;">✓</span>
                             <?php endif; ?>
                             <i class="fas fa-utensils" style="font-size: 10px; color: #28a745; margin-left: 3px;"></i>
                         </td>
                         <td style="border: 1px solid #ddd; padding: 8px; text-align: center; background: <?php echo $isBeforeDinner ? 'linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%)' : 'transparent'; ?>;">
                             <?php if ($isBeforeDinner): ?>
                                 <span style="font-size: 16px; color: #28a745; font-weight: bold;">✓</span>
                             <?php endif; ?>
                             <i class="fas fa-drumstick-bite" style="font-size: 10px; color: #ffc107; margin-left: 3px;"></i>
                         </td>
                         <td style="border: 1px solid #ddd; padding: 8px; text-align: center; background: <?php echo $isAfterDinner ? 'linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%)' : 'transparent'; ?>;">
                             <?php if ($isAfterDinner): ?>
                                 <span style="font-size: 16px; color: #28a745; font-weight: bold;">✓</span>
                             <?php endif; ?>
                             <i class="fas fa-drumstick-bite" style="font-size: 10px; color: #28a745; margin-left: 3px;"></i>
                         </td>
                         <td style="border: 1px solid #ddd; padding: 8px; text-align: center; background: <?php echo $isBedtime ? 'linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%)' : 'transparent'; ?>;">
                             <?php if ($isBedtime): ?>
                                 <span style="font-size: 16px; color: #28a745; font-weight: bold;">✓</span>
                             <?php endif; ?>
                             <i class="fas fa-moon" style="font-size: 10px; color: #17a2b8; margin-left: 3px;"></i>
                         </td>
                     </tr>
                     <?php endforeach; ?>
                 </tbody>
             </table>
        <?php endif; ?>
    </div>


        <div class="doctor-signature" style="display: flex; justify-content: space-between; align-items: flex-start; padding: 0 25px 25px;">
            <div style="flex: 1; text-align: left;">
                <div style="font-weight: 700; color: #2c3e50;">Next Visit</div>
                <div style="border-bottom: 2px solid #333; width: 220px; height: 18px; margin-top: 6px;"></div>
            </div>
            <div style="flex: 1; text-align: right; color: #2c3e50;">
                <div style="font-weight: 700;"><?php echo htmlspecialchars($prescription['doctor_name']); ?></div>
                <div style="margin-top: 4px; text-align: right;">Lic No.: 854438</div>
                <div style="margin-top: 4px; text-align: right;">PTR #: <span style="display: inline-block; border-bottom: 2px solid #333; min-width: 120px; height: 16px;"></span></div>
            </div>
        </div>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 1000);
        };

        function openFollowUpModal() {
            document.getElementById('followup_modal').style.display = 'flex';
            document.getElementById('followup_message').style.display = 'none';
        }

        function closeFollowUpModal() {
            document.getElementById('followup_modal').style.display = 'none';
        }

        document.getElementById('followup_form').addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('followup_submit_btn');
            var msgEl = document.getElementById('followup_message');
            btn.disabled = true;
            msgEl.style.display = 'none';

            var formData = new FormData(this);
            fetch('ajax/add_appointment.php', {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                msgEl.style.display = 'block';
                if (res.status === 'success') {
                    msgEl.style.background = '#d4edda';
                    msgEl.style.color = '#155724';
                    msgEl.innerHTML = '<i class="fas fa-check-circle"></i> ' + (res.message || 'Follow-up scheduled.');
                    setTimeout(function() {
                        closeFollowUpModal();
                        if (confirm('Follow-up scheduled. Open Appointments page?')) {
                            window.location.href = 'appointments.php';
                        }
                    }, 1500);
                } else {
                    msgEl.style.background = '#f8d7da';
                    msgEl.style.color = '#721c24';
                    msgEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (res.message || 'Failed to schedule.');
                    btn.disabled = false;
                }
            })
            .catch(function() {
                msgEl.style.display = 'block';
                msgEl.style.background = '#f8d7da';
                msgEl.style.color = '#721c24';
                msgEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> Network error. Please try again.';
                btn.disabled = false;
            });
        });
        
        // Function to go back to doctor queue
        function goToDoctorQueue() {
            // Check if we came from the queue
            const urlParams = new URLSearchParams(window.location.search);
            const fromQueue = urlParams.get('from_queue');
            
            if (fromQueue === '1') {
                // If we came from queue, go back to queue
                window.location.href = 'page.php';
            } else {
                // Otherwise, go to the main queue page
                window.location.href = 'page.php';
            }
        }
        
        // Add hover effects for the queue button
        document.addEventListener('DOMContentLoaded', function() {
            const queueButton = document.querySelector('.queue-button');
            if (queueButton) {
                queueButton.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                    this.style.boxShadow = '0 6px 20px rgba(102, 126, 234, 0.6)';
                });
                
                queueButton.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 4px 15px rgba(102, 126, 234, 0.4)';
                });
            }
            const followupBtn = document.querySelector('.followup-button');
            if (followupBtn) {
                followupBtn.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                    this.style.boxShadow = '0 6px 20px rgba(40, 167, 69, 0.5)';
                });
                followupBtn.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 4px 15px rgba(40, 167, 69, 0.4)';
                });
            }
        });
    </script>
</body>
</html>
