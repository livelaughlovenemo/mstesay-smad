<?php
session_start();
require_once "includes/db.php"; // Your PDO connection file

$login_error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php"); // Redirect after successful login
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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Castoro:ital@0;1&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lexend:wght@100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Pacifico&family=Urbanist:ital,wght@0,100..900;1,100..900&family=Varela+Round&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/landing-styles.css">
  <link rel="icon" type="image/png" href="assets/img/mainlogo.png">
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
    <a href="#login" class="login-btn">Login</a>
  </nav>
</header>

<section class="hero-slider">

  <div class="slide active" style="background-image: url('assets/img/signin.png')">
    <div class="overlay"></div>
    <div class="hero-text animate-text">
      <h1 style="font-family: Urbanist, sans-serif;">Sales Monitoring Dashboard</h1>
      <p>Made for Ms. Tesay Chicken — built to keep products organized, track daily sales, and monitor branch performance with ease.</p>
      <a href="#login" class="cta">Proceed to Login →</a>
    </div>
  </div>

  <div class="slide" style="background-image: url('assets/img/frozenfoods1.png')">
    <div class="overlay"></div>
    <div class="hero-text animate-text">
      <h1 style="font-family: Urbanist, sans-serif;">Real-Time Sales Insights</h1>
      <p>Accurate sales reporting across all product categories.</p>
      <a href="#login" class="cta">Access Dashboard</a>
    </div>
  </div>

  <div class="slide" style="background-image: url('assets/img/frozenfoods2.png')">
    <div class="overlay"></div>
    <div class="hero-text animate-text">
      <h1 style="font-family: Urbanist, sans-serif;">Branch Performance Tracking</h1>
      <p>Identify top-performing stores and optimize inventory levels.</p>
      <a href="#login" class="cta">View Branch Data</a>
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

<section class="login-section" id="login">
  <div class="login-box">
    <h2 style="font-family: Urbanist, sans-serif;">LOG IN</h2>
    <p>Please login to access your account.</p>

    <?php if($login_error): ?>
        <p style="color:red; font-size:14px; margin-bottom:15px;"><?php echo $login_error; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="input-group">
        <span class="icon">
          <!-- User Icon SVG -->
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="#F7743B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <input type="text" name="username" placeholder="Username" required>
      </div>

      <div class="input-group">
        <span class="icon">
          <!-- Password Icon SVG -->
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M7 10.0288C7.47142 10 8.05259 10 8.8 10H15.2C15.9474 10 16.5286 10 17 10.0288M7 10.0288C6.41168 10.0647 5.99429 10.1455 5.63803 10.327C5.07354 10.6146 4.6146 11.0735 4.32698 11.638C4 12.2798 4 13.1198 4 14.8V16.2C4 17.8802 4 18.7202 4.32698 19.362C4.6146 19.9265 5.07354 20.3854 5.63803 20.673C6.27976 21 7.11984 21 8.8 21H15.2C16.8802 21 17.7202 21 18.362 20.673C18.9265 20.3854 19.3854 19.9265 19.673 19.362C20 18.7202 20 17.8802 20 16.2V14.8C20 13.1198 20 12.2798 19.673 11.638C19.3854 11.0735 18.9265 10.6146 18.362 10.327C18.0057 10.1455 17.5883 10.0647 17 10.0288M7 10.0288V8C7 5.23858 9.23858 3 12 3C14.7614 3 17 5.23858 17 8V10.0288" stroke="#F7743B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <input type="password" name="password" placeholder="Password" required>
      </div>

      <button type="submit" class="login-btn">LOGIN</button>
    </form>
  </div>
</section>

<footer>
  <p style="font-size: smaller;">© 2025 Ms. Tesay Chicken Sales Monitoring System</p>
</footer>

<script>
let slides = document.querySelectorAll(".slide");
let index = 0;

function showSlide() {
  slides.forEach(s => s.classList.remove("active"));
  slides[index].classList.add("active");
  index = (index + 1) % slides.length;
}

setInterval(showSlide, 5000);
</script>

</body>
</html>
