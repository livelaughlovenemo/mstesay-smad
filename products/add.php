<?php
require_once "../includes/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Basic validation
    $errors = [];

    if (empty($_POST['name'])) $errors[] = "Product name is required.";
    if (empty($_POST['category'])) $errors[] = "Category is required.";
    if (empty($_POST['unit'])) $errors[] = "Unit is required.";
    if (empty($_POST['price'])) {
        $errors[] = "Price is required.";
    } elseif (!is_numeric($_POST['price'])) {
        $errors[] = "Price must be a number.";
    }

    // If there are errors, redirect back with error message
    if (!empty($errors)) {
        $errorStr = urlencode(implode(', ', $errors));
        header("Location: ../dashboard.php#products?error=$errorStr");
        exit;
    }

    // Trim inputs
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $unit = trim($_POST['unit']);
    $price = $_POST['price'];

    // Insert into database
    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, category, unit, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $category, $unit, $price]);
        header("Location: ../dashboard.php#products?success=Product added successfully");
        exit;
    } catch (PDOException $e) {
        $errorStr = urlencode("Database error: " . $e->getMessage());
        header("Location: ../dashboard.php#products?error=$errorStr");
        exit;
    }
}
