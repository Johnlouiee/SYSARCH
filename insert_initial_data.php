<?php
require_once 'db_connect.php';

// Insert admin user if not exists
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$admin_sql = "INSERT IGNORE INTO users (username, password_hash, idno, lastname, firstname, course, year, email, role) 
              VALUES ('admin', ?, 'admin', 'Admin', 'System', 'BSIT', 1, 'admin@system.com', 'admin')";
$stmt = $conn->prepare($admin_sql);
$stmt->bind_param("s", $admin_password);
$stmt->execute();

// Insert sample announcement
$announcement_sql = "INSERT IGNORE INTO announcements (title, content) 
                    VALUES ('Welcome to the System', 'This is a sample announcement.')";
$conn->query($announcement_sql);

echo "Initial data inserted successfully!";
$conn->close();
?> 