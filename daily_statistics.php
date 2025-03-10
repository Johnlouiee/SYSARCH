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

// Fetch sit-ins for the current day
$sql_daily = "SELECT COUNT(*) as daily FROM sit_in_history WHERE DATE(session_start) = CURDATE()";
$result_daily = $conn->query($sql_daily);
$daily_sitins = $result_daily->fetch_assoc()['daily'];

// Fetch sit-in details for the current day
$sql_daily_details = "SELECT users.firstname, users.lastname, sit_in_history.session_start, sit_in_history.session_end 
                      FROM sit_in_history 
                      JOIN users ON sit_in_history.user_id = users.idno 
                      WHERE DATE(session_start) = CURDATE() 
                      ORDER BY session_start DESC";
$result_daily_details = $conn->query($sql_daily_details);
$daily_details = [];
while ($row = $result_daily_details->fetch_assoc()) {
    $daily_details[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Statistics</title>
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
        .statistics-container {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }
        .statistic {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 30%;
        }
        .statistic h2 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .statistic p {
            margin: 10px 0 0;
            font-size: 18px;
            color: #666;
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
    <h1>Daily Statistics</h1>

    <!-- Statistics Card -->
    <div class="statistics-container">
        <div class="statistic">
            <h2><?= $daily_sitins ?></h2>
            <p>Today's Sit-ins</p>
        </div>
    </div>

    <!-- Daily Sit-in Details -->
    <h2>Today's Sit-in Details</h2>
    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Login Time</th>
                <th>Logout Time</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($daily_details)): ?>
                <?php foreach ($daily_details as $detail): ?>
                    <tr>
                        <td><?= htmlspecialchars($detail['firstname'] . ' ' . $detail['lastname']) ?></td>
                        <td><?= htmlspecialchars($detail['session_start']) ?></td>
                        <td><?= htmlspecialchars($detail['session_end'] ?? 'Still Active') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align: center;">No sit-ins found for today.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

<?php
$conn->close();
?>