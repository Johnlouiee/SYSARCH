<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form inputs
    if (empty($_POST['idno']) || empty($_POST['purpose']) || empty($_POST['lab'])) {
        header("Location: search_student.php?error=missing_fields");
        exit();
    }

    $idno = mysqli_real_escape_string($conn, $_POST['idno']);
    $purpose = mysqli_real_escape_string($conn, $_POST['purpose']);
    $lab = mysqli_real_escape_string($conn, $_POST['lab']);

    // Check if student exists
    $check_sql = "SELECT idno FROM users WHERE idno = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $idno);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        header("Location: search_student.php?error=student_not_found");
        exit();
    }

    // Check if student already has an active sit-in
    $active_sql = "SELECT id FROM sit_in_history WHERE user_id = ? AND session_end IS NULL";
    $active_stmt = $conn->prepare($active_sql);
    $active_stmt->bind_param("s", $idno);
    $active_stmt->execute();
    $active_result = $active_stmt->get_result();

    if ($active_result->num_rows > 0) {
        header("Location: search_student.php?error=active_session_exists");
        exit();
    }

    // Insert new sit-in record
    $sql = "INSERT INTO sit_in_history (user_id, purpose, lab, session_start) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sss", $idno, $purpose, $lab);

        if ($stmt->execute()) {
            header("Location: view_current_sitin.php?success=1");
            exit();
        } else {
            header("Location: search_student.php?error=database_error");
            exit();
        }
    } else {
        header("Location: search_student.php?error=prepare_error");
        exit();
    }
} else {
    header("Location: search_student.php?error=invalid_method");
    exit();
}

$conn->close();
?>