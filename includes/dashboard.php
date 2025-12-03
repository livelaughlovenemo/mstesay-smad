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
    
    // Check if enough stock exists
    $checkStmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $checkStmt->execute([$product_id]);
    $product = $checkStmt->fetch();
    
    if ($product && $product['stock'] >= $quantity) {
        // Update stock
        $updateStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $updateStmt->execute([$quantity, $product_id]);
        
        // Add sale
        $stmt = $pdo->prepare("INSERT INTO sales (product_id, quantity, total_price, sale_datetime) 
                              SELECT ?, ?, price * ?, NOW() FROM products WHERE id = ?");
        $stmt->execute([$product_id, $quantity, $quantity, $product_id]);
    } else {
        $_SESSION['error'] = "Insufficient stock for this product!";
    }
    header("Location: dashboard.php?tab=sales");
    exit;
}

if (isset($_POST['delete_sale'])) {
    $id = $_POST['sale_id'];
    
    // Get sale details to restore stock
    $saleStmt = $pdo->prepare("SELECT product_id, quantity FROM sales WHERE id = ?");
    $saleStmt->execute([$id]);
    $sale = $saleStmt->fetch();
    
    if ($sale) {
        // Restore stock
        $restoreStmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        $restoreStmt->execute([$sale['quantity'], $sale['product_id']]);
        
        // Delete sale
        $stmt = $pdo->prepare("DELETE FROM sales WHERE id = ?");
        $stmt->execute([$id]);
    }
    header("Location: dashboard.php?tab=sales");
    exit;
}

