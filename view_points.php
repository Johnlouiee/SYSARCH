<?php
session_start();
if (!isset($_SESSION['user_info'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Get student information
$student_id = $_SESSION['user_info']['idno'];
$student_info = $conn->query("SELECT * FROM users WHERE idno = '$student_id'")->fetch_assoc();

// Get all completed sessions with points calculation
$sessions = $conn->query("SELECT * FROM sit_in_history 
                         WHERE user_id = '$student_id' 
                         AND session_end IS NOT NULL 
                         ORDER BY session_start DESC");

// Calculate total points from sessions
$total_points = 0;
$session_points = [];
while ($session = $sessions->fetch_assoc()) {
    $start = new DateTime($session['session_start']);
    $end = new DateTime($session['session_end']);
    $hours = $start->diff($end)->h;
    $points = $hours; // 1 point per hour
    $total_points += $points;
    
    $session_points[] = [
        'date' => $session['session_start'],
        'lab' => $session['lab'],
        'purpose' => $session['purpose'],
        'duration' => $start->diff($end)->format('%h hours %i minutes'),
        'points' => $points
    ];
}

// Get rewards information (assuming 3 points = 1 free session)
$free_sessions_earned = floor($total_points / 3);
$points_towards_next = $total_points % 3;
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Points & Rewards</title>
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
        .points-card {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .rewards-card {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .progress {
            height: 25px;
            border-radius: 12px;
        }
        .progress-bar {
            background-color: #4CAF50;
        }
        .session-row:hover {
            background-color: #f8f9fa;
        }
        .badge-custom {
            font-size: 1rem;
            padding: 8px 12px;
        }
    </style>
</head>
<body>
<div class="header">
    <div>
        <h2>College of Computer Studies</h2>
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
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<div class="container mt-4">
    <!-- Points Overview -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="points-card">
                <h3><i class="fas fa-star"></i> Total Points</h3>
                <h2 class="display-4"><?= $total_points ?></h2>
                <p>Points earned from lab sessions</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="rewards-card">
                <h3><i class="fas fa-gift"></i> Rewards Earned</h3>
                <h2 class="display-4"><?= $free_sessions_earned ?></h2>
                <p>Free sessions earned (3 points = 1 free session)</p>
            </div>
        </div>
    </div>

    <!-- Progress to Next Reward -->
    <div class="card mb-4">
        <div class="card-header">
            <h4><i class="fas fa-chart-line"></i> Progress to Next Free Session</h4>
        </div>
        <div class="card-body">
            <div class="progress">
                <div class="progress-bar" role="progressbar" 
                     style="width: <?= ($points_towards_next / 3) * 100 ?>%" 
                     aria-valuenow="<?= $points_towards_next ?>" 
                     aria-valuemin="0" 
                     aria-valuemax="3">
                    <?= $points_towards_next ?>/3 points
                </div>
            </div>
            <p class="mt-2">
                <?php if ($points_towards_next > 0): ?>
                    You need <?= 3 - $points_towards_next ?> more points to earn your next free session!
                <?php else: ?>
                    You've earned a free session! Keep going to earn more!
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Points History -->
    <div class="card">
        <div class="card-header">
            <h4><i class="fas fa-history"></i> Points History</h4>
        </div>
        <div class="card-body">
            <?php if (!empty($session_points)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Lab</th>
                                <th>Purpose</th>
                                <th>Duration</th>
                                <th>Points Earned</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($session_points as $session): ?>
                                <tr class="session-row">
                                    <td><?= date('M j, Y', strtotime($session['date'])) ?></td>
                                    <td><?= htmlspecialchars($session['lab']) ?></td>
                                    <td><?= htmlspecialchars($session['purpose']) ?></td>
                                    <td><?= $session['duration'] ?></td>
                                    <td>
                                        <span class="badge bg-success">
                                            <i class="fas fa-star"></i> <?= $session['points'] ?> points
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No points history available yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- How to Earn Points -->
    <div class="card mt-4">
        <div class="card-header">
            <h4><i class="fas fa-info-circle"></i> How to Earn Points</h4>
        </div>
        <div class="card-body">
            <ul class="list-group">
                <li class="list-group-item">
                    <i class="fas fa-clock text-primary"></i> 
                    <strong>Lab Sessions:</strong> Earn 1 point for every hour spent in the lab
                </li>
                <li class="list-group-item">
                    <i class="fas fa-gift text-warning"></i> 
                    <strong>Rewards:</strong> Every 3 points earned = 1 free lab session
                </li>
                <li class="list-group-item">
                    <i class="fas fa-star text-success"></i> 
                    <strong>Bonus Points:</strong> Complete feedback forms to earn extra points
                </li>
            </ul>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 