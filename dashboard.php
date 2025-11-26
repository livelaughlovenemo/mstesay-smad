<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$activeTab = isset($_GET["tab"]) ? $_GET["tab"] : "sales";

include "includes/header.php"; // Keep your standard header (navbar)
?>

<main class="dashboard-main">

  <!-- DASHBOARD HERO -->
  <section class="dashboard-hero">
      <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
      <p>Monitor your sales and inventory efficiently.</p>
  </section>

  <!-- TAB NAVIGATION -->
  <div class="tab-navigation">
      <button class="tab-btn <?= $activeTab === 'sales' ? 'active' : '' ?>" data-target="sales">
        📊 Sales Dashboard
      </button>
      <button class="tab-btn <?= $activeTab === 'inventory' ? 'active' : '' ?>" data-target="inventory">
        📦 Inventory
      </button>
  </div>

  <!-- TAB CONTENT -->
  <div class="tab-container">

      <!-- SALES TAB -->
      <section id="sales" class="tab-content <?= $activeTab === 'sales' ? 'active' : '' ?>">
          <div class="loader"></div>
          <p>Sales content will go here...</p>
      </section>

      <!-- INVENTORY TAB -->
      <section id="inventory" class="tab-content <?= $activeTab === 'inventory' ? 'active' : '' ?>">
          <div class="loader"></div>
          <p>Inventory content will go here...</p>
      </section>

  </div>

</main>

<style>
.dashboard-main {
    font-family: 'Montserrat', sans-serif;
    padding: 120px 40px 60px 40px;
    background: #f4f4f4;
    min-height: 90vh;
    color: #333;
}

.dashboard-hero {
    text-align: left;
    margin-bottom: 40px;
    background: linear-gradient(90deg, #fff5d7, #fff);
    padding: 30px 25px;
    border-radius: 18px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
}

.dashboard-hero h1 {
    font-family: 'Urbanist', sans-serif;
    font-size: 36px;
    font-weight: 800;
    color: #F5A200;
    margin-bottom: 10px;
}

.dashboard-hero p {
    font-size: 16px;
    color: #4a4a4a;
}

.tab-navigation {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.tab-btn {
    padding: 10px 18px;
    border: none;
    background: #fff3cd;
    cursor: pointer;
    border-radius: 12px;
    font-weight: 600;
    transition: 0.2s ease;
}

.tab-btn.active {
    background: #F5A200;
    color: white;
}

.tab-content {
    display: none;
    animation: fadeIn 0.3s ease;
    background: #fff;
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
}

.tab-content.active {
    display: block;
}

.loader {
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #ddd, #F5A200, #ddd);
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
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
// tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

    btn.classList.add('active');
    document.getElementById(btn.dataset.target).classList.add('active');

    // Update URL
    const newURL = "?tab=" + btn.dataset.target;
    history.replaceState(null, "", newURL);
  });
});
</script>

<?php include "includes/footer.php"; ?>
