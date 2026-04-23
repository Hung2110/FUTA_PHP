<?php
// Check if notifications table exists to prevent fatal errors
$table_exists_query = $conn->query("SHOW TABLES LIKE 'notifications'");
$table_exists = $table_exists_query && $table_exists_query->num_rows > 0;

$unread_count = 0;
$notifications = [];

if ($table_exists) {
    // Fetch notifications for the logged-in user
    $current_user_id = $_SESSION['admin_id'];

    // Get unread count
    $count_stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
    $count_stmt->bind_param("i", $current_user_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    if ($count_row = $count_result->fetch_assoc()) {
        $unread_count = $count_row['unread_count'];
    }
    $count_stmt->close();

    // Lấy 20 thông báo gần nhất để danh sách đủ dài và kích hoạt thanh cuộn
    $notif_stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
    $notif_stmt->bind_param("i", $current_user_id);
    $notif_stmt->execute();
    $notif_result = $notif_stmt->get_result();
    while ($row = $notif_result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $notif_stmt->close();
}

// Lấy danh sách quyền của người dùng để hiển thị menu
$user_roles = !empty($_SESSION['admin_role']) ? explode(',', $_SESSION['admin_role']) : [];
$user_roles = array_map('trim', $user_roles); // Loại bỏ khoảng trắng thừa

// Sử dụng cấu hình từ auth_check.php (biến global $role_config)
global $role_config;

// Fallback: Nếu $role_config chưa tồn tại (do include order), tự định nghĩa lại để tránh lỗi hiển thị
if (!isset($role_config) || empty($role_config)) {
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
}

$display_roles = array_map(function($r) use ($role_config) {
    if (isset($role_config) && isset($role_config[$r])) {
        return $role_config[$r]['label'];
    }
    return ucfirst($r);
}, $user_roles);
$display_role_str = !empty($display_roles) ? implode(', ', $display_roles) : 'Chưa phân quyền';
?>
<style>
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        width: 260px;
        height: 100vh;        
        background: #f8f9fa; /* Nền màu xám rất nhạt */
        color: #343a40; /* Màu chữ chính (đen) */
        padding: 20px 0;
        overflow-y: auto;
        z-index: 1000;
        border-right: 1px solid #dee2e6; /* Thêm đường viền phải để phân tách */
    }
    .sidebar-header { padding: 20px; border-bottom: 1px solid #dee2e6; margin-bottom: 20px; }
    .sidebar-logo { max-width: 200px; display:block; margin-bottom:8px }
    .sidebar-menu { list-style: none; padding: 0; margin: 0; }
    .sidebar-menu a { display: flex; align-items: center; padding: 12px 20px; color: #212529; text-decoration: none; transition: all 0.25s ease; }
    .sidebar-menu a:hover, .sidebar-menu a.active { background: #e9ecef; border-left: 4px solid #007bff; color: #007bff; font-weight: 600; }
    .sidebar-menu i { width: 24px; margin-right: 10px; text-align:center }
    .sidebar .small { color: #6c757d; }
    .sidebar-menu .dropdown-toggle::after {
        display: inline-block;
        margin-left: auto;
        vertical-align: .255em;
        content: "";
        border-top: .3em solid;
        border-right: .3em solid transparent;
        border-bottom: 0;
        border-left: .3em solid transparent;
        transition: transform .2s ease-in-out;
    }
    .sidebar-menu .dropdown-toggle[aria-expanded="true"]::after {
        transform: rotate(180deg);
    }
    .sidebar-submenu {
        background-color: rgba(0,0,0,0.1);
    }
    .notification-dropdown-menu {
        width: 350px;
        max-height: 400px;
        overflow-y: auto;
    }
    /* Tùy chỉnh giao diện thanh cuộn cho danh sách thông báo */
    .notification-dropdown-menu::-webkit-scrollbar {
        width: 6px;
    }
    .notification-dropdown-menu::-webkit-scrollbar-track {
        background: #f8f9fa;
    }
    .notification-dropdown-menu::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
    .notification-dropdown-menu::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    .notification-item small {
        white-space: normal;
    }
    .main-content { 
        margin-left: 260px; 
        padding: 30px; 
        padding-top: 90px;
        min-height: 100vh;
    }
    .top-right-actions {
        position: fixed;
        top: 20px;
        right: 30px;
        z-index: 1040;
        display: flex;
        align-items: center;
        gap: 25px;
        background: rgba(255, 255, 255, 0.9);
        padding: 8px 24px;
        border-radius: 50px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        backdrop-filter: blur(10px);
    }
    .top-right-actions .top-right-icon {
        font-size: 1.3rem;
        color: #4a5568;
        position: relative;
    }
    .top-right-actions .notification-badge {
        position: absolute;
        top: -5px;
        right: -10px;
        font-size: 0.6em;
        padding: 2px 5px;
        border: 1px solid white;
    }
    @media (max-width: 767px) { 
        .sidebar { position: relative; width:100%; height:auto; } 
        .main-content { margin-left:0 !important; padding: 30px 15px; padding-top: 80px !important; } 
        .top-right-actions {
            background: #fff; padding: 10px 15px; width: 100%;
            left: 0; top: 0; right: 0; justify-content: flex-end;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 0;
        }
    }
</style>

<div class="sidebar">
    <div class="sidebar-header">
        <a href="dashboard.php">
            <img src="../assets/images/logo/Advertising.png" class="sidebar-logo">
        </a>
        <div class="mt-2">
            <div class="fw-bold text-dark small"><?php echo htmlspecialchars($_SESSION['admin_fullname'] ?? 'User'); ?></div>
            <div class="text-muted" style="font-size: 11px; line-height: 1.2;"><?php echo htmlspecialchars($display_role_str); ?></div>
        </div>
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-home"></i> Dashboard</a></li>
        <?php if (!empty(array_intersect(['admin', 'user_manager'], $user_roles))): ?>
        <li><a href="users.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['users.php', 'view_user.php']) ? 'active' : ''; ?>"><i class="fas fa-users"></i> Quản Lý Người Dùng</a></li>
        <?php endif; ?>

        <?php if (!empty(array_intersect(['admin', 'project_manager'], $user_roles))): ?>
        <li><a href="projects.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'projects.php' ? 'active' : ''; ?>"><i class="fas fa-project-diagram"></i> Quản Lý Dự Án</a></li>
        <?php endif; ?>

        <?php if (!empty(array_intersect(['admin', 'news_manager'], $user_roles))): ?>
        <li><a href="news.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'news.php' ? 'active' : ''; ?>"><i class="fas fa-newspaper"></i> Quản Lý Tin Tức</a></li>
        <?php endif; ?>
        
        <?php if (!empty(array_intersect(['admin', 'carousel_manager'], $user_roles))): ?>
        <li><a href="carousel_slides.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'carousel_slides.php' ? 'active' : ''; ?>"><i class="fas fa-images"></i> Quản Lý Carousel</a></li>
        <?php endif; ?>
        
        
        <?php if (!empty(array_intersect(['admin', 'recruitment_manager'], $user_roles))): ?>
        <?php 
            $isRecruitmentActive = in_array(basename($_SERVER['PHP_SELF']), ['recruitments.php', 'applications.php', 'view_application.php']);
        ?>
        <li class="sidebar-dropdown">
            <a href="#recruitmentSubmenu" data-bs-toggle="collapse" aria-expanded="<?php echo $isRecruitmentActive ? 'true' : 'false'; ?>" class="dropdown-toggle <?php echo $isRecruitmentActive ? 'active' : ''; ?>">
                <i class="fas fa-briefcase"></i> Quản Lý Tuyển Dụng
            </a>
            <ul class="collapse list-unstyled sidebar-submenu <?php echo $isRecruitmentActive ? 'show' : ''; ?>" id="recruitmentSubmenu">
                <li><a href="recruitments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'recruitments.php' ? 'active' : ''; ?>"><i class="fas fa-list"></i> Danh sách tin</a></li>
                <li><a href="applications.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['applications.php', 'view_application.php']) ? 'active' : ''; ?>"><i class="fas fa-file-alt"></i> Đơn ứng tuyển</a></li>
            </ul>
        </li>
        <?php endif; ?>

        <?php if (in_array('admin', $user_roles)): ?>
        <li><a href="activity_logs.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'activity_logs.php' ? 'active' : ''; ?>"><i class="fas fa-history"></i> Nhật Ký Hoạt Động</a></li>
        <?php endif; ?>

        <?php if (!empty(array_intersect(['admin', 'project_manager', 'news_manager'], $user_roles))): ?>
        <li><a href="import.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'import.php' ? 'active' : ''; ?>"><i class="fas fa-file-import"></i> Import Dữ liệu</a></li>
        <?php endif; ?>

        <?php if (!empty(array_intersect(['admin', 'contact_manager'], $user_roles))): ?>
        <li><a href="contacts.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contacts.php' ? 'active' : ''; ?>"><i class="fas fa-envelope"></i> Liên hệ</a></li>
        <?php endif; ?>
        
        <?php if (!empty(array_intersect(['admin', 'chat_manager'], $user_roles))): ?>
        <li><a href="chat.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'chat.php' ? 'active' : ''; ?>"><i class="fas fa-comments"></i> Quản Lý Chat</a></li>
        <?php endif; ?>
    </ul>
</div>

<div class="top-right-actions">
    <!-- Notification Dropdown -->
    <div class="dropdown">
        <a href="#" class="text-secondary top-right-icon" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-bell"></i>
            <?php if ($unread_count > 0): ?>
                <span class="badge rounded-pill bg-danger notification-badge"><?php echo $unread_count; ?></span>
            <?php endif; ?>
        </a>
        <ul class="dropdown-menu dropdown-menu-end notification-dropdown-menu" aria-labelledby="notificationDropdown">
            <li class="dropdown-header">Bạn có <?php echo $unread_count; ?> thông báo mới</li>
            <li><hr class="dropdown-divider"></li>
            <?php if (!$table_exists): ?>
                <li class="text-center text-danger p-2 small">Lỗi: Bảng `notifications` không tồn tại.</li>
            <?php elseif (empty($notifications)): ?>
                <li class="text-center text-muted p-2">Không có thông báo</li>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <li>
                        <a class="dropdown-item notification-item <?php echo $notification['is_read'] ? '' : 'fw-bold'; ?>" href="<?php echo htmlspecialchars($notification['link']); ?>" data-id="<?php echo $notification['id']; ?>">
                            <small><i class="fas <?php echo $notification['type'] == 'contact' ? 'fa-envelope text-primary' : 'fa-file-alt text-success'; ?> me-2"></i><?php echo htmlspecialchars($notification['message']); ?></small>
                            <small class="d-block text-muted mt-1"><?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?></small>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

    <!-- User Dropdown -->
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-decoration-none text-dark" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="me-2 d-none d-sm-inline"><?php echo htmlspecialchars($_SESSION['admin_fullname']); ?></span>
            <i class="fas fa-user-circle fa-2x text-secondary"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><h6 class="dropdown-header">Xin chào, <?php echo htmlspecialchars($_SESSION['admin_fullname']); ?></h6></li>
            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-edit fa-fw me-2"></i>Hồ sơ</a></li>
            <li><a class="dropdown-item" href="../index.php" target="_blank"><i class="fas fa-globe fa-fw me-2"></i>Xem website</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt fa-fw me-2"></i>Đăng xuất</a></li>
        </ul>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function(e) {
            const notificationId = this.dataset.id;
            const isUnread = this.classList.contains('fw-bold');
            const href = this.getAttribute('href');

            if (isUnread) {
                e.preventDefault(); // Ngăn trình duyệt chuyển trang ngay lập tức để chờ API
                
                // Cập nhật giao diện ngay lập tức (Optimistic UI)
                this.classList.remove('fw-bold');
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    let count = parseInt(badge.textContent) - 1;
                    if (count > 0) {
                        badge.textContent = count;
                    } else {
                        badge.remove(); // Xóa chấm đỏ nếu đã đọc hết
                    }
                }

                fetch('mark_notification_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + notificationId
                })
                .then(response => response.json())
                .then(data => {
                    window.location.href = href; // Chuyển trang sau khi đã đánh dấu đọc thành công
                }).catch(error => {
                    console.error('Error:', error);
                    window.location.href = href; // Vẫn cho phép chuyển trang nếu có lỗi mạng
                });
            }
        });
    });
});
</script>