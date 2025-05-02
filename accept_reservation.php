<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // First get the reservation details
    $sql = "SELECT * FROM reservations WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reservation = $result->fetch_assoc();
    $stmt->close();
    
    if ($reservation) {
        // Check if PC is already reserved for this date
        $check_sql = "SELECT id FROM reservations 
                      WHERE lab = ? AND pc_number = ? AND reservation_date = ? 
                      AND status = 'Accepted' AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("sssi", $reservation['lab'], $reservation['pc_number'], $reservation['reservation_date'], $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            header("Location: view_reservation.php?error=PC+already+reserved+for+this+date");
            exit();
        }
        
        // Update reservation status
        $update_sql = "UPDATE reservations SET status = 'Accepted' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $id);
        
        if ($update_stmt->execute()) {
            header("Location: view_reservation.php?success=Reservation+accepted");
        } else {
            header("Location: view_reservation.php?error=Error+accepting+reservation");
        }
        $update_stmt->close();
    } else {
        header("Location: view_reservation.php?error=Reservation+not+found");
    }
} else {
    header("Location: view_reservation.php");
}
$conn->close();
?>