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

// Handle AJAX requests for announcements
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'get_all_announcements') {
        $sql = "SELECT * FROM announcements ORDER BY created_at DESC";
        $result = $conn->query($sql);
        $announcements = [];
        while ($row = $result->fetch_assoc()) {
            $announcements[] = $row;
        }
        echo json_encode($announcements);
        exit();
    }

    if ($_POST['action'] === 'get_announcement' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $sql = "SELECT * FROM announcements WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $announcement = $result->fetch_assoc();
        if ($announcement) {
            echo json_encode(['status' => 'success', 'announcement' => $announcement]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Announcement not found']);
        }
        $stmt->close();
        exit();
    }

    if ($_POST['action'] === 'update_announcement' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $sql = "UPDATE announcements SET title = ?, content = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $title, $content, $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update announcement']);
        }
        $stmt->close();
        exit();
    }

    if ($_POST['action'] === 'delete_announcement' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM announcements WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete announcement']);
        }
        $stmt->close();
        exit();
    }
}

// Fetch total users
$sql = "SELECT COUNT(*) as total_users FROM users";
$result = $conn->query($sql);
$total_users = ($result->num_rows > 0) ? $result->fetch_assoc()['total_users'] : 0;

// Fetch total registered students
$sql_registered = "SELECT COUNT(*) as total FROM users";
$result_registered = $conn->query($sql_registered);
$registered_students = $result_registered->fetch_assoc()['total'];

// Fetch total sit-ins
$sql_total = "SELECT COUNT(*) as total FROM sit_in_history";
$result_total = $conn->query($sql_total);
$total_sitins = $result_total->fetch_assoc()['total'];

// Fetch active sit-ins
$sql_active = "SELECT COUNT(*) as active FROM sit_in_history WHERE session_end IS NULL";
$result_active = $conn->query($sql_active);
$active_sitins = $result_active->fetch_assoc()['active'];

// Fetch programming language distribution data
$sql_languages = "SELECT purpose, COUNT(*) as count 
                FROM sit_in_history 
                GROUP BY purpose";
$result_languages = $conn->query($sql_languages);

$labels_languages = [];
$data_languages = [];
$colors_languages = [
    'rgba(255, 99, 132, 0.6)',   // Pink for C#
    'rgba(54, 162, 235, 0.6)',   // Blue for C
    'rgba(255, 206, 86, 0.6)',   // Yellow for Java
    'rgba(75, 192, 192, 0.6)',   // Teal for ASP.net
    'rgba(153, 102, 255, 0.6)'   // Purple for PHP
];

while ($row = $result_languages->fetch_assoc()) {
    $labels_languages[] = $row['purpose'];
    $data_languages[] = $row['count'];
}

