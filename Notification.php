<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
  .announcements-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .announcements h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        .announcements h3 {
            font-size: 20px;
            color: #555;
            margin-bottom: 10px;
        }

        .announcements p {
            font-size: 16px;
            color: #444;
            margin: 10px 0;
            line-height: 1.5;
        }
</style>
</head>
<body>
<div class="announcements-container">
        <h2>Announcements</h2>
        <div class="announcement">
            <h3>CCS Admin | 2025-Feb-12</h3>
            <p>The College of Computer Studies will open the registration of students for the Sit-in privilege starting tomorrow.</p>
            <p>Thank you! Lab Supervisor</p>
        </div>
        <div class="announcement">
            <h3>CCS Admin | 2024-May-08</h3>
            <p>Important Announcement We are excited to announce the launch of our new website! 🎉 Explore our latest products and services now!</p>
        </div>
    </div>
</body>
</html>