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

// Initialize or refresh session count
$session_count_sql = "SELECT sessions_remaining FROM users WHERE idno = ?";
$session_count_stmt = $conn->prepare($session_count_sql);
if ($session_count_stmt) {
    $session_count_stmt->bind_param("s", $user_info['idno']);
    $session_count_stmt->execute();
    $session_count_result = $session_count_stmt->get_result();
    $session_count_row = $session_count_result->fetch_assoc();

    $_SESSION['user_info']['sessions'] = $session_count_row['sessions_remaining'] ?? 30; // Default to 30
    $session_count_stmt->close();
}

// Start session logic
if (isset($_POST['start_session'])) {
    if ($_SESSION['user_info']['sessions'] > 0) {
        // Insert new session record
        $insert_session_sql = "INSERT INTO user_sessions (user_id, session_start) VALUES (?, NOW())";
        $insert_stmt = $conn->prepare($insert_session_sql);
        if ($insert_stmt) {
            $insert_stmt->bind_param("s", $user_info['id']);
            $insert_stmt->execute();
            $insert_stmt->close();
        } else {
            die("Error preparing statement: " . $conn->error);
        }

        // Decrement session count in database
        $decrement_sql = "UPDATE users SET sessions_remaining = sessions_remaining - 1 WHERE idno = ?";
        $decrement_stmt = $conn->prepare($decrement_sql);
        if ($decrement_stmt) {
            $decrement_stmt->bind_param("s", $user_info['idno']);
            $decrement_stmt->execute();
            $decrement_stmt->close();
        }

        // Refresh session count from the database
        $refresh_count_sql = "SELECT sessions_remaining FROM users WHERE idno = ?";
        $refresh_count_stmt = $conn->prepare($refresh_count_sql);
        if ($refresh_count_stmt) {
            $refresh_count_stmt->bind_param("s", $user_info['idno']);
            $refresh_count_stmt->execute();
            $refresh_result = $refresh_count_stmt->get_result();
            $refresh_row = $refresh_result->fetch_assoc();
            $_SESSION['user_info']['sessions'] = $refresh_row['sessions_remaining'];
            $refresh_count_stmt->close();
        }

        $_SESSION['session_started'] = true;
        $_SESSION['session_start_time'] = time();
    } else {
        $error = "No sessions remaining.";
    }
}

// End session logic
if (isset($_POST['end_session'])) {
    if (isset($_SESSION['session_started']) && $_SESSION['session_started']) {
        // Update session_end timestamp
        $update_session_sql = "UPDATE user_sessions 
                               SET session_end = NOW() 
                               WHERE user_id = ? 
                               AND session_end IS NULL 
                               ORDER BY session_start DESC 
                               LIMIT 1";
        $update_stmt = $conn->prepare($update_session_sql);
        if ($update_stmt) {
            $update_stmt->bind_param("s", $user_info['id']);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            die("Error preparing statement: " . $conn->error);
        }

        // Update session variables
        $_SESSION['session_started'] = false;
        $session_duration = time() - $_SESSION['session_start_time'];
    } else {
        $error = "No active session to end.";
    }
}

$idno = htmlspecialchars($_SESSION['idno']);
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

        .session-buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .session-buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .session-buttons button.start {
            background-color: #4CAF50;
            color: white;
        }

        .session-buttons button.end {
            background-color: #f44336;
            color: white;
        }

        .session-buttons button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
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
        </div>
        <div>
            <span>Welcome, <?php echo $idno; ?>!</span>
            <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
        </div>
    </div>

    <div class="dashboard">
        <div class="user-section">
            <div class="card">
                <img src="ok.jpg">
            </div>
            <div class="user-info-container">
                <div class="user-info">
                    <p><strong>IDNO:</strong> <?php echo $user_info['idno']; ?></p>
                    <p><strong>Name:</strong> <?php echo $user_info['firstname'] . ' ' . $user_info['lastname']; ?></p>
                    <p><strong>Course:</strong> <?php echo $user_info['course']; ?></p>
                    <p><strong>Year Level:</strong> <?php echo $user_info['year']; ?></p>
                    <p><strong>Email:</strong> <?php echo $user_info['email']; ?></p>
                    <p><strong>Sessions Remaining:</strong> <?php echo $_SESSION['user_info']['sessions']; ?></p>
                </div>
                <div class="session-buttons">
                    <form method="POST" action="home.php">
                        <button type="submit" name="start_session" class="start" <?php echo ($_SESSION['user_info']['sessions'] <= 0 || isset($_SESSION['session_started'])) ? 'disabled' : ''; ?>>Start Session</button>
                        <button type="submit" name="end_session" class="end" <?php echo !isset($_SESSION['session_started']) ? 'disabled' : ''; ?>>End Session</button>
                    </form>
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
                        <li>9. Anyone causing a continual disturbance will be asked to leave the lab. Acts or gestures.</li>
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