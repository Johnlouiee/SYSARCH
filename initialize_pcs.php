<?php
require_once 'db_connect.php';

try {
    // Start transaction
    $conn->begin_transaction();

    // Define labs and number of PCs
    $labs = ['Lab 1', 'Lab 2', 'Lab 3', 'Lab 4'];
    $pcs_per_lab = 50;

    // First, clear existing records
    $conn->query("TRUNCATE TABLE computer_control");

    // Insert PCs for each lab
    foreach ($labs as $lab) {
        for ($i = 1; $i <= $pcs_per_lab; $i++) {
            $pc_number = "PC-" . $i;
            $sql = "INSERT INTO computer_control (lab_name, pc_number, status, last_update) 
                    VALUES (?, ?, 'available', NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $lab, $pc_number);
            $stmt->execute();
        }
    }

    // Commit transaction
    $conn->commit();
    echo "Successfully initialized all PCs in all labs!";
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}

$conn->close();
?> 