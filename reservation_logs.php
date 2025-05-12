<?php
include 'db_connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class ReservationLog {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Log reservation action
    public function logAction($reservation_id, $action, $user_id, $details) {
        $sql = "INSERT INTO reservation_logs 
                (reservation_id, action, user_id, details, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $reservation_id, $action, $user_id, $details);
        return $stmt->execute();
    }

    // Log accepted reservation
    public function logAcceptedReservation($reservation_id, $admin_id, $student_name, $lab, $pc_number, $reservation_date, $time_slot) {
        $details = "Reservation accepted for {$student_name}\n";
        $details .= "Lab: {$lab}\n";
        $details .= "PC: {$pc_number}\n";
        $details .= "Date: " . date('F j, Y', strtotime($reservation_date)) . "\n";
        $details .= "Time: " . date('g:i A', strtotime($time_slot));
        
        return $this->logAction($reservation_id, 'accepted', $admin_id, $details);
    }

    // Log declined reservation
    public function logDeclinedReservation($reservation_id, $admin_id, $student_name, $lab, $pc_number, $reservation_date, $time_slot) {
        $details = "Reservation declined for {$student_name}\n";
        $details .= "Lab: {$lab}\n";
        $details .= "PC: {$pc_number}\n";
        $details .= "Date: " . date('F j, Y', strtotime($reservation_date)) . "\n";
        $details .= "Time: " . date('g:i A', strtotime($time_slot));
        
        return $this->logAction($reservation_id, 'declined', $admin_id, $details);
    }

    // Get all logs with pagination
    public function getAllLogs($page = 1, $per_page = 20) {
        $offset = ($page - 1) * $per_page;
        $sql = "SELECT rl.*, r.student_name, r.lab, r.pc_number, r.reservation_date, r.time_in,
                       u.firstname, u.lastname 
                FROM reservation_logs rl 
                LEFT JOIN reservations r ON rl.reservation_id = r.id 
                LEFT JOIN users u ON rl.user_id = u.idno
                ORDER BY rl.created_at DESC 
                LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $per_page, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Get total number of logs
    public function getTotalLogs() {
        $sql = "SELECT COUNT(*) as total FROM reservation_logs";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc()['total'];
    }

    public function logReservationEnabled($reservationId, $adminId, $studentName, $lab, $pcNumber) {
        $details = "Reservation enabled for {$studentName} - {$lab} - {$pcNumber}";
        $this->logAction($reservationId, 'enable', $adminId, $details);
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit();
    }

    $log = new ReservationLog($conn);

    switch ($_POST['action']) {
        case 'get_logs':
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $logs = $log->getAllLogs($page);
            $total = $log->getTotalLogs();
            echo json_encode(['status' => 'success', 'logs' => $logs, 'total' => $total]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
    exit();
}

// Only display the logs page if this file is being accessed directly
if (basename($_SERVER['PHP_SELF']) === 'reservation_logs.php') {
    if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
        header('Location: login.php');
        exit();
    }
    
    // Display the logs page directly instead of including another file
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Reservation Logs</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <style>
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
            .badge-accepted {
                background-color: #28a745;
                color: white;
                padding: 5px 10px;
                border-radius: 4px;
            }
            .badge-declined {
                background-color: #dc3545;
                color: white;
                padding: 5px 10px;
                border-radius: 4px;
            }
            .log-details {
                white-space: pre-line;
            }
            .table th {
                background-color: #f8f9fa;
            }
        </style>
    </head>
    <body>
    <div class="header">
            <div>
                <h1> </h1>
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
            <h2 class="mb-4">Reservation Logs</h2>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="logsTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Action</th>
                                    <th>Student</th>
                                    <th>Lab</th>
                                    <th>PC</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Logs will be loaded here -->
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
                let logsTable = $('#logsTable').DataTable({
                    order: [[0, 'desc']],
                    columns: [
                        { 
                            data: 'created_at',
                            render: function(data) {
                                return new Date(data).toLocaleString();
                            }
                        },
                        { 
                            data: 'action',
                            render: function(data) {
                                if (data === 'accepted') {
                                    return '<span class="badge badge-accepted">Accepted</span>';
                                } else if (data === 'declined') {
                                    return '<span class="badge badge-declined">Declined</span>';
                                }
                                return data;
                            }
                        },
                        { data: 'student_name' },
                        { data: 'lab' },
                        { data: 'pc_number' },
                        { 
                            data: 'details',
                            render: function(data) {
                                return `<div class="log-details">${data}</div>`;
                            }
                        }
                    ],
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]]
                });

                // Function to load logs
                function loadLogs() {
                    $.ajax({
                        url: 'reservation_logs.php',
                        method: 'POST',
                        data: {
                            action: 'get_logs'
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                logsTable.clear();
                                logsTable.rows.add(response.logs);
                                logsTable.draw();
                            } else {
                                console.error('Failed to load logs:', response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading logs:', error);
                        }
                    });
                }

                // Load logs on page load
                loadLogs();

                // Refresh logs every 30 seconds
                setInterval(loadLogs, 30000);
            });
        </script>
    </body>
    </html>
    <?php
    exit();
}
?>