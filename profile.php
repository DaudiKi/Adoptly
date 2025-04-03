<?php
session_start();
require_once 'db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        // User not found in database
        session_destroy();
        header("Location: login.php?error=user_not_found");
        exit();
    }

    // Handle form submission for updating profile
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize and validate input
        $full_name = filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_STRING);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirmPassword'] ?? '';

        // Validate input
        if (!$full_name || !preg_match("/^[A-Za-z\s]+$/", $full_name)) {
            $errors['fullName'] = "Please enter a valid name (alphabets only).";
        }

        if (!$phone || !preg_match("/^(\+?\d{1,3}[- ]?)?\d{10}$/", $phone)) {
            $errors['phone'] = "Please enter a valid phone number.";
        }

        // Password validation only if a new password is provided
        if (!empty($password)) {
            $password_regex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/";
            if (!preg_match($password_regex, $password)) {
                $errors['password'] = "Password must be at least 8 characters, include uppercase, lowercase, number, and special character.";
            } elseif ($password !== $confirm_password) {
                $errors['confirmPassword'] = "Passwords do not match.";
            }
        }

        if (empty($errors)) {
            try {
                // Start a transaction
                $pdo->beginTransaction();

                // Update users table (email is not changeable)
                if (!empty($password)) {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, password_hash = ? WHERE user_id = ?");
                    $stmt->execute([$full_name, $phone, $password_hash, $user_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE user_id = ?");
                    $stmt->execute([$full_name, $phone, $user_id]);
                }

                // Commit the transaction
                $pdo->commit();
                
                // Success message
                $success = "Profile updated successfully!";
                
                // Update session data
                $_SESSION['full_name'] = $full_name;
                
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
                
            } catch (PDOException $e) {
                // Rollback the transaction if there was an error
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors['general'] = "Database error: " . $e->getMessage();
            }
        }
    }
    
    // Get user's favorites count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $favorites_count = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    $errors['general'] = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $errors['general'] = "An unexpected error occurred: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Adoptly - My Profile</title>
  <meta name="description" content="Manage your Adoptly profile.">
  <meta name="keywords" content="Profile, adoption, pet adoption">
  <link rel="stylesheet" href="Adoptly.css">
  <script defer src="Adoptly.js"></script>
  <style>
    /* Profile page styles */
    .profile-container {
      max-width: 800px;
      margin: 30px auto;
    }
    
    .profile-header {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .profile-avatar {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      background: linear-gradient(90deg, #ff7e5f, #feb47b);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 15px;
      color: white;
      font-size: 2.5rem;
      font-weight: bold;
    }
    
    .profile-name {
      font-size: 1.8rem;
      margin: 10px 0 5px;
    }
    
    .profile-type {
      display: inline-block;
      background: linear-gradient(90deg, #ff7e5f, #feb47b);
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 0.9rem;
      margin: 5px 0 15px;
    }
    
    .profile-form {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      padding: 30px;
    }
    
    .form-title {
      margin-top: 0;
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 1px solid #eee;
      color: #333;
      font-size: 1.4rem;
    }
    
    .form-item {
      margin-bottom: 25px;
    }
    
    .form-label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: #444;
    }
    
    .form-input {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 1rem;
      margin-bottom: 5px;
    }
    
    .form-input.readonly {
      background-color: #f7f7f7;
      cursor: not-allowed;
    }
    
    .form-help {
      font-size: 0.85rem;
      color: #777;
      margin-top: 5px;
    }
    
    .password-section {
      margin-top: 40px;
      padding-top: 20px;
      border-top: 1px solid #eee;
    }
    
    .form-buttons {
      margin-top: 30px;
      display: flex;
      gap: 10px;
    }
    
    .form-button {
      padding: 12px 25px;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .form-button.primary {
      background: linear-gradient(90deg, #ff7e5f, #feb47b);
      color: white;
      font-weight: bold;
    }
    
    .form-button.secondary {
      background: transparent;
      border: 1px solid #ddd;
      color: #555;
    }
    
    .form-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .success-message {
      background-color: #e8f7ee;
      color: #2ecc71;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      text-align: center;
      font-weight: 500;
    }
    
    .error-message {
      color: #ff4d4d;
      margin-top: 5px;
      font-size: 0.9rem;
    }
    
    .nav-links {
      display: flex;
      justify-content: center;
      margin-top: 20px;
      gap: 20px;
    }
    
    .nav-link {
      text-decoration: none;
      color: #555;
      transition: color 0.3s;
    }
    
    .nav-link:hover {
      color: #ff7e5f;
    }
    
    @media (max-width: 768px) {
      .profile-form {
        padding: 20px;
      }
    }
  </style>
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
      <li><a href="AdoptlySign-Up.php">Sign Up</a></li>
      <li><a href="my_adoptions.php" class="nav-link">My Adoptions</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
      <?php else: ?>
        <li><a href="login.php">Login</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</header>
<main>
  <div class="profile-container">
    <div class="profile-header">
      <div class="profile-avatar">
        <?php echo substr($user['full_name'], 0, 1); ?>
      </div>
      <h2 class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></h2>
      <span class="profile-type"><?php echo ucfirst(htmlspecialchars($user['account_type'])); ?></span>
      <div class="nav-links">
        <a href="AdoptlyGallery.php" class="nav-link">My Favorites (<?php echo $favorites_count; ?>)</a>
        <a href="logout.php" class="nav-link">Logout</a>
      </div>
    </div>
    
    <?php if ($success): ?>
      <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (isset($errors['general'])): ?>
      <div class="error-message"><?php echo $errors['general']; ?></div>
    <?php endif; ?>
    
    <form method="POST" class="profile-form">
      <h3 class="form-title">Account Details</h3>
      
      <div class="form-item">
        <label class="form-label" for="fullName">Full Name</label>
        <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($user['full_name']); ?>" class="form-input" required pattern="[A-Za-z\s]+">
        <?php if (isset($errors['fullName'])): ?>
          <div class="error-message"><?php echo $errors['fullName']; ?></div>
        <?php endif; ?>
      </div>
      
      <div class="form-item">
        <label class="form-label" for="email">Email (cannot be changed)</label>
        <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-input readonly" readonly>
      </div>
      
      <div class="form-item">
        <label class="form-label" for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="form-input" required pattern="^(\+?\d{1,3}[- ]?)?\d{10}$">
        <?php if (isset($errors['phone'])): ?>
          <div class="error-message"><?php echo $errors['phone']; ?></div>
        <?php endif; ?>
      </div>
      
      <div class="form-item">
        <label class="form-label" for="dob">Date of Birth (cannot be changed)</label>
        <input type="date" id="dob" value="<?php echo htmlspecialchars($user['dob']); ?>" class="form-input readonly" readonly>
      </div>
      
      <div class="form-item">
        <label class="form-label" for="accountType">Account Type (cannot be changed)</label>
        <input type="text" id="accountType" value="<?php echo ucfirst(htmlspecialchars($user['account_type'])); ?>" class="form-input readonly" readonly>
      </div>
      
      <div class="form-item">
        <label class="form-label" for="created">Member Since</label>
        <input type="text" id="created" value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" class="form-input readonly" readonly>
      </div>
      
      <div class="password-section">
        <h3 class="form-title">Change Password</h3>
        <p class="form-help">Leave blank to keep your current password</p>
        
        <div class="form-item">
          <label class="form-label" for="password">New Password</label>
          <input type="password" id="password" name="password" placeholder="Enter new password" class="form-input">
          <?php if (isset($errors['password'])): ?>
            <div class="error-message"><?php echo $errors['password']; ?></div>
          <?php endif; ?>
        </div>
        
        <div class="form-item">
          <label class="form-label" for="confirmPassword">Confirm New Password</label>
          <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter new password" class="form-input">
          <?php if (isset($errors['confirmPassword'])): ?>
            <div class="error-message"><?php echo $errors['confirmPassword']; ?></div>
          <?php endif; ?>
        </div>
      </div>
      
      <div class="form-buttons">
        <button type="submit" class="form-button primary">Update Profile</button>
        <button type="reset" class="form-button secondary">Reset Changes</button>
      </div>
    </form>
  </div>
</main>
<footer role="contentinfo">
  <div class="footer-contact">
    <p>
      Contact us:
      <a href="mailto:support@adoptly.com">support@adoptly.com</a> |
      Phone: +254756387850
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password validation
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    
    confirmPasswordInput.addEventListener('input', function() {
        if (passwordInput.value && this.value) {
            if (passwordInput.value !== this.value) {
                this.setCustomValidity("Passwords don't match");
            } else {
                this.setCustomValidity('');
            }
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Reset button confirmation
    const resetButton = document.querySelector('button[type="reset"]');
    resetButton.addEventListener('click', function(e) {
        if (!confirm('Are you sure you want to reset all changes?')) {
            e.preventDefault();
        }
    });
});
</script>
</body>
</html>