<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SMAD Dashboard</title>
  <link rel="stylesheet" href="assets/styles.css">
  <link rel="icon" href="assets/main-logo.svg">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header>
  <div class="head"><h3>SMAD</h3></div>
  <div class="header-right">
    <img src="assets/user.svg" alt="User" width="28">
    <span><?= htmlspecialchars($_SESSION["username"] ?? "Guest") ?></span>
    <a href="logout.php" style="color:white;">Logout</a>
  </div>
</header>

