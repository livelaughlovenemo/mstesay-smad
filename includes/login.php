<?php
session_start();
require_once "db.php";

$loginError = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login_submit'])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = :u");
    $stmt->execute(["u" => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirect to dashboard after login
        header("Location: dashboard.php");
        exit;
    } else {
        $loginError = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Ms. Tesay Chicken</title>
<link rel="stylesheet" href="assets/css/landing-styles.css">
<link rel="icon" type="image/png" href="assets/img/mainlogo.png">
<style>
.login-container {
    min-height: 90vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #f4f4f4;
}
.login-box {
    background: #fff;
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    width: 100%;
    max-width: 400px;
}
.login-box h2 {
    margin-bottom: 20px;
    color: #F5A200;
    font-family: 'Urbanist', sans-serif;
}
.login-box p {
    margin-bottom: 20px;
}
.input-group {
    margin-bottom: 15px;
}
.input-group input {
    width: 100%;
    padding: 10px 14px;
    border-radius: 12px;
    border: 1px solid #ccc;
}
.login-btn {
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 12px;
    background: #F5A200;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
}
.login-error {
    color: red;
    margin-bottom: 10px;
}
</style>
</head>
<body>

<div class="login-container">
    <div class="login-box">
        <h2>Login</h2>
        <p>Enter your credentials to access the dashboard.</p>
        <?php if($loginError): ?>
            <div class="login-error"><?= htmlspecialchars($loginError) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" name="login_submit" class="login-btn">Login</button>
        </form>
    </div>
</div>

</body>
</html>
