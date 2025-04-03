<?php
session_start();
require_once 'db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Get user's adoption requests
    $stmt = $pdo->prepare("
        SELECT a.*, p.name, p.breed, p.type, p.image_url, p.age, p.status as pet_status
        FROM adoptions a
        JOIN pets p ON a.pet_id = p.pet_id
        WHERE a.user_id = ?
        ORDER BY a.request_date DESC
    ");
    $stmt->execute([$user_id]);
    $adoptions = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Adoptly - My Adoptions</title>
  <meta name="description" content="View your adoption requests at Adoptly.">
  <meta name="keywords" content="adoptions, pets, adoption requests">
  <link rel="stylesheet" href="Adoptly.css">
  <script defer src="Adoptly.js"></script>
  <style>
    .adoptions-container {
      max-width: 800px;
      margin: 30px auto;
      padding: 0 15px;
    }
    
    .page-title {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .adoptions-list {
      background: white;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }
    
    .no-adoptions {
      padding: 40px;
      text-align: center;
      color: #666;
    }
    
    .adoption-item {
      display: flex;
      border-bottom: 1px solid #eee;
      padding: 20px;
    }
    
    .adoption-item:last-child {
      border-bottom: none;
    }
    
    .pet-image {
      width: 120px;
      height: 120px;
      border-radius: 10px;
      object-fit: cover;
      margin-right: 20px;
    }
    
    .adoption-info {
      flex: 1;
    }
    
    .pet-name {
      font-size: 1.3rem;
      margin: 0 0 5px 0;
      color: #333;
    }
    
    .pet-breed {
      font-size: 0.9rem;
      color: #777;
      margin-bottom: 10px;
    }
    
    .adoption-details {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-top: 10px;
    }
    
    .adoption-detail {
      font-size: 0.9rem;
      color: #555;
    }
    
    .adoption-status {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: bold;
      text-transform: uppercase;
    }
    
    .adoption-status.pending {
      background-color: #fef5e8;
      color: #f39c12;
    }
    
    .adoption-status.approved {
      background-color: #e8f7ee;
      color: #2ecc71;
    }
    
    .adoption-status.rejected {
      background-color: #fde8e8;
      color: #e74c3c;
    }
    
    .adoption-actions {
      display: flex;
      gap: 10px;
      margin-top: 10px;
    }
    
    .action-button {
      padding: 8px 15px;
      border: none;
      border-radius: 5px;
      font-size: 0.9rem;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.3s;
    }
    
    .view-button {
      background-color: #f5f5f5;
      color: #333;
    }
    
    .view-button:hover {
      background-color: #e5e5e5;
    }
    
    @media (max-width: 600px) {
      .adoption-item {
        flex-direction: column;
      }
      
      .pet-image {
        width: 100%;
        height: 180px;
        margin-right: 0;
        margin-bottom: 15px;
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
  <div class="adoptions-container">
    <h1 class="page-title">My Adoption Requests</h1>
    
    <?php if (isset($error_message)): ?>
      <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <div class="adoptions-list">
      <?php if (empty($adoptions)): ?>
        <div class="no-adoptions">
          <h3>No adoption requests found</h3>
          <p>You haven't made any adoption requests yet.</p>
          <a href="AdoptlyGallery.php" class="action-button view-button">Browse Pets</a>
        </div>
      <?php else: ?>
        <?php foreach ($adoptions as $adoption): ?>
          <div class="adoption-item">
            <img src="<?php echo htmlspecialchars($adoption['image_url']); ?>" alt="<?php echo htmlspecialchars($adoption['name']); ?>" class="pet-image">
            
            <div class="adoption-info">
              <h3 class="pet-name"><?php echo htmlspecialchars($adoption['name']); ?></h3>
              <div class="pet-breed">
                <?php echo htmlspecialchars($adoption['breed']); ?> • 
                <?php echo htmlspecialchars($adoption['age']); ?> years old
              </div>
              
              <div class="adoption-details">
                <div class="adoption-detail">
                  <strong>Request Date:</strong> <?php echo date('M j, Y', strtotime($adoption['request_date'])); ?>
                </div>
                
                <div class="adoption-detail">
                  <strong>Status:</strong> 
                  <span class="adoption-status <?php echo htmlspecialchars($adoption['status']); ?>">
                    <?php echo htmlspecialchars($adoption['status']); ?>
                  </span>
                </div>
                
                <?php if ($adoption['response_date']): ?>
                  <div class="adoption-detail">
                    <strong>Response Date:</strong> <?php echo date('M j, Y', strtotime($adoption['response_date'])); ?>
                  </div>
                <?php endif; ?>
              </div>
              
              <div class="adoption-actions">
                <a href="pet_details.php?id=<?php echo $adoption['pet_id']; ?>" class="action-button view-button">View Pet</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
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