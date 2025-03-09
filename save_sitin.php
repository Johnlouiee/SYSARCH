<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_info'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['user_info']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Include the database connection file
include 'db_connect.php';

// Debugging: Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debugging: Check if form is submitted
    echo "Form submitted successfully!<br>";

    // Validate form inputs
    if (empty($_POST['idno']) || empty($_POST['purpose']) || empty($_POST['lab']) || empty($_POST['remaining_sessions'])) {
        die("All fields are required."); // Debugging line
    }

    $idno = $_POST['idno'];
    $purpose = $_POST['purpose'];
    $lab = $_POST['lab'];
    $remaining_sessions = $_POST['remaining_sessions'];

    // Prepare and execute the SQL query
    $sql = "INSERT INTO sit_in_history (user_id, purpose, lab, remaining_sessions, session_start) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sssi", $idno, $purpose, $lab, $remaining_sessions);

        if ($stmt->execute()) {
            // Debugging: Check if query is executed successfully
            echo "Query executed successfully!<br>";

            // Redirect to admin dashboard on success
            header("Location: admin_home.php?success=1");
            exit();
        } else {
            // Debugging: Display SQL error
            die("Error executing query: " . $stmt->error);
        }
    } else {
        // Debugging: Display SQL preparation error
        die("Error preparing query: " . $conn->error);
    }
} else {
    // Debugging: Check if form is not submitted
    echo "Form not submitted.<br>";

    // Redirect if the form is not submitted
    header("Location: admin_home.php");
    exit();
}
?>