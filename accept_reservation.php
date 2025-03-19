<?php
session_start();

if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

if (isset($_GET['id'])) {
    $reservation_id = (int)$_GET['id'];

    // Fetch reservation details
    $sql = "SELECT user_id FROM reservations WHERE id = ? AND status = 'Pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reservation = $result->fetch_assoc();
    $stmt->close();

    if ($reservation) {
        // Update reservation status to "Accepted"
        $update_sql = "UPDATE reservations SET status = 'Accepted' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $reservation_id);
        $update_success = $update_stmt->execute();
        $update_stmt->close();

        if ($update_success) {
            // Decrement the user's session count
            $decrement_sql = "UPDATE users SET sessions_remaining = sessions_remaining - 1 WHERE idno = ?";
            $decrement_stmt = $conn->prepare($decrement_sql);
            $decrement_stmt->bind_param("s", $reservation['user_id']);
            $decrement_stmt->execute();
            $decrement_stmt->close();

            header("Location: view_reservation.php?success=Reservation accepted successfully");
        } else {
            header("Location: view_reservation.php?error=Failed to accept reservation");
        }
    } else {
        header("Location: view_reservation.php?error=Reservation not found or already processed");
    }
} else {
    header("Location: view_reservation.php?error=Invalid reservation ID");
}

$conn->close();
?>