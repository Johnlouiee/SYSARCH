<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10; // Entries per page
$offset = ($page - 1) * $per_page;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch all sit-in records with search and pagination
$sql = "SELECT sit_in_history.*, users.firstname, users.lastname 
        FROM sit_in_history 
        JOIN users ON sit_in_history.user_id = users.idno 
        WHERE (sit_in_history.user_id LIKE ? OR users.firstname LIKE ? OR users.lastname LIKE ? OR sit_in_history.purpose LIKE ? OR sit_in_history.lab LIKE ?)
        ORDER BY sit_in_history.session_start DESC 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing query: " . $conn->error);
}

$search_term = "%$search%";
$stmt->bind_param("sssssii", $search_term, $search_term, $search_term, $search_term, $search_term, $per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Fetch total number of sit-in records for pagination
$sql_total = "SELECT COUNT(*) as total 
              FROM sit_in_history 
              JOIN users ON sit_in_history.user_id = users.idno 
              WHERE (sit_in_history.user_id LIKE ? OR users.firstname LIKE ? OR users.lastname LIKE ? OR sit_in_history.purpose LIKE ? OR sit_in_history.lab LIKE ?)";
$stmt_total = $conn->prepare($sql_total);
if (!$stmt_total) {
    die("Error preparing query: " . $conn->error);
}
$stmt_total->bind_param("sssss", $search_term, $search_term, $search_term, $search_term, $search_term);
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_row = $total_result->fetch_assoc();
$total_sitins = $total_row['total'];
$total_pages = ceil($total_sitins / $per_page);

// Fetch purpose usage data for the pie chart (all records)
$sql_pie_purpose = "SELECT purpose, COUNT(*) as count 
                    FROM sit_in_history 
                    GROUP BY purpose";
$result_pie_purpose = $conn->query($sql_pie_purpose);

$labels_purpose = [];
$data_purpose = [];
$colors_purpose = [];

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
    $colors_purpose[] = $colorPalette[array_rand($colorPalette)];
}

$error = null;
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'active_session_exists':
            $error = "This student already has an active sit-in session.";
            break;
        case 'student_not_found':
            $error = "Student not found.";
            break;
        case 'database_error':
            $error = "A database error occurred.";
            break;
        default:
            $error = "An unknown error occurred.";
            break;
    }
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
        .search-bar {
            margin-bottom: 20px;
            text-align: center;
        }
        .search-bar input[type="text"] {
            padding: 8px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-bar button {
            padding: 8px 16px;
            border: none;
            background-color: #4CAF50;
            color: white;
            border-radius: 4px;
            cursor: pointer;
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
        <h2> College of Computer Studies Admin</h2>
        <a href="admin_home.php">Home</a>
        <a href="search_student.php">Search</a>
        <a href="view_current_sitin.php">Current Sit-in</a>
        <a href="view_sitin.php">Sit-in Records</a>
        <a href="sitin_reports.php">Sit-in Reports</a>
        <a href="view_feedback.php">View Feedback</a>
        <a href="view_reservation.php">View Reservation</a>
    </div>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>
    <h1>Current Sit-in Records</h1>

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
                       
                            </form>
                        
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