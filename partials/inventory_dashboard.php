<?php
include __DIR__ . "/../includes/db.php";  

// Fetch all chicken products
$chickenQuery = $pdo->prepare("
    SELECT id, name FROM products 
    WHERE category = 'chicken'
    ORDER BY name ASC
");
$chickenQuery->execute();
$chickenProducts = $chickenQuery->fetchAll();


// Fetch all frozen products
$frozenQuery = $pdo->prepare("
    SELECT id, name FROM products 
    WHERE category = 'frozen'
    ORDER BY name ASC
");
$frozenQuery->execute();
$frozenProducts = $frozenQuery->fetchAll();


//suppliers 
$suppliers = ["Marcela","Manay","Remaining","Lexzoes","Wella","Pick-Ups"];

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
        <li><button type="button" data-target="chickenForm" class="tab-btn active">🐔 Chicken</button></li>
        <li><button type="button" data-target="frozenForm" class="tab-btn">❄️ Frozen</button></li>
      </ul>

      <!-- CHICKEN PRODUCTS -->
      <div id="chickenForm" class="tab-content active">
        <h5 class="fw-bold mb-2">Chicken Products</h5>
        <table class="table table-bordered table-sm align-middle text-center">
          <thead>
            <tr>
              <th width="60%">Product</th>
              <th>Kilos</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($chickenProducts as $p): ?>
              <tr>
                <td class="text-start"><?= htmlspecialchars($p['name']) ?></td>
                <td>
                  <input type="number" step="0.01" 
                         name="inv[chicken][<?= $p['id'] ?>]" 
                         class="form-control text-center">
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- FROZEN PRODUCTS -->
      <div id="frozenForm" class="tab-content">
        <h5 class="fw-bold mb-2">Frozen Products</h5>
        <table class="table table-bordered table-sm align-middle text-center">
          <thead>
            <tr>
              <th width="60%">Product</th>
              <th>Kilos</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($frozenProducts as $p): ?>
              <tr>
                <td class="text-start"><?= htmlspecialchars($p['name']) ?></td>
                <td>
                  <input type="number" step="0.01" 
                         name="inv[frozen][<?= $p['id'] ?>]" 
                         class="form-control text-center">
                </td>
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
  btn.addEventListener("click", (e) => {
    e.preventDefault();

    // switch active tab button
    document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");

    // switch visible content
    document.querySelectorAll(".tab-content").forEach(c => c.classList.remove("active"));
    document.getElementById(btn.dataset.target).classList.add("active");
  });
});
</script>