// Handle Inventory CRUD
if (isset($_POST['add_inventory'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $inv_type = $_POST['inv_type']; // 'add' or 'subtract'
    $inv_date = $_POST['inv_date'];
    
    // Update stock based on type
    if ($inv_type === 'add') {
        $stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
    } else {
        // Check if enough stock exists before subtracting
        $checkStmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $checkStmt->execute([$product_id]);
        $product = $checkStmt->fetch();
        
        if ($product && $product['stock'] >= $quantity) {
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        } else {
            $_SESSION['error'] = "Cannot subtract more than available stock!";
            header("Location: dashboard.php?tab=inventory");
            exit;
        }
    }
    
    $stmt->execute([$quantity, $product_id]);
    
    // Record inventory transaction
    $recordStmt = $pdo->prepare("INSERT INTO inventory (product_id, quantity, inv_type, inv_date) VALUES (?, ?, ?, ?)");
    $recordStmt->execute([$product_id, $quantity, $inv_type, $inv_date]);
    
    header("Location: dashboard.php?tab=inventory");
    exit;
}

if (isset($_POST['delete_inventory'])) {
    $id = $_POST['inv_id'];
    
    // Get inventory record to adjust stock
    $invStmt = $pdo->prepare("SELECT product_id, quantity, inv_type FROM inventory WHERE id = ?");
    $invStmt->execute([$id]);
    $inv = $invStmt->fetch();
    
    if ($inv) {
        // Reverse the stock adjustment
        if ($inv['inv_type'] === 'add') {
            $adjustStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        } else {
            $adjustStmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        }
        $adjustStmt->execute([$inv['quantity'], $inv['product_id']]);
        
        // Delete inventory record
        $stmt = $pdo->prepare("DELETE FROM inventory WHERE id = ?");
        $stmt->execute([$id]);
    }
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
    
    $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock) VALUES (?, ?, ?, 0)");
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

// Get date filter for sales records
$sales_date_filter = isset($_GET['sales_filter']) ? $_GET['sales_filter'] : 'daily';
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Build sales query based on filter
$sales_date_condition = '';
$sales_group_by = '';
switch($sales_date_filter) {
    case 'daily':
        $sales_date_condition = "DATE(s.sale_datetime) = CURDATE()";
        $sales_group_by = "DATE(s.sale_datetime), p.name";
        break;
    case 'monthly':
        $sales_date_condition = "YEAR(s.sale_datetime) = YEAR(CURDATE()) AND MONTH(s.sale_datetime) = MONTH(CURDATE())";
        $sales_group_by = "DATE_FORMAT(s.sale_datetime, '%Y-%m'), p.name";
        break;
    case 'yearly':
        $sales_date_condition = "YEAR(s.sale_datetime) = YEAR(CURDATE())";
        $sales_group_by = "YEAR(s.sale_datetime), p.name";
        break;
    case 'custom_month':
        $sales_date_condition = "DATE_FORMAT(s.sale_datetime, '%Y-%m') = '$selected_month'";
        $sales_group_by = "DATE(s.sale_datetime), p.name";
        break;
    case 'custom_year':
        $sales_date_condition = "YEAR(s.sale_datetime) = '$selected_year'";
        $sales_group_by = "MONTH(s.sale_datetime), p.name";
        break;
    case 'all':
    default:
        $sales_date_condition = "1=1";
        $sales_group_by = "DATE(s.sale_datetime), p.name";
        break;
}

// Get sales records
$salesRecordsStmt = $pdo->prepare("
    SELECT 
        DATE(s.sale_datetime) as sale_date,
        p.name as product_name,
        p.category,
        SUM(s.quantity) as total_quantity,
        SUM(s.total_price) as total_amount,
        COUNT(s.id) as sales_count
    FROM sales s
    JOIN products p ON s.product_id = p.id
    WHERE $sales_date_condition
    GROUP BY $sales_group_by
    ORDER BY sale_date DESC, total_amount DESC
");
$salesRecordsStmt->execute();
$salesRecords = $salesRecordsStmt->fetchAll();

// Get top products
$topProductsStmt = $pdo->query("
    SELECT 
        p.name as product_name,
        p.category,
        SUM(s.quantity) as total_quantity,
        SUM(s.total_price) as total_amount,
        COUNT(s.id) as sales_count
    FROM sales s
    JOIN products p ON s.product_id = p.id
    GROUP BY p.id, p.name, p.category
    ORDER BY total_amount DESC
    LIMIT 10
");
$topProducts = $topProductsStmt->fetchAll();

// Get top suppliers (if supplier data exists)
$topSuppliersStmt = $pdo->query("
    SELECT 
        supplier,
        COUNT(DISTINCT product_name) as products_count,
        SUM(kilos) as total_kilos,
        COUNT(id) as transactions_count
    FROM inventory 
    WHERE supplier IS NOT NULL AND supplier != ''
    GROUP BY supplier
    ORDER BY total_kilos DESC
    LIMIT 10
");
$topSuppliers = $topSuppliersStmt->fetchAll();

// Get monthly summary for admin
$monthlySummary = [];
if ($role == 'Admin') {
    $monthlyStmt = $pdo->query("
        SELECT 
            DATE_FORMAT(s.sale_datetime, '%Y-%m') as month,
            COUNT(DISTINCT DATE(s.sale_datetime)) as days_with_sales,
            SUM(s.quantity) as total_quantity,
            SUM(s.total_price) as total_amount,
            COUNT(s.id) as total_sales
        FROM sales s
        GROUP BY DATE_FORMAT(s.sale_datetime, '%Y-%m')
        ORDER BY month DESC
    ");
    $monthlySummary = $monthlyStmt->fetchAll();
}

// Inventory Dashboard - Products with pagination, search and sorting
$items_per_page = 10;
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'asc';

// Validate sort parameters
$allowed_sorts = ['id', 'name', 'category', 'price', 'stock', 'availability'];
$sort_by = in_array($sort_by, $allowed_sorts) ? $sort_by : 'name';
$sort_order = in_array(strtolower($sort_order), ['asc', 'desc']) ? strtolower($sort_order) : 'asc';

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

// Get products for current page with sorting
if (!empty($search_term)) {
    $query = "SELECT id, name, category, price, stock, availability FROM products WHERE name LIKE ? OR category LIKE ? ORDER BY $sort_by $sort_order LIMIT $items_per_page OFFSET $offset";
    $productStmt = $pdo->prepare($query);
    $productStmt->execute([$search_param, $search_param]);
} else {
    $query = "SELECT id, name, category, price, stock, availability FROM products ORDER BY $sort_by $sort_order LIMIT $items_per_page OFFSET $offset";
    $productStmt = $pdo->prepare($query);
    $productStmt->execute();
}

$products = $productStmt->fetchAll();
?>

<main class="dashboard-main">
  <div class="tab-navigation">
    <button class="tab-btn <?= $activeTab === 'sales' ? 'active' : '' ?>" data-target="sales">
        📊 Sales Dashboard
    </button>

    <button class="tab-btn <?= $activeTab === 'sales_records' ? 'active' : '' ?>" data-target="sales_records">
        📈 Sales Records
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
    <button class="tab-btn <?= $activeTab === 'monthly_reports' ? 'active' : '' ?>" data-target="monthly_reports">
        📅 Monthly Reports
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

  <section id="sales" class="tab-content <?= $activeTab === 'sales' ? 'active' : '' ?>">
      <?php if(isset($_SESSION['error'])): ?>
          <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
              <?= $_SESSION['error']; unset($_SESSION['error']); ?>
          </div>
      <?php endif; ?>
      
      <h3>Add New Sale</h3>
      <form method="POST" class="crud-form">
          <div class="form-row">
              <select name="product_id" required>
                  <option value="">Select Product</option>
                  <?php
                  $availableProducts = $pdo->query("SELECT id, name, price, stock FROM products WHERE availability = 1 ORDER BY name")->fetchAll();
                  foreach($availableProducts as $product): ?>
                      <option value="<?= $product['id'] ?>">
                          <?= htmlspecialchars($product['name']) ?> - ₱<?= $product['price'] ?> (Stock: <?= $product['stock'] ?>)
                      </option>
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
                      <form method="POST" style="display:inline;">
                          <input type="hidden" name="sale_id" value="<?= $sale['id'] ?>">
                          <button type="submit" name="delete_sale" class="delete-btn" onclick="return confirm('Delete this sale? Stock will be restored.')">Delete</button>
                      </form>
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


  <section id="sales_records" class="tab-content <?= $activeTab === 'sales_records' ? 'active' : '' ?>">
      <h3>Sales Records & Analytics</h3>
      

      <div class="date-filter" style="margin: 20px 0;">
          <h4>Filter Sales Records:</h4>
          <div class="filter-buttons">
              <a href="?tab=sales_records&sales_filter=daily" class="filter-btn <?= $sales_date_filter == 'daily' ? 'active' : '' ?>">Today</a>
              <a href="?tab=sales_records&sales_filter=monthly" class="filter-btn <?= $sales_date_filter == 'monthly' ? 'active' : '' ?>">This Month</a>
              <a href="?tab=sales_records&sales_filter=yearly" class="filter-btn <?= $sales_date_filter == 'yearly' ? 'active' : '' ?>">This Year</a>
              <a href="?tab=sales_records&sales_filter=all" class="filter-btn <?= $sales_date_filter == 'all' ? 'active' : '' ?>">All Time</a>
              
              <!-- Custom Month Selector -->
              <div class="custom-filter" style="display: inline-block; margin-left: 15px;">
                  <form method="GET" style="display: inline;">
                      <input type="hidden" name="tab" value="sales_records">
                      <input type="hidden" name="sales_filter" value="custom_month">
                      <input type="month" name="month" value="<?= $selected_month ?>" onchange="this.form.submit()" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                  </form>
              </div>
              
              <!-- Custom Year Selector -->
              <div class="custom-filter" style="display: inline-block; margin-left: 10px;">
                  <form method="GET" style="display: inline;">
                      <input type="hidden" name="tab" value="sales_records">
                      <input type="hidden" name="sales_filter" value="custom_year">
                      <select name="year" onchange="this.form.submit()" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                          <?php for($y = 2020; $y <= date('Y'); $y++): ?>
                              <option value="<?= $y ?>" <?= $selected_year == $y ? 'selected' : '' ?>><?= $y ?></option>
                          <?php endfor; ?>
                      </select>
                  </form>
              </div>
          </div>
      </div>
      
      <?php
      $totalSalesAmount = 0;
      $totalSalesQuantity = 0;
      foreach($salesRecords as $record) {
          $totalSalesAmount += $record['total_amount'];
          $totalSalesQuantity += $record['total_quantity'];
      }
      ?>
      <div class="sales-stats" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
          <h4>Sales Summary (<?= ucfirst(str_replace('_', ' ', $sales_date_filter)) ?>)</h4>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
              <div class="stat-card">
                  <div class="stat-label">Total Sales Amount</div>
                  <div class="stat-value" style="color: #28a745;">₱<?= number_format($totalSalesAmount, 2) ?></div>
              </div>
              <div class="stat-card">
                  <div class="stat-label">Total Quantity Sold</div>
                  <div class="stat-value"><?= number_format($totalSalesQuantity, 2) ?></div>
              </div>
              <div class="stat-card">
                  <div class="stat-label">Number of Sales</div>
                  <div class="stat-value"><?= count($salesRecords) ?></div>
              </div>
              <div class="stat-card">
                  <div class="stat-label">Average Sale Value</div>
                  <div class="stat-value">₱<?= count($salesRecords) > 0 ? number_format($totalSalesAmount / count($salesRecords), 2) : '0.00' ?></div>
              </div>
          </div>
      </div>
      
      <h4>Sales Records</h4>
      <div class="table-container" style="overflow-x: auto;">
          <table>
              <thead>
                  <tr>
                      <th>Date</th>
                      <th>Product</th>
                      <th>Category</th>
                      <th>Quantity Sold</th>
                      <th>Total Amount</th>
                      <th>Sales Count</th>
                  </tr>
              </thead>
              <tbody>
                  <?php if(empty($salesRecords)): ?>
                  <tr>
                      <td colspan="6" style="text-align: center; padding: 30px;">
                          No sales records found for the selected period.
                      </td>
                  </tr>
                  <?php else: ?>
                  <?php foreach($salesRecords as $record): ?>
                  <tr>
                      <td><?= $record['sale_date'] ?></td>
                      <td><?= htmlspecialchars($record['product_name']) ?></td>
                      <td><?= htmlspecialchars($record['category']) ?></td>
                      <td><?= number_format($record['total_quantity'], 2) ?></td>
                      <td>₱<?= number_format($record['total_amount'], 2) ?></td>
                      <td><?= $record['sales_count'] ?></td>
                  </tr>
                  <?php endforeach; ?>
                  <?php endif; ?>
              </tbody>
          </table>
      </div>
      
      <!-- Top Products Section -->
      <h4 style="margin-top: 40px;">Top Selling Products (All Time)</h4>
      <div class="table-container" style="overflow-x: auto;">
          <table>
              <thead>
                  <tr>
                      <th>Rank</th>
                      <th>Product</th>
                      <th>Category</th>
                      <th>Quantity Sold</th>
                      <th>Total Revenue</th>
                      <th>Sales Count</th>
                  </tr>
              </thead>
              <tbody>
                  <?php if(empty($topProducts)): ?>
                  <tr>
                      <td colspan="6" style="text-align: center; padding: 30px;">
                          No sales data available.
                      </td>
                  </tr>
                  <?php else: ?>
                  <?php $rank = 1; foreach($topProducts as $product): ?>
                  <tr>
                      <td><?= $rank++ ?></td>
                      <td><?= htmlspecialchars($product['product_name']) ?></td>
                      <td><?= htmlspecialchars($product['category']) ?></td>
                      <td><?= number_format($product['total_quantity'], 2) ?></td>
                      <td>₱<?= number_format($product['total_amount'], 2) ?></td>
                      <td><?= $product['sales_count'] ?></td>
                  </tr>
                  <?php endforeach; ?>
                  <?php endif; ?>
              </tbody>
          </table>
      </div>
      
      <!-- Top Suppliers Section -->
      <?php if(!empty($topSuppliers)): ?>
      <h4 style="margin-top: 40px;">Top Suppliers by Inventory Contribution</h4>
      <div class="table-container" style="overflow-x: auto;">
          <table>
              <thead>
                  <tr>
                      <th>Rank</th>
                      <th>Supplier</th>
                      <th>Products Supplied</th>
                      <th>Total Kilos</th>
                      <th>Transactions</th>
                  </tr>
              </thead>
              <tbody>
                  <?php $rank = 1; foreach($topSuppliers as $supplier): ?>
                  <tr>
                      <td><?= $rank++ ?></td>
                      <td><?= htmlspecialchars($supplier['supplier']) ?></td>
                      <td><?= $supplier['products_count'] ?></td>
                      <td><?= number_format($supplier['total_kilos'], 2) ?> kg</td>
                      <td><?= $supplier['transactions_count'] ?></td>
                  </tr>
                  <?php endforeach; ?>
              </tbody>
          </table>
      </div>
      <?php endif; ?>
  </section>

  <!-- INVENTORY DASHBOARD TAB -->
  <section id="inventory_dashboard" class="tab-content <?= $activeTab === 'inventory_dashboard' ? 'active' : '' ?>">
      <h3>Product Inventory</h3>
      
      <!-- Search and Sort Form -->
      <form method="GET" class="search-form">
          <input type="hidden" name="tab" value="inventory_dashboard">
          <div class="search-sort-container">
              <div class="search-box">
                  <input type="text" name="search" placeholder="Search products by name or category..." 
                         value="<?= htmlspecialchars($search_term) ?>">
                  <button type="submit" class="search-btn">🔍 Search</button>
                  <?php if(!empty($search_term)): ?>
                      <a href="?tab=inventory_dashboard" class="clear-search">Clear Search</a>
                  <?php endif; ?>
              </div>
              
              <div class="sort-options">
                  <label>Sort by:</label>
                  <select name="sort" onchange="this.form.submit()">
                      <option value="name" <?= $sort_by == 'name' ? 'selected' : '' ?>>Name</option>
                      <option value="category" <?= $sort_by == 'category' ? 'selected' : '' ?>>Category</option>
                      <option value="price" <?= $sort_by == 'price' ? 'selected' : '' ?>>Price</option>
                      <option value="stock" <?= $sort_by == 'stock' ? 'selected' : '' ?>>Stock</option>
                      <option value="availability" <?= $sort_by == 'availability' ? 'selected' : '' ?>>Status</option>
                  </select>
                  
                  <select name="order" onchange="this.form.submit()">
                      <option value="asc" <?= $sort_order == 'asc' ? 'selected' : '' ?>>Ascending</option>
                      <option value="desc" <?= $sort_order == 'desc' ? 'selected' : '' ?>>Descending</option>
                  </select>
              </div>
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
                      <th>Stock</th>
                      <th>Status</th>
                      <th>Actions</th>
                  </tr>
              </thead>
              <tbody>
                  <?php if(empty($products)): ?>
                  <tr>
                      <td colspan="6" style="text-align: center; padding: 30px;">
                          No products found. <?= !empty($search_term) ? 'Try a different search term.' : '' ?>
                      </td>
                  </tr>
                  <?php else: ?>
                  <?php foreach($products as $product): 
                      $stock_level = $product['stock'];
                      $status = $product['availability'] ? 'Available' : 'Out of Stock';
                      $status_class = $product['availability'] ? 'status-available' : 'status-outofstock';
                  ?>
                  <tr>
                      <td><?= htmlspecialchars($product['name']) ?></td>
                      <td><?= htmlspecialchars($product['category']) ?></td>
                      <td>₱<?= number_format($product['price'], 2) ?></td>
                      <td><?= number_format($stock_level, 2) ?></td>
                      <td><span class="status-badge <?= $status_class ?>"><?= $status ?></span></td>
                      <td>
                          <button class="action-btn edit-btn" onclick="openEditModal(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name']) ?>', '<?= htmlspecialchars($product['category']) ?>', <?= $product['price'] ?>, <?= $product['availability'] ?>)">Edit</button>
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
                  <?php endif; ?>
              </tbody>
          </table>
      </div>
      
      <!-- Pagination -->
      <?php if($total_pages > 1): ?>
      <div class="pagination">
          <?php if($current_page > 1): ?>
              <a href="?tab=inventory_dashboard&page=<?= $current_page - 1 ?>&search=<?= urlencode($search_term) ?>&sort=<?= $sort_by ?>&order=<?= $sort_order ?>" class="page-btn">
                  &laquo; Previous
              </a>
          <?php endif; ?>
          
          <?php for($i = 1; $i <= $total_pages; $i++): ?>
              <?php if($i == $current_page): ?>
                  <span class="page-btn current"><?= $i ?></span>
              <?php else: ?>
                  <a href="?tab=inventory_dashboard&page=<?= $i ?>&search=<?= urlencode($search_term) ?>&sort=<?= $sort_by ?>&order=<?= $sort_order ?>" class="page-btn">
                      <?= $i ?>
                  </a>
              <?php endif; ?>
          <?php endfor; ?>
          
          <?php if($current_page < $total_pages): ?>
              <a href="?tab=inventory_dashboard&page=<?= $current_page + 1 ?>&search=<?= urlencode($search_term) ?>&sort=<?= $sort_by ?>&order=<?= $sort_order ?>" class="page-btn">
                  Next &raquo;
              </a>
          <?php endif; ?>
      </div>
      <div class="pagination-info">
          Showing <?= count($products) ?> of <?= $total_items ?> products (Page <?= $current_page ?> of <?= $total_pages ?>)
          <?php if($sort_by != 'name' || $sort_order != 'asc'): ?>
              • Sorted by: <?= ucfirst($sort_by) ?> (<?= $sort_order ?>)
          <?php endif; ?>
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
      <?php if(isset($_SESSION['error'])): ?>
          <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
              <?= $_SESSION['error']; unset($_SESSION['error']); ?>
          </div>
      <?php endif; ?>
      
      <h3>Adjust Stock</h3>
      <form method="POST" class="crud-form">
          <div class="form-row">
              <select name="product_id" required>
                  <option value="">Select Product</option>
                  <?php
                  $allProducts = $pdo->query("SELECT id, name, stock FROM products ORDER BY name")->fetchAll();
                  foreach($allProducts as $product): ?>
                      <option value="<?= $product['id'] ?>">
                          <?= htmlspecialchars($product['name']) ?> (Current Stock: <?= $product['stock'] ?>)
                      </option>
                  <?php endforeach; ?>
              </select>
              <select name="inv_type" required>
                  <option value="add">Add Stock</option>
                  <option value="subtract">Subtract Stock</option>
              </select>
              <input type="number" name="quantity" placeholder="Quantity" step="0.01" min="0.01" required>
              <input type="date" name="inv_date" value="<?= date('Y-m-d') ?>" required>
              <button type="submit" name="add_inventory" class="add-btn">Adjust Stock</button>
          </div>
      </form>
      
      <?php
      // Date filter
      $date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'all';
      $current_date = date('Y-m-d');
      $current_month = date('Y-m');
      $current_year = date('Y');
      
      // Build date filter condition
      $date_condition = '';
      switch($date_filter) {
          case 'daily':
              $date_condition = "DATE(inv_date) = CURDATE()";
              break;
          case 'monthly':
              $date_condition = "YEAR(inv_date) = YEAR(CURDATE()) AND MONTH(inv_date) = MONTH(CURDATE())";
              break;
          case 'yearly':
              $date_condition = "YEAR(inv_date) = YEAR(CURDATE())";
              break;
          case 'all':
          default:
              $date_condition = "1=1";
              break;
      }
      
      // Fetch inventory records with product names
      $invStmt = $pdo->prepare("
          SELECT i.id, i.inv_date, p.name AS product_name, i.quantity, i.inv_type, i.created_at
          FROM inventory i
          JOIN products p ON i.product_id = p.id
          WHERE $date_condition
          ORDER BY i.inv_date DESC, i.created_at DESC
      ");
      $invStmt->execute();
      $inventoryData = $invStmt->fetchAll();
      
      // Calculate totals
      $addedTotal = 0;
      $subtractedTotal = 0;
      foreach($inventoryData as $inv) {
          if ($inv['inv_type'] === 'add') {
              $addedTotal += $inv['quantity'];
          } else {
              $subtractedTotal += $inv['quantity'];
          }
      }
      $netChange = $addedTotal - $subtractedTotal;
      ?>
      
      <!-- Date Filter Options -->
      <div class="date-filter" style="margin: 20px 0;">
          <h4>Filter by Date:</h4>
          <div class="filter-buttons">
              <a href="?tab=inventory&date_filter=all" class="filter-btn <?= $date_filter == 'all' ? 'active' : '' ?>">All Records</a>
              <a href="?tab=inventory&date_filter=daily" class="filter-btn <?= $date_filter == 'daily' ? 'active' : '' ?>">Daily</a>
              <a href="?tab=inventory&date_filter=monthly" class="filter-btn <?= $date_filter == 'monthly' ? 'active' : '' ?>">Monthly</a>
              <a href="?tab=inventory&date_filter=yearly" class="filter-btn <?= $date_filter == 'yearly' ? 'active' : '' ?>">Yearly</a>
          </div>
      </div>
      
      
      <h3>All Inventory Records</h3>
      <table>
          <thead>
              <tr>
                  <th>Date</th>
                  <th>Product</th>
                  <th>Quantity</th>
                  <th>Type</th>
                  <th>Created</th>
                  <th>Actions</th>
              </tr>
          </thead>
          <tbody>
              <?php 
              foreach($inventoryData as $inv): 
                  $type_class = $inv['inv_type'] === 'add' ? 'type-add' : 'type-subtract';
                  $type_symbol = $inv['inv_type'] === 'add' ? '+' : '-';
              ?>
              <tr>
                  <td><?= $inv['inv_date'] ?></td>
                  <td><?= htmlspecialchars($inv['product_name']) ?></td>
                  <td><?= $type_symbol ?> <?= number_format($inv['quantity'], 2) ?></td>
                  <td><span class="type-badge <?= $type_class ?>"><?= ucfirst($inv['inv_type']) ?></span></td>
                  <td><?= $inv['created_at'] ?></td>
                  <td>
                      <form method="POST" style="display:inline;">
                          <input type="hidden" name="inv_id" value="<?= $inv['id'] ?>">
                          <button type="submit" name="delete_inventory" class="delete-btn" onclick="return confirm('Delete this inventory record? Stock will be adjusted accordingly.')">Delete</button>
                      </form>
                  </td>
              </tr>
              <?php endforeach; ?>
          </tbody>
      </table>
  </section>
  <?php endif; ?>

  <!-- MONTHLY REPORTS TAB (Admin Only) -->
  <?php if($role == 'Admin'): ?>
  <section id="monthly_reports" class="tab-content <?= $activeTab === 'monthly_reports' ? 'active' : '' ?>">
      <h3>📅 Monthly Sales Reports (Admin Only)</h3>
      
      <div class="reports-intro" style="background: #e8f4fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #2196F3;">
          <h4>📊 Executive Summary</h4>
          <p>View detailed monthly sales performance, track revenue trends, and analyze business growth over time.</p>
      </div>
      
      <!-- Monthly Summary Table -->
      <h4>Monthly Sales Performance</h4>
      <div class="table-container" style="overflow-x: auto; margin-bottom: 30px;">
          <table>
              <thead>
                  <tr>
                      <th>Month</th>
                      <th>Days with Sales</th>
                      <th>Total Quantity Sold</th>
                      <th>Total Revenue</th>
                      <th>Total Sales Count</th>
                      <th>Avg Daily Revenue</th>
                  </tr>
              </thead>
              <tbody>
                  <?php if(empty($monthlySummary)): ?>
                  <tr>
                      <td colspan="6" style="text-align: center; padding: 30px;">
                          No monthly sales data available.
                      </td>
                  </tr>
                  <?php else: ?>
                  <?php 
                  $grandTotalRevenue = 0;
                  $grandTotalQuantity = 0;
                  $grandTotalSales = 0;
                  foreach($monthlySummary as $month): 
                      $grandTotalRevenue += $month['total_amount'];
                      $grandTotalQuantity += $month['total_quantity'];
                      $grandTotalSales += $month['total_sales'];
                      $avgDailyRevenue = $month['days_with_sales'] > 0 ? $month['total_amount'] / $month['days_with_sales'] : 0;
                  ?>
                  <tr>
                      <td><strong><?= $month['month'] ?></strong></td>
                      <td><?= $month['days_with_sales'] ?></td>
                      <td><?= number_format($month['total_quantity'], 2) ?></td>
                      <td>₱<?= number_format($month['total_amount'], 2) ?></td>
                      <td><?= $month['total_sales'] ?></td>
                      <td>₱<?= number_format($avgDailyRevenue, 2) ?></td>
                  </tr>
                  <?php endforeach; ?>
                  <!-- Grand Total Row -->
                  <tr style="background-color: #f8f9fa; font-weight: bold;">
                      <td><strong>GRAND TOTAL</strong></td>
                      <td>-</td>
                      <td><?= number_format($grandTotalQuantity, 2) ?></td>
                      <td>₱<?= number_format($grandTotalRevenue, 2) ?></td>
                      <td><?= $grandTotalSales ?></td>
                      <td>-</td>
                  </tr>
                  <?php endif; ?>
              </tbody>
          </table>
      </div>
      
      <!-- Yearly Summary -->
      <?php
      // Get yearly summary
      $yearlyStmt = $pdo->query("
          SELECT 
              YEAR(s.sale_datetime) as year,
              COUNT(DISTINCT MONTH(s.sale_datetime)) as months_with_sales,
              SUM(s.quantity) as total_quantity,
              SUM(s.total_price) as total_amount,
              COUNT(s.id) as total_sales
          FROM sales s
          GROUP BY YEAR(s.sale_datetime)
          ORDER BY year DESC
      ");
      $yearlySummary = $yearlyStmt->fetchAll();
      ?>
      
      <h4>Yearly Sales Performance</h4>
      <div class="table-container" style="overflow-x: auto; margin-bottom: 30px;">
          <table>
              <thead>
                  <tr>
                      <th>Year</th>
                      <th>Months with Sales</th>
                      <th>Total Quantity Sold</th>
                      <th>Total Revenue</th>
                      <th>Total Sales Count</th>
                      <th>Avg Monthly Revenue</th>
                  </tr>
              </thead>
              <tbody>
                  <?php if(empty($yearlySummary)): ?>
                  <tr>
                      <td colspan="6" style="text-align: center; padding: 30px;">
                          No yearly sales data available.
                      </td>
                  </tr>
                  <?php else: ?>
                  <?php foreach($yearlySummary as $year): 
                      $avgMonthlyRevenue = $year['months_with_sales'] > 0 ? $year['total_amount'] / $year['months_with_sales'] : 0;
                  ?>
                  <tr>
                      <td><strong><?= $year['year'] ?></strong></td>
                      <td><?= $year['months_with_sales'] ?></td>
                      <td><?= number_format($year['total_quantity'], 2) ?></td>
                      <td>₱<?= number_format($year['total_amount'], 2) ?></td>
                      <td><?= $year['total_sales'] ?></td>
                      <td>₱<?= number_format($avgMonthlyRevenue, 2) ?></td>
                  </tr>
                  <?php endforeach; ?>
                  <?php endif; ?>
              </tbody>
          </table>
      </div>
      
      <!-- Best Performing Months -->
      <h4>📈 Top Performing Months</h4>
      <div class="table-container" style="overflow-x: auto; margin-bottom: 30px;">
          <table>
              <thead>
                  <tr>
                      <th>Rank</th>
                      <th>Month</th>
                      <th>Revenue</th>
                      <th>Quantity Sold</th>
                      <th>Sales Count</th>
                      <th>Performance</th>
                  </tr>
              </thead>
              <tbody>
                  <?php if(empty($monthlySummary)): ?>
                  <tr>
                      <td colspan="6" style="text-align: center; padding: 30px;">
                          No data available.
                      </td>
                  </tr>
                  <?php else: 
                      // Sort monthly summary by revenue descending
                      usort($monthlySummary, function($a, $b) {
                          return $b['total_amount'] <=> $a['total_amount'];
                      });
                      
                      $rank = 1;
                      foreach(array_slice($monthlySummary, 0, 5) as $month):
                          $performance = '';
                          if ($month['total_amount'] > 100000) {
                              $performance = 'Excellent ★★★';
                          } elseif ($month['total_amount'] > 50000) {
                              $performance = 'Good ★★';
                          } elseif ($month['total_amount'] > 20000) {
                              $performance = 'Average ★';
                          } else {
                              $performance = 'Needs Improvement';
                          }
                  ?>
                  <tr>
                      <td><?= $rank++ ?></td>
                      <td><strong><?= $month['month'] ?></strong></td>
                      <td>₱<?= number_format($month['total_amount'], 2) ?></td>
                      <td><?= number_format($month['total_quantity'], 2) ?></td>
                      <td><?= $month['total_sales'] ?></td>
                      <td><span style="color: #28a745;"><?= $performance ?></span></td>
                  </tr>
                  <?php endforeach; ?>
                  <?php endif; ?>
              </tbody>
          </table>
      </div>
      
      <!-- Quick Stats -->
      <div class="quick-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;">
          <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px;">
              <div class="stat-icon">💰</div>
              <div class="stat-label" style="color: rgba(255,255,255,0.9);">Total Lifetime Revenue</div>
              <div class="stat-value" style="font-size: 28px; margin: 10px 0;">₱<?= number_format($grandTotalRevenue, 2) ?></div>
          </div>
          
          <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 10px;">
              <div class="stat-icon">📦</div>
              <div class="stat-label" style="color: rgba(255,255,255,0.9);">Total Products Sold</div>
              <div class="stat-value" style="font-size: 28px; margin: 10px 0;"><?= number_format($grandTotalQuantity, 2) ?></div>
          </div>
          
          <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 10px;">
              <div class="stat-icon">🛒</div>
              <div class="stat-label" style="color: rgba(255,255,255,0.9);">Total Transactions</div>
              <div class="stat-value" style="font-size: 28px; margin: 10px 0;"><?= number_format($grandTotalSales, 0) ?></div>
          </div>
          
          <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 20px; border-radius: 10px;">
              <div class="stat-icon">📊</div>
              <div class="stat-label" style="color: rgba(255,255,255,0.9);">Average Transaction Value</div>
              <div class="stat-value" style="font-size: 28px; margin: 10px 0;">₱<?= $grandTotalSales > 0 ? number_format($grandTotalRevenue / $grandTotalSales, 2) : '0.00' ?></div>
          </div>
      </div>
      
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
      // Get sorting parameters for products tab
      $prod_sort_by = isset($_GET['prod_sort']) ? $_GET['prod_sort'] : 'name';
      $prod_sort_order = isset($_GET['prod_order']) ? $_GET['prod_order'] : 'asc';
      
      $allowed_prod_sorts = ['id', 'name', 'category', 'price', 'stock', 'availability', 'created_at'];
      $prod_sort_by = in_array($prod_sort_by, $allowed_prod_sorts) ? $prod_sort_by : 'name';
      $prod_sort_order = in_array(strtolower($prod_sort_order), ['asc', 'desc']) ? strtolower($prod_sort_order) : 'asc';
      
      $allProducts = $pdo->query("SELECT id, name, category, price, stock, availability, created_at FROM products ORDER BY $prod_sort_by $prod_sort_order")->fetchAll();
      ?>
      
      <!-- Sort Options for Products -->
      <div class="sort-options" style="margin: 15px 0;">
          <label>Sort by:</label>
          <select name="prod_sort" onchange="window.location.href='?tab=products&prod_sort='+this.value+'&prod_order=<?= $prod_sort_order ?>'">
              <option value="id" <?= $prod_sort_by == 'id' ? 'selected' : '' ?>>ID</option>
              <option value="name" <?= $prod_sort_by == 'name' ? 'selected' : '' ?>>Name</option>
              <option value="category" <?= $prod_sort_by == 'category' ? 'selected' : '' ?>>Category</option>
              <option value="price" <?= $prod_sort_by == 'price' ? 'selected' : '' ?>>Price</option>
              <option value="stock" <?= $prod_sort_by == 'stock' ? 'selected' : '' ?>>Stock</option>
              <option value="availability" <?= $prod_sort_by == 'availability' ? 'selected' : '' ?>>Status</option>
              <option value="created_at" <?= $prod_sort_by == 'created_at' ? 'selected' : '' ?>>Created Date</option>
          </select>
          
          <select name="prod_order" onchange="window.location.href='?tab=products&prod_sort=<?= $prod_sort_by ?>&prod_order='+this.value">
              <option value="asc" <?= $prod_sort_order == 'asc' ? 'selected' : '' ?>>Ascending</option>
              <option value="desc" <?= $prod_sort_order == 'desc' ? 'selected' : '' ?>>Descending</option>
          </select>
      </div>
      
      <h3>All Products (<?= count($allProducts) ?>)</h3>
      <table>
          <thead>
              <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Category</th>
                  <th>Price</th>
                  <th>Stock</th>
                  <th>Status</th>
                  <th>Created</th>
                  <th>Actions</th>
              </tr>
          </thead>
          <tbody>
              <?php foreach($allProducts as $product): 
                  $status = $product['availability'] ? 'Available' : 'Disabled';
                  $status_class = $product['availability'] ? 'status-available' : 'status-outofstock';
              ?>
              <tr>
                  <td><?= $product['id'] ?></td>
                  <td><?= htmlspecialchars($product['name']) ?></td>
                  <td><?= htmlspecialchars($product['category']) ?></td>
                  <td>₱<?= number_format($product['price'], 2) ?></td>
                  <td><?= number_format($product['stock'], 2) ?></td>
                  <td><span class="status-badge <?= $status_class ?>"><?= $status ?></span></td>
                  <td><?= date('Y-m-d', strtotime($product['created_at'])) ?></td>
                  <td>
                      <?php if($product['availability']): ?>
                      <form method="POST" style="display:inline;">
                          <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                          <input type="hidden" name="action" value="disable">
                          <button type="submit" name="toggle_product_status" class="disable-btn" onclick="return confirm('Disable this product? It will be hidden from sales but remain in database.')">Disable</button>
                      </form>
                      <?php else: ?>
                      <form method="POST" style="display:inline;">
                          <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                          <input type="hidden" name="action" value="enable">
                          <button type="submit" name="toggle_product_status" class="enable-btn" onclick="return confirm('Enable this product? It will be available for sales again.')">Enable</button>
                      </form>
                      <?php endif; ?>
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

.tab-content {
    display: none;
    padding: 20px;
    background: white;
    border-radius: 8px;
    margin-top: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.tab-content.active {
    display: block;
}

.crud-form {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
}

.form-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.form-row select,
.form-row input {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    min-width: 150px;
    flex: 1;
}

.add-btn, .delete-btn, .search-btn, .action-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
    font-size: 14px;
}

.add-btn {
    background: #28a745;
    color: white;
}

.add-btn:hover {
    background: #218838;
}

.delete-btn {
    background: #dc3545;
    color: white;
}

.delete-btn:hover {
    background: #c82333;
}

.search-btn {
    background: #007bff;
    color: white;
}

.search-btn:hover {
    background: #0056b3;
}

.action-btn {
    padding: 8px 12px;
    font-size: 12px;
    margin: 2px;
}

.edit-btn {
    background: #ffc107;
    color: #212529;
}

.edit-btn:hover {
    background: #e0a800;
}

.disable-btn {
    background: #6c757d;
    color: white;
}

.disable-btn:hover {
    background: #545b62;
}

.enable-btn {
    background: #17a2b8;
    color: white;
}

.enable-btn:hover {
    background: #138496;
}

.table-container {
    overflow-x: auto;
    margin: 20px 0;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 600px;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
}

th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
}

tbody tr:hover {
    background-color: #f8f9fa;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: white;
    padding: 30px;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 5px 30px rgba(0,0,0,0.3);
    position: relative;
}

.close-modal {
    position: absolute;
    right: 20px;
    top: 15px;
    font-size: 28px;
    cursor: pointer;
    color: #666;
}

.close-modal:hover {
    color: #333;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 16px;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 25px;
}

.cancel-btn, .save-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
}

.cancel-btn {
    background: #6c757d;
    color: white;
}

.save-btn {
    background: #28a745;
    color: white;
}

.search-sort-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 20px;
}

.search-box {
    display: flex;
    gap: 10px;
    align-items: center;
    flex: 1;
}

.search-box input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.sort-options {
    display: flex;
    gap: 10px;
    align-items: center;
}

.sort-options label {
    font-weight: 600;
    color: #495057;
}

.sort-options select {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: white;
}

.pagination {
    display: flex;
    gap: 5px;
    justify-content: center;
    margin: 20px 0;
    flex-wrap: wrap;
}

.page-btn {
    padding: 8px 12px;
    border: 1px solid #ddd;
    background: white;
    color: #333;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.3s;
}

.page-btn:hover {
    background: #f8f9fa;
    border-color: #F5A200;
}

.page-btn.current {
    background: #F5A200;
    color: white;
    border-color: #F5A200;
}

.pagination-info {
    text-align: center;
    color: #6c757d;
    font-size: 14px;
    margin-top: 10px;
}

.status-badge, .type-badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.status-available {
    background: #d4edda;
    color: #155724;
}

.status-outofstock {
    background: #f8d7da;
    color: #721c24;
}

.type-add {
    background: #d4edda;
    color: #155724;
}

.type-subtract {
    background: #f8d7da;
    color: #721c24;
}

.stat-card {
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.stat-label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.stat-value {
    font-size: 20px;
    font-weight: 700;
    color: #212529;
}

.filter-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.filter-btn {
    padding: 8px 15px;
    background: #e9ecef;
    color: #495057;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.3s;
}

.filter-btn:hover, .filter-btn.active {
    background: #F5A200;
    color: white;
}

.clear-search {
    color: #dc3545;
    text-decoration: none;
    font-size: 14px;
    padding: 5px 10px;
}

.clear-search:hover {
    text-decoration: underline;
}

/* Export Buttons */
.export-btn {
    background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: transform 0.3s, box-shadow 0.3s;
}

.export-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(106, 17, 203, 0.3);
}

/* Stat Cards in Monthly Reports */
.stat-icon {
    font-size: 32px;
    margin-bottom: 10px;
}

/* Performance indicators */
.performance-excellent { color: #28a745; }
.performance-good { color: #ffc107; }
.performance-average { color: #fd7e14; }
.performance-poor { color: #dc3545; }

/* Tab navigation adjustments for more tabs */
.tab-navigation {
    padding-top: 20px;
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
    overflow-x: auto;
    padding-bottom: 5px;
    z-index: 20;
}

.tab-btn {
    padding: 10px 15px;
    border: none;
    background: #fff3cd;
    cursor: pointer;
    border-radius: 12px;
    font-weight: 600;
    transition: 0.2s ease;
    white-space: nowrap;
    font-size: 14px;
    color: black;
}

.tab-btn.active { 
    background: #F5A200; 
    color: black; 
    box-shadow: 0 4px 8px rgba(245, 162, 0, 0.2);
}

/* Custom month/year selectors */
.custom-filter {
    display: inline-block;
    vertical-align: middle;
}

.custom-filter input[type="month"],
.custom-filter select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: white;
    font-size: 14px;
}

.custom-filter input[type="month"]:focus,
.custom-filter select:focus {
    outline: none;
    border-color: #F5A200;
    box-shadow: 0 0 0 3px rgba(245, 162, 0, 0.1);
}

/* Sales stats cards */
.sales-stats .stat-card {
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border-left: 4px solid #28a745;
}

.sales-stats .stat-card:nth-child(2) {
    border-left-color: #17a2b8;
}

.sales-stats .stat-card:nth-child(3) {
    border-left-color: #ffc107;
}

.sales-stats .stat-card:nth-child(4) {
    border-left-color: #6f42c1;
}

/* Rank column styling */
td:first-child {
    font-weight: bold;
    color: #F5A200;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .tab-navigation {
        padding-top: 20px;
        gap: 5px;
    }
    
    .tab-btn {
        padding: 8px 12px;
        font-size: 12px;
        color: black;
    }
    
    .filter-buttons {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .custom-filter {
        margin: 10px 0 0 0;
        width: 100%;
    }
    
    .custom-filter input[type="month"],
    .custom-filter select {
        width: 100%;
    }
    
    .quick-stats {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .form-row select,
    .form-row input {
        width: 100%;
        min-width: auto;
    }
    
    .search-sort-container {
        flex-direction: column;
        align-items: stretch;
    }
    
    .sort-options {
        justify-content: flex-start;
    }
}

@media (max-width: 480px) {
    .dashboard-hero h1 {
        font-size: 24px;
    }
    
    .dashboard-hero p {
        font-size: 14px;
    }
    
    .export-btn {
        width: 100%;
        margin-bottom: 10px;
    }
    
    table {
        font-size: 12px;
    }
    
    th, td {
        padding: 8px;
    }
}
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
    document.getElementById('editForm').action = `?tab=inventory_dashboard&page=<?= $current_page ?>&search=<?= urlencode($search_term) ?>&sort=<?= $sort_by ?>&order=<?= $sort_order ?>`;
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function openAddModal() {
    window.location.href = '?tab=products';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        closeEditModal();
    }
}

// Export functions (NOT FUNCTIONAL, DO NOT ALTER)
function exportToCSV(type) {
    alert('Export feature would generate a CSV file for ' + type + ' data.');
}

// Initialize active tab based on URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab') || 'sales';
    const tabBtn = document.querySelector(`.tab-btn[data-target="${tab}"]`);
    const tabContent = document.getElementById(tab);
    
    if (tabBtn && tabContent) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        tabBtn.classList.add('active');
        tabContent.classList.add('active');
    }
});
</script>