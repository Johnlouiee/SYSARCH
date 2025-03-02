<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create"])) {
    $idno = $_POST["idno"];
    $lastname = $_POST["lastname"];
    $firstname = $_POST["firstname"];
    $middlename = $_POST["middlename"];
    $course = $_POST["course"];
    $year = $_POST["year"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $role = $_POST["role"];  

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (idno, lastname, firstname, middlename, course, year, email, password_hash, role) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $idno, $lastname, $firstname, $middlename, $course, $year, $email, $hashed_password, $role);

    if ($stmt->execute()) {
        header("Location: index.php"); 
        echo "<script> alert('User Created');</script>";

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
        }
        .form-container {
            background-color: #fff;
            padding: 25px 45px;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h1, h2 {
            text-align: center;
            color: #333;
        }
        select, input[type="text"], input[type="password"], button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h1>Select Role</h1>
    <select id="roleSelection">
        <option value="">-- Select Role --</option>
        <option value="student">Student</option>
        <option value="admin">Admin</option>
    </select>
    <button class="btn" onclick="showForm()">Continue</button>
</div>


<div class="form-container hidden" id="registrationForm">
    <h2>Register as <span id="selectedRole"></span></h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="hidden" name="role" id="roleInput">
        <label>IDNO</label>
        <input type="text" name="idno" required>
        <label>Lastname</label>
        <input type="text" name="lastname" required>
        <label>Firstname</label>
        <input type="text" name="firstname" required>
        <label>Middlename</label>
        <input type="text" name="middlename">
        <label>Course</label>
        <select name="course" required>
            <option value="BSIT">BSIT</option>
            <option value="BSCS">BSCS</option>
            <option value="BSIS">BSIS</option>
            <option value="BSECE">BSECE</option>
            <option value="BSCRIM">BSCRIM</option>
            <option value="HRM">HRM</option>
        </select>
        <label>Year Level</label>
        <select name="year" required>
            <option value="1">1st Year</option>
            <option value="2">2nd Year</option>
            <option value="3">3rd Year</option>
            <option value="4">4th Year</option>
        </select>
        <label>Email Address</label>
        <input type="text" name="email" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <label>Confirm Password</label>
        <input type="password" name="password" required>
        <button class="btn" type="submit" name="create">Sign Up</button>
    </form>
</div>

<script>
    function showForm() {
        var role = document.getElementById("roleSelection").value;
        if (role) {
            document.getElementById("selectedRole").innerText = role.charAt(0).toUpperCase() + role.slice(1);
            document.getElementById("roleInput").value = role;
            document.querySelector(".form-container.hidden").classList.remove("hidden");
            document.querySelector(".form-container").classList.add("hidden");
        } else {
            alert("Please select a role!");
        }
    }
</script>

</body>
</html>
