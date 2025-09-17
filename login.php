<?php
// login.php
session_start();
require_once __DIR__ . '/includes/db.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $err = 'Please fill both fields.';
    } else {
        $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = :u LIMIT 1");
        $stmt->execute(['u' => $username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            // login success
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            header('Location: dashboard.php');
            exit;
        } else {
            $err = 'Invalid username or password.';
        }
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow-sm mt-5">
      <div class="card-body">
        <h4 class="card-title mb-3">Login</h4>
        <?php if ($err): ?>
          <div class="alert alert-danger"><?=htmlspecialchars($err)?></div>
        <?php endif; ?>
        <form method="post" action="login.php">
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input name="username" class="form-control" required autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input name="password" type="password" class="form-control" required>
          </div>
          <div class="d-grid">
            <button class="btn btn-primary">Login</button>
          </div>
        </form>
        <div class="mt-3 text-muted small">Demo: username <b>admin</b> | password <b>admin123</b></div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
