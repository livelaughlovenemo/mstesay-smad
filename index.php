<?php
session_start();
require_once "includes/db.php";

$login_error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            $login_error = "Invalid username or password.";
        }
    } else {
        $login_error = "Please fill in both fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ms. Tesay Chicken</title>
  <link rel="stylesheet" href="landing-styles.css">
  <link rel="icon" type="image/png" href="mainlogo.png">
  <style>
    /* LOGIN MODAL STYLING */
    .modal {
      display: none; 
      position: fixed;
      z-index: 2000; 
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto; 
      background: rgba(0,0,0,0.6);
    }
    .modal-content {
      background-color: #fff;
      margin: 10% auto;
      padding: 40px;
      border-radius: 20px;
      width: 380px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.10);
      position: relative;
      text-align: center;
    }
    .close-modal {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 24px;
      cursor: pointer;
      color: #333;
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<header class="navbar">
  <div class="logo">
    <img src="mainlogo.png" alt="Logo">
    <h2 style="font-family: Pacifico, cursive;">Ms. Tesay Chicken</h2>
  </div>
  <nav>
    <a href="#about">About</a>
    <a href="#branches">Branches</a>
    <a href="#contact">Contact</a>
    <a href="#" id="loginBtn" class="login-btn">Login</a>
  </nav>
</header>

<!-- HERO SLIDER -->
<section class="hero-slider">
  <div class="slide active" style="background-image: url('signin.png')">
    <div class="overlay"></div>
    <div class="hero-text animate-text">
      <h1 style="font-family: Urbanist, sans-serif;">Sales Monitoring Dashboard</h1>
      <p>Keep products organized, track daily sales, and monitor branch performance with ease.</p>
      <a href="#" class="cta" id="heroLoginBtn">Proceed to Login →</a>
    </div>
  </div>
  <div class="slide" style="background-image: url('frozenfoods1.png')">
    <div class="overlay"></div>
    <div class="hero-text animate-text">
      <h1 style="font-family: Urbanist, sans-serif;">Real-Time Sales Insights</h1>
      <p>Accurate sales reporting across all product categories.</p>
      <a href="#" class="cta" id="heroLoginBtn2">Access Dashboard</a>
    </div>
  </div>
  <div class="slide" style="background-image: url('frozenfoods2.png')">
    <div class="overlay"></div>
    <div class="hero-text animate-text">
      <h1 style="font-family: Urbanist, sans-serif;">Branch Performance Tracking</h1>
      <p>Identify top-performing stores and optimize inventory levels.</p>
      <a href="#" class="cta" id="heroLoginBtn3">View Branch Data</a>
    </div>
  </div>
</section>

<!-- BRANCH FINDER -->
<section class="branch-finder" id="branches">
  <h2 class="fade-in">View Branch Analytics</h2>
  <div class="finder-box fade-up">
    <select>
      <option>Select Branch</option>
      <option>Branch A</option>
      <option>Branch B</option>
      <option>Branch C</option>
    </select>
    <button>View Dashboard</button>
  </div>
</section>

<!-- LOGIN MODAL -->
<div id="loginModal" class="modal">
  <div class="modal-content">
    <span class="close-modal">&times;</span>
    <h2 style="font-family: Urbanist, sans-serif;">LOG IN</h2>
    <p>Please login to access your account.</p>

    <?php if($login_error): ?>
      <p style="color:red; font-size:14px; margin-bottom:15px;"><?php echo $login_error; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <input type="hidden" name="login" value="1">
      <div class="input-group">
        <input type="text" name="username" placeholder="Username" required>
      </div>
      <div class="input-group">
        <input type="password" name="password" placeholder="Password" required>
      </div>
      <button type="submit" class="login-btn">LOGIN</button>
    </form>
  </div>
</div>

<!-- FOOTER -->
<footer>
  <p style="font-size: smaller;">© 2025 Ms. Tesay Chicken Sales Monitoring System</p>
</footer>

<!-- SCRIPTS -->
<script>
  // Hero slider
  let slides = document.querySelectorAll(".slide");
  let index = 0;
  function showSlide() {
    slides.forEach(s => s.classList.remove("active"));
    slides[index].classList.add("active");
    index = (index + 1) % slides.length;
  }
  setInterval(showSlide, 5000);

  // Login modal
  const loginBtn = document.getElementById('loginBtn');
  const loginModal = document.getElementById('loginModal');
  const closeModal = document.querySelector('.close-modal');

  const heroLoginBtns = [document.getElementById('heroLoginBtn'), document.getElementById('heroLoginBtn2'), document.getElementById('heroLoginBtn3')];

  loginBtn.onclick = () => loginModal.style.display = 'block';
  closeModal.onclick = () => loginModal.style.display = 'none';
  heroLoginBtns.forEach(btn => btn.onclick = () => loginModal.style.display = 'block');

  window.onclick = (e) => {
    if(e.target == loginModal) loginModal.style.display = 'none';
  }
</script>

</body>
</html>
