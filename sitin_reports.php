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

// Fetch sit-in report data with search and pagination
$sql = "SELECT sit_in_history.user_id, users.firstname, users.lastname, sit_in_history.lab, sit_in_history.purpose, 
               sit_in_history.session_start, sit_in_history.session_end 
        FROM sit_in_history 
        JOIN users ON sit_in_history.user_id = users.idno 
        WHERE (sit_in_history.user_id LIKE ? OR sit_in_history.lab LIKE ? OR sit_in_history.purpose LIKE ?)
        ORDER BY sit_in_history.session_start DESC 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing query: " . $conn->error);
}

$search_term = "%$search%";
$stmt->bind_param("sssii", $search_term, $search_term, $search_term, $per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}

// Fetch total number of reports for pagination
$sql_total = "SELECT COUNT(*) as total 
              FROM sit_in_history 
              JOIN users ON sit_in_history.user_id = users.idno 
              WHERE (sit_in_history.user_id LIKE ? OR sit_in_history.lab LIKE ? OR sit_in_history.purpose LIKE ?)";
$stmt_total = $conn->prepare($sql_total);
if (!$stmt_total) {
    die("Error preparing query: " . $conn->error);
}
$stmt_total->bind_param("sss", $search_term, $search_term, $search_term);
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_row = $total_result->fetch_assoc();
$total_reports = $total_row['total'];
$total_pages = ceil($total_reports / $per_page);

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
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a, .pagination span {
            padding: 8px 16px;
            margin: 0 4px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #4CAF50;
        }
        .pagination a:hover {
            background-color: #4CAF50;
            color: white;
        }
        .pagination span {
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
        .logout-btn {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .logout-btn:hover {
            background-color: #d32f2f;
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
    <h1>View Reservation</h1>

    <!-- Search Bar -->
    <div class="search-bar">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search by ID, name, lab, or purpose..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <!-- Reservation Table -->
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
            <?php if (!empty($reservations)): ?>
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
            <?php else: ?>
                <tr>
                    <td colspan="7" class="no-records">No reservations found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Previous</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php if ($i == $page): ?>
                <span><?= $i ?></span>
            <?php else: ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next</a>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$stmt->close();
$stmt_total->close();
$conn->close();
?>