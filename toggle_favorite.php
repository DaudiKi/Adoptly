<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page with return URL
    header("Location: login.php?redirect=" . urlencode($_POST['redirect'] ?? 'AdoptlyGallery.php'));
    exit();
}

// Check if pet_id is provided
if (!isset($_POST['pet_id']) || !is_numeric($_POST['pet_id'])) {
    header("Location: AdoptlyGallery.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$pet_id = (int) $_POST['pet_id'];
$redirect = $_POST['redirect'] ?? 'AdoptlyGallery.php';

try {
    // Check if the pet exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pets WHERE pet_id = ?");
    $stmt->execute([$pet_id]);
    if ($stmt->fetchColumn() == 0) {
        // Pet doesn't exist
        header("Location: " . $redirect);
        exit();
    }
    
    // Check if this pet is already a favorite for the user
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND pet_id = ?");
    $stmt->execute([$user_id, $pet_id]);
    $exists = $stmt->fetchColumn() > 0;
    
    if ($exists) {
        // Remove from favorites
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND pet_id = ?");
        $stmt->execute([$user_id, $pet_id]);
    } else {
        // Add to favorites
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, pet_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $pet_id]);
    }
    
    // Redirect back to the original page
    header("Location: " . $redirect);
    exit();
    
} catch (PDOException $e) {
    // Log error
    error_log("Error toggling favorite: " . $e->getMessage());
    
    // Redirect with error
    header("Location: " . $redirect . "?error=database");
    exit();
}
?>