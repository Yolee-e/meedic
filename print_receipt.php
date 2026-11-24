<?php
include './config/connection.php';

// Prefer sale_id if provided, else fallback to JSON payload
$receipt = [];
$saleId = isset($_GET['sale_id']) ? (int)$_GET['sale_id'] : 0;
if ($saleId > 0) {
    try {
        $stmt = $con->prepare("SELECT s.sale_date, s.total_amount, s.payment_method, s.patient_id, p.patient_name, s.user_id, u.display_name AS cashier_name
                                FROM sales s
                                LEFT JOIN patients p ON p.id = s.patient_id
                                LEFT JOIN users u ON u.id = s.user_id
                                WHERE s.id = :sid LIMIT 1");
        $stmt->bindParam(':sid', $saleId, PDO::PARAM_INT);
        $stmt->execute();
        $header = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt2 = $con->prepare("SELECT si.medicine_detail_id, si.quantity, si.price_at_sale, si.subtotal, md.packing, m.medicine_name
                                 FROM sale_items si
                                 JOIN medicine_details md ON md.id = si.medicine_detail_id
                                 JOIN medicines m ON m.id = md.medicine_id
                                 WHERE si.sale_id = :sid");
        $stmt2->bindParam(':sid', $saleId, PDO::PARAM_INT);
        $stmt2->execute();
        $items = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // Compute not bought items (prefer new prescription tables, fallback to legacy)
        $notBought = [];
        $patientId = $header ? (int)$header['patient_id'] : 0;
        $saleDate = $header && isset($header['sale_date']) ? date('Y-m-d', strtotime($header['sale_date'])) : date('Y-m-d');
        if ($patientId > 0) {
            // Map of purchased qty by medicine_detail_id and by medicine_name (exact + base name for matching)
            $purchasedQtyByDetailId = [];
            $purchasedQtyByName = [];
            foreach ($items as $r) {
                $mdid = isset($r['medicine_detail_id']) ? (int)$r['medicine_detail_id'] : 0;
                $nm = isset($r['medicine_name']) ? trim($r['medicine_name']) : '';
                $qty = (int)$r['quantity'];
                if ($mdid > 0) {
                    $purchasedQtyByDetailId[$mdid] = ($purchasedQtyByDetailId[$mdid] ?? 0) + $qty;
                }
                if ($nm !== '') {
                    $nmLower = strtolower($nm);
                    $purchasedQtyByName[$nmLower] = ($purchasedQtyByName[$nmLower] ?? 0) + $qty;
                    // Also index by base name (before " (" ) so "Paracetamol (Tylenol)" matches "paracetamol"
                    if (preg_match('/^(.+?)\s*\(/', $nm, $m)) {
                        $base = strtolower(trim($m[1]));
                        if ($base !== $nmLower) {
                            $purchasedQtyByName[$base] = ($purchasedQtyByName[$base] ?? 0) + $qty;
                        }
                    }
                }
            }

            $notBoughtComputed = false;
            try {
                // Check if prescriptions table exists (use information_schema for reliable check)
                $st = $con->query("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'prescriptions' LIMIT 1");
                $hasPrescriptions = $st && $st->fetch();
                $st = $con->query("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'prescription_list' LIMIT 1");
                $hasPrescriptionList = $st && $st->fetch();

                if ($hasPrescriptions) {
                    // Load latest prescription for this patient (prefer one on or before sale date)
                    $st = $con->prepare("SELECT id FROM prescriptions WHERE patient_id = :pid AND consultation_date <= :sale_date ORDER BY consultation_date DESC, created_at DESC LIMIT 1");
                    $st->bindParam(':pid', $patientId, PDO::PARAM_INT);
                    $st->bindValue(':sale_date', $saleDate, PDO::PARAM_STR);
                    $st->execute();
                    $prescriptionId = (int)$st->fetchColumn();
                    if ($prescriptionId <= 0) {
                        $st = $con->prepare("SELECT id FROM prescriptions WHERE patient_id = :pid ORDER BY created_at DESC LIMIT 1");
                        $st->bindParam(':pid', $patientId, PDO::PARAM_INT);
                        $st->execute();
                        $prescriptionId = (int)$st->fetchColumn();
                    }
                    if ($prescriptionId > 0) {
                        $rows = [];
                        if ($hasPrescriptionList) {
                            $rx = $con->prepare("SELECT pl.medicine_name, pl.quantity AS prescribed_qty, md.id AS medicine_detail_id, COALESCE(md.packing,'') AS packing
                                                 FROM prescription_list pl
                                                 LEFT JOIN medicine_details md ON md.id = pl.medicine_details_id
                                                 WHERE pl.prescription_id = :pid");
                            $rx->bindParam(':pid', $prescriptionId, PDO::PARAM_INT);
                            $rx->execute();
                            $rows = $rx->fetchAll(PDO::FETCH_ASSOC);
                        }
                        // Fallback: if prescription_list empty, use prescription_medicines (no packing/detail_id)
                        if (empty($rows)) {
                            $st = $con->query("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'prescription_medicines' LIMIT 1");
                            if ($st && $st->fetch()) {
                                $rx = $con->prepare("SELECT medicine_name, quantity AS prescribed_qty FROM prescription_medicines WHERE prescription_id = :pid");
                                $rx->bindParam(':pid', $prescriptionId, PDO::PARAM_INT);
                                $rx->execute();
                                foreach ($rx->fetchAll(PDO::FETCH_ASSOC) as $row) {
                                    $rows[] = [
                                        'medicine_name' => $row['medicine_name'],
                                        'prescribed_qty' => $row['prescribed_qty'],
                                        'medicine_detail_id' => null,
                                        'packing' => ''
                                    ];
                                }
                            }
                        }
                        foreach ($rows as $r) {
                            $mdid = isset($r['medicine_detail_id']) ? (int)$r['medicine_detail_id'] : 0;
                            $nmRaw = trim($r['medicine_name'] ?? '');
                            $nmKey = strtolower($nmRaw);
                            $prescribed = (int)($r['prescribed_qty'] ?? 0);
                            $purchased = 0;
                            if ($mdid > 0 && isset($purchasedQtyByDetailId[$mdid])) {
                                $purchased = (int)$purchasedQtyByDetailId[$mdid];
                            } elseif ($nmKey !== '') {
                                $purchased = (int)($purchasedQtyByName[$nmKey] ?? 0);
                                if ($purchased === 0 && preg_match('/^(.+?)\s*\(/', $nmRaw, $m)) {
                                    $base = strtolower(trim($m[1]));
                                    $purchased = (int)($purchasedQtyByName[$base] ?? 0);
                                }
                            }
                            $remaining = max(0, $prescribed - $purchased);
                            if ($remaining > 0) {
                                $notBought[] = [
                                    'medicine_detail_id' => $mdid,
                                    'medicine_name' => $r['medicine_name'],
                                    'packing' => $r['packing'] ?? '',
                                    'remaining_qty' => $remaining,
                                    'prescribed_qty' => $prescribed,
                                    'purchased_qty' => $purchased
                                ];
                            }
                        }
                        $notBoughtComputed = true;
                    }
                }
            } catch (Exception $e) {
                // fall through to legacy logic
            }

            // Legacy fallback using patient_visits/patient_medication_history
            if (!$notBoughtComputed) {
                $stmtVisit = $con->prepare("SELECT id FROM patient_visits WHERE patient_id = :pid ORDER BY visit_date DESC, id DESC LIMIT 1");
                $stmtVisit->bindParam(':pid', $patientId, PDO::PARAM_INT);
                $stmtVisit->execute();
                $visitId = $stmtVisit->fetchColumn();
                if ($visitId) {
                    $rxSql = "SELECT pmh.medicine_details_id, m.medicine_name, md.packing, pmh.quantity
                              FROM patient_medication_history pmh
                              JOIN medicine_details md ON md.id = pmh.medicine_details_id
                              JOIN medicines m ON m.id = md.medicine_id
                              WHERE pmh.patient_visit_id = :vid";
                    $stmtRx = $con->prepare($rxSql);
                    $stmtRx->bindParam(':vid', $visitId, PDO::PARAM_INT);
                    $stmtRx->execute();
                    $rxRows = $stmtRx->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rxRows as $rx) {
                        $mdid = (int)$rx['medicine_details_id'];
                        $prescribed = (int)$rx['quantity'];
                        $purchased = $purchasedQtyByDetailId[$mdid] ?? 0;
                        $remaining = max(0, $prescribed - $purchased);
                        if ($remaining > 0) {
                            $notBought[] = [
                                'medicine_detail_id' => $mdid,
                                'medicine_name' => $rx['medicine_name'],
                                'packing' => $rx['packing'],
                                'remaining_qty' => $remaining,
                                'prescribed_qty' => $prescribed,
                                'purchased_qty' => $purchased
                            ];
                        }
                    }
                }
            }
        }

        $receipt = [
            'patient_name' => $header ? ($header['patient_name'] ?? '') : '',
            'datetime' => $header ? $header['sale_date'] : date('Y-m-d H:i:s'),
            'payment_method' => $header ? ($header['payment_method'] ?? 'Cash') : 'Cash',
            'cashier_name' => $header ? ($header['cashier_name'] ?? '') : '',
            'bought_items' => array_map(function($r){
                return [
                    'medicine_name' => $r['medicine_name'],
                    'packing' => $r['packing'],
                    'qty' => (int)$r['quantity'],
                    'price' => (float)$r['price_at_sale'],
                    'subtotal' => (float)$r['subtotal']
                ];
            }, $items),
            'not_bought_items' => $notBought,
            'total' => $header ? (float)$header['total_amount'] : 0.0
        ];
    } catch (PDOException $ex) {
        app_log_exception($ex);
        $receipt = [];
    }
} else {
    $dataJson = isset($_GET['data']) ? $_GET['data'] : '';
    try { $receipt = json_decode($dataJson, true); } catch(Exception $e) { $receipt = []; }
}

$patientName = isset($receipt['patient_name']) ? $receipt['patient_name'] : '';
$dt = isset($receipt['datetime']) ? $receipt['datetime'] : date('Y-m-d H:i:s');
$paymentMethod = isset($receipt['payment_method']) ? $receipt['payment_method'] : 'Cash';
$cashierName = isset($receipt['cashier_name']) ? $receipt['cashier_name'] : '';
$bought = isset($receipt['bought_items']) ? $receipt['bought_items'] : [];
$notBought = isset($receipt['not_bought_items']) ? $receipt['not_bought_items'] : [];
$total = isset($receipt['total']) ? (float)$receipt['total'] : 0.0;

// Store header (customize as needed)
$storeName = 'St. Rita Family Clinic';
$storeAddr = 'Purok 4 Poblacion, Tupi South Cotabato';
$storePhone = '09106616911';
$storeHours = 'Wed-Sat 9:00am-5:00pm';
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <?php include './config/site_css_links.php';?>
 <title>Receipt</title>
 <style>
  @page { size: 80mm auto; margin: 0; }
  @media print {
    html, body { width: 80mm; }
  }
  body { background: #fff; margin: 0; font-family: monospace; font-size: 12px; }
  .receipt { width: 80mm; max-width: 80mm; margin: 0 auto; padding: 6mm 4mm; }
  .logo-container { margin-bottom: 8px; }
  .title { text-align: center; }
  .muted { color: #555; }
  .hr { border-top: 1px dashed #000; margin: 6px 0; }
  .row { display: flex; justify-content: space-between; }
  .items { width: 100%; }
  .item { margin: 0 0 4px 0; }
  .name { word-wrap: break-word; }
  .qtyprice { display: flex; justify-content: space-between; }
  .totals { margin-top: 6px; }
  .center { text-align: center; }
  .right { text-align: right; }
  .small { font-size: 11px; }
  .cut { margin-top: 10px; border-top: 1px dashed #000; padding-top: 6px; }
 </style>
 <script>
  function printNow(){ window.print(); }
 </script>
</head>
<body onload="printNow()">
<div class="receipt">
  <div class="title">
    <div class="logo-container" style="display: flex; align-items: center; justify-content: center;">
      <img src="./dist/img/st.png" alt="Logo" style="width: 200px; height: 80px;">
    </div>
    <div class="small muted"><?php echo htmlspecialchars($storeAddr);?></div>
    <div class="small muted" style="display: flex; justify-content: space-between; align-items: center;">
      <span>📅 <?php echo htmlspecialchars($storeHours);?></span>
      <span>📞 <?php echo htmlspecialchars($storePhone);?></span>
    </div>
  </div>
  <div class="hr"></div>
  <div class="small">
    <div class="row"><span>Date</span><span><?php echo htmlspecialchars($dt);?></span></div>
    <?php if($cashierName !== '') { ?><div class="row"><span>Cashier</span><span><?php echo htmlspecialchars($cashierName);?></span></div><?php } ?>
    <div class="row"><span>Payment</span><span><?php echo htmlspecialchars($paymentMethod);?></span></div>
    <div class="row"><span>Patient</span><span><?php echo $patientName !== '' ? htmlspecialchars($patientName) : 'Walk-in';?></span></div>
  </div>
  <div class="hr"></div>
  <div class="items">
    <?php if(empty($bought)) { ?>
      <div class="center muted">No items.</div>
    <?php } ?>
    <?php foreach($bought as $it) { 
      $nm = htmlspecialchars($it['medicine_name'].' ('.$it['packing'].')');
      $qty = (int)$it['qty'];
      $pr = number_format((float)$it['price'], 2);
      $sub = number_format((float)$it['subtotal'], 2);
    ?>
      <div class="item">
        <div class="name"><?php echo $nm;?></div>
        <div class="qtyprice small"><span><?php echo $qty;?> x ₱<?php echo $pr;?></span><span>₱<?php echo $sub;?></span></div>
      </div>
    <?php } ?>
  </div>
  <div class="hr"></div>
  <div class="totals">
    <div class="row"><strong>Total</strong><strong>₱<?php echo number_format($total, 2);?></strong></div>
  </div>
  <?php if(!empty($notBought)) { ?>
  <div class="hr"></div>
  <div class="small"><strong>Not Bought (Rx)</strong></div>
  <div class="items">
    <?php foreach($notBought as $it) { 
      $nm2 = htmlspecialchars(($it['medicine_name'] ?? '').' ('.($it['packing'] ?? '').')');
      $pres = (int)($it['prescribed_qty'] ?? 0);
      $purch = (int)($it['purchased_qty'] ?? 0);
      $rem = (int)($it['remaining_qty'] ?? max(0, $pres - $purch));
    ?>
      <div class="item">
        <div class="name"><?php echo $nm2; ?></div>
        <div class="qtyprice small"><span>Presc: <?php echo $pres; ?> | Bought: <?php echo $purch; ?></span><span>Remain: <?php echo $rem; ?></span></div>
      </div>
    <?php } ?>
  </div>
  <?php } ?>
  <div class="cut center small muted">Thank you for your purchase</div>
</div>
</body>
</html>


