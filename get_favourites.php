<?php
session_start();
require_once 'db_connect.php';

// Return JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to view favorites.'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Get user's favorites
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
        'favorites' => $favorites
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>