<?php
session_start();
require_once 'db_connect.php';

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: AdoptlyHome.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        // Validate input
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif (empty($password)) {
            $error = "Please enter your password.";
        } else {
            // Check if the email exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Start session and store user data
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['account_type'] = $user['account_type'];
                
                // Redirect to home page (changed from profile to home)
                header("Location: AdoptlyHome.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        $error = "An unexpected error occurred: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Adoptly - Login</title>
  <meta name="description" content="Log in to your Adoptly account to adopt a pet.">
  <meta name="keywords" content="Login, adoption, pet adoption">
  <link rel="stylesheet" href="Adoptly.css">
  <script defer src="Adoptly.js"></script>
</head>
<body>
<header role="banner">
  <div class="logo">
    <h1>Adoptly</h1>
  </div>
  <nav role="navigation">
    <ul>
      <li><a href="AdoptlyHome.php">Home</a></li>
      <li><a href="AdoptlyGallery.php">Gallery</a></li>
      <li><a href="signup.php">Sign Up</a></li>
      <li><a href="AdoptlyQuiz.php">Pet Matcher</a></li>
      <li><a href="login.php">Login</a></li>
    </ul>
  </nav>
</header>
<main>
  <section class="login" aria-label="Login Form">
    <h2>Log In to Adoptly</h2>
    <?php if ($error): ?>
      <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST" class="login-form">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Your email address" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Your password" required>
      </div>
      <button type="submit" class="btn-primary">Log In</button>
      <p class="form-link">Don't have an account? <a href="signup.php">Sign Up</a></p>
    </form>
  </section>
</main>
<footer role="contentinfo">
  <div class="footer-contact">
    <p>
      Contact us:
      <a href="mailto:support@adoptly.com">support@adoptly.com</a> |
      Phone: +1234567890
    </p>
  </div>
  <div class="footer-social">
    <a href="https://facebook.com" target="_blank" aria-label="Facebook">Facebook</a> |
    <a href="https://twitter.com" target="_blank" aria-label="Twitter">Twitter</a> |
    <a href="https://instagram.com" target="_blank" aria-label="Instagram">Instagram</a>
  </div>
  <p>© 2025 Adoptly. All rights reserved.</p>
</footer>
<div class="back-to-top" aria-label="Back to top">⇧</div>
</body>
</html>