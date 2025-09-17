<?php
// dashboard.php
session_start();
if (!isset($_SESSION['user_id'])) header('Location: login.php');

require_once __DIR__ . '/includes/db.php';

// Total sales today
$stmt = $pdo->prepare("SELECT IFNULL(SUM(total_price),0) AS total_today FROM sales WHERE DATE(sale_datetime) = CURDATE()");
$stmt->execute();
$totalToday = $stmt->fetchColumn();

// Top-selling product today
$stmt = $pdo->prepare("SELECT p.name, SUM(s.quantity) AS qty FROM sales s JOIN products p ON s.product_id = p.id WHERE DATE(s.sale_datetime)=CURDATE() GROUP BY p.id ORDER BY qty DESC LIMIT 1");
$stmt->execute();
$topToday = $stmt->fetch();

// Revenue last 7 days (for chart)
$stmt = $pdo->prepare("SELECT DATE(sale_datetime) AS day, SUM(total_price) AS revenue FROM sales WHERE sale_datetime >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY day ORDER BY day");
$stmt->execute();
$last7 = $stmt->fetchAll();

$labels7 = [];
$data7 = [];
$period = new DatePeriod(new DateTime('-6 days'), new DateInterval('P1D'), 7);
$map = [];
foreach ($last7 as $r) $map[$r['day']] = $r['revenue'];
for ($i=6; $i>=0; $i--) {
    $d = (new DateTime())->sub(new DateInterval('P'.$i.'D'))->format('Y-m-d');
    $labels7[] = $d;
    $data7[] = isset($map[$d]) ? (float)$map[$d] : 0.0;
}

include __DIR__ . '/includes/header.php';
?>
<div class="row">
  <div class="col-12">
    <h3>Dashboard</h3>
    <p class="text-muted">Welcome, <?=htmlspecialchars($_SESSION['username'])?> — here's a quick summary.</p>
  </div>

  <div class="col-md-4">
    <div class="card card-summary mb-3">
      <div class="card-body">
        <h6>Total Sales Today</h6>
        <h3>₱ <?=number_format($totalToday,2)?></h3>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card card-summary mb-3">
      <div class="card-body">
        <h6>Top Product Today</h6>
        <h5><?= $topToday ? htmlspecialchars($topToday['name']).' (x'.$topToday['qty'].')' : '—' ?></h5>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card card-summary mb-3">
      <div class="card-body">
        <h6>Quick Actions</h6>
        <a class="btn btn-sm btn-outline-primary" href="daily_sales.php">Add Sale</a>
        <a class="btn btn-sm btn-outline-secondary" href="top_products.php">View Top Products</a>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card mb-3">
      <div class="card-body">
        <h6>Revenue — Last 7 Days</h6>
        <canvas id="revenue7"></canvas>
      </div>
    </div>
  </div>
</div>

<script>
const labels7 = <?= json_encode($labels7) ?>;
const data7 = <?= json_encode($data7) ?>;
new Chart(document.getElementById('revenue7').getContext('2d'), {
  type: 'line',
  data: { labels: labels7, datasets: [{ label: 'Revenue', data: data7, tension: 0.3, fill: false }] },
  options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
