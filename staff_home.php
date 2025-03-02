<?php
session_start();
include 'db_connect.php';
$staff_username = $_SESSION['user_info']['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 20px; background: #f4f4f4; }
        h2 { margin-bottom: 20px; }
        .container { max-width: 600px; margin: auto; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .info { font-size: 18px; margin-bottom: 20px; }
        .logout-btn { margin-top: 20px; display: inline-block; padding: 10px; background: red; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Welcome, <?= htmlspecialchars($staff_username) ?>!</h2>
    <p class="info">This is your staff dashboard.</p>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

</body>
</html>
