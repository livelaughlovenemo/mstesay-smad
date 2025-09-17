<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
include "includes/header.php";
?>
<main class="main-content">

  <ul class="nav-tabs" style="display:flex; gap:15px; margin-bottom:20px;">
    <li><a href="#sales" class="tab-btn active">Sales</a></li>
    <li><a href="#inventory" class="tab-btn">Inventory</a></li>
  </ul>

  <!-- SALES TAB -->
  <section id="sales" class="tab-content active">
    <?php include "partials/sales_dashboard.php"; ?>
  </section>

  <!-- INVENTORY TAB -->
  <section id="inventory" class="tab-content">
    <?php include "partials/inventory_dashboard.php"; ?>
  </section>

</main>

<script>
// tab switching
document.querySelectorAll('.tab-btn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c=>c.classList.remove('active'));
    btn.classList.add('active');
    document.querySelector(btn.getAttribute('href')).classList.add('active');
  });
});
</script>

<?php include "includes/footer.php"; ?>
