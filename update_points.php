<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $session_start = $_POST['session_start'];
    $session_end = $_POST['session_end'];
    
    // Calculate points based on session duration (1 point per hour)
    $start = new DateTime($session_start);
    $end = new DateTime($session_end);
    $duration = $start->diff($end);
    $hours = $duration->h + ($duration->days * 24);
    $points_earned = $hours; // 1 point per hour
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Check if user has existing points
        $check_query = "SELECT points FROM user_points WHERE user_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing points
            $update_query = "UPDATE user_points SET points = points + ?, last_updated = NOW() WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ii", $points_earned, $user_id);
            $update_stmt->execute();
        } else {
            // Insert new points record
            $insert_query = "INSERT INTO user_points (user_id, points, last_updated) VALUES (?, ?, NOW())";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("ii", $user_id, $points_earned);
            $insert_stmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        // Return success response
        echo json_encode(['success' => true, 'points_earned' => $points_earned]);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

$conn->close();
?> 