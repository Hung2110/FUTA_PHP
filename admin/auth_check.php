<?php
// Cấu hình session an toàn
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // Chỉ gửi cookie qua HTTPS
ini_set('session.cookie_samesite', 'Lax');

session_start();
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Cấu hình danh sách quyền hạn và màu sắc hiển thị (Dùng chung cho toàn hệ thống)
$role_config = [
    'user' => ['label' => 'Người dùng', 'color' => 'secondary'],
    'admin' => ['label' => 'Quản trị viên', 'color' => 'danger'],
    'user_manager' => ['label' => 'Quản lý người dùng', 'color' => 'dark'],
    'project_manager' => ['label' => 'Quản lý dự án', 'color' => 'primary'],
    'carousel_manager' => ['label' => 'Quản lý Carousel', 'color' => 'info'],
    'news_manager' => ['label' => 'Quản lý Tin tức', 'color' => 'success'],
    'recruitment_manager' => ['label' => 'Quản lý tuyển dụng', 'color' => 'warning'],
    'contact_manager' => ['label' => 'Quản lý liên hệ', 'color' => 'secondary'],
    'chat_manager' => ['label' => 'Quản lý Chat', 'color' => 'primary']
];

// Cập nhật lại quyền hạn từ database để đảm bảo tính thời gian thực
$stmt_auth = $conn->prepare("SELECT role, status FROM users WHERE id = ?");
$stmt_auth->bind_param("i", $_SESSION['admin_id']);
$stmt_auth->execute();
$user_auth = $stmt_auth->get_result()->fetch_assoc();
$stmt_auth->close();

if ($user_auth) {
    if ($user_auth['status'] !== 'active') {
        header('Location: logout.php'); // Nếu bị khóa thì đăng xuất ngay
        exit;
    }
    $_SESSION['admin_role'] = $user_auth['role']; // Cập nhật session
}

// Định nghĩa quyền truy cập cho từng trang cụ thể
$page_permissions = [
    // Quản trị hệ thống (Chỉ Admin)
    'users.php'             => ['admin', 'user_manager'],
    'view_user.php'         => ['admin', 'user_manager'],
    'activity_logs.php'     => ['admin'],

    // Quản lý Dự án & Tuyển dụng & Liên hệ (Admin + Manager)
    'projects.php'          => ['admin', 'project_manager'],
    'recruitments.php'      => ['admin', 'recruitment_manager'],
    'applications.php'      => ['admin', 'recruitment_manager'],
    'view_application.php'  => ['admin', 'recruitment_manager'],
    'contacts.php'          => ['admin', 'contact_manager'],
    'view_contact.php'      => ['admin', 'contact_manager'],

    // Quản lý Nội dung (Admin + Staff)
    'news.php'              => ['admin', 'news_manager'],
    'post-edit.php'         => ['admin', 'news_manager'],
    'carousel_slides.php'   => ['admin', 'carousel_manager'],
    'posts.php'             => ['admin', 'news_manager'],

    // Các trang chung (Admin + Manager + Staff)
    'import.php'            => ['admin', 'project_manager', 'news_manager'],
    
    // Phân quyền cho Chat
    'chat.php'              => ['admin', 'chat_manager'],
];

// Lấy tên trang hiện hành
$current_page = basename($_SERVER['PHP_SELF']);

// Lấy danh sách quyền của người dùng
$user_roles = isset($_SESSION['admin_role']) ? explode(',', $_SESSION['admin_role']) : [];
$user_roles = array_map('trim', $user_roles); // Chuẩn hóa dữ liệu (xóa khoảng trắng thừa)

// Tạo chuỗi hiển thị quyền hạn cho Dashboard
$display_roles = [];
foreach ($user_roles as $r) {
    if (isset($role_config[$r])) {
        $display_roles[] = $role_config[$r]['label'];
    }
}
$display_role_str = !empty($display_roles) ? implode(', ', $display_roles) : 'Người dùng';

// Kiểm tra nếu trang hiện tại có trong danh sách phân quyền
if (array_key_exists($current_page, $page_permissions)) {
    $allowed_roles = $page_permissions[$current_page];
    // Nếu không có vai trò nào của user nằm trong danh sách cho phép -> Chặn
    if (empty(array_intersect($allowed_roles, $user_roles))) {
        header('Location: dashboard.php?error=permission');
        exit;
    }
}
?>