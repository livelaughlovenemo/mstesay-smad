<?php
require_once __DIR__ . '/includes/db.php';

// Generate a fresh bcrypt hash for admin123
$hash = password_hash('admin123', PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (:u, :p)
                       ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)");
$stmt->execute([
    'u' => 'admin',
    'p' => $hash
]);

echo "✅ Admin reset complete.<br>";
echo "Username: admin<br>";
echo "Password: admin123<br>";
