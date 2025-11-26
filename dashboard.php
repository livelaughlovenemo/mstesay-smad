<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Determine current tab
$activeTab = isset($_GET["tab"]) ? $_GET["tab"] : "sales";

include "includes/header.php";
?>
<main class="main-content">

  <!-- TOP NAV -->
  <div class="tab-navigation">
      <button class="tab-btn <?= $activeTab === 'sales' ? 'active' : '' ?>" data-target="sales">
        📊 Sales Dashboard
      </button>
      <button class="tab-btn <?= $activeTab === 'inventory' ? 'active' : '' ?>" data-target="inventory">
        📦 Inventory
      </button>
  </div>

  <!-- CONTENT WRAPPER -->
  <div class="tab-container">

      <!-- SALES TAB -->
      <section id="sales" class="tab-content <?= $activeTab === 'sales' ? 'active' : '' ?>">
          <div class="loader"></div>
          <?php include "partials/sales_dashboard.php"; ?>
      </section>

      <!-- INVENTORY TAB -->
      <section id="inventory" class="tab-content <?= $activeTab === 'inventory' ? 'active' : '' ?>">
          <div class="loader"></div>
          <?php include "partials/inventory_dashboard.php"; ?>
      </section>

  </div>

</main>

<style>
  .tab-navigation {
      display: flex;
      gap: 15px;
      margin-bottom: 20px;
  }

  .tab-btn {
      padding: 10px 18px;
      border: none;
      background: #f1f1f1;
      cursor: pointer;
      border-radius: 6px;
      font-weight: 600;
      transition: 0.2s ease;
  }

  .tab-btn.active {
      background: #007bff;
      color: white;
  }

  .tab-content {
      display: none;
      animation: fadeIn 0.3s ease;
  }

  .tab-content.active {
      display: block;
  }

  .loader {
      width: 100%;
      height: 4px;
      background: linear-gradient(90deg, #ddd, #007bff, #ddd);
      background-size: 200% 100%;
      animation: loading 1s infinite linear;
      margin-bottom: 10px;
      border-radius: 4px;
      display: none;
  }

  .tab-content.active .loader {
      display: block;
  }

  @keyframes loading {
      from { background-position: 200% 0}
      to { background-position: -200% 0}
  }

  @keyframes fadeIn {
      from { opacity: 0; transform: translateY(5px); }
      to   { opacity: 1; transform: translateY(0); }
  }
</style>

<script>
// tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {

    // Update visual active state
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

    btn.classList.add('active');

    const target = btn.dataset.target;
    document.getElementById(target).classList.add('active');

    // Update URL (optional but useful)
    const newURL = "?tab=" + target;
    history.replaceState(null, "", newURL);
  });
});
</script>

<?php include "includes/footer.php"; ?>
