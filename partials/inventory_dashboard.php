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

    <h4 class="mb-3">📦 Inventory Entry <span class="text-muted">(<?= $today ?>)</span></h4>

    <form method="post" action="save_inventory.php">

      <!-- SUPPLIER -->
      <div class="mb-3">
        <label class="form-label fw-bold">Supplier</label>
        <select class="form-select" name="supplier" required>
          <option value="">-- Choose Supplier --</option>
          <?php foreach ($suppliers as $s): ?>
            <option value="<?= $s ?>"><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- TABS -->
      <ul class="nav-tabs mb-3 inventory-tabs">
        <li><button type="button" data-target="#chickenForm" class="tab-btn active">🐔 Chicken</button></li>
        <li><button type="button" data-target="#frozenForm" class="tab-btn">❄️ Frozen</button></li>
      </ul>

      <!-- CHICKEN SECTION -->
      <div id="chickenForm" class="tab-content active">
        <h5 class="mb-2 fw-bold">Chicken Products</h5>

        <table class="table table-bordered table-sm align-middle text-center">
          <thead>
            <tr>
              <th style="width:60%">Product</th>
              <th>Kilos</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($chickenProducts as $prod): ?>
              <tr>
                <td class="text-start"><?= $prod ?></td>
                <td><input type="number" step="0.01" name="inv[chicken][<?= $prod ?>]" class="form-control text-center"></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- FROZEN SECTION -->
      <div id="frozenForm" class="tab-content">
        <h5 class="mb-2 fw-bold">Frozen Products</h5>

        <table class="table table-bordered table-sm align-middle text-center">
          <thead>
            <tr>
              <th style="width:60%">Product</th>
              <th>Kilos</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($frozenProducts as $prod): ?>
              <tr>
                <td class="text-start"><?= $prod ?></td>
                <td><input type="number" step="0.01" name="inv[frozen][<?= $prod ?>]" class="form-control text-center"></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <button class="btn btn-primary mt-3">💾 Save Inventory</button>

    </form>

  </div>
</div>

<style>
  .inventory-tabs { display:flex; gap:10px; padding-left:0; }
  .inventory-tabs li { list-style:none; }
  .inventory-tabs .tab-btn {
      padding:8px 16px;
      border:none;
      background:#eee;
      border-radius:5px;
      cursor:pointer;
      font-weight:600;
  }
  .inventory-tabs .tab-btn.active {
      background:#0d6efd;
      color:white;
  }
  .tab-content { display:none; }
  .tab-content.active { display:block; }
</style>

<script>
document.querySelectorAll(".tab-btn").forEach(btn => {
  btn.addEventListener("click", () => {
    
    document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");

    document.querySelectorAll(".tab-content").forEach(c => c.classList.remove("active"));
    document.querySelector(btn.dataset.target).classList.add("active");

  });
});
</script>
