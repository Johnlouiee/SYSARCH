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

// Initialize variables
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d'); // Default to today's date
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); // Default to today's date
$reports = [];

// Fetch sit-in reports based on date range
if (!empty($start_date) && !empty($end_date)) {
    $sql = "SELECT sit_in_history.*, users.firstname, users.lastname 
            FROM sit_in_history 
            JOIN users ON sit_in_history.user_id = users.idno 
            WHERE DATE(session_start) BETWEEN ? AND ? 
            ORDER BY session_start DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ss", $start_date, $end_date);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $reports[] = $row;
            }
        } else {
            echo "SQL Error: " . $stmt->error;
        }
    } else {
        echo "Prepare Error: " . $conn->error;
    }
}

// Export reports to CSV
if (isset($_GET['export']) && !empty($start_date) && !empty($end_date)) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sit_in_reports_' . $start_date . '_to_' . $end_date . '.csv"');

    $output = fopen('php://output', 'w');

    // Add CSV headers
    fputcsv($output, [
        'IDNO', 'Student Name', 'Purpose', 'Lab', 'Login Time', 'Logout Time', 'Date'
    ]);

    // Add CSV data
    foreach ($reports as $report) {
        // Extract date from session_start
        $date = date('Y-m-d', strtotime($report['session_start']));

        fputcsv($output, [
            $report['user_id'],
            $report['firstname'] . ' ' . $report['lastname'],
            $report['purpose'],
            $report['lab'],
            $report['session_start'], // Login Time
            $report['session_end'],   // Logout Time
            $date                     // Date
        ]);
    }

    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sit-in Reports</title>
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
        .filter-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .filter-container input[type="date"] {
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .filter-container button {
            padding: 8px 16px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .filter-container button:hover {
            background-color: #45a049;
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
        .no-records {
            text-align: center;
            color: #777;
            margin-top: 20px;
        }
        .export-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .export-btn:hover {
            background-color: #45a049;
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
        <h1> </h1>  
        <a href="admin_home.php">Home</a>
        <a href="#" id="searchLink">Search</a>
        <a href="view_current_sitin.php">Current Sit-in</a>
        <a href="view_sitin.php">Sit-in Records</a>
        <a href="sitin_reports.php">Sit-in Reports</a>
        <a href="view_feedback.php">View Feedback</a>
        <a href="view_reservation.php">View Reservation</a>
        <a href="reservation_logs.php">Reservation Logs</a>
        <a href="student_management.php">Student Information</a>
        <a href="lab_schedule.php">Lab Schedule</a>
        <a href="lab_resources.php">Lab Resources</a>
        <a href="admin_notification.php">Notification</a>
        <a href="computer_control.php">Computer Control</a>
    </div>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>
    <h1>Sit-In Reports</h1>

    <!-- Date Range Form -->
    <div class="filter-container">
        <form method="GET" action="">
            <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" required>
            <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" required>
            <button type="submit">Generate Report</button>
        </form>
    </div>

    <!-- Export Button -->
    <?php if (!empty($reports)): ?>
        <a href="sitin_reports.php?export=1&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>" class="export-btn">Export to CSV</a>
    <?php endif; ?>

    <!-- Reports Table -->
    <?php if (!empty($reports)): ?>
        <table>
            <thead>
                <tr>
                    <th>IDNO</th>
                    <th>Student Name</th>
                    <th>Purpose</th>
                    <th>Lab</th>
                    <th>Login Time</th>
                    <th>Logout Time</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): ?>
                    <?php
                    // Extract date from session_start
                    $date = date('Y-m-d', strtotime($report['session_start']));
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($report['user_id']) ?></td>
                        <td><?= htmlspecialchars($report['firstname'] . ' ' . $report['lastname']) ?></td>
                        <td><?= htmlspecialchars($report['purpose']) ?></td>
                        <td><?= htmlspecialchars($report['lab']) ?></td>
                        <td><?= htmlspecialchars($report['session_start']) ?></td>
                        <td><?= htmlspecialchars($report['session_end']) ?></td>
                        <td><?= htmlspecialchars($date) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif (!empty($start_date) && !empty($end_date)): ?>
        <p class="no-records">No records found for the selected date range.</p>
    <?php endif; ?>
</body>
</html>

<?php
$conn->close();
?>