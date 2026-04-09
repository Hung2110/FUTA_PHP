<?php
// Xử lý lưu liên hệ từ form chat nhanh (contact-chat.php)
require_once '../db.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $subject = 'Chat nhanh';
    if ($name && $email && $phone && $message) {
        $stmt = $conn->prepare("INSERT INTO contact (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $name, $email, $phone, $subject, $message);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success'=>true]);
        exit;
    }
    echo json_encode(['error'=>'Thiếu thông tin']);
    exit;
}
http_response_code(405);
echo json_encode(['error'=>'Phương thức không hợp lệ']);
