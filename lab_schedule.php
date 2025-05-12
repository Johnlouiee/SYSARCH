<?php
session_start();


include 'db_connect.php';

// Handle all actions in one file
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file upload
    if (isset($_FILES['schedule_file'])) {
        $uploadDir = 'Uploads/lab_schedules/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = basename($_FILES['schedule_file']['name']);
        $filePath = $uploadDir . uniqid() . '_' . $fileName;
        $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        if (move_uploaded_file($_FILES['schedule_file']['tmp_name'], $filePath)) {
            $title = $_POST['title'] ?? 'Lab Schedule';
            $description = $_POST['description'] ?? '';
            $startDate = $_POST['start_date'];
            $endDate = $_POST['end_date'];
            
            $sql = "INSERT INTO lab_schedules (title, description, file_name, file_path, file_type, file_size, uploaded_by, schedule_date, schedule_end_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssisss", $title, $description, $fileName, $filePath, $fileType, $_FILES['schedule_file']['size'], $_SESSION['user_info']['idno'], $startDate, $endDate);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Schedule uploaded successfully!";
            } else {
                $_SESSION['error'] = "Database error: " . $conn->error;
                unlink($filePath); // Clean up if DB fails
            }
        } else {
            $_SESSION['error'] = "Error uploading file";
        }
        header("Location: lab_schedule.php");
        exit();
    }
    // Handle toggle status
    elseif (isset($_POST['toggle_status'])) {
        $id = $_POST['id'];
        $sql = "UPDATE lab_schedules SET is_active = NOT is_active WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: lab_schedule.php");
        exit();
    }
    // Handle delete
    elseif (isset($_POST['delete_schedule'])) {
        $id = $_POST['id'];
        // Get file path first
        $stmt = $conn->prepare("SELECT file_path FROM lab_schedules WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $schedule = $result->fetch_assoc();
        
        // Delete record
        $stmt = $conn->prepare("DELETE FROM lab_schedules WHERE id = ?");
        if ($stmt->bind_param("i", $id) && $stmt->execute()) {
            // Only delete file if DB deletion succeeded
            if ($schedule && file_exists($schedule['file_path'])) {
                unlink($schedule['file_path']);
            }
            $_SESSION['success'] = "Schedule deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting schedule";
        }
        header("Location: lab_schedule.php");
        exit();
    }
}

// Get all schedules
$schedules = $conn->query("SELECT * FROM lab_schedules ORDER BY schedule_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lab Schedule Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
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
        .header a:hover {
            text-decoration: underline;
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
    </style>
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
    <h2><i class="fas fa-calendar-alt"></i> Lab Schedule Management</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <h4>Upload New Schedule</h4>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Schedule File (PDF/Image)</label>
                    <input type="file" name="schedule_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Upload Schedule
                </button>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h4>Current Schedules</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Date Range</th>
                            <th>File</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($schedule = $schedules->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($schedule['title']) ?></td>
                            <td>
                                <?= date('M j, Y', strtotime($schedule['schedule_date'])) ?> - 
                                <?= date('M j, Y', strtotime($schedule['schedule_end_date'])) ?>
                            </td>
                            <td>
                                <a href="<?= htmlspecialchars($schedule['file_path']) ?>" target="_blank">
                                    <?= htmlspecialchars($schedule['file_name']) ?>
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-<?= $schedule['is_active'] ? 'success' : 'secondary' ?>">
                                    <?= $schedule['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="id" value="<?= $schedule['id'] ?>">
                                    <button type="submit" name="toggle_status" class="btn btn-sm btn-<?= $schedule['is_active'] ? 'warning' : 'success' ?>">
                                        <?= $schedule['is_active'] ? 'Disable' : 'Enable' ?>
                                    </button>
                                </form>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this schedule?');">
                                    <input type="hidden" name="id" value="<?= $schedule['id'] ?>">
                                    <button type="submit" name="delete_schedule" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>