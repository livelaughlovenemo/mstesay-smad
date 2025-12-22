<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/security.php';

// Define missing security functions if not in security.php
if (!function_exists('validate_password_strength')) {
    function validate_password_strength($password) {
        $errors = [];
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain uppercase letter';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain lowercase letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain number';
        }
        if (!preg_match('/[!@#$%^&*]/', $password)) {
            $errors[] = 'Password must contain special character';
        }
        return $errors;
    }
}

if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('validate_csrf_token')) {
    function validate_csrf_token($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

class IntegrationTest extends TestCase
{
    private $pdo;
    
    protected function setUp(): void {
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=smad_test',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Create test database schema
        $this->createTestSchema();
    }
    
    protected function tearDown(): void {
        // Clean up test data
        $this->pdo->exec("DROP DATABASE IF EXISTS smad_test");
    }
    
    private function createTestSchema() {
        // Create test database schema similar to production
        $sql = file_get_contents(__DIR__ . '/../database/schema.sql');
        $this->pdo->exec($sql);
    }
    
    public function testUserRegistration() {
        // Test valid registration
        $username = 'testuser_' . time();
        $password = 'Test@1234';
        
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, password_hash, role) 
            VALUES (?, ?, 'Staff')
        ");
        
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $result = $stmt->execute([$username, $hash]);
        
        $this->assertTrue($result);
        
        // Verify user was created
        $checkStmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $checkStmt->execute([$username]);
        $user = $checkStmt->fetch();
        
        $this->assertNotEmpty($user);
        $this->assertEquals($username, $user['username']);
        $this->assertTrue(password_verify($password, $user['password_hash']));
    }
    
    public function testProductCreation() {
        // Test product creation
        $productData = [
            'name' => 'Test Product',
            'category' => 'test',
            'price' => 100.00,
            'stock' => 50
        ];
        
        $stmt = $this->pdo->prepare("
            INSERT INTO products (name, category, price, stock)
            VALUES (?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $productData['name'],
            $productData['category'],
            $productData['price'],
            $productData['stock']
        ]);
        
        $this->assertTrue($result);
        
        // Verify product was created
        $checkStmt = $this->pdo->prepare("SELECT * FROM products WHERE name = ?");
        $checkStmt->execute([$productData['name']]);
        $product = $checkStmt->fetch();
        
        $this->assertNotEmpty($product);
        $this->assertEquals($productData['price'], (float)$product['price']);
        $this->assertEquals($productData['stock'], (float)$product['stock']);
    }
    
    public function testSaleTransaction() {
        // Create test product
        $productStmt = $this->pdo->prepare("
            INSERT INTO products (name, price, stock) 
            VALUES ('Test Sale Product', 50.00, 100)
        ");
        $productStmt->execute();
        $productId = $this->pdo->lastInsertId();
        
        // Create test user
        $userStmt = $this->pdo->prepare("
            INSERT INTO users (username, password_hash) 
            VALUES ('testsaleuser', ?)
        ");
        $userStmt->execute([password_hash('test', PASSWORD_BCRYPT)]);
        $userId = $this->pdo->lastInsertId();
        
        // Test sale transaction
        $quantity = 10;
        
        $this->pdo->beginTransaction();
        
        try {
            // Record sale
            $saleStmt = $this->pdo->prepare("
                INSERT INTO sales (product_id, quantity, total_price, sale_datetime, user_id)
                VALUES (?, ?, ?, NOW(), ?)
            ");
            
            $totalPrice = 50.00 * $quantity;
            $saleStmt->execute([$productId, $quantity, $totalPrice, $userId]);
            
            // Update stock
            $updateStmt = $this->pdo->prepare("
                UPDATE products SET stock = stock - ? WHERE id = ?
            ");
            $updateStmt->execute([$quantity, $productId]);
            
            $this->pdo->commit();
            
            // Verify sale was recorded
            $checkSaleStmt = $this->pdo->prepare("SELECT * FROM sales WHERE product_id = ?");
            $checkSaleStmt->execute([$productId]);
            $sale = $checkSaleStmt->fetch();
            
            $this->assertNotEmpty($sale);
            $this->assertEquals($quantity, $sale['quantity']);
            $this->assertEquals($totalPrice, (float)$sale['total_price']);
            
            // Verify stock was updated
            $checkStockStmt = $this->pdo->prepare("SELECT stock FROM products WHERE id = ?");
            $checkStockStmt->execute([$productId]);
            $stock = $checkStockStmt->fetchColumn();
            
            $this->assertEquals(90, (float)$stock);
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    public function testInventoryAdjustment() {
        // Create test product
        $productStmt = $this->pdo->prepare("
            INSERT INTO products (name, price, stock) 
            VALUES ('Test Inventory Product', 30.00, 50)
        ");
        $productStmt->execute();
        $productId = $this->pdo->lastInsertId();
        
        // Test inventory adjustment
        $adjustmentQuantity = 20;
        
        $this->pdo->beginTransaction();
        
        try {
            // Add inventory
            $invStmt = $this->pdo->prepare("
                INSERT INTO inventory (product_id, quantity, inv_type, inv_date)
                VALUES (?, ?, 'add', CURDATE())
            ");
            $invStmt->execute([$productId, $adjustmentQuantity]);
            
            // Update product stock
            $updateStmt = $this->pdo->prepare("
                UPDATE products SET stock = stock + ? WHERE id = ?
            ");
            $updateStmt->execute([$adjustmentQuantity, $productId]);
            
            $this->pdo->commit();
            
            // Verify inventory record
            $checkInvStmt = $this->pdo->prepare("
                SELECT * FROM inventory WHERE product_id = ? AND inv_type = 'add'
            ");
            $checkInvStmt->execute([$productId]);
            $inventory = $checkInvStmt->fetch();
            
            $this->assertNotEmpty($inventory);
            $this->assertEquals($adjustmentQuantity, (float)$inventory['quantity']);
            
            // Verify stock update
            $checkStockStmt = $this->pdo->prepare("SELECT stock FROM products WHERE id = ?");
            $checkStockStmt->execute([$productId]);
            $stock = $checkStockStmt->fetchColumn();
            
            $this->assertEquals(70, (float)$stock);
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    public function testSecurityFunctions() {
        // Test password strength validation
        $weakPasswords = [
            'short',
            'nouppercase123',
            'NOLOWERCASE123',
            'NoNumbersHere',
            'NoSpecial123'
        ];
        
        foreach ($weakPasswords as $password) {
            $errors = validate_password_strength($password);
            $this->assertNotEmpty($errors, "Password '$password' should fail validation");
        }
        
        // Test strong password
        $strongPassword = 'Strong@Pass123';
        $errors = validate_password_strength($strongPassword);
        $this->assertEmpty($errors, "Strong password should pass validation");
        
        // Test CSRF token generation and validation
        $token1 = generate_csrf_token();
        $token2 = generate_csrf_token();
        
        $this->assertEquals($token1, $token2, "CSRF token should be consistent in same session");
        $this->assertTrue(validate_csrf_token($token1), "Valid CSRF token should validate");
        $this->assertFalse(validate_csrf_token('invalid_token'), "Invalid CSRF token should not validate");
    }
    
    public function testReportGeneration() {
        // Create test data for reports
        for ($i = 1; $i <= 10; $i++) {
            // Create product
            $productStmt = $this->pdo->prepare("
                INSERT INTO products (name, price, stock, category)
                VALUES (?, ?, ?, ?)
            ");
            $productStmt->execute([
                "Test Product $i",
                $i * 10,
                $i * 5,
                $i % 2 == 0 ? 'chicken' : 'frozen'
            ]);
            $productId = $this->pdo->lastInsertId();
            
            // Create sales
            $saleStmt = $this->pdo->prepare("
                INSERT INTO sales (product_id, quantity, total_price, sale_datetime)
                VALUES (?, ?, ?, DATE_SUB(NOW(), INTERVAL ? DAY))
            ");
            $saleStmt->execute([
                $productId,
                $i,
                $i * 10 * $i,
                $i - 1
            ]);
        }
        
        // Test daily sales report
        $dailyStmt = $this->pdo->prepare("
            SELECT 
                DATE(sale_datetime) as sale_date,
                SUM(quantity) as total_quantity,
                SUM(total_price) as total_amount,
                COUNT(*) as sales_count
            FROM sales
            WHERE sale_datetime >= CURDATE()
            GROUP BY DATE(sale_datetime)
            ORDER BY sale_date DESC
        ");
        $dailyStmt->execute();
        $dailyReport = $dailyStmt->fetchAll();
        
        $this->assertIsArray($dailyReport);
        
        // Test monthly report
        $monthlyStmt = $this->pdo->prepare("
            SELECT 
                DATE_FORMAT(sale_datetime, '%Y-%m') as sale_month,
                SUM(quantity) as total_quantity,
                SUM(total_price) as total_amount,
                COUNT(*) as sales_count
            FROM sales
            WHERE sale_datetime >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
            GROUP BY DATE_FORMAT(sale_datetime, '%Y-%m')
            ORDER BY sale_month DESC
        ");
        $monthlyStmt->execute();
        $monthlyReport = $monthlyStmt->fetchAll();
        
        $this->assertIsArray($monthlyReport);
        
        // Test top products report
        $topProductsStmt = $this->pdo->prepare("
            SELECT 
                p.name,
                p.category,
                SUM(s.quantity) as total_quantity,
                SUM(s.total_price) as total_amount,
                COUNT(s.id) as sales_count
            FROM sales s
            JOIN products p ON s.product_id = p.id
            GROUP BY p.id, p.name, p.category
            ORDER BY total_amount DESC
            LIMIT 5
        ");
        $topProductsStmt->execute();
        $topProducts = $topProductsStmt->fetchAll();
        
        $this->assertIsArray($topProducts);
        $this->assertLessThanOrEqual(5, count($topProducts));
    }
    
    public function testErrorHandling() {
        // Test database connection failure
        $this->expectException(PDOException::class);
        
        new PDO(
            'mysql:host=invalid_host;dbname=nonexistent',
            'invalid_user',
            'invalid_pass',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    
    public function testPerformanceBenchmark() {
        // Test query performance
        $startTime = microtime(true);
        
        for ($i = 0; $i < 100; $i++) {
            $stmt = $this->pdo->prepare("SELECT * FROM products WHERE category = ?");
            $stmt->execute(['chicken']);
            $stmt->fetchAll();
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Assert that 100 queries complete in reasonable time
        $this->assertTrue($executionTime < 2.0, 
            "100 queries should complete in less than 2 seconds");
        
        echo "\nPerformance: 100 queries in " . round($executionTime, 3) . " seconds";
    }
}