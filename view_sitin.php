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

// Fetch feedback data with student ID, laboratory, date, and message
$sql_feedback = "SELECT feedback.*, sit_in_history.lab, sit_in_history.session_start 
                 FROM feedback 
                 JOIN sit_in_history ON feedback.sit_in_id = sit_in_history.id 
                 ORDER BY feedback.submitted_at DESC";
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
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <h1>View Feedback</h1>

    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Laboratory</th>
                <th>Date</th>
                <th>Message</th>
                <th>Rating</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($feedback)): ?>
                <?php foreach ($feedback as $fb): ?>
                    <tr>
                        <td><?= htmlspecialchars($fb['user_id']) ?></td>
                        <td><?= htmlspecialchars($fb['lab']) ?></td>
                        <td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($fb['session_start']))) ?></td>
                        <td><?= htmlspecialchars($fb['comments']) ?></td>
                        <td><?= htmlspecialchars($fb['rating']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No feedback found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

<?php
$conn->close();
?>