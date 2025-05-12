<?php
session_start();
if (!isset($_SESSION['user_info'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Get active schedules
$currentDate = date('Y-m-d');
$schedules = $conn->query("SELECT * FROM lab_schedules 
                          WHERE is_active = 1 
                          AND schedule_end_date >= '$currentDate'
                          ORDER BY schedule_date ASC");

// Get available lab resources
$resources = $conn->query("SELECT * FROM lab_resources WHERE is_active = 1 ORDER BY uploaded_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lab Schedules & Resources</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        }
        .logout-btn:hover {
            background: darkred;
        }
        .resource-card {
            transition: transform 0.2s;
        }
        .resource-card:hover {
            transform: translateY(-5px);
        }
        .schedule-card {
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .schedule-card:hover {
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
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
            <a href="lab_schedule_student.php">Lab Schudles</a>
            <a href="view_points.php">View Points</a>
            <a href="lab_student.php">Lab Resources</a>
        </div>
        <div>
        <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
    </div>
    </div>

<div class="container mt-4">
    <!-- Lab Schedules -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4><i class="fas fa-calendar-alt"></i> Lab Schedules</h4>
        </div>
        <div class="card-body">
            <?php if ($schedules->num_rows > 0): ?>
                <div class="row">
                    <?php while ($schedule = $schedules->fetch_assoc()): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card schedule-card h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5><?= htmlspecialchars($schedule['title']) ?></h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($schedule['description']): ?>
                                        <p class="card-text"><?= htmlspecialchars($schedule['description']) ?></p>
                                    <?php endif; ?>
                                    
                                    <p class="text-muted">
                                        <i class="fas fa-calendar"></i> 
                                        <?= date('F j, Y', strtotime($schedule['schedule_date'])) ?> - 
                                        <?= date('F j, Y', strtotime($schedule['schedule_end_date'])) ?>
                                    </p>
                                    
                                    <?php if (in_array(strtolower($schedule['file_type']), ['jpg','jpeg','png'])): ?>
                                        <img src="<?= htmlspecialchars($schedule['file_path']) ?>" 
                                             class="img-fluid mb-3" 
                                             style="max-height: 300px; width: 100%; object-fit: cover;">
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer">
                                    <a href="<?= htmlspecialchars($schedule['file_path']) ?>" 
                                       class="btn btn-primary w-100" 
                                       download="<?= htmlspecialchars($schedule['file_name']) ?>">
                                       <i class="fas fa-download"></i> Download Schedule
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No active lab schedules available at this time.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Lab Resources -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4><i class="fas fa-book"></i> Lab Resources</h4>
        </div>
        <div class="card-body">
            <?php if ($resources->num_rows > 0): ?>
                <div class="row">
                    <?php while ($resource = $resources->fetch_assoc()): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card resource-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-file"></i> <?= htmlspecialchars($resource['title']) ?>
                                    </h5>
                                    <?php if ($resource['description']): ?>
                                        <p class="card-text"><?= htmlspecialchars($resource['description']) ?></p>
                                    <?php endif; ?>
                                    <p class="text-muted">
                                        <small>
                                            <i class="fas fa-file-alt"></i> <?= strtoupper($resource['file_type']) ?>
                                            <br>
                                            <i class="fas fa-clock"></i> <?= date('M j, Y', strtotime($resource['uploaded_at'])) ?>
                                        </small>
                                    </p>
                                </div>
                                <div class="card-footer">
                                    <a href="<?= htmlspecialchars($resource['file_path']) ?>" 
                                       class="btn btn-primary w-100" 
                                       download="<?= htmlspecialchars($resource['file_name']) ?>">
                                       <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No lab resources available at this time.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>