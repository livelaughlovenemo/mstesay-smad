<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: ../login.php");
require_once "../includes/db.php";
?>
<!DOCTYPE html>
<html>
<head>
  <title>Products List</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
  <div class="main-content">
    <h2>📦 Product List</h2>
    <a href="add.php" class="view-btn">➕ Add Product</a>
    <table class="table-card">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Category</th>
          <th>Unit</th>
          <th>Price</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
        while ($row = $stmt->fetch()) {
          echo "<tr>
                  <td>{$row['id']}</td>
                  <td>{$row['name']}</td>
                  <td>{$row['category']}</td>
                  <td>{$row['unit']}</td>
                  <td>{$row['price']}</td>
                  <td>
                    <a href='edit.php?id={$row['id']}' class='view-btn'>✏️ Edit</a>
                    <a href='delete.php?id={$row['id']}' class='view-btn' style='background:red;'>🗑 Delete</a>
                  </td>
                </tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</body>
</html>
