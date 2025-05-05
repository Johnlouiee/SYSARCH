<?php
class LabManagement {
    private $db;
    private $labs = ['Lab 1', 'Lab 2', 'Lab 3', 'Lab 4'];
    private $computerControl;

    public function __construct() {
        $this->db = new PDO('mysql:host=localhost;dbname=my_database', 'root', '');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->computerControl = new ComputerControl();
    }

    // Initialize all labs
    public function initializeAllLabs() {
        foreach ($this->labs as $lab) {
            $this->computerControl->initializeLab($lab);
        }
    }

    // Get lab schedule
    public function getLabSchedule($labName, $date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM lab_schedules 
                WHERE lab_name = ? 
                AND schedule_date <= ? 
                AND (schedule_end_date >= ? OR schedule_end_date IS NULL)
                AND is_active = 1
            ");
            $stmt->execute([$labName, $date, $date]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting lab schedule: " . $e->getMessage());
            return [];
        }
    }

    // Check PC availability
    public function isPCAvailable($labName, $pcId, $date, $time) {
        try {
            // Check reservations
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM reservations 
                WHERE lab = ? 
                AND pc_number = ? 
                AND reservation_date = ? 
                AND time_in = ? 
                AND status = 'Accepted'
            ");
            $stmt->execute([$labName, $pcId, $date, $time]);
            $hasReservation = $stmt->fetchColumn() > 0;

            // Check current usage
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM lab_usage 
                WHERE lab_name = ? 
                AND id = ? 
                AND session_end IS NULL 
                AND student_id != 'SYSTEM'
            ");
            $stmt->execute([$labName, $pcId]);
            $isInUse = $stmt->fetchColumn() > 0;

            return !$hasReservation && !$isInUse;
        } catch (PDOException $e) {
            error_log("Error checking PC availability: " . $e->getMessage());
            return false;
        }
    }

    // Create reservation
    public function createReservation($userId, $studentName, $purpose, $lab, $pcNumber, $timeIn, $reservationDate) {
        try {
            // Check if PC is available
            if (!$this->isPCAvailable($lab, $pcNumber, $reservationDate, $timeIn)) {
                return false;
            }

            $stmt = $this->db->prepare("
                INSERT INTO reservations (
                    user_id, student_name, purpose, lab, pc_number, 
                    time_in, reservation_date, remaining_session, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 30, 'Pending')
            ");
            return $stmt->execute([
                $userId, $studentName, $purpose, $lab, $pcNumber,
                $timeIn, $reservationDate
            ]);
        } catch (PDOException $e) {
            error_log("Error creating reservation: " . $e->getMessage());
            return false;
        }
    }

    // Get lab status overview
    public function getLabStatus() {
        $status = [];
        foreach ($this->labs as $lab) {
            $computers = $this->computerControl->getLabComputers($lab);
            $available = 0;
            $inUse = 0;
            $offline = 0;

            foreach ($computers as $pc) {
                switch ($pc['status']) {
                    case 'available':
                        $available++;
                        break;
                    case 'in_use':
                        $inUse++;
                        break;
                    default:
                        $offline++;
                }
            }

            $status[$lab] = [
                'total' => 50,
                'available' => $available,
                'in_use' => $inUse,
                'offline' => $offline
            ];
        }
        return $status;
    }

    // Get PC status with icon
    public function getPCStatusWithIcon($labName, $pcId) {
        $status = $this->computerControl->getPCStatus($labName, $pcId);
        $icon = '0'; // Base icon

        switch ($status) {
            case 'available':
                $icon = '🟢'; // Green circle for available
                break;
            case 'in_use':
                $icon = '🔴'; // Red circle for in use
                break;
            case 'offline':
                $icon = '⚫'; // Black circle for offline
                break;
        }

        return [
            'status' => $status,
            'icon' => $icon
        ];
    }

    // Get all reservations for a lab
    public function getLabReservations($labName, $date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        try {
            $stmt = $this->db->prepare("
                SELECT r.*, u.username 
                FROM reservations r
                JOIN users u ON r.user_id = u.idno
                WHERE r.lab = ? 
                AND r.reservation_date = ?
                ORDER BY r.time_in
            ");
            $stmt->execute([$labName, $date]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting lab reservations: " . $e->getMessage());
            return [];
        }
    }
}

// Example usage:
/*
$labManager = new LabManagement();

// Initialize all labs
$labManager->initializeAllLabs();

// Get lab status overview
$status = $labManager->getLabStatus();

// Create a reservation
$labManager->createReservation(
    '1010',
    'John Doe',
    'Programming',
    'Lab 1',
    'PC-1',
    '10:00:00',
    '2025-04-10'
);

// Get PC status with icon
$pcStatus = $labManager->getPCStatusWithIcon('Lab 1', 1);

// Get lab schedule
$schedule = $labManager->getLabSchedule('Lab 1');

// Get lab reservations
$reservations = $labManager->getLabReservations('Lab 1');
*/
?> 