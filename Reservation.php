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

// Construct full name
$full_name = trim($user_info['firstname'] . ' ' . $user_info['middlename'] . ' ' . $user_info['lastname']);

// Calculate remaining sessions
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

// Define available labs
$labs = ['Lab 524', 'Lab 526', 'Lab 542', 'Lab 544', 'Lab 517', 'Lab 528'];

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($remaining_sessions > 0) {
        $user_id = $_POST['idNumber'];
        $student_name = $_POST['studentName'];
        $purpose = $_POST['purpose'];
        $lab = $_POST['lab'];
        $pc_number = $_POST['pc_number'];
        $time_in = date('H:i:s', strtotime($_POST['timeIn']));
        $reservation_date = $_POST['date'];
        $remaining_session = $remaining_sessions;

        // Check if PC is disabled
        $check_disabled_sql = "SELECT status FROM computer_control WHERE lab_name = ? AND pc_number = ?";
        $check_disabled_stmt = $conn->prepare($check_disabled_sql);
        $check_disabled_stmt->bind_param("ss", $lab, $pc_number);
        $check_disabled_stmt->execute();
        $check_disabled_result = $check_disabled_stmt->get_result();
        $pc_status = $check_disabled_result->fetch_assoc();
        $check_disabled_stmt->close();

        if ($pc_status && $pc_status['status'] === 'offline') {
            $error_message = "This PC is currently unavailable (disabled). Please select another PC.";
        } else {
            // Check if PC is already reserved
            $check_sql = "SELECT id FROM reservations 
                          WHERE lab = ? AND pc_number = ? AND reservation_date = ? 
                          AND status IN ('Pending', 'Accepted')";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("sss", $lab, $pc_number, $reservation_date);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error_message = "This PC is already reserved for the selected date.";
            } else {
                // Insert reservation as "Pending"
                $sql = "INSERT INTO reservations 
                        (user_id, student_name, purpose, lab, pc_number, time_in, reservation_date, remaining_session, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssi", $user_id, $student_name, $purpose, $lab, $pc_number, $time_in, $reservation_date, $remaining_session);

                if ($stmt->execute()) {
                    // Get the inserted reservation ID
                    $reservation_id = $stmt->insert_id;
                    
                    // Update computer_control table
                    $update_sql = "INSERT INTO computer_control 
                                  (pc_number, lab_name, status, reservation_id, last_update)
                                  VALUES (?, ?, 'pending', ?, NOW())
                                  ON DUPLICATE KEY UPDATE
                                  status = 'pending',
                                  reservation_id = ?,
                                  last_update = NOW()";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ssii", $pc_number, $lab, $reservation_id, $reservation_id);
                    $update_stmt->execute();
                    $update_stmt->close();

                    // Create notification for admin
                    $message = "New reservation request from {$student_name}\n";
                    $message .= "Lab: {$lab}\n";
                    $message .= "PC: {$pc_number}\n";
                    $message .= "Purpose: {$purpose}\n";
                    $message .= "Date: " . date('F j, Y', strtotime($reservation_date)) . "\n";
                    $message .= "Time: " . date('g:i A', strtotime($time_in));

                    $notify_sql = "INSERT INTO notifications (user_id, message, type, reference_id, created_at) 
                                  VALUES ('admin', ?, 'reservation', ?, NOW())";
                    $notify_stmt = $conn->prepare($notify_sql);
                    $notify_stmt->bind_param("si", $message, $reservation_id);
                    $notify_stmt->execute();
                    $notify_stmt->close();

                    $success_message = "Reservation request submitted successfully! Awaiting admin approval.";
                } else {
                    $error_message = "Error: " . $stmt->error;
                }
                $stmt->close();
            }
            $check_stmt->close();
        }
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
        input, select {
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
    <script>
        function generatePCOptions() {
            const labSelect = document.getElementById('lab');
            const pcSelect = document.getElementById('pc_number');
            const selectedLab = labSelect.value;
            
            pcSelect.innerHTML = '<option value="">Select PC</option>';
            
            if (selectedLab) {
                // Fetch available PCs for the selected lab
                fetch(`get_available_pcs.php?lab=${selectedLab}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            data.pcs.forEach(pc => {
                                const option = document.createElement('option');
                                option.value = pc.pc_number;
                                option.textContent = pc.pc_number;
                                pcSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error('Error fetching available PCs:', error));
            }
        }
        
        // Set minimum date to today
        window.onload = function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('date').min = today;
        };
    </script>
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
            <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
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
        <select id="purpose" name="purpose" required>
            <option value="">Select Purpose</option>
            <option value="C#">C#</option>
            <option value="Java Programming">Java Programming</option>
            <option value="C Programming">C Programming</option>
            <option value="ASP.NET">ASP.NET</option>
        </select>

        <label for="lab">Lab:</label>
        <select id="lab" name="lab" onchange="generatePCOptions()" required>
            <option value="">Select Lab</option>
            <?php foreach ($labs as $lab): ?>
                <option value="<?= htmlspecialchars($lab) ?>"><?= htmlspecialchars($lab) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="pc_number">PC Number:</label>
        <select id="pc_number" name="pc_number" required>
            <option value="">Select Lab first</option>
        </select>

        <label for="timeIn">Time In:</label>
        <input type="time" id="timeIn" name="timeIn" required>

        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required>

        <label for="remainingSession">Remaining Session:</label>
        <input type="text" id="remainingSession" name="remainingSession" value="<?= htmlspecialchars($remaining_sessions) ?>" readonly>

        <button type="submit">Reserve</button>
    </form>
</main>
</body>
</html>