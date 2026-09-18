<?php
require_once 'auth_check.php';
$pageTitle = 'Dashboard';

// Helper: Badge định danh từng module hoạt động
function get_module_badge_info($module) {
    $m = strtolower(trim($module ?? ''));
    switch ($m) {
        case 'users':
            return ['label' => 'Người dùng', 'icon' => 'fas fa-users', 'class' => 'badge-mod-users'];
        case 'projects':
            return ['label' => 'Dự án', 'icon' => 'fas fa-project-diagram', 'class' => 'badge-mod-projects'];
        case 'news':
        case 'posts':
            return ['label' => 'Tin tức', 'icon' => 'fas fa-newspaper', 'class' => 'badge-mod-news'];
        case 'carousel':
        case 'carousel slides':
            return ['label' => 'Carousel', 'icon' => 'fas fa-images', 'class' => 'badge-mod-carousel'];
        case 'recruitments':
        case 'jobs':
            return ['label' => 'Tuyển dụng', 'icon' => 'fas fa-list', 'class' => 'badge-mod-recruitments'];
        case 'applications':
            return ['label' => 'Đơn ứng tuyển', 'icon' => 'fas fa-file-alt', 'class' => 'badge-mod-applications'];
        case 'activity_logs':
        case 'logs':
        case 'authentication':
        case 'auth':
            return ['label' => 'Nhật ký', 'icon' => 'fas fa-history', 'class' => 'badge-mod-logs'];
        case 'import':
            return ['label' => 'Import', 'icon' => 'fas fa-file-import', 'class' => 'badge-mod-import'];
        case 'contacts':
        case 'contact':
            return ['label' => 'Liên hệ', 'icon' => 'fas fa-envelope', 'class' => 'badge-mod-contacts'];
        case 'chat':
            return ['label' => 'Chat', 'icon' => 'fas fa-comments', 'class' => 'badge-mod-chat'];
        default:
            return ['label' => !empty($module) ? htmlspecialchars($module) : 'Hệ thống', 'icon' => 'fas fa-cog', 'class' => 'badge-mod-default'];
    }
}

// 1. Thống kê tổng quan hệ thống (4 thẻ KPI đầu trang)
$stats = [];
$result = $conn->query("SELECT COUNT(*) as total FROM users");
$stats['users'] = $result ? $result->fetch_assoc()['total'] : 0;

$result = $conn->query("SELECT COUNT(*) as total FROM projects");
$stats['projects'] = $result ? $result->fetch_assoc()['total'] : 0;

$result = $conn->query("SELECT COUNT(*) as total FROM applications");
$stats['applications'] = $result ? $result->fetch_assoc()['total'] : 0;

$result = $conn->query("SELECT COUNT(*) as total FROM contact");
$stats['contacts'] = $result ? $result->fetch_assoc()['total'] : 0;

// 2. TRUY VẤN DỮ LIỆU ĐẦY ĐỦ CÁC TRANG THEO ĐÚNG THỨ TỰ SIDEBAR

