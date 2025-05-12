<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

class AdminNotification {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Get all notifications for admin
    public function getAdminNotifications() {
        $sql = "SELECT n.*, 
                       u.firstname, 
                       u.lastname,
                       r.student_name,
                       r.lab,
                       r.pc_number,
                       r.purpose,
                       r.reservation_date,
                       r.time_in
                FROM notifications n 
                LEFT JOIN users u ON n.user_id = u.idno 
                LEFT JOIN reservations r ON n.reference_id = r.id
                WHERE n.type = 'reservation' 
                ORDER BY n.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return [];
        }
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return [];
        }
        
        $result = $stmt->get_result();
        if (!$result) {
            error_log("Get result failed: " . $stmt->error);
            return [];
        }
        
        $notifications = $result->fetch_all(MYSQLI_ASSOC);
        
        // Format the notifications to include proper student information
        foreach ($notifications as &$notification) {
            // If we have reservation data, use that for student name
            if (!empty($notification['student_name'])) {
                $notification['student_display_name'] = $notification['student_name'];
            } 
            // Otherwise use the user table data
            else if (!empty($notification['firstname']) || !empty($notification['lastname'])) {
                $notification['student_display_name'] = trim($notification['firstname'] . ' ' . $notification['lastname']);
            }
            // If no name is available, use the user_id
            else {
                $notification['student_display_name'] = $notification['user_id'];
            }
        }
        
        return $notifications;
    }

    // Get unread notification count
    public function getUnreadCount() {
        $sql = "SELECT COUNT(*) as count FROM notifications 
                WHERE type = 'reservation' AND is_read = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'];
    }

    // Mark notification as read
    public function markAsRead($notification_id) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $notification_id);
        return $stmt->execute();
    }

    // Mark all notifications as read
    public function markAllAsRead() {
        $sql = "UPDATE notifications SET is_read = 1 WHERE type = 'reservation'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute();
    }

    // Create notification for admin when new reservation is made
    public function createReservationNotification($reservation_id, $student_name, $lab, $pc_number, $purpose, $reservation_date, $time_slot) {
        $message = "New reservation request from {$student_name}\n";
        $message .= "Lab: {$lab}\n";
        if ($pc_number) {
            $message .= "PC: {$pc_number}\n";
        }
        $message .= "Purpose: {$purpose}\n";
        $message .= "Date: " . date('F j, Y', strtotime($reservation_date)) . "\n";
        $message .= "Time: " . date('g:i A', strtotime($time_slot));
        
        $sql = "INSERT INTO notifications (user_id, message, type, reference_id, created_at) 
                VALUES ('admin', ?, 'reservation', ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $message, $reservation_id);
        return $stmt->execute();
    }

    // Create notification for student when reservation is accepted/declined
    public function createStudentNotification($student_id, $message, $type, $reservation_id) {
        $sql = "INSERT INTO notifications (user_id, message, type, reference_id, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", $student_id, $message, $type, $reservation_id);
        return $stmt->execute();
    }
}

// Only check for admin role if this file is being accessed directly
if (basename($_SERVER['PHP_SELF']) === 'admin_notification.php') {
    if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
        header('Location: index.php');
        exit();
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit();
    }

    $notification = new AdminNotification($conn);

    switch ($_POST['action']) {
        case 'get_notifications':
            $notifications = $notification->getAdminNotifications();
            echo json_encode(['status' => 'success', 'notifications' => $notifications]);
            break;

        case 'get_unread_count':
            $count = $notification->getUnreadCount();
            echo json_encode(['status' => 'success', 'count' => $count]);
            break;

        case 'mark_as_read':
            if (isset($_POST['notification_id'])) {
                $result = $notification->markAsRead($_POST['notification_id']);
                echo json_encode(['status' => $result ? 'success' : 'error']);
            }
            break;

        case 'mark_all_as_read':
            $result = $notification->markAllAsRead();
            echo json_encode(['status' => $result ? 'success' : 'error']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
    exit();
}

// Only display the notification page if this file is being accessed directly
if (basename($_SERVER['PHP_SELF']) === 'admin_notification.php') {
    // Display the notification page HTML here
    // ... rest of the HTML code ...
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Notifications</title>
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
        .badge-reservation {
            background-color: #17a2b8;
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
    <div class="container mt-4">
        <h2 class="mb-4">Reservation Requests</h2>
        
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
                                <th>Student</th>
                                <th>Message</th>
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
                    { 
                        data: 'student_display_name'
                    },
                    { data: 'message' },
                    { 
                        data: 'is_read',
                        render: function(data) {
                            return data == 0 ? '<span class="badge bg-warning">Unread</span>' : '<span class="badge bg-success">Read</span>';
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            let buttons = '';
                            if (data.is_read == 0) {
                                buttons += `<button class="btn btn-sm btn-primary mark-read me-2" data-id="${data.id}">Mark as Read</button>`;
                            }
                            if (data.reference_id) {
                                buttons += `<a href="view_reservation.php?id=${data.reference_id}" class="btn btn-sm btn-info">View Details</a>`;
                            }
                            return buttons;
                        }
                    }
                ]
            });

            // Function to load notifications
            function loadNotifications() {
                $.ajax({
                    url: 'admin_notification.php',
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
                    url: 'admin_notification.php',
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
                    url: 'admin_notification.php',
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