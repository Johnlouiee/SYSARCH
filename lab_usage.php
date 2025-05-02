<?php
session_start();
if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Handle manual points adjustment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adjust_points'])) {
    $student_id = $_POST['student_id'];
    $points = (int)$_POST['points'];
    $reason = $_POST['reason'];
    
    // Update rewards
    $conn->query("INSERT INTO student_rewards (student_id, total_points, free_sessions_available) 
                 VALUES ('$student_id', $points, FLOOR($points/3))
                 ON DUPLICATE KEY UPDATE 
                 total_points = VALUES(total_points),
                 free_sessions_available = VALUES(free_sessions_available)");
    
    // Record transaction
    $admin_id = $_SESSION['user_info']['idno'];
    $conn->query("INSERT INTO reward_transactions 
                 (student_id, points_change, transaction_type, description, admin_id) 
                 VALUES ('$student_id', $points, 'admin_adjust', '$reason', '$admin_id')");
    
    $_SESSION['success'] = "Points adjusted successfully!";
    header("Location: admin_lab_usage_rewards.php");
    exit();
}

// Handle free session redemption
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redeem_session'])) {
    $student_id = $_POST['student_id'];
    
    // Verify student has available sessions
    $result = $conn->query("SELECT free_sessions_available FROM student_rewards WHERE student_id = '$student_id'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['free_sessions_available'] > 0) {
            // Deduct one free session
            $conn->query("UPDATE student_rewards 
                         SET free_sessions_available = free_sessions_available - 1 
                         WHERE student_id = '$student_id'");
            
            // Record transaction
            $admin_id = $_SESSION['user_info']['idno'];
            $conn->query("INSERT INTO reward_transactions 
                         (student_id, points_change, transaction_type, description, admin_id) 
                         VALUES ('$student_id', -3, 'redeem', 'Redeemed free lab session', '$admin_id')");
            
            $_SESSION['success'] = "Free session redeemed successfully!";
        } else {
            $_SESSION['error'] = "Student has no available free sessions";
        }
    } else {
        $_SESSION['error'] = "Student not found in rewards system";
    }
    header("Location: admin_lab_usage_rewards.php");
    exit();
}

// Get all lab usage data
$usage_data = $conn->query("
    SELECT u.*, s.firstname, s.lastname 
    FROM lab_usage u
    LEFT JOIN users s ON u.student_id = s.idno
    ORDER BY u.session_start DESC
    LIMIT 100
");

// Get all rewards data
$rewards_data = $conn->query("
    SELECT r.*, s.firstname, s.lastname 
    FROM student_rewards r
    LEFT JOIN users s ON r.student_id = s.idno
    ORDER BY r.total_points DESC
");

// Get recent transactions
$transactions = $conn->query("
    SELECT t.*, s.firstname, s.lastname 
    FROM reward_transactions t
    LEFT JOIN users s ON t.student_id = s.idno
    ORDER BY t.created_at DESC
    LIMIT 20
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Lab Usage & Rewards</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card-header {
            font-weight: bold;
        }
        .badge-points {
            background-color: #ffc107;
            color: #212529;
        }
        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }
        .nav-pills .nav-link.active {
            background-color: #0d6efd;
        }
    </style>
</head>
<body>
<div class="container-fluid mt-3">
    <h2><i class="fas fa-chart-line"></i> Lab Usage & Reward System</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="usage-tab" data-bs-toggle="tab" data-bs-target="#usage" type="button">
                <i class="fas fa-desktop"></i> Lab Usage
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="rewards-tab" data-bs-toggle="tab" data-bs-target="#rewards" type="button">
                <i class="fas fa-trophy"></i> Reward System
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="transactions-tab" data-bs-toggle="tab" data-bs-target="#transactions" type="button">
                <i class="fas fa-exchange-alt"></i> Transactions
            </button>
        </li>
    </ul>
    
    <div class="tab-content p-3 border border-top-0 rounded-bottom">
        <!-- Lab Usage Tab -->
        <div class="tab-pane fade show active" id="usage" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-list"></i> Recent Lab Usage
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Lab</th>
                                <th>Session Start</th>
                                <th>Session End</th>
                                <th>Duration</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $usage_data->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></td>
                                <td><?= htmlspecialchars($row['lab_name']) ?></td>
                                <td><?= date('M j, Y g:i A', strtotime($row['session_start'])) ?></td>
                                <td><?= $row['session_end'] ? date('M j, Y g:i A', strtotime($row['session_end'])) : 'Active' ?></td>
                                <td><?= $row['duration_minutes'] ? $row['duration_minutes'] . ' mins' : '-' ?></td>
                                <td><span class="badge badge-points">+<?= $row['points_earned'] ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Reward System Tab -->
        <div class="tab-pane fade" id="rewards" role="tabpanel">
            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <i class="fas fa-trophy"></i> Student Rewards
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Total Points</th>
                                        <th>Free Sessions</th>
                                        <th>Progress</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $rewards_data->fetch_assoc()): 
                                        $progress = ($row['total_points'] % 3) * 33.33;
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></td>
                                        <td><span class="badge badge-points"><?= $row['total_points'] ?></span></td>
                                        <td><?= $row['free_sessions_available'] ?></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-warning" 
                                                     role="progressbar" 
                                                     style="width: <?= $progress ?>%" 
                                                     aria-valuenow="<?= $progress ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <?= ($row['total_points'] % 3) ?>/3
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                                    data-bs-target="#adjustPointsModal" 
                                                    data-student-id="<?= $row['student_id'] ?>"
                                                    data-student-name="<?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?>">
                                                <i class="fas fa-edit"></i> Adjust
                                            </button>
                                            <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                    data-bs-target="#redeemSessionModal"
                                                    data-student-id="<?= $row['student_id'] ?>"
                                                    data-student-name="<?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?>"
                                                    <?= $row['free_sessions_available'] < 1 ? 'disabled' : '' ?>>
                                                <i class="fas fa-gift"></i> Redeem
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <i class="fas fa-plus-circle"></i> Quick Actions
                        </div>
                        <div class="card-body">
                            <h5>Manual Points Adjustment</h5>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Student ID</label>
                                    <input type="text" name="student_id" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Points (+/-)</label>
                                    <input type="number" name="points" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Reason</label>
                                    <textarea name="reason" class="form-control" required></textarea>
                                </div>
                                <button type="submit" name="adjust_points" class="btn btn-primary w-100">
                                    <i class="fas fa-save"></i> Save Adjustment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Transactions Tab -->
        <div class="tab-pane fade" id="transactions" role="tabpanel">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <i class="fas fa-history"></i> Recent Transactions
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Student</th>
                                <th>Type</th>
                                <th>Points</th>
                                <th>Description</th>
                                <th>Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $transactions->fetch_assoc()): ?>
                            <tr>
                                <td><?= date('M j, Y g:i A', strtotime($row['created_at'])) ?></td>
                                <td><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></td>
                                <td><?= ucfirst(str_replace('_', ' ', $row['transaction_type'])) ?></td>
                                <td class="<?= $row['points_change'] > 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= $row['points_change'] > 0 ? '+' : '' ?><?= $row['points_change'] ?>
                                </td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= $row['admin_id'] ?? 'System' ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Adjust Points Modal -->
<div class="modal fade" id="adjustPointsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adjust Points</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="student_id" id="modal_student_id">
                    <div class="mb-3">
                        <label class="form-label">Student</label>
                        <input type="text" id="modal_student_name" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Points Adjustment (+/-)</label>
                        <input type="number" name="points" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="adjust_points" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Redeem Session Modal -->
<div class="modal fade" id="redeemSessionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Redeem Free Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="student_id" id="redeem_student_id">
                    <p>Confirm redemption of 1 free lab session for <span id="redeem_student_name" class="fw-bold"></span>?</p>
                    <p>This will deduct 3 points from their total.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="redeem_session" class="btn btn-success">Confirm Redemption</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Initialize modals with student data
document.addEventListener('DOMContentLoaded', function() {
    var adjustModal = document.getElementById('adjustPointsModal');
    adjustModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        document.getElementById('modal_student_id').value = button.getAttribute('data-student-id');
        document.getElementById('modal_student_name').value = button.getAttribute('data-student-name');
    });
    
    var redeemModal = document.getElementById('redeemSessionModal');
    redeemModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        document.getElementById('redeem_student_id').value = button.getAttribute('data-student-id');
        document.getElementById('redeem_student_name').textContent = button.getAttribute('data-student-name');
    });
});
</script>
</body>
</html>