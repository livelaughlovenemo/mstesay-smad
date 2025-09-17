<?php
// top_products.php
session_start();
if (!isset($_SESSION['user_id'])) header('Location: login.php');

require_once __DIR__ . '/includes/db.php';

$period = $_GET['period'] ?? 'daily'; // daily | weekly | monthly

switch ($period) {
    case 'weekly':
        $where = "sale_datetime >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)";
        break;
    case 'monthly':
        $where = "sale_datetime >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
        break;
    default:
        $where = "DATE(sale_datetime) = CURDATE()";
}

$stmt = $pdo->prepare("SELECT p.name, SUM(s.quantity) AS qty, SUM(s.total_price) AS revenue
                       FROM sales s JOIN products p ON s.product_id = p.id
                       WHERE $where
                       GROUP BY p.id ORDER BY qty DESC");
$stmt->execute();
$tops = $stmt->fetchAll();

$labels = array_column($tops, 'name');
$qtys = array_map('intval', array_column($tops,'qty'));

include __DIR__ . '/includes/header.php';
?>
<div class="row">
  <div class="col-12 d-flex justify-content-between align-items-center">
    <h3>Top Products</h3>
    <div>
      <a class="btn btn-sm btn-outline-secondary" href="top_products.php?period=daily">Daily</a>
      <a class="btn btn-sm btn-outline-secondary" href="top_products.php?period=weekly">Weekly</a>
      <a class="btn btn-sm btn-outline-secondary" href="top_products.php?period=monthly">Monthly</a>
    </div>
  </div>

  <div class="col-12">
    <div class="card mt-2">
      <div class="card-body">
        <?php if(!$tops): ?>
          <div class="text-muted">No sales for this period.</div>
        <?php else: ?>
          <canvas id="topChart"></canvas>
          <table class="table table-sm mt-3">
            <thead><tr><th>Product</th><th>Qty</th><th>Revenue</th></tr></thead>
            <tbody>
              <?php foreach ($tops as $t): ?>
                <tr>
                  <td><?= htmlspecialchars($t['name']) ?></td>
                  <td><?= $t['qty'] ?></td>
                  <td>₱ <?= number_format($t['revenue'],2) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
const topLabels = <?= json_encode($labels) ?>;
const topData = <?= json_encode($qtys) ?>;
new Chart(document.getElementById('topChart').getContext('2d'), {
  type: 'bar',
  data: { labels: topLabels, datasets: [{ label: 'Quantity', data: topData, barPercentage: 0.6 }] },
  options: { indexAxis: 'x', responsive: true }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
