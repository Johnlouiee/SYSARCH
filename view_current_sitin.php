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
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch active sit-in records (where session_end is NULL)
$sql = "SELECT sit_in_history.*, users.firstname, users.lastname 
        FROM sit_in_history 
        JOIN users ON sit_in_history.user_id = users.idno 
        WHERE session_end IS NULL 
        AND (users.firstname LIKE ? OR users.lastname LIKE ? OR sit_in_history.user_id LIKE ? OR sit_in_history.lab LIKE ?)
        ORDER BY session_start DESC 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing query: " . $conn->error);
}

$search_term = "%$search%";
$stmt->bind_param("ssssii", $search_term, $search_term, $search_term, $search_term, $per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Fetch total number of active sit-ins for pagination
$sql_total = "SELECT COUNT(*) as total 
              FROM sit_in_history 
              JOIN users ON sit_in_history.user_id = users.idno 
              WHERE session_end IS NULL 
              AND (users.firstname LIKE ? OR users.lastname LIKE ? OR sit_in_history.user_id LIKE ? OR sit_in_history.lab LIKE ?)";
$stmt_total = $conn->prepare($sql_total);
if (!$stmt_total) {
    die("Error preparing query: " . $conn->error);
}
$stmt_total->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_row = $total_result->fetch_assoc();
$total_sitins = $total_row['total'];
$total_pages = ceil($total_sitins / $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Sit-in</title>
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
        .message {
            text-align: center;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
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
        <a href="student_management.php">Student Information</a>
        <a href="lab_resources.php">Lab Resources</a>
    </div>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>
    <h1>Current Sit-In</h1>

    <!-- Success/Error Messages -->
    <?php if (isset($_GET['success'])): ?>
        <div class="message success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="message error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <!-- Search Bar -->
    <div class="search-bar">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search by name, ID, or lab..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>
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
                <th>Session Start</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['user_id']) ?></td>
                        <td><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></td>
                        <td><?= htmlspecialchars($row['purpose']) ?></td>
                        <td><?= htmlspecialchars($row['lab']) ?></td>
                        <td><?= htmlspecialchars($row['session_start']) ?></td>
                        <td><?= $row['session_end'] ? 'Ended' : 'Active' ?></td>
                        <td>
                            <?php if (!$row['session_end']): ?>
                                <form method="POST" action="end_sitin.php" style="display: inline;">
                                    <input type="hidden" name="sit_in_id" value="<?= htmlspecialchars($row['id']) ?>">
                                    <button type="submit" class="logout-btn" onclick="return confirm('Are you sure you want to log out this student?');">Log Out</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="no-records">No active sit-in records found.</td>
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