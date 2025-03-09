<?php
session_start();
include 'db_connect.php';

if (isset($_SESSION['user_info'])) {
    $userid = $_SESSION['user_info']['id']; // Assuming 'id' is the user ID in the users table

    // Update the session_end timestamp for the active session
    $update_session_sql = "UPDATE user_sessions SET session_end = NOW() WHERE user_id = ? AND session_end IS NULL ORDER BY session_start DESC LIMIT 1";
    $update_stmt = $conn->prepare($update_session_sql);
    if ($update_stmt) {
        $update_stmt->bind_param("s", $userid);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        die("Error preparing statement: " . $conn->error);
    }
}

// Destroy the session
session_destroy();
header("Location: index.php");
exit();
?>