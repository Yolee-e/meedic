<?php
include './config/connection.php';

$from = isset($_GET['from']) ? $_GET['from'] : '';
$to = isset($_GET['to']) ? $_GET['to'] : '';

// Convert to mysql format (mm/dd/yyyy or mm-dd-yyyy expected by UI 'L')
function toMysqlDate($d) {
  if (!$d) return '';
  $d = str_replace('-', '/', $d);
  $parts = explode('/', $d);
  if (count($parts) !== 3) return '';
  return $parts[2] . '-' . $parts[0] . '-' . $parts[1];
}

$fromMysql = toMysqlDate($from);
$toMysql = toMysqlDate($to);

header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html>
<head>
  <title>Sales Report</title>
  <?php include './config/site_css_links.php';?>
  <style>
    body { background: #fff; color: #000; }
    .report { max-width: 980px; margin: 20px auto; }
    .table-sm td, .table-sm th { padding: .35rem; }
  </style>
</head>
<body onload="window.print()">
  <div class="report">
    <h3>Sales Report</h3>
    <div class="text-muted mb-3">From: <?php echo htmlspecialchars($from);?> To: <?php echo htmlspecialchars($to);?></div>

    <?php if($fromMysql === '' || $toMysql === '') { ?>
      <div class="alert alert-warning">Invalid date range.</div>
    <?php } else { ?>
      <?php
        try {
          // If sales tables don't exist, show message
          $exists = $con->query("SHOW TABLES LIKE 'sales'")->rowCount() > 0;
          if (!$exists) {
            echo '<div class="alert alert-info">Sales data is not available.</div>';
          } else {
            $sql = "SELECT s.id as sale_id, DATE_FORMAT(s.sale_date, '%Y-%m-%d %H:%i') as sale_date,
                           p.patient_name, s.total_amount, s.payment_method
                    FROM sales s
                    LEFT JOIN patients p ON p.id = s.patient_id
                    WHERE DATE(s.sale_date) BETWEEN :from AND :to
                    ORDER BY s.sale_date ASC";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':from', $fromMysql);
            $stmt->bindParam(':to', $toMysql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$rows) {
              echo '<div class="alert alert-info">No sales found in this range.</div>';
            } else {
              echo '<table class="table table-bordered table-sm">';
              echo '<thead><tr><th>#</th><th>Date</th><th>Patient</th><th class="text-right">Total</th><th>Payment</th></tr></thead><tbody>';
              $i = 0; $grand = 0.0;
              foreach($rows as $r) {
                $i++; $grand += (float)$r['total_amount'];
                echo '<tr>';
                echo '<td>'. $i .'</td>';
                echo '<td>'. htmlspecialchars($r['sale_date']) .'</td>';
                echo '<td>'. htmlspecialchars($r['patient_name'] ?? 'Walk-in') .'</td>';
                echo '<td class="text-right">₱'. number_format((float)$r['total_amount'], 2) .'</td>';
                echo '<td>'. htmlspecialchars($r['payment_method']) .'</td>';
                echo '</tr>';
              }
              echo '</tbody><tfoot><tr><th colspan="3" class="text-right">Grand Total</th><th class="text-right">₱'. number_format($grand, 2) .'</th><th></th></tr></tfoot></table>';

              // Top 5 items in range
              $sqlTop = "SELECT m.medicine_name, md.packing, SUM(si.quantity) qty, SUM(si.subtotal) amt
                         FROM sale_items si
                         JOIN sales s ON s.id = si.sale_id
                         JOIN medicine_details md ON md.id = si.medicine_detail_id
                         JOIN medicines m ON m.id = md.medicine_id
                         WHERE DATE(s.sale_date) BETWEEN :from AND :to
                         GROUP BY si.medicine_detail_id
                         ORDER BY qty DESC
                         LIMIT 5";
              $stmtTop = $con->prepare($sqlTop);
              $stmtTop->bindParam(':from', $fromMysql);
              $stmtTop->bindParam(':to', $toMysql);
              $stmtTop->execute();
              $tops = $stmtTop->fetchAll(PDO::FETCH_ASSOC);
              if ($tops) {
                echo '<h5 class="mt-4">Top Medicines</h5>';
                echo '<table class="table table-bordered table-sm"><thead><tr><th>Medicine</th><th>Packing</th><th class="text-right">Qty</th><th class="text-right">Amount</th></tr></thead><tbody>';
                foreach($tops as $t){
                  echo '<tr>';
                  echo '<td>'. htmlspecialchars($t['medicine_name']) .'</td>';
                  echo '<td>'. htmlspecialchars($t['packing']) .'</td>';
                  echo '<td class="text-right">'. (int)$t['qty'] .'</td>';
                  echo '<td class="text-right">₱'. number_format((float)$t['amt'], 2) .'</td>';
                  echo '</tr>';
                }
                echo '</tbody></table>';
              }
            }
          }
        } catch(PDOException $ex) {
          echo '<div class="alert alert-danger">'. htmlspecialchars($ex->getMessage()) .'</div>';
        }
      ?>
    <?php } ?>
  </div>
</body>
</html>


