<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: ../login.php");
require_once "../includes/db.php";

$id = $_GET['id'] ?? null;
if ($id) {
  $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
  $stmt->execute([$id]);
}
header("Location: list.php");
exit;
