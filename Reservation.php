<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve</title>
    <style>
    body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
}

header {
    background-color: #333;
    color: white;
    padding: 10px 0;
}

nav ul {
    list-style: none;
    text-align: right;
    margin: 0;
    padding: 0;
}

nav ul li {
    display: inline;
    margin-right: 20px;
}

nav ul li a {
    color: white;
    text-decoration: none;
}

main {
    margin: 20px auto;
    padding: 20px;
    width: 400px;
    background-color: white;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h1 {
    text-align: center;
}

form {
    display: flex;
    flex-direction: column;
}

label {
    margin-top: 10px;
}

input {
    margin-top: 5px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

button {
    margin-top: 15px;
    padding: 10px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

button:hover {
    background-color: #45a049;
}

.reserve-button {
    background-color: #green;
}

.reserve-button:hover {
    background-color: green;
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

        <h1>Reservation</h1>
        <form action="reservation.php" method="post">

            <label for="idNumber">ID Number:</label>
            <input type="text" id="idNumber" name="idNumber" >

            <label for="studentName">Student Name:</label>
            <input type="text" id="studentName" name="studentName" >

            <label for="purpose">Purpose:</label>
            <input type="text" id="purpose" name="purpose" >

            <label for="lab">Lab:</label>
            <input type="text" id="lab" name="lab" >
            <button type="submit">Submit</button>

            <label for="timeIn">Time In:</label>
            <input type="text" id="timeIn" name="timeIn" placeholder="hh:mm">

            <label for="date">Date:</label>
            <input type="text" id="date" name="date" placeholder="dd/mm/yyyy">

            <label for="remainingSession">Remaining Session:</label>
            <input type="text" id="remainingSession" name="remainingSession" value="30">

            <button type="submit" class="reserve-button">Reserve</button>
        </form>
    </main>

</body>
</html>
