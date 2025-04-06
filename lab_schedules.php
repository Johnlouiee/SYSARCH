<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['idno'])) {
    header("Location: index.php");
    exit();
}

// Fetch lab schedules
$sql = "SELECT * FROM lab_schedules ORDER BY day_of_week, start_time";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Schedules</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7f9;
        }
        .header {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .schedule-table th, .schedule-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .schedule-table th {
            background-color: #4CAF50;
            color: white;
        }
        .schedule-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .schedule-table tr:hover {
            background-color: #f1f1f1;
        }
        .day-header {
            background-color: #e8f5e9;
            font-weight: bold;
        }
        .no-schedules {
            text-align: center;
            padding: 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <a href="home.php">Home</a>
            <a href="reports.php">Reports</a>
            <a href="editprofile.php">Edit Profile</a>
            <a href="view_announcements.php">View Announcement</a>
            <a href="reservation.php">Reservation</a>
            <a href="sitin.php">Sit-In History</a>
            <a href="lab_schedules.php">Lab Schedules</a>
        </div>
        <div>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['idno']); ?>!</span>
            <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
        </div>
    </div>

    <div class="container">
        <h1>Lab Schedules</h1>
        
        <?php if ($result->num_rows > 0): ?>
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Lab</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Subject</th>
                        <th>Instructor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $current_day = '';
                    while ($row = $result->fetch_assoc()): 
                        $day = $row['day_of_week'];
                        if ($day !== $current_day) {
                            $current_day = $day;
                            echo "<tr class='day-header'><td colspan='6'>" . ucfirst($day) . "</td></tr>";
                        }
                    ?>
                        <tr>
                            <td></td>
                            <td><?php echo htmlspecialchars($row['lab']); ?></td>
                            <td><?php echo date('h:i A', strtotime($row['start_time'])); ?></td>
                            <td><?php echo date('h:i A', strtotime($row['end_time'])); ?></td>
                            <td><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td><?php echo htmlspecialchars($row['instructor']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-schedules">
                <p>No lab schedules available at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?> 