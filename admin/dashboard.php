<?php
require_once 'auth_check.php';
$pageTitle = 'Dashboard';

// Get statistics
$stats = [];
// Kiểm tra quyền admin để hiển thị log (biến $user_roles từ auth_check.php)
$is_admin = in_array('admin', $user_roles);

$result = $conn->query("SELECT COUNT(*) as total FROM users");
$stats['users'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM projects");
$stats['projects'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM applications");
$stats['applications'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM contact");
$stats['contacts'] = $result->fetch_assoc()['total'];

// Recent activity logs
if ($is_admin) {
    $recent_logs = $conn->query("SELECT al.*, u.username, u.fullname FROM activity_logs al 
                                 LEFT JOIN users u ON al.user_id = u.id 
                                 ORDER BY al.created_at DESC LIMIT 10");
}

// Recent projects
$recent_projects = $conn->query("SELECT p.*, u.fullname as created_by_name FROM projects p 
                                 LEFT JOIN users u ON p.created_by = u.id 
                                 ORDER BY p.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | FUTA Advertising' : 'FUTA Advertising'; ?></title>
    <link rel="icon" href="/FUTA_PHP/assets/images/logo/futa.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background: #f7f9fc; }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid;
        }
        .stat-card.users { border-color: #003366; }
        .stat-card.projects { border-color: #667eea; }
        .stat-card.applications { border-color: #4facfe; }
        .stat-card.contacts { border-color: #43e97b; }
        .stat-icon { font-size: 2.5rem; margin-bottom: 10px; }
        .stat-card.users .stat-icon { color: #003366; }
        .stat-card.projects .stat-icon { color: #667eea; }
        .stat-card.applications .stat-icon { color: #4facfe; }
        .stat-card.contacts .stat-icon { color: #43e97b; }
        .page-header {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .page-header h1 { font-weight: 700; font-size: 1.75rem; margin: 0; color: #1f2a37; }
        .page-header p { color: #6b7280; margin: 5px 0 0; font-size: 14px; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-tachometer-alt text-primary me-2"></i>Dashboard</h1>
                <p class="mb-0">
                    Chào mừng quay trở lại, <strong><?php echo htmlspecialchars($_SESSION['admin_fullname'] ?? 'Admin'); ?></strong>
                    <span class="badge bg-light text-secondary border ms-2"><?php echo isset($display_role_str) ? htmlspecialchars($display_role_str) : ''; ?></span>
                </p>
            </div>
            <div class="text-end d-none d-md-block">
                <div class="text-muted small">Hôm nay</div>
                <div class="fw-bold fs-5"><?php echo date('d/m/Y'); ?></div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card users">
                    <i class="fas fa-users stat-icon"></i>
                    <h3 class="mb-0"><?php echo $stats['users']; ?></h3>
                    <p class="text-muted mb-0">Tổng người dùng</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card projects">
                    <i class="fas fa-project-diagram stat-icon"></i>
                    <h3 class="mb-0"><?php echo $stats['projects']; ?></h3>
                    <p class="text-muted mb-0">Tổng dự án</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card applications">
                    <i class="fas fa-file-alt stat-icon"></i>
                    <h3 class="mb-0"><?php echo $stats['applications']; ?></h3>
                    <p class="text-muted mb-0">Đơn ứng tuyển</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card contacts">
                    <i class="fas fa-envelope stat-icon"></i>
                    <h3 class="mb-0"><?php echo $stats['contacts']; ?></h3>
                    <p class="text-muted mb-0">Tin nhắn liên hệ</p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="<?php echo $is_admin ? 'col-md-6' : 'col-12'; ?>">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-project-diagram"></i> Dự án gần đây</h5>
                        <a href="projects.php" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tiêu đề</th>
                                        <th>Mô tả ngắn</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_projects->num_rows > 0): ?>
                                        <?php while($project = $recent_projects->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($project['title']); ?></td>
                                                <td><?php echo htmlspecialchars($project['client']); ?></td>
                                                <td>
                                                    <?php 
                                                    $badge_class = ['draft' => 'warning', 'pending' => 'info', 'published' => 'success'];
                                                    $status_text = ['draft' => 'Nháp', 'pending' => 'Chờ duyệt', 'published' => 'Đã xuất bản'];
                                                    ?>
                                                    <span class="badge bg-<?php echo $badge_class[$project['status']] ?? 'secondary'; ?>">
                                                        <?php echo $status_text[$project['status']] ?? $project['status']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Chưa có dự án nào</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($is_admin): ?>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Hoạt động gần đây</h5>
                        <a href="activity_logs.php" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Người dùng</th>
                                        <th>Hành động</th>
                                        <th>Thời gian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_logs->num_rows > 0): ?>
                                        <?php while($log = $recent_logs->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($log['fullname'] ?? $log['username'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($log['action']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Chưa có hoạt động nào</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>