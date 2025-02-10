<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create"])) {
    $idno = $_POST["idno"];
    $lastname = $_POST["lastname"];
    $firstname = $_POST["firstname"];
    $middlename = $_POST["middlename"];
    $course = $_POST["course"];
    $year = $_POST["year"];
    $email = $_POST["email"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);


    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = ""; 
    $dbname = "my_database";

    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "INSERT INTO users (idno, lastname, firstname, middlename, course, year, email, username, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $idno, $lastname, $firstname, $middlename, $course, $year, $email, $username, $hashed_password);

    if ($stmt->execute()) {
        echo "<script>alert('User Created');</script>";
        header('location:index.php');
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        background-color: #e9ecef;
        overflow: hidden; 
    }

    .form-container {
        background-color: #fff;
        padding: 25px 45px;
        border-radius: 15px;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
        max-height: 90vh; 
        overflow-y: auto; 
    }

    h1 {
        text-align: center;
        font-size: 24px;
        margin-bottom: 20px;
        color: #333;
    }

    input[type="text"], input[type="password"], select {
        width: 100%;
        padding: 12px 10px;
        margin: 15px 0;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        background-color: #f9f9f9;
    }

    input[type="text"]:focus, input[type="password"]:focus, select:focus {
        border-color: #4CAF50;
        outline: none;
        background-color: #fff;
        box-shadow: 0 0 6px rgba(76, 175, 80, 0.3);
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

    .btn:hover, .back-btn:hover {
        background-color: #45a049;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .back-btn {
        background-color: #dc3545;
        margin-top: 20px;
    }

    .back-btn:hover {
        background-color: #c82333;
    }

    .arrow-icon {
        font-size: 20px; 
        margin-left: 10px;
    }

    label {
        font-size: 14px;
        font-weight: bold;
        color: #333;
        display: block;
        margin-bottom: 5px;
    }
</style>
</head>
<body>
<div class="form-container">
<a class="back-btn" href="index.php">Go Back </a>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <h1>Registration</h1>
        <label>IDNO</label>
        <input type="text" name="idno" required><br>
        <label>Lastname</label>
        <input type="text" name="lastname" required><br>
        <label>Firstname</label>
        <input type="text" name="firstname" required><br>
        <label>Middlename</label>
        <input type="text" name="middlename"><br>
        <label>Course</label>
        <select name="course" id="course" class="form-control" style="padding: 12px 15px;">
            <option value="BSIT">BSIT</option>
            <option value="BSCS">BSCS</option>
            <option value="BSIS">BSIS</option>
            <option value="BSECE">BSECE</option>
            <option value="BSCRIM">BSCRIM</option>
            <option value="HRM">HRM</option>
        </select><br>
        <label>Year Level</label>
        <select name="year" required style="padding: 12px 15px;">
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
        </select><br>
        <label>Email Address</label>
        <input type="text" name="email" required><br>
        <label>Username</label>
        <input type="text" name="username" required><br>
        <label>Password</label>
        <input type="password" name="password" required><br>
        <button class="btn" type="submit" name="create">Sign Up</button><br><br>
    </form>
</div>

<?php
if (isset($error_message)) {
    echo "<p style='color: red;'>$error_message</p>";
}
?>
</body>
</html>
