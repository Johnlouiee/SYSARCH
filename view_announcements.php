<?php
session_start();
if (!isset($_SESSION['user_info'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';


$sql = "SELECT * FROM announcements ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Announcements</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h2 { margin-bottom: 20px; }
        .announcement { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9; }
        .announcement h3 { margin-top: 0; }
        .announcement p { margin: 10px 0; }
        .announcement small { color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Announcements</h2>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="announcement">
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                    <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
                    <small>Posted on: <?= date('F j, Y, g:i a', strtotime($row['created_at'])) ?></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No announcements found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>