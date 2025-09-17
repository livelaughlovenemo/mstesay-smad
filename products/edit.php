<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: ../login.php");
require_once "../includes/db.php";

$id = $_GET['id'] ?? null;
if (!$id) header("Location: list.php");

$stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $stmt = $pdo->prepare("UPDATE products SET name=?, category=?, unit=?, price=? WHERE id=?");
  $stmt->execute([$_POST['name'], $_POST['category'], $_POST['unit'], $_POST['price'], $id]);
  header("Location: list.php");
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit Product</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
  <div class="main-content">
    <h2>✏️ Edit Product</h2>
    <form method="post">
      <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
      <select name="category">
        <option value="chicken" <?= $product['category']=="chicken"?"selected":"" ?>>Chicken</option>
        <option value="frozen" <?= $product['category']=="frozen"?"selected":"" ?>>Frozen</option>
      </select>
      <input type="text" name="unit" value="<?= htmlspecialchars($product['unit']) ?>">
      <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>">
      <button class="view-btn">💾 Save Changes</button>
    </form>
    <a href="list.php">⬅️ Back to list</a>
  </div>
</body>
</html>
