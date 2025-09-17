<?php
require_once "../includes/db.php";

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $stmt = $pdo->prepare("INSERT INTO products (name, category, unit, price) VALUES (?,?,?,?)");
  $stmt->execute([$_POST['name'], $_POST['category'], $_POST['unit'], $_POST['price']]);
}

header("Location: ../dashboard.php#products");
exit;
