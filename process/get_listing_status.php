<?php
session_start();
require '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$ids_param = $_GET['ids'] ?? '';
if (empty($ids_param)) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

// Security: filter IDs
$ids = array_filter(explode(',', $ids_param), 'is_numeric');
if (empty($ids)) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

$idList = implode(',', $ids);

$q = mysqli_query($conn, "
    SELECT id, type, tree_count, rice_area, remaining_amount, status 
    FROM carbon_listings 
    WHERE id IN ($idList)
");

$data = [];
if ($q) {
    while ($row = mysqli_fetch_assoc($q)) {
        $rem = $row['remaining_amount'] ?? ($row['type'] === 'tree' ? $row['tree_count'] : $row['rice_area']);
        $data[$row['id']] = [
            'remaining_amount' => (float)$rem,
            'status' => $row['status']
        ];
    }
}

echo json_encode(['success' => true, 'data' => $data]);
?>
