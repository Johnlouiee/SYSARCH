<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Initialize variables
$student_info = null;
$student_sessions = [];
$student_points = 0;
$error = null;
$success = null;

// Search for a specific student
if (isset($_POST['search'])) {
    $search_term = trim($_POST['search_term']);
    
    if (!empty($search_term)) {
        // Search by ID or name, only for students
        $sql = "SELECT * FROM users WHERE role = 'student' AND (idno = ? OR CONCAT(firstname, ' ', lastname) LIKE ?)";
        $stmt = $conn->prepare($sql);
        $search_param = "%$search_term%";
        $stmt->bind_param("ss", $search_term, $search_param);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $student_info = $result->fetch_assoc();
            $student_id = $student_info['idno'];
            $student_points = $student_info['total_points'];
            
            // Get student's sessions
            $sql_sessions = "SELECT * FROM sit_in_history WHERE user_id = ? ORDER BY session_start DESC";
            $stmt_sessions = $conn->prepare($sql_sessions);
            $stmt_sessions->bind_param("s", $student_id);
            $stmt_sessions->execute();
            $result_sessions = $stmt_sessions->get_result();
            $student_sessions = $result_sessions->fetch_all(MYSQLI_ASSOC);
        } else {
            $error = "Student not found.";
        }
    } else {
        $error = "Please enter a search term.";
    }
}

// Reset a specific session
if (isset($_POST['reset_session'])) {
    $session_id = $_POST['session_id'];
    $student_id = $_POST['student_id'];
    
    $sql = "UPDATE sit_in_history SET session_end = NOW() WHERE id = ? AND user_id = ? AND session_end IS NULL AND status = 'accepted'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $session_id, $student_id);
    
    if ($stmt->execute()) {
        $success = "Session successfully ended.";
        // Refresh student data if searching
        if (isset($_POST['student_id'])) {
            $student_id = $_POST['student_id'];
            $sql = "SELECT * FROM users WHERE idno = ? AND role = 'student'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $student_info = $result->fetch_assoc();
            $student_points = $student_info['total_points'];
            
            // Get updated sessions
            $sql_sessions = "SELECT * FROM sit_in_history WHERE user_id = ? ORDER BY session_start DESC";
            $stmt_sessions = $conn->prepare($sql_sessions);
            $stmt_sessions->bind_param("s", $student_id);
            $stmt_sessions->execute();
            $result_sessions = $stmt_sessions->get_result();
            $student_sessions = $result_sessions->fetch_all(MYSQLI_ASSOC);
        }
    } else {
        $error = "Error ending session: " . $conn->error;
    }
}

// Reset all active sessions for a student
if (isset($_POST['reset_all_sessions'])) {
    $student_id = $_POST['student_id'];
    
    $sql = "UPDATE sit_in_history SET session_end = NOW() WHERE user_id = ? AND session_end IS NULL AND status = 'accepted'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $student_id);
    
    if ($stmt->execute()) {
        $success = "All active sessions for this student have been ended.";
        // Refresh student data if searching
        if (isset($_POST['student_id'])) {
            $sql = "SELECT * FROM users WHERE idno = ? AND role = 'student'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $student_info = $result->fetch_assoc();
            $student_points = $student_info['total_points'];
            
            // Get updated sessions
            $sql_sessions = "SELECT * FROM sit_in_history WHERE user_id = ? ORDER BY session_start DESC";
            $stmt_sessions = $conn->prepare($sql_sessions);
            $stmt_sessions->bind_param("s", $student_id);
            $stmt_sessions->execute();
            $result_sessions = $stmt_sessions->get_result();
            $student_sessions = $result_sessions->fetch_all(MYSQLI_ASSOC);
        }
    } else {
        $error = "Error ending sessions: " . $conn->error;
    }
}

// Reset all active sessions for all students
if (isset($_POST['reset_all_students_sessions'])) {
    $sql = "UPDATE sit_in_history SET session_end = NOW() WHERE session_end IS NULL AND status = 'accepted'";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute()) {
        $success = "All active sessions for all students have been ended.";
        // Refresh student data if searching
        if ($student_info) {
            $student_id = $student_info['idno'];
            $sql_sessions = "SELECT * FROM sit_in_history WHERE user_id = ? ORDER BY session_start DESC";
            $stmt_sessions = $conn->prepare($sql_sessions);
            $stmt_sessions->bind_param("s", $student_id);
            $stmt_sessions->execute();
            $result_sessions = $stmt_sessions->get_result();
            $student_sessions = $result_sessions->fetch_all(MYSQLI_ASSOC);
            
            // Re-fetch student data
            $sql = "SELECT * FROM users WHERE idno = ? AND role = 'student'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $student_info = $result->fetch_assoc();
            $student_points = $student_info['total_points'];
        }
    } else {
        $error = "Error ending all sessions: " . $conn->error;
    }
}

