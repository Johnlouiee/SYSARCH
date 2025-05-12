<?php
include 'db_connect.php';

$labs = ['Lab 1', 'Lab 2', 'Lab 3', 'Lab 4']; // Note the space after Lab
$pcs_per_lab = 50;

foreach ($labs as $lab) {
    for ($i = 1; $i <= $pcs_per_lab; $i++) {
        $pc = "PC-" . $i; // Changed to use hyphen format
        $sql = "INSERT INTO computer_control (lab_name, pc_number, status) VALUES (?, ?, 'available')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $lab, $pc);
        $stmt->execute();
    }
}
echo "Successfully inserted 200 PCs (4 labs × 50 PCs) into computer_control with correct PC numbering format";
?>