<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Invalid request');
}

// รับค่าจากฟอร์มหลัก
$user_type = $_POST['user_type'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Check for duplicates
$check = pg_query($conn, "SELECT * FROM users WHERE phone = '$phone' OR username = '$username'");
if (pg_num_rows($check) > 0) {
    $row = pg_fetch_assoc($check);
    $msg = '';
    if ($row['phone'] === $phone) $msg = 'เบอร์โทรศัพท์นี้ถูกใช้งานแล้ว';
    if ($row['username'] === $username) $msg = 'Username นี้ถูกใช้งานแล้ว';
    
    $_SESSION['flash_alert'] = [
        'type' => 'error',
        'title' => 'ข้อมูลซ้ำ',
        'message' => $msg
    ];
    header("Location: ../auth/register.php");
    exit;
}

// Handling File Uploads
$uploadDir = '../assets/uploads/docs/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

function uploadFile($inputName, $uploadDir) {
    if (!empty($_FILES[$inputName]['name'])) {
        $ext = pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION);
        $filename = uniqid($inputName . '_') . '.' . $ext;
        move_uploaded_file($_FILES[$inputName]['tmp_name'], $uploadDir . $filename);
        return $filename;
    }
    return '';
}

$gov_doc = ($user_type === 'government') ? uploadFile('gov_doc', $uploadDir) : '';
$corp_cert = ($user_type === 'corporate') ? uploadFile('corp_cert', $uploadDir) : '';
$vat_id = ($user_type === 'corporate') ? uploadFile('vat_id', $uploadDir) : '';
$auth_doc = ($user_type === 'government') ? uploadFile('auth_doc', $uploadDir) : '';
$has_poa = isset($_POST['has_poa']) ? 1 : 0;
$poa_doc = ($user_type === 'government' && $has_poa) ? uploadFile('poa_doc', $uploadDir) : '';

$bank_name = $_POST['bank_name'];
if ($bank_name === 'อื่นๆ') {
    $bank_name = $_POST['bank_other'];
}

// เก็บข้อมูลทั้งหมดไว้ใน Session
$_SESSION['register_data'] = [
    'user_type' => $user_type,
    'username' => $username,
    'password' => $password,
    'phone' => $phone,
    'email' => $email,
    'first_name' => $_POST['first_name'] ?? '',
    'last_name' => $_POST['last_name'] ?? '',
    'national_id' => $_POST['national_id'] ?? '',
    'agency_name' => $_POST['agency_name'] ?? '',
    'tax_id' => ($user_type === 'government') ? ($_POST['tax_id_gov'] ?? '') : ($_POST['tax_id'] ?? ''),
    'agency_address' => $_POST['agency_address'] ?? '',
    'auth_first_name' => $_POST['auth_first_name'] ?? '',
    'auth_last_name' => $_POST['auth_last_name'] ?? '',
    'auth_position' => $_POST['auth_position'] ?? '',
    'auth_doc' => $auth_doc,
    'has_poa' => $has_poa,
    'poa_first_name' => $_POST['poa_first_name'] ?? '',
    'poa_last_name' => $_POST['poa_last_name'] ?? '',
    'poa_position' => $_POST['poa_position'] ?? '',
    'poa_doc' => $poa_doc,
    'gov_doc' => $gov_doc,
    'corp_cert' => $corp_cert,
    'vat_id' => $vat_id,
    'bank_name' => $bank_name,
    'account_number' => $_POST['account_number'],
    'account_name' => $_POST['account_name']
];

// สร้าง OTP
$otp = rand(100000, 999999);
$expire = date("Y-m-d H:i:s", strtotime("+5 minutes"));

// บันทึก OTP ลง DB
$sql = "INSERT INTO otp_verifications (phone, otp_code, expires_at)
        VALUES ('$phone', '$otp', '$expire')";

if (!pg_query($conn, $sql)) {
    die(pg_error($conn));
}

// เก็บ OTP ไว้แสดง (simulation)
$_SESSION['otp_demo'] = $otp;

// ไปหน้า verify OTP
header("Location: ../auth/verify_otp.php");
exit;
