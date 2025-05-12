<?php
session_start();
include 'db_connect.php';
require_once 'computer_control.php';
require_once 'reservation_logs.php';

if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $computerControl = new ComputerControl();
    $reservationLog = new ReservationLog($conn);

    // Start transaction
    $conn->begin_transaction();

    try {
        // 1. Fetch reservation details
        $fetch_sql = "SELECT r.*, u.idno as student_id 
                     FROM reservations r 
                     JOIN users u ON r.user_id = u.idno 
                     WHERE r.id = ? AND r.status = 'Accepted'";
        $fetch_stmt = $conn->prepare($fetch_sql);
        $fetch_stmt->bind_param("i", $id);
        $fetch_stmt->execute();
        $fetch_result = $fetch_stmt->get_result();
        $reservation = $fetch_result->fetch_assoc();

        if ($reservation && !empty($reservation['lab']) && !empty($reservation['pc_number'])) {
            $lab = trim($reservation['lab']);
            $pc_number = trim($reservation['pc_number']);
            
            // Standardize PC number format
            $pc_number = preg_replace('/^PC-?/i', '', $pc_number);
            if (!is_numeric($pc_number) || $pc_number < 1 || $pc_number > 50) {
                throw new Exception("Invalid PC number: {$pc_number}");
            }

            // Format PC number with PC- prefix
            $formatted_pc_number = "PC-" . $pc_number;

            // 2. Update computer control status to 'reserved'
            $result = $computerControl->updatePCStatus($lab, $formatted_pc_number, 'reserved');

            if (!$result) {
                error_log("Failed to update PC status in enable_reservation.php: Lab={$lab}, PC={$formatted_pc_number}");
                throw new Exception("Failed to update PC status. Please try again.");
            }

            // 3. Update PC management status
            $pc_management_sql = "UPDATE pc_management 
                                SET status = 'reserved'
                                WHERE lab_name = ? AND pc_number = ? AND reservation_id = ?";
            $pc_management_stmt = $conn->prepare($pc_management_sql);
            $pc_management_stmt->bind_param("ssi", 
                $lab,
                $formatted_pc_number,
                $id
            );

            if (!$pc_management_stmt->execute()) {
                error_log("Failed to update PC management status: " . $pc_management_stmt->error);
                throw new Exception("Failed to update PC management status");
            }

            // 4. Log the enabling
            $reservationLog->logReservationEnabled(
                $id,
                $_SESSION['user_info']['idno'],
                $reservation['student_name'],
                $lab,
                $formatted_pc_number
            );

            // Commit transaction
            $conn->commit();
            header("Location: view_reservation.php?success=Reservation+enabled+successfully");
            exit();
        } else {
            throw new Exception("Invalid or non-accepted reservation");
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error in enable_reservation.php: " . $e->getMessage());
        header("Location: view_reservation.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: view_reservation.php");
    exit();
}
$conn->close();
?> 