// Fetch recent announcements
$announcements_sql = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5";
$announcements_result = $conn->query($announcements_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chart.js for graphs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
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
        .container {
            padding: 20px;
        }
        h2 {
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
        .chart-container {
            width: 50%;
            margin: 20px auto;
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
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .logout-btn {
            display: inline-block;
            padding: 10px 20px;
            background: red;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .logout-btn:hover {
            background: darkred;
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 5px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .announcements-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .announcement-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .announcement-item:last-child {
            border-bottom: none;
        }
        .announcement-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .announcement-content {
            color: #666;
            margin-bottom: 5px;
        }
        .announcement-date {
            font-size: 0.9em;
            color: #999;
        }
    </style>
</head>
<body>

<div class="header">
    <div>
        <h1> College of Computer Studies Admin</h1>
        <a href="admin_home.php">Home</a>
        <a href="#" id="searchLink">Search</a>
        <a href="view_current_sitin.php">Current Sit-in</a>
        <a href="view_sitin.php">Sit-in Records</a>
        <a href="sitin_reports.php">Sit-in Reports</a>
        <a href="view_feedback.php">View Feedback</a>
        <a href="view_reservation.php">View Reservation</a>
    </div>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<div class="container">
    <h2>Admin Dashboard</h2>
    <p>Total Users: <strong><?= $total_users ?></strong></p>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Left Column - Statistics -->
        <div class="col-md-7">
            <h2>View Statistics</h2>
            <!-- Statistics Cards -->
            <div class="statistics-container mb-4">
                <div class="statistic">
                    <h2><?= $registered_students ?></h2>
                    <p>Registered Students</p>
                </div>
                <div class="statistic">
                    <h2><?= $active_sitins ?></h2>
                    <p>Current Sit-ins</p>
                </div>
                <div class="statistic">
                    <h2><?= $total_sitins ?></h2>
                    <p>Total Sit-ins</p>
                </div>
            </div>

            <!-- Programming Language Chart -->
            <div class="card">
                <div class="card-header">
                    <h4>Statistics</h4>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:400px; width:100%">
                        <canvas id="languagePieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Announcements -->
        <div class="col-md-5">
            <div class="announcements-section">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>Announcements</h3>
                    <button class="btn btn-success" id="createAnnouncementBtn">Create New Announcement</button>
                </div>
                <?php if ($announcements_result && $announcements_result->num_rows > 0): ?>
                    <?php while($announcement = $announcements_result->fetch_assoc()): ?>
                        <div class="announcement-item">
                            <div class="announcement-title"><?= htmlspecialchars($announcement['title']) ?></div>
                            <div class="announcement-content"><?= nl2br(htmlspecialchars($announcement['content'])) ?></div>
                            <div class="announcement-date">Posted on: <?= date('F j, Y g:i A', strtotime($announcement['created_at'])) ?></div>
                        </div>
                    <?php endwhile; ?>
                    <div class="text-center mt-3">
                        <button class="btn btn-primary" id="viewAllAnnouncementsBtn">View All Announcements</button>
                    </div>
                <?php else: ?>
                    <p>No announcements yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Search Modal -->
<div id="searchModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('searchModal')">×</span>
        <h3>Search Student</h3>
        <form id="searchForm">
            <div class="form-group">
                <label for="search_idno">ID Number:</label>
                <input type="text" id="search_idno" name="search_idno" placeholder="Enter ID Number" required>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        <div id="searchResults"></div>
    </div>
</div>

<!-- Create Announcement Modal -->
<div id="createAnnouncementModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('createAnnouncementModal')">×</span>
        <h3>Create Announcement</h3>
        <form id="createAnnouncementForm">
            <div class="form-group mb-3">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="content">Content:</label>
                <textarea id="content" name="content" class="form-control" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Create Announcement</button>
        </form>
    </div>
</div>

<!-- View All Announcements Modal -->
<div id="viewAnnouncementsModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('viewAnnouncementsModal')">×</span>
        <h3>All Announcements</h3>
        <div id="allAnnouncementsList" class="announcements-list">
            <!-- Announcements will be loaded here -->
        </div>
    </div>
</div>

<!-- Edit Announcement Modal -->
<div id="editAnnouncementModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editAnnouncementModal')">×</span>
        <h3>Edit Announcement</h3>
        <form id="editAnnouncementForm">
            <input type="hidden" id="edit_id" name="id">
            <div class="form-group mb-3">
                <label for="edit_title">Title:</label>
                <input type="text" id="edit_title" name="title" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="edit_content">Content:</label>
                <textarea id="edit_content" name="content" class="form-control" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update Announcement</button>
        </form>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Function to open specific modal
    function openModal(modalId) {
        document.getElementById(modalId).style.display = "block";
    }

    // Function to close specific modal
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = "none";
        if (modalId === 'createAnnouncementModal') {
            document.getElementById("createAnnouncementForm").reset();
        } else if (modalId === 'editAnnouncementModal') {
            document.getElementById("editAnnouncementForm").reset();
        }
    }

    // Event listeners for opening modals
    document.getElementById("searchLink").addEventListener("click", function(event) {
        event.preventDefault();
        openModal('searchModal');
    });

    document.getElementById("createAnnouncementBtn").addEventListener("click", function(event) {
        event.preventDefault();
        openModal('createAnnouncementModal');
    });

    document.getElementById("viewAllAnnouncementsBtn").addEventListener("click", function(event) {
        event.preventDefault();
        loadAllAnnouncements();
    });

    // Handle search form submission
    document.getElementById("searchForm").addEventListener("submit", function(event) {
        event.preventDefault();
        const search_idno = document.getElementById("search_idno").value;
        window.location.href = "search_student.php?search_idno=" + encodeURIComponent(search_idno);
    });

    // Handle create announcement form submission
    document.getElementById("createAnnouncementForm").addEventListener("submit", function(event) {
        event.preventDefault();

        const formData = new FormData(this);

        fetch("save_announcement.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                alert("Announcement created successfully!");
                closeModal('createAnnouncementModal');
                window.location.reload();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred while creating the announcement.");
        });
    });

    // Function to load all announcements
    function loadAllAnnouncements() {
        fetch(window.location.href, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "action=get_all_announcements"
        })
        .then(response => response.json())
        .then(data => {
            const announcementsList = document.getElementById("allAnnouncementsList");
            announcementsList.innerHTML = "";

            if (data.length > 0) {
                data.forEach(announcement => {
                    const announcementDiv = document.createElement("div");
                    announcementDiv.className = "announcement-item";
                    announcementDiv.innerHTML = `
                        <div class="announcement-title">${announcement.title}</div>
                        <div class="announcement-content">${announcement.content}</div>
                        <div class="announcement-date">Posted on: ${new Date(announcement.created_at).toLocaleString()}</div>
                        <div class="mt-2">
                            <button class="btn btn-primary btn-sm edit-btn" data-id="${announcement.id}">Edit</button>
                            <button class="btn btn-danger btn-sm delete-btn" data-id="${announcement.id}">Delete</button>
                        </div>
                    `;
                    announcementsList.appendChild(announcementDiv);
                });

                // Add event listeners for edit and delete buttons
                document.querySelectorAll('.edit-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        openEditModal(id);
                    });
                });

                document.querySelectorAll('.delete-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        if (confirm('Are you sure you want to delete this announcement?')) {
                            deleteAnnouncement(id);
                        }
                    });
                });
            } else {
                announcementsList.innerHTML = "<p>No announcements available.</p>";
            }
            openModal('viewAnnouncementsModal');
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred while loading announcements.");
        });
    }

    // Function to open edit modal and populate it with announcement data
    function openEditModal(id) {
        fetch(window.location.href, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `action=get_announcement&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                document.getElementById('edit_id').value = data.announcement.id;
                document.getElementById('edit_title').value = data.announcement.title;
                document.getElementById('edit_content').value = data.announcement.content;
                openModal('editAnnouncementModal');
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred while fetching announcement data.");
        });
    }

    // Handle edit announcement form submission
    document.getElementById("editAnnouncementForm").addEventListener("submit", function(event) {
        event.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'update_announcement');

        fetch(window.location.href, {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                alert("Announcement updated successfully!");
                closeModal('editAnnouncementModal');
                loadAllAnnouncements();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred while updating the announcement.");
        });
    });

    // Function to delete an announcement
    function deleteAnnouncement(id) {
        fetch(window.location.href, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `action=delete_announcement&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                alert("Announcement deleted successfully!");
                loadAllAnnouncements();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred while deleting the announcement.");
        });
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = "none";
        }
    }

    // Programming Language Distribution Pie Chart
    const languageCtx = document.getElementById('languagePieChart').getContext('2d');
    new Chart(languageCtx, {
        type: 'pie',
        data: {
            labels: <?= json_encode($labels_languages) ?>,
            datasets: [{
                data: <?= json_encode($data_languages) ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',   // Pink for C#
                    'rgba(54, 162, 235, 0.7)',   // Blue for C
                    'rgba(255, 206, 86, 0.7)',   // Yellow for Java
                    'rgba(75, 192, 192, 0.7)',   // Teal for ASP.net
                    'rgba(153, 102, 255, 0.7)'   // Purple for PHP
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 20,
                        font: {
                            size: 14
                        }
                    }
                },
                title: {
                    display: true,
                    text: 'Statistics',
                    font: {
                        size: 16,
                        weight: 'bold'
                    },
                    padding: 20
                }
            }
        }
    });
</script>
</body>
</html>

<?php
$conn->close();
?>