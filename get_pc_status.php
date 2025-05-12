<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_info'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Check if lab parameter is present
if (!isset($_GET['lab'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing lab parameter'
    ]);
    exit;
}

$lab = $_GET['lab'];

try {
    // Get all PCs for the lab
    $sql = "SELECT pc_number, status FROM computer_control WHERE lab_name = ? ORDER BY CAST(SUBSTRING(pc_number, 4) AS UNSIGNED)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $lab);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pcs = [];
    while ($row = $result->fetch_assoc()) {
        $pcs[] = [
            'pc_number' => $row['pc_number'],
            'status' => $row['status']
        ];
    }
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'pcs' => $pcs
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 