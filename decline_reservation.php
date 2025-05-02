<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Update reservation status
    $sql = "UPDATE reservations SET status = 'Declined' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: view_reservation.php?success=Reservation+declined");
    } else {
        header("Location: view_reservation.php?error=Error+declining+reservation");
    }
    $stmt->close();
} else {
    header("Location: view_reservation.php");
}
$conn->close();
?>