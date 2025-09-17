<div class="card mb-4">
  <div class="card-body">
    <h5>📦 Products Management</h5>
    <button class="view-btn" onclick="document.getElementById('addModal').style.display='block'">➕ Add Product</button>

    <table class="table table-bordered table-striped mt-3">
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
        require_once "includes/db.php";
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
        while ($row = $stmt->fetch()) {
          echo "<tr>
                  <td>{$row['id']}</td>
                  <td>{$row['name']}</td>
                  <td>{$row['category']}</td>
                  <td>{$row['unit']}</td>
                  <td>{$row['price']}</td>
                  <td>
                    <a href='products/edit.php?id={$row['id']}' class='view-btn'>✏️ Edit</a>
                    <a href='products/delete.php?id={$row['id']}' class='view-btn' style='background:red;'>🗑 Delete</a>
                  </td>
                </tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Product Modal -->
<div id="addModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
    <h3>Add New Product</h3>
    <form method="post" action="products/add.php">
      <input type="text" name="name" placeholder="Product Name" required>
      <select name="category">
        <option value="chicken">Chicken</option>
        <option value="frozen">Frozen</option>
      </select>
      <input type="text" name="unit" placeholder="Unit (e.g. kilo, pack)">
      <input type="number" step="0.01" name="price" placeholder="Price">
      <button class="view-btn">Save</button>
    </form>
  </div>
</div>
