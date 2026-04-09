<?php
session_start();
require_once '../db.php';

if (isset($_SESSION['admin_id'])) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, module, ip) VALUES (?, ?, ?, ?)");
    $action = "Đăng xuất hệ thống";
    $module = "Authentication";
    $user_id = $_SESSION['admin_id'];
    $log_stmt->bind_param("isss", $user_id, $action, $module, $ip);
    $log_stmt->execute();
}

// Hủy session một cách an toàn
$_SESSION = array(); // Xóa tất cả các biến session

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

session_destroy(); // Hủy session
header('Location: login.php');
exit;
?>