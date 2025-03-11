<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_info'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['user_info']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sit_in_id'])) {
    $sit_in_id = $_POST['sit_in_id'];
    
    // Update the sit-in session end time
    $sql = "UPDATE sit_in_history 
            SET session_end = NOW(), 
                date_time = NOW() 
            WHERE id = ? AND session_end IS NULL";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $sit_in_id);
        
        if ($stmt->execute()) {
            // Successfully ended the sit-in session
            $_SESSION['success_message'] = "Sit-in session ended successfully.";
        } else {
            $_SESSION['error_message'] = "Error ending sit-in session.";
        }
        
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Database error: " . $conn->error;
    }
}

// Redirect back to the current sit-in page
header("Location: view_current_sitin.php");
exit();
?> 