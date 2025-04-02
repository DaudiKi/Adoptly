<?php
session_start();
require_once 'db_connect.php';

// Return JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to add favorites.'
    ]);
    exit();
}

// Check for required parameters
if (!isset($_POST['pet_id']) || !is_numeric($_POST['pet_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid pet ID.'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];
$pet_id = (int) $_POST['pet_id'];

try {
    // Check if the pet exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pets WHERE pet_id = ?");
    $stmt->execute([$pet_id]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Pet not found.'
        ]);
        exit();
    }
    
    // Check if this pet is already a favorite
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND pet_id = ?");
    $stmt->execute([$user_id, $pet_id]);
    $exists = $stmt->fetchColumn() > 0;
    
    if ($exists) {
        // Remove from favorites
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND pet_id = ?");
        $stmt->execute([$user_id, $pet_id]);
        $action = 'removed';
    } else {
        // Add to favorites
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, pet_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $pet_id]);
        $action = 'added';
    }
    
    // Get updated favorites list
    $stmt = $pdo->prepare("
        SELECT p.* 
        FROM pets p
        JOIN favorites f ON p.pet_id = f.pet_id
        WHERE f.user_id = ?
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $favorites = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'action' => $action,
        'message' => $action === 'added' ? 'Pet added to favorites.' : 'Pet removed from favorites.',
        'favorites' => $favorites
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>