<?php
require_once 'auth_check.php';

$message = '';
$message_type = '';

// Cấu hình danh sách quyền hạn và màu sắc hiển thị
$role_config = [
    'user' => ['label' => 'Người dùng', 'color' => 'secondary'],
    'admin' => ['label' => 'Quản trị viên', 'color' => 'danger'],
    'user_manager' => ['label' => 'Quản lý người dùng', 'color' => 'dark'],
    'project_manager' => ['label' => 'Quản lý dự án', 'color' => 'primary'],
    'carousel_manager' => ['label' => 'Quản lý Carousel', 'color' => 'info'],
    'news_manager' => ['label' => 'Quản lý Tin tức', 'color' => 'success'],
    'recruitment_manager' => ['label' => 'Quản lý tuyển dụng', 'color' => 'warning'],
    'contact_manager' => ['label' => 'Quản lý liên hệ', 'color' => 'secondary']
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
    <title>Quản Lý Người Dùng</title>
    <link rel="icon" href="/FUTA_PHP/assets/images/logo/futa.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --primary: #007bff;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --dark: #343a40;
            --light: #f8f9fa;
        }

        body {
            background: #f7f9fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1f2a37;
        }

        .page-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 30px;            
            padding: 25px;           
        }

        .page-header h1 {
            font-weight: 700;
            font-size: 1.75rem;
            margin: 0;
            color: #1f2a37;
        }

        .page-header p {
            color: #6b7280;
            margin: 5px 0 0;
            font-size: 14px;
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            border: none;
            color: #fff;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,123,255,0.3);
            text-decoration: none;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,123,255,0.4);
            color: #fff;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid var(--primary);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        }

        .stat-card:nth-child(2) {
            border-left-color: var(--success);
        }

        .stat-card:nth-child(3) {
            border-left-color: var(--danger);
        }

        .stat-card h6 {
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 1px;
            color: #6b7280;
            font-weight: 600;
            margin: 0 0 12px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin: 8px 0;
            color: #1f2a37;
        }

        .stat-trend {
            font-size: 13px;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
            background: #fff;
            margin-bottom: 24px;
        }

        .card-header {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            padding: 16px 24px;
        }

        .card-header h5 {
            margin: 0;
            font-weight: 700;
            font-size: 1.1rem;
            color: #1f2a37;
        }

        .card-body {
            padding: 24px;
        }

        .table {
            margin: 0;
        }

        .table thead th {
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 11px;
            color: #6b7280;
            border-bottom: 2px solid #e5e7eb;
            background: #f9fafb;
            padding: 16px;
            font-weight: 600;
        }

        .table tbody td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
        }

        .table tbody tr:hover {
            background: #f9fafb;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 10px 14px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
            border-color: #007bff;
            outline: none;
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 14px 20px;
            margin-bottom: 24px;
        }

        .btn {
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        @media (max-width: 992px) {
            .stat-grid {
                grid-template-columns: 1fr;
            }
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-users me-2 text-primary"></i>Quản lý người dùng</h1>
            <p class="mb-0">Quản lý tài khoản, quyền và trạng thái người dùng trong hệ thống.</p>
        </div>
        <?php if ($show_form): ?>
            <a href="users.php" class="cta-button"><i class="fas fa-arrow-left"></i> Quay lại danh sách</a>
        <?php else: ?>
            <a href="users.php?action=add" class="cta-button"><i class="fas fa-plus"></i> Thêm người dùng mới</a>
        <?php endif; ?>
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
        <div class="stat-grid">
            <div class="stat-card">
                <h6>Tổng số người dùng</h6>
                <div class="stat-value"><?php echo number_format($stats['total_users'] ?? 0); ?></div>
                <div class="stat-trend"><i class="fas fa-users"></i>Tất cả người dùng</div>
            </div>
            <div class="stat-card">
                <h6>Đang hoạt động</h6>
                <div class="stat-value text-success"><?php echo number_format($stats['active_users'] ?? 0); ?></div>
                <div class="stat-trend text-success"><i class="fas fa-check-circle"></i>Trạng thái active</div>
            </div>
            <div class="stat-card">
                <h6>Quản trị viên</h6>
                <div class="stat-value text-danger"><?php echo number_format($stats['admin_users'] ?? 0); ?></div>
                <div class="stat-trend text-danger"><i class="fas fa-shield-alt"></i>Vai trò admin</div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách người dùng</h5>
                    <form method="GET" action="users.php" class="d-flex align-items-center">
                        <select name="role_filter" class="form-select form-select-sm" style="width: auto; min-width: 200px;" onchange="this.form.submit()">
                            <option value="">-- Tất cả vai trò --</option>
                            <?php foreach ($role_config as $key => $config): ?>
                                <option value="<?php echo $key; ?>" <?php echo $role_filter === $key ? 'selected' : ''; ?>><?php echo $config['label']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên đăng nhập</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Vai trò</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($users->num_rows): ?>
                                <?php while($user = $users->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>#<?php echo $user['id']; ?></strong></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
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
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                        </td>
                                        <td style="font-size: 13px; color: #6b7280;"><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                        <td class="text-end">
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                var btn = form.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang xử lý...';
                }
            });
        });

        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const passwordInput = document.getElementById('passwordInput');
                const icon = this.querySelector('i');
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                icon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
            });
        }
    });
</script>

</body>
</html>
