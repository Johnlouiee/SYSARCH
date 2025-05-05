<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // First, get the reservation details including lab and pc_number
    $reservation_sql = "SELECT lab, pc_number FROM reservations WHERE id = ?";
    $reservation_stmt = $conn->prepare($reservation_sql);
    $reservation_stmt->bind_param("i", $id);
    $reservation_stmt->execute();
    $reservation = $reservation_stmt->get_result()->fetch_assoc();
    $reservation_stmt->close();

    if (!$reservation || !isset($reservation['lab']) || !isset($reservation['pc_number'])) {
        header("Location: view_reservation.php?error=Invalid+reservation+or+missing+lab/pc+information");
        exit();
    }

    $lab = $reservation['lab'];
    $pc_number = $reservation['pc_number'];

    // Update PC availability status
    $pcUpdateSql = "INSERT INTO pc_availability (lab_name, pc_number, is_available, reservation_id, reserved_from, reserved_to) 
                   VALUES (?, ?, 0, ?, NOW(), DATE_ADD(NOW(), INTERVAL 2 HOUR))
                   ON DUPLICATE KEY UPDATE 
                   is_available = 0, 
                   reservation_id = VALUES(reservation_id),
                   reserved_from = VALUES(reserved_from),
                   reserved_to = VALUES(reserved_to)";
    
    $pcStmt = $conn->prepare($pcUpdateSql);
    $pcStmt->bind_param("ssi", $lab, $pc_number, $id);

    // Update computer control status
    $controlUpdateSql = "UPDATE computer_control 
                        SET status = 'reserved', 
                            reservation_id = ?,
                            last_update = NOW()
                        WHERE lab_name = ? 
                        AND pc_number = ?";
    $controlStmt = $conn->prepare($controlUpdateSql);
    $controlStmt->bind_param("iss", $id, $lab, $pc_number);
    if (!$pcStmt->execute()) {
        error_log("Failed to update PC availability: " . $pcStmt->error);
        header("Location: view_reservation.php?error=Failed+to+update+PC+status");
        exit();
    }
    $pcStmt->close();

    // Then update the reservation status
    $sql = "UPDATE reservations SET status = 'Accepted' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Update computer_control table
        $computerControlSql = "INSERT INTO computer_control 
                              (lab_name, pc_number, status, reservation_id, last_update) 
                              VALUES (?, ?, 'reserved', ?, NOW())
                              ON DUPLICATE KEY UPDATE 
                              status = VALUES(status), 
                              reservation_id = VALUES(reservation_id),
                              last_update = NOW()";
        
        $ccStmt = $conn->prepare($computerControlSql);
        $ccStmt->bind_param("ssi", $lab, $pc_number, $id);
        if (!$ccStmt->execute()) {
            error_log("Failed to update computer_control: " . $ccStmt->error);
        }
        $ccStmt->close();
        
        // Update PC availability
        $pcUpdateSql = "INSERT INTO pc_availability 
                       (lab_name, pc_number, is_available, reservation_id, reserved_from, reserved_to) 
                       VALUES (?, ?, 0, ?, NOW(), DATE_ADD(NOW(), INTERVAL 2 HOUR))
                       ON DUPLICATE KEY UPDATE 
                       is_available = 0, 
                       reservation_id = VALUES(reservation_id),
                       reserved_from = VALUES(reserved_from),
                       reserved_to = VALUES(reserved_to)";
        
        $pcStmt = $conn->prepare($pcUpdateSql);
        $pcStmt->bind_param("ssi", $lab, $pc_number, $id);
        if (!$pcStmt->execute()) {
            error_log("Failed to update PC availability: " . $pcStmt->error);
        }
        $pcStmt->close();
        
        header("Location: view_reservation.php?success=Reservation+accepted");
    } else {
        header("Location: view_reservation.php?error=Error+accepting+reservation");
    }
    $stmt->close();
} else {
    header("Location: view_reservation.php");
}
$conn->close();
?>