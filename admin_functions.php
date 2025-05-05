function isPCAvailable($lab, $pc_number) {
    global $conn;
    
    $sql = "SELECT is_available FROM pc_availability 
           WHERE lab_name = ? AND pc_number = ? 
           AND (reserved_to IS NULL OR reserved_to > NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $lab, $pc_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['is_available'] == 1;
    }
    
    return true; // PC is available if no record exists
}