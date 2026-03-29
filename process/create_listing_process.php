<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
    die(json_encode(['success' => false, 'message' => 'ไม่มีสิทธิ์เข้าถึง']));
}

$seller_id     = $_SESSION['user_id'];
$carbon_type   = $_POST['carbon_type'] ?? '';
$province      = $_POST['province'] ?? '';
$tree_count    = !empty($_POST['tree_count']) ? intval($_POST['tree_count']) : null;
$avg_height    = !empty($_POST['avg_height']) ? floatval($_POST['avg_height']) : null;
$tree_age      = !empty($_POST['tree_age']) ? intval($_POST['tree_age']) : null;
$land_area     = !empty($_POST['land_area']) ? floatval($_POST['land_area']) : null;
$rice_age      = !empty($_POST['rice_age']) ? intval($_POST['rice_age']) : null;
$carbon_amount = floatval($_POST['carbon_amount'] ?? 0);
$price         = floatval($_POST['price'] ?? 0);

// File Upload Utility
function uploadImage($fileKey, $isDocument = false) {
    if (empty($_FILES[$fileKey]['name'])) return null;

    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if ($isDocument) $allowedTypes[] = 'application/pdf';
    $maxSize = 5 * 1024 * 1024; // 5MB

    $file = $_FILES[$fileKey];
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['error' => 'ประเภทไฟล์ไม่ถูกต้อง (อนุญาตเฉพาะ jpg, png' . ($isDocument ? ', pdf' : '') . ')'];
    }
    if ($file['size'] > $maxSize) {
        return ['error' => 'ขนาดไฟล์เกิน 5MB'];
    }

    $uploadDir = $isDocument ? '../uploads/documents/' : '../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $uniqueName = time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
    $targetPath = $uploadDir . $uniqueName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $uniqueName;
    } else {
        return ['error' => 'ไม่สามารถย้ายไฟล์ไปที่จัดเก็บได้'];
    }
}

// Upload Files
$full_tree_image = uploadImage('full_tree_image');
$reference_image = uploadImage('reference_image');
$id_card_image   = uploadImage('id_card_image');
$land_document   = uploadImage('land_document', true);

$remaining_amount = ($carbon_type === 'tree') ? $tree_count : $land_area;

// Handle Upload Errors
foreach ([$full_tree_image, $reference_image, $id_card_image, $land_document] as $res) {
    if (is_array($res) && isset($res['error'])) {
        die("<script>alert('Error: " . $res['error'] . "'); window.history.back();</script>");
    }
}

// Prepare SQL
$sql = "INSERT INTO `carbon_listings` 
        (`seller_id`, `type`, `province`, `tree_count`, `tree_age`, `tree_height`, `avg_height`, `rice_area`, `rice_age`, `carbon_amount`, `price_token`, `full_tree_image`, `reference_image`, `id_card_image`, `land_document`, `remaining_amount`, `status`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die("Error preparing statement: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "issiddddidsssssd", 
    $seller_id, 
    $carbon_type, 
    $province, 
    $tree_count, 
    $tree_age, 
    $avg_height, // Mapping avg_height as well
    $avg_height, 
    $land_area, 
    $rice_age, 
    $carbon_amount, 
    $price, 
    $full_tree_image, 
    $reference_image, 
    $id_card_image,
    $land_document,
    $remaining_amount
);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['flash_alert'] = [
        'type' => 'success',
        'title' => 'สำเร็จ',
        'message' => 'ส่งข้อมูลให้ Admin ตรวจสอบเรียบร้อยแล้ว'
    ];
    header("Location: ../dashboard/history.php");
} else {
    echo "Error: " . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
