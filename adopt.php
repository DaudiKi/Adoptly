<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if pet ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: AdoptlyGallery.php");
    exit();
}

$pet_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Check if pet exists and is available
    $stmt = $pdo->prepare("SELECT * FROM pets WHERE pet_id = ? AND status = 'available'");
    $stmt->execute([$pet_id]);
    $pet = $stmt->fetch();
    
    if (!$pet) {
        // Pet not available
        $pdo->rollBack();
        header("Location: pet_details.php?id=$pet_id&adoption=unavailable");
        exit();
    }
    
    // Check if user already has a pending adoption request for this pet
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM adoptions WHERE user_id = ? AND pet_id = ? AND status = 'pending'");
    $stmt->execute([$user_id, $pet_id]);
    
    if ($stmt->fetchColumn() > 0) {
        // Adoption request already exists
        $pdo->rollBack();
        header("Location: pet_details.php?id=$pet_id&adoption=duplicate");
        exit();
    }
    
    // Record the adoption request
    $stmt = $pdo->prepare("INSERT INTO adoptions (user_id, pet_id, status, notes) VALUES (?, ?, 'pending', ?)");
    $notes = "Adoption request submitted via website on " . date("Y-m-d H:i:s");
    $stmt->execute([$user_id, $pet_id, $notes]);
    
    // Update pet status to pending
    $stmt = $pdo->prepare("UPDATE pets SET status = 'pending' WHERE pet_id = ?");
    $stmt->execute([$pet_id]);
    
    // Commit transaction
    $pdo->commit();
    
    // Redirect back to pet details with success message
    header("Location: pet_details.php?id=$pet_id&adoption=success");
    
} catch (PDOException $e) {
    // Rollback the transaction if there was an error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log the error
    error_log("Adoption error: " . $e->getMessage());
    
    // Redirect with error
    header("Location: pet_details.php?id=$pet_id&adoption=error");
}
exit();
?>