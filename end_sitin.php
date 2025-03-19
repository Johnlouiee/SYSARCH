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