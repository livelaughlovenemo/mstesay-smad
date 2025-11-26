<?php
session_start();
require_once "includes/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    try {
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = :u");
        $stmt->execute(["u" => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password_hash"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        } 
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ms. Tesay Chicken - Login</title>
  <link rel="stylesheet" href="login-styles.css"> <!-- your new CSS -->
  <link rel="icon" type="image/png" href="mainlogo.png">
</head>
<body>

<!-- NAVBAR -->
<header class="navbar">
  <div class="logo">
    <img src="mainlogo.png" alt="Logo">
    <span>Ms. Tesay Chicken</span>
  </div>
  <nav>
    <a href="landing.html">Home</a>
    <a href="#login">Login</a>
  </nav>
</header>

<!-- LOGIN SECTION -->
<section class="login-section" id="login">
  <div class="login-box">
    <h2>LOG IN</h2>
    <?php if (!empty($error)): ?>
        <p style="color:red; margin-bottom:15px;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
      <div class="input-group">
        <span class="icon">
          <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="#ff8a50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <input type="text" name="username" placeholder="Username" required>
      </div>

      <div class="input-group">
        <span class="icon">
          <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M7 10.0288C7.47142 10 8.05259 10 8.8 10H15.2C15.9474 10 16.5286 10 17 10.0288M7 10.0288V8C7 5.23858 9.23858 3 12 3C14.7614 3 17 5.23858 17 8V10.0288" stroke="#ff8a50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <input type="password" name="password" placeholder="Password" required>
      </div>

      <button type="submit" class="login-btn">LOGIN</button>
    </form>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <p>© 2025 Ms. Tesay Chicken Sales Monitoring System</p>
</footer>

</body>
</html>
