<?php
session_start();
<<<<<<< HEAD
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
=======
>>>>>>> 3ae84d6776b12f223043370486462e3efd84e342
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit</title>
<<<<<<< HEAD

<style>
=======
    <style>
>>>>>>> 3ae84d6776b12f223043370486462e3efd84e342
  .card {
    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
    max-width: 400px;
    margin: auto;
    text-align: center;
    font-family: Arial, sans-serif;
  }

  .form-container {
    background-color: #fff;
    padding: 50px 60px;
    border-radius: 20px;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 500px;
    max-height: 100vh;
    overflow-y: auto;
    margin: 0 auto;
<<<<<<< HEAD
    text-align: left; 
=======
    text-align: left; /* Added to align text elements to the left */
>>>>>>> 3ae84d6776b12f223043370486462e3efd84e342
  }

  input[type="text"]:focus,
  input[type="password"]:focus,
  select:focus {
    border-color: #4CAF50;
    outline: none;
    background-color: #fff;
    box-shadow: 0 0 6px rgba(76, 175, 80, 0.3);
  }

  input[type="text"],
  input[type="password"],
  select {
<<<<<<< HEAD
    width: calc(100% - 40px); 
    padding: 15px 20px; 
=======
    width: calc(100% - 40px); /* Adjusted width for padding consistency */
    padding: 15px 20px; /* Reduced padding to avoid overflow */
>>>>>>> 3ae84d6776b12f223043370486462e3efd84e342
    margin: 15px 0;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
    background-color: #f9f9f9;
  }

  a {
    text-decoration: none;
    font-size: 22px;
    color: black;
  }

  label {
    font-size: 14px;
    font-weight: bold;
    color: #333;
    display: block;
    margin-bottom: 5px;
  }

  .btn, .back-btn {
    font-size: 20px;
    font-weight: bold;
    padding: 15px 0;
    background-color: #4CAF50;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 8px;
    width: 100%;
    transition: background-color 0.3s ease;
    text-align: center;
    text-decoration: none;
    display: block;
    margin-top: 10px;
  }

  .btn {
    width: 30%;
  }
</style>
</head>

<body>
  <div class="form-container">
    <div class="card">
      <a class="btn" href="home.php">Go Back</a>
      <img src="ok.jpg" style="width:100%">
      <form action="editprofile.php" method="POST">
        <label for="idno">IDNO</label>
        <input type="text" id="idno" name="idno" required><br>

        <label for="lastname">Lastname</label>
        <input type="text" id="lastname" name="lastname" required><br>

        <label for="firstname">Firstname</label>
        <input type="text" id="firstname" name="firstname" required><br>

        <label for="middlename">Middlename</label>
        <input type="text" id="middlename" name="middlename"><br>

        <label for="course">Course</label>
        <select id="course" name="course" class="form-control" style="padding: 12px 15px;">
          <option value="BSIT">BSIT</option>
          <option value="BSCS">BSCS</option>
          <option value="BSIS">BSIS</option>
          <option value="BSECE">BSECE</option>
          <option value="BSCRIM">BSCRIM</option>
          <option value="HRM">HRM</option>
        </select><br>

        <label for="year">Year Level</label>
        <select id="year" name="year" required style="padding: 12px 15px;">
          <option value="1">1</option>
          <option value="2">2</option>
          <option value="3">3</option>
          <option value="4">4</option>
        </select><br>

        <label for="email">Email Address</label>
        <input type="text" id="email" name="email" required><br>

<<<<<<< HEAD
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required><br>

        <label for="password">Confirm Password</label>
=======
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required><br>

        <label for="password">Password</label>
>>>>>>> 3ae84d6776b12f223043370486462e3efd84e342
        <input type="password" id="password" name="password" required><br>

        <button class="back-btn" type="submit" name="create">Edit</button><br><br>
      </form>
    </div>
  </div>
  </body>
</html>