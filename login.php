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
  <title>Login</title>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<div class="container" style="display:flex; height:100vh; align-items:center; justify-content:center;">
  <div class="login-box" style="background:white; padding:30px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.2);">
    <h2>LOG IN</h2>
    <?php if (!empty($error)): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post">
      <div><input type="text" name="username" placeholder="Username" required></div>
      <div><input type="password" name="password" placeholder="Password" required></div>
      <button type="submit" class="view-btn">LOGIN</button>
    </form>
  </div>
</div>
</body>
</html>
