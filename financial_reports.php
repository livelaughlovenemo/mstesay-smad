<?php
// financial_reports.php
session_start();
if (!isset($_SESSION['user_id'])) header('Location: login.php');
require_once __DIR__ . '/includes/db.php';

$from = $_GET['from'] ?? date('Y-m-01'); // default start of month
$to = $_GET['to'] ?? date('Y-m-d');

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $stmt = $pdo->prepare("SELECT s.id, p.name, s.quantity, s.total_price, s.sale_datetime FROM sales s JOIN products p ON p.id = s.product_id WHERE DATE(s.sale_datetime) BETWEEN :from AND :to ORDER BY s.sale_datetime DESC");
    $stmt->execute(['from'=>$from, 'to'=>$to]);
    $rows = $stmt->fetchAll();

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="smad_report_'.$from.'_'.$to.'.csv"');
    $out = fopen('php://output','w');
    fputcsv($out, ['ID','Product','Quantity','Total Price','Sale Datetime']);
    foreach ($rows as $r) fputcsv($out, [$r['id'], $r['name'], $r['quantity'], $r['total_price'], $r['sale_datetime']]);
    fclose($out);
    exit;
}

// totals aggregated by day in the range
$stmt = $pdo->prepare("SELECT DATE(sale_datetime) AS day, SUM(total_price) AS revenue FROM sales WHERE DATE(sale_datetime) BETWEEN :from AND :to GROUP BY day ORDER BY day");
$stmt->execute(['from'=>$from, 'to'=>$to]);
$rows = $stmt->fetchAll();
$labels = array_column($rows, 'day');
$values = array_map('floatval', array_column($rows, 'revenue'));

include __DIR__ . '/includes/header.php';
?>
<div class="row">
  <div class="col-12">
    <h3>Financial Reports</h3>
    <form class="row g-2 align-items-end mb-3" method="get" action="financial_reports.php">
      <div class="col-auto"><label class="form-label">From</label><input type="date" name="from" value="<?=htmlspecialchars($from)?>" class="form-control"></div>
      <div class="col-auto"><label class="form-label">To</label><input type="date" name="to" value="<?=htmlspecialchars($to)?>" class="form-control"></div>
      <div class="col-auto"><button class="btn btn-primary">Apply</button></div>
      <div class="col-auto"><a class="btn btn-outline-secondary" href="financial_reports.php?from=<?=urlencode($from)?>&to=<?=urlencode($to)?>&export=csv">Export CSV</a></div>
    </form>

    <div class="card mb-3">
      <div class="card-body">
        <canvas id="revRange"></canvas>
      </div>
    </div>

    <table class="table table-sm">
      <thead><tr><th>Day</th><th>Revenue</th></tr></thead>
      <tbody>
        <?php if(!$rows): ?>
          <tr><td colspan="2" class="text-muted">No data in range.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr><td><?=htmlspecialchars($r['day'])?></td><td>₱ <?= number_format($r['revenue'],2) ?></td></tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
const frLabels = <?= json_encode($labels) ?>;
const frValues = <?= json_encode($values) ?>;
new Chart(document.getElementById('revRange').getContext('2d'), {
  type: 'line',
  data: { labels: frLabels, datasets: [{ label: 'Revenue', data: frValues, tension: 0.25 }] },
  options: { responsive: true }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
