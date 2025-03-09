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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sit_in_id = $_POST['sit_in_id'];

    // Update session_end to mark the sit-in as ended
    $sql = "UPDATE sit_in_history SET session_end = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $sit_in_id);
        if ($stmt->execute()) {
            header("Location: view_current_sitin.php?success=1");
            exit();
        } else {
            header("Location: view_current_sitin.php?error=1");
            exit();
        }
    } else {
        header("Location: view_current_sitin.php?error=1");
        exit();
    }
}
?>