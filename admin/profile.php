<?php
require_once 'auth_check.php';

$message = '';
$message_type = '';
$admin_id = $_SESSION['admin_id'];

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Cập nhật thông tin cá nhân
        $fullname = trim($_POST['fullname'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $bio      = trim($_POST['bio'] ?? '');

        if (empty($fullname) || empty($email)) {
            $message = 'Họ tên và Email là bắt buộc.';
            $message_type = 'danger';
        } else {
            $stmt = $conn->prepare("UPDATE users SET fullname=?, email=?, phone=?, bio=? WHERE id=?");
            $stmt->bind_param("ssssi", $fullname, $email, $phone, $bio, $admin_id);
            if ($stmt->execute()) {
                $_SESSION['admin_fullname'] = $fullname; // Cập nhật session
                $message = 'Cập nhật thông tin thành công!';
                $message_type = 'success';
                // Log activity
                $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, module, ip) VALUES (?, ?, ?, ?)");
                $action = "Tự cập nhật thông tin cá nhân";
                $module = "Profile";
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $log_stmt->bind_param("isss", $admin_id, $action, $module, $ip);
                $log_stmt->execute();
            } else {
                $message = 'Lỗi khi cập nhật thông tin.';
                $message_type = 'danger';
            }
            $stmt->close();
        }
    } elseif (isset($_POST['change_password'])) {
        // Thay đổi mật khẩu
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $message = 'Vui lòng điền đầy đủ các trường mật khẩu.';
            $message_type = 'danger';
        } elseif ($new_password !== $confirm_password) {
            $message = 'Mật khẩu mới không khớp.';
            $message_type = 'danger';
        } else {
            // Lấy mật khẩu hiện tại từ DB
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user && md5($current_password) === $user['password']) {
                // Mật khẩu hiện tại đúng, cập nhật mật khẩu mới
                $new_password_md5 = md5($new_password);
                $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt_update->bind_param("si", $new_password_md5, $admin_id);
                if ($stmt_update->execute()) {
                    $message = 'Đổi mật khẩu thành công!';
                    $message_type = 'success';
                    // Log activity
                    $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, module, ip) VALUES (?, ?, ?, ?)");
                    $action = "Tự thay đổi mật khẩu";
                    $module = "Profile";
                    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    $log_stmt->bind_param("isss", $admin_id, $action, $module, $ip);
                    $log_stmt->execute();
                } else {
                    $message = 'Lỗi khi đổi mật khẩu.';
                    $message_type = 'danger';
                }
                $stmt_update->close();
            } else {
                $message = 'Mật khẩu hiện tại không đúng.';
                $message_type = 'danger';
            }
        }
    }
}

// Lấy thông tin người dùng hiện tại để hiển thị
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: logout.php');
    exit;
}

$roles = [
    'user' => 'Người Dùng',
    'admin' => 'Quản Trị Viên',
    'user_manager' => 'Quản Lý Người Dùng',
    'project_manager' => 'Quản Lý Dự Án',
    'carousel_manager' => 'Quản Lý Carousel',
    'news_manager' => 'Quản Lý Tin Tức',
    'recruitment_manager' => 'Quản Lý Tuyển Dụng',
    'contact_manager' => 'Quản Lý Liên Hệ'
];
$role_colors = [
    'user' => 'secondary', 'admin' => 'danger',
    'user_manager' => 'dark', 'project_manager' => 'primary',
    'carousel_manager' => 'info', 'news_manager' => 'success',
    'recruitment_manager' => 'warning', 'contact_manager' => 'secondary'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ Của Tôi</title>
    <link rel="icon" href="/FUTA_PHP/assets/images/logo/futa.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background: #f7f9fc; }
        .card { border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: none; }
        .profile-header { text-align: center; padding: 2rem 1rem; }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
        .form-label { font-weight: 600; }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <h1 class="mb-4"><i class="fas fa-user-edit text-primary"></i> Hồ Sơ Của Tôi</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body profile-header">
                    <i class="fas fa-user-circle fa-6x text-secondary mb-3"></i>
                    <h4 class="card-title mb-1"><?php echo htmlspecialchars($user['fullname']); ?></h4>
                    <p class="text-muted mb-2">@<?php echo htmlspecialchars($user['username']); ?></p>
                    <span class="fs-6">
                        <?php 
                            $user_roles_arr = !empty($user['role']) ? explode(',', $user['role']) : [];
                            foreach ($user_roles_arr as $r) {
                                $r = trim($r);
                                if (empty($r)) continue;
                                echo '<span class="badge bg-'.($role_colors[$r] ?? 'secondary').' me-1">'.($roles[$r] ?? ucfirst($r)).'</span>';
                            }
                        ?>
                    </span>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">Email</span>
                        <strong><?php echo htmlspecialchars($user['email']); ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">Điện thoại</span>
                        <strong><?php echo htmlspecialchars($user['phone'] ?: 'Chưa cập nhật'); ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">Ngày tham gia</span>
                        <strong><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></strong>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Cập nhật thông tin</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Họ và tên *</label>
                                <input type="text" class="form-control" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Giới thiệu</label>
                            <textarea class="form-control" name="bio" rows="3"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Đổi mật khẩu</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="change_password" value="1">
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu hiện tại *</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mật khẩu mới *</label>
                                <input type="password" class="form-control" name="new_password" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Xác nhận mật khẩu mới *</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>