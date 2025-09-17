<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// show nav for logged in users
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>SMAD - Sales Monitoring</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { padding-top: 70px; }
    .card-summary { min-height: 90px; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">SMAD</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if (is_logged_in()): ?>
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="daily_sales.php">Daily Sales</a></li>
        <li class="nav-item"><a class="nav-link" href="top_products.php">Top Products</a></li>
        <li class="nav-item"><a class="nav-link" href="financial_reports.php">Financial Reports</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="overallDropdown" role="button" data-bs-toggle="dropdown">Overall</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="dashboard.php">Overview</a></li>
            <li><a class="dropdown-item" href="daily_sales.php">All Daily Sales</a></li>
            <li><a class="dropdown-item" href="top_products.php">All Products</a></li>
            <li><a class="dropdown-item" href="financial_reports.php">All Reports</a></li>
          </ul>
        </li>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav mb-2 mb-lg-0">
        <?php if (is_logged_in()): ?>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
        <?php endif; ?>
      </ul>

    </div>
  </div>
</nav>

<div class="container">
