<?php
session_start();
require_once 'db_connect.php';

// Check if pet ID is provided or not
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: AdoptlyGallery.php");
    exit();
}

$pet_id = (int) $_GET['id'];
$user_logged_in = isset($_SESSION['user_id']);
$is_favorite = false;
$has_pending_adoption = false;

try {
    // Get pet details
    $stmt = $pdo->prepare("SELECT * FROM pets WHERE pet_id = ?");
    $stmt->execute([$pet_id]);
    $pet = $stmt->fetch();
    
    if (!$pet) {
        // Pet not found
        header("Location: AdoptlyGallery.php");
        exit();
    }
    
    // Check if this pet is already a favorite for the logged-in user
    if ($user_logged_in) {
        $user_id = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND pet_id = ?");
        $stmt->execute([$user_id, $pet_id]);
        $is_favorite = $stmt->fetchColumn() > 0;
        
        // Check if user has a pending adoption request
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM adoptions WHERE user_id = ? AND pet_id = ? AND status = 'pending'");
        $stmt->execute([$user_id, $pet_id]);
        $has_pending_adoption = $stmt->fetchColumn() > 0;
    }
    
    // Get adoption status message
    $adoption_message = '';
    if (isset($_GET['adoption'])) {
        $adoption_status = $_GET['adoption'];
        
        switch ($adoption_status) {
            case 'success':
                $adoption_message = "Thank you for your adoption request! Our team will contact you soon to complete the process.";
                $message_type = "success";
                break;
            case 'unavailable':
                $adoption_message = "Sorry, this pet is no longer available for adoption.";
                $message_type = "error";
                break;
            case 'duplicate':
                $adoption_message = "You already have a pending adoption request for this pet.";
                $message_type = "info";
                break;
            case 'error':
                $adoption_message = "There was an error processing your adoption request. Please try again later.";
                $message_type = "error";
                break;
        }
    }
    
    // Handle favorite button submission via the pet details page
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['favorite']) && $user_logged_in) {
        $user_id = $_SESSION['user_id'];
        
        if ($is_favorite) {
            // Remove from favorites
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND pet_id = ?");
            $stmt->execute([$user_id, $pet_id]);
            $is_favorite = false;
            $favorite_message = "Removed from favorites.";
            $favorite_type = "info";
        } else {
            // Add to favorites
            $stmt = $pdo->prepare("INSERT INTO favorites (user_id, pet_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $pet_id]);
            $is_favorite = true;
            $favorite_message = "Added to favorites!";
            $favorite_type = "success";
        }
    }
    
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Adoptly - <?php echo isset($pet) ? htmlspecialchars($pet['name']) : 'Pet Details'; ?></title>
  <meta name="description" content="View details and adopt <?php echo htmlspecialchars($pet['name']); ?> at Adoptly.">
  <meta name="keywords" content="Pet adoption, <?php echo htmlspecialchars($pet['breed']); ?>, <?php echo htmlspecialchars($pet['type']); ?>">
  <link rel="stylesheet" href="Adoptly.css">
  <script defer src="Adoptly.js"></script>
  <style>
    .pet-detail-container {
      max-width: 1000px;
      margin: 30px auto;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }
    
    .pet-detail-header {
      position: relative;
      height: 300px;
      overflow: hidden;
    }
    
    .pet-detail-header img {
      width: 100%;
      height: 1000px;
      object-fit: fill;
    }
    
    .pet-status-badge {
      position: absolute;
      top: 20px;
      right: 20px;
      padding: 5px 15px;
      border-radius: 20px;
      font-weight: bold;
      color: white;
      text-transform: uppercase;
      font-size: 0.8rem;
    }
    
    .pet-status-badge.available {
      background-color: #2ecc71;
    }
    
    .pet-status-badge.pending {
      background-color: #f39c12;
    }
    
    .pet-status-badge.adopted {
      background-color: #3498db;
    }
    
    .pet-detail-content {
      padding: 30px;
    }
    
    .pet-detail-name {
      font-size: 2rem;
      margin: 0 0 5px 0;
      color: #333;
    }
    
    .pet-detail-breed {
      font-size: 1.2rem;
      color: #777;
      margin-bottom: 20px;
    }
    
    .alert-message {
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-weight: 500;
    }
    
    .alert-success {
      background-color: #e8f7ee;
      color: #2ecc71;
    }
    
    .alert-error {
      background-color: #fde8e8;
      color: #e74c3c;
    }
    
    .alert-info {
      background-color: #e8f4fc;
      color: #3498db;
    }
    
    .pet-detail-info {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .pet-info-item {
      flex: 1;
      min-width: 120px;
      background: #f7f7f7;
      padding: 15px;
      border-radius: 8px;
      text-align: center;
    }
    
    .info-label {
      font-size: 0.9rem;
      color: #777;
      margin-bottom: 5px;
    }
    
    .info-value {
      font-size: 1.1rem;
      font-weight: bold;
      color: #333;
    }
    
    .pet-detail-description {
      margin-bottom: 30px;
      line-height: 1.6;
    }
    
    .action-buttons {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-top: 30px;
    }
    
    .pet-button {
      flex: 1;
      min-width: 120px;
      padding: 12px 15px;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      cursor: pointer;
      text-align: center;
      text-decoration: none;
      transition: all 0.3s;
      font-weight: bold;
    }
    
    .pet-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .back-button {
      background-color: #f5f5f5;
      color: #333;
    }
    
    .adopt-button {
      background: linear-gradient(90deg, #ff7e5f, #feb47b);
      color: white;
    }
    
    .adopt-button.disabled {
      background: #ccc;
      cursor: not-allowed;
    }
    
    .favorite-button {
      background-color: white;
      border: 2px solid #ff7e5f;
      color: #ff7e5f;
    }
    
    .favorite-button.active {
      background-color: #ff7e5f;
      color: white;
    }
    
    .login-prompt {
      text-align: center;
      margin-top: 20px;
      padding: 15px;
      background-color: #f7f7f7;
      border-radius: 8px;
    }
    
    @media (max-width: 600px) {
      .action-buttons {
        flex-direction: column;
      }
      
      .pet-button {
        width: 100%;
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
  <div class="pet-detail-container">
    <?php if (isset($pet)): ?>
      <div class="pet-detail-header">
        <img src="<?php echo htmlspecialchars($pet['image_url']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?> - <?php echo htmlspecialchars($pet['breed']); ?>">
        <div class="pet-status-badge <?php echo htmlspecialchars($pet['status']); ?>">
          <?php echo htmlspecialchars($pet['status']); ?>
        </div>
      </div>
      
      <div class="pet-detail-content">
        <h1 class="pet-detail-name"><?php echo htmlspecialchars($pet['name']); ?></h1>
        <div class="pet-detail-breed"><?php echo htmlspecialchars($pet['breed']); ?></div>
        
        <?php if (isset($adoption_message)): ?>
          <div class="alert-message alert-<?php echo $message_type; ?>"><?php echo $adoption_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($favorite_message)): ?>
          <div class="alert-message alert-<?php echo $favorite_type; ?>"><?php echo $favorite_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
          <div class="alert-message alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="pet-detail-info">
          <div class="pet-info-item">
            <div class="info-label">Type</div>
            <div class="info-value"><?php echo ucfirst(htmlspecialchars($pet['type'])); ?></div>
          </div>
          
          <div class="pet-info-item">
            <div class="info-label">Age</div>
            <div class="info-value"><?php echo htmlspecialchars($pet['age']); ?> years</div>
          </div>
          
          <div class="pet-info-item">
            <div class="info-label">Status</div>
            <div class="info-value"><?php echo ucfirst(htmlspecialchars($pet['status'])); ?></div>
          </div>
        </div>
        
        <div class="pet-detail-description">
          <h3>About <?php echo htmlspecialchars($pet['name']); ?></h3>
          <p><?php echo htmlspecialchars($pet['description']); ?></p>
        </div>
        
        <div class="action-buttons">
          <a href="AdoptlyGallery.php" class="pet-button back-button">Back to Gallery</a>
          
          <?php if ($user_logged_in): ?>
            <form method="POST" style="flex: 1; min-width: 120px;">
              <button type="submit" name="favorite" class="pet-button favorite-button <?php echo $is_favorite ? 'active' : ''; ?>">
                <?php echo $is_favorite ? 'Remove from Favorites' : 'Add to Favorites'; ?>
              </button>
            </form>
            
            <?php if ($pet['status'] === 'available'): ?>
              <a href="adopt.php?id=<?php echo $pet_id; ?>" class="pet-button adopt-button">Adopt <?php echo htmlspecialchars($pet['name']); ?></a>
            <?php elseif ($has_pending_adoption): ?>
              <button class="pet-button adopt-button disabled">Adoption Pending</button>
            <?php else: ?>
              <button class="pet-button adopt-button disabled">Not Available</button>
            <?php endif; ?>
          <?php else: ?>
            <a href="login.php?redirect=<?php echo urlencode('pet_details.php?id=' . $pet_id); ?>" class="pet-button favorite-button">Login to Add to Favorites</a>
            <a href="login.php?redirect=<?php echo urlencode('pet_details.php?id=' . $pet_id); ?>" class="pet-button adopt-button">Login to Adopt</a>
          <?php endif; ?>
        </div>
        
        <?php if (!$user_logged_in): ?>
          <div class="login-prompt">
            <p>Please <a href="login.php?redirect=<?php echo urlencode('pet_details.php?id=' . $pet_id); ?>">log in</a> or <a href="AdoptlySign-Up.php">sign up</a> to adopt or favorite this pet.</p>
          </div>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div style="text-align: center; padding: 50px;">
        <h2>Pet not found</h2>
        <p>Sorry, the pet you're looking for doesn't exist.</p>
        <a href="AdoptlyGallery.php" class="pet-button back-button" style="display: inline-block; margin-top: 20px;">Return to Gallery</a>
      </div>
    <?php endif; ?>
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
</body>
</html>