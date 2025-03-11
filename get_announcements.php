<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_info'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

include 'db_connect.php';

// Fetch all announcements
$sql = "SELECT * FROM announcements ORDER BY created_at DESC";
$result = $conn->query($sql);

$announcements = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = [
            'title' => htmlspecialchars($row['title']),
            'content' => nl2br(htmlspecialchars($row['content'])),
            'created_at' => $row['created_at']
        ];
    }
}

echo json_encode($announcements);

$conn->close();
?> 