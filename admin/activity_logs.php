<?php
require_once 'auth_check.php';
$pageTitle = 'Nhật Ký Hoạt Động';

$logs = $conn->query("SELECT al.*, u.username, u.fullname FROM activity_logs al 
                     LEFT JOIN users u ON al.user_id = u.id 
                     ORDER BY al.created_at DESC");
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
    <link rel="stylesheet" href="css/activity_logs.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-history text-primary me-2"></i>Nhật Ký Hoạt Động</h1>
                <p class="mb-0">Theo dõi lịch sử thao tác và đăng nhập của quản trị viên.</p>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div class="text-muted small">
                <i class="fas fa-history me-1 text-primary"></i> Tổng số: <strong><?php echo (int)($logs ? $logs->num_rows : 0); ?></strong> bản ghi hoạt động
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 16.666%;">ID</th>
                                <th style="width: 16.666%;">Người dùng</th>
                                <th style="width: 16.666%;">Hành động</th>
                                <th class="text-center" style="width: 16.666%;">Module</th>
                                <th class="text-center" style="width: 16.666%;">Địa chỉ IP</th>
                                <th class="text-center" style="width: 16.666%;">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($logs->num_rows > 0): ?>
                                <?php while($log = $logs->fetch_assoc()): ?>
                                    <tr>
                                        <td data-label="Mã ID" class="text-center fw-semibold text-secondary">#<?php echo $log['id']; ?></td>
                                        <td data-label="Người dùng" class="text-truncate" title="<?php echo htmlspecialchars($log['fullname'] ?? $log['username'] ?? 'N/A'); ?>">
                                            <span class="fw-medium text-dark"><i class="far fa-user me-1 text-muted"></i><?php echo htmlspecialchars($log['fullname'] ?? $log['username'] ?? 'N/A'); ?></span>
                                        </td>
                                        <td data-label="Hành động">
                                            <div style="line-height: 1.4; word-break: break-word;"><?php echo htmlspecialchars($log['action']); ?></div>
                                        </td>
                                        <td data-label="Module" class="text-center">
                                            <span class="badge bg-light text-secondary border px-2 py-1"><?php echo htmlspecialchars($log['module']); ?></span>
                                        </td>
                                        <td data-label="Địa chỉ IP" class="text-center">
                                            <code class="text-dark bg-light px-2 py-1 rounded border" style="font-size: 12px;"><?php echo htmlspecialchars($log['ip']); ?></code>
                                        </td>
                                        <td data-label="Thời gian" class="text-center text-nowrap text-muted" style="font-size: 13px;">
                                            <i class="far fa-clock me-1 text-secondary"></i><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Chưa có hoạt động nào</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
