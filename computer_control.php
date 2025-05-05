<?php
session_start();



class ComputerControl {
    private $db;
    private $pcIcons = [
        'available' => '💻',  // PC icon for available
        'in_use' => '🖥️',    // Desktop PC for in use
        'offline' => '📺',    // Monitor for offline
        'maintenance' => '🔧', // Wrench for maintenance
        'reserved' => '📅'    // Calendar for reserved - Ensure this exists
    ];

    public function __construct() {
        // Database connection using existing database
        $this->db = new PDO('mysql:host=localhost;dbname=my_database', 'root', '');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Initialize lab with 50 PCs
    public function initializeLab($labName) {
        try {
            // Check if lab already exists in lab_usage table
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM lab_usage WHERE lab_name = ?");
            $stmt->execute([$labName]);
            $count = $stmt->fetchColumn();

            // If lab doesn't have 50 PCs, initialize them
            if ($count < 50) {
                // Delete existing records for this lab to start fresh
                $stmt = $this->db->prepare("DELETE FROM lab_usage WHERE lab_name = ?");
                $stmt->execute([$labName]);

                // Initialize 50 PCs for the lab
                for ($i = 1; $i <= 50; $i++) {
                    $stmt = $this->db->prepare("
                        INSERT INTO lab_usage (student_id, lab_name, session_start, status) 
                        VALUES (?, ?, NOW(), 'available')
                    ");
                    $stmt->execute(['SYSTEM', $labName]);
                }
            }
            return true;
        } catch (PDOException $e) {
            error_log("Error initializing lab: " . $e->getMessage());
            return false;
        }
    }

    // Get all PCs in a lab with icons
    public function getLabComputers($labName) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    lu.id,
                    lu.student_id,
                    lu.lab_name,
                    lu.session_start,
                    lu.session_end,
                    lu.duration_minutes,
                    lu.points_earned,
                    COALESCE(cc.status, 'available') as control_status,
                    lu.student_id as usage_student_id,
                    lu.session_end as usage_session_end,
                    r.id as reservation_id,
                    r.status as reservation_status
                FROM lab_usage lu
                LEFT JOIN computer_control cc ON lu.lab_name = cc.lab_name AND lu.id = cc.pc_number
                LEFT JOIN reservations r ON cc.reservation_id = r.id
                WHERE lu.lab_name = ?
                ORDER BY lu.id
            ");
            $stmt->execute([$labName]);
            $computersData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $computers = [];
            foreach ($computersData as $data) {
                $status = 'offline'; // Default to offline if none of the conditions below match
                if ($data['control_status'] === 'reserved') {
                    $status = 'reserved';
                } elseif ($data['control_status'] === 'maintenance') {
                    $status = 'maintenance';
                } elseif ($data['usage_session_end'] === null && $data['usage_student_id'] !== 'SYSTEM') {
                    $status = 'in_use';
                } elseif ($data['usage_student_id'] === 'SYSTEM' || $data['usage_session_end'] !== null) { // Fixed: Changed IS NOT NULL to !== null
                     // If controlled by SYSTEM or the last session ended, check control_status again, default to available
                     if ($data['control_status'] === 'available') {
                         $status = 'available';
                     } elseif ($data['control_status'] === 'offline') {
                         $status = 'offline'; // Explicitly offline from control table
                     } else {
                         // Default to available if student is SYSTEM or session ended, and not reserved/maintenance/offline in control table
                         $status = 'available'; 
                     }
                }

                // Ensure the 'reserved' icon exists in your $pcIcons array
                if (!isset($this->pcIcons['reserved'])) {
                     $this->pcIcons['reserved'] = '📅'; // Add default if missing
                }
                
                $data['status'] = $status;
                $data['icon'] = $this->pcIcons[$status] ?? $this->pcIcons['offline'];
                $computers[] = $data;
            }
    
            return $computers;
        } catch (PDOException $e) {
            error_log("Error getting lab computers: " . $e->getMessage());
            return [];
        }
    }

