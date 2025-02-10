<?php
session_start([
    'cookie_lifetime' => 86400, 
    'cookie_secure' => true, 
    'cookie_httponly' => true, 
]);

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

session_regenerate_id(true); 
$username = htmlspecialchars($_SESSION['username']);
$user_info = $_SESSION['user_info']; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7f9;
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

        .dashboard {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 90vh;
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .dashboard-header h1 {
            font-size: 26px;
            color: #333;
            margin-bottom: 10px;
        }

        .dashboard-header p {
            font-size: 18px;
            color: #555;
        }

        .user-info-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            width: 100%;
            max-width: 400px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .user-info p {
            margin: 0;
            font-size: 16px;
            color: #333;
        }

        .spaceBetween {
            display: flex;
            justify-content: space-between;
        }

        .dashboard-menu {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            font-size: 18px;
            padding: 12px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            color: white;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transform: scale(1.05);
        }

       

        @media (max-width: 768px) {
            .dashboard-menu {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
<div class="header">
    <div>
        <a href="home.php">Home</a>
        <a href="reports.php">Reports</a>
        <a href="edit_profile.php">Edit Profile</a>
        <a href="notification.php">Notification</a>
        <a href="reservation.php">Reservation</a>
    </div>
    <div>
        <span>Welcome, <?php echo $username; ?>!</span>
        <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
    </div>
</div>
<div class="dashboard">
    <div class="dashboard-header">
        <h1> Dashboard </h1>
        <p>You have successfully logged in to the Dashboard.</p>
        <div class="user-info-container">
            <div class="user-info">
                <p><strong>IDNO:</strong> <?php echo $user_info['idno']; ?></p>
                <p><strong>Name:</strong> <?php echo $user_info['firstname'] . ' ' . $user_info['lastname']; ?></p>
                <p><strong>Course:</strong> <?php echo $user_info['course']; ?></p>
                <p><strong>Year Level:</strong> <?php echo $user_info['year']; ?></p>
                <p><strong>Email:</strong> <?php echo $user_info['email']; ?></p>
            </div>
        </div>
      
    </div>
</div>
</body>
</html>