<?php
require_once 'auth_check.php';
$pageTitle = 'Quản Lý Người Dùng';

$message = '';
$message_type = '';

// Cấu hình danh sách quyền hạn và màu sắc hiển thị
$role_config = [
    'user' => ['label' => 'Người Dùng', 'color' => 'secondary'],
    'admin' => ['label' => 'Quản Trị Viên', 'color' => 'danger'],
    'user_manager' => ['label' => 'Quản Lý Người Dùng', 'color' => 'dark'],
    'project_manager' => ['label' => 'Quản Lý Dự Án', 'color' => 'primary'],
    'carousel_manager' => ['label' => 'Quản Lý Carousel', 'color' => 'info'],
    'news_manager' => ['label' => 'Quản Lý Tin Tức', 'color' => 'success'],
    'recruitment_manager' => ['label' => 'Quản Lý Tuyển Dụng', 'color' => 'warning'],
    'contact_manager' => ['label' => 'Quản Lý Liên Hệ', 'color' => 'secondary'],
    'chat_manager' => ['label' => 'Quản Lý Chat', 'color' => 'primary']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $id       = $_POST['id'] ?? null;
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $fullname = trim($_POST['fullname'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $bio      = trim($_POST['bio'] ?? '');
        $roles_array = (isset($_POST['roles']) && is_array($_POST['roles'])) ? $_POST['roles'] : [];
        $status   = $_POST['status'] ?? 'active';

        $valid_roles = array_keys($role_config);
        $sanitized_roles = array_intersect($roles_array, $valid_roles);
        $role = !empty($sanitized_roles) ? implode(',', $sanitized_roles) : 'user';

        if ($username === '' || $fullname === '' || $email === '') {
            $message = 'Vui lòng điền đủ các trường bắt buộc.';
            $message_type = 'danger';
        } else {
            $check = $conn->prepare("SELECT id FROM users WHERE username = ?" . ($action === 'edit' ? " AND id != ?" : ""));
            if ($action === 'edit') {
                $check->bind_param("si", $username, $id);
            } else {
                $check->bind_param("s", $username);
            }
            $check->execute();
            $exists = $check->get_result()->num_rows > 0;
            $check->close();

            if ($exists) {
                $message = 'Tên đăng nhập đã tồn tại.';
                $message_type = 'danger';
            } else {
                if ($action === 'add') {
                    $password_md5 = md5($password ?: '123456');
                    $stmt = $conn->prepare("INSERT INTO users (username, password, fullname, email, phone, bio, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssssss", $username, $password_md5, $fullname, $email, $phone, $bio, $role, $status);
                } else {
                    if (!empty($password)) {
                        $password_md5 = md5($password);
                        $stmt = $conn->prepare("UPDATE users SET username=?, password=?, fullname=?, email=?, phone=?, bio=?, role=?, status=? WHERE id=?");
                        $stmt->bind_param("ssssssssi", $username, $password_md5, $fullname, $email, $phone, $bio, $role, $status, $id);
                    } else {
                        $stmt = $conn->prepare("UPDATE users SET username=?, fullname=?, email=?, phone=?, bio=?, role=?, status=? WHERE id=?");
                        $stmt->bind_param("sssssssi", $username, $fullname, $email, $phone, $bio, $role, $status, $id);
                    }
                }

                if ($stmt->execute()) {
                    $action_text = $action === 'add' ? 'Thêm người dùng: ' : 'Cập nhật người dùng: ';
                    $message = $action === 'add' ? 'Thêm người dùng thành công!' : 'Cập nhật người dùng thành công!';
                    $message_type = 'success';

                    $log = $conn->prepare("INSERT INTO activity_logs (user_id, action, module, ip) VALUES (?, ?, ?, ?)");
                    $log_action = $action_text . $username;
                    $module = 'Users';
                    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    $log->bind_param("isss", $_SESSION['admin_id'], $log_action, $module, $ip);
                    $log->execute();
                    $log->close();

                    header('Location: users.php?success=1');
                    exit;
                } else {
                    $message = 'Có lỗi khi lưu người dùng: ' . $stmt->error;
                    $message_type = 'danger';
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        if ($id == $_SESSION['admin_id']) {
            $message = 'Không thể xóa tài khoản của chính bạn.';
            $message_type = 'danger';
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = 'Xóa người dùng thành công!';
                $message_type = 'success';

                $log = $conn->prepare("INSERT INTO activity_logs (user_id, action, module, ip) VALUES (?, ?, ?, ?)");
                $log_action = "Xóa người dùng ID: " . $id;
                $module = 'Users';
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $log->bind_param("isss", $_SESSION['admin_id'], $log_action, $module, $ip);
                $log->execute();
                $log->close();

                header('Location: users.php?deleted=1');
                exit;
            } else {
                $message = 'Có lỗi khi xóa người dùng.';
                $message_type = 'danger';
            }
            $stmt->close();
        }
    }
}

if (isset($_GET['success'])) {
    $message = 'Thao tác thành công!';
    $message_type = 'success';
}

if (isset($_GET['deleted'])) {
    $message = 'Xóa người dùng thành công!';
    $message_type = 'success';
}

// Xử lý lọc theo vai trò
$role_filter = $_GET['role_filter'] ?? '';
$sql = "SELECT * FROM users";

if (!empty($role_filter) && array_key_exists($role_filter, $role_config)) {
    // Sử dụng FIND_IN_SET để tìm chính xác vai trò trong chuỗi phân cách dấu phẩy
    $sql .= " WHERE FIND_IN_SET(?, role) > 0";
    $stmt = $conn->prepare($sql . " ORDER BY created_at DESC");
    $stmt->bind_param("s", $role_filter);
    $stmt->execute();
    $users = $stmt->get_result();
} else {
    $users = $conn->query($sql . " ORDER BY created_at DESC");
}

// --- Logic to determine view (list or form) ---
$show_form = false;
$edit_user = null;
$action_param = $_GET['action'] ?? null;
$id_param = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($action_param === 'add') {
    $show_form = true;
} elseif ($action_param === 'edit' && $id_param > 0) {
    $show_form = true;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id_param);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_user = $result->fetch_assoc();
    $stmt->close();
    if (!$edit_user) { // If ID not found, redirect to list
        header('Location: users.php');
        exit;
    }
}
$statsResult = $conn->query("
    SELECT
        COUNT(*) AS total_users,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_users,
        SUM(CASE WHEN role LIKE '%admin%' THEN 1 ELSE 0 END) AS admin_users
    FROM users
");
$stats = $statsResult ? $statsResult->fetch_assoc() : ['total_users' => 0, 'active_users' => 0, 'admin_users' => 0];

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | FUTA Advertising' : 'FUTA Advertising'; ?></title>
    <link rel="icon" href="../assets/images/logo/futa.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/users.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-users text-primary me-2"></i>Quản Lý Người Dùng</h1>
            <p class="mb-0">Quản lý tài khoản, quyền hạn và trạng thái người dùng trong hệ thống.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?php if ($show_form): ?>
                <a href="users.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Quay lại danh sách</a>
            <?php else: ?>
                <a href="users.php?action=add" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Thêm người dùng mới</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($show_form): ?>
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-<?php echo $edit_user ? 'edit' : 'plus-circle'; ?> me-2"></i><?php echo $edit_user ? 'Cập nhật người dùng' : 'Thêm người dùng mới'; ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="users.php" class="row g-3">
                    <input type="hidden" name="action" value="<?php echo $edit_user ? 'edit' : 'add'; ?>">
                    <?php if ($edit_user): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
                    <?php endif; ?>

                    <div class="col-md-6">
                        <label class="form-label">Tên đăng nhập *</label>
                        <input type="text" class="form-control" name="username" required value="<?php echo htmlspecialchars($edit_user['username'] ?? ''); ?>" placeholder="Nhập tên đăng nhập">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mật khẩu <?php echo $edit_user ? '(để trống nếu không đổi)' : '*'; ?></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password" id="passwordInput" placeholder="<?php echo $edit_user ? 'Để trống để giữ nguyên' : 'Mặc định: 123456'; ?>" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword"><i class="fas fa-eye"></i></button>
                        </div>
                        <?php if ($edit_user): ?>
                            <div class="form-text text-muted" style="font-size: 12px;"><i class="fas fa-info-circle"></i> Mật khẩu được mã hóa. Chỉ nhập vào ô này nếu bạn muốn đặt mật khẩu mới.</div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Họ và tên *</label>
                        <input type="text" class="form-control" name="fullname" required value="<?php echo htmlspecialchars($edit_user['fullname'] ?? ''); ?>" placeholder="Nhập họ và tên">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required value="<?php echo htmlspecialchars($edit_user['email'] ?? ''); ?>" placeholder="example@email.com">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($edit_user['phone'] ?? ''); ?>" placeholder="0123456789">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="status">
                            <option value="active" <?php echo ($edit_user['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                            <option value="inactive" <?php echo ($edit_user['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Không hoạt động</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Phân quyền *</label>
                        <div class="role-checkbox-group">
                        <?php
                            $current_roles = isset($edit_user['role']) ? array_map('trim', explode(',', $edit_user['role'])) : [];
                            foreach ($role_config as $key => $config) {
                                $checked = in_array($key, $current_roles) ? 'checked' : '';
                                $label = $config['label'];
                                echo '<div class="form-check form-check-inline">';
                                echo "<input class=\"form-check-input\" type=\"checkbox\" name=\"roles[]\" value=\"$key\" id=\"role_$key\" $checked>";
                                echo "<label class=\"form-check-label\" for=\"role_$key\">$label</label>";
                                echo '</div>';
                            }
                        ?>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Giới thiệu</label>
                        <textarea class="form-control" rows="3" name="bio" placeholder="Mô tả về người dùng..."><?php echo htmlspecialchars($edit_user['bio'] ?? ''); ?></textarea>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2">
                        <a href="users.php" class="btn btn-outline-secondary">Hủy</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-<?php echo $edit_user ? 'save' : 'plus'; ?> me-1"></i>
                            <?php echo $edit_user ? 'Cập nhật' : 'Thêm mới'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
          
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 table-filter-toolbar">
            <div class="btn-group filter-btn-group flex-wrap" role="group">
                <a href="users.php" class="btn <?php echo empty($role_filter) ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                    Tất cả <span class="badge <?php echo empty($role_filter) ? 'bg-white text-primary' : 'bg-secondary'; ?> ms-1"><?php echo (int)($stats['total_users'] ?? 0); ?></span>
                </a>
                <?php foreach ($role_config as $key => $config): ?>
                    <a href="users.php?role_filter=<?php echo urlencode($key); ?>" class="btn <?php echo $role_filter === $key ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                        <?php echo $config['label']; ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="text-muted small filter-count-info">
                <i class="fas fa-users me-1 text-primary"></i> Tổng số: <strong><?php echo (int)($stats['total_users'] ?? 0); ?></strong> tài khoản
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3" style="min-width: 50px;">ID</th>
                                <th style="min-width: 120px;">Tên đăng nhập</th>
                                <th style="min-width: 130px;">Họ tên</th>
                                <th style="min-width: 140px;">Email</th>
                                <th style="min-width: 120px;">Vai trò</th>
                                <th style="min-width: 95px;">Trạng thái</th>
                                <th style="min-width: 110px;">Ngày tạo</th>
                                <th class="text-end pe-3" style="min-width: 105px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($users->num_rows): ?>
                                <?php while($user = $users->fetch_assoc()): ?>
                                    <tr>
                                        <td data-label="Mã ID"><strong>#<?php echo $user['id']; ?></strong></td>
                                        <td data-label="Tên đăng nhập"><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td data-label="Họ tên"><div><?php echo htmlspecialchars($user['fullname']); ?></div></td>
                                        <td data-label="Email"><div><?php echo htmlspecialchars($user['email']); ?></div></td>
                                        <td data-label="Vai trò">
                                            <div>
                                            <?php 
                                                $user_roles_arr = !empty($user['role']) ? explode(',', $user['role']) : [];
                                                foreach ($user_roles_arr as $r) {
                                                    $r = trim($r); // Loại bỏ khoảng trắng thừa để khớp với key trong $role_config
                                                    if (empty($r)) continue;
                                                    $label = $role_config[$r]['label'] ?? ucfirst($r);
                                                    $color = $role_config[$r]['color'] ?? 'secondary';
                                                    echo '<span class="badge bg-'.$color.' me-1">'.$label.'</span>';
                                                }
                                            ?>
                                            </div>                                            
                                        </td>
                                        <td data-label="Trạng thái">
                                            <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                        </td>
                                        <td data-label="Ngày tạo" style="font-size: 13px; color: #6b7280;"><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                        <td data-label="Thao tác" class="text-end">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <a href="view_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info" title="Xem">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning" title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa người dùng này?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="empty-state">
                                        <i class="fas fa-users"></i>
                                        <p class="mb-0">Chưa có người dùng nào</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/users.js"></script>
</body>
</html>
