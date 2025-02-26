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
    background-color: #0066cc;
}

.reserve-button:hover {
    background-color: #0055a3;
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
<main>
        <h1>Reservation</h1>
        <form action="reservation.php" method="post">
        <a class="btn" href="home.php">Go Back</a>
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
