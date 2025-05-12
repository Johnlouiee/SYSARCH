<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



class ComputerControl {
    private $db;
    private $pcIcons = [
        'available' => '🟢',
        'in_use' => '🖥️',
        'offline' => '⚫',
        'maintenance' => '🔧',
        'reserved' => '📅',
        'pending' => '⏳'
    ];

    public function __construct() {
        // Database connection using existing database
        $this->db = new PDO('mysql:host=localhost;dbname=my_database', 'root', '');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Initialize lab with 50 PCs
    public function initializeLab($labName) {
        try {
            // Check if lab already exists in computer_control table
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM computer_control WHERE lab_name = ?");
            $stmt->execute([$labName]);
            $count = $stmt->fetchColumn();

            // If lab doesn't have 50 PCs, initialize them
            if ($count < 50) {
                // Begin transaction
                $this->db->beginTransaction();

                try {
                    // Delete existing records for this lab to start fresh
                    $stmt = $this->db->prepare("DELETE FROM computer_control WHERE lab_name = ?");
                    $stmt->execute([$labName]);

                    // Initialize 50 PCs for the lab
                    for ($i = 1; $i <= 50; $i++) {
                        $pc_number = "PC-" . $i;
                        $stmt = $this->db->prepare("
                            INSERT INTO computer_control 
                            (lab_name, pc_number, status, last_update) 
                            VALUES (?, ?, 'offline', NOW())
                        ");
                        $stmt->execute([$labName, $pc_number]);
                    }

                    // Commit the transaction
                    $this->db->commit();
                    error_log("Successfully initialized lab {$labName} with 50 PCs");
                } catch (PDOException $e) {
                    // Rollback the transaction on error
                    $this->db->rollBack();
                    throw $e;
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
            // First ensure the lab is initialized
            $this->initializeLab($labName);

            // Fetch computer control status and linked reservation details
            $stmt = $this->db->prepare("
                SELECT
                    cc.id as control_id,
                    cc.pc_number,
                    cc.lab_name,
                    cc.status as control_status,
                    cc.reservation_id,
                    r.status as reservation_status,
                    r.student_name as reserved_by,
                    r.purpose as reservation_purpose,
                    r.reservation_date,
                    r.time_slot_start,
                    r.time_slot_end
                FROM computer_control cc
                LEFT JOIN reservations r ON cc.reservation_id = r.id 
                WHERE cc.lab_name = ?
                ORDER BY CAST(SUBSTRING(cc.pc_number, 4) AS UNSIGNED)
            ");
            $stmt->execute([$labName]);
            $controlData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Create a map of PC data keyed by pc_number for easy lookup
            $pc_map = [];
            if (is_array($controlData)) {
                foreach ($controlData as $data) {
                    if (!isset($data['pc_number'])) continue;
                    
                    // Determine the effective status based on control and reservation data
                    $status = $data['control_status'] ?? 'offline';

                    // If PC has a pending reservation, keep it as pending
                    if ($status === 'pending' && isset($data['reservation_status']) && $data['reservation_status'] === 'Pending') {
                        $status = 'pending';
                    }
                    // If PC has an accepted reservation, mark it as reserved
                    else if (isset($data['reservation_status']) && $data['reservation_status'] === 'Accepted') {
                        $status = 'reserved';
                    }
                    // If no reservation or reservation is not accepted, keep it offline
                    else if (!isset($data['reservation_status']) || $data['reservation_status'] !== 'Accepted') {
                        $status = 'offline';
                    }

                    $pc_map[$data['pc_number']] = [
                        'id' => $data['control_id'] ?? null,
                        'pc_number' => $data['pc_number'],
                        'lab_name' => $data['lab_name'] ?? $labName,
                        'status' => $status,
                        'icon' => $this->pcIcons[$status] ?? $this->pcIcons['offline'],
                        'control_status' => $data['control_status'] ?? 'offline',
                        'reservation_id' => $data['reservation_id'] ?? null,
                        'reservation_status' => $data['reservation_status'] ?? null,
                        'reserved_by' => $data['reserved_by'] ?? null,
                        'reservation_purpose' => $data['reservation_purpose'] ?? null,
                        'reservation_date' => $data['reservation_date'] ?? null,
                        'time_slot_start' => $data['time_slot_start'] ?? null,
                        'time_slot_end' => $data['time_slot_end'] ?? null
                    ];
                }
            }

            // Ensure we have exactly 50 PCs
            $computers = [];
            for ($i = 1; $i <= 50; $i++) {
                $pc_num = "PC-" . $i;
                if (isset($pc_map[$pc_num])) {
                    $computers[] = $pc_map[$pc_num];
                } else {
                    // If PC doesn't exist in the map, create a new record
                    $stmt = $this->db->prepare("
                        INSERT INTO computer_control 
                        (lab_name, pc_number, status, last_update) 
                        VALUES (?, ?, 'offline', NOW())
                    ");
                    $stmt->execute([$labName, $pc_num]);
                    
                    $computers[] = [
                        'id' => null,
                        'pc_number' => $pc_num,
                        'lab_name' => $labName,
                        'status' => 'offline',
                        'icon' => $this->pcIcons['offline'],
                        'control_status' => 'offline',
                        'reservation_id' => null,
                        'reservation_status' => null,
                        'reserved_by' => null,
                        'reservation_purpose' => null,
                        'reservation_date' => null,
                        'time_slot_start' => null,
                        'time_slot_end' => null
                    ];
                }
            }

            return $computers;
        } catch (PDOException $e) {
            error_log("Error getting lab computers: " . $e->getMessage());
            return array_fill(0, 50, [
                'id' => null,
                'pc_number' => null,
                'lab_name' => $labName,
                'status' => 'offline',
                'icon' => $this->pcIcons['offline'],
                'control_status' => 'offline',
                'reservation_id' => null,
                'reservation_status' => null,
                'reserved_by' => null,
                'reservation_purpose' => null,
                'reservation_date' => null,
                'time_slot_start' => null,
                'time_slot_end' => null
            ]);
        }
    }

    // Display lab layout with PC icons
    public function displayLabLayout($labName) {
        try {
            // Initialize lab with 50 PCs if not already done
            $this->initializeLab($labName);
            
            // Get all PCs for this lab from database
            $stmt = $this->db->prepare("
                SELECT pc_number, status, reservation_id 
                FROM computer_control 
                WHERE lab_name = ? 
                ORDER BY CAST(SUBSTRING(pc_number, 4) AS UNSIGNED)
            ");
            $stmt->execute([$labName]);
            $pcs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Count available PCs
            $availablePCs = 0;
            foreach ($pcs as $pc) {
                if ($pc['status'] === 'available') {
                    $availablePCs++;
                }
            }
            
            $layout = "<div class='lab-layout' id='lab-{$labName}'>\n";
            $layout .= "<h2>{$labName} - Available PCs: {$availablePCs}/50</h2>\n";
            
            // Add admin controls for lab status if user is admin
            if (isset($_SESSION['user_info']) && $_SESSION['user_info']['role'] === 'admin') {
                $layout .= "<div class='lab-admin-controls'>\n";
                $layout .= "<form action='set_pc_status.php' method='post' class='lab-status-form'>\n";
                $layout .= "<input type='hidden' name='lab' value='{$labName}'>\n";
                $layout .= "<select name='status' onchange='this.form.submit()'>\n";
                $layout .= "<option value='' disabled selected>Set Lab Status</option>\n";
                $layout .= "<option value='available'>Enable All PCs</option>\n";
                $layout .= "<option value='offline'>Disable All PCs</option>\n";
                $layout .= "<option value='maintenance'>Maintenance Mode</option>\n";
                $layout .= "</select>\n";
                $layout .= "</form>\n";
                $layout .= "</div>\n";
            }
            
            $layout .= "<div class='pc-grid'>\n";
            
            // Create 5 rows of 10 PCs each
            for ($row = 0; $row < 5; $row++) {
                $layout .= "<div class='pc-row'>\n";
                
                for ($col = 0; $col < 10; $col++) {
                    $pcIndex = $row * 10 + $col;
                    $pcNumber = "PC-" . ($pcIndex + 1);
                    
                    // Find PC status from database
                    $status = 'available';
                    $reservationId = null;
                    foreach ($pcs as $pc) {
                        if ($pc['pc_number'] === $pcNumber) {
                            $status = $pc['status'];
                            $reservationId = $pc['reservation_id'];
                            break;
                        }
                    }
                    
                    $icon = $this->pcIcons[$status] ?? $this->pcIcons['offline'];
                    
                    // Add PC item with status
                    $layout .= "<div class='pc-item status-{$status}' data-pc-number='{$pcNumber}' data-status='{$status}'>\n";
                    $layout .= "<span class='pc-icon'>{$icon}</span>\n";
                    $layout .= "<span class='pc-number'>{$pcNumber}</span>\n";
                    
                    // Add admin controls if user is admin
                    if (isset($_SESSION['user_info']) && $_SESSION['user_info']['role'] === 'admin') {
                        $layout .= "<div class='pc-admin-controls'>\n";
                        $layout .= "<form action='set_pc_status.php' method='post' class='status-form'>\n";
                        $layout .= "<input type='hidden' name='lab' value='{$labName}'>\n";
                        $layout .= "<input type='hidden' name='pc_number' value='{$pcNumber}'>\n";
                        $layout .= "<select name='status' onchange='this.form.submit()'>\n";
                        $layout .= "<option value='available'" . ($status === 'available' ? ' selected' : '') . ">Enable</option>\n";
                        $layout .= "<option value='offline'" . ($status === 'offline' ? ' selected' : '') . ">Disable</option>\n";
                        $layout .= "<option value='maintenance'" . ($status === 'maintenance' ? ' selected' : '') . ">Maintenance</option>\n";
                        $layout .= "</select>\n";
                        $layout .= "</form>\n";
                        $layout .= "</div>\n";
                    }
                    
                    $layout .= "</div>\n";
                }
                
                $layout .= "</div>\n";
            }
            
            $layout .= "</div>\n"; // Close pc-grid
            $layout .= "</div>\n"; // Close lab-layout
            
            return $layout;
        } catch (PDOException $e) {
            error_log("Error displaying lab layout: " . $e->getMessage());
            return "<div class='error'>Error loading lab layout</div>";
        }
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
                'status' => 'available',
                'title' => 'No Schedule',
                'description' => 'Laboratory is available for use',
                'schedule_date' => null,
                'schedule_end_date' => null,
                'uploaded_by' => null
            ];
        }
    }

    // Get all labs with their status
    public function getAllLabsStatus() {
        $labs = ['Lab 524', 'Lab 526', 'Lab 542', 'Lab 544', 'Lab 517', 'Lab 528']; // Updated lab list
        $status = [];

        foreach ($labs as $lab) {
            $scheduleStatus = $this->getLabScheduleStatus($lab);
            $pcStatus = $this->getLabComputers($lab); // Get detailed PC statuses

            // Initialize counters
            $availablePCs = 0;
            $inUsePCs = 0;
            $reservedPCs = 0;
            $maintenancePCs = 0;
            $offlinePCs = 0;

            // Count PC statuses
            if (is_array($pcStatus)) {
                foreach ($pcStatus as $pc) {
                    if (is_array($pc) && isset($pc['status'])) {
                        switch ($pc['status']) {
                            case 'available':
                                $availablePCs++;
                                break;
                            case 'in_use':
                                $inUsePCs++;
                                break;
                            case 'reserved':
                                $reservedPCs++;
                                break;
                            case 'maintenance':
                                $maintenancePCs++;
                                break;
                            case 'offline':
                                $offlinePCs++;
                                break;
                        }
                    }
                }
            }

            // Determine lab status based on PC availability
            $labStatus = 'available';
            if ($availablePCs === 0) {
                $labStatus = 'occupied';
            }

            $status[$lab] = [
                'schedule_status' => $labStatus,
                'schedule_info' => [
                    'title' => $scheduleStatus['title'] ?? 'No Schedule',
                    'description' => $scheduleStatus['description'] ?? 'Laboratory is available for use',
                    'schedule_date' => $scheduleStatus['schedule_date'] ?? null,
                    'schedule_end_date' => $scheduleStatus['schedule_end_date'] ?? null,
                    'uploaded_by' => $scheduleStatus['uploaded_by'] ?? null
                ],
                'available_pcs' => $availablePCs,
                'in_use_pcs' => $inUsePCs,
                'reserved_pcs' => $reservedPCs,
                'maintenance_pcs' => $maintenancePCs,
                'offline_pcs' => $offlinePCs,
                'total_pcs' => 50
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
                .header a {
                    color: white;
                    text-decoration: none;
                    margin: 0 10px;
                }
                .header a:hover {
                    text-decoration: underline;
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
                    position: relative;
                    transition: all 0.3s ease;
                    padding: 10px;
                    background: white;
                    border-radius: 5px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .pc-item:hover {
                    transform: scale(1.05);
                    z-index: 1;
                    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
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
                .pc-label {
                    font-size: 12px;
                    font-weight: bold;
                    color: #333;
                    margin-top: 2px;
                    background: #f0f0f0;
                    padding: 2px 4px;
                    border-radius: 3px;
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
                .pc-admin-controls {
                    margin-top: 5px;
                    font-size: 12px;
                    display: none; /* Hide by default */
                }
                .pc-item:hover .pc-admin-controls {
                    display: block; /* Show on hover */
                }
                .pc-admin-controls select {
                    padding: 5px;
                    font-size: 12px;
                    border: 1px solid #ddd;
                    border-radius: 3px;
                    background-color: white;
                    cursor: pointer;
                    width: 100%;
                    margin-top: 5px;
                    transition: all 0.3s ease;
                }
                .pc-admin-controls select:hover {
                    border-color: #4CAF50;
                    box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
                }
                .pc-admin-controls select:focus {
                    outline: none;
                    border-color: #4CAF50;
                    box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
                }
                .pc-item[data-status="available"] .pc-status {
                    background: #e8f5e9;
                    color: #2e7d32;
                }
                .pc-item[data-status="in_use"] .pc-status {
                    background: #ffebee;
                    color: #c62828;
                }
                .pc-item[data-status="reserved"] .pc-status {
                    background: #e3f2fd;
                    color: #1565c0;
                }
                .pc-item[data-status="offline"] .pc-status {
                    background: #f5f5f5;
                    color: #616161;
                }
                .pc-item[data-status="maintenance"] .pc-status {
                    background: #fffde7;
                    color: #f57f17;
                }
                .status-form {
                    margin: 0;
                    padding: 0;
                    width: 100%;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div>
                    <h1>    </h1>
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
                <div class="lab-selector">
                    <select id="labSelect" onchange="changeLab(this.value)">
                        <option value="">Select Laboratory</option>';

        // Add lab options
        $labs = ['Lab 524', 'Lab 526', 'Lab 542', 'Lab 544', 'Lab 517', 'Lab 528'];
        foreach ($labs as $lab) {
            $html .= "<option value='{$lab}'>{$lab}</option>";
        }

        $html .= '</select>
                </div>
                <div class="status-legend">
                    <span><i>💻</i> Available</span>
                    <span><i>🖥️</i> In Use</span>
                    <span><i>📅</i> Reserved</span>
                    <span><i>📺</i> Offline</span>
                    <span><i>🔧</i> Maintenance</span>
                </div>';

        // Initialize and display each lab
        foreach ($labs as $lab) {
            $this->initializeLab($lab);
            $html .= $this->displayLabLayout($lab);
        }

        $html .= '</div>
            <script src="js/computer_control.js"></script>
        </body>
        </html>';

        return $html;
    }

    // Update PC status
    public function updatePCStatus($labName, $pcNumber, $status) {
        try {
            if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
                error_log("User is not admin");
                return false;
            }

            // Begin transaction
            $this->db->beginTransaction();

            try {
                // Check if there's a pending reservation for this PC
                $check_sql = "SELECT r.status 
                            FROM reservations r 
                            JOIN computer_control cc ON r.id = cc.reservation_id 
                            WHERE cc.lab_name = ? AND cc.pc_number = ?";
                $check_stmt = $this->db->prepare($check_sql);
                $check_stmt->execute([$labName, $pcNumber]);
                $reservation_status = $check_stmt->fetchColumn();

                // Only allow status change if:
                // 1. There's no reservation
                // 2. The reservation is accepted
                // 3. The admin is changing it to offline/maintenance
                if (!$reservation_status || 
                    $reservation_status === 'Accepted' || 
                    in_array($status, ['offline', 'maintenance'])) {
                    
                // Update computer_control table
                $sql = "UPDATE computer_control 
                       SET status = ?, 
                           last_update = NOW()
                       WHERE lab_name = ? AND pc_number = ?";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([$status, $labName, $pcNumber]);

                if ($result) {
                    // Update pc_availability
                    $avail_sql = "UPDATE pc_availability 
                                SET is_available = CASE 
                                        WHEN ? IN ('available', 'reserved') THEN 1 
                                    ELSE 0 
                                END
                                WHERE lab_name = ? AND pc_number = ?";
                    $avail_stmt = $this->db->prepare($avail_sql);
                    $avail_result = $avail_stmt->execute([$status, $labName, $pcNumber]);

                    // If no rows were updated in pc_availability, insert new record
                    if ($avail_stmt->rowCount() === 0) {
                        $insert_avail_sql = "INSERT INTO pc_availability 
                                           (lab_name, pc_number, is_available)
                                           VALUES (?, ?, CASE 
                                                   WHEN ? IN ('available', 'reserved') THEN 1 
                                               ELSE 0 
                                           END)";
                        $insert_avail_stmt = $this->db->prepare($insert_avail_sql);
                        $avail_result = $insert_avail_stmt->execute([$labName, $pcNumber, $status]);
                    }

                    // Log the status change
                    $log_sql = "INSERT INTO reservation_logs 
                               (reservation_id, action, user_id, details, created_at)
                               VALUES (NULL, 'status_change', ?, ?, NOW())";
                    $log_stmt = $this->db->prepare($log_sql);
                    $log_details = "PC {$pcNumber} in {$labName} status changed to {$status}";
                    $log_stmt->execute([$_SESSION['user_info']['idno'], $log_details]);

                    $this->db->commit();
                    return true;
                    }
                }

                $this->db->rollBack();
                return false;
            } catch (PDOException $e) {
                $this->db->rollBack();
                error_log("Error updating PC status: " . $e->getMessage());
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error in updatePCStatus: " . $e->getMessage());
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
                    if ($data['control_status'] === 'available') {
                        $status = 'available';
                    } elseif ($data['control_status'] === 'offline') {
                        $status = 'offline';
                    } else {
                        $status = 'available';
                    }
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

    // Add this new method to handle automatic PC status updates
    public function updatePCStatusFromReservation($labName, $pcNumber, $reservationId) {
        try {
            // First check if the PC exists in computer_control
            $check_sql = "SELECT id FROM computer_control WHERE lab_name = ? AND pc_number = ?";
            $check_stmt = $this->db->prepare($check_sql);
            $check_stmt->execute([$labName, $pcNumber]);
            
            if ($check_stmt->rowCount() > 0) {
                // Update existing record
                $update_sql = "UPDATE computer_control 
                             SET status = 'in_use',
                                 reservation_id = ?,
                                 last_update = NOW()
                             WHERE lab_name = ? AND pc_number = ?";
                $update_stmt = $this->db->prepare($update_sql);
                $result = $update_stmt->execute([$reservationId, $labName, $pcNumber]);
                
                if (!$result) {
                    error_log("Failed to update computer_control: " . print_r($update_stmt->errorInfo(), true));
                }
                return $result;
            } else {
                // Insert new record
                $insert_sql = "INSERT INTO computer_control 
                             (lab_name, pc_number, status, reservation_id, last_update)
                             VALUES (?, ?, 'in_use', ?, NOW())";
                $insert_stmt = $this->db->prepare($insert_sql);
                $result = $insert_stmt->execute([$labName, $pcNumber, $reservationId]);
                
                if (!$result) {
                    error_log("Failed to insert into computer_control: " . print_r($insert_stmt->errorInfo(), true));
                }
                return $result;
            }
        } catch (PDOException $e) {
            error_log("Error updating PC status from reservation: " . $e->getMessage());
            return false;
        }
    }

    // Set lab status
    public function setLabStatus($labName, $status) {
        try {
            if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
                error_log("User is not admin");
                return false;
            }

            // Begin transaction
            $this->db->beginTransaction();

            try {
                // Update all PCs in the lab
                $sql = "UPDATE computer_control 
                       SET status = ?, 
                           last_update = NOW()
                       WHERE lab_name = ?";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([$status, $labName]);

                if ($result) {
                    // Update pc_availability for all PCs in the lab
                    $avail_sql = "UPDATE pc_availability 
                                SET is_available = CASE 
                                    WHEN ? IN ('available', 'reserved', 'pending') THEN 1 
                                    ELSE 0 
                                END
                                WHERE lab_name = ?";
                    $avail_stmt = $this->db->prepare($avail_sql);
                    $avail_result = $avail_stmt->execute([$status, $labName]);

                    // Log the lab status change
                    $log_sql = "INSERT INTO reservation_logs 
                               (reservation_id, action, user_id, details, created_at)
                               VALUES (NULL, 'lab_status_change', ?, ?, NOW())";
                    $log_stmt = $this->db->prepare($log_sql);
                    $log_details = "Lab {$labName} status changed to {$status}";
                    $log_stmt->execute([$_SESSION['user_info']['idno'], $log_details]);

                    $this->db->commit();
                    error_log("Successfully updated lab {$labName} status to {$status}");
                    return true;
                }

                $this->db->rollBack();
                error_log("Failed to update lab {$labName} status to {$status}");
                return false;
            } catch (PDOException $e) {
                $this->db->rollBack();
                error_log("Database error: " . $e->getMessage());
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error setting lab status: " . $e->getMessage());
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