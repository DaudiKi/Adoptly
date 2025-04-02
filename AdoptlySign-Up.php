<?php
session_start();
require_once 'db_connect.php';

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
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

        // Account type-specific fields
        if ($account_type === 'adopter') {
            $housing_type = filter_input(INPUT_POST, 'housingType', FILTER_SANITIZE_STRING);
            if (!$housing_type || !in_array($housing_type, ['apartment', 'house', 'townhouse', 'farm'])) {
                $errors['housingType'] = "Please select a valid housing type.";
            }
        } elseif ($account_type === 'volunteer') {
            $availability = filter_input(INPUT_POST, 'availability', FILTER_SANITIZE_STRING);
            if (!$availability || !in_array($availability, ['weekdays', 'weekends', 'evenings', 'flexible'])) {
                $errors['availability'] = "Please select a valid availability.";
            }
        } elseif ($account_type === 'rescuer') {
            $experience = filter_input(INPUT_POST, 'experience', FILTER_SANITIZE_NUMBER_INT);
            if ($experience === false || $experience === null || $experience < 0) {
                $errors['experience'] = "Please enter a valid number of years.";
            }
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

            // Insert into account type-specific table
            if ($account_type === 'adopter') {
                $stmt = $pdo->prepare("INSERT INTO adopters (user_id, housing_type) VALUES (?, ?)");
                $stmt->execute([$user_id, $housing_type]);
            } elseif ($account_type === 'volunteer') {
                $stmt = $pdo->prepare("INSERT INTO volunteers (user_id, availability) VALUES (?, ?)");
                $stmt->execute([$user_id, $availability]);
            } elseif ($account_type === 'rescuer') {
                $stmt = $pdo->prepare("INSERT INTO rescuers (user_id, experience_years) VALUES (?, ?)");
                $stmt->execute([$user_id, $experience]);
            }

            // Commit the transaction
            $pdo->commit();

            // Success message
            $success = "Sign up successful! You can now <a href='login.php'>log in</a>.";
            
            // Optionally, automatically log the user in
            // $_SESSION['user_id'] = $user_id;
            // $_SESSION['full_name'] = $full_name;
            // $_SESSION['account_type'] = $account_type;
            // header("Location: profile.php");
            // exit();
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
      <li><a href="AdoptlySign-Up.php">Sign Up</a></li>
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
    <?php if ($success): ?>
      <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>
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

      <div id="adopterFields" class="account-specific-fields" style="display: <?php echo (isset($_POST['accountType']) && $_POST['accountType'] === 'adopter') ? 'block' : 'none'; ?>;">
        <div class="form-group">
          <label for="housingType">Housing Type</label>
          <select id="housingType" name="housingType">
            <option value="">Select housing type</option>
            <option value="apartment" <?php echo (isset($_POST['housingType']) && $_POST['housingType'] === 'apartment') ? 'selected' : ''; ?>>Apartment</option>
            <option value="house" <?php echo (isset($_POST['housingType']) && $_POST['housingType'] === 'house') ? 'selected' : ''; ?>>House</option>
            <option value="townhouse" <?php echo (isset($_POST['housingType']) && $_POST['housingType'] === 'townhouse') ? 'selected' : ''; ?>>Townhouse</option>
            <option value="farm" <?php echo (isset($_POST['housingType']) && $_POST['housingType'] === 'farm') ? 'selected' : ''; ?>>Farm/Rural</option>
          </select>
          <span class="error" id="housingError"><?php echo isset($errors['housingType']) ? $errors['housingType'] : ''; ?></span>
        </div>
      </div>

      <div id="volunteerFields" class="account-specific-fields" style="display: <?php echo (isset($_POST['accountType']) && $_POST['accountType'] === 'volunteer') ? 'block' : 'none'; ?>;">
        <div class="form-group">
          <label for="availability">Availability</label>
          <select id="availability" name="availability">
            <option value="">Select availability</option>
            <option value="weekdays" <?php echo (isset($_POST['availability']) && $_POST['availability'] === 'weekdays') ? 'selected' : ''; ?>>Weekdays</option>
            <option value="weekends" <?php echo (isset($_POST['availability']) && $_POST['availability'] === 'weekends') ? 'selected' : ''; ?>>Weekends</option>
            <option value="evenings" <?php echo (isset($_POST['availability']) && $_POST['availability'] === 'evenings') ? 'selected' : ''; ?>>Evenings</option>
            <option value="flexible" <?php echo (isset($_POST['availability']) && $_POST['availability'] === 'flexible') ? 'selected' : ''; ?>>Flexible</option>
          </select>
          <span class="error" id="availabilityError"><?php echo isset($errors['availability']) ? $errors['availability'] : ''; ?></span>
        </div>
      </div>

      <div id="rescuerFields" class="account-specific-fields" style="display: <?php echo (isset($_POST['accountType']) && $_POST['accountType'] === 'rescuer') ? 'block' : 'none'; ?>;">
        <div class="form-group">
          <label for="experience">Years of Experience</label>
          <input type="number" id="experience" name="experience" placeholder="Years of rescue experience" value="<?php echo isset($_POST['experience']) ? htmlspecialchars($_POST['experience']) : ''; ?>">
          <span class="error" id="experienceError"><?php echo isset($errors['experience']) ? $errors['experience'] : ''; ?></span>
        </div>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Create a strong password" required>
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
</body>
</html>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to show/hide account-specific fields
    function toggleAccountFields() {
        var accountType = document.getElementById('accountType').value;
        document.getElementById('adopterFields').style.display = accountType === 'adopter' ? 'block' : 'none';
        document.getElementById('volunteerFields').style.display = accountType === 'volunteer' ? 'block' : 'none';
        document.getElementById('rescuerFields').style.display = accountType === 'rescuer' ? 'block' : 'none';
    }
    
    // Add event listener to account type dropdown
    var accountTypeSelect = document.getElementById('accountType');
    if (accountTypeSelect) {
        accountTypeSelect.addEventListener('change', toggleAccountFields);
        // Call the function on page load to handle pre-selected values
        toggleAccountFields();
    }
    
    // Handle terms and conditions modal
    var termsLink = document.querySelector('.terms-link');
    var termsModal = document.getElementById('terms-modal');
    var closeModal = document.querySelector('.close-modal');
    
    if (termsLink && termsModal && closeModal) {
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
    }
});
</script>