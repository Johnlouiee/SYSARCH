<?php
session_start();
include 'db_connect.php';
require_once 'reservation_logs.php';

if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $reservationLog = new ReservationLog($conn);

    // Start transaction
    $conn->begin_transaction();

    try {
        // 1. Fetch reservation details first
        $fetch_sql = "SELECT r.*, u.idno as student_id 
                     FROM reservations r 
                     JOIN users u ON r.user_id = u.idno 
                     WHERE r.id = ?";
        $fetch_stmt = $conn->prepare($fetch_sql);
        $fetch_stmt->bind_param("i", $id);
        $fetch_stmt->execute();
        $fetch_result = $fetch_stmt->get_result();
        $reservation = $fetch_result->fetch_assoc();

        if ($reservation) {
            // 2. Update reservation status to Declined
            $sql = "UPDATE reservations SET status = 'Declined' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                // 3. Log the decline
                $reservationLog->logDeclinedReservation(
                    $id,
                    $_SESSION['user_info']['idno'],
                    $reservation['student_name'],
                    $reservation['lab'],
                    $reservation['pc_number'],
                    $reservation['reservation_date'],
                    $reservation['time_in']
                );

                // 4. Create notification for student
                $notification_sql = "INSERT INTO notifications (user_id, message, type, created_at) 
                                   VALUES (?, ?, 'decline', NOW())";
                $notification_stmt = $conn->prepare($notification_sql);
                $message = "Your reservation for Lab {$reservation['lab']}, PC {$reservation['pc_number']} on {$reservation['reservation_date']} at {$reservation['time_in']} has been declined.";
                $notification_stmt->bind_param("ss", $reservation['student_id'], $message);
                $notification_stmt->execute();

                // Commit transaction
                $conn->commit();
                header("Location: view_reservation.php?success=Reservation+declined+successfully");
                exit();
            } else {
                throw new Exception("Failed to update reservation status");
            }
        } else {
            throw new Exception("Reservation not found");
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error in decline_reservation.php: " . $e->getMessage());
        header("Location: view_reservation.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: view_reservation.php");
    exit();
}
$conn->close();
?>