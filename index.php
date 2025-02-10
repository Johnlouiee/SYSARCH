<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = ""; 
    $dbname = "my_database";

    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
       
        if (password_verify($password, $row['password_hash'])) {
            session_start();
            $_SESSION['username'] = $username;
            $_SESSION['user_info'] = $row; 
           
            header("Location: home.php");
            exit();
        } else {
            $error_message = "Invalid username or password!";
        }
    } else {
        $error_message = "No user found with that username!";
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
    <title>CCS Sit-in Monitoring System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
        }

        .form-container {
            background: #fff;
            padding: 80px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            margin: 20px auto;
            width: 500px;
        }

        input[type="text"], input[type="password"] {
            width: 90%;
            padding: 20px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            padding: 10px 30px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }

        button:hover {
            background: #45a049;
        }

        .remember-me {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .forgot-password {
            margin-top: 10px;
        }

        .btn-register {
            margin-top: 20px;
            background-color: #f44336;
            color: white;
            text-decoration: none;
            padding: 10px 30px;
            border-radius: 10px;
            display: inline-block;
            font-size: 16px;
        }

        .btn-register:hover {
            background-color: #e53935;
        }

        .spaceBetween {
            display: flex;
            justify-content: space-between;
        }
        .reg {
            color:red;
            text-decoration:none;
            margin-top: 30px;
            display: inline-block;
        }
        .reg:hover {
            color: red;
            text-decoration: underline;
        }
        
    </style>
</head>
<body>
    <h1>CCS Sit-in Monitoring System</h1>
    <img src="ccs.jpg" alt="CCS" style="width: 250px;">
    <div class="form-container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label>Username</label>
            <input type="text" placeholder="Username" name="username" required><br>
            <label>Password</label>
            <input type="password" placeholder="Password" name="password" required><br>
            <button type="submit" name="create">Login</button><br><br>
            <div class="spaceBetween">
                <div>
                    <input class="form-check-input me-2" type="checkbox" value="" id="form2Example3">
                    <label class="form-check-label" for="form2Example3"> Remember me </label>
                </div>
                <a href="#!" class="text-body" style="color: black;">Forgot Password?</a>
            </div>
        </form>
        <div>
            <label> Don't have an account? </label>
            <a href="registration.php" class="reg">Register</a>
        </div>
        <?php if (isset($error_message)) { echo "<p style='color: red;'>$error_message</p>"; } ?>
    </div>
</body>
</html>