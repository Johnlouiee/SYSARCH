<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['idno'])) {
    header("Location: index.php");
    exit();
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'get_notifications':
            $sql = "SELECT * FROM notifications 
                    WHERE user_id = ? AND type IN ('acceptance', 'decline')
                    ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $_SESSION['idno']);
            $stmt->execute();
            $result = $stmt->get_result();
            $notifications = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['status' => 'success', 'notifications' => $notifications]);
            break;

        case 'mark_as_read':
            if (isset($_POST['notification_id'])) {
                $sql = "UPDATE notifications SET is_read = 1 
                        WHERE id = ? AND user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $_POST['notification_id'], $_SESSION['idno']);
                $result = $stmt->execute();
                echo json_encode(['status' => $result ? 'success' : 'error']);
            }
            break;

        case 'mark_all_as_read':
            $sql = "UPDATE notifications SET is_read = 1 
                    WHERE user_id = ? AND type IN ('acceptance', 'decline')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $_SESSION['idno']);
            $result = $stmt->execute();
            echo json_encode(['status' => $result ? 'success' : 'error']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .notification-card {
            margin-bottom: 1rem;
            border-left: 4px solid #007bff;
        }
        .notification-card.unread {
            background-color: #f8f9fa;
        }
        .search-container {
            margin-bottom: 1rem;
        }
        .badge-accepted {
            background-color: #28a745;
        }
        .badge-declined {
            background-color: #dc3545;
        }
        

        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .nav-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        
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
    </style>
</head>
<body>
<div class="header">
<div>
            <a href="home.php">Home</a>
            <a href="reports.php">Reports</a>
            <a href="editprofile.php">Edit Profile</a>
            <a href="view_announcements.php">Announcements</a>
            <a href="reservation.php">Reservation</a>
            <a href="sitin.php">Sit-In History</a>
            <a href="lab_schedule_student.php">Lab Schedules</a>
            <a href="view_points.php">View Points</a>
            <a href="student_notification.php">Notifications</a>
            <a href="lab_student.php">Lab Resources</a>
        </div>
        <div>
        <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
    </div>
    </div>
</div>

<div class="container mt-4">
    <h2 class="mb-4">My Notifications</h2>
    
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="search-container">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search notifications...">
                </div>
                <button id="markAllRead" class="btn btn-primary">Mark All as Read</button>
            </div>

            <div class="table-responsive">
                <table id="notificationsTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Message</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Notifications will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        let notificationsTable = $('#notificationsTable').DataTable({
            order: [[0, 'desc']],
            columns: [
                { 
                    data: 'created_at',
                    render: function(data) {
                        return new Date(data).toLocaleString();
                    }
                },
                { data: 'message' },
                { 
                    data: 'type',
                    render: function(data) {
                        if (data === 'acceptance') {
                            return '<span class="badge badge-accepted">Accepted</span>';
                        } else if (data === 'decline') {
                            return '<span class="badge badge-declined">Declined</span>';
                        }
                        return data;
                    }
                },
                { 
                    data: 'is_read',
                    render: function(data) {
                        return data == 0 ? '<span class="badge bg-warning">Unread</span>' : '<span class="badge bg-success">Read</span>';
                    }
                },
                {
                    data: null,
                    render: function(data) {
                        return data.is_read == 0 ? 
                            `<button class="btn btn-sm btn-primary mark-read" data-id="${data.id}">Mark as Read</button>` : 
                            '';
                    }
                }
            ]
        });

        // Function to load notifications
        function loadNotifications() {
            $.ajax({
                url: 'student_notification.php',
                method: 'POST',
                data: {
                    action: 'get_notifications'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        notificationsTable.clear();
                        notificationsTable.rows.add(response.notifications);
                        notificationsTable.draw();
                    } else {
                        console.error('Failed to load notifications:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading notifications:', error);
                }
            });
        }

        // Load notifications on page load
        loadNotifications();

        // Mark single notification as read
        $('#notificationsTable').on('click', '.mark-read', function() {
            const notificationId = $(this).data('id');
            $.ajax({
                url: 'student_notification.php',
                method: 'POST',
                data: {
                    action: 'mark_as_read',
                    notification_id: notificationId
                },
                success: function(response) {
                    if (response.status === 'success') {
                        loadNotifications();
                    }
                }
            });
        });

        // Mark all notifications as read
        $('#markAllRead').click(function() {
            $.ajax({
                url: 'student_notification.php',
                method: 'POST',
                data: {
                    action: 'mark_all_as_read'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        loadNotifications();
                    }
                }
            });
        });

        // Search functionality
        $('#searchInput').on('keyup', function() {
            notificationsTable.search(this.value).draw();
        });
    });
</script>
</body>
</html>