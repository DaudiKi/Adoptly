<?php
session_start();
require_once 'db_connect.php';

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: AdoptlyHome.php");
    exit();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize and validate input
        $full_name = filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        $dob = filter_input(INPUT_POST, 'dob', FILTER_SANITIZE_STRING);
        $account_type = filter_input(INPUT_POST, 'accountType', FILTER_SANITIZE_STRING);
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirmPassword'] ?? '';

        // Server-side validation
        if (!$full_name || !preg_match("/^[A-Za-z\s]+$/", $full_name)) {
            $errors['fullName'] = "Please enter a valid name (alphabets only).";
        }

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Please enter a valid email address.";
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $errors['email'] = "This email is already registered.";
            }
        }

        if (!$phone || !preg_match("/^(\+?\d{1,3}[- ]?)?\d{10}$/", $phone)) {
            $errors['phone'] = "Please enter a valid phone number.";
        }

        if (!$dob) {
            $errors['dob'] = "Please enter your date of birth.";
        } else {
            $dob_date = new DateTime($dob);
            $today = new DateTime();
            $age = $today->diff($dob_date)->y;
            if ($dob_date > $today) {
                $errors['dob'] = "Date of birth cannot be in the future.";
            } elseif ($age < 18) {
                $errors['dob'] = "You must be 18 years or older to adopt a pet.";
            }
        }

        if (!$account_type || !in_array($account_type, ['adopter', 'volunteer', 'rescuer'])) {
            $errors['accountType'] = "Please select a valid account type.";
        }

        $password_regex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/";
        if (!$password || !preg_match($password_regex, $password)) {
            $errors['password'] = "Password must be at least 8 characters, include uppercase, lowercase, number, and special character.";
        }

        if ($password !== $confirm_password) {
            $errors['confirmPassword'] = "Passwords do not match.";
        }

        if (empty($errors)) {
            // Hash the password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Start a transaction
            $pdo->beginTransaction();

            // Insert into users table
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, dob, account_type, password_hash) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $email, $phone, $dob, $account_type, $password_hash]);
            $user_id = $pdo->lastInsertId();

            // Insert into account type-specific table (simplified - no specific fields)
            if ($account_type === 'adopter') {
                $stmt = $pdo->prepare("INSERT INTO adopters (user_id) VALUES (?)");
                $stmt->execute([$user_id]);
            } elseif ($account_type === 'volunteer') {
                $stmt = $pdo->prepare("INSERT INTO volunteers (user_id) VALUES (?)");
                $stmt->execute([$user_id]);
            } elseif ($account_type === 'rescuer') {
                $stmt = $pdo->prepare("INSERT INTO rescuers (user_id) VALUES (?)");
                $stmt->execute([$user_id]);
            }

            // Commit the transaction
            $pdo->commit();

            // Log the user in automatically
            $_SESSION['user_id'] = $user_id;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['account_type'] = $account_type;
            
            // Redirect to home page
            header("Location: AdoptlyHome.php");
            exit();
        }
    } catch (PDOException $e) {
        // Rollback the transaction if there was an error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $errors['general'] = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        $errors['general'] = "An unexpected error occurred: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Adoptly - Sign Up</title>
  <meta name="description" content="Sign up to adopt a pet at Adoptly.">
  <meta name="keywords" content="Sign Up, adoption, pet adoption, form">
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
  <section class="signup" aria-label="Sign Up Form">
    <h2>Sign Up to Adopt</h2>
    <?php if (isset($errors['general'])): ?>
      <p class="error"><?php echo $errors['general']; ?></p>
    <?php endif; ?>
    <form id="signupForm" method="POST" novalidate>
      <div class="form-group">
        <label for="fullName">Full Name</label>
        <input type="text" id="fullName" name="fullName" placeholder="Your full name" value="<?php echo isset($_POST['fullName']) ? htmlspecialchars($_POST['fullName']) : ''; ?>" required pattern="[A-Za-z\s]+">
        <span class="error" id="nameError"><?php echo isset($errors['fullName']) ? $errors['fullName'] : ''; ?></span>
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Your email address" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
        <span class="error" id="emailError"><?php echo isset($errors['email']) ? $errors['email'] : ''; ?></span>
      </div>

      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" placeholder="e.g., +1234567890" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required pattern="^(\+?\d{1,3}[- ]?)?\d{10}$">
        <span class="error" id="phoneError"><?php echo isset($errors['phone']) ? $errors['phone'] : ''; ?></span>
      </div>

      <div class="form-group">
        <label for="dob">Date of Birth</label>
        <input type="date" id="dob" name="dob" value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>" required>
        <span class="error" id="dobError"><?php echo isset($errors['dob']) ? $errors['dob'] : ''; ?></span>
        <small class="help-text">You must be 18 years or older to adopt a pet.</small>
      </div>

      <div class="form-group">
        <label for="accountType">Account Type</label>
        <select id="accountType" name="accountType" required>
          <option value="">Select account type</option>
          <option value="adopter" <?php echo (isset($_POST['accountType']) && $_POST['accountType'] === 'adopter') ? 'selected' : ''; ?>>Adopter</option>
          <option value="volunteer" <?php echo (isset($_POST['accountType']) && $_POST['accountType'] === 'volunteer') ? 'selected' : ''; ?>>Volunteer</option>
          <option value="rescuer" <?php echo (isset($_POST['accountType']) && $_POST['accountType'] === 'rescuer') ? 'selected' : ''; ?>>Rescuer</option>
        </select>
        <span class="error" id="accountError"><?php echo isset($errors['accountType']) ? $errors['accountType'] : ''; ?></span>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Create a strong password" required>
        <div id="password-strength" class="password-strength"></div>
        <span class="error" id="passwordError"><?php echo isset($errors['password']) ? $errors['password'] : ''; ?></span>
      </div>

      <div class="form-group">
        <label for="confirmPassword">Confirm Password</label>
        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter your password" required>
        <span class="error" id="confirmPasswordError"><?php echo isset($errors['confirmPassword']) ? $errors['confirmPassword'] : ''; ?></span>
      </div>

      <div class="form-group checkbox-group">
        <input type="checkbox" id="terms" name="terms" required>
        <label for="terms">I agree to the <a href="#" class="terms-link">Terms and Conditions</a></label>
        <span class="error" id="termsError"></span>
      </div>

      <button type="submit">Sign Up</button>
    </form>
  </section>
</main>

<!-- Terms and Conditions Modal -->
<div id="terms-modal" class="modal">
  <div class="modal-content">
    <span class="close-modal">×</span>
    <h2>Terms and Conditions</h2>
    <div class="terms-content">
      <h3>Adoption Agreement</h3>
      <p>By creating an account on Adoptly, you agree to provide accurate information about yourself and your living situation. This helps us ensure the best match between pets and their potential new homes.</p>

      <h3>Privacy Policy</h3>
      <p>We respect your privacy and will only use your personal information to facilitate the adoption process. Your information will not be shared with third parties without your consent.</p>

      <h3>Adoption Process</h3>
      <p>Creating an account does not guarantee approval for adoption. All adopters must complete our screening process, which may include a home visit, reference checks, and an interview.</p>

      <h3>Account Responsibilities</h3>
      <p>You are responsible for maintaining the confidentiality of your account and password. Please notify us immediately of any unauthorized use of your account.</p>
    </div>
  </div>
</div>

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
    // =============== REAL-TIME DATE OF BIRTH VALIDATION ===============
    const dobInput = document.getElementById('dob');
    const dobError = document.getElementById('dobError');
    
    dobInput.addEventListener('change', function() {
        validateDOB();
    });
    
    function validateDOB() {
        const dobValue = dobInput.value;
        dobError.textContent = '';
        
        if (!dobValue) {
            dobError.textContent = 'Please enter your date of birth.';
            return false;
        }
        
        const dobDate = new Date(dobValue);
        const today = new Date();
        
        // Check if date is in the future
        if (dobDate > today) {
            dobError.textContent = 'Date of birth cannot be in the future.';
            return false;
        }
        
        // Calculate age
        const yearDiff = today.getFullYear() - dobDate.getFullYear();
        const monthDiff = today.getMonth() - dobDate.getMonth();
        const dayDiff = today.getDate() - dobDate.getDate();
        
        // Adjust age if birth month/day hasn't occurred yet this year
        let age = yearDiff;
        if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
            age--;
        }
        
        // Check if age is at least 18
        if (age < 18) {
            dobError.textContent = 'You must be 18 years or older to adopt a pet.';
            return false;
        }
        
        return true;
    }
    
    // =============== REAL-TIME PASSWORD VALIDATION ===============
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const passwordError = document.getElementById('passwordError');
    const confirmPasswordError = document.getElementById('confirmPasswordError');
    const passwordStrengthDiv = document.getElementById('password-strength');
    
    passwordInput.addEventListener('input', function() {
        validatePassword();
        checkPasswordMatch();
    });
    
    confirmPasswordInput.addEventListener('input', function() {
        checkPasswordMatch();
    });
    
    function validatePassword() {
        const password = passwordInput.value;
        passwordError.textContent = '';
        
        // Clear strength indicator if empty
        if (!password) {
            passwordStrengthDiv.textContent = '';
            passwordStrengthDiv.className = 'password-strength';
            return false;
        }
        
        // Check for minimum length
        if (password.length < 8) {
            passwordError.textContent = 'Password must be at least 8 characters.';
            passwordStrengthDiv.textContent = 'Weak';
            passwordStrengthDiv.className = 'password-strength weak';
            return false;
        }
        
        // Initialize strength score
        let strengthScore = 0;
        
        // Check for lowercase letters
        if (/[a-z]/.test(password)) {
            strengthScore++;
        } else {
            passwordError.textContent = 'Password must include lowercase letters.';
        }
        
        // Check for uppercase letters
        if (/[A-Z]/.test(password)) {
            strengthScore++;
        } else {
            passwordError.textContent = passwordError.textContent || 'Password must include uppercase letters.';
        }
        
        // Check for numbers
        if (/\d/.test(password)) {
            strengthScore++;
        } else {
            passwordError.textContent = passwordError.textContent || 'Password must include numbers.';
        }
        
        // Check for special characters
        if (/[\W_]/.test(password)) {
            strengthScore++;
        } else {
            passwordError.textContent = passwordError.textContent || 'Password must include special characters.';
        }
        
        // Set strength indicator
        if (strengthScore === 0) {
            passwordStrengthDiv.textContent = 'Very Weak';
            passwordStrengthDiv.className = 'password-strength very-weak';
        } else if (strengthScore === 1) {
            passwordStrengthDiv.textContent = 'Weak';
            passwordStrengthDiv.className = 'password-strength weak';
        } else if (strengthScore === 2) {
            passwordStrengthDiv.textContent = 'Medium';
            passwordStrengthDiv.className = 'password-strength medium';
        } else if (strengthScore === 3) {
            passwordStrengthDiv.textContent = 'Strong';
            passwordStrengthDiv.className = 'password-strength strong';
        } else {
            passwordStrengthDiv.textContent = 'Very Strong';
            passwordStrengthDiv.className = 'password-strength very-strong';
        }
        
        return strengthScore === 4;
    }
    
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        confirmPasswordError.textContent = '';
        
        if (confirmPassword && password !== confirmPassword) {
            confirmPasswordError.textContent = 'Passwords do not match.';
            return false;
        }
        
        return true;
    }
    
    // =============== FORM SUBMISSION VALIDATION ===============
    const signupForm = document.getElementById('signupForm');
    const termsCheckbox = document.getElementById('terms');
    const termsError = document.getElementById('termsError');
    
    signupForm.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validate DOB
        if (!validateDOB()) {
            isValid = false;
        }
        
        // Validate password
        if (!validatePassword()) {
            isValid = false;
        }
        
        // Check password match
        if (!checkPasswordMatch()) {
            isValid = false;
        }
        
        // Check terms agreement
        if (!termsCheckbox.checked) {
            termsError.textContent = 'You must agree to the Terms and Conditions.';
            isValid = false;
        } else {
            termsError.textContent = '';
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // =============== TERMS AND CONDITIONS MODAL ===============
    const termsLink = document.querySelector('.terms-link');
    const termsModal = document.getElementById('terms-modal');
    const closeModal = document.querySelector('.close-modal');
    
    termsLink.addEventListener('click', function(e) {
        e.preventDefault();
        termsModal.style.display = 'block';
    });
    
    closeModal.addEventListener('click', function() {
        termsModal.style.display = 'none';
    });
    
    window.addEventListener('click', function(e) {
        if (e.target == termsModal) {
            termsModal.style.display = 'none';
        }
    });
});
</script>
</body>
</html>