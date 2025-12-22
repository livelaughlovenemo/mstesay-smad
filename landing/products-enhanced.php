
<?php
require_once "includes/db.php";
require_once "includes/cache.php";

// Initialize cache if the class is available
$cache = null;
if (isset($pdo) && class_exists('CacheManager')) {
    try {
        $cache = new CacheManager($pdo);
    } catch (Exception $e) {
        error_log("Cache initialization error: " . $e->getMessage());
        $cache = null;
    }
}
$category = $_GET['category'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 12;

// Try to get cached products
$cacheKey = "products_{$category}_{$search}_{$page}";
$cachedProducts = null;
if ($cache) {
    $cachedProducts = $cache->getCachedReport('products', [
        'category' => $category,
        'search' => $search,
        'page' => $page
    ]);
}

if ($cache && $cachedProducts) {
    $products = $cachedProducts['data'];
    $totalPages = $cachedProducts['total_pages'];
} else {
    // Build query
    $where = [];
    $params = [];
    
    if ($category !== 'all') {
        $where[] = "category = ?";
        $params[] = $category;
    }
    
    if (!empty($search)) {
        $where[] = "(name LIKE ? OR description LIKE ? OR category LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);
    
    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM products {$whereClause}");
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];
    $totalPages = ceil($total / $limit);
    
    // Get products with pagination
    $offset = ($page - 1) * $limit;
    $productStmt = $pdo->prepare("
        SELECT * FROM products 
        {$whereClause}
        ORDER BY name ASC
        LIMIT ? OFFSET ?
    ");
    
    $params[] = $limit;
    $params[] = $offset;
    $productStmt->execute($params);
    $products = $productStmt->fetchAll();
    
    // Cache the results
    if ($cache) {
        $cache->cacheReport('products', [
            'data' => $products,
            'total_pages' => $totalPages
        ], [
            'category' => $category,
            'search' => $search,
            'page' => $page
        ]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Ms. Tesay Chicken</title>
    <link rel="stylesheet" href="../assets/css/products-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->

    <section class="product-section">
        <div class="product-filters">
            <div class="search-box">
                <input type="text" id="productSearch" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
                <button onclick="searchProducts()"><i class="fas fa-search"></i></button>
            </div>
            
            <div class="category-filter">
                <button class="category-btn <?= $category === 'all' ? 'active' : '' ?>" onclick="filterCategory('all')">All Products</button>
                <button class="category-btn <?= $category === 'chicken' ? 'active' : '' ?>" onclick="filterCategory('chicken')">Chicken</button>
                <button class="category-btn <?= $category === 'frozen' ? 'active' : '' ?>" onclick="filterCategory('frozen')">Frozen</button>
            </div>
            
            <div class="sort-options">
                <select id="sortBy" onchange="sortProducts()">
                    <option value="name">Name A-Z</option>
                    <option value="name_desc">Name Z-A</option>
                    <option value="price_low">Price Low-High</option>
                    <option value="price_high">Price High-Low</option>
                </select>
            </div>
        </div>
        
        <div class="product-grid" id="productGrid">
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open fa-3x"></i>
                    <h3>No products found</h3>
                    <p>Try adjusting your search or filter criteria.</p>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card" data-product-id="<?= $product['id'] ?>">
                        <div class="product-image">
                            <?php if ($product['image_url']): ?>
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="image-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($product['stock'] <= $product['minimum_stock']): ?>
                                <span class="stock-badge low-stock">Low Stock</span>
                            <?php elseif ($product['stock'] == 0): ?>
                                <span class="stock-badge out-of-stock">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-info">
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="product-category"><?= htmlspecialchars($product['category']) ?></p>
                            
                            <?php if ($product['description']): ?>
                                <p class="product-description"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                            <?php endif; ?>
                            
                            <div class="product-meta">
                                <span class="stock-info">
                                    <i class="fas fa-box"></i> Stock: <?= number_format($product['stock'], 2) ?>
                                </span>
                                <span class="price">₱<?= number_format($product['price'], 2) ?></span>
                            </div>
                            
                            <div class="product-actions">
                                <button class="btn-view" onclick="viewProduct(<?= $product['id'] ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn-quick-order" onclick="quickOrder(<?= $product['id'] ?>)">
                                    <i class="fas fa-shopping-cart"></i> Order
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?category=<?= $category ?>&search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>" class="page-link">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="page-link active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?category=<?= $category ?>&search=<?= urlencode($search) ?>&page=<?= $i ?>" class="page-link"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?category=<?= $category ?>&search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>" class="page-link">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
    
    <!-- Product Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <div id="modalContent"></div>
        </div>
    </div>
    
    <script>
        let currentPage = <?= $page ?>;
        let currentCategory = '<?= $category ?>';
        let currentSearch = '<?= $search ?>';
        let currentSort = 'name';
        
        function filterCategory(category) {
            currentCategory = category;
            loadProducts();
        }
        
        function searchProducts() {
            currentSearch = document.getElementById('productSearch').value;
            currentPage = 1;
            loadProducts();
        }
        
        function sortProducts() {
            currentSort = document.getElementById('sortBy').value;
            loadProducts();
        }
        
        function loadProducts() {
            const url = new URL(window.location.href);
            url.searchParams.set('category', currentCategory);
            url.searchParams.set('search', currentSearch);
            url.searchParams.set('page', currentPage);
            url.searchParams.set('sort', currentSort);
            
            // Show loading state
            document.getElementById('productGrid').innerHTML = `
                <div class="loading-state">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Loading products...</p>
                </div>
            `;
            
            // AJAX request
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                // Parse HTML and extract product grid
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const productGrid = doc.getElementById('productGrid');
                const pagination = doc.querySelector('.pagination');
                
                // Update content
                document.getElementById('productGrid').innerHTML = productGrid.innerHTML;
                document.querySelector('.pagination').innerHTML = pagination ? pagination.innerHTML : '';
                
                // Update URL without reload
                window.history.pushState({}, '', url);
            })
            .catch(error => {
                console.error('Error loading products:', error);
                document.getElementById('productGrid').innerHTML = `
                    <div class="error-state">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                        <p>Failed to load products. Please try again.</p>
                    </div>
                `;
            });
        }
        
        function viewProduct(productId) {
            fetch(`ajax/get_product.php?id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const modalContent = `
                            <div class="product-modal-view">
                                <div class="product-modal-image">
                                    ${data.image_url ? 
                                        `<img src="${data.image_url}" alt="${data.name}">` : 
                                        `<div class="image-placeholder-large"><i class="fas fa-image"></i></div>`
                                    }
                                </div>
                                <div class="product-modal-info">
                                    <h2>${data.name}</h2>
                                    <p class="product-category">${data.category}</p>
                                    <p class="product-description">${data.description || 'No description available.'}</p>
                                    
                                    <div class="product-specs">
                                        <div class="spec">
                                            <span class="spec-label">Price:</span>
                                            <span class="spec-value">₱${parseFloat(data.price).toFixed(2)}</span>
                                        </div>
                                        <div class="spec">
                                            <span class="spec-label">Stock:</span>
                                            <span class="spec-value ${data.stock <= data.minimum_stock ? 'text-warning' : ''}">
                                                ${parseFloat(data.stock).toFixed(2)} ${data.unit}
                                            </span>
                                        </div>
                                        <div class="spec">
                                            <span class="spec-label">SKU:</span>
                                            <span class="spec-value">${data.sku || 'N/A'}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="modal-actions">
                                        <button class="btn-primary" onclick="quickOrder(${data.id})">
                                            <i class="fas fa-shopping-cart"></i> Add to Order
                                        </button>
                                        <button class="btn-secondary" onclick="closeModal()">
                                            Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        document.getElementById('modalContent').innerHTML = modalContent;
                        document.getElementById('productModal').style.display = 'flex';
                    }
                })
                .catch(error => {
                    console.error('Error loading product:', error);
                    alert('Failed to load product details.');
                });
        }
        
        function quickOrder(productId) {
            // Implementation for quick order
            alert('Quick order functionality would be implemented here.');
        }
        
        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
        }
        
        // Event listeners
        document.getElementById('productSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchProducts();
            }
        });
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Set active category buttons
            document.querySelectorAll('.category-btn').forEach(btn => {
                if (btn.textContent.toLowerCase().includes(currentCategory)) {
                    btn.classList.add('active');
                }
            });
            
            // Set sort select value
            document.getElementById('sortBy').value = currentSort;
        });
    </script>
    
    <style>
        .product-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .search-box {
            flex: 1;
            display: flex;
            gap: 10px;
        }
        
        .search-box input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        
        .search-box button {
            padding: 10px 20px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        
        .category-filter {
            display: flex;
            gap: 10px;
        }
        
        .category-btn {
            padding: 8px 16px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
        }
        
        .category-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .low-stock {
            background: #fff3cd;
            color: #856404;
        }
        
        .out-of-stock {
            background: #f8d7da;
            color: #721c24;
        }
        
        .product-image {
            position: relative;
            height: 200px;
            overflow: hidden;
            border-radius: 8px;
            background: #f8f9fa;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .image-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #6c757d;
            font-size: 48px;
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px 0;
        }
        
        .stock-info {
            font-size: 12px;
            color: #6c757d;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .product-actions button {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .btn-view {
            background: var(--info-color);
            color: white;
        }
        
        .btn-quick-order {
            background: var(--success-color);
            color: white;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 10px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 20px;
            position: relative;
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
        }
        
        .product-modal-view {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }
        
        .product-modal-image {
            border-radius: 8px;
            overflow: hidden;
            background: #f8f9fa;
        }
        
        .product-modal-image img {
            width: 100%;
            height: auto;
        }
        
        .image-placeholder-large {
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 72px;
        }
        
        .product-specs {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .spec {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .spec-label {
            font-weight: 600;
            color: #495057;
        }
        
        .spec-value {
            color: var(--dark-color);
        }
        
        .text-warning {
            color: var(--warning-color);
        }
        
        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-primary {
            padding: 10px 20px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        
        .btn-secondary {
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .product-filters {
                flex-direction: column;
                align-items: stretch;
            }
            
            .product-modal-view {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>