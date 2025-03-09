<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_info']['idno'])) {
    die("Unauthorized access.");
}

$username = $_SESSION['user_info']['idno']; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idno = $_POST["idno"];
    $lastname = $_POST['lastname'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $course = $_POST['course'];
    $year = $_POST['year'];
    $email = $_POST['email'];
    $new_password = $_POST['password'];

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE profiles 
                SET idno = ?, lastname = ?, firstname = ?, middlename = ?, course = ?, year = ?, email = ?, password = ?
                WHERE idno = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $idno, $lastname, $firstname, $middlename, $course, $year, $email, $hashed_password);
    } else {
        $sql = "UPDATE profiles 
                SET idno = ?, lastname = ?, firstname = ?, middlename = ?, course = ?, year = ?, email = ?
                WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $idno, $lastname, $firstname, $middlename, $course, $year, $email,);
    }

    if ($stmt->execute()) {
        $_SESSION['user_info']['idno'] = $idno;
        $_SESSION['user_info']['lastname'] = $lastname;
        $_SESSION['user_info']['firstname'] = $firstname;
        $_SESSION['user_info']['middlename'] = $middlename;
        $_SESSION['user_info']['course'] = $course;
        $_SESSION['user_info']['year'] = $year;
        $_SESSION['user_info']['email'] = $email;

        echo "Profile updated successfully!";
    } else {
        echo "Error updating profile: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
        }

        .header {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            width: 100%;
            box-sizing: border-box;
            text-align: center;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .header a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            font-size: 14px;
        }

        .header a:hover {
            text-decoration: underline;
        }

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 600px;
            border: 2px solid #4CAF50;
            margin-top: 80px; /* Space for the header */
        }

        .profile-card {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-card img {
            width: 100px;
            height: 100px;
            border-radius: 10%;
            border: 3px solid red;
        }

        .profile-card h3 {
            margin: 10px 0 5px;
            font-size: 18px;
            color: #333;
        }

        .profile-card p {
            margin: 0;
            font-size: 14px;
            color: #777;
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 10px;
            color: #333;
        }

        .form-container label {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            display: block;
            margin-bottom: 5px;
        }

        .form-container input[type="text"],
        .form-container input[type="password"],
        .form-container select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            background-color: #f9f9f9;
            box-sizing: border-box; /* Ensure padding is included in width */
        }

        .form-container input[type="text"]:focus,
        .form-container input[type="password"]:focus,
        .form-container select:focus {
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
        }

        .form-container .btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }

        .form-container .btn:hover {
            background-color: #45a049;
        }

        .form-container .back-btn {
            background-color: green;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-top: 10px;
            text-align: center;
            text-decoration: none;
            display: block;
            transition: background-color 0.3s ease;
        }

        .form-container .back-btn:hover {
            background-color: darkgreen;
        }
    </style>
</head>
<body>

    <div class="header">
        <a href="home.php">Home</a>
        <a href="reports.php">Reports</a>
        <a href="editprofile.php">Edit Profile</a>
        <a href="view_announcements.php">View Announcement</a>
        <a href="reservation.php">Reservation</a>
        <a href="sitin.php">Sit-In History</a>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <div class="profile-card">
            <img src="ok.jpg" alt="Profile Image">
        </div>

        <h2>Edit Profile</h2>
        <form action="editprofile.php" method="POST">
            <label for="idno">IDNO</label>
            <input type="text" id="idno" name="idno" required>

            <label for="lastname">Lastname</label>
            <input type="text" id="lastname" name="lastname" required>

            <label for="firstname">Firstname</label>
            <input type="text" id="firstname" name="firstname" required>

            <label for="middlename">Middlename</label>
            <input type="text" id="middlename" name="middlename">

            <label for="course">Course</label>
            <select id="course" name="course" required>
                <option value="BSIT">BSIT</option>
                <option value="BSCS">BSCS</option>
                <option value="BSIS">BSIS</option>
                <option value="BSECE">BSECE</option>
                <option value="BSCRIM">BSCRIM</option>
                <option value="HRM">HRM</option>
            </select>

            <label for="year">Year Level</label>
            <select id="year" name="year" required>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
            </select>

            <label for="email">Email Address</label>
            <input type="text" id="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <label for="password">Confirm Password</label>
            <input type="password" id="password" name="password" required>

            <button class="btn" type="submit" name="create">Save Changes</button>
        </form>
    </div>
</body>
</html>