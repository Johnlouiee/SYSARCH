<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Check if required parameters are present
if (!isset($_POST['lab']) || !isset($_POST['lab_status'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required parameters'
    ]);
    exit;
}

$lab = $_POST['lab'];
$status = $_POST['lab_status'];

// Validate status
$valid_statuses = ['available', 'offline', 'maintenance'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid status'
    ]);
    exit;
}

try {
    // Begin transaction
    $conn->begin_transaction();

    // First, ensure all PCs exist in computer_control
    for ($i = 1; $i <= 50; $i++) {
        $pc_number = "PC-" . $i;
        
        // Check if PC exists
        $check_sql = "SELECT id FROM computer_control WHERE lab_name = ? AND pc_number = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $lab, $pc_number);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows === 0) {
            // Insert new PC
            $insert_sql = "INSERT INTO computer_control (lab_name, pc_number, status, last_update) VALUES (?, ?, ?, NOW())";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("sss", $lab, $pc_number, $status);
            $insert_stmt->execute();
        }
    }

    // Update all PCs in the lab
    $update_sql = "UPDATE computer_control SET status = ?, last_update = NOW() WHERE lab_name = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ss", $status, $lab);
    $result = $update_stmt->execute();

    if ($result) {
        // Update pc_availability for all PCs in the lab
        $is_available = ($status === 'available') ? 1 : 0;
        
        // First, ensure all PCs exist in pc_availability
        for ($i = 1; $i <= 50; $i++) {
            $pc_number = "PC-" . $i;
            
            // Check if PC exists in pc_availability
            $check_avail_sql = "SELECT id FROM pc_availability WHERE lab_name = ? AND pc_number = ?";
            $check_avail_stmt = $conn->prepare($check_avail_sql);
            $check_avail_stmt->bind_param("ss", $lab, $pc_number);
            $check_avail_stmt->execute();
            $check_avail_stmt->store_result();
            
            if ($check_avail_stmt->num_rows === 0) {
                // Insert new PC
                $insert_avail_sql = "INSERT INTO pc_availability (lab_name, pc_number, is_available) VALUES (?, ?, ?)";
                $insert_avail_stmt = $conn->prepare($insert_avail_sql);
                $insert_avail_stmt->bind_param("ssi", $lab, $pc_number, $is_available);
                $insert_avail_stmt->execute();
            }
        }

        // Update all PCs in pc_availability
        $update_avail_sql = "UPDATE pc_availability SET is_available = ? WHERE lab_name = ?";
        $update_avail_stmt = $conn->prepare($update_avail_sql);
        $update_avail_stmt->bind_param("is", $is_available, $lab);
        $avail_result = $update_avail_stmt->execute();

        if ($avail_result) {
            $conn->commit();
            
            // Return success response
            echo json_encode([
                'status' => 'success',
                'message' => 'Lab status updated successfully',
                'data' => [
                    'lab' => $lab,
                    'status' => $status,
                    'is_available' => $is_available
                ]
            ]);
        } else {
            throw new Exception("Failed to update pc_availability");
        }
    } else {
        throw new Exception("Failed to update computer_control");
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 