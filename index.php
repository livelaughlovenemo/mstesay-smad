<?php
session_start();
require_once "includes/db.php";

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
<title>Ms. Tesay Chicken</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Castoro:ital@0;1&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lexend:wght@100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Pacifico&family=Urbanist:ital,wght@0,100..900;1,100..900&family=Varela+Round&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/landing-styles.css">
<link rel="icon" type="image/png" href="assets/img/mainlogo.png">
<style>
/* Modal Login Styles */
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0; top: 0;
  width: 100%; height: 100%;
  overflow: auto;
  background: rgba(0,0,0,0.6);
}
.modal-content {
  background: #fff;
  margin: 10% auto;
  padding: 30px;
  border-radius: 16px;
  max-width: 400px;
  position: relative;
}
.close-btn {
  position: absolute;
  top: 12px; right: 16px;
  font-size: 22px;
  cursor: pointer;
  font-weight: bold;
}
</style>
</head>
<body>

<header class="navbar">
  <div class="logo">
    <img src="assets/img/mainlogo.png" alt="Logo">
    <h2 style="font-family: Pacifico, cursive;">Ms. Tesay Chicken</h2>
  </div>

  <nav>
    <a href="#about">About</a>
    <a href="#branches">Branches</a>
    <a href="#contact">Contact</a>
    <?php if(isset($_SESSION['user_id'])): ?>
      <a href="dashboard.php" class="profile-btn">Profile</a>
    <?php else: ?>
      <a href="#" id="profileBtn" class="login-btn">Profile</a>
    <?php endif; ?>
  </nav>
</header>

<section class="hero-slider">
  <div class="slide active" style="background-image: url('assets/img/signin.png')">
    <div class="overlay"></div>
    <div class="hero-text animate-text">
      <h1 style="font-family: Urbanist, sans-serif;">Sales Monitoring Dashboard</h1>
      <p>Made for Ms. Tesay Chicken — built to keep products organized, track daily sales, and monitor branch performance with ease.</p>
      <a href="#" id="heroProfileBtn" class="cta">Proceed to Profile →</a>
    </div>
  </div>

  <div class="slide" style="background-image: url('assets/img/frozenfoods1.png')">
    <div class="overlay"></div>
    <div class="hero-text animate-text">
      <h1 style="font-family: Urbanist, sans-serif;">Real-Time Sales Insights</h1>
      <p>Accurate sales reporting across all product categories.</p>
      <a href="#" id="heroProfileBtn2" class="cta">Access Dashboard</a>
    </div>
  </div>

  <div class="slide" style="background-image: url('assets/img/frozenfoods2.png')">
    <div class="overlay"></div>
    <div class="hero-text animate-text">
      <h1 style="font-family: Urbanist, sans-serif;">Branch Performance Tracking</h1>
      <p>Identify top-performing stores and optimize inventory levels.</p>
      <a href="#" id="heroProfileBtn3" class="cta">View Branch Data</a>
    </div>
  </div>
</section>

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

<!-- Modal Login -->
<div class="modal" id="loginModal">
  <div class="modal-content">
    <span class="close-btn">&times;</span>
    <h2 style="font-family: Urbanist, sans-serif;">LOG IN</h2>
    <p>Please login to access your account.</p>
    <?php if($loginError): ?>
      <p style="color:red; margin-bottom:10px;"><?= htmlspecialchars($loginError) ?></p>
    <?php endif; ?>
    <form method="POST" action="">
      <div class="input-group">
        <span class="icon"></span>
        <input type="text" name="username" placeholder="Username" required>
      </div>
      <div class="input-group">
        <span class="icon"></span>
        <input type="password" name="password" placeholder="Password" required>
      </div>
      <button type="submit" name="login_submit" class="login-btn">LOGIN</button>
    </form>
  </div>
</div>

<footer>
  <p style="font-size: smaller;">© 2025 Ms. Tesay Chicken Sales Monitoring System</p>
</footer>

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

// Modal login
const modal = document.getElementById("loginModal");
const profileBtn = document.getElementById("profileBtn");
const heroBtns = [
  document.getElementById("heroProfileBtn"),
  document.getElementById("heroProfileBtn2"),
  document.getElementById("heroProfileBtn3")
];
const closeBtn = document.querySelector(".close-btn");

function openModal() { modal.style.display = "block"; }
function closeModal() { modal.style.display = "none"; }

if(profileBtn) profileBtn.addEventListener("click", openModal);
heroBtns.forEach(btn => { if(btn) btn.addEventListener("click", openModal); });
if(closeBtn) closeBtn.addEventListener("click", closeModal);

window.addEventListener("click", e => {
  if(e.target == modal) closeModal();
});

// Open modal automatically if login failed
<?php if($loginError): ?>
  modal.style.display = "block";
<?php endif; ?>
</script>

</body>
</html>
