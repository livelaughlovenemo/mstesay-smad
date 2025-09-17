<?php
// products.php
session_start();
if (!isset($_SESSION['user_id'])) header('Location: login.php');
require_once __DIR__ . '/includes/db.php';

$errors = [];

// CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = (float)$_POST['price'];

    if ($name === '' || $price <= 0) {
        $errors[] = "Name and valid price required.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (name, category, price) VALUES (:n,:c,:p)");
        $stmt->execute(['n'=>$name,'c'=>$category,'p'=>$price]);
        header("Location: products.php");
        exit;
    }
}

// UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = (float)$_POST['price'];

    if ($id>0 && $name !== '' && $price > 0) {
        $stmt = $pdo->prepare("UPDATE products SET name=:n, category=:c, price=:p WHERE id=:id");
        $stmt->execute(['n'=>$name,'c'=>$category,'p'=>$price,'id'=>$id]);
        header("Location: products.php");
        exit;
    } else {
        $errors[] = "Fill all fields correctly.";
    }
}

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id=:id");
    $stmt->execute(['id'=>$id]);
    header("Location: products.php");
    exit;
}

// FETCH PRODUCTS
$products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="row">
  <div class="col-12">
    <h3>Manage Products</h3>
  </div>

  <div class="col-md-5">
    <div class="card mb-3">
      <div class="card-body">
        <h5>Add Product</h5>
        <?php foreach($errors as $e): ?>
          <div class="alert alert-danger"><?=htmlspecialchars($e)?></div>
        <?php endforeach; ?>
        <form method="post">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Category</label>
            <input name="category" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Price (₱)</label>
            <input type="number" step="0.01" name="price" class="form-control" required>
          </div>
          <div class="d-grid">
            <button name="add_product" class="btn btn-primary">Add Product</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-7">
    <div class="card mb-3">
      <div class="card-body">
        <h5>Product List</h5>
        <table class="table table-sm table-hover">
          <thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($products as $p): ?>
              <tr>
                <td><?=$p['id']?></td>
                <td><?=htmlspecialchars($p['name'])?></td>
                <td><?=htmlspecialchars($p['category'])?></td>
                <td>₱ <?=number_format($p['price'],2)?></td>
                <td>
                  <!-- Edit Button triggers modal -->
                  <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#edit<?=$p['id']?>">Edit</button>
                  <a href="products.php?delete=<?=$p['id']?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this product?')">Delete</a>
                </td>
              </tr>

              <!-- Edit Modal -->
              <div class="modal fade" id="edit<?=$p['id']?>" tabindex="-1">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <form method="post">
                      <div class="modal-header"><h5 class="modal-title">Edit Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <input type="hidden" name="id" value="<?=$p['id']?>">
                        <div class="mb-3">
                          <label class="form-label">Name</label>
                          <input name="name" class="form-control" value="<?=htmlspecialchars($p['name'])?>" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Category</label>
                          <input name="category" class="form-control" value="<?=htmlspecialchars($p['category'])?>">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Price (₱)</label>
                          <input type="number" step="0.01" name="price" class="form-control" value="<?=number_format($p['price'],2,'.','')?>" required>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="submit" name="edit_product" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              <!-- End Edit Modal -->

            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
