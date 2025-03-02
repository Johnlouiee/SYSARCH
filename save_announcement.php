<?php
session_start();
if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

   
    $sql = "INSERT INTO announcements (title, content) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $title, $content);

    if ($stmt->execute()) {
        header("Location: create_announcement.php?success=1");
        exit();
    } else {
        header("Location: create_announcement.php?error=1");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>