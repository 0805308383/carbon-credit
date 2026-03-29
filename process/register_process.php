<?php
session_start();
require '../config/db.php';

// ป้องกันเรียกซ้ำ
if (!isset($_SESSION['register_data'])) {
    exit('ขั้นตอนสมัครเสร็จสิ้นแล้ว กรุณาเข้าสู่ระบบ');
}

$inputOtp = $_POST['otp'];
$phone = $_SESSION['register_data']['phone'];

// ตรวจ OTP
$q = pg_query($conn, "
    SELECT * FROM otp_verifications
    WHERE phone='$phone'
    ORDER BY id DESC LIMIT 1
");

$data = pg_fetch_assoc($q);

if (!$data || $data['otp_code'] != $inputOtp) {
    $_SESSION['flash_alert'] = [
        'type' => 'error',
        'title' => 'ผิดพลาด',
        'message' => 'OTP ไม่ถูกต้อง'
    ];
    header("Location: ../auth/verify_otp.php"); 
    exit;
}

// 🔒 ตรวจว่ามีผู้ใช้นี้แล้วหรือยัง 
$username = $_SESSION['register_data']['username'];
$checkUser = pg_query($conn, "
    SELECT id FROM users WHERE phone='$phone' OR username='$username'
");

if (pg_num_rows($checkUser) > 0) {
    // ล้าง session แล้วพาไป login
    unset($_SESSION['register_data']);
    unset($_SESSION['otp_demo']);
    header("Location: ../auth/login.php");
    exit;
}

// INSERT user
$rd = $_SESSION['register_data'];

$n_id = empty($rd['national_id']) ? "NULL" : "'{$rd['national_id']}'";
$a_name = empty($rd['agency_name']) ? "NULL" : "'{$rd['agency_name']}'";
$t_id = empty($rd['tax_id']) ? "NULL" : "'{$rd['tax_id']}'";
$g_doc = empty($rd['gov_doc']) ? "NULL" : "'{$rd['gov_doc']}'";
$c_cert = empty($rd['corp_cert']) ? "NULL" : "'{$rd['corp_cert']}'";
$v_id = empty($rd['vat_id']) ? "NULL" : "'{$rd['vat_id']}'";

$a_addr = empty($rd['agency_address']) ? "NULL" : "'{$rd['agency_address']}'";
$au_fn = empty($rd['auth_first_name']) ? "NULL" : "'{$rd['auth_first_name']}'";
$au_ln = empty($rd['auth_last_name']) ? "NULL" : "'{$rd['auth_last_name']}'";
$au_pos = empty($rd['auth_position']) ? "NULL" : "'{$rd['auth_position']}'";
$au_doc = empty($rd['auth_doc']) ? "NULL" : "'{$rd['auth_doc']}'";
$h_poa = (int)($rd['has_poa'] ?? 0);
$p_fn = empty($rd['poa_first_name']) ? "NULL" : "'{$rd['poa_first_name']}'";
$p_ln = empty($rd['poa_last_name']) ? "NULL" : "'{$rd['poa_last_name']}'";
$p_pos = empty($rd['poa_position']) ? "NULL" : "'{$rd['poa_position']}'";
$p_doc = empty($rd['poa_doc']) ? "NULL" : "'{$rd['poa_doc']}'";

// role = user 
$sql = "
    INSERT INTO users (
        user_type, username, email, phone, password, 
        first_name, last_name, national_id, agency_name, tax_id,
        gov_doc, corp_cert, vat_id, role, status,
        agency_address, auth_first_name, auth_last_name, auth_position, auth_doc,
        has_poa, poa_first_name, poa_last_name, poa_position, poa_doc
    ) VALUES (
        '{$rd['user_type']}', '{$rd['username']}', '{$rd['email']}', '{$rd['phone']}', '{$rd['password']}',
        '{$rd['first_name']}', '{$rd['last_name']}', $n_id, $a_name, $t_id,
        $g_doc, $c_cert, $v_id, 'user', 'active',
        $a_addr, $au_fn, $au_ln, $au_pos, $au_doc,
        $h_poa, $p_fn, $p_ln, $p_pos, $p_doc
    )
";
pg_query($conn, $sql) or die(pg_error($conn));

$user_id = pg_insert_id($conn);

// INSERT bank
pg_query($conn, "
    INSERT INTO bank_accounts (user_id, bank_name, account_number, account_name)
    VALUES (
        $user_id,
        '{$rd['bank_name']}',
        '{$rd['account_number']}',
        '{$rd['account_name']}'
    )
");

// ✅ ล้าง session สมัคร (สำคัญสุด)
unset($_SESSION['register_data']);
unset($_SESSION['otp_demo']);

// สมัครสำเร็จ → ไป login
$_SESSION['flash_alert'] = [
    'type' => 'success',
    'title' => 'สมัครสมาชิกสำเร็จ',
    'message' => 'กรุณาเข้าสู่ระบบเพื่อเริ่มต้นใช้งาน'
];

// ลบ OTP ทิ้งหลัง verify สำเร็จ
pg_query($conn, "
    DELETE FROM otp_verifications WHERE phone='$phone'
");

header("Location: ../auth/login.php");
exit;
