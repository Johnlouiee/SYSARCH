<?php
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => true,
    'cookie_httponly' => true,
]);

include 'db_connect.php'; // Include the database connection

if (!isset($_SESSION['idno'])) {
    header("Location: index.php");
    exit();
}

session_regenerate_id(true);

// Fetch user information from the database
$idno = $_SESSION['idno'];
$sql = "SELECT * FROM users WHERE idno = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $idno);
$stmt->execute();
$result = $stmt->get_result();
$user_info = $result->fetch_assoc();
$stmt->close();

// Get total completed sit-in sessions (where session_end is NOT NULL)
$completed_sessions_sql = "SELECT COUNT(*) as completed_sessions 
                          FROM sit_in_history 
                          WHERE user_id = ? AND session_end IS NOT NULL";
$completed_stmt = $conn->prepare($completed_sessions_sql);
$completed_stmt->bind_param("s", $idno);
$completed_stmt->execute();
$completed_result = $completed_stmt->get_result();
$completed_row = $completed_result->fetch_assoc();
$completed_sessions = $completed_row['completed_sessions'];
$completed_stmt->close();

// Calculate remaining sessions
$max_sessions = 30;
$remaining_sessions = max(0, $max_sessions - $completed_sessions);

// Get current active sit-in session status
$active_session_sql = "SELECT COUNT(*) as active_sessions 
                       FROM sit_in_history 
                       WHERE user_id = ? AND session_end IS NULL";
$active_stmt = $conn->prepare($active_session_sql);
$active_stmt->bind_param("s", $idno);
$active_stmt->execute();
$active_result = $active_stmt->get_result();
$active_row = $active_result->fetch_assoc();
$is_session_active = $active_row['active_sessions'] > 0;
$active_stmt->close();

// Update session info in $_SESSION
$_SESSION['user_info'] = [
    'idno' => $user_info['idno'],
    'firstname' => $user_info['firstname'],
    'lastname' => $user_info['lastname'],
    'course' => $user_info['course'],
    'year' => $user_info['year'],
    'email' => $user_info['email'],
    'sessions' => $remaining_sessions,
    'role' => $user_info['role']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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

        .dashboard {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .user-section {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            width: 320px;
            margin-right: 20px;
        }

        .user-info-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
        }

        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .card img {
            width: 100%;
            height: auto;
        }

        .main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .rules-container, .announcements-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .rules h2, .rules h3, .rules h4 {
            color: #333;
        }

        .rules p, .rules li {
            font-size: 16px;
            color: #444;
        }

        .announcements h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        .announcement {
            margin-bottom: 15px;
        }

        .announcement h3 {
            font-size: 20px;
            color: #555;
        }

        .announcement p {
            font-size: 16px;
            color: #444;
            line-height: 1.5;
        }
        .session-status {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }

        .session-active {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }

        .session-inactive {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }

        .session-note {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
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
            <a href="lab_schedule_student.php">Lab Schudles</a>
            <a href="view_points.php">View Points</a>
            <a href="lab_student.php">Lab Resources</a>
        </div>
        <div>
            <span>Welcome, <?php echo htmlspecialchars($idno); ?>!</span>
            <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
        </div>
    </div>

    <div class="dashboard">
        <div class="user-section">
            <div class="card">
                <img src="ok.jpg" alt="User Image">
            </div>
            <div class="user-info-container">
                <div class="user-info">
                    <p><strong>IDNO:</strong> <?php echo htmlspecialchars($user_info['idno']); ?></p>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user_info['firstname'] . ' ' . $user_info['lastname']); ?></p>
                    <p><strong>Course:</strong> <?php echo htmlspecialchars($user_info['course']); ?></p>
                    <p><strong>Year Level:</strong> <?php echo htmlspecialchars($user_info['year']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user_info['email']); ?></p>
                    <p><strong>Sessions Remaining:</strong> <?php echo $remaining_sessions; ?> / <?php echo $max_sessions; ?></p>
                </div>
                <div class="session-status <?php echo $is_session_active ? 'session-active' : 'session-inactive'; ?>">
                    <?php 
                    echo $is_session_active 
                        ? "Session Active" 
                        : "Session Inactive"; 
                    ?>
                    <div class="session-note">*Session controlled by admin</div>
                </div>
            </div>
        </div>
        
        <div class="main-content">
            <div class="rules-container">
                <div class="rules">
                    <h2>University of Cebu</h2>
                    <h3>COLLEGE OF INFORMATION & COMPUTER STUDIES</h3>
                    <h4>LABORATORY RULES AND REGULATIONS</h4>
                    <p>To avoid embarrassment and maintain camaraderie with your friends and superiors at our laboratories, please observe the following:</p>
                    <ul>
                        <li>1. Maintain silence, proper decorum, and discipline inside the laboratory. Mobile phones, walkmans, and other personal equipment must be switched off.</li>
                        <li>2. Games are not allowed inside the lab. This includes computer-related games, card games, and other games that may disturb the operation of the lab.</li>
                        <li>3. Surfing the internet is allowed only with the permission of the instructor. Downloading and installing software are strictly prohibited.</li>
                        <li>4. Getting access to other websites not related to the course (especially pornographic and illicit sites) is strictly prohibited.</li>
                        <li>5. Deleting computer files and changing the setup of the computer is a major offense.</li>
                        <li>6. Observe computer time usage carefully. A fifteen-minute allowance is given for each use. Otherwise, the unit will be given to those who wish to "sit-in".</li>
                        <li>7. Observe proper decorum while inside the laboratory.
                            <ul>
                                <li>a. Do not enter the lab unless the instructor is present.</li>
                                <li>b. All bags, knapsacks, and the likes must be deposited at the counter.</li>
                                <li>c. Follow the seating arrangement of your instructor.</li>
                                <li>d. At the end of class, all software programs must be closed.</li>
                                <li>e. Return all chairs to their proper places after use.</li>
                            </ul>
                        </li>
                        <li>8. Chewing gum, eating, drinking, smoking, and other forms inside the lab are prohibited.</li>
                        <li>9. Anyone causing a continual disturbance will be asked to leave the lab.</li>
                        <li>10. Persons exhibiting hostile or threatening behavior such as yelling, swearing, or disregarding requests made by lab personnel will be asked to leave the lab.</li>
                        <li>11. For serious offenses, the lab personnel may call the Civil Security Office (CSU) for assistance.</li>
                        <li>12. Any technical problem or difficulty must be addressed to the laboratory supervisor, student assistant, or instructor immediately.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>