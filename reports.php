<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>reports</title>
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
        </div>
</div>
</body>
</html>