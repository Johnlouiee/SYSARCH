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

// Fetch active sit-in records (where session_end is NULL)
$sql = "SELECT sit_in_history.*, users.firstname, users.lastname 
        FROM sit_in_history 
        JOIN users ON sit_in_history.user_id = users.idno 
        WHERE session_end IS NULL 
        ORDER BY session_start DESC";
$result = $conn->query($sql);

// Fetch purpose usage data for the pie chart
$sql_pie_purpose = "SELECT purpose, COUNT(*) as count 
                    FROM sit_in_history 
                    WHERE session_end IS NULL 
                    GROUP BY purpose";
$result_pie_purpose = $conn->query($sql_pie_purpose);

$labels_purpose = [];
$data_purpose = [];
$colors_purpose = [];

// Assign unique colors to each purpose
$colorPalette = [
    'rgba(255, 99, 132, 0.6)', // Red
    'rgba(54, 162, 235, 0.6)', // Blue
    'rgba(255, 206, 86, 0.6)', // Yellow
    'rgba(75, 192, 192, 0.6)', // Green
    'rgba(153, 102, 255, 0.6)', // Purple
];

while ($row = $result_pie_purpose->fetch_assoc()) {
    $labels_purpose[] = $row['purpose'];
    $data_purpose[] = $row['count'];
    $colors_purpose[] = $colorPalette[array_rand($colorPalette)]; // Assign a random color from the palette
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Sit-in</title>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
        .logout-btn {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .logout-btn:hover {
            background-color: #d32f2f;
        }
        .chart-container {
            width: 300px; /* Smaller width for the pie chart */
            height: 300px; /* Smaller height for the pie chart */
            margin: 20px auto; /* Center the chart */
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
    <h1>Current Sit-in</h1>

    <!-- Pie Chart for Purpose Usage -->
    <div class="chart-container">
        <canvas id="purposeUsageChart"></canvas>
    </div>

    <!-- Table for Current Sit-in Records -->
    <table>
        <thead>
            <tr>
                <th>Sit-in Number</th>
                <th>ID Number</th>
                <th>Student Name</th>
                <th>Purpose</th>
                <th>Lab</th>
                <th>Login Time</th>
                <th>Logout Time</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    // Extract date from session_start
                    $date = date('Y-m-d', strtotime($row['session_start']));
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td> <!-- Sit-in Number -->
                        <td><?= htmlspecialchars($row['user_id']) ?></td> <!-- ID Number -->
                        <td><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></td> <!-- Student Name -->
                        <td><?= htmlspecialchars($row['purpose']) ?></td> <!-- Purpose -->
                        <td><?= htmlspecialchars($row['lab']) ?></td> <!-- Lab -->
                        <td><?= htmlspecialchars($row['session_start']) ?></td> <!-- Login Time -->
                        <td><?= htmlspecialchars($row['session_end']) ?></td> <!-- Logout Time -->
                        <td><?= htmlspecialchars($date) ?></td> <!-- Date -->
                        <td>
                            <form method="POST" action="end_sitin.php" style="display: inline;">
                                <input type="hidden" name="sit_in_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="logout-btn">Log Out</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="no-records">No active sit-in records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        // Render the pie chart for purpose usage
        const ctxPurpose = document.getElementById('purposeUsageChart').getContext('2d');
        const purposeUsageChart = new Chart(ctxPurpose, {
            type: 'pie',
            data: {
                labels: <?= json_encode($labels_purpose) ?>,
                datasets: [{
                    label: 'Purpose Usage',
                    data: <?= json_encode($data_purpose) ?>,
                    backgroundColor: <?= json_encode($colors_purpose) ?>,
                    borderColor: <?= json_encode(array_map(function($color) { return str_replace('0.6', '1', $color); }, $colors_purpose)) ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Allow the chart to resize
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Purpose Usage in Current Sit-ins'
                    }
                }
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>