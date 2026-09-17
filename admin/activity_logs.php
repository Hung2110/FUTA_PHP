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
    <style>
        body { background: #f7f9fc; }
        .page-header {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .page-header h1 { font-weight: 700; font-size: 1.75rem; margin: 0; color: #1f2a37; }
        .page-header p { color: #6b7280; margin: 5px 0 0; font-size: 14px; }
        .table-responsive {
            max-height: 70vh; /* Giới hạn chiều cao bảng khoảng 70% màn hình */
            overflow-y: auto;  /* Cho phép cuộn dọc */
        }
        .table thead th {
            position: sticky;
            top: 0;
            background: #f8f9fa; /* Màu nền header để không bị trong suốt khi cuộn */
            z-index: 1;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 8px 10px;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table tbody td {
            padding: 8px 10px;
            font-size: 0.82rem;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-history text-primary me-2"></i>Nhật Ký Hoạt Động</h1>
            <p class="mb-0">Theo dõi lịch sử thao tác và đăng nhập của quản trị viên.</p>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="min-width: 50px;">ID</th>
                                <th style="min-width: 120px;">Người dùng</th>
                                <th style="min-width: 160px;">Hành động</th>
                                <th style="min-width: 100px;">Module</th>
                                <th style="min-width: 100px;">IP</th>
                                <th class="text-end pe-3" style="min-width: 130px;">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($logs->num_rows > 0): ?>
                                <?php while($log = $logs->fetch_assoc()): ?>
                                    <tr>
                                        <td data-label="Mã ID" class="ps-3"><strong>#<?php echo $log['id']; ?></strong></td>
                                        <td data-label="Người dùng"><?php echo htmlspecialchars($log['fullname'] ?? $log['username'] ?? 'N/A'); ?></td>
                                        <td data-label="Hành động"><div style="line-height: 1.35;"><?php echo htmlspecialchars($log['action']); ?></div></td>
                                        <td data-label="Module"><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($log['module']); ?></span></td>
                                        <td data-label="Địa chỉ IP"><code><?php echo htmlspecialchars($log['ip']); ?></code></td>
                                        <td data-label="Thời gian" class="text-end pe-3 text-nowrap"><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
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
