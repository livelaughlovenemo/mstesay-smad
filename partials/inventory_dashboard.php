<?php
include __DIR__ . "/../includes/db.php";


// --- Settings ---
$limit = 10; // products per page
$today = date('Y-m-d');
$suppliers = ["Marcela","Manay","Remaining","Lexzoes","Wella","Pick-Ups"];

// --- Active tab handling ---
$active_tab = isset($_GET['active_tab']) ? $_GET['active_tab'] : 'chicken';

// --- Pagination for Chicken ---
$page_chicken = isset($_GET['page_chicken']) ? (int)$_GET['page_chicken'] : 1;
if ($page_chicken < 1) $page_chicken = 1;
$offset_chicken = ($page_chicken - 1) * $limit;

$totalQueryC = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category = 'chicken'");
$totalQueryC->execute();
$totalChicken = $totalQueryC->fetchColumn();
$totalPagesC = ceil($totalChicken / $limit);

$chickenQuery = $pdo->prepare("
    SELECT id, name FROM products 
    WHERE category = 'chicken'
    ORDER BY name ASC
    LIMIT :limit OFFSET :offset
");
$chickenQuery->bindValue(':limit', $limit, PDO::PARAM_INT);
$chickenQuery->bindValue(':offset', $offset_chicken, PDO::PARAM_INT);
$chickenQuery->execute();
$chickenProducts = $chickenQuery->fetchAll(PDO::FETCH_ASSOC);

// --- Pagination for Frozen ---
$page_frozen = isset($_GET['page_frozen']) ? (int)$_GET['page_frozen'] : 1;
if ($page_frozen < 1) $page_frozen = 1;
$offset_frozen = ($page_frozen - 1) * $limit;

$totalQueryF = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category = 'frozen'");
$totalQueryF->execute();
$totalFrozen = $totalQueryF->fetchColumn();
$totalPagesF = ceil($totalFrozen / $limit);

$frozenQuery = $pdo->prepare("
    SELECT id, name FROM products 
    WHERE category = 'frozen'
    ORDER BY name ASC
    LIMIT :limit OFFSET :offset
");
$frozenQuery->bindValue(':limit', $limit, PDO::PARAM_INT);
$frozenQuery->bindValue(':offset', $offset_frozen, PDO::PARAM_INT);
$frozenQuery->execute();
$frozenProducts = $frozenQuery->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="../assets/styles.css">

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
            <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- TABS -->
      <ul class="nav-tabs mb-3 inventory-tabs">
        <li><a href="#" class="<?= $active_tab=='chicken'?'active':'' ?>" data-target="chickenForm">🐔 Chicken</a></li>
        <li><a href="#" class="<?= $active_tab=='frozen'?'active':'' ?>" data-target="frozenForm">❄️ Frozen</a></li>
      </ul>


      <!-- CHICKEN PRODUCTS -->
      <div id="chickenForm" class="tab-content <?= $active_tab=='chicken'?'active':'' ?>">
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
                  <input type="number" step="0.01" min="0"
                         name="inv[chicken][<?= $p['id'] ?>]" 
                         class="form-control text-center">
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <!-- Chicken Pagination -->
        <?php if ($totalPagesC > 1): ?>
          <nav>
            <ul class="pagination justify-content-center">
              <?php for($i=1; $i<=$totalPagesC; $i++): ?>
                <li class="page-item <?= ($i==$page_chicken)?'active':'' ?>">
                  <a class="page-link" href="?page_chicken=<?= $i ?>&active_tab=chicken#chickenForm"><?= $i ?></a>
                </li>
              <?php endfor; ?>
            </ul>
          </nav>
        <?php endif; ?>

      </div>

      <!-- FROZEN PRODUCTS -->
      <div id="frozenForm" class="tab-content <?= $active_tab=='frozen'?'active':'' ?>">
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
                  <input type="number" step="0.01" min="0"
                         name="inv[frozen][<?= $p['id'] ?>]" 
                         class="form-control text-center">
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <!-- Frozen Pagination -->
        <?php if ($totalPagesF > 1): ?>
          <nav>
            <ul class="pagination justify-content-center">
              <?php for($i=1; $i<=$totalPagesF; $i++): ?>
                <li class="page-item <?= ($i==$page_frozen)?'active':'' ?>">
                  <a class="page-link" href="?page_frozen=<?= $i ?>&active_tab=frozen#frozenForm"><?= $i ?></a>
                </li>
              <?php endfor; ?>
            </ul>
          </nav>
        <?php endif; ?>

      </div>

      <button class="btn btn-primary mt-3">💾 Save Inventory</button>

    </form>
  </div>
</div>


<script>
document.querySelectorAll(".nav-tabs li a").forEach(tab => {
  tab.addEventListener("click", e => {
    e.preventDefault();
    // Switch active tab
    document.querySelectorAll(".nav-tabs li a").forEach(t => t.classList.remove("active"));
    tab.classList.add("active");
    // Show the correct tab content
    document.querySelectorAll(".tab-content").forEach(c => c.classList.remove("active"));
    document.getElementById(tab.dataset.target).classList.add("active");
  });
});

</script>


