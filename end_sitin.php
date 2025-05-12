<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Check if sit_in_id is provided via POST or GET
if ((!isset($_POST['sit_in_id']) && !isset($_GET['sit_in_id'])) || 
    (isset($_POST['sit_in_id']) && empty(trim($_POST['sit_in_id'])) && 
     isset($_GET['sit_in_id']) && empty(trim($_GET['sit_in_id'])))) {
    header("Location: view_current_sitin.php?error=Invalid%20request");
    exit();
}

// Get sit_in_id from either POST or GET
$sit_in_id = isset($_POST['sit_in_id']) ? 
             mysqli_real_escape_string($conn, $_POST['sit_in_id']) : 
             mysqli_real_escape_string($conn, $_GET['sit_in_id']);

// Check if the sit-in session exists and is active
$check_sql = "SELECT user_id, session_start FROM sit_in_history WHERE id = ? AND session_end IS NULL";
$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    header("Location: view_current_sitin.php?error=Database%20error:%20" . urlencode($conn->error));
    exit();
}

$check_stmt->bind_param("i", $sit_in_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $row = $check_result->fetch_assoc();
    $user_id = $row['user_id'];
    $session_start = $row['session_start'];
    
    // End the active session
    $update_sql = "UPDATE sit_in_history SET session_end = NOW() WHERE id = ? AND session_end IS NULL";
    $update_stmt = $conn->prepare($update_sql);

    if (!$update_stmt) {
        header("Location: view_current_sitin.php?error=Database%20error:%20" . urlencode($conn->error));
        $check_stmt->close();
        $conn->close();
        exit();
    }

    $update_stmt->bind_param("i", $sit_in_id);
    if ($update_stmt->execute()) {
        // Calculate points based on session duration (1 point per hour)
        $start = new DateTime($session_start);
        $end = new DateTime();
        $duration = $start->diff($end);
        $hours = $duration->h + ($duration->days * 24);
        $points_earned = $hours; // 1 point per hour
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Check if user has existing points
            $check_points_sql = "SELECT points FROM user_points WHERE user_id = ?";
            $check_points_stmt = $conn->prepare($check_points_sql);
            $check_points_stmt->bind_param("s", $user_id);
            $check_points_stmt->execute();
            $points_result = $check_points_stmt->get_result();
            
            if ($points_result->num_rows > 0) {
                // Update existing points
                $update_points_sql = "UPDATE user_points SET points = points + ?, last_updated = NOW() WHERE user_id = ?";
                $update_points_stmt = $conn->prepare($update_points_sql);
                $update_points_stmt->bind_param("is", $points_earned, $user_id);
                $update_points_stmt->execute();
                
                // Get updated points
                $get_points_sql = "SELECT points FROM user_points WHERE user_id = ?";
                $get_points_stmt = $conn->prepare($get_points_sql);
                $get_points_stmt->bind_param("s", $user_id);
                $get_points_stmt->execute();
                $current_points_result = $get_points_stmt->get_result();
                $current_points_row = $current_points_result->fetch_assoc();
                $current_points = $current_points_row['points'];
                
                // Check if points are divisible by 3 to award a free session
                if ($current_points >= 3) {
                    $sessions_to_award = floor($current_points / 3);
                    $remaining_points = $current_points % 3;
                    
                    // Update user's sessions
                    $update_sessions_sql = "UPDATE users SET sessions_remaining = sessions_remaining + ? WHERE idno = ?";
                    $update_sessions_stmt = $conn->prepare($update_sessions_sql);
                    $update_sessions_stmt->bind_param("is", $sessions_to_award, $user_id);
                    $update_sessions_stmt->execute();
                    
                    // Update remaining points
                    $update_remaining_sql = "UPDATE user_points SET points = ? WHERE user_id = ?";
                    $update_remaining_stmt = $conn->prepare($update_remaining_sql);
                    $update_remaining_stmt->bind_param("is", $remaining_points, $user_id);
                    $update_remaining_stmt->execute();
                }
            } else {
                // Insert new points record
                $insert_points_sql = "INSERT INTO user_points (user_id, points, last_updated) VALUES (?, ?, NOW())";
                $insert_points_stmt = $conn->prepare($insert_points_sql);
                $insert_points_stmt->bind_param("si", $user_id, $points_earned);
                $insert_points_stmt->execute();
            }
            
            // Commit transaction
            $conn->commit();
            
            header("Location: view_current_sitin.php?success=Session%20ended%20successfully");
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            header("Location: view_current_sitin.php?error=Failed%20to%20update%20points:%20" . urlencode($e->getMessage()));
        }
    } else {
        header("Location: view_current_sitin.php?error=Failed%20to%20end%20session");
    }
    $update_stmt->close();
} else {
    header("Location: view_current_sitin.php?error=No%20active%20session%20found");
}

$check_stmt->close();
$conn->close();
?>