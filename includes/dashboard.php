<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$role = $_SESSION['role']; 
$activeTab = isset($_GET["tab"]) ? $_GET["tab"] : "sales";

// Handle CRUD operations
include "db.php";

// Handle Sales CRUD
if (isset($_POST['add_sale'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $stmt = $pdo->prepare("INSERT INTO sales (product_id, quantity, total_price, sale_datetime) 
                          SELECT ?, ?, price * ?, NOW() FROM products WHERE id = ?");
    $stmt->execute([$product_id, $quantity, $quantity, $product_id]);
    header("Location: dashboard.php?tab=sales");
    exit;
}

if (isset($_POST['delete_sale'])) {
    $id = $_POST['sale_id'];
    $stmt = $pdo->prepare("DELETE FROM sales WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: dashboard.php?tab=sales");
    exit;
}

// Handle Inventory CRUD
if (isset($_POST['add_inventory'])) {
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $supplier = $_POST['supplier'];
    $kilos = $_POST['kilos'];
    $inv_date = $_POST['inv_date'];
    
    // Get location_id from supplier name
    $locStmt = $pdo->prepare("SELECT id FROM locations WHERE name = ?");
    $locStmt->execute([$supplier]);
    $location = $locStmt->fetch();
    $location_id = $location ? $location['id'] : null;
    
    $stmt = $pdo->prepare("INSERT INTO inventory (inv_date, category, product_name, supplier, location_id, kilos) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$inv_date, $category, $product_name, $supplier, $location_id, $kilos]);
    header("Location: dashboard.php?tab=inventory");
    exit;
}

if (isset($_POST['delete_inventory'])) {
    $id = $_POST['inv_id'];
    $stmt = $pdo->prepare("DELETE FROM inventory WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: dashboard.php?tab=inventory");
    exit;
}

// Handle Users CRUD
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $user_role = $_POST['role'];
    
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
    $stmt->execute([$username, $password_hash, $user_role]);
    header("Location: dashboard.php?tab=users");
    exit;
}

if (isset($_POST['delete_user'])) {
    $id = $_POST['user_id'];
    // Prevent deleting yourself
    if ($id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }
    header("Location: dashboard.php?tab=users");
    exit;
}

// Handle Product CRUD
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    
    $stmt = $pdo->prepare("INSERT INTO products (name, category, price) VALUES (?, ?, ?)");
    $stmt->execute([$name, $category, $price]);
    header("Location: dashboard.php?tab=products");
    exit;
}

if (isset($_POST['update_product'])) {
    $id = $_POST['product_id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $availability = isset($_POST['availability']) ? 1 : 0;
    
    $stmt = $pdo->prepare("UPDATE products SET name = ?, category = ?, price = ?, availability = ? WHERE id = ?");
    $stmt->execute([$name, $category, $price, $availability, $id]);
    header("Location: dashboard.php?tab=inventory_dashboard&page=" . $_GET['page']);
    exit;
}

if (isset($_POST['toggle_product_status'])) {
    $id = $_POST['product_id'];
    $action = $_POST['action']; // 'disable' or 'enable'
    $availability = ($action === 'disable') ? 0 : 1;
    
    $stmt = $pdo->prepare("UPDATE products SET availability = ? WHERE id = ?");
    $stmt->execute([$availability, $id]);
    header("Location: dashboard.php?tab=inventory_dashboard&page=" . $_GET['page']);
    exit;
}

include "header.php";

// Inventory Dashboard - Products with pagination and search
$items_per_page = 10;
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Build search query
$search_condition = '';
$params = [];
if (!empty($search_term)) {
    $search_condition = "WHERE name LIKE ? OR category LIKE ?";
    $search_param = "%$search_term%";
    $params = [$search_param, $search_param];
}

// Get total count
$countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM products $search_condition");
$countStmt->execute($params);
$total_items = $countStmt->fetch()['total'];
$total_pages = ceil($total_items / $items_per_page);

// Get products for current page - FIXED: Remove LIMIT/OFFSET from parameters
if (!empty($search_term)) {
    $query = "SELECT id, name, category, price, stock, availability FROM products WHERE name LIKE ? OR category LIKE ? ORDER BY name LIMIT $items_per_page OFFSET $offset";
    $productStmt = $pdo->prepare($query);
    $productStmt->execute([$search_param, $search_param]);
} else {
    $query = "SELECT id, name, category, price, stock, availability FROM products ORDER BY name LIMIT $items_per_page OFFSET $offset";
    $productStmt = $pdo->prepare($query);
    $productStmt->execute();
}

$products = $productStmt->fetchAll();

// Get inventory summary for each product
$inventorySummary = [];
foreach ($products as $product) {
    $invStmt = $pdo->prepare("
        SELECT SUM(kilos) as total_kilos 
        FROM inventory 
        WHERE product_name = ?
    ");
    $invStmt->execute([$product['name']]);
    $invData = $invStmt->fetch();
    $inventorySummary[$product['id']] = $invData['total_kilos'] ?? 0;
}
?>

<main class="dashboard-main">

  <!-- DASHBOARD HERO -->
  <section class="dashboard-hero">
      <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
      <p>Monitor your sales and inventory efficiently.</p>
  </section>

  <div class="tab-navigation">
    <button class="tab-btn <?= $activeTab === 'sales' ? 'active' : '' ?>" data-target="sales">
        📊 Sales Dashboard
    </button>

    <button class="tab-btn <?= $activeTab === 'inventory_dashboard' ? 'active' : '' ?>" data-target="inventory_dashboard">
        📋 Inventory Dashboard
    </button>

    <?php if($role == 'Admin' || $role == 'Manager'): ?>
    <button class="tab-btn <?= $activeTab === 'inventory' ? 'active' : '' ?>" data-target="inventory">
        📦 Inventory Records
    </button>
    <?php endif; ?>

    <?php if($role == 'Admin'): ?>
    <button class="tab-btn <?= $activeTab === 'products' ? 'active' : '' ?>" data-target="products">
        🛒 Manage Products
    </button>
    
    <button class="tab-btn <?= $activeTab === 'users' ? 'active' : '' ?>" data-target="users">
        👥 Manage Users
    </button>
    <?php endif; ?>
</div>

<div class="tab-container">

  <!-- SALES TAB -->
  <section id="sales" class="tab-content <?= $activeTab === 'sales' ? 'active' : '' ?>">
      <h3>Add New Sale</h3>
      <form method="POST" class="crud-form">
          <div class="form-row">
              <select name="product_id" required>
                  <option value="">Select Product</option>
                  <?php
                  $allProducts = $pdo->query("SELECT id, name, price FROM products ORDER BY name")->fetchAll();
                  foreach($allProducts as $product): ?>
                      <option value="<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?> - ₱<?= $product['price'] ?></option>
                  <?php endforeach; ?>
              </select>
              <input type="number" name="quantity" placeholder="Quantity" min="1" required>
              <button type="submit" name="add_sale" class="add-btn">Add Sale</button>
          </div>
      </form>
      
      <?php
      // Fetch sales with product names
      $salesStmt = $pdo->query("
          SELECT s.id, p.name AS product, s.quantity, s.total_price, s.sale_datetime
          FROM sales s
          JOIN products p ON s.product_id = p.id
          ORDER BY s.sale_datetime DESC
      ");
      $salesData = $salesStmt->fetchAll();
      
      // Fetch summary
      $summaryStmt = $pdo->query("
          SELECT p.name AS product, SUM(s.quantity) AS qty_sold, SUM(s.total_price) AS total_amount
          FROM sales s
          JOIN products p ON s.product_id = p.id
          GROUP BY p.name
          ORDER BY total_amount DESC
      ");
      $summaryData = $summaryStmt->fetchAll();
      ?>
      
      <h3>Recent Sales</h3>
      <table>
          <thead>
              <tr>
                  <th>Product</th>
                  <th>Quantity</th>
                  <th>Total Price</th>
                  <th>Date/Time</th>
                  <th>Actions</th>
              </tr>
          </thead>
          <tbody>
              <?php foreach($salesData as $sale): ?>
              <tr>
                  <td><?= htmlspecialchars($sale['product']) ?></td>
                  <td><?= $sale['quantity'] ?></td>
                  <td>₱<?= number_format($sale['total_price'], 2) ?></td>
                  <td><?= $sale['sale_datetime'] ?></td>
                  <td>
                     <?php if($product['availability']): ?>
<form method="POST" style="display:inline;">
    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
    <input type="hidden" name="action" value="disable">
    <button type="submit" name="toggle_product_status" class="action-btn disable-btn" onclick="return confirm('Disable this product? It will be hidden from sales but remain in database.')">Disable</button>
</form>
<?php else: ?>
<form method="POST" style="display:inline;">
    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
    <input type="hidden" name="action" value="enable">
    <button type="submit" name="toggle_product_status" class="action-btn enable-btn" onclick="return confirm('Enable this product? It will be available for sales again.')">Enable</button>
</form>
<?php endif; ?>
                  </td>
              </tr>
              <?php endforeach; ?>
          </tbody>
      </table>
      
      <h3>Sales Summary</h3>
      <table>
          <thead>
              <tr>
                  <th>Product</th>
                  <th>Quantity Sold</th>
                  <th>Total Amount</th>
              </tr>
          </thead>
          <tbody>
              <?php 
              $grandTotal = 0;
              foreach($summaryData as $sale): 
                  $grandTotal += $sale['total_amount'];
              ?>
              <tr>
                  <td><?= htmlspecialchars($sale['product']) ?></td>
                  <td><?= $sale['qty_sold'] ?></td>
                  <td>₱<?= number_format($sale['total_amount'], 2) ?></td>
              </tr>
              <?php endforeach; ?>
              <tr>
                  <td colspan="2" style="font-weight: bold;">Grand Total</td>
                  <td style="font-weight: bold;">₱<?= number_format($grandTotal, 2) ?></td>
              </tr>
          </tbody>
      </table>
  </section>

  <!-- INVENTORY DASHBOARD TAB -->
  <section id="inventory_dashboard" class="tab-content <?= $activeTab === 'inventory_dashboard' ? 'active' : '' ?>">
      <h3>Product Inventory</h3>
      
      <!-- Search Form -->
      <form method="GET" class="search-form">
          <input type="hidden" name="tab" value="inventory_dashboard">
          <div class="search-box">
              <input type="text" name="search" placeholder="Search products by name or category..." 
                     value="<?= htmlspecialchars($search_term) ?>">
              <button type="submit" class="search-btn">🔍 Search</button>
              <?php if(!empty($search_term)): ?>
                  <a href="?tab=inventory_dashboard" class="clear-search">Clear Search</a>
              <?php endif; ?>
          </div>
      </form>
      
      <!-- Products Table -->
      <div class="inventory-table-container">
          <table class="inventory-table">
              <thead>
                  <tr>
                      <th>Product Name</th>
                      <th>Category</th>
                      <th>Price</th>
                      <th>Total Inventory (Kilos)</th>
                      <th>Stock</th>
                      <th>Status</th>
                      <th>Actions</th>
                  </tr>
              </thead>
              <tbody>
                  <?php if(empty($products)): ?>
                  <tr>
                      <td colspan="7" style="text-align: center; padding: 30px;">
                          No products found. <?= !empty($search_term) ? 'Try a different search term.' : '' ?>
                      </td>
                  </tr>
                  <?php else: ?>
                  <?php foreach($products as $product): 
                      $total_kilos = $inventorySummary[$product['id']];
                      $stock_level = $product['stock'];
                      $status = $product['availability'] ? 'Available' : 'Out of Stock';
                      $status_class = $product['availability'] ? 'status-available' : 'status-outofstock';
                  ?>
                  <tr>
                      <td><?= htmlspecialchars($product['name']) ?></td>
                      <td><?= htmlspecialchars($product['category']) ?></td>
                      <td>₱<?= number_format($product['price'], 2) ?></td>
                      <td><?= number_format($total_kilos, 2) ?> kg</td>
                      <td><?= number_format($stock_level, 2) ?></td>
                      <td><span class="status-badge <?= $status_class ?>"><?= $status ?></span></td>
                      <td>
                          <button class="action-btn edit-btn" onclick="openEditModal(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name']) ?>', '<?= htmlspecialchars($product['category']) ?>', <?= $product['price'] ?>, <?= $product['availability'] ?>)">Edit</button>
                          <form method="POST" style="display:inline;">
                              <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                              <button type="submit" name="delete_product" class="action-btn delete-btn" onclick="return confirm('Delete this product?')">Delete</button>
                          </form>
                      </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php endif; ?>
              </tbody>
          </table>
      </div>
      
      <!-- Pagination -->
      <?php if($total_pages > 1): ?>
      <div class="pagination">
          <?php if($current_page > 1): ?>
              <a href="?tab=inventory_dashboard&page=<?= $current_page - 1 ?>&search=<?= urlencode($search_term) ?>" class="page-btn">
                  &laquo; Previous
              </a>
          <?php endif; ?>
          
          <?php for($i = 1; $i <= $total_pages; $i++): ?>
              <?php if($i == $current_page): ?>
                  <span class="page-btn current"><?= $i ?></span>
              <?php else: ?>
                  <a href="?tab=inventory_dashboard&page=<?= $i ?>&search=<?= urlencode($search_term) ?>" class="page-btn">
                      <?= $i ?>
                  </a>
              <?php endif; ?>
          <?php endfor; ?>
          
          <?php if($current_page < $total_pages): ?>
              <a href="?tab=inventory_dashboard&page=<?= $current_page + 1 ?>&search=<?= urlencode($search_term) ?>" class="page-btn">
                  Next &raquo;
              </a>
          <?php endif; ?>
      </div>
      <div class="pagination-info">
          Showing <?= count($products) ?> of <?= $total_items ?> products (Page <?= $current_page ?> of <?= $total_pages ?>)
      </div>
      <?php endif; ?>
      
      <!-- Add Product Button -->
      <div style="margin-top: 20px;">
          <button class="add-btn" onclick="openAddModal()">+ Add New Product</button>
      </div>
  </section>

  <!-- INVENTORY RECORDS TAB -->
  <?php if($role == 'Admin' || $role == 'Manager'): ?>
  <section id="inventory" class="tab-content <?= $activeTab === 'inventory' ? 'active' : '' ?>">
      <h3>Add Inventory Record</h3>
      <form method="POST" class="crud-form">
          <div class="form-row">
              <input type="date" name="inv_date" value="<?= date('Y-m-d') ?>" required>
              <input type="text" name="product_name" placeholder="Product Name" required>
              <input type="text" name="category" placeholder="Category">
              <select name="supplier" required>
                  <option value="">Select Supplier/Location</option>
                  <?php
                  $locations = $pdo->query("SELECT name FROM locations ORDER BY name")->fetchAll();
                  foreach($locations as $loc): ?>
                      <option value="<?= htmlspecialchars($loc['name']) ?>"><?= htmlspecialchars($loc['name']) ?></option>
                  <?php endforeach; ?>
                  <option value="Other">Other</option>
              </select>
              <input type="number" name="kilos" step="0.01" placeholder="Kilos" required>
              <button type="submit" name="add_inventory" class="add-btn">Add Record</button>
          </div>
      </form>
      
      <?php
      // Fetch all inventory records
      $invStmt = $pdo->query("
          SELECT id, inv_date, product_name, category, supplier, kilos, created_at
          FROM inventory 
          ORDER BY inv_date DESC, created_at DESC
      ");
      $inventoryData = $invStmt->fetchAll();
      ?>
      
      <h3>All Inventory Records</h3>
      <table>
          <thead>
              <tr>
                  <th>Date</th>
                  <th>Product</th>
                  <th>Category</th>
                  <th>Supplier/Location</th>
                  <th>Kilos</th>
                  <th>Created</th>
                  <th>Actions</th>
              </tr>
          </thead>
          <tbody>
              <?php 
              $invTotal = 0;
              foreach($inventoryData as $inv): 
                  $invTotal += $inv['kilos'];
              ?>
              <tr>
                  <td><?= $inv['inv_date'] ?></td>
                  <td><?= htmlspecialchars($inv['product_name']) ?></td>
                  <td><?= htmlspecialchars($inv['category']) ?></td>
                  <td><?= htmlspecialchars($inv['supplier']) ?></td>
                  <td><?= number_format($inv['kilos'], 2) ?></td>
                  <td><?= $inv['created_at'] ?></td>
                  <td>
                      <form method="POST" style="display:inline;">
                          <input type="hidden" name="inv_id" value="<?= $inv['id'] ?>">
                          <button type="submit" name="delete_inventory" class="delete-btn" onclick="return confirm('Delete this inventory record?')">Delete</button>
                      </form>
                  </td>
              </tr>
              <?php endforeach; ?>
              <tr>
                  <td colspan="4" style="font-weight: bold;">Total Inventory</td>
                  <td style="font-weight: bold;"><?= number_format($invTotal, 2) ?></td>
                  <td colspan="2"></td>
              </tr>
          </tbody>
      </table>
      
      <h3>Inventory Summary by Supplier</h3>
      <?php
      // Fetch inventory summary per supplier
      $summaryStmt = $pdo->query("
          SELECT product_name, supplier, SUM(kilos) AS total_kilos
          FROM inventory
          GROUP BY product_name, supplier
          ORDER BY product_name, supplier
      ");
      $summaryData = $summaryStmt->fetchAll();
      ?>
      <table>
          <thead>
              <tr>
                  <th>Product</th>
                  <th>Supplier / Location</th>
                  <th>Total Kilos</th>
              </tr>
          </thead>
          <tbody>
              <?php 
              $summaryTotal = 0;
              foreach($summaryData as $inv): 
                  $summaryTotal += $inv['total_kilos'];
              ?>
              <tr>
                  <td><?= htmlspecialchars($inv['product_name']) ?></td>
                  <td><?= htmlspecialchars($inv['supplier']) ?></td>
                  <td><?= number_format($inv['total_kilos'], 2) ?></td>
              </tr>
              <?php endforeach; ?>
              <tr>
                  <td colspan="2" style="font-weight: bold;">Total</td>
                  <td style="font-weight: bold;"><?= number_format($summaryTotal, 2) ?></td>
              </tr>
          </tbody>
      </table>
  </section>
  <?php endif; ?>

  <!-- PRODUCTS MANAGEMENT TAB -->
  <?php if($role == 'Admin'): ?>
  <section id="products" class="tab-content <?= $activeTab === 'products' ? 'active' : '' ?>">
      <h3>Add New Product</h3>
      <form method="POST" class="crud-form">
          <div class="form-row">
              <input type="text" name="name" placeholder="Product Name" required>
              <input type="text" name="category" placeholder="Category (e.g., chicken, frozen)">
              <input type="number" name="price" step="0.01" placeholder="Price" required>
              <button type="submit" name="add_product" class="add-btn">Add Product</button>
          </div>
      </form>
      
      <?php
      $allProducts = $pdo->query("SELECT id, name, category, price, created_at FROM products ORDER BY name")->fetchAll();
      ?>
      
      <h3>All Products</h3>
      <table>
          <thead>
              <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Category</th>
                  <th>Price</th>
                  <th>Created</th>
                  <th>Actions</th>
              </tr>
          </thead>
          <tbody>
              <?php foreach($allProducts as $product): ?>
              <tr>
                  <td><?= $product['id'] ?></td>
                  <td><?= htmlspecialchars($product['name']) ?></td>
                  <td><?= htmlspecialchars($product['category']) ?></td>
                  <td>₱<?= number_format($product['price'], 2) ?></td>
                  <td><?= $product['created_at'] ?></td>
                  <td>
                      <form method="POST" style="display:inline;">
                          <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                          <button type="submit" name="delete_product" class="delete-btn" onclick="return confirm('Delete this product?')">Delete</button>
                      </form>
                  </td>
              </tr>
              <?php endforeach; ?>
          </tbody>
      </table>
  </section>
  <?php endif; ?>

  <!-- USERS TAB -->
  <?php if($role == 'Admin'): ?>
  <section id="users" class="tab-content <?= $activeTab === 'users' ? 'active' : '' ?>">
      <h3>Add New User</h3>
      <form method="POST" class="crud-form">
          <div class="form-row">
              <input type="text" name="username" placeholder="Username" required>
              <input type="password" name="password" placeholder="Password" required>
              <select name="role" required>
                  <option value="Staff">Staff</option>
                  <option value="Manager">Manager</option>
                  <option value="Admin">Admin</option>
              </select>
              <button type="submit" name="add_user" class="add-btn">Add User</button>
          </div>
      </form>
      
      <?php
      $userStmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY id ASC");
      $users = $userStmt->fetchAll();
      ?>
      
      <h3>All Users</h3>
      <table>
          <thead>
              <tr>
                  <th>ID</th>
                  <th>Username</th>
                  <th>Role</th>
                  <th>Created At</th>
                  <th>Actions</th>
              </tr>
          </thead>
          <tbody>
              <?php foreach($users as $user): ?>
              <tr>
                  <td><?= $user['id'] ?></td>
                  <td><?= htmlspecialchars($user['username']) ?></td>
                  <td><?= $user['role'] ?></td>
                  <td><?= $user['created_at'] ?></td>
                  <td>
                      <?php if($user['id'] != $_SESSION['user_id']): ?>
                      <form method="POST" style="display:inline;">
                          <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                          <button type="submit" name="delete_user" class="delete-btn" onclick="return confirm('Delete this user?')">Delete</button>
                      </form>
                      <?php else: ?>
                      <span style="color: #666;">Current User</span>
                      <?php endif; ?>
                  </td>
              </tr>
              <?php endforeach; ?>
          </tbody>
      </table>
  </section>
  <?php endif; ?>

</div>

<!-- Edit Product Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeEditModal()">&times;</span>
        <h3>Edit Product</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="product_id" id="editProductId">
            <div class="form-group">
                <label>Product Name:</label>
                <input type="text" name="name" id="editProductName" required>
            </div>
            <div class="form-group">
                <label>Category:</label>
                <input type="text" name="category" id="editProductCategory" required>
            </div>
            <div class="form-group">
                <label>Price (₱):</label>
                <input type="number" name="price" id="editProductPrice" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Availability:</label>
                <select name="availability" id="editProductAvailability">
                    <option value="1">Available</option>
                    <option value="0">Out of Stock</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="button" class="cancel-btn" onclick="closeEditModal()">Cancel</button>
                <button type="submit" name="update_product" class="save-btn">Save Changes</button>
            </div>
        </form>
    </div>
</div>

</main>

<style>
/* Keep your existing styles */
.dashboard-main { font-family: 'Montserrat', sans-serif; padding: 120px 40px 60px 40px; background: #f4f4f4; min-height: 90vh; color: #333; }
.dashboard-hero { text-align: left; margin-bottom: 40px; background: linear-gradient(90deg, #fff5d7, #fff); padding: 30px 25px; border-radius: 18px; box-shadow: 0 6px 18px rgba(0,0,0,0.08); }
.dashboard-hero h1 { font-family: 'Urbanist', sans-serif; font-size: 36px; font-weight: 800; color: #F5A200; margin-bottom: 10px; }
.dashboard-hero p { font-size: 16px; color: #4a4a4a; }
.tab-navigation { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
.tab-btn { padding: 10px 18px; border: none; background: #fff3cd; cursor: pointer; border-radius: 12px; font-weight: 600; transition: 0.2s ease; white-space: nowrap; }
.tab-btn.active { background: #F5A200; color: white; }
.tab-content { display: none; animation: fadeIn 0.3s ease; background: #fff; padding: 25px; border-radius: 16px; box-shadow: 0 6px 20px rgba(0,0,0,0.08); margin-bottom: 20px; }
.tab-content.active { display: block; }

/* Search Form */
.search-form { margin-bottom: 25px; }
.search-box { display: flex; gap: 10px; align-items: center; }
.search-box input { flex: 1; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
.search-btn { background: #F5A200; color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }
.clear-search { color: #666; text-decoration: none; padding: 8px 12px; }
.clear-search:hover { color: #F5A200; }

/* Inventory Table */
.inventory-table-container { overflow-x: auto; margin: 20px 0; }
.inventory-table { width: 100%; border-collapse: collapse; }
.inventory-table th { background: #2c3e50; color: white; padding: 12px; text-align: left; }
.inventory-table td { padding: 12px; border-bottom: 1px solid #eee; }
.inventory-table tr:hover { background-color: #f8f9fa; }

/* Status Badges */
.status-badge { padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
.status-available { background-color: #d4edda; color: #155724; }
.status-outofstock { background-color: #f8d7da; color: #721c24; }

/* Action Buttons */
.action-btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; margin: 2px; }
.edit-btn { background: #ffc107; color: #333; }
.delete-btn { background: #dc3545; color: white; }

/* Pagination */
.pagination { display: flex; justify-content: center; gap: 5px; margin: 25px 0; flex-wrap: wrap; }
.page-btn { padding: 8px 14px; border: 1px solid #ddd; background: white; color: #333; text-decoration: none; border-radius: 4px; transition: all 0.3s; }
.page-btn:hover { background: #f5f5f5; border-color: #F5A200; }
.page-btn.current { background: #F5A200; color: white; border-color: #F5A200; }
.pagination-info { text-align: center; color: #666; font-size: 14px; margin-bottom: 20px; }

/* Modal Styles */
.modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
.modal-content { background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; position: relative; }
.close-modal { position: absolute; right: 20px; top: 15px; font-size: 24px; cursor: pointer; color: #666; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
.form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; }
.form-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 25px; }
.cancel-btn { padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 6px; cursor: pointer; }
.save-btn { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; }

/* CRUD Form Styles */
.crud-form { background: #f8f9fa; padding: 20px; border-radius: 12px; margin-bottom: 25px; }
.form-row { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.form-row input, .form-row select { padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; min-width: 150px; }
.form-row input:focus, .form-row select:focus { outline: none; border-color: #F5A200; }
.add-btn { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: background 0.3s; }
.add-btn:hover { background: #218838; }
.disable-btn {
    background: #ffc107;
    color: #333;
}

.enable-btn {
    background: #28a745;
    color: white;
}

.action-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    margin: 2px;
    transition: all 0.3s ease;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

/* Table Styles */
table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 30px; }
th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
th { background: #F5A200; color: white; font-weight: 600; }
tr:nth-child(even) { background-color: #f9f9f9; }
tr:hover { background-color: #f5f5f5; }

h3 { color: #333; margin: 25px 0 15px 0; font-size: 18px; border-bottom: 2px solid #F5A200; padding-bottom: 8px; }

@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
</style>

<script>
// tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

    btn.classList.add('active');
    document.getElementById(btn.dataset.target).classList.add('active');

    const newURL = "?tab=" + btn.dataset.target;
    history.replaceState(null, "", newURL);
  });
});

// Modal Functions
function openEditModal(id, name, category, price, availability) {
    document.getElementById('editProductId').value = id;
    document.getElementById('editProductName').value = name;
    document.getElementById('editProductCategory').value = category;
    document.getElementById('editProductPrice').value = price;
    document.getElementById('editProductAvailability').value = availability ? '1' : '0';
    document.getElementById('editForm').action = `?tab=inventory_dashboard&page=<?= $current_page ?>&search=<?= urlencode($search_term) ?>`;
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function openAddModal() {
    // You can implement add modal similarly or redirect to products tab
    window.location.href = '?tab=products';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        closeEditModal();
    }
}
</script>

