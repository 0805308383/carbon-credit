<?php
session_start();
require '../config/db.php';
if ($_SESSION['role'] !== 'admin') exit;

$id = $_GET['id'];
$action = $_GET['action'];

$status = $action === 'approve' ? 'approved' : 'rejected';

mysqli_query($conn, "
UPDATE carbon_listings SET status='$status' WHERE id=$id
");

header("Location: manage_listings.php");
exit;