// Add points to a student
if (isset($_POST['add_points'])) {
    $student_id = $_POST['student_id'];
    $points_to_add = (int)$_POST['points_to_add'];
    
    if ($points_to_add > 0) {
        $sql = "UPDATE users SET total_points = total_points + ? WHERE idno = ? AND role = 'student'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $points_to_add, $student_id);
        
        if ($stmt->execute()) {
            $success = "Successfully added $points_to_add points to student.";
            $student_points += $points_to_add;
        } else {
            $error = "Error adding points: " . $conn->error;
        }
    } else {
        $error = "Please enter a positive number of points to add.";
    }
}

// Reset student points
if (isset($_POST['reset_points'])) {
    $student_id = $_POST['student_id'];
    
    $sql = "UPDATE users SET total_points = 0 WHERE idno = ? AND role = 'student'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $student_id);
    
    if ($stmt->execute()) {
        $success = "Student points have been reset to 0.";
        $student_points = 0;
    } else {
        $error = "Error resetting points: " . $conn->error;
    }
}

// Fetch all students for display
$students_sql = "SELECT u.idno, u.firstname, u.lastname, u.course, u.year, u.total_points as points,
                COUNT(sh.id) as total_sessions,
                COUNT(CASE WHEN sh.session_end IS NULL AND sh.status = 'accepted' THEN 1 END) as active_sessions
                FROM users u
                LEFT JOIN sit_in_history sh ON u.idno = sh.user_id
                WHERE u.role = 'student'
                GROUP BY u.idno, u.firstname, u.lastname, u.course, u.year, u.total_points
                ORDER BY u.lastname, u.firstname";
$students_result = $conn->query($students_sql);

// Fetch top points earners
$points_sql = "SELECT firstname, lastname, total_points as points 
              FROM users 
              WHERE role = 'student' AND total_points > 0
              ORDER BY total_points DESC
              LIMIT 10";
$points_result = $conn->query($points_sql);

// Fetch top time spent
$time_sql = "SELECT u.firstname, u.lastname, 
            SUM(TIMESTAMPDIFF(HOUR, sh.session_start, COALESCE(sh.session_end, NOW()))) as total_hours,
            COUNT(DISTINCT DATE(sh.session_start)) as total_days
            FROM sit_in_history sh
            JOIN users u ON sh.user_id = u.idno
            WHERE u.role = 'student' AND sh.status = 'accepted'
            GROUP BY u.idno, u.firstname, u.lastname
            ORDER BY total_hours DESC
            LIMIT 10";
