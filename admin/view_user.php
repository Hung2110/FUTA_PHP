<?php
require_once 'auth_check.php';
$pageTitle = 'Hồ Sơ Người Dùng';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: users.php');
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: users.php?error=notfound');
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

$roles = [
    'user' => 'Người Dùng',
    'admin' => 'Quản Trị Viên',
    'user_manager' => 'Quản Lý Người Dùng',
    'project_manager' => 'Quản Lý Dự Án',
    'carousel_manager' => 'Quản Lý Carousel',
    'news_manager' => 'Quản Lý Tin Tức',
    'recruitment_manager' => 'Quản Lý Tuyển Dụng',
    'contact_manager' => 'Quản Lý Liên Hệ',
    'chat_manager' => 'Quản Lý Chat'
];
$role_colors = [
    'user' => 'secondary', 'admin' => 'danger',
    'user_manager' => 'dark', 'project_manager' => 'primary',
    'carousel_manager' => 'info', 'blog_manager' => 'success',
    'news_manager' => 'success', 'recruitment_manager' => 'warning', 'contact_manager' => 'secondary',
    'chat_manager' => 'primary'
];

$status_text = ['active' => 'Hoạt động', 'inactive' => 'Không hoạt động'];
$status_colors = ['active' => 'success', 'inactive' => 'secondary'];

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | FUTA Advertising' : 'FUTA Advertising'; ?></title>
    <link rel="icon" href="../assets/images/logo/futa.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/view_user.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-user-circle text-primary me-2"></i>Hồ Sơ Người Dùng</h1>
                <p class="mb-0">Xem chi tiết thông tin tài khoản và vai trò phân quyền người dùng.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="users.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
                </a>
            </div>
        </div>
        
        <div class="card profile-card">
            <div class="card-body">
                <h3 class="card-title mb-4">Thông tin chi tiết: <?php echo htmlspecialchars($user['fullname']); ?></h3>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>ID Người dùng:</strong> <span class="value">#<?php echo $user['id']; ?></span></li>
                    <li class="list-group-item"><strong>Tên đăng nhập:</strong> <span class="value"><?php echo htmlspecialchars($user['username']); ?></span></li>
                    <li class="list-group-item"><strong>Họ và tên:</strong> <span class="value"><?php echo htmlspecialchars($user['fullname']); ?></span></li>
                    <li class="list-group-item"><strong>Email:</strong> <span class="value"><?php echo htmlspecialchars($user['email']); ?></span></li>
                    <li class="list-group-item"><strong>Số điện thoại:</strong> <span class="value"><?php echo htmlspecialchars($user['phone'] ?: 'Chưa cập nhật'); ?></span></li>
                    <li class="list-group-item"><strong>Vai trò:</strong> 
                        <span class="value">
                            <?php 
                                $user_roles_arr = !empty($user['role']) ? explode(',', $user['role']) : [];
                                foreach ($user_roles_arr as $r) {
                                    $r = trim($r);
                                    if (empty($r)) continue;
                                    echo '<span class="badge bg-'.($role_colors[$r] ?? 'secondary').' me-1">'.($roles[$r] ?? ucfirst($r)).'</span>';
                                }
                            ?>
                        </span>
                    </li>
                    <li class="list-group-item"><strong>Trạng thái:</strong> <span class="value"><span class="badge bg-<?php echo $status_colors[$user['status']] ?? 'secondary'; ?>"><?php echo $status_text[$user['status']] ?? ucfirst($user['status']); ?></span></span></li>
                    <li class="list-group-item"><strong>Ngày tham gia:</strong> <span class="value"><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></span></li>
                    <li class="list-group-item d-block align-items-start"><strong>Giới thiệu:</strong> <div class="value mt-2 bio-text"><?php echo htmlspecialchars($user['bio'] ?: 'Chưa có giới thiệu.'); ?></div></li>
                </ul>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>