    // Display lab layout with PC icons
    public function displayLabLayout($labName) {
        $computers = $this->getLabComputers($labName);
        $layout = "<div class='lab-layout' id='lab-{$labName}'>\n";
        $layout .= "<h2>{$labName}</h2>\n";
        $layout .= "<div class='pc-grid'>\n";

        // Display PCs in a 5x10 grid
        for ($i = 0; $i < 50; $i++) {
            if ($i % 10 === 0) {
                $layout .= "<div class='pc-row'>\n";
            }

            $pc = $computers[$i] ?? ['status' => 'offline', 'icon' => $this->pcIcons['offline']];
            $pcNumber = $i + 1;
            
            $layout .= "<div class='pc-item' data-pc-id='{$pcNumber}' data-status='{$pc['status']}'>\n";
            $layout .= "<span class='pc-icon'>{$pc['icon']}</span>\n";
            $layout .= "<span class='pc-number'>PC-{$pcNumber}</span>\n";
            $layout .= "<span class='pc-status'>{$pc['status']}</span>\n";
            $layout .= "</div>\n";

            if ($i % 10 === 9) {
                $layout .= "</div>\n";
            }
        }

        $layout .= "</div>\n"; // Close pc-grid
        $layout .= "</div>\n"; // Close lab-layout

        return $layout;
    }

    // Get lab schedule status
    public function getLabScheduleStatus($labName) {
        try {
            $currentDate = date('Y-m-d');
            $currentTime = date('H:i:s');
            
            $stmt = $this->db->prepare("
                SELECT 
                    ls.*,
                    u.firstname,
                    u.lastname
                FROM lab_schedules ls
                LEFT JOIN users u ON ls.uploaded_by = u.idno
                WHERE ls.lab_name = ? 
                AND ls.schedule_date <= ? 
                AND (ls.schedule_end_date >= ? OR ls.schedule_end_date IS NULL)
                AND ls.is_active = 1
                ORDER BY ls.schedule_date DESC, ls.uploaded_at DESC
                LIMIT 1
            ");
            $stmt->execute([$labName, $currentDate, $currentDate]);
            $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($schedule) {
                return [
                    'status' => 'occupied',
                    'title' => $schedule['title'],
                    'description' => $schedule['description'],
                    'schedule_date' => $schedule['schedule_date'],
                    'schedule_end_date' => $schedule['schedule_end_date'],
                    'uploaded_by' => $schedule['firstname'] . ' ' . $schedule['lastname']
                ];
            }
            
            return [
                'status' => 'available',
                'title' => 'No Schedule',
                'description' => 'Laboratory is available for use',
                'schedule_date' => null,
                'schedule_end_date' => null,
                'uploaded_by' => null
            ];
        } catch (PDOException $e) {
            error_log("Error getting lab schedule: " . $e->getMessage());
            return [
                'status' => 'unknown',
                'title' => 'Error',
                'description' => 'Unable to fetch schedule information',
                'schedule_date' => null,
                'schedule_end_date' => null,
                'uploaded_by' => null
            ];
        }
    }

    // Get all labs with their status
    public function getAllLabsStatus() {
        $labs = ['Lab 1', 'Lab 2', 'Lab 3', 'Lab 4']; // Or fetch dynamically if needed
        $status = [];
        
        foreach ($labs as $lab) {
            $scheduleStatus = $this->getLabScheduleStatus($lab);
            $pcStatus = $this->getLabComputers($lab); // Get detailed PC statuses
            
            $availablePCs = 0;
            $inUsePCs = 0;
            $reservedPCs = 0;
            $maintenancePCs = 0;
            $totalActivePCs = 0; // Total PCs excluding offline ones
            
            foreach ($pcStatus as $pc) {
                if ($pc['status'] !== 'offline') {
                    $totalActivePCs++; // Count only non-offline PCs towards the total displayed
                    switch ($pc['status']) {
                        case 'available':
                            $availablePCs++;
                            break;
                        case 'in_use':
                            $inUsePCs++;
                            break;
                        case 'reserved':
                            $reservedPCs++; // Optionally track reserved
                            break;
                        case 'maintenance':
                            $maintenancePCs++; // Optionally track maintenance
                            break;
                    }
                }
            }
            
            $status[$lab] = [
                'schedule_status' => $scheduleStatus['status'],
                'schedule_info' => $scheduleStatus,
                'available_pcs' => $availablePCs,
                'in_use_pcs' => $inUsePCs,
                'reserved_pcs' => $reservedPCs, // Add if needed
                'maintenance_pcs' => $maintenancePCs, // Add if needed
                'total_pcs' => $totalActivePCs // Use the count of non-offline PCs
            ];
        }
        
        return $status;
    }

    // Display all labs
    public function displayAllLabs() {
        $html = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Computer Control - College of Computer Studies</title>
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
                .header h1 {
                    margin: 0;
                    font-size: 24px;
                }
                .header div {
                    display: flex;
                    align-items: center;
                }
                .header a {
                    color: white;
                    text-decoration: none;
                    margin: 0 10px;
                    font-size: 14px;
                }
                .header a:hover {
                    text-decoration: underline;
                }
                .logout-btn {
                    display: inline-block;
                    padding: 10px 20px;
                    background: red;
                    color: white;
                    text-decoration: none;
                    border-radius: 4px;
                }
                .logout-btn:hover {
                    background: darkred;
                }
                .lab-layout {
                    padding: 20px;
                    background: #f5f5f5;
                    border-radius: 8px;
                    margin: 20px;
                    display: none;
                }
                .lab-layout.active {
                    display: block;
                }
                .lab-layout h2 {
                    text-align: center;
                    color: #333;
                    margin-bottom: 20px;
                }
                .pc-grid {
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                }
                .pc-row {
                    display: flex;
                    justify-content: center;
                    gap: 10px;
                }
                .pc-item {
                    background: white;
                    padding: 10px;
                    border-radius: 5px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    width: 80px;
                    cursor: pointer;
                    transition: transform 0.2s;
                }
                .pc-item:hover {
                    transform: scale(1.05);
                }
                .pc-icon {
                    font-size: 24px;
                    margin-bottom: 5px;
                }
                .pc-number {
                    font-size: 12px;
                    color: #666;
                }
                .pc-status {
                    font-size: 10px;
                    color: #999;
                    text-transform: capitalize;
                }
                .pc-item[data-status="available"] {
                    border: 2px solid #4CAF50;
                }
                .pc-item[data-status="in_use"] {
                    border: 2px solid #f44336;
                }
                .pc-item[data-status="offline"] {
                    border: 2px solid #9e9e9e;
                }
                .pc-item[data-status="maintenance"] {
                    border: 2px solid #ffeb3b;
                }
                .container {
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .lab-selector {
                    text-align: center;
                    margin: 20px 0;
                }
                .lab-selector select {
                    padding: 10px;
                    font-size: 16px;
                    border-radius: 5px;
                    border: 1px solid #ddd;
                    background-color: white;
                    cursor: pointer;
                }
                .lab-selector select:focus {
                    outline: none;
                    border-color: #4CAF50;
                }
                .status-legend {
                    text-align: center;
                    margin: 20px 0;
                    padding: 10px;
                    background: white;
                    border-radius: 5px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .status-legend span {
                    margin: 0 15px;
                    display: inline-flex;
                    align-items: center;
                }
                .status-legend i {
                    margin-right: 5px;
                }
                .lab-status-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 20px;
                    margin: 20px 0;
                }
                .lab-status-card {
                    background: white;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    text-align: center;
                }
                .lab-status-card h3 {
                    margin: 0 0 10px 0;
                    color: #333;
                }
                .lab-status-card .status {
                    font-size: 18px;
                    font-weight: bold;
                    margin: 10px 0;
                }
                .lab-status-card .status.available {
                    color: #4CAF50;
                }
                .lab-status-card .status.occupied {
                    color: #f44336;
                }
                .lab-status-card .pc-count {
                    font-size: 14px;
                    color: #666;
                }
                .schedule-info {
                    font-size: 14px;
                    color: #666;
                    margin-top: 5px;
                }
                .schedule-info p {
                    margin: 3px 0;
                }
                .schedule-title {
                    font-weight: bold;
                    color: #333;
                }
                .schedule-date {
                    color: #666;
                    font-style: italic;
                }
                .schedule-uploader {
                    color: #888;
                    font-size: 12px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div>
                    <h1>College of Computer Studies Admin</h1>
                    <a href="admin_home.php">Home</a>
                    <a href="#" id="searchLink">Search</a>
                    <a href="view_current_sitin.php">Current Sit-in</a>
                    <a href="view_sitin.php">Sit-in Records</a>
                    <a href="sitin_reports.php">Sit-in Reports</a>
                    <a href="view_feedback.php">View Feedback</a>
                    <a href="view_reservation.php">View Reservation</a>
                    <a href="student_management.php">Student Information</a>
                    <a href="lab_schedule.php">Lab Schedule</a>
                    <a href="lab_resources.php">Lab Resources</a>
                    <a href="computer_control.php">Computer Control</a>
                </div>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>

            <div class="container">
                <div class="lab-status-grid">';

        // Display lab status cards with schedule information
        $labsStatus = $this->getAllLabsStatus();
        foreach ($labsStatus as $lab => $status) {
            $html .= "
                <div class='lab-status-card'>
                    <h3>{$lab}</h3>
                    <div class='status " . ($status['schedule_status'] === 'available' ? 'available' : 'occupied') . "'>
                        " . ucfirst($status['schedule_status']) . "
                    </div>
                    <div class='schedule-info'>
                        <p class='schedule-title'>{$status['schedule_info']['title']}</p>
                        <p class='schedule-date'>";
            
            if ($status['schedule_info']['schedule_date']) {
                $html .= "From: " . date('M d, Y', strtotime($status['schedule_info']['schedule_date']));
                if ($status['schedule_info']['schedule_end_date']) {
                    $html .= " to " . date('M d, Y', strtotime($status['schedule_info']['schedule_end_date']));
                }
            }
            
            $html .= "</p>";
            
            if ($status['schedule_info']['uploaded_by']) {
                $html .= "<p class='schedule-uploader'>Scheduled by: {$status['schedule_info']['uploaded_by']}</p>";
            }
            
            $html .= "
                    </div>
                    <div class='pc-count'>
                        Available PCs: {$status['available_pcs']} / {$status['total_pcs']}
                    </div>
                </div>";
        }

        $html .= '</div>
                <div class="lab-selector">
                    <select id="labSelect" onchange="changeLab(this.value)">
                        <option value="">Select Laboratory</option>';

        // Add lab options
        $labs = ['Lab 1', 'Lab 2', 'Lab 3', 'Lab 4'];
        foreach ($labs as $lab) {
            $html .= "<option value='{$lab}'>{$lab}</option>";
        }

        $html .= '</select>
                </div>
                <div class="status-legend">
                    <span><i>💻</i> Available</span>
                    <span><i>🖥️</i> In Use</span>
                    <span><i>📅</i> Reserved</span>  <!-- Added Reserved Legend -->
                    <span><i>📺</i> Offline</span>
                    <span><i>🔧</i> Maintenance</span>
                </div>';

        // Initialize and display each lab
        foreach ($labs as $lab) {
            $this->initializeLab($lab);
            $html .= $this->displayLabLayout($lab);
        }

        $html .= '</div>
            <script>
                function changeLab(labName) {
                    // Hide all labs
                    document.querySelectorAll(".lab-layout").forEach(lab => {
                        lab.style.display = "none";
                    });
                    
                    // Show selected lab
                    if (labName) {
                        const selectedLab = document.getElementById("lab-" + labName);
                        if (selectedLab) {
                            selectedLab.style.display = "block";
                        }
                    }
                }

                // Add click event listeners to PC items
                document.querySelectorAll(".pc-item").forEach(pc => {
                    pc.addEventListener("click", function() {
                        const pcId = this.getAttribute("data-pc-id");
                        const labName = this.closest(".lab-layout").querySelector("h2").textContent;
                        const status = this.querySelector(".pc-status").textContent;
                        alert(`PC ${pcId} in ${labName}\nStatus: ${status}`);
                    });
                });
            </script>
        </body>
        </html>';

        return $html;
    }

    // Update PC status
    public function updatePCStatus($labName, $pcId, $status) {
        try {
            if ($status === 'available') {
                $stmt = $this->db->prepare("
                    UPDATE lab_usage 
                    SET student_id = 'SYSTEM',
                        session_start = NOW(),
                        session_end = NULL,
                        duration_minutes = NULL,
                        points_earned = 0
                    WHERE id = ? AND lab_name = ?
                ");
            } else {
                $stmt = $this->db->prepare("
                    UPDATE lab_usage 
                    SET status = ?
                    WHERE id = ? AND lab_name = ?
                ");
            }
            return $stmt->execute([$status, $pcId, $labName]);
        } catch (PDOException $e) {
            error_log("Error updating PC status: " . $e->getMessage());
            return false;
        }
    }

    // Get PC status
    public function getPCStatus($labName, $pcId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    CASE 
                        WHEN session_end IS NULL AND student_id != 'SYSTEM' THEN 'in_use'
                        WHEN student_id = 'SYSTEM' THEN 'available'
                        ELSE 'offline'
                    END as status
                FROM lab_usage 
                WHERE lab_name = ? AND id = ?
            ");
            $stmt->execute([$labName, $pcId]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting PC status: " . $e->getMessage());
            return null;
        }
    }

    // Get all labs
    public function getAllLabs() {
        try {
            $stmt = $this->db->query("
                SELECT DISTINCT lab_name 
                FROM lab_usage 
                ORDER BY lab_name
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting labs: " . $e->getMessage());
            return [];
        }
    }

    // Assign PC to student
    public function assignPCToStudent($labName, $pcId, $studentId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE lab_usage 
                SET student_id = ?,
                    session_start = NOW(),
                    session_end = NULL
                WHERE id = ? AND lab_name = ? AND student_id = 'SYSTEM'
            ");
            return $stmt->execute([$studentId, $pcId, $labName]);
        } catch (PDOException $e) {
            error_log("Error assigning PC: " . $e->getMessage());
            return false;
        }
    }

    // Reserve PC from student
    // Add method to handle PC reservations
    public function reservePC($labName, $pcId, $studentId) {
        try {
            // Check if PC is available
            $stmt = $this->db->prepare("
                SELECT status 
                FROM lab_usage 
                WHERE id = ? AND lab_name = ?
            ");
            $stmt->execute([$pcId, $labName]);
            $status = $stmt->fetchColumn();

            if ($status === 'available') {
                // Update PC status to reserved
                $stmt = $this->db->prepare("
                    UPDATE lab_usage 
                    SET status = 'reserved',
                        student_id = ?
                    WHERE id = ? AND lab_name = ?
                ");
                return $stmt->execute([$studentId, $pcId, $labName]);
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error reserving PC: " . $e->getMessage());
            return false;
        }
    }

    // Rename one of the methods to avoid conflict
    public function fetchLabComputers($labName) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    lu.id,
                    lu.student_id,
                    lu.lab_name,
                    lu.session_start,
                    lu.session_end,
                    lu.duration_minutes,
                    lu.points_earned,
                    COALESCE(cc.status, 'available') as control_status,
                    lu.student_id as usage_student_id,
                    lu.session_end as usage_session_end,
                    r.id as reservation_id,
                    r.status as reservation_status
                FROM lab_usage lu
                LEFT JOIN computer_control cc ON lu.lab_name = cc.lab_name AND lu.id = cc.pc_number
                LEFT JOIN reservations r ON cc.reservation_id = r.id
                WHERE lu.lab_name = ?
                ORDER BY lu.id
            ");
            $stmt->execute([$labName]);
            $computersData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $computers = [];
            foreach ($computersData as $data) {
                $status = 'offline';
                if ($data['control_status'] === 'reserved') {
                    $status = 'reserved';
                } elseif ($data['control_status'] === 'maintenance') {
                    $status = 'maintenance';
                } elseif ($data['usage_session_end'] === null && $data['usage_student_id'] !== 'SYSTEM') {
                    $status = 'in_use';
                } elseif ($data['usage_student_id'] === 'SYSTEM' || $data['usage_session_end'] !== null) {
                     // If controlled by SYSTEM or the last session ended, check control_status again, default to available
                     if ($data['control_status'] === 'available') {
                         $status = 'available';
                     } elseif ($data['control_status'] === 'offline') {
                         $status = 'offline';
                     } else {
                         $status = 'available';
                     }
                }

                if (!isset($this->pcIcons['reserved'])) {
                     $this->pcIcons['reserved'] = '📅';
                }
                
                $data['status'] = $status;
                $data['icon'] = $this->pcIcons[$status] ?? $this->pcIcons['offline'];
                $computers[] = $data;
            }
    
            return $computers;
        } catch (PDOException $e) {
            error_log("Error getting lab computers: " . $e->getMessage());
            return [];
        }
    }

    // Release PC from student
    public function releasePC($labName, $pcId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE lab_usage 
                SET student_id = 'SYSTEM',
                    session_end = NOW(),
                    duration_minutes = TIMESTAMPDIFF(MINUTE, session_start, NOW())
                WHERE id = ? AND lab_name = ? AND student_id != 'SYSTEM'
            ");
            return $stmt->execute([$pcId, $labName]);
        } catch (PDOException $e) {
            error_log("Error releasing PC: " . $e->getMessage());
            return false;
        }
    }
}

// If this file is accessed directly, display all labs
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $control = new ComputerControl();
    echo $control->displayAllLabs();
}
?>