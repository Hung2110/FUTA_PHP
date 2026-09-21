<?php
require_once 'auth_check.php';
$pageTitle = 'Hồ Sơ Của Tôi';

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
                $stmt_update = $conn->prepare("UPDATE users SET password = ?, plain_password = ? WHERE id = ?");
                $stmt_update->bind_param("ssi", $new_password_md5, $new_password, $admin_id);
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
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | FUTA Advertising' : 'FUTA Advertising'; ?></title>
    <link rel="icon" href="../assets/images/logo/futa.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-user-edit text-primary me-2"></i>Hồ Sơ Của Tôi</h1>
            <p class="mb-0">Cập nhật thông tin cá nhân và thay đổi mật khẩu quản trị.</p>
        </div>
    </div>

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
                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-1">
                        <span class="text-muted">Email</span>
                        <strong class="text-break"><?php echo htmlspecialchars($user['email']); ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-1">
                        <span class="text-muted">Điện thoại</span>
                        <strong><?php echo htmlspecialchars($user['phone'] ?: 'Chưa cập nhật'); ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-1">
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
                    <!-- Hiển thị mật khẩu hiện hành của cá nhân tài khoản -->
                    <div class="mb-3 p-2 px-3 bg-light rounded border d-flex justify-content-between align-items-center">
                        <span class="small text-muted"><i class="fas fa-key me-1 text-warning"></i> Mật khẩu hiện tại của bạn:</span>
                        <div class="d-flex align-items-center gap-2">
                            <span id="profPassMasked" class="font-monospace fw-bold">••••••••</span>
                            <span id="profPassText" class="font-monospace fw-bold text-primary d-none"><?php echo !empty($user['plain_password']) ? htmlspecialchars($user['plain_password']) : '(Chưa lưu mật khẩu dạng xem)'; ?></span>
                            <button class="btn btn-sm btn-outline-secondary py-0 px-2 shadow-none" type="button" id="btnToggleProfPass" title="Hiện/Ẩn mật khẩu">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="change_password" value="1">
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu hiện tại *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="current_password" id="inputCurrentPass" required>
                                <button class="btn btn-outline-secondary toggle-pass-btn" type="button" data-target="inputCurrentPass" title="Hiện/Ẩn mật khẩu"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mật khẩu mới *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="new_password" id="inputNewPass" required>
                                    <button class="btn btn-outline-secondary toggle-pass-btn" type="button" data-target="inputNewPass" title="Hiện/Ẩn mật khẩu"><i class="fas fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Xác nhận mật khẩu mới *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="confirm_password" id="inputConfirmPass" required>
                                    <button class="btn btn-outline-secondary toggle-pass-btn" type="button" data-target="inputConfirmPass" title="Hiện/Ẩn mật khẩu"><i class="fas fa-eye"></i></button>
                                </div>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle xem mật khẩu hiện hành
    const btnToggleProf = document.getElementById('btnToggleProfPass');
    if (btnToggleProf) {
        btnToggleProf.addEventListener('click', function() {
            const masked = document.getElementById('profPassMasked');
            const text = document.getElementById('profPassText');
            const icon = this.querySelector('i');
            const isHidden = text.classList.contains('d-none');
            if (isHidden) {
                text.classList.remove('d-none');
                masked.classList.add('d-none');
                icon.className = 'fas fa-eye-slash';
            } else {
                text.classList.add('d-none');
                masked.classList.remove('d-none');
                icon.className = 'fas fa-eye';
            }
        });
    }

    // Toggle xem các input mật khẩu
    document.querySelectorAll('.toggle-pass-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (input) {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                const icon = this.querySelector('i');
                if (icon) {
                    icon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
                }
            }
        });
    });
});
</script>
</body>
</html>