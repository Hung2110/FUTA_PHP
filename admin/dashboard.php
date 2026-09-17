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
    <link rel="icon" href="../assets/images/logo/futa.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background: #f7f9fc; }
        .stat-card {
            background: white;
            padding: 16px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border-left: 4px solid;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(0,0,0,0.07);
        }
        .stat-card.users { border-color: #003366; }
        .stat-card.projects { border-color: #667eea; }
        .stat-card.applications { border-color: #4facfe; }
        .stat-card.contacts { border-color: #43e97b; }
        .stat-icon { font-size: 2rem; margin-bottom: 8px; }
        .stat-card.users .stat-icon { color: #003366; }
        .stat-card.projects .stat-icon { color: #667eea; }
        .stat-card.applications .stat-icon { color: #4facfe; }
        .stat-card.contacts .stat-icon { color: #43e97b; }
        .stat-number { font-size: 1.55rem; font-weight: 700; color: #1e293b; }
        .stat-label { font-size: 0.82rem; color: #64748b; font-weight: 500; }

        .page-header {
            background: #fff;
            padding: 16px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .page-header h1 { font-weight: 700; font-size: 1.5rem; margin: 0; color: #1f2a37; }
        .page-header p { color: #6b7280; margin: 4px 0 0; font-size: 13px; }

        .dashboard-table-card {
            border-radius: 10px;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 1px 6px rgba(0,0,0,0.03);
            overflow: hidden;
            background: #fff;
            height: 100%;
        }
        .dashboard-table-card .card-header {
            padding: 8px 14px;
            border-bottom: 1px solid #edf2f7;
        }
        @media (min-width: 992px) {
            .dashboard-table {
                table-layout: fixed;
                width: 100%;
                margin-bottom: 0;
            }
            .dashboard-table th {
                font-size: 0.80rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                font-weight: 600;
                color: #64748b;
                background-color: #f8fafc;
                border-top: none;
                padding: 9px 12px;
                vertical-align: middle;
                white-space: nowrap;
            }
            .dashboard-table td {
                padding: 9px 12px;
                vertical-align: middle;
                font-size: 0.84rem;
                border-bottom: 1px solid #f1f5f9;
                white-space: normal;
                word-break: break-word;
                line-height: 1.4;
            }
        }

        /* Responsive Điều Chỉnh cho Tablet & Mobile - Rõ ràng, hiển thị đầy đủ nội dung */
        @media (max-width: 991.98px) {
            .page-header {
                padding: 14px 16px;
                margin-bottom: 14px;
            }
            .page-header h1 {
                font-size: 1.35rem;
                font-weight: 700;
            }
            .page-header p {
                font-size: 0.88rem;
                color: #475569;
            }
            .stat-card {
                padding: 14px 16px;
                border-radius: 10px;
            }
            .stat-icon {
                font-size: 1.5rem;
                margin-bottom: 4px;
            }
            .stat-number {
                font-size: 1.45rem;
                font-weight: 700;
            }
            .stat-label {
                font-size: 0.85rem;
                color: #475569;
            }
            .dashboard-table-card .card-header {
                padding: 12px 16px;
            }
            .dashboard-table-card .card-header h6 {
                font-size: 1rem !important;
                font-weight: 600;
            }
            .dashboard-table th {
                padding: 9px 12px !important;
                font-size: 0.82rem !important;
            }
            .dashboard-table td {
                padding: 9px 12px !important;
                font-size: 0.88rem !important;
                line-height: 1.5;
            }
            .dashboard-table .badge {
                font-size: 0.80rem !important;
                padding: 0.35em 0.65em !important;
            }
        }

        @media (max-width: 767.98px) {
            .page-header {
                padding: 12px 14px;
                margin-bottom: 12px;
            }
            .page-header h1 {
                font-size: 1.25rem;
            }
            .page-header p {
                font-size: 0.85rem;
            }
            .stat-card {
                padding: 12px 12px;
                border-radius: 8px;
                border-left-width: 4px;
            }
            .stat-icon {
                font-size: 1.35rem;
                margin-bottom: 3px;
            }
            .stat-number {
                font-size: 1.35rem;
            }
            .stat-label {
                font-size: 0.82rem;
            }
            .dashboard-table-card {
                border-radius: 8px;
            }
            .dashboard-table-card .card-header {
                padding: 10px 14px;
            }
            .dashboard-table-card .card-header h6 {
                font-size: 0.95rem !important;
            }
            .dashboard-table th {
                padding: 8px 10px !important;
                font-size: 0.80rem !important;
                letter-spacing: 0;
            }
            .dashboard-table td {
                padding: 8px 10px !important;
                font-size: 0.86rem !important;
                line-height: 1.45;
            }
            .dashboard-table .badge {
                font-size: 0.78rem !important;
                padding: 0.3em 0.6em !important;
            }
        }

        /* Điện thoại cực nhỏ (< 400px, ví dụ iPhone SE 375px hoặc 320px) */
        @media (max-width: 399.98px) {
            .page-header {
                padding: 10px 10px;
                margin-bottom: 10px;
            }
            .page-header h1 {
                font-size: 1.15rem;
            }
            .page-header p {
                font-size: 0.80rem;
            }
            .stat-card {
                padding: 10px 10px;
                border-radius: 8px;
            }
            .stat-icon {
                font-size: 1.25rem;
                margin-bottom: 2px;
            }
            .stat-number {
                font-size: 1.20rem;
            }
            .stat-label {
                font-size: 0.78rem;
            }
            .dashboard-table-card .card-header {
                padding: 8px 10px;
            }
            .dashboard-table th {
                padding: 7px 8px !important;
                font-size: 0.76rem !important;
            }
            .dashboard-table td {
                padding: 7px 8px !important;
                font-size: 0.82rem !important;
                line-height: 1.4;
            }
            .dashboard-table .badge {
                font-size: 0.74rem !important;
                padding: 0.25em 0.5em !important;
            }
        }
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
                    <span class="badge bg-light text-secondary border ms-1 mt-1 mt-sm-0 align-middle"><?php echo isset($display_role_str) ? htmlspecialchars($display_role_str) : ''; ?></span>
                </p>
            </div>
            <div class="text-end d-none d-sm-block">
                <div class="text-muted small">Hôm nay</div>
                <div class="fw-bold fs-5 text-dark"><?php echo date('d/m/Y'); ?></div>
            </div>
        </div>

        <div class="row g-2 g-md-3 mb-3">
            <div class="col-6 col-md-6 col-lg-3">
                <div class="stat-card users">
                    <i class="fas fa-users stat-icon"></i>
                    <div>
                        <h3 class="stat-number mb-0"><?php echo number_format($stats['users']); ?></h3>
                        <p class="stat-label mb-0">Tổng người dùng</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-6 col-lg-3">
                <div class="stat-card projects">
                    <i class="fas fa-project-diagram stat-icon"></i>
                    <div>
                        <h3 class="stat-number mb-0"><?php echo number_format($stats['projects']); ?></h3>
                        <p class="stat-label mb-0">Tổng dự án</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-6 col-lg-3">
                <div class="stat-card applications">
                    <i class="fas fa-file-alt stat-icon"></i>
                    <div>
                        <h3 class="stat-number mb-0"><?php echo number_format($stats['applications']); ?></h3>
                        <p class="stat-label mb-0">Đơn ứng tuyển</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-6 col-lg-3">
                <div class="stat-card contacts">
                    <i class="fas fa-envelope stat-icon"></i>
                    <div>
                        <h3 class="stat-number mb-0"><?php echo number_format($stats['contacts']); ?></h3>
                        <p class="stat-label mb-0">Tin nhắn liên hệ</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="<?php echo $is_admin ? 'col-12 col-xl-6' : 'col-12'; ?>">
                <div class="card dashboard-table-card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 px-3">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-project-diagram text-primary me-2"></i>Dự án gần đây</h6>
                        <a href="projects.php" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" style="font-size: 11px;">Xem tất cả <i class="fas fa-arrow-right ms-1 small"></i></a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table dashboard-table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 46%;">Tiêu đề</th>
                                        <th scope="col" style="width: 29%;">Khách hàng</th>
                                        <th scope="col" class="text-center" style="width: 25%;">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_projects->num_rows > 0): ?>
                                        <?php while($project = $recent_projects->fetch_assoc()): ?>
                                            <tr>
                                                <td data-label="Tiêu đề">
                                                    <span class="fw-semibold text-dark">
                                                        <?php echo htmlspecialchars($project['title']); ?>
                                                    </span>
                                                </td>
                                                <td data-label="Khách hàng">
                                                    <span class="text-muted">
                                                        <?php echo !empty($project['client']) ? htmlspecialchars($project['client']) : '<em class="text-muted small">Chưa có</em>'; ?>
                                                    </span>
                                                </td>
                                                <td data-label="Trạng thái" class="text-center">
                                                    <?php 
                                                     $badge_class = ['draft' => 'warning text-dark', 'pending' => 'info text-dark', 'published' => 'success'];
                                                     $status_text = ['draft' => 'Nháp', 'pending' => 'Chờ duyệt', 'published' => 'Đã xuất bản'];
                                                     ?>
                                                    <span class="badge rounded-pill bg-<?php echo $badge_class[$project['status']] ?? 'secondary'; ?>">
                                                        <?php echo $status_text[$project['status']] ?? $project['status']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                <i class="fas fa-folder-open fa-2x mb-2 d-block text-secondary opacity-50"></i>
                                                Chưa có dự án nào
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($is_admin): ?>
            <div class="col-12 col-xl-6">
                <div class="card dashboard-table-card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 px-3">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-history text-primary me-2"></i>Hoạt động gần đây</h6>
                        <a href="activity_logs.php" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" style="font-size: 11px;">Xem tất cả <i class="fas fa-arrow-right ms-1 small"></i></a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table dashboard-table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 34%;">Người dùng</th>
                                        <th scope="col" style="width: 38%;">Hành động</th>
                                        <th scope="col" class="text-end" style="width: 28%;">Thời gian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_logs->num_rows > 0): ?>
                                        <?php while($log = $recent_logs->fetch_assoc()): ?>
                                            <tr>
                                                <td data-label="Người dùng">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-user-circle text-secondary me-1 flex-shrink-0" style="font-size: 0.95rem;"></i>
                                                        <span class="fw-semibold text-dark">
                                                             <?php echo htmlspecialchars($log['fullname'] ?? $log['username'] ?? 'N/A'); ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td data-label="Hành động">
                                                    <span class="text-secondary">
                                                        <?php echo htmlspecialchars($log['action']); ?>
                                                    </span>
                                                </td>
                                                <td data-label="Thời gian" class="text-end">
                                                    <span class="text-muted small">
                                                        <i class="far fa-clock me-1"></i>
                                                        <?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                <i class="fas fa-history fa-2x mb-2 d-block text-secondary opacity-50"></i>
                                                Chưa có hoạt động nào
                                            </td>
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