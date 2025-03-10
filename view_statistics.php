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

// Fetch total registered students
$sql_registered = "SELECT COUNT(*) as total FROM users";
$result_registered = $conn->query($sql_registered);
$registered_students = $result_registered->fetch_assoc()['total'];

// Fetch total sit-ins
$sql_total = "SELECT COUNT(*) as total FROM sit_in_history";
$result_total = $conn->query($sql_total);
$total_sitins = $result_total->fetch_assoc()['total'];

// Fetch active sit-ins
$sql_active = "SELECT COUNT(*) as active FROM sit_in_history WHERE session_end IS NULL";
$result_active = $conn->query($sql_active);
$active_sitins = $result_active->fetch_assoc()['active'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Statistics</title>
    <!-- Chart.js for graphs -->
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
        .chart-container {
            width: 50%;
            margin: 20px auto;
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
    <h1>View Statistics</h1>

    <!-- Statistics Cards -->
    <div class="statistics-container">
        <div class="statistic">
            <h2><?= $registered_students ?></h2>
            <p>Registered Students</p>
        </div>
        <div class="statistic">
            <h2><?= $active_sitins ?></h2>
            <p>Current Sit-ins</p>
        </div>
        <div class="statistic">
            <h2><?= $total_sitins ?></h2>
            <p>Total Sit-ins</p>
        </div>
    </div>

    <!-- Chart for Statistics -->
    <div class="chart-container">
        <canvas id="statisticsChart"></canvas>
    </div>

    <script>
        // Render the statistics chart
        const ctx = document.getElementById('statisticsChart').getContext('2d');
        const statisticsChart = new Chart(ctx, {
            type: 'bar', // Use 'bar' for a bar chart
            data: {
                labels: ['Registered Students', 'Current Sit-ins', 'Total Sit-ins'],
                datasets: [{
                    label: 'Statistics',
                    data: [<?= $registered_students ?>, <?= $active_sitins ?>, <?= $total_sitins ?>],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Sit-in Statistics'
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