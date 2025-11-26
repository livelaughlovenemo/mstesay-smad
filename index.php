<?php
session_start();
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
  </nav>
</header>

<section class="hero-slider">
  <div class="slide active" style="background-image: url('assets/img/signin.png')">
    <div class="overlay"></div>
    <div class="hero-text animate-text">
      <h1 style="font-family: Urbanist, sans-serif;">Sales Monitoring Dashboard</h1>
      <p>Made for Ms. Tesay Chicken — built to keep products organized, track daily sales, and monitor branch performance with ease.</p>
      <a href="includes/login.php" class="cta">Proceed to Profile →</a>
    </div>
  </div>

  <div class="slide" style="background-image: url('assets/img/frozenfoods1.png')">
    <div class="overlay"></div>
    <div class="hero-text animate-text">
      <h1 style="font-family: Urbanist, sans-serif;">Real-Time Sales Insights</h1>
      <p>Accurate sales reporting across all product categories.</p>
      <a href="includes/login.php" class="cta">Access Dashboard</a>
    </div>
  </div>

  <div class="slide" style="background-image: url('assets/img/frozenfoods2.png')">
    <div class="overlay"></div>
    <div class="hero-text animate-text">
      <h1 style="font-family: Urbanist, sans-serif;">Branch Performance Tracking</h1>
      <p>Identify top-performing stores and optimize inventory levels.</p>
      <a href="includes/login.php" class="cta">View Branch Data</a>
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
  </div>
</section>

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
</script>

</body>
</html>