// Mục 1: Quản Lý Người Dùng (users.php)
$recent_users = $conn->query("
    SELECT id, username, fullname, email, avatar, role, status, created_at 
    FROM users 
    ORDER BY created_at DESC LIMIT 5
");

// Mục 2: Quản Lý Dự Án (projects.php)
$recent_projects = $conn->query("
    SELECT p.*, u.fullname as created_by_name 
    FROM projects p 
    LEFT JOIN users u ON p.created_by = u.id 
    ORDER BY p.created_at DESC LIMIT 5
");

// Mục 3: Quản Lý Tin Tức (news.php)
$recent_posts = $conn->query("
    SELECT p.*, u.fullname as author_name 
    FROM posts p 
    LEFT JOIN users u ON p.created_by = u.id 
    ORDER BY p.created_at DESC LIMIT 5
");

// Mục 4: Quản Lý Carousel (carousel_slides.php)
$table_carousel_res = $conn->query("SHOW TABLES LIKE 'carousel_slides'");
$has_carousel_table = $table_carousel_res && $table_carousel_res->num_rows > 0;
$recent_slides = null;
if ($has_carousel_table) {
    $recent_slides = $conn->query("
        SELECT id, image_path, sort_order, status 
        FROM carousel_slides 
        ORDER BY sort_order ASC, id DESC LIMIT 5
    ");
}

// Mục 5: Quản Lý Tuyển Dụng - Danh sách tin (recruitments.php)
$recent_jobs = $conn->query("
    SELECT id, title, branch, status, created_at 
    FROM jobs 
    ORDER BY created_at DESC LIMIT 5
");

// Mục 6: Quản Lý Tuyển Dụng - Đơn ứng tuyển (applications.php)
$recent_applications = $conn->query("
    SELECT * FROM applications 
    ORDER BY created_at DESC LIMIT 5
");

// Mục 7: Nhật Ký Hoạt Động (activity_logs.php)
$recent_logs = $conn->query("
    SELECT al.*, u.username, u.fullname 
    FROM activity_logs al 
    LEFT JOIN users u ON al.user_id = u.id 
    ORDER BY al.created_at DESC LIMIT 5
");

// Mục 8: Import Dữ liệu (import.php)
$recent_imports = $conn->query("
    SELECT al.*, u.username, u.fullname 
    FROM activity_logs al 
    LEFT JOIN users u ON al.user_id = u.id 
    WHERE al.module = 'import' 
    ORDER BY al.created_at DESC LIMIT 5
");

// Mục 9: Liên hệ (contacts.php)
$recent_contacts = $conn->query("
    SELECT * FROM contact 
    ORDER BY created_at DESC LIMIT 5
");

// Mục 10: Quản Lý Chat (chat.php)
$table_chat_res = $conn->query("SHOW TABLES LIKE 'chat_sessions'");
$has_chat_table = $table_chat_res && $table_chat_res->num_rows > 0;
$recent_chats = null;
if ($has_chat_table) {
    $recent_chats = $conn->query("
        SELECT cs.*, 
               (SELECT message FROM chat_messages cm WHERE cm.session_id = cs.id ORDER BY cm.id DESC LIMIT 1) as last_message 
        FROM chat_sessions cs 
        ORDER BY cs.last_message_time DESC LIMIT 5
    ");
}
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
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1><i class="fas fa-tachometer-alt text-primary me-2"></i>Dashboard</h1>
                <p class="mb-0">
                    Chào mừng quay trở lại, <strong><?php echo htmlspecialchars($_SESSION['admin_fullname'] ?? 'Admin'); ?></strong>
                    <span class="badge bg-light text-secondary border ms-1 mt-1 mt-sm-0 align-middle"><?php echo isset($display_role_str) ? htmlspecialchars($display_role_str) : ''; ?></span>
                </p>
            </div>
            <div class="text-end d-none d-sm-block">
                <div class="text-muted small">Hôm nay</div>
                <div class="fw-bold fs-5 text-dark"><?php echo date('d/m/Y'); ?></div>
            </div>
        </div>
        
        <div class="dashboard-menu-bar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h5 class="section-title mb-1">
                    <i class="fas fa-th-list text-primary me-2"></i>Hoạt động gần nhất
                </h5>
                <div class="section-subtitle">Sắp xếp chuẩn theo thứ tự các menu trên thanh điều hướng sidebar</div>
            </div>
            
            <!-- 1. Hiển thị trên Desktop lớn (>= 992px): Thanh nút lọc cuộn/bấm tiện lợi -->
            <div class="d-none d-lg-flex align-items-center gap-1 flex-wrap">
                <button type="button" class="btn btn-sm btn-section-filter active" data-target="all">
                    <i class="fas fa-th-large me-1"></i>Tất cả mục
                </button>
                <button type="button" class="btn btn-sm btn-section-filter" data-target="users">
                    <i class="fas fa-users me-1" style="color: #003366;"></i>Người dùng
                </button>
                <button type="button" class="btn btn-sm btn-section-filter" data-target="projects">
                    <i class="fas fa-project-diagram me-1 text-primary"></i>Dự án
                </button>
                <button type="button" class="btn btn-sm btn-section-filter" data-target="news">
                    <i class="fas fa-newspaper me-1 text-success"></i>Tin tức
                </button>
                <button type="button" class="btn btn-sm btn-section-filter" data-target="carousel">
                    <i class="fas fa-images me-1" style="color: #db2777;"></i>Carousel
                </button>
                <button type="button" class="btn btn-sm btn-section-filter" data-target="jobs">
                    <i class="fas fa-list me-1" style="color: #9333ea;"></i>Tin tuyển dụng
                </button>
                <button type="button" class="btn btn-sm btn-section-filter" data-target="applications">
                    <i class="fas fa-file-alt me-1 text-info"></i>Đơn ứng tuyển
                </button>
                <button type="button" class="btn btn-sm btn-section-filter" data-target="logs">
                    <i class="fas fa-history me-1 text-secondary"></i>Nhật ký
                </button>
                <button type="button" class="btn btn-sm btn-section-filter" data-target="import">
                    <i class="fas fa-file-import me-1" style="color: #ca8a04;"></i>Import
                </button>
                <button type="button" class="btn btn-sm btn-section-filter" data-target="contacts">
                    <i class="fas fa-envelope me-1 text-warning"></i>Liên hệ
                </button>
                <button type="button" class="btn btn-sm btn-section-filter" data-target="chat">
                    <i class="fas fa-comments me-1 text-info"></i>Chat
                </button>
            </div>

            <!-- 2. Hiển thị trên Màn hình vừa & nhỏ (< 992px): Dropdown menu cực kỳ gọn gàng -->
            <div class="dropdown d-lg-none mobile-filter-dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle d-flex justify-content-between align-items-center w-100" type="button" id="dashboardFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="d-flex align-items-center text-truncate">
                        <i class="fas fa-filter text-primary me-2"></i>
                        <span class="text-muted small me-1">Đang xem:</span>
                        <strong class="text-dark active-filter-text"><i class="fas fa-th-large me-1 text-secondary"></i>Tất cả các trang</strong>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2 w-100" aria-labelledby="dashboardFilterDropdown" style="max-height: 350px; overflow-y: auto;">
                    <li><a class="dropdown-item py-2 active d-flex justify-content-between align-items-center btn-dropdown-filter" href="#" data-target="all">
                        <span><i class="fas fa-th-large me-2 text-secondary"></i>Tất cả các trang</span>
                        <i class="fas fa-check small text-primary filter-check-icon"></i>
                    </a></li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li><a class="dropdown-item py-2 d-flex justify-content-between align-items-center btn-dropdown-filter" href="#" data-target="users">
                        <span><i class="fas fa-users me-2" style="color: #003366;"></i>1. Quản Lý Người Dùng</span>
                        <i class="fas fa-check small text-primary filter-check-icon d-none"></i>
                    </a></li>
                    <li><a class="dropdown-item py-2 d-flex justify-content-between align-items-center btn-dropdown-filter" href="#" data-target="projects">
                        <span><i class="fas fa-project-diagram me-2 text-primary"></i>2. Quản Lý Dự Án</span>
                        <i class="fas fa-check small text-primary filter-check-icon d-none"></i>
                    </a></li>
                    <li><a class="dropdown-item py-2 d-flex justify-content-between align-items-center btn-dropdown-filter" href="#" data-target="news">
                        <span><i class="fas fa-newspaper me-2 text-success"></i>3. Quản Lý Tin Tức</span>
                        <i class="fas fa-check small text-primary filter-check-icon d-none"></i>
                    </a></li>
                    <li><a class="dropdown-item py-2 d-flex justify-content-between align-items-center btn-dropdown-filter" href="#" data-target="carousel">
                        <span><i class="fas fa-images me-2" style="color: #db2777;"></i>4. Quản Lý Carousel</span>
                        <i class="fas fa-check small text-primary filter-check-icon d-none"></i>
                    </a></li>
                    <li><a class="dropdown-item py-2 d-flex justify-content-between align-items-center btn-dropdown-filter" href="#" data-target="jobs">
                        <span><i class="fas fa-list me-2" style="color: #9333ea;"></i>5. Tuyển Dụng - Danh sách tin</span>
                        <i class="fas fa-check small text-primary filter-check-icon d-none"></i>
                    </a></li>
                    <li><a class="dropdown-item py-2 d-flex justify-content-between align-items-center btn-dropdown-filter" href="#" data-target="applications">
                        <span><i class="fas fa-file-alt me-2 text-info"></i>6. Tuyển Dụng - Đơn ứng tuyển</span>
                        <i class="fas fa-check small text-primary filter-check-icon d-none"></i>
                    </a></li>
                    <li><a class="dropdown-item py-2 d-flex justify-content-between align-items-center btn-dropdown-filter" href="#" data-target="logs">
                        <span><i class="fas fa-history me-2 text-secondary"></i>7. Nhật Ký Hoạt Động</span>
                        <i class="fas fa-check small text-primary filter-check-icon d-none"></i>
                    </a></li>
                    <li><a class="dropdown-item py-2 d-flex justify-content-between align-items-center btn-dropdown-filter" href="#" data-target="import">
                        <span><i class="fas fa-file-import me-2" style="color: #ca8a04;"></i>8. Import Dữ Liệu</span>
                        <i class="fas fa-check small text-primary filter-check-icon d-none"></i>
                    </a></li>
                    <li><a class="dropdown-item py-2 d-flex justify-content-between align-items-center btn-dropdown-filter" href="#" data-target="contacts">
                        <span><i class="fas fa-envelope me-2 text-warning"></i>9. Liên Hệ</span>
                        <i class="fas fa-check small text-primary filter-check-icon d-none"></i>
                    </a></li>
                    <li><a class="dropdown-item py-2 d-flex justify-content-between align-items-center btn-dropdown-filter" href="#" data-target="chat">
                        <span><i class="fas fa-comments me-2 text-info"></i>10. Quản Lý Chat</span>
                        <i class="fas fa-check small text-primary filter-check-icon d-none"></i>
                    </a></li>
                </ul>
            </div>
        </div>

        <!-- DANH SÁCH CÁC TRANG ĐƯỢC TÁCH BIỆT RÕ RÀNG (THỨ TỰ CHUẨN SIDEBAR) -->
        <div class="row g-3 mb-4">
            
            <!-- 1. QUẢN LÝ NGƯỜI DÙNG (users.php) -->
            <div class="col-12 col-xl-6 section-card-col" data-section="users">
                <div class="card dashboard-table-card card-section-users">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-module badge-mod-users">
                                <i class="fas fa-users"></i> Quản Lý Người Dùng
                            </span>
                            <h6 class="mb-0 fw-bold text-dark">Tài khoản gần đây</h6>
                        </div>
                        <a href="users.php" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" style="font-size: 11px;">
                            Xem tất cả <i class="fas fa-arrow-right ms-1 small"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table dashboard-table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 44%;">Họ tên & Tài khoản</th>
                                        <th scope="col" style="width: 32%;">Vai trò</th>
                                        <th scope="col" class="text-center" style="width: 24%;">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_users && $recent_users->num_rows > 0): ?>
                                        <?php while($u = $recent_users->fetch_assoc()): ?>
                                            <tr>
                                                <td data-label="Tài khoản">
                                                    <div class="d-flex align-items-center">
                                                        <div class="activity-user-avatar me-2">
                                                            <?php if (!empty($u['avatar']) && file_exists(__DIR__ . '/../' . $u['avatar'])): ?>
                                                                <img src="../<?php echo htmlspecialchars($u['avatar']); ?>" alt="Avatar">
                                                            <?php else: ?>
                                                                <i class="fas fa-user-circle"></i>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div>
                                                            <a href="view_user.php?id=<?php echo (int)$u['id']; ?>" class="table-item-title fw-semibold text-dark d-block text-truncate" style="max-width: 170px;" title="<?php echo htmlspecialchars(!empty($u['fullname']) ? $u['fullname'] : $u['username']); ?>">
                                                                <?php echo htmlspecialchars(!empty($u['fullname']) ? $u['fullname'] : $u['username']); ?>
                                                            </a>
                                                            <div class="cell-meta-sub">
                                                                <span>@<?php echo htmlspecialchars($u['username']); ?></span>
                                                                <span class="mx-1">•</span>
                                                                <span><i class="far fa-calendar-alt me-1"></i><?php echo date('d/m/Y', strtotime($u['created_at'])); ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td data-label="Vai trò">
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <?php 
                                                            $u_roles = !empty($u['role']) ? explode(',', $u['role']) : ['user'];
                                                            foreach ($u_roles as $ur) {
                                                                $ur = trim($ur);
                                                                if (empty($ur)) continue;
                                                                $r_label = $role_config[$ur]['label'] ?? ucfirst($ur);
                                                                $r_color = $role_config[$ur]['color'] ?? 'secondary';
                                                                $text_dark = in_array($r_color, ['warning', 'info']) ? ' text-dark' : '';
                                                                echo '<span class="badge bg-' . $r_color . $text_dark . ' fw-normal" style="font-size: 11px;">' . htmlspecialchars($r_label) . '</span>';
                                                            }
                                                        ?>
                                                    </div>
                                                </td>
                                                <td data-label="Trạng thái" class="text-center">
                                                    <span class="badge rounded-pill bg-<?php echo ($u['status'] === 'active') ? 'success' : 'danger'; ?>">
                                                        <?php echo ($u['status'] === 'active') ? 'Hoạt động' : 'Đã khóa'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                <i class="fas fa-user-friends d-block mb-1 text-secondary" style="font-size: 1.5rem; opacity: 0.5;"></i>
                                                Chưa có người dùng nào
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. QUẢN LÝ DỰ ÁN (projects.php) -->
            <div class="col-12 col-xl-6 section-card-col" data-section="projects">
                <div class="card dashboard-table-card card-section-projects">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-module badge-mod-projects">
                                <i class="fas fa-project-diagram"></i> Quản Lý Dự Án
                            </span>
                            <h6 class="mb-0 fw-bold text-dark">Dự án gần đây</h6>
                        </div>
                        <a href="projects.php" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" style="font-size: 11px;">
                            Xem tất cả <i class="fas fa-arrow-right ms-1 small"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table dashboard-table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 46%;">Tiêu đề dự án</th>
                                        <th scope="col" style="width: 28%;">Khách hàng</th>
                                        <th scope="col" class="text-center" style="width: 26%;">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_projects && $recent_projects->num_rows > 0): ?>
                                        <?php while($p = $recent_projects->fetch_assoc()): ?>
                                            <tr>
                                                <td data-label="Tiêu đề">
                                                    <a href="project-edit.php?id=<?php echo $p['id']; ?>" class="table-item-title text-truncate" style="max-width: 220px;" title="<?php echo htmlspecialchars($p['title']); ?>">
                                                        <?php echo htmlspecialchars($p['title']); ?>
                                                    </a>
                                                    <div class="cell-meta-sub">
                                                        <i class="far fa-calendar-alt me-1"></i><?php echo date('d/m/Y', strtotime($p['created_at'])); ?>
                                                    </div>
                                                </td>
                                                <td data-label="Khách hàng">
                                                    <span class="text-muted">
                                                        <?php echo !empty($p['client']) ? htmlspecialchars($p['client']) : '<em class="text-muted small">Chưa có</em>'; ?>
                                                    </span>
                                                </td>
                                                <td data-label="Trạng thái" class="text-center">
                                                    <?php 
                                                     $p_map = ['published' => ['Đã xuất bản', 'success'], 'pending' => ['Chờ duyệt', 'info text-dark'], 'draft' => ['Bản nháp', 'warning text-dark']];
                                                     $p_st = $p_map[$p['status']] ?? [$p['status'], 'secondary'];
                                                    ?>
                                                    <span class="badge rounded-pill bg-<?php echo $p_st[1]; ?>">
                                                        <?php echo $p_st[0]; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Chưa có dự án nào</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. QUẢN LÝ TIN TỨC (news.php) -->
            <div class="col-12 col-xl-6 section-card-col" data-section="news">
                <div class="card dashboard-table-card card-section-news">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-module badge-mod-news">
                                <i class="fas fa-newspaper"></i> Quản Lý Tin Tức
                            </span>
                            <h6 class="mb-0 fw-bold text-dark">Tin tức mới nhất</h6>
                        </div>
                        <a href="news.php" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1" style="font-size: 11px;">
                            Xem tất cả <i class="fas fa-arrow-right ms-1 small"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table dashboard-table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 48%;">Tiêu đề bài viết</th>
                                        <th scope="col" style="width: 26%;">Tác giả</th>
                                        <th scope="col" class="text-center" style="width: 26%;">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_posts && $recent_posts->num_rows > 0): ?>
                                        <?php while($post = $recent_posts->fetch_assoc()): ?>
                                            <tr>
                                                <td data-label="Tiêu đề">
                                                    <a href="post-edit.php?id=<?php echo $post['id']; ?>" class="table-item-title text-truncate" style="max-width: 220px;" title="<?php echo htmlspecialchars($post['title']); ?>">
                                                        <?php echo htmlspecialchars($post['title']); ?>
                                                    </a>
                                                    <div class="cell-meta-sub">
                                                        <i class="far fa-calendar-alt me-1"></i><?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
                                                    </div>
                                                </td>
                                                <td data-label="Tác giả">
                                                    <span class="text-muted small">
                                                        <i class="far fa-user me-1"></i>
                                                        <?php echo htmlspecialchars($post['author_name'] ?? 'Ban biên tập'); ?>
                                                    </span>
                                                </td>
                                                <td data-label="Trạng thái" class="text-center">
                                                    <span class="badge rounded-pill bg-<?php echo ($post['status'] === 'published') ? 'success' : 'warning text-dark'; ?>">
                                                        <?php echo ($post['status'] === 'published') ? 'Đã xuất bản' : 'Bản nháp'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Chưa có bài viết nào</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. QUẢN LÝ CAROUSEL (carousel_slides.php) -->
            <div class="col-12 col-xl-6 section-card-col" data-section="carousel">
                <div class="card dashboard-table-card card-section-carousel">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-module badge-mod-carousel">
                                <i class="fas fa-images"></i> Quản Lý Carousel
                            </span>
                            <h6 class="mb-0 fw-bold text-dark">Slide ảnh quảng cáo</h6>
                        </div>
                        <a href="carousel_slides.php" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" style="font-size: 11px; border-color: #db2777; color: #db2777;">
                            Xem tất cả <i class="fas fa-arrow-right ms-1 small"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table dashboard-table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 45%;">Hình ảnh</th>
                                        <th scope="col" class="text-center" style="width: 25%;">Thứ tự</th>
                                        <th scope="col" class="text-center" style="width: 30%;">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_slides && $recent_slides->num_rows > 0): ?>
                                        <?php while($slide = $recent_slides->fetch_assoc()): ?>
                                            <tr>
                                                <td data-label="Hình ảnh">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <?php if (!empty($slide['image_path']) && file_exists('../' . $slide['image_path'])): ?>
                                                            <img src="../<?php echo htmlspecialchars($slide['image_path']); ?>" alt="Slide" class="rounded border" style="width: 58px; height: 32px; object-fit: cover;">
                                                        <?php else: ?>
                                                            <span class="badge bg-light text-secondary border p-2"><i class="fas fa-image"></i></span>
                                                        <?php endif; ?>
                                                        <span class="small text-muted text-truncate" style="max-width: 140px;">Slide #<?php echo $slide['id']; ?></span>
                                                    </div>
                                                </td>
                                                <td data-label="Thứ tự" class="text-center">
                                                    <span class="badge bg-light text-dark border">#<?php echo $slide['sort_order']; ?></span>
                                                </td>
                                                <td data-label="Trạng thái" class="text-center">
                                                    <span class="badge rounded-pill bg-<?php echo ($slide['status'] === 'active') ? 'success' : 'secondary'; ?>">
                                                        <?php echo ($slide['status'] === 'active') ? 'Hiển thị' : 'Tạm ẩn'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Chưa có slide nào</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. QUẢN LÝ TUYỂN DỤNG - DANH SÁCH TIN (recruitments.php) -->
            <div class="col-12 col-xl-6 section-card-col" data-section="jobs">
                <div class="card dashboard-table-card card-section-jobs">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-module badge-mod-recruitments">
                                <i class="fas fa-list"></i> Tuyển Dụng - Tin Đăng
                            </span>
                            <h6 class="mb-0 fw-bold text-dark">Tin tuyển dụng</h6>
                        </div>
                        <a href="recruitments.php" class="btn btn-sm btn-outline-purple rounded-pill px-2 py-1" style="font-size: 11px; border-color: #9333ea; color: #9333ea;">
                            Xem tất cả <i class="fas fa-arrow-right ms-1 small"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table dashboard-table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 48%;">Vị trí tuyển dụng</th>
                                        <th scope="col" style="width: 28%;">Chi nhánh</th>
                                        <th scope="col" class="text-center" style="width: 24%;">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_jobs && $recent_jobs->num_rows > 0): ?>
                                        <?php while($job = $recent_jobs->fetch_assoc()): 
                                            $job_open = ($job['status'] === 'open');
                                        ?>
                                            <tr>
                                                <td data-label="Vị trí">
                                                    <span class="fw-semibold text-dark d-block text-truncate" style="max-width: 220px;" title="<?php echo htmlspecialchars($job['title']); ?>">
                                                        <?php echo htmlspecialchars($job['title']); ?>
                                                    </span>
                                                    <div class="cell-meta-sub">
                                                        <i class="far fa-calendar-alt me-1"></i><?php echo date('d/m/Y', strtotime($job['created_at'])); ?>
                                                    </div>
                                                </td>
                                                <td data-label="Chi nhánh">
                                                    <span class="text-muted small">
                                                        <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                                        <?php echo htmlspecialchars($job['branch'] ?? 'Toàn quốc'); ?>
                                                    </span>
                                                </td>
                                                <td data-label="Trạng thái" class="text-center">
                                                    <span class="badge rounded-pill bg-<?php echo $job_open ? 'success' : 'secondary'; ?>">
                                                        <?php echo $job_open ? 'Đang tuyển' : 'Đã đóng'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Chưa có tin tuyển dụng nào</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. QUẢN LÝ TUYỂN DỤNG - ĐƠN ỨNG TUYỂN (applications.php) -->
            <div class="col-12 col-xl-6 section-card-col" data-section="applications">
                <div class="card dashboard-table-card card-section-applications">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-module badge-mod-applications">
                                <i class="fas fa-file-alt"></i> Tuyển Dụng - Hồ Sơ
                            </span>
                            <h6 class="mb-0 fw-bold text-dark">Đơn ứng tuyển</h6>
                        </div>
                        <a href="applications.php" class="btn btn-sm btn-outline-info rounded-pill px-2 py-1" style="font-size: 11px;">
                            Xem tất cả <i class="fas fa-arrow-right ms-1 small"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table dashboard-table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 40%;">Ứng viên</th>
                                        <th scope="col" style="width: 36%;">Vị trí</th>
                                        <th scope="col" class="text-end" style="width: 24%;">Hồ sơ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_applications && $recent_applications->num_rows > 0): ?>
                                        <?php while($app = $recent_applications->fetch_assoc()): ?>
                                            <tr>
                                                <td data-label="Ứng viên">
                                                    <span class="fw-semibold text-dark d-block">
                                                        <?php echo htmlspecialchars($app['fullname']); ?>
                                                    </span>
                                                    <div class="cell-meta-sub">
                                                        <i class="fas fa-phone-alt me-1"></i><?php echo htmlspecialchars($app['phone'] ?? ''); ?>
                                                    </div>
                                                </td>
                                                <td data-label="Vị trí">
                                                    <span class="text-secondary fw-medium">
                                                        <?php echo htmlspecialchars($app['position']); ?>
                                                    </span>
                                                    <div class="cell-meta-sub">
                                                        <i class="far fa-clock me-1"></i><?php echo date('d/m/Y', strtotime($app['created_at'])); ?>
                                                    </div>
                                                </td>
                                                <td data-label="Hồ sơ" class="text-end">
                                                    <a href="view_application.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-light border px-2 py-1" style="font-size: 11px;">
                                                        <i class="fas fa-eye text-primary me-1"></i>Xem CV
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Chưa có đơn ứng tuyển nào</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 7. NHẬT KÝ HOẠT ĐỘNG (activity_logs.php) -->
            <div class="col-12 col-xl-6 section-card-col" data-section="logs">
                <div class="card dashboard-table-card card-section-logs">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-module badge-mod-logs">
                                <i class="fas fa-history"></i> Nhật Ký Hoạt Động
                            </span>
                            <h6 class="mb-0 fw-bold text-dark">Thao tác hệ thống</h6>
                        </div>
                        <a href="activity_logs.php" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" style="font-size: 11px;">
                            Xem tất cả <i class="fas fa-arrow-right ms-1 small"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table dashboard-table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 32%;">Người dùng</th>
                                        <th scope="col" style="width: 44%;">Hành động</th>
                                        <th scope="col" class="text-end" style="width: 24%;">Thời gian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_logs && $recent_logs->num_rows > 0): ?>
                                        <?php while($log = $recent_logs->fetch_assoc()): ?>
                                            <tr>
                                                <td data-label="Người dùng">
                                                    <div class="d-flex align-items-center">
                                                        <div class="activity-user-avatar me-2">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                        <span class="fw-medium text-dark text-truncate" style="max-width: 130px;">
                                                            <?php echo htmlspecialchars($log['fullname'] ?? $log['username'] ?? 'Hệ thống'); ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td data-label="Hành động">
                                                    <div class="cell-action-text text-secondary" title="<?php echo htmlspecialchars($log['action']); ?>">
                                                        <?php echo htmlspecialchars($log['action']); ?>
                                                    </div>
                                                </td>
                                                <td data-label="Thời gian" class="text-end">
                                                    <span class="text-muted small">
                                                        <i class="far fa-clock me-1"></i>
                                                        <?php echo date('d/m H:i', strtotime($log['created_at'])); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Chưa có nhật ký hoạt động nào</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 8. IMPORT DỮ LIỆU (import.php) -->
            <div class="col-12 col-xl-6 section-card-col" data-section="import">
                <div class="card dashboard-table-card card-section-import">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-module badge-mod-import">
                                <i class="fas fa-file-import"></i> Import Dữ Liệu
                            </span>
                            <h6 class="mb-0 fw-bold text-dark">Nhập dữ liệu nhanh</h6>
                        </div>
                        <a href="import.php" class="btn btn-sm btn-outline-warning rounded-pill px-2 py-1" style="font-size: 11px; border-color: #ca8a04; color: #ca8a04;">
                            Vào trang Import <i class="fas fa-arrow-right ms-1 small"></i>
                        </a>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded border mb-2">
                            <div>
                                <div class="fw-semibold text-dark"><i class="fas fa-file-word text-primary me-2"></i>Tải lên file Word (.docx) hoặc PDF</div>
                                <div class="text-muted small mt-1">Chuyển đổi văn bản thành Dự án hoặc Bài viết tin tức tự động.</div>
                            </div>
                            <a href="import.php" class="btn btn-sm btn-primary rounded-pill px-3 py-1 text-nowrap">
                                <i class="fas fa-upload me-1"></i> Tải file
                            </a>
                        </div>
                        <?php if ($recent_imports && $recent_imports->num_rows > 0): ?>
                            <div class="small fw-semibold text-muted mb-2"><i class="fas fa-history me-1"></i>Lần import gần nhất:</div>
                            <ul class="list-group list-group-flush small">
                                <?php while($imp = $recent_imports->fetch_assoc()): ?>
                                    <li class="list-group-item px-0 py-1 d-flex justify-content-between align-items-center border-0">
                                        <span class="text-truncate" style="max-width: 250px;"><?php echo htmlspecialchars($imp['action']); ?></span>
                                        <span class="text-muted"><?php echo date('d/m H:i', strtotime($imp['created_at'])); ?></span>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 9. LIÊN HỆ (contacts.php) -->
            <div class="col-12 col-xl-6 section-card-col" data-section="contacts">
                <div class="card dashboard-table-card card-section-contacts">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-module badge-mod-contacts">
                                <i class="fas fa-envelope"></i> Liên Hệ
                            </span>
                            <h6 class="mb-0 fw-bold text-dark">Liên hệ mới nhất</h6>
                        </div>
                        <a href="contacts.php" class="btn btn-sm btn-outline-warning rounded-pill px-2 py-1" style="font-size: 11px;">
                            Xem tất cả <i class="fas fa-arrow-right ms-1 small"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table dashboard-table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 40%;">Người gửi</th>
                                        <th scope="col" style="width: 36%;">Yêu cầu tư vấn</th>
                                        <th scope="col" class="text-center" style="width: 24%;">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_contacts && $recent_contacts->num_rows > 0): ?>
                                        <?php while($c = $recent_contacts->fetch_assoc()): 
                                            $c_done = in_array($c['status'], ['replied', 'done']);
                                        ?>
                                            <tr>
                                                <td data-label="Người gửi">
                                                    <a href="view_contact.php?id=<?php echo $c['id']; ?>" class="table-item-title text-truncate" style="max-width: 170px;" title="<?php echo htmlspecialchars($c['name']); ?>">
                                                        <?php echo htmlspecialchars($c['name']); ?>
                                                    </a>
                                                    <div class="cell-meta-sub">
                                                        <i class="far fa-clock me-1"></i><?php echo date('d/m/Y', strtotime($c['created_at'])); ?>
                                                    </div>
                                                </td>
                                                <td data-label="Nội dung">
                                                    <span class="text-secondary text-truncate d-block" style="max-width: 170px;" title="<?php echo htmlspecialchars($c['subject'] ?? ''); ?>">
                                                        <?php echo !empty($c['subject']) ? htmlspecialchars($c['subject']) : '<em class="text-muted small">Tư vấn quảng cáo</em>'; ?>
                                                    </span>
                                                </td>
                                                <td data-label="Trạng thái" class="text-center">
                                                    <span class="badge rounded-pill bg-<?php echo $c_done ? 'success' : 'warning text-dark'; ?>">
                                                        <?php echo $c_done ? 'Đã phản hồi' : 'Chưa xử lý'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Chưa có liên hệ nào</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 10. QUẢN LÝ CHAT (chat.php) -->
            <div class="col-12 col-xl-6 section-card-col" data-section="chat">
                <div class="card dashboard-table-card card-section-chat">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-module badge-mod-chat">
                                <i class="fas fa-comments"></i> Quản Lý Chat
                            </span>
                            <h6 class="mb-0 fw-bold text-dark">Hội thoại trực tuyến</h6>
                        </div>
                        <a href="chat.php" class="btn btn-sm btn-outline-info rounded-pill px-2 py-1" style="font-size: 11px;">
                            Mở phòng Chat <i class="fas fa-arrow-right ms-1 small"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table dashboard-table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 38%;">Khách hàng</th>
                                        <th scope="col" style="width: 38%;">Tin nhắn gần nhất</th>
                                        <th scope="col" class="text-center" style="width: 24%;">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_chats && $recent_chats->num_rows > 0): ?>
                                        <?php while($chat = $recent_chats->fetch_assoc()): ?>
                                            <tr>
                                                <td data-label="Khách hàng">
                                                    <a href="chat.php" class="table-item-title text-truncate" style="max-width: 160px;" title="<?php echo htmlspecialchars($chat['name']); ?>">
                                                        <?php echo htmlspecialchars($chat['name']); ?>
                                                    </a>
                                                    <div class="cell-meta-sub">
                                                        <i class="fas fa-phone-alt me-1"></i><?php echo htmlspecialchars($chat['phone'] ?? ''); ?>
                                                    </div>
                                                </td>
                                                <td data-label="Tin nhắn">
                                                    <span class="text-secondary text-truncate d-block" style="max-width: 170px;" title="<?php echo htmlspecialchars($chat['last_message'] ?? '...'); ?>">
                                                        <?php echo !empty($chat['last_message']) ? htmlspecialchars($chat['last_message']) : '<em class="text-muted small">Chưa có tin</em>'; ?>
                                                    </span>
                                                </td>
                                                <td data-label="Trạng thái" class="text-center">
                                                    <span class="badge rounded-pill bg-<?php echo ($chat['status'] === 'open') ? 'success' : 'secondary'; ?>">
                                                        <?php echo ($chat['status'] === 'open') ? 'Đang mở' : 'Đã đóng'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Chưa có phiên chat nào</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html>