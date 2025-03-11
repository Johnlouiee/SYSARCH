<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

$student = null;
$error = null;

// Error messages array
$error_messages = [
    'missing_fields' => 'Please fill in all required fields',
    'student_not_found' => 'Student not found',
    'active_session_exists' => 'This student already has an active sit-in session',
    'database_error' => 'Database error occurred',
    'prepare_error' => 'Error preparing database query',
    'invalid_method' => 'Invalid request method'
];

// Get error message if exists
if (isset($_GET['error']) && isset($error_messages[$_GET['error']])) {
    $error = $error_messages[$_GET['error']];
}

// Add success message if coming back from successful sit-in
if (isset($_GET['success'])) {
    $success = "Sit-in recorded successfully!";
}

if (isset($_GET['search_idno'])) {
    $search_idno = mysqli_real_escape_string($conn, $_GET['search_idno']);
    
    // Search for the student
    $sql = "SELECT idno, firstname, lastname FROM users WHERE idno = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $search_idno);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();
            
            // Get total sessions used
            $sessions_sql = "SELECT COUNT(*) as total_sessions FROM sit_in_history WHERE user_id = ?";
            $sessions_stmt = $conn->prepare($sessions_sql);
            $sessions_stmt->bind_param("s", $search_idno);
            $sessions_stmt->execute();
            $sessions_result = $sessions_stmt->get_result();
            $sessions_data = $sessions_result->fetch_assoc();
            
            // Calculate remaining sessions (max 30)
            $max_sessions = 30;
            $used_sessions = $sessions_data['total_sessions'];
            $remaining = $max_sessions - $used_sessions;
            $remaining = max(0, $remaining);
            
            $student['remaining_sessions'] = $remaining;

            // Check for active session
            $active_sql = "SELECT id FROM sit_in_history WHERE user_id = ? AND session_end IS NULL";
            $active_stmt = $conn->prepare($active_sql);
            $active_stmt->bind_param("s", $search_idno);
            $active_stmt->execute();
            $active_result = $active_stmt->get_result();

            if ($active_result->num_rows > 0) {
                $error = "This student already has an active sit-in session";
                $student = null;
            }
        } else {
            $error = "Student not found";
        }
    } else {
        $error = "Database error occurred";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .btn-primary {
            background-color: #4CAF50;
            border-color: #4CAF50;
        }
        .btn-primary:hover {
            background-color: #45a049;
            border-color: #45a049;
        }
        .alert {
            margin-bottom: 20px;
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
    </div>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

    <div class="container">
        <h2>Search Student</h2>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Search Form -->
        <form method="GET" action="" class="mb-4">
            <div class="form-group">
                <label for="search_idno">Student ID Number:</label>
                <input type="text" class="form-control" id="search_idno" name="search_idno" value="<?= isset($_GET['search_idno']) ? htmlspecialchars($_GET['search_idno']) : '' ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <?php if ($student): ?>
            <!-- Sit-in Form -->
            <form id="sitInForm" action="save_sitin.php" method="POST">
                <h3>Student Information</h3>
                <div class="form-group">
                    <label for="idno">ID Number:</label>
                    <input type="text" class="form-control" id="idno" name="idno" value="<?= htmlspecialchars($student['idno']) ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" class="form-control" id="name" value="<?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']) ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="purpose">Purpose:</label>
                      <input type="text" class="form-control" id="purpose" name="purpose" required>
                </div>
                <div class="form-group">
                    <label for="lab">Lab:</label>
                     <input type="text" class="form-control" id="lab" name ="lab" required>
                </div>
                <div class="form-group">
                    <label for="remaining_sessions">Remaining Sessions:</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="remaining_sessions" value="<?= $student['remaining_sessions'] ?>" readonly>
                        <span class="input-group-text">/ 30</span>
                    </div>
                    <small class="text-muted">Maximum 30 sessions allowed</small>
                </div>
                <button type="submit" class="btn btn-primary">Submit Sit-in</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        // Handle purpose selection
        document.getElementById("purpose")?.addEventListener("change", function() {
            const otherPurposeDiv = document.getElementById("otherPurposeDiv");
            const otherPurpose = document.getElementById("otherPurpose");
            
            if (this.value === "Others") {
                otherPurposeDiv.style.display = "block";
                otherPurpose.required = true;
            } else {
                otherPurposeDiv.style.display = "none";
                otherPurpose.required = false;
            }
        });

        // Form validation
        document.getElementById("sitInForm")?.addEventListener("submit", function(event) {
            const purpose = document.getElementById("purpose").value;
            const lab = document.getElementById("lab").value;
            
            if (!purpose || !lab) {
                event.preventDefault();
                alert("Please fill in all required fields");
                return;
            }

            if (purpose === "Others") {
                const otherPurpose = document.getElementById("otherPurpose").value.trim();
                if (!otherPurpose) {
                    event.preventDefault();
                    alert("Please specify the other purpose");
                    return;
                }
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>