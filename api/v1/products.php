<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

require_once '../../includes/db.php';

// API Key authentication
function authenticate_api() {
    $headers = getallheaders();
    
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'API key required']);
        exit;
    }
    
    $apiKey = str_replace('Bearer ', '', $headers['Authorization']);
    
    $stmt = $GLOBALS['pdo']->prepare("SELECT user_id FROM api_keys WHERE api_key = ? AND is_active = 1 AND expires_at > NOW()");
    $stmt->execute([hash('sha256', $apiKey)]);
    $keyData = $stmt->fetch();
    
    if (!$keyData) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid API key']);
        exit;
    }
    
    return $keyData['user_id'];
}

$method = $_SERVER['REQUEST_METHOD'];
$userId = authenticate_api();

switch ($method) {
    case 'GET':
        // Get products
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 20;
        $offset = ($page - 1) * $limit;
        
        $where = [];
        $params = [];
        
        if ($search) {
            $where[] = "(name LIKE ? OR description LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($category) {
            $where[] = "category = ?";
            $params[] = $category;
        }
        
        $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);
        
        // Get total count
        $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM products {$whereClause}");
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        // Get products
        $productStmt = $pdo->prepare("
            SELECT id, name, description, category, price, stock, unit, image_url, availability
            FROM products 
            {$whereClause}
            ORDER BY name ASC
            LIMIT ? OFFSET ?
        ");
        
        $params[] = $limit;
        $params[] = $offset;
        $productStmt->execute($params);
        $products = $productStmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $products,
            'pagination' => [
                'page' => (int)$page,
                'limit' => (int)$limit,
                'total' => (int)$total,
                'pages' => ceil($total / $limit)
            ]
        ]);
        break;
        
    case 'POST':
        // Create new product (admin only)
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $required = ['name', 'price'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Missing required field: {$field}"]);
                exit;
            }
        }
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO products 
                (name, description, category, price, stock, unit, image_url, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['name'],
                $data['description'] ?? '',
                $data['category'] ?? '',
                $data['price'],
                $data['stock'] ?? 0,
                $data['unit'] ?? 'pcs',
                $data['image_url'] ?? null
            ]);
            
            $productId = $pdo->lastInsertId();
            
            // Log the action
            log_audit($pdo, $userId, 'API_PRODUCT_CREATE', 'products', $productId, 
                     "Product created via API: {$data['name']}");
            
            echo json_encode([
                'success' => true,
                'message' => 'Product created successfully',
                'product_id' => $productId
            ]);
            
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create product: ' . $e->getMessage()]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>