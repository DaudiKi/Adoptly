<?php
session_start();
require_once 'db_connect.php';

// Fetch all pets from the database
try {
    // Simple query to get all pets
    $stmt = $pdo->prepare("SELECT * FROM pets ORDER BY name ASC");
    $stmt->execute();
    $pets = $stmt->fetchAll();
    
    // Get user's favorites if logged in
    $favorites = [];
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT pet_id FROM favorites WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $favRows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $favorites = array_flip($favRows); // For faster lookup
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
  <title>Adoptly - Gallery</title>
  <meta name="description" content="Browse our gallery of adorable pets available for adoption at Adoptly.">
  <meta name="keywords" content="Gallery, pets, pet adoption, adopt, photos">
  <link rel="stylesheet" href="Adoptly.css">
  <script defer src="Adoptly.js"></script>
  <style>
    /* Enhanced Gallery Styles */
    .gallery-container {
      max-width: 1200px;
      margin: 30px auto;
      padding: 0 15px;
    }
    
    .gallery-header {
      margin-bottom: 30px;
      text-align: center;
    }
    
    .gallery-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 25px;
    }
    
    .pet-card {
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .pet-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }
    
    .pet-image {
      height: 200px;
      width: 100%;
      object-fit: cover;
    }
    
    .pet-info {
      padding: 20px;
    }
    
    .pet-info h3 {
      margin: 0 0 5px 0;
      color: #333;
      font-size: 1.4rem;
    }
    
    .pet-breed {
      color: #777;
      margin-bottom: 10px;
      font-size: 0.95rem;
    }
    
    .pet-age {
      margin-bottom: 15px;
      font-size: 0.9rem;
    }
    
    .pet-status {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: bold;
      text-transform: uppercase;
      margin-bottom: 15px;
    }
    
    .pet-status.available {
      background-color: #e8f7ee;
      color: #2ecc71;
    }
    
    .pet-status.pending {
      background-color: #fef5e8;
      color: #f39c12;
    }
    
    .pet-status.adopted {
      background-color: #e8f4fc;
      color: #3498db;
    }
    
    .pet-description {
      margin-bottom: 20px;
      font-size: 0.95rem;
      line-height: 1.5;
      color: #555;
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
    }
    
    .pet-buttons {
      display: flex;
      gap: 10px;
    }
    
    .pet-button {
      flex: 1;
      padding: 10px;
      border: none;
      border-radius: 8px;
      font-size: 0.9rem;
      cursor: pointer;
      text-align: center;
      text-decoration: none;
      transition: all 0.3s;
    }
    
    .pet-button:hover {
      transform: translateY(-2px);
    }
    
    .details-button {
      background: linear-gradient(90deg, #ff7e5f, #feb47b);
      color: white;
      font-weight: bold;
    }
    
    .favorite-button {
      background: transparent;
      border: 2px solid #ff7e5f;
      color: #ff7e5f;
    }
    
    .favorite-button.active {
      background: #ff7e5f;
      color: white;
    }
    
    /* For no results */
    .no-results {
      text-align: center;
      padding: 50px;
      background: #fff;
      border-radius: 10px;
      margin-top: 20px;
    }
    
    /* For favorites section */
    .favorites-section {
      margin-top: 40px;
      padding-top: 30px;
      border-top: 1px solid #eee;
    }
    
    .favorites-header {
      margin-bottom: 20px;
      text-align: center;
    }
    
    /* For loading state */
    .loading {
      text-align: center;
      padding: 30px;
    }
    
    @media (max-width: 768px) {
      .gallery-grid {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
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
  <div class="gallery-container">
    <div class="gallery-header">
      <h2>Our Adoptable Pets</h2>
      <p>Find your perfect furry, feathered, or scaly friend!</p>
    </div>
    
    <?php if (isset($error_message)): ?>
      <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <?php if (empty($pets)): ?>
      <div class="no-results">
        <h3>No pets found</h3>
        <p>Please check back later for new additions.</p>
      </div>
    <?php else: ?>
      <div class="gallery-grid">
        <?php foreach ($pets as $pet): ?>
          <div class="pet-card">
            <img src="<?php echo htmlspecialchars($pet['image_url']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>" class="pet-image">
            <div class="pet-info">
              <h3><?php echo htmlspecialchars($pet['name']); ?></h3>
              <div class="pet-breed"><?php echo htmlspecialchars($pet['breed']); ?></div>
              <div class="pet-age"><?php echo htmlspecialchars($pet['age']); ?> years old</div>
              <div class="pet-status <?php echo htmlspecialchars($pet['status']); ?>"><?php echo htmlspecialchars($pet['status']); ?></div>
              <div class="pet-description"><?php echo htmlspecialchars($pet['description']); ?></div>
              <div class="pet-buttons">
                <a href="pet_details.php?id=<?php echo $pet['pet_id']; ?>" class="pet-button details-button">View Details</a>
                
                <form method="POST" action="toggle_favorite.php" style="flex: 1;">
                  <input type="hidden" name="pet_id" value="<?php echo $pet['pet_id']; ?>">
                  <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                  <button type="submit" class="pet-button favorite-button <?php echo isset($favorites[$pet['pet_id']]) ? 'active' : ''; ?>">
                    <?php echo isset($favorites[$pet['pet_id']]) ? '♥' : '♡'; ?>
                  </button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['user_id']) && !empty($favorites)): ?>
      <div class="favorites-section" id="favorites-section">
        <div class="favorites-header">
          <h2>Your Favorite Pets</h2>
          <p>Quick access to pets you've saved</p>
        </div>
        
        <div class="gallery-grid">
          <?php
            $favoriteIds = array_keys($favorites);
            $placeholders = implode(',', array_fill(0, count($favoriteIds), '?'));
            
            $stmt = $pdo->prepare("SELECT * FROM pets WHERE pet_id IN ($placeholders) ORDER BY name ASC");
            $stmt->execute($favoriteIds);
            $favoritePets = $stmt->fetchAll();
            
            foreach ($favoritePets as $pet):
          ?>
            <div class="pet-card">
              <img src="<?php echo htmlspecialchars($pet['image_url']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>" class="pet-image">
              <div class="pet-info">
                <h3><?php echo htmlspecialchars($pet['name']); ?></h3>
                <div class="pet-breed"><?php echo htmlspecialchars($pet['breed']); ?></div>
                <div class="pet-age"><?php echo htmlspecialchars($pet['age']); ?> years old</div>
                <div class="pet-status <?php echo htmlspecialchars($pet['status']); ?>"><?php echo htmlspecialchars($pet['status']); ?></div>
                <div class="pet-description"><?php echo htmlspecialchars($pet['description']); ?></div>
                <div class="pet-buttons">
                  <a href="pet_details.php?id=<?php echo $pet['pet_id']; ?>" class="pet-button details-button">View Details</a>
                  
                  <form method="POST" action="toggle_favorite.php" style="flex: 1;">
                    <input type="hidden" name="pet_id" value="<?php echo $pet['pet_id']; ?>">
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                    <button type="submit" class="pet-button favorite-button active">♥</button>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
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