<?php
session_start();
require_once 'db_connect.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_info']) || $_SESSION['user_info']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get the export format from the URL
$format = isset($_GET['format']) ? strtolower($_GET['format']) : '';

// Fetch sit-in records with student information
$sql = "SELECT 
            sit_in_history.id,
            sit_in_history.user_id,
            CONCAT(users.firstname, ' ', users.lastname) as student_name,
            sit_in_history.purpose,
            sit_in_history.lab,
            sit_in_history.session_start,
            sit_in_history.session_end,
            DATE(sit_in_history.session_start) as date,
            TIMESTAMPDIFF(HOUR, sit_in_history.session_start, IFNULL(sit_in_history.session_end, NOW())) as hours_spent
        FROM sit_in_history 
        JOIN users ON sit_in_history.user_id = users.idno 
        ORDER BY sit_in_history.session_start DESC";
$result = $conn->query($sql);

// Prepare data for export
$data = [];
$headers = ['Sit-in ID', 'Student ID', 'Student Name', 'Purpose', 'Lab', 'Login Time', 'Logout Time', 'Date', 'Hours Spent'];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        $row['id'],
        $row['user_id'],
        $row['student_name'],
        $row['purpose'],
        $row['lab'],
        $row['session_start'],
        $row['session_end'] ? $row['session_end'] : 'Still Active',
        $row['date'],
        $row['hours_spent']
    ];
}

// Export based on format
switch ($format) {
    case 'excel':
        // Set headers for download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="sit_in_records.xls"');
        
        // Start HTML table
        echo '<table border="1">';
        
        // Add headers
        echo '<tr>';
        foreach ($headers as $header) {
            echo '<th>' . htmlspecialchars($header) . '</th>';
        }
        echo '</tr>';
        
        // Add data
        foreach ($data as $row) {
            echo '<tr>';
            foreach ($row as $value) {
                echo '<td>' . htmlspecialchars($value) . '</td>';
            }
            echo '</tr>';
        }
        
        echo '</table>';
        exit;
        break;

    case 'csv':
        // Set headers for download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="sit_in_records.csv"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Add headers
        fputcsv($output, $headers);
        
        // Add data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
        break;

    case 'pdf':
        // Set headers for download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment;filename="sit_in_records.pdf"');
        
        // Start HTML content
        $html = '<html><body>';
        $html .= '<h1>Sit-in Records</h1>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0">';
        
        // Add headers
        $html .= '<tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr>';
        
        // Add data
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $value) {
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</table></body></html>';
        
        // Output HTML
        echo $html;
        exit;
        break;

    case 'docx':
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment;filename="sit_in_records.docx"');
        
        // Start HTML content
        $html = '<html><body>';
        $html .= '<h1>Sit-in Records</h1>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0">';
        
        // Add headers
        $html .= '<tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr>';
        
        // Add data
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $value) {
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</table></body></html>';
        
        // Output HTML
        echo $html;
        exit;
        break;

    default:
        header("Location: view_sitin.php");
        exit();
        break;
}

$conn->close();
?> 