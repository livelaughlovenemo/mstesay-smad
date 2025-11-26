<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$activeTab = isset($_GET["tab"]) ? $_GET["tab"] : "sales";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Ms. Tesay Chicken</title>
  <link rel="stylesheet" href="assets/css/landing-styles.css">
  <link rel="icon" type="image/png" href="assets/img/mainlogo.png">
</head>
<body>

<!-- NAVBAR -->
<header class="navbar">
  <div class="logo">
    <img src="assets/img/mainlogo.png" alt="Logo">
    <span>Ms. Tesay Chicken</span>
  </div>

  <nav>
    <span style="margin-right:15px; font-weight:600; color:#333;">
      <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle;">
        <path d="M12 12C14.2091 12 16 10.2091 16 8C16 5.79086 14.2091 4 12 4C9.79086 4 8 5.79086 8 8C8 10.2091 9.79086 12 12 12Z" stroke="#F7743B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M4 20C4 17.2386 7.23858 15 12 15C16.7614 15 20 17.2386 20 20" stroke="#F7743B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <?= htmlspecialchars($_SESSION['username']) ?>
    </span>
    <a href="logout.php" class="login-btn" style="padding:8px 16px; font-size:14px;">Logout</a>
  </nav>
</header>

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
/* --- Dashboard Styles --- */
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

/* TAB STYLING */
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

/* NAVBAR ADJUSTMENTS */
.navbar {
  width: 100%;
  padding: 18px 50px;
  background: #ffffffee;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: fixed;
  z-index: 1000;
  top: 0;
  border-bottom: 1px solid #dedede;
  backdrop-filter: blur(8px);
}

.navbar nav {
  display: flex;
  align-items: center;
  gap: 15px;
}

.login-btn {
  background: #FF7A2F;
  color: white;
  border-radius: 12px;
  padding: 8px 16px;
  text-decoration: none;
  font-weight: 600;
  transition: 0.3s ease;
}

.login-btn:hover {
  background: #F55A00;
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

</body>
</html>