$time_result = $conn->query($time_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
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
        .header a:hover {
            text-decoration: underline;
        }
        .header .logout-btn {
            background-color: #f44336;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
        }
        .header .logout-btn:hover {
            background-color: #d32f2f;
            text-decoration: none;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #333;
        }
        .search-box {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .search-box input[type="text"] {
            padding: 8px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-box button {
            padding: 8px 16px;
            border: none;
            background-color: #4CAF50;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }
        .student-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .info-card {
            flex: 1;
            min-width: 200px;
            padding: 15px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }
        .info-card h3 {
            margin-top: 0;
            color: #4CAF50;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .points-display {
            font-size: 24px;
            font-weight: bold;
            color: #4CAF50;
            text-align: center;
            margin: 10px 0;
        }
        .points-form {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .points-form input[type="number"] {
            padding: 8px;
            width: 80px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .points-form button {
            padding: 8px 12px;
            border: none;
            background-color: #4CAF50;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }
        .reset-btn {
            padding: 8px 12px;
            border: none;
            background-color: #f44336;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
        }
        .reset-all-btn {
            padding: 8px 16px;
            border: none;
            background-color: #f44336; /* Changed to red */
            color: white;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .reset-all-btn:hover {
            background-color: #d32f2f; /* Darker red on hover */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 0.9em;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
        }
        .session-history-table {
            font-size: 1.1em; /* Increased font size for larger text */
        }
        .session-history-table th, .session-history-table td {
            padding: 24px; /* Further increased padding for larger cells */
        }
        th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .active-session {
            background-color: #ffeb3b;
        }
        .session-action {
            text-align: center;
        }
        .session-action button {
            padding: 5px 10px;
            border: none;
            background-color: #f44336;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-error {
            background-color: #ffebee;
            color: #f44336;
            border: 1px solid #f44336;
        }
        .alert-success {
            background-color: #e8f5e9;
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        .dashboard {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }
        .leaderboard-section {
            flex: 1;
            min-width: 300px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }
        .leaderboard-section h3 {
            margin-top: 0;
            color: #4CAF50;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .leaderboard-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }
        .leaderboard-item:hover {
            background-color: #f1f1f1;
        }
        .rank {
            width: 40px;
            text-align: center;
            font-weight: bold;
        }
        .gold { color: #FFD700; }
        .silver { color: #C0C0C0; }
        .bronze { color: #CD7F32; }
        .student-info-leaderboard {
            flex: 1;
            margin: 0 15px;
        }
        .points-badge {
            background: #007bff;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.9em;
            margin-left: 8px;
        }
        .score {
            font-weight: bold;
            color: #333;
        }
        .sub-score {
            color: #666;
            font-size: 0.9em;
            margin-left: 5px;
        }
        .no-records {
            text-align: center;
            color: #666;
            padding: 20px;
        }
        .action-btn {
            padding: 5px 10px;
            border: none;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            margin: 2px;
        }
        .add-points-btn {
            background-color: #4CAF50;
        }
        .reset-session-btn {
            background-color: #f44336;
        }
        .view-sessions-btn {
            background-color: #2196F3;
        }
        @media (max-width: 768px) {
            table {
                font-size: 0.8em;
            }
            th, td {
                padding: 8px;
            }
            .session-history-table {
                font-size: 0.9em; /* Adjusted font size for smaller screens */
            }
            .session-history-table th, .session-history-table td {
                padding: 16px; /* Adjusted padding for smaller screens */
            }
            .action-btn {
                padding: 4px 8px;
                font-size: 0.8em;
            }
            .points-form input[type="number"] {
                width: 60px;
            }
        }
    </style>
</head>
<body>
<div class="header">
    <div>
        <h1> College of Computer Studies Admin</h1>
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
<div class="container">
    <h1>Student Management</h1>
    <!-- Leaderboards -->
    <div class="dashboard">
        <!-- Points Leaderboard -->
        <div class="leaderboard-section">
            <h3>Top Points Earners</h3>
            <div class="leaderboard">
                <?php if ($points_result->num_rows > 0): ?>
                    <?php $rank = 1; ?>
                    <?php while ($row = $points_result->fetch_assoc()): ?>
                        <div class="leaderboard-item">
                            <div class="rank">
                                <?php 
                                    if ($rank == 1) echo '<span class="gold">1st</span>';
                                    elseif ($rank == 2) echo '<span class="silver">2nd</span>';
                                    elseif ($rank == 3) echo '<span class="bronze">3rd</span>';
                                    else echo $rank . 'th';
                                ?>
                            </div>
                            <div class="student-info-leaderboard">
                                <?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?>
                                <span class="points-badge"><?= htmlspecialchars($row['points']) ?> pts</span>
                            </div>
                            <div class="score">
                                Points
                            </div>
                        </div>
                        <?php $rank++; ?>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-records">No points data available</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Time Spent Leaderboard -->
        <div class="leaderboard-section">
            <h3>Top Time Spent</h3>
            <div class="leaderboard">
                <?php if ($time_result->num_rows > 0): ?>
                    <?php $rank = 1; ?>
                    <?php while ($row = $time_result->fetch_assoc()): ?>
                        <div class="leaderboard-item">
                            <div class="rank">
                                <?php 
                                    if ($rank == 1) echo '<span class="gold">1st</span>';
                                    elseif ($rank == 2) echo '<span class="silver">2nd</span>';
                                    elseif ($rank == 3) echo '<span class="bronze">3rd</span>';
                                    else echo $rank . 'th';
                                ?>
                            </div>
                            <div class="student-info-leaderboard">
                                <?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?>
                            </div>
                            <div class="score">
                                <?= htmlspecialchars($row['total_hours']) ?> hours
                                <span class="sub-score">(<?= htmlspecialchars($row['total_days']) ?> days)</span>
                            </div>
                        </div>
                        <?php $rank++; ?>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-records">No time data available</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <div class="search-box">
        <form method="POST">
            <input type="text" name="search_term" placeholder="Enter student ID or name" value="<?= isset($_POST['search_term']) ? htmlspecialchars($_POST['search_term']) : '' ?>">
            <button type="submit" name="search">Search</button>
        </form>
    </div>
    
    <!-- All Students Table -->
    <h2>All Students</h2>
    <form method="POST">
        <button type="submit" name="reset_all_students_sessions" class="reset-all-btn">Reset All Active Sessions for All Students</button>
    </form>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Course</th>
                <th>Year</th>
                <th>Points</th>
                <th>Total Sessions</th>
                <th>Active Sessions</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($students_result->num_rows > 0): ?>
                <?php while ($student = $students_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($student['idno']) ?></td>
                        <td><?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']) ?></td>
                        <td><?= htmlspecialchars($student['course']) ?></td>
                        <td><?= htmlspecialchars($student['year']) ?></td>
                        <td><?= htmlspecialchars($student['points']) ?></td>
                        <td><?= htmlspecialchars($student['total_sessions']) ?></td>
                        <td><?= htmlspecialchars($student['active_sessions']) ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="student_id" value="<?= htmlspecialchars($student['idno']) ?>">
                                <input type="number" name="points_to_add" min="1" placeholder="Points" style="width: 60px; padding: 2px;" required>
                                <button type="submit" name="add_points" class="action-btn add-points-btn">Add</button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="student_id" value="<?= htmlspecialchars($student['idno']) ?>">
                                <button type="submit" name="reset_all_sessions" class="action-btn reset-session-btn">Reset Sessions</button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="search_term" value="<?= htmlspecialchars($student['idno']) ?>">
                                <button type="submit" name="search" class="action-btn view-sessions-btn">View Sessions</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="no-records">No students found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php if ($student_info): ?>
        <div class="student-info">
            <div class="info-card">
                <h3>Student Information</h3>
                <p><strong>ID:</strong> <?= htmlspecialchars($student_info['idno']) ?></p>
                <p><strong>Name:</strong> <?= htmlspecialchars($student_info['firstname'] . ' ' . $student_info['lastname']) ?></p>
                <p><strong>Course:</strong> <?= htmlspecialchars($student_info['course']) ?></p>
                <p><strong>Year:</strong> <?= htmlspecialchars($student_info['year']) ?></p>
            </div>
            
            <div class="info-card">
                <h3>Points Management</h3>
                <div class="points-display"><?= $student_points ?> points</div>
                <form method="POST" class="points-form">
                    <input type="hidden" name="student_id" value="<?= htmlspecialchars($student_info['idno']) ?>">
                    <input type="number" name="points_to_add" min="1" placeholder="Points" required>
                    <button type="submit" name="add_points">Add Points</button>
                </form>
                <form method="POST">
                    <input type="hidden" name="student_id" value="<?= htmlspecialchars($student_info['idno']) ?>">
                    <button type="submit" name="reset_points" class="reset-btn">Reset Points to 0</button>
                </form>
            </div>
            
            <div class="info-card">
                <h3>Session Management</h3>
                <p><strong>Total Sessions:</strong> <?= count($student_sessions) ?></p>
                <p><strong>Active Sessions:</strong> <?= count(array_filter($student_sessions, function($s) { return is_null($s['session_end']) && $s['status'] === 'accepted'; })) ?></p>
                <form method="POST">
                    <input type="hidden" name="student_id" value="<?= htmlspecialchars($student_info['idno']) ?>">
                    <button type="submit" name="reset_all_sessions" class="reset-btn">End All Active Sessions</button>
                </form>
            </div>
        </div>
        
        <h2>Session History</h2>
        <table class="session-history-table">
            <thead>
                <tr>
                    <th>Session ID</th>
                    <th>Lab</th>
                    <th>Purpose</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($student_sessions as $session): ?>
                    <tr class="<?= is_null($session['session_end']) && $session['status'] === 'accepted' ? 'active-session' : '' ?>">
                        <td><?= htmlspecialchars($session['id']) ?></td>
                        <td><?= htmlspecialchars($session['lab']) ?></td>
                        <td><?= htmlspecialchars($session['purpose']) ?></td>
                        <td><?= htmlspecialchars($session['session_start']) ?></td>
                        <td><?= htmlspecialchars($session['session_end'] ?? 'Still active') ?></td>
                        <td>
                            <?php 
                            if ($session['session_end']) {
                                $start = new DateTime($session['session_start']);
                                $end = new DateTime($session['session_end']);
                                $diff = $start->diff($end);
                                echo $diff->format('%h hours %i minutes');
                            } else {
                                $start = new DateTime($session['session_start']);
                                $now = new DateTime();
                                $diff = $start->diff($now);
                                echo $diff->format('%h hours %i minutes (ongoing)');
                            }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($session['status']) ?></td>
                        <td class="session-action">
                            <?php if (is_null($session['session_end']) && $session['status'] === 'accepted'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="session_id" value="<?= $session['id'] ?>">
                                    <input type="hidden" name="student_id" value="<?= htmlspecialchars($student_info['idno']) ?>">
                                    <button type="submit" name="reset_session">End Session</button>
                                </form>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif (isset($_POST['search'])): ?>
        <p>No student found with that ID or name.</p>
    <?php else: ?>
        <p>Search for a student to view and manage their detailed information.</p>
    <?php endif; ?>
</div>
</body>
</html>

<?php
$conn->close();
?>