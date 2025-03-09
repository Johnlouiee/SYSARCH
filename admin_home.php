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

// Fetch total users
$sql = "SELECT COUNT(*) as total_users FROM users";
$result = $conn->query($sql);
$total_users = ($result->num_rows > 0) ? $result->fetch_assoc()['total_users'] : 0;

// Fetch users for list of students
$sql = "SELECT id, idno, lastname, firstname, middlename, course, year, email, role FROM users ORDER BY role ASC";
$result = $conn->query($sql);

// Handle search
$search_result = null;
if (isset($_GET['search_idno'])) {
    $search_idno = $_GET['search_idno'];
    $sql = "SELECT id, idno, lastname, firstname, middlename, course, year, email, role FROM users WHERE idno = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $search_idno);
        $stmt->execute();
        $search_result = $stmt->get_result()->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f4f4; }
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
        .container {
            padding: 20px;
        }
        h2 { margin-bottom: 20px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #4CAF50;
            color: white;
        }
        .search-bar {
            margin-bottom: 20px;
        }
        .search-bar input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .logout-btn {
            display: inline-block;
            padding: 10px 20px;
            background: red;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .logout-btn:hover {
            background: darkred;
        }
        .reset-btn {
            background: #f44336;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
        }
        .reset-btn:hover {
            background: #d32f2f;
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
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<div class="container">
    <h2>Admin Dashboard</h2>
    <p>Total Users: <strong><?= $total_users ?></strong></p>

    <!-- Search Bar -->
    <div class="search-bar">
        <form method="GET" action="">
            <input type="text" id="search" name="search_idno" placeholder="Search by IDNO..." value="<?= isset($_GET['search_idno']) ? htmlspecialchars($_GET['search_idno']) : '' ?>">
            <input type="submit" value="Search">
        </form>
    </div>

    <!-- Modal for Sit-in Form -->
    <div class="modal fade" id="sitInModal" tabindex="-1" aria-labelledby="sitInModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sitInModalLabel">Sit-in Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($search_result): ?>
                        <form action="save_sitin.php" method="POST">
                            <label for="idno">IDNO:</label>
                            <input type="text" id="idno" name="idno" value="<?= htmlspecialchars($search_result['idno']) ?>" readonly>

                            <label for="student_name">Student Name:</label>
                            <input type="text" id="student_name" name="student_name" value="<?= htmlspecialchars($search_result['firstname'] . ' ' . $search_result['lastname']) ?>" readonly>

                            <label for="purpose">Purpose:</label>
                            <input type="text" id="purpose" name="purpose" required>

                            <label for="lab">Lab:</label>
                            <input type="text" id="purpose" name="lab" required>
                          
                        

                            <label for="remaining_sessions">Remaining Sessions:</label>
                            <input type="text" id="remaining_sessions" name="remaining_sessions" value="30" readonly> <!-- Adjust this value dynamically if needed -->

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- List of Students -->
    <div class="section">
        <h3>List of Students</h3>
        <table id="students-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ID No.</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Email</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['idno']) ?></td>
                    <td><?= htmlspecialchars($row['lastname']) ?></td>
                    <td><?= htmlspecialchars($row['firstname']) ?></td>
                    <td><?= htmlspecialchars($row['middlename']) ?></td>
                    <td><?= htmlspecialchars($row['course']) ?></td>
                    <td><?= htmlspecialchars($row['year']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= ucfirst(htmlspecialchars($row['role'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <button class="reset-btn" onclick="resetSession()">Reset Session</button>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function resetSession() {
    if (confirm('Are you sure you want to reset the session?')) {
        fetch('reset_session.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reset: true })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Session has been reset.');
            } else {
                alert('Failed to reset session.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while resetting the session.');
        });
    }
}

// Show the modal if a student is found
<?php if ($search_result): ?>
    document.addEventListener('DOMContentLoaded', function () {
        var myModal = new bootstrap.Modal(document.getElementById('sitInModal'), {});
        myModal.show();
    });
<?php endif; ?>
</script>

</body>
</html>

<?php
$conn->close();
?>