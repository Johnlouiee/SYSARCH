<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['idno'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php'; // Include the database connection

// Fetch student information
$idno = $_SESSION['idno'];
$sql = "SELECT * FROM users WHERE idno = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $idno);
$stmt->execute();
$result = $stmt->get_result();
$user_info = $result->fetch_assoc();
$stmt->close();

// Fetch reservation history
$reservation_sql = "SELECT * FROM reservations WHERE user_id = ? ORDER BY reservation_date DESC";
$reservation_stmt = $conn->prepare($reservation_sql);
$reservation_stmt->bind_param("s", $idno);
$reservation_stmt->execute();
$reservation_result = $reservation_stmt->get_result();
$reservations = [];
while ($row = $reservation_result->fetch_assoc()) {
    $reservations[] = $row;
}
$reservation_stmt->close();

// Fetch sit-in history
$sitin_sql = "SELECT * FROM user_sessions WHERE user_id = ? ORDER BY session_start DESC";
$sitin_stmt = $conn->prepare($sitin_sql);
$sitin_stmt->bind_param("s", $idno);
$sitin_stmt->execute();
$sitin_result = $sitin_stmt->get_result();
$sitins = [];
while ($row = $sitin_result->fetch_assoc()) {
    $sitins[] = $row;
}
$sitin_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <style>
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        h2 {
            margin-top: 20px;
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
        <a href="lab_student.php">Lab Resources</a>
    </div>
</div>

<h1>Reports</h1>

<!-- Remaining Sessions -->
<h2>Remaining Sessions</h2>
<p>You have <strong><?php echo htmlspecialchars($user_info['sessions_remaining'] ?? 0); ?></strong> sessions remaining.</p>

<!-- Reservation History -->
<h2>Reservation History</h2>
<table>
    <thead>
        <tr>
            <th>Reservation Date</th>
            <th>Lab</th>
            <th>Purpose</th>
            <th>Time In</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($reservations)): ?>
            <?php foreach ($reservations as $reservation): ?>
                <tr>
                    <td><?php echo htmlspecialchars($reservation['reservation_date']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['lab']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['purpose']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['time_in']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['status'] ?? 'Pending'); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No reservations found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Sit-In History -->
<h2>Sit-In History</h2>
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Lab</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Duration</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($sitins)): ?>
            <?php foreach ($sitins as $sitin): ?>
                <tr>
                    <td><?php echo htmlspecialchars($sitin['session_start']); ?></td>
                    <td><?php echo htmlspecialchars($sitin['lab'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($sitin['session_start']); ?></td>
                    <td><?php echo htmlspecialchars($sitin['session_end'] ?? 'Ongoing'); ?></td>
                    <td>
                        <?php
                        if (!empty($sitin['session_end'])) {
                            $start = new DateTime($sitin['session_start']);
                            $end = new DateTime($sitin['session_end']);
                            $duration = $start->diff($end);
                            echo $duration->format('%h hours %i minutes');
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No sit-in records found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>