<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['idno'])) {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}

$idno = $_SESSION['idno']; // Get the logged-in user's IDNO
include 'db_connect.php'; // Include your database connection file

// Fetch sit-in history for the logged-in user
$sql = "SELECT * FROM sit_in_history WHERE user_id = ? ORDER BY session_start DESC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing query: " . $conn->error);
}
$stmt->bind_param("s", $idno); // Use IDNO instead of user_id
$stmt->execute();
$sit_in_history = $stmt->get_result();

// Handle feedback submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_feedback"])) {
    // Check if all required fields are set
    if (isset($_POST["sit_in_id"]) && isset($_POST["rating"]) && isset($_POST["comments"])) {
        $sit_in_id = $_POST["sit_in_id"];
        $rating = $_POST["rating"];
        $comments = $_POST["comments"];

        // Insert feedback into the database
        $sql = "INSERT INTO feedback (user_id, sit_in_id, rating, comments, submitted_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error preparing query: " . $conn->error);
        }
        $stmt->bind_param("siis", $idno, $sit_in_id, $rating, $comments); // Use IDNO instead of user_id

        if ($stmt->execute()) {
            $feedback_message = "Feedback submitted successfully!";
        } else {
            $feedback_message = "Error submitting feedback: " . $stmt->error;
        }
    } else {
        $feedback_message = "All fields are required!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sit-In History and Feedback</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f8f8f8;
        }
        .feedback-form {
            margin-top: 10px;
        }
        .feedback-form textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .feedback-form select, .feedback-form input[type="submit"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .feedback-form input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }
        .feedback-form input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .message {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
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
        .header {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
        }
        .header a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="header">
        <div>
            <a href="home.php">Home</a>
            <a href="reports.php">Reports</a>
            <a href="editprofile.php">Edit Profile</a>
            <a href="view_announcements.php">View Announcement</a>
            <a href="reservation.php">Reservation</a>
            <a href="sitin.php">Sit-In History</a>
        </div>
</div>
    <div class="container">
        <h1>Sit-In History</h1>
        <?php if (isset($feedback_message)): ?>
            <div class="message <?php echo strpos($feedback_message, 'successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo $feedback_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($sit_in_history->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Duration (Minutes)</th>
                        <th>Location</th>
                        <th>Feedback</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $sit_in_history->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date("F j, Y, g:i a", strtotime($row['session_start'])); ?></td>
                            <td><?php echo isset($row['duration']) ? $row['duration'] : 'N/A'; ?></td>
                            <td><?php echo isset($row['lab']) ? $row['lab'] : 'N/A'; ?></td>
                            <td>
                                <!-- Feedback Form -->
                                <form class="feedback-form" method="POST" action="">
                                    <input type="hidden" name="sit_in_id" value="<?php echo $row['id']; ?>">
                                   
                                    <textarea name="comments" placeholder="Your feedback..." required></textarea>
                                    <input type="submit" name="submit_feedback" value="Submit Feedback">
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No sit-in history found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// Close the database connection
$stmt->close();
$conn->close();
?>