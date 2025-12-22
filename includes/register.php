<?php
session_start();
require_once "db.php";

$registrationError = '';
$successMessage = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register_submit'])) {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];
    $role = $_POST["role"] ?? 'Staff'; // Default to Staff for security

    // Validate inputs
    if (empty($username) || empty($password) || empty($confirmPassword)) {
        $registrationError = "All fields are required.";
    } elseif ($password !== $confirmPassword) {
        $registrationError = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $registrationError = "Password must be at least 6 characters long.";
    } else {
        // Check if username already exists
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = :u");
        $checkStmt->execute(["u" => $username]);
        
        if ($checkStmt->fetch()) {
            $registrationError = "Username already exists. Please choose another.";
        } else {
            // Hash the password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (:u, :ph, :r)");
            
            try {
                $stmt->execute([
                    "u" => $username,
                    "ph" => $passwordHash,
                    "r" => $role
                ]);
                
                $successMessage = "Registration successful! You can now <a href='login.php' style='color: #F5A200;'>login</a>.";
                
            } catch (PDOException $e) {
                $registrationError = "Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Ms. Tesay Chicken</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Castoro:ital@0;1&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lexend:wght@100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Pacifico&family=Urbanist:ital,wght@0,100..900;1,100..900&family=Varela+Round&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/login-styles.css">
  <link rel="icon" type="image/png" href="../assets/img/mainlogo.png">
  <style>
    .role-select {
        width: 100%;
        padding: 12px 40px;
        border: 2px solid #F5A200;
        border-radius: 25px;
        font-size: 16px;
        background-color: #FFF5E6;
        color: #333;
        margin-bottom: 15px;
        box-sizing: border-box;
    }
    
    .role-select:focus {
        outline: none;
        border-color: #F7743B;
    }
    
    .register-box {
        max-width: 400px;
        width: 90%;
    }
  </style>
</head>
<body>

    <header class="navbar">
    <div class="logo">
      <img src="../assets/img/mainlogo.png" alt="Logo">
      <h2 style="font-family: Pacifico, cursive;">Ms. Tesay Chicken</h2>
    </div>

    <nav>
    <a href="../index.php">Home</a>
    <a href="about.php">About</a>
    <a href="products.php">Products</a>
    <a href="contact.php">Contact</a>
    <a href="login.php" class="user-icon">
      <svg width="35px" height="35px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path opacity="0.5" stroke="#F5A200" stroke-width="0.8" d="M12 22.01C17.5228 22.01 22 17.5329 22 12.01C22 6.48716 17.5228 2.01001 12 2.01001C6.47715 2.01001 2 6.48716 2 12.01C2 17.5329 6.47715 22.01 12 22.01Z" fill="none"/>
      <path d="M12 6.93994C9.93 6.93994 8.25 8.61994 8.25 10.6899C8.25 12.7199 9.84 14.3699 11.95 14.4299C11.98 14.4299 12.02 14.4299 12.04 14.4299C12.06 14.4299 12.09 14.4299 12.11 14.4299C12.12 14.4299 12.13 14.4299 12.13 14.4299C14.15 14.3599 15.74 12.7199 15.75 10.6899C15.75 8.61994 14.07 6.93994 12 6.93994Z" fill="#F5A200"/>
      <path d="M18.7807 19.36C17.0007 21 14.6207 22.01 12.0007 22.01C9.3807 22.01 7.0007 21 5.2207 19.36C5.4607 18.45 6.1107 17.62 7.0607 16.98C9.7907 15.16 14.2307 15.16 16.9407 16.98C17.9007 17.62 18.5407 18.45 18.7807 19.36Z" fill="#F5A200"/>
      </svg>
      </a>
    </nav>
  </header>
  <div class="container">
    <div class="left-side"> 
      <img src="../assets/img/chicken-logo.png" style="opacity: 0.9;" alt="Registration Illustration">
    </div>

    <div class="right-side">
      <div class="login-box register-box">
        <h2>CREATE ACCOUNT</h2>
        <p>Please fill in the details to register.</p>
        
        <!-- Display error message if registration fails -->
        <?php if ($registrationError): ?>
          <div class="error-message" style="color: red; margin-bottom: 15px; text-align: center; padding: 10px; background-color: #FFE6E6; border-radius: 5px;">
            <?php echo htmlspecialchars($registrationError); ?>
          </div>
        <?php endif; ?>
        
        <!-- Display success message -->
        <?php if ($successMessage): ?>
          <div class="success-message" style="color: green; margin-bottom: 15px; text-align: center; padding: 10px; background-color: #E6FFE6; border-radius: 5px;">
            <?php echo $successMessage; ?>
          </div>
        <?php endif; ?>
        
        <?php if (!$successMessage): ?>
        <form method="POST" action="">
          <div class="input-group">
            <span class="icon">
              <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="#F7743B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
            <input type="text" name="username" placeholder="Username" required>
          </div>
          <div class="input-group">
            <span class="icon">
              <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M7 10.0288C7.47142 10 8.05259 10 8.8 10H15.2C15.9474 10 16.5286 10 17 10.0288M7 10.0288C6.41168 10.0647 5.99429 10.1455 5.63803 10.327C5.07354 10.6146 4.6146 11.0735 4.32698 11.638C4 12.2798 4 13.1198 4 14.8V16.2C4 17.8802 4 18.7202 4.32698 19.362C4.6146 19.9265 5.07354 20.3854 5.63803 20.673C6.27976 21 7.11984 21 8.8 21H15.2C16.8802 21 17.7202 21 18.362 20.673C18.9265 20.3854 19.3854 19.9265 19.673 19.362C20 18.7202 20 17.8802 20 16.2V14.8C20 13.1198 20 12.2798 19.673 11.638C19.3854 11.0735 18.9265 10.6146 18.362 10.327C18.0057 10.1455 17.5883 10.0647 17 10.0288M7 10.0288V8C7 5.23858 9.23858 3 12 3C14.7614 3 17 5.23858 17 8V10.0288" stroke="#F7743B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            </span>
            <input type="password" name="password" placeholder="Password (min. 6 characters)" required>
          </div>
          <div class="input-group">
            <span class="icon">
              <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M7 10.0288C7.47142 10 8.05259 10 8.8 10H15.2C15.9474 10 16.5286 10 17 10.0288M7 10.0288C6.41168 10.0647 5.99429 10.1455 5.63803 10.327C5.07354 10.6146 4.6146 11.0735 4.32698 11.638C4 12.2798 4 13.1198 4 14.8V16.2C4 17.8802 4 18.7202 4.32698 19.362C4.6146 19.9265 5.07354 20.3854 5.63803 20.673C6.27976 21 7.11984 21 8.8 21H15.2C16.8802 21 17.7202 21 18.362 20.673C18.9265 20.3854 19.3854 19.9265 19.673 19.362C20 18.7202 20 17.8802 20 16.2V14.8C20 13.1198 20 12.2798 19.673 11.638C19.3854 11.0735 18.9265 10.6146 18.362 10.327C18.0057 10.1455 17.5883 10.0647 17 10.0288M7 10.0288V8C7 5.23858 9.23858 3 12 3C14.7614 3 17 5.23858 17 8V10.0288" stroke="#F7743B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            </span>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
          </div>
          
          <select name="role" class="role-select">
            <option value="Staff">Staff</option>
            <option value="Manager">Manager</option>
            <!-- Admin role is typically assigned manually in the database for security -->
          </select>
          
          <button type="submit" name="register_submit">REGISTER</button>
        </form>
        
        <div class="register-link" style="text-align: center; margin-top: 20px;">
            <p>Already have an account? <a href="login.php" style="color: #F5A200; text-decoration: none;">Login here</a></p>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>