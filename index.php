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
    <a href="index.php">Home</a>
    <a href="landing/about.php" >About</a>
    <a href="landing/products.php">Products</a>
    <a href="landing/contact.php">Contact</a>
    <a href="includes/login.php" class="user-icon">
    <svg width="35px" height="35px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path opacity="0.5" stroke="#000000" stroke-width="0.8" d="M12 22.01C17.5228 22.01 22 17.5329 22 12.01C22 6.48716 17.5228 2.01001 12 2.01001C6.47715 2.01001 2 6.48716 2 12.01C2 17.5329 6.47715 22.01 12 22.01Z" fill="none"/>
    <path d="M12 6.93994C9.93 6.93994 8.25 8.61994 8.25 10.6899C8.25 12.7199 9.84 14.3699 11.95 14.4299C11.98 14.4299 12.02 14.4299 12.04 14.4299C12.06 14.4299 12.09 14.4299 12.11 14.4299C12.12 14.4299 12.13 14.4299 12.13 14.4299C14.15 14.3599 15.74 12.7199 15.75 10.6899C15.75 8.61994 14.07 6.93994 12 6.93994Z" fill="#333"/>
    <path d="M18.7807 19.36C17.0007 21 14.6207 22.01 12.0007 22.01C9.3807 22.01 7.0007 21 5.2207 19.36C5.4607 18.45 6.1107 17.62 7.0607 16.98C9.7907 15.16 14.2307 15.16 16.9407 16.98C17.9007 17.62 18.5407 18.45 18.7807 19.36Z" fill="#333"/>
    </svg>
    </a>
  </nav>
</header>

<section class="hero-slider">
  <div class="slide active" style="background-image: url('assets/img/chicken-logo.png')">
    <div class="overlay"></div>
    <div class="hero-text animate-text">
      <h1 style="font-family: Urbanist, sans-serif;">Sales Monitoring Dashboard</h1>
      <p>Made for Ms. Tesay Chicken — built to keep products organized, track daily sales, and monitor branch performance with ease.</p>
      <a href="includes/login.php" class="cta">Proceed to Profile </a>
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


<script>
  let slides = document.querySelectorAll(".slide");
  let index = 0;
  function showSlide() {
    slides.forEach(s => s.classList.remove("active"));
    slides[index].classList.add("active");
    index = (index + 1) % slides.length;
  }
  setInterval(showSlide, 5000);

    const links = document.querySelectorAll("nav a");
    const visitedBefore = sessionStorage.getItem("visited");
    const current = window.location.pathname.split("/").pop();

  if (visitedBefore && current === "index.html") {
    document.querySelector('a[href="index.html"]').classList.add("active");
  }

  links.forEach(link => {
    link.addEventListener("click", () => {
      sessionStorage.setItem("visited", "yes");
    });
  });
</script>

</body>
</html>

<?php include "includes/footer.php"; ?>
