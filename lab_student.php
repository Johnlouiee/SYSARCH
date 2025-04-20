<?php
session_start();
if (!isset($_SESSION['user_info'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Get only ACTIVE resources (is_active = 1)
$sql = "SELECT * FROM lab_resources WHERE is_active = 1 ORDER BY uploaded_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Resources - Student View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .resource-card {
            transition: all 0.3s ease;
        }
        .resource-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .file-icon {
            font-size: 2.5rem;
            color: #0d6efd;
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
        <a href="view_announcements.php">View Announcement</a>
        <a href="reservation.php">Reservation</a>
        <a href="sitin.php">Sit-In History</a>
        <a href="lab_student.php">Lab Resources</a>
    </div>
    <div>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-microscope me-2"></i>Lab Resources</h2>
    
    <?php if ($result->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($resource = $result->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card resource-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-3">
                                <i class="fas fa-<?= 
                                    match(strtolower($resource['file_type'])) {
                                        'pdf' => 'file-pdf',
                                        'doc','docx' => 'file-word',
                                        'ppt','pptx' => 'file-powerpoint',
                                        'xls','xlsx' => 'file-excel',
                                        'jpg','jpeg','png' => 'file-image',
                                        'zip','rar' => 'file-archive',
                                        default => 'file'
                                    } 
                                ?> file-icon me-3"></i>
                                <div>
                                    <h5><?= htmlspecialchars($resource['title']) ?></h5>
                                    <?php if ($resource['description']): ?>
                                        <p class="text-muted"><?= htmlspecialchars($resource['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if (in_array(strtolower($resource['file_type']), ['jpg','jpeg','png'])): ?>
                                <img src="<?= htmlspecialchars($resource['file_path']) ?>" 
                                     class="img-fluid rounded mb-3" 
                                     style="max-height: 150px; object-fit: contain;">
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <?= strtoupper($resource['file_type']) ?> • 
                                    <?= round($resource['file_size'] / 1024) ?> KB
                                </small>
                                <small class="text-muted">
                                    <?= date('M j, Y', strtotime($resource['uploaded_at'])) ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <a href="<?= htmlspecialchars($resource['file_path']) ?>" 
                               class="btn btn-primary w-100" 
                               download="<?= htmlspecialchars($resource['file_name']) ?>">
                               <i class="fas fa-download me-2"></i>Download
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No lab resources available at the moment.
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>