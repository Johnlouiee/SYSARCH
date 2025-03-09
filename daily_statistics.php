<?php
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

include 'db_connect.php';

// Fetch sit-ins for the current day
$sql_daily = "SELECT COUNT(*) as daily FROM sit_in_history WHERE DATE(session_start) = CURDATE()";
$result_daily = $conn->query($sql_daily);
$daily_sitins = $result_daily->fetch_assoc()['daily'];

// Fetch programming language usage for the current day
$sql_daily_languages = "SELECT programming_language, COUNT(*) as count FROM sit_in_history WHERE DATE(session_start) = CURDATE() GROUP BY programming_language";
$result_daily_languages = $conn->query($sql_daily_languages);
$daily_languages = [];
while ($row = $result_daily_languages->fetch_assoc()) {
    $daily_languages[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Statistics</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f7f9;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .statistics-container {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }
        .statistic {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 30%;
        }
        .statistic h2 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .statistic p {
            margin: 10px 0 0;
            font-size: 18px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Daily Statistics</h1>

    <div class="statistics-container">
        <div class="statistic">
            <h2><?= $daily_sitins ?></h2>
            <p>Today's Sit-ins</p>
        </div>
    </div>

    <h2>Programming Language Usage Today</h2>
    <table>
        <thead>
            <tr>
                <th>Programming Language</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($daily_languages as $language): ?>
                <tr>
                    <td><?= htmlspecialchars($language['programming_language']) ?></td>
                    <td><?= htmlspecialchars($language['count']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>

<?php
$conn->close();
?>