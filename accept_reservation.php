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
                     WHERE r.id = ? AND r.status = 'Pending'";
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

            // 2. Update reservation status to 'Accepted'
            $update_sql = "UPDATE reservations SET status = 'Accepted' WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $id);
            
            if (!$update_stmt->execute()) {
                throw new Exception("Failed to update reservation status");
            }

            // 3. Update computer control status to 'reserved'
            // First, ensure the PC exists in computer_control
            $check_sql = "SELECT id FROM computer_control WHERE lab_name = ? AND pc_number = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ss", $lab, $formatted_pc_number);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows === 0) {
                // Insert new record if PC doesn't exist
                $insert_sql = "INSERT INTO computer_control (lab_name, pc_number, status, reservation_id, last_update) 
                              VALUES (?, ?, 'reserved', ?, NOW())";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("ssi", $lab, $formatted_pc_number, $id);
                if (!$insert_stmt->execute()) {
                    throw new Exception("Failed to create PC control record");
                }
            } else {
                // Update existing record
                $update_control_sql = "UPDATE computer_control 
                                     SET status = 'reserved', 
                                         reservation_id = ?,
                                         last_update = NOW()
                                     WHERE lab_name = ? AND pc_number = ?";
                $update_control_stmt = $conn->prepare($update_control_sql);
                $update_control_stmt->bind_param("iss", $id, $lab, $formatted_pc_number);
                if (!$update_control_stmt->execute()) {
                    throw new Exception("Failed to update PC control status");
                }
            }

            // 4. Update PC management status
            $pc_management_sql = "UPDATE pc_management 
                                SET status = 'reserved', 
                                    reservation_id = ?
                                WHERE lab_name = ? AND pc_number = ?";
            $pc_management_stmt = $conn->prepare($pc_management_sql);
            $pc_management_stmt->bind_param("iss", 
                $id,
                $lab,
                $formatted_pc_number
            );

            if (!$pc_management_stmt->execute()) {
                // If no rows were updated, insert new record
                $insert_management_sql = "INSERT INTO pc_management 
                                        (lab_name, pc_number, status, reservation_id)
                                        VALUES (?, ?, 'reserved', ?)";
                $insert_management_stmt = $conn->prepare($insert_management_sql);
                $insert_management_stmt->bind_param("ssi", $lab, $formatted_pc_number, $id);
                if (!$insert_management_stmt->execute()) {
                throw new Exception("Failed to update PC management status");
                }
            }

            // 5. Record in sit-in history
            $sit_in_sql = "INSERT INTO sit_in_history 
                          (user_id, purpose, lab, session_start, status)
                          VALUES (?, ?, ?, ?, 'pending')";
            $sit_in_stmt = $conn->prepare($sit_in_sql);
            if (!$sit_in_stmt) {
                throw new Exception("Failed to prepare sit-in history statement: " . $conn->error);
            }
            $session_start = $reservation['time_in'];
            $sit_in_stmt->bind_param("ssss", 
                $reservation['student_id'],
                $reservation['purpose'],
                $lab,
                $session_start
            );

            if (!$sit_in_stmt->execute()) {
                error_log("Failed to record sit-in history: " . $sit_in_stmt->error);
                throw new Exception("Failed to record sit-in history");
            }

            // 6. Log the acceptance
            $reservationLog->logAcceptedReservation(
                $id,
                $_SESSION['user_info']['idno'],
                $reservation['student_name'],
                $lab,
                $formatted_pc_number,
                $reservation['reservation_date'],
                $reservation['time_in']
            );

            // 7. Create notification for student
            $notification_sql = "INSERT INTO notifications 
                               (user_id, message, type, created_at)
                               VALUES (?, ?, 'reservation_accepted', NOW())";
            $notification_stmt = $conn->prepare($notification_sql);
            $message = "Your reservation for {$lab} - {$formatted_pc_number} has been accepted.";
            $notification_stmt->bind_param("ss", $reservation['student_id'], $message);
            $notification_stmt->execute();

            // Commit transaction
            $conn->commit();
            header("Location: view_reservation.php?success=Reservation+accepted+successfully");
            exit();
        } else {
            throw new Exception("Invalid or non-pending reservation");
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error in accept_reservation.php: " . $e->getMessage());
        header("Location: view_reservation.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: view_reservation.php");
    exit();
}
$conn->close();
?>