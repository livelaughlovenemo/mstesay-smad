<?php
// daily_sales.php
session_start();
if (!isset($_SESSION['user_id'])) header('Location: login.php');

require_once __DIR__ . '/includes/db.php';
$errors = [];

// add sale
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sale'])) {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);
    if ($product_id <= 0 || $quantity <= 0) {
        $errors[] = 'Select product and enter a valid quantity.';
    } else {
        // fetch price
        $stmt = $pdo->prepare("SELECT price FROM products WHERE id = :id");
        $stmt->execute(['id' => $product_id]);
        $p = $stmt->fetch();
        if (!$p) $errors[] = 'Product not found.';
        else {
            $total = $p['price'] * $quantity;
            $stmt = $pdo->prepare("INSERT INTO sales (product_id, quantity, total_price, sale_datetime) VALUES (:pid, :qty, :tp, NOW())");
            $stmt->execute(['pid'=>$product_id, 'qty'=>$quantity, 'tp'=>$total]);
            header('Location: daily_sales.php');
            exit;
        }
    }
}

// fetch today's sales
$stmt = $pdo->query("SELECT s.*, p.name FROM sales s JOIN products p ON p.id = s.product_id WHERE DATE(s.sale_datetime)=CURDATE() ORDER BY s.sale_datetime DESC");
$sales = $stmt->fetchAll();

// fetch products
$products = $pdo->query("SELECT * FROM products ORDER BY name")->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<div class="row">
  <div class="col-md-6">
    <div class="card mb-3">
      <div class="card-body">
        <h5>Add Sale</h5>
        <?php if ($errors): foreach ($errors as $e): ?>
          <div class="alert alert-danger"><?=htmlspecialchars($e)?></div>
        <?php endforeach; endif; ?>
        <form method="post" action="daily_sales.php">
          <div class="mb-3">
            <label class="form-label">Product</label>
            <select name="product_id" class="form-select" required>
              <option value="">-- select product --</option>
              <?php foreach ($products as $pp): ?>
                <option value="<?= $pp['id'] ?>"><?= htmlspecialchars($pp['name']) ?> — ₱<?= number_format($pp['price'],2) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" class="form-control" min="1" value="1" required>
          </div>
          <div class="d-grid">
            <button name="add_sale" class="btn btn-primary">Add Sale</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card mb-3">
      <div class="card-body">
        <h5>Today's Sales</h5>
        <table class="table table-sm">
          <thead><tr><th>#</th><th>Product</th><th>Qty</th><th>Total</th><th>Time</th></tr></thead>
          <tbody>
            <?php if(!$sales): ?>
              <tr><td colspan="5" class="text-muted">No sales today.</td></tr>
            <?php else: foreach ($sales as $i => $s): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($s['name']) ?></td>
                <td><?= $s['quantity'] ?></td>
                <td>₱ <?= number_format($s['total_price'],2) ?></td>
                <td><?= (new DateTime($s['sale_datetime']))->format('H:i') ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
