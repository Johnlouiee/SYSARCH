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
$sql_feedback = "SELECT feedback.user_id, sit_in_history.lab, sit_in_history.session_start, feedback.comments 
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
        .header {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
        }
        .header a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="header">
    <div>
        <a href="admin_home.php">Home</a>
        <a href="view_current_sitin.php">Current Sit-in</a>
        <a href="view_sitin.php">Sit-in Records</a>
        <a href="sitin_reports.php">Sit-in Reports</a>
        <a href="create_announcement.php">Create Announcement</a>
        <a href="view_statistics.php">View Statistics</a>
        <a href="daily_statistics.php">Daily Statistics</a>
        <a href="view_feedback.php">View Feedback</a>
        <a href="view_reservation.php">View Reservation</a>
    </div>
</div>
    <h1>View Feedback</h1>

    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Laboratory</th>
                <th>Date</th>
                <th>Message</th>
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
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center;">No feedback found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

<?php
$conn->close();
?>