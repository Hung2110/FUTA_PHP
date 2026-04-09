<?php
session_start();
require_once __DIR__ . '/../db.php'; // Đường dẫn tương đối an toàn hơn

// Giả sử bạn lưu role của người dùng trong session sau khi đăng nhập
// Ví dụ: $_SESSION['user_role'] = 'admin';
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'manager', 'collaborator'])) {
    // Nếu không có quyền, chuyển hướng về trang đăng nhập
    // Trong ví dụ này, tôi sẽ comment lại để bạn có thể test mà không cần đăng nhập.
    // Hãy mở comment này ra khi bạn đã có trang login.
    // header('Location: /FUTA_PHP/admin/login.php');
    // exit;
}
?>
