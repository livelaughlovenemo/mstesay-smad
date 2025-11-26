<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$role = $_SESSION['role']; 
$activeTab = isset($_GET["tab"]) ? $_GET["tab"] : "sales";

include "db.php"; 
include "header.php";
?>

<main class="dashboard-main">

  <!-- DASHBOARD HERO -->
  <section class="dashboard-hero">
      <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
      <p>Monitor your sales and inventory efficiently.</p>
  </section>

  <div class="tab-navigation">
    <button class="tab-btn <?= $activeTab === 'sales' ? 'active' : '' ?>" data-target="sales">
        📊 Sales Dashboard
    </button>

    <?php if($role == 'Admin' || $role == 'Manager'): ?>
    <button class="tab-btn <?= $activeTab === 'inventory' ? 'active' : '' ?>" data-target="inventory">
        📦 Inventory
    </button>
    <?php endif; ?>

    <?php if($role == 'Admin'): ?>
    <button class="tab-btn <?= $activeTab === 'users' ? 'active' : '' ?>" data-target="users">
        👥 Manage Users
    </button>
    <?php endif; ?>
</div>

<div class="tab-container">

  <!-- SALES TAB -->
  <section id="sales" class="tab-content <?= $activeTab === 'sales' ? 'active' : '' ?>">
      <?php
      // Fetch total sales per product
      $salesStmt = $pdo->query("
          SELECT p.name AS product, SUM(s.quantity) AS qty_sold, SUM(s.total_price) AS total_amount
          FROM sales s
          JOIN products p ON s.product_id = p.id
          GROUP BY p.name
          ORDER BY total_amount DESC
      ");
      $salesData = $salesStmt->fetchAll();
      ?>
      <table>
          <thead>
              <tr>
                  <th>Product</th>
                  <th>Quantity Sold</th>
                  <th>Total Amount</th>
              </tr>
          </thead>
          <tbody>
              <?php 
              $grandTotal = 0;
              foreach($salesData as $sale): 
                  $grandTotal += $sale['total_amount'];
              ?>
              <tr>
                  <td><?= htmlspecialchars($sale['product']) ?></td>
                  <td><?= $sale['qty_sold'] ?></td>
                  <td>₱<?= number_format($sale['total_amount'], 2) ?></td>
              </tr>
              <?php endforeach; ?>
              <tr>
                  <td colspan="2" style="font-weight: bold;">Grand Total</td>
                  <td style="font-weight: bold;">₱<?= number_format($grandTotal, 2) ?></td>
              </tr>
          </tbody>
      </table>
  </section>

  <!-- INVENTORY TAB -->
  <section id="inventory" class="tab-content <?= $activeTab === 'inventory' ? 'active' : '' ?>">
      <?php
      // Fetch inventory per supplier
      $invStmt = $pdo->query("
          SELECT product_name, supplier, SUM(kilos) AS total_kilos
          FROM inventory
          GROUP BY product_name, supplier
          ORDER BY product_name, supplier
      ");
      $inventoryData = $invStmt->fetchAll();
      ?>
      <table>
          <thead>
              <tr>
                  <th>Product</th>
                  <th>Supplier / Location</th>
                  <th>Total Kilos</th>
              </tr>
          </thead>
          <tbody>
              <?php 
              $invTotal = 0;
              foreach($inventoryData as $inv): 
                  $invTotal += $inv['total_kilos'];
              ?>
              <tr>
                  <td><?= htmlspecialchars($inv['product_name']) ?></td>
                  <td><?= htmlspecialchars($inv['supplier']) ?></td>
                  <td><?= $inv['total_kilos'] ?></td>
              </tr>
              <?php endforeach; ?>
              <tr>
                  <td colspan="2" style="font-weight: bold;">Total Inventory</td>
                  <td style="font-weight: bold;"><?= $invTotal ?></td>
              </tr>
          </tbody>
      </table>
  </section>

  <!-- USERS TAB -->
  <?php if($role == 'Admin'): ?>
  <section id="users" class="tab-content <?= $activeTab === 'users' ? 'active' : '' ?>">
      <?php
      $userStmt = $pdo->query("SELECT id, username, created_at FROM users ORDER BY id ASC");
      $users = $userStmt->fetchAll();
      ?>
      <table>
          <thead>
              <tr>
                  <th>ID</th>
                  <th>Username</th>
                  <th>Created At</th>
              </tr>
          </thead>
          <tbody>
              <?php foreach($users as $user): ?>
              <tr>
                  <td><?= $user['id'] ?></td>
                  <td><?= htmlspecialchars($user['username']) ?></td>
                  <td><?= $user['created_at'] ?></td>
              </tr>
              <?php endforeach; ?>
          </tbody>
      </table>
  </section>
  <?php endif; ?>

</div>

</main>

<style>
/* Keep your existing styles */
.dashboard-main { font-family: 'Montserrat', sans-serif; padding: 120px 40px 60px 40px; background: #f4f4f4; min-height: 90vh; color: #333; }
.dashboard-hero { text-align: left; margin-bottom: 40px; background: linear-gradient(90deg, #fff5d7, #fff); padding: 30px 25px; border-radius: 18px; box-shadow: 0 6px 18px rgba(0,0,0,0.08); }
.dashboard-hero h1 { font-family: 'Urbanist', sans-serif; font-size: 36px; font-weight: 800; color: #F5A200; margin-bottom: 10px; }
.dashboard-hero p { font-size: 16px; color: #4a4a4a; }
.tab-navigation { display: flex; gap: 15px; margin-bottom: 20px; }
.tab-btn { padding: 10px 18px; border: none; background: #fff3cd; cursor: pointer; border-radius: 12px; font-weight: 600; transition: 0.2s ease; }
.tab-btn.active { background: #F5A200; color: white; }
.tab-content { display: none; animation: fadeIn 0.3s ease; background: #fff; padding: 20px; border-radius: 16px; box-shadow: 0 6px 20px rgba(0,0,0,0.08); }
.tab-content.active { display: block; }
.loader { width: 100%; height: 4px; background: linear-gradient(90deg, #ddd, #F5A200, #ddd); background-size: 200% 100%; animation: loading 1s infinite linear; margin-bottom: 10px; border-radius: 4px; display: none; }
.tab-content.active .loader { display: block; }
@keyframes loading { from { background-position: 200% 0} to { background-position: -200% 0} }
@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
th { background: #F5A200; color: white; }
</style>

<script>
// tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

    btn.classList.add('active');
    document.getElementById(btn.dataset.target).classList.add('active');

    const newURL = "?tab=" + btn.dataset.target;
    history.replaceState(null, "", newURL);
  });
});
</script>

<?php include "includes/footer.php"; ?>
