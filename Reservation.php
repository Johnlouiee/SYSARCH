<?php
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => true,
    'cookie_httponly' => true,
]);

include 'db_connect.php';

if (!isset($_SESSION['idno'])) {
    header("Location: index.php");
    exit();
}

session_regenerate_id(true);

// Fetch user information
$idno = $_SESSION['idno'];
$sql = "SELECT * FROM users WHERE idno = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $idno);
$stmt->execute();
$result = $stmt->get_result();
$user_info = $result->fetch_assoc();
$stmt->close();

// Construct full name (firstname middlename lastname)
$full_name_parts = [
    $user_info['firstname'],
    $user_info['middlename'],
    $user_info['lastname']
];
$full_name = implode(' ', array_filter($full_name_parts));

// Calculate remaining sessions based on sit_in_history (same as home.php)
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

$max_sessions = 30;
$remaining_sessions = max(0, $max_sessions - $completed_sessions);

// Update session info
$_SESSION['user_info']['sessions'] = $remaining_sessions;

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($remaining_sessions > 0) {
        $user_id = $_POST['idNumber'];
        $student_name = $_POST['studentName'];
        $purpose = $_POST['purpose'];
        $lab = $_POST['lab'];
        $time_in = date('H:i:s', strtotime($_POST['timeIn']));
        $reservation_date = $_POST['date'];
        $remaining_session = $remaining_sessions;

        // Insert reservation as "Pending"
        $sql = "INSERT INTO reservations (user_id, student_name, purpose, lab, time_in, reservation_date, remaining_session, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $user_id, $student_name, $purpose, $lab, $time_in, $reservation_date, $remaining_session);

        if ($stmt->execute()) {
            $success_message = "Reservation request submitted successfully! Awaiting admin approval.";
        } else {
            $error_message = "Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error_message = "No sessions remaining.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        header {
            background-color: #333;
            color: white;
            padding: 10px 0;
        }
        nav ul {
            list-style: none;
            text-align: right;
            margin: 0;
            padding: 0;
        }
        nav ul li {
            display: inline;
            margin-right: 20px;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
        }
        main {
            margin: 20px auto;
            padding: 20px;
            width: 400px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
        }
        input {
            margin-top: 5px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            margin-top: 15px;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
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
        <a href="home.php">Home</a>
        <a href="reports.php">Reports</a>
        <a href="editprofile.php">Edit Profile</a>
        <a href="view_announcements.php">View Announcement</a>
        <a href="reservation.php">Reservation</a>
        <a href="sitin.php">Sit-In History</a>
    </div>
</div>

<main>
    <h1>Reservation</h1>
    <?php if ($success_message): ?>
        <div class="message success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="message error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    <form action="reservation.php" method="post">
        <label for="idNumber">ID Number:</label>
        <input type="text" id="idNumber" name="idNumber" value="<?= htmlspecialchars($user_info['idno'] ?? '') ?>" readonly>

        <label for="studentName">Student Name:</label>
        <input type="text" id="studentName" name="studentName" value="<?= htmlspecialchars($full_name) ?>" readonly>

        <label for="purpose">Purpose:</label>
        <input type="text" id="purpose" name="purpose" required>

        <label for="lab">Lab:</label>
        <input type="text" id="lab" name="lab" required>

        <label for="timeIn">Time In:</label>
        <input type="text" id="timeIn" name="timeIn" placeholder="hh:mm" required>

        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required>

        <label for="remainingSession">Remaining Session:</label>
        <input type="text" id="remainingSession" name="remainingSession" value="<?= htmlspecialchars($remaining_sessions) ?>" readonly>

        <button type="submit">Reserve</button>
    </form>
</main>
</body>
</html>