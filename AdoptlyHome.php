<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Adoptly - Home</title>
  <meta name="description" content="Adoptly is a pet adoption platform connecting loving families with adorable pets.">
  <meta name="keywords" content="Adoption, pets, pet adoption, rescue, adopt, gallery, signup, pet matcher, about us">
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
  <!-- Hero Section -->
  <section class="hero" aria-label="Welcome Section">
    <h2>Welcome to Adoptly</h2>
    <p>Your journey to finding the perfect pet starts here!</p>
    <button onclick="document.getElementById('about').scrollIntoView({ behavior: 'smooth' });">
      Get Started
    </button>
  </section>

  <!-- About Section -->
  <section id="about" class="about" aria-label="About Us">
    <h2>About Adoptly</h2>
    <p>At Adoptly, we believe every pet deserves a loving home. Our mission is to connect caring families with adorable pets looking for their forever homes. Explore our services, meet our featured pets, and join a community that cares!</p>
  </section>

  <!-- Featured Pets Section -->
  <section id="featured" class="featured" aria-label="Featured Pets">
    <h2>Featured Pets</h2>
    <div class="featured-grid">
      <div class="featured-item">
        <div class="pet-card">
          <img src="https://images.unsplash.com/photo-1633722715463-d30f4f325e24?q=80&w=2000&auto=format&fit=crop" alt="Buddy - Golden Retriever">
          <div class="pet-info">
            <h3>Buddy</h3>
            <p class="pet-breed">Golden Retriever</p>
            <p class="pet-age">2 years old</p>
            <p class="pet-description">A friendly companion waiting for you.</p>
            <button class="pet-details-btn" onclick="location.href='AdoptlyGallery.php'">View Details</button>
          </div>
        </div>
      </div>
      <div class="featured-item">
        <div class="pet-card">
          <img src="https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?q=80&w=2000&auto=format&fit=crop" alt="Luna - Siamese Cat">
          <div class="pet-info">
            <h3>Luna</h3>
            <p class="pet-breed">Siamese Cat</p>
            <p class="pet-age">1 year old</p>
            <p class="pet-description">An energetic spirit ready for adventure.</p>
            <button class="pet-details-btn" onclick="location.href='AdoptlyGallery.php'">View Details</button>
          </div>
        </div>
      </div>
      <div class="featured-item">
        <div class="pet-card">
          <img src="https://images.unsplash.com/photo-1586671267731-da2cf3ceeb80?q=80&w=2000&auto=format&fit=crop" alt="Charlie - Beagle">
          <div class="pet-info">
            <h3>Charlie</h3>
            <p class="pet-breed">Beagle</p>
            <p class="pet-age">2 years old</p>
            <p class="pet-description">Looking for a loving family to call home.</p>
            <button class="pet-details-btn" onclick="location.href='AdoptlyGallery.php'">View Details</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Removed Search Section -->
  
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