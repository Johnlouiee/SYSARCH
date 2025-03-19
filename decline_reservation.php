<?php
session_start();

if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

if (isset($_GET['id'])) {
    $reservation_id = (int)$_GET['id'];

    // Check if reservation exists and is pending
    $sql = "SELECT * FROM reservations WHERE id = ? AND status = 'Pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reservation = $result->fetch_assoc();
    $stmt->close();

    if ($reservation) {
        // Update reservation status to "Declined"
        $update_sql = "UPDATE reservations SET status = 'Declined' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $reservation_id);
        if ($update_stmt->execute()) {
            header("Location: view_reservation.php?success=Reservation declined successfully");
        } else {
            header("Location: view_reservation.php?error=Failed to decline reservation");
        }
        $update_stmt->close();
    } else {
        header("Location: view_reservation.php?error=Reservation not found or already processed");
    }
} else {
    header("Location: view_reservation.php?error=Invalid reservation ID");
}

$conn->close();
?>