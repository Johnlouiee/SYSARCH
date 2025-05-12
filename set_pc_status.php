<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
    header('HTTP/1.1 204 No Content');
    exit;
}

// Check if required parameters are present
if (!isset($_POST['lab']) || !isset($_POST['status'])) {
    header('HTTP/1.1 204 No Content');
    exit;
}

$lab = $_POST['lab'];
$status = $_POST['status'];
$pcNumber = isset($_POST['pc_number']) ? $_POST['pc_number'] : null;

// Validate status
$valid_statuses = ['available', 'offline', 'maintenance'];
if (!in_array($status, $valid_statuses)) {
    header('HTTP/1.1 204 No Content');
    exit;
}

try {
    // Begin transaction
    $conn->begin_transaction();

    if ($pcNumber) {
        // Update single PC
        $update_sql = "UPDATE computer_control 
                      SET status = ?, 
                          last_update = NOW()
                      WHERE lab_name = ? AND pc_number = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sss", $status, $lab, $pcNumber);
        $result = $update_stmt->execute();

        if ($result) {
            // Update pc_availability for single PC
            $is_available = ($status === 'available') ? 1 : 0;
            $avail_sql = "UPDATE pc_availability 
                         SET is_available = ?
                         WHERE lab_name = ? AND pc_number = ?";
            $avail_stmt = $conn->prepare($avail_sql);
            $avail_stmt->bind_param("iss", $is_available, $lab, $pcNumber);
            $avail_stmt->execute();
        }
    } else {
        // Update all PCs in the lab
        $update_sql = "UPDATE computer_control 
                      SET status = ?, 
                          last_update = NOW()
                      WHERE lab_name = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ss", $status, $lab);
        $result = $update_stmt->execute();

        if ($result) {
            // Update pc_availability for all PCs
            $is_available = ($status === 'available') ? 1 : 0;
            $avail_sql = "UPDATE pc_availability 
                         SET is_available = ?
                         WHERE lab_name = ?";
            $avail_stmt = $conn->prepare($avail_sql);
            $avail_stmt->bind_param("is", $is_available, $lab);
            $avail_stmt->execute();
        }
    }

    $conn->commit();
    header('HTTP/1.1 204 No Content');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    header('HTTP/1.1 204 No Content');
    exit;
}
?> 