<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Check if sit_in_id is provided via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['sit_in_id']) || empty(trim($_POST['sit_in_id']))) {
    header("Location: view_current_sitin.php?error=Invalid%20request");
    exit();
}

$sit_in_id = mysqli_real_escape_string($conn, $_POST['sit_in_id']);

// Check if the sit-in session exists and is active
$check_sql = "SELECT user_id FROM sit_in_history WHERE id = ? AND session_end IS NULL";
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
        // Award points (1 point per session)
        $points = 1;
        
        // Update user_points table
        $points_sql = "INSERT INTO user_points (user_id, points) 
                      VALUES (?, ?) 
                      ON DUPLICATE KEY UPDATE 
                      points = points + VALUES(points)";
        $points_stmt = $conn->prepare($points_sql);
        $points_stmt->bind_param("si", $user_id, $points);
        $points_stmt->execute();
        
        // Update total_points in users table
        $update_points_sql = "UPDATE users SET total_points = total_points + ? WHERE idno = ?";
        $update_points_stmt = $conn->prepare($update_points_sql);
        $update_points_stmt->bind_param("is", $points, $user_id);
        $update_points_stmt->execute();

        // Check if user has accumulated 3 points to earn a free session
        $check_points_sql = "SELECT points FROM user_points WHERE user_id = ?";
        $check_points_stmt = $conn->prepare($check_points_sql);
        $check_points_stmt->bind_param("s", $user_id);
        $check_points_stmt->execute();
        $points_result = $check_points_stmt->get_result();
        
        if ($points_result->num_rows > 0) {
            $points_row = $points_result->fetch_assoc();
            $current_points = $points_row['points'];
            
            // If points are divisible by 3, award a free session
            if ($current_points % 3 == 0) {
                $update_sessions_sql = "UPDATE users SET sessions_remaining = sessions_remaining + 1 WHERE idno = ?";
                $update_sessions_stmt = $conn->prepare($update_sessions_sql);
                $update_sessions_stmt->bind_param("s", $user_id);
                $update_sessions_stmt->execute();
                
                // Reset points after awarding free session
                $reset_points_sql = "UPDATE user_points SET points = 0 WHERE user_id = ?";
                $reset_points_stmt = $conn->prepare($reset_points_sql);
                $reset_points_stmt->bind_param("s", $user_id);
                $reset_points_stmt->execute();
            }
        }
        
        header("Location: view_current_sitin.php?success=Session%20ended%20successfully");
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