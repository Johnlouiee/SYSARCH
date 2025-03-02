<?php
session_start();
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        <a href="view_current_sitin.php">View Current Sit-in</a>
        <a href="view_sitin_records.php">View Sit-in Records</a>
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


    <div class="search-bar">
        <input type="text" id="search" placeholder="Search students..." onkeyup="searchStudents()">
    </div>

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

<script>

function searchStudents() {
    const searchInput = document.getElementById('search').value.toLowerCase();
    const table = document.getElementById('students-table');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let match = false;

        for (let j = 0; j < cells.length; j++) {
            if (cells[j].innerText.toLowerCase().includes(searchInput)) {
                match = true;
                break;
            }
        }

        rows[i].style.display = match ? '' : 'none';
    }
}


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
</script>

</body>
</html>

<?php
$conn->close();
?>