<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_info'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['user_info']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Fetch feedback data
$sql_feedback = "SELECT * FROM feedback ORDER BY submitted_at DESC";
$result_feedback = $conn->query($sql_feedback);
$feedback = [];
while ($row = $result_feedback->fetch_assoc()) {
    $feedback[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Feedback</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f7f9;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
    <h1>View Feedback</h1>

    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Rating</th>
                <th>Comments</th>
                <th>Submitted At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($feedback as $fb): ?>
                <tr>
                    <td><?= htmlspecialchars($fb['user_id']) ?></td>
                    <td><?= htmlspecialchars($fb['rating']) ?></td>
                    <td><?= htmlspecialchars($fb['comments']) ?></td>
                    <td><?= htmlspecialchars($fb['submitted_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>

<?php
$conn->close();
?>