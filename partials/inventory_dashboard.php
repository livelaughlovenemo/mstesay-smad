<?php
$suppliers = ["Marcela","Manay","Remaining","Lexzoes","Wella","Pick-Ups"];
$chickenProducts = [
  "Whole Chicken","BackBones","Neck","SKT Bones","Skin","Cuttings","Fillet",
  "Liver","Gizzard/B","Atay Baticon","Feet","Heads","Intestine",
  "Crps/Prvn/BTC","Dugo","Fats","Gizzard Fats"
];
$frozenProducts = [
  "Champion Hotdog Jumbo 1Kilo","Champion Hotdog Jumbo 250G","Champion Hotdog Mini 250G",
  "Booster Hotdog Jumbo 1k","Booster Hotdog Jumbo 240G","Booster Hotdog Regular 240G",
  "BS Hotdog Classic KingSize 1K","BS Hotdog Classic Jumbo 1K","BS Hotdog Cheese KingSize 1K",
  "BS Hotdog Cheese Jumbo 1K","Champion Pork Longganiza","Champion Chicken Longganiza",
  "Winner Cooked Ham","Winner Sweet Ham","EL RANCHO Corned Beef","Virginia Pork Tocino",
  "Champion Chicken Loaf","Champion Chicken Hotdog","Virginia Chicken Hotdog","Champion Cheese Hotdog",
  "Winner Bola-bola","Kings Longganiza","IQF Longganiza","Luncheon Meat","Tocino Roll",
  "Smoke Longganiza","Longga Dog","Bilog","Calderon","K - Patties","Ganado",
  "TJ Classic","TJ Cheesedog Regular","TJ Cheesedog Jumbo","TJ Cocktail","Lumpia Shanghai",
  "Bologna","Ginaling","Virginia Tocino Roll","Bulgogi","BS Spicy Hotdog","Sisig"
];
$today = date('Y-m-d');
?>

<div class="card mb-4">
  <div class="card-body">
    <h5>Inventory Entry (<?= $today ?>)</h5>

    <!-- Supplier Selector -->
    <form method="post" action="save_inventory.php">
      <label for="supplierSelect">Select Supplier:</label>
      <select id="supplierSelect" name="supplier" required>
        <option value="">-- Choose Supplier --</option>
        <?php foreach ($suppliers as $s): ?>
          <option value="<?= $s ?>"><?= $s ?></option>
        <?php endforeach; ?>
      </select>

      <!-- Tabs for Chicken / Frozen -->
      <ul class="nav-tabs" style="display:flex; gap:10px; margin-top:15px;">
        <li><a href="#chickenForm" class="tab-btn active">🐔 Chicken Products</a></li>
        <li><a href="#frozenForm" class="tab-btn">❄️ Frozen Products</a></li>
      </ul>

      <!-- Chicken -->
      <div id="chickenForm" class="tab-content active">
        <table class="table table-bordered table-sm text-center align-middle">
          <thead><tr><th>Product</th><th>Kilos</th></tr></thead>
          <tbody>
            <?php foreach ($chickenProducts as $prod): ?>
              <tr>
                <td class="text-start"><?= $prod ?></td>
                <td><input type="number" step="0.01" name="inv[chicken][<?= $prod ?>]"></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Frozen -->
      <div id="frozenForm" class="tab-content">
        <table class="table table-bordered table-sm text-center align-middle">
          <thead><tr><th>Product</th><th>Kilos</th></tr></thead>
          <tbody>
            <?php foreach ($frozenProducts as $prod): ?>
              <tr>
                <td class="text-start"><?= $prod ?></td>
                <td><input type="number" step="0.01" name="inv[frozen][<?= $prod ?>]"></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <button class="view-btn">💾 Save Inventory</button>
    </form>
  </div>
</div>

<script>
// tab switching
document.querySelectorAll('.tab-btn').forEach(btn=>{
  btn.addEventListener('click',e=>{
    e.preventDefault();
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c=>c.classList.remove('active'));
    btn.classList.add('active');
    document.querySelector(btn.getAttribute('href')).classList.add('active');
  });
});
</script>
