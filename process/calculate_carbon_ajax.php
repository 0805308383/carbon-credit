<?php
header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$carbon = 0;

if ($type === 'tree') {
    $count = floatval($_GET['tree_count'] ?? 0);
    $age = floatval($_GET['tree_age'] ?? 0);
    $height = floatval($_GET['tree_height'] ?? 0);
    
    // Formula from calculator.php: Count * (Height * 0.1 + Age * 0.05)
    $carbon = $count * (($height * 0.1) + ($age * 0.05));
    
} else if ($type === 'rice') {
    $area = floatval($_GET['rice_area'] ?? 0);
    // Formula from calculator.php: Area * 0.5
    $carbon = $area * 0.5;
}

echo json_encode([
    'success' => true,
    'carbon' => round($carbon, 2)
]);
?>
