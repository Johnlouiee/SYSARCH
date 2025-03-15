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

// Fetch reservation data
$sql_reservation = "SELECT * FROM reservations ORDER BY reservation_date DESC";
$result_reservation = $conn->query($sql_reservation);
$reservations = [];
while ($row = $result_reservation->fetch_assoc()) {
    $reservations[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reservation</title>
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
        .header a:hover {
            text-decoration: underline;
        }
</style>
</head>
<body>
<div class="header">
    <div>
     
        <a href="admin_home.php">Home</a>
        <a href="#" id="searchLink">Search</a>
        <a href="view_current_sitin.php">Current Sit-in</a>
        <a href="view_sitin.php">Sit-in Records</a>
        <a href="sitin_reports.php">Sit-in Reports</a>
        <a href="view_feedback.php">View Feedback</a>
        <a href="view_reservation.php">View Reservation</a>
    </div>
  
</div>
<h1>View Reservation</h1>

<table>
    <thead>
        <tr>
            <th>User ID</th>
            <th>Student Name</th>
            <th>Lab</th>
            <th>Reservation Date</th>
            <th>Time In</th>
            <th>Purpose</th>
            <th>Remaining Session</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($reservations as $reservation): ?>
            <tr>
                <td><?= htmlspecialchars($reservation['user_id']) ?></td>
                <td><?= htmlspecialchars($reservation['student_name']) ?></td>
                <td><?= htmlspecialchars($reservation['lab']) ?></td>
                <td><?= htmlspecialchars($reservation['reservation_date']) ?></td>
                <td><?= htmlspecialchars($reservation['time_in']) ?></td>
                <td><?= htmlspecialchars($reservation['purpose']) ?></td>
                <td><?= htmlspecialchars($reservation['remaining_session']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>

<?php
$conn->close();
?>