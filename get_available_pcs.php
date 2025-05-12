<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_GET['lab'])) {
    echo json_encode(['status' => 'error', 'message' => 'Lab parameter is required']);
    exit;
}

$lab = $_GET['lab'];

// Get all PCs that are not disabled (offline) and not already reserved
$sql = "SELECT cc.pc_number 
        FROM computer_control cc
        LEFT JOIN reservations r ON cc.pc_number = r.pc_number 
            AND cc.lab_name = r.lab 
            AND r.reservation_date = CURDATE() 
            AND r.status IN ('Pending', 'Accepted')
        WHERE cc.lab_name = ? 
        AND cc.status != 'offline'
        AND r.id IS NULL
        ORDER BY CAST(SUBSTRING(cc.pc_number, 4) AS UNSIGNED)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $lab);
$stmt->execute();
$result = $stmt->get_result();

$pcs = [];
while ($row = $result->fetch_assoc()) {
    $pcs[] = $row;
}

echo json_encode([
    'status' => 'success',
    'pcs' => $pcs
]);

$stmt->close();
$conn->close();
?> 