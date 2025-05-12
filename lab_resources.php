<?php
session_start();

include 'db_connect.php';

// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
file_put_contents('debug.log', "Request received: " . print_r($_POST, true) . "\n", FILE_APPEND);

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'get_lab_resources') {
        $sql = "SELECT * FROM lab_resources ORDER BY uploaded_at DESC";
        $result = $conn->query($sql);
        $resources = [];
        while ($row = $result->fetch_assoc()) {
            $resources[] = $row;
        }
        file_put_contents('debug.log', "Fetched resources: " . print_r($resources, true) . "\n", FILE_APPEND);
        echo json_encode($resources);
        exit();
    }

    if ($_POST['action'] === 'update_resource_status' && isset($_POST['id']) && isset($_POST['is_active'])) {
        $id = (int)$_POST['id'];
        $is_active = $_POST['is_active'] === '1' ? 1 : 0;
        file_put_contents('debug.log', "Updating resource ID: $id, is_active: $is_active\n", FILE_APPEND);
        
        $sql = "UPDATE lab_resources SET is_active = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error = $conn->error;
            file_put_contents('debug.log', "Prepare failed: $error\n", FILE_APPEND);
            echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $error]);
            exit();
        }
        $stmt->bind_param("ii", $is_active, $id);
        $execute_result = $stmt->execute();
        $affected_rows = $stmt->affected_rows;
        file_put_contents('debug.log', "Execute result: $execute_result, Affected rows: $affected_rows, is_active: $is_active\n", FILE_APPEND);
        
        if ($execute_result && $affected_rows > 0) {
            echo json_encode(['status' => 'success', 'is_active' => $is_active]);
        } else {
            $error = $stmt->error ?: 'No rows updated';
            file_put_contents('debug.log', "Update failed: $error\n", FILE_APPEND);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update resource status: ' . $error]);
        }
        $stmt->close();
        exit();
    }

    if ($_POST['action'] === 'delete_lab_resource' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        
        // First get the file path to delete it from server
        $sql = "SELECT file_path FROM lab_resources WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $resource = $result->fetch_assoc();
        
        if ($resource) {
            // Delete from database
            $sql = "DELETE FROM lab_resources WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                // Delete the actual file
                if (file_exists($resource['file_path'])) {
                    unlink($resource['file_path']);
                }
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete resource']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Resource not found']);
        }
        $stmt->close();
        exit();
    }
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['lab_resource'])) {
    header('Content-Type: application/json');
    
    $uploadDir = 'Uploads/lab_resources/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = basename($_FILES['lab_resource']['name']);
    $filePath = $uploadDir . uniqid() . '_' . $fileName;
    $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    
    // Check if file already exists
    if (file_exists($filePath)) {
        echo json_encode(['status' => 'error', 'message' => 'File already exists']);
        exit();
    }
    
    // Check file size (10MB max)
    if ($_FILES['lab_resource']['size'] > 10000000) {
        echo json_encode(['status' => 'error', 'message' => 'File is too large (max 10MB)']);
        exit();
    }
    
    // Allow certain file formats
    $allowedTypes = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip', 'rar', 'jpg', 'jpeg', 'png'];
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['status' => 'error', 'message' => 'Only PDF, DOC, PPT, XLS, TXT, ZIP, RAR, JPG, PNG files are allowed']);
        exit();
    }
    
    // Upload file
    if (move_uploaded_file($_FILES['lab_resource']['tmp_name'], $filePath)) {
        $title = $_POST['title'] ?? pathinfo($fileName, PATHINFO_FILENAME);
        $description = $_POST['description'] ?? '';
        $is_active = isset($_POST['is_active']) && $_POST['is_active'] === '1' ? 1 : 0;
        file_put_contents('debug.log', "Uploading file, is_active: $is_active\n", FILE_APPEND);
        
        $sql = "INSERT INTO lab_resources (title, description, file_name, file_path, file_type, file_size, uploaded_by, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssisi", $title, $description, $fileName, $filePath, $fileType, $_FILES['lab_resource']['size'], $_SESSION['user_info']['idno'], $is_active);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'File uploaded successfully']);
        } else {
            unlink($filePath);
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error uploading file']);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Resources Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .header {
            background-color: #343a40;
            color: white;
            padding: 15px 20px;
            margin-bottom: 30px;
        }
        .resource-card {
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .resource-card:hover {
            transform: translateY(-5px);
        }
        .resource-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        .file-preview {
            max-height: 200px;
            object-fit: contain;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .upload-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .file-type-icon {
            font-size: 1.5rem;
            margin-right: 10px;
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
           
        .header a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
        }
        .header a:hover {
            text-decoration: underline;
        }
        .edit-btn {
            min-width: 90px;
        }
    </style>
</head>
<body>
<div class="header">
    <div>
    <h1> </h1>
        <a href="admin_home.php">Home</a>
        <a href="#" id="searchLink">Search</a>
        <a href="view_current_sitin.php">Current Sit-in</a>
        <a href="view_sitin.php">Sit-in Records</a>
        <a href="sitin_reports.php">Sit-in Reports</a>
        <a href="view_feedback.php">View Feedback</a>
        <a href="view_reservation.php">View Reservation</a>
        <a href="reservation_logs.php">Reservation Logs</a>
        <a href="student_management.php">Student Information</a>
        <a href="lab_schedule.php">Lab Schedule</a>
        <a href="lab_resources.php">Lab Resources</a>
        <a href="admin_notification.php">Notification</a>
        <a href="computer_control.php">Computer Control</a>
    </div>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>
<div class="container">
    <h1>Lab Resources Management</h1>
    <!-- Upload Form -->
    <div class="upload-container">
        <h3><i class="fas fa-cloud-upload-alt"></i> Upload New Resource</h3>
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="2"></textarea>
            </div>
            <div class="mb-3">
                <label for="lab_resource" class="form-label">Select File</label>
                <input class="form-control" type="file" id="lab_resource" name="lab_resource" required>
                <div class="form-text">Max file size: 10MB. Allowed types: PDF, DOC, PPT, XLS, TXT, ZIP, RAR, JPG, PNG</div>
            </div>
            <div class="mb-3">
                <label for="is_active" class="form-label">Status</label>
                <select class="form-select" id="is_active" name="is_active" required>
                    <option value="1" selected>Enabled</option>
                    <option value="0">Disabled</option>
                </select>
                <div class="form-text">Select whether the resource should be enabled or disabled upon upload.</div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-upload"></i> Upload
            </button>
            <div id="uploadStatus" class="mt-2"></div>
        </form>
    </div>

    <!-- Resources List -->
    <h3 class="mt-5 mb-4"><i class="fas fa-file-alt"></i> Lab Resources</h3>
    <div id="resourcesList" class="row">
        <!-- Resources will be loaded here -->
        <div class="col-12 text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <!-- Edit Status Modal -->
    <div class="modal fade" id="editStatusModal" tabindex="-1" aria-labelledby="editStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStatusModalLabel">Edit Resource Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editStatusForm">
                        <input type="hidden" id="edit_resource_id" name="id">
                        <div class="mb-3">
                            <label for="edit_is_active" class="form-label">Status</label>
                            <select class="form-select" id="edit_is_active" name="is_active" required>
                                <option value="1">Enabled</option>
                                <option value="0">Disabled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Function to get file type icon
    function getFileTypeIcon(fileType) {
        const icons = {
            'pdf': 'file-pdf',
            'doc': 'file-word',
            'docx': 'file-word',
            'ppt': 'file-powerpoint',
            'pptx': 'file-powerpoint',
            'xls': 'file-excel',
            'xlsx': 'file-excel',
            'txt': 'file-alt',
            'zip': 'file-archive',
            'rar': 'file-archive',
            'jpg': 'file-image',
            'jpeg': 'file-image',
            'png': 'file-image'
        };
        
        const defaultIcon = 'file';
        return icons[fileType] || defaultIcon;
    }

    // Function to format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Function to load resources
    function loadResources() {
        const timestamp = new Date().getTime();
        fetch('lab_resources.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=get_lab_resources&_=${timestamp}`
        })
        .then(response => response.json())
        .then(data => {
            console.log('Loaded resources:', data);
            const resourcesList = document.getElementById('resourcesList');
            resourcesList.innerHTML = '';

            if (data.length > 0) {
                data.forEach(resource => {
                    const fileType = resource.file_type.toLowerCase();
                    const fileIcon = getFileTypeIcon(fileType);
                    const fileSize = formatFileSize(resource.file_size);
                    const uploadDate = new Date(resource.uploaded_at).toLocaleString();
                    
                    const resourceCard = document.createElement('div');
                    resourceCard.className = 'col-md-6 col-lg-4';
                    resourceCard.innerHTML = `
                        <div class="card resource-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <i class="fas fa-${fileIcon} file-type-icon text-primary"></i>
                                        <h5 class="card-title d-inline">${resource.title}</h5>
                                    </div>
                                    <span class="badge ${resource.is_active == 1 ? 'bg-success' : 'bg-secondary'} status-badge">
                                        ${resource.is_active == 1 ? 'Enabled' : 'Disabled'}
                                    </span>
                                </div>
                                <p class="card-text">${resource.description || 'No description provided.'}</p>
                                
                                ${fileType.match(/(jpg|jpeg|png)/) ? 
                                    `<img src="${resource.file_path}" class="img-fluid file-preview mb-2" alt="${resource.title}">` : ''}
                                
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted">${fileSize} • ${fileType.toUpperCase()}</small>
                                    <small class="text-muted">${uploadDate}</small>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-sm btn-warning edit-btn" 
                                            data-id="${resource.id}" 
                                            data-is-active="${resource.is_active}" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            title="Edit resource status">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <a href="${resource.file_path}" class="btn btn-sm btn-primary" download>
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="${resource.id}">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    resourcesList.appendChild(resourceCard);
                });

                // Add event listeners for edit buttons
                document.querySelectorAll('.edit-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const isActive = this.getAttribute('data-is-active');
                        openEditModal(id, isActive);
                    });
                });

                // Add event listeners for delete buttons
                document.querySelectorAll('.delete-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        if (confirm('Are you sure you want to delete this resource?')) {
                            deleteResource(id);
                        }
                    });
                });

                // Initialize Bootstrap tooltips
                const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
            } else {
                resourcesList.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-info">No lab resources found.</div>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading resources:', error);
            document.getElementById('resourcesList').innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger">Error loading resources. Please try again.</div>
                </div>
            `;
        });
    }

    // Function to open edit modal
    function openEditModal(id, isActive) {
        console.log('Opening modal for ID:', id, 'is_active:', isActive);
        document.getElementById('edit_resource_id').value = id;
        document.getElementById('edit_is_active').value = isActive;
        const modal = new bootstrap.Modal(document.getElementById('editStatusModal'));
        modal.show();
    }

    // Function to update resource status
    function updateResourceStatus(id, isActive) {
        console.log('Updating resource ID:', id, 'is_active:', isActive);
        const timestamp = new Date().getTime();
        const body = `action=update_resource_status&id=${encodeURIComponent(id)}&is_active=${encodeURIComponent(isActive)}&_=${timestamp}`;
        fetch('lab_resources.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: body
        })
        .then(response => response.json())
        .then(data => {
            console.log('Update response:', data);
            if (data.status === 'success') {
                console.log('Status updated to is_active:', data.is_active);
                loadResources();
                bootstrap.Modal.getInstance(document.getElementById('editStatusModal')).hide();
            } else {
                alert('Error: ' + (data.message || 'Failed to update resource status'));
            }
        })
        .catch(error => {
            console.error('Error updating status:', error);
            alert('An error occurred while updating the resource status');
        });
    }

    // Function to delete resource
    function deleteResource(id) {
        const timestamp = new Date().getTime();
        fetch('lab_resources.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete_lab_resource&id=${encodeURIComponent(id)}&_=${timestamp}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                loadResources();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete resource'));
            }
        })
        .catch(error => {
            console.error('Error deleting resource:', error);
            alert('An error occurred while deleting the resource');
        });
    }

    // Handle form submission for upload
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const statusElement = document.getElementById('uploadStatus');
        statusElement.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Uploading...';
        statusElement.className = 'mt-2 text-primary';
        
        fetch('lab_resources.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                statusElement.innerHTML = '<div class="text-success"><i class="fas fa-check-circle"></i> ' + data.message + '</div>';
                this.reset();
                loadResources();
            } else {
                statusElement.innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-circle"></i> ' + data.message + '</div>';
            }
        })
        .catch(error => {
            console.error('Error uploading file:', error);
            statusElement.innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-circle"></i> An error occurred during upload</div>';
        });
    });

    // Handle form submission for edit status
    document.getElementById('editStatusForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('edit_resource_id').value;
        const isActive = document.getElementById('edit_is_active').value;
        console.log('Form submitted with ID:', id, 'is_active:', isActive);
        updateResourceStatus(id, isActive);
    });

    // Initial load of resources
    document.addEventListener('DOMContentLoaded', loadResources);
</script>
</body>
</html>