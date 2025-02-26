<?php
session_start();
if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
    header("Location: index.php"); 
    exit();
}

include 'db_connect.php';


$sql = "SELECT COUNT(*) as total_users FROM users";
$result = $conn->query($sql);
$total_users = ($result->num_rows > 0) ? $result->fetch_assoc()['total_users'] : 0;

$sql = "SELECT id, username, role FROM users ORDER BY role ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 20px; background: #f4f4f4; }
        h2 { margin-bottom: 20px; }
        table { width: 80%; margin: auto; border-collapse: collapse; background: white; }
        th, td { padding: 10px; border: 1px solid #ddd; }
        th { background: #4CAF50; color: white; }
        .logout-btn { margin-top: 20px; display: inline-block; padding: 10px; background: red; color: white; text-decoration: none; }
    </style>
</head>
<body>

<h2>Admin Dashboard</h2>
<p>Total Users: <strong><?= $total_users ?></strong></p>

<table>
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Role</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['username'] ?></td>
        <td><?= ucfirst($row['role']) ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<a href="logout.php" class="logout-btn">Logout</a>

</body>
</html>

<?php
$conn->close();
?>
