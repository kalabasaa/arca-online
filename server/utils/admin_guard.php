<?php

$acting_id = sanitize_int($_SESSION['user_id'] ?? '');
if ($acting_id === false) {
    session_destroy();
    header("Location: ../auth/login.php");
    exit();
}

$_gstmt = $conn->prepare("SELECT user_name FROM users WHERE user_id = ?");
$_gstmt->bind_param("i", $acting_id);
$_gstmt->execute();
$_gactor = $_gstmt->get_result()->fetch_assoc();
$_gstmt->close();

if (!$_gactor || $_gactor['user_name'] !== 'admin_renier') {
    header("Location: ../pages/dashboard.php");
    exit();
}