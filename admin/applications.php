<?php
require_once 'auth_check.php';
$pageTitle = 'Đơn Ứng Tuyển';

$applications = $conn->query("SELECT * FROM applications ORDER BY created_at DESC");
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
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <h1 class="mb-4"><i class="fas fa-file-alt"></i> Đơn Ứng Tuyển</h1>
        
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="min-width: 50px;">ID</th>
                                <th style="min-width: 120px;">Họ tên</th>
                                <th style="min-width: 140px;">Email</th>
                                <th style="min-width: 100px;">Điện thoại</th>
                                <th style="min-width: 120px;">Vị trí</th>
                                <th style="min-width: 90px;">CV File</th>
                                <th style="min-width: 105px;">Ngày nộp</th>
                                <th class="text-end pe-3" style="min-width: 100px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($applications->num_rows > 0): ?>
                                <?php while($app = $applications->fetch_assoc()): ?>
                                    <tr>
                                        <td data-label="Mã ID"><strong>#<?php echo $app['id']; ?></strong></td>
                                        <td data-label="Họ tên"><div class="fw-semibold"><?php echo htmlspecialchars($app['fullname']); ?></div></td>
                                        <td data-label="Email"><?php echo htmlspecialchars($app['email']); ?></td>
                                        <td data-label="Điện thoại"><?php echo htmlspecialchars($app['phone']); ?></td>
                                        <td data-label="Vị trí"><div><?php echo htmlspecialchars($app['position']); ?></div></td>
                                        <td data-label="CV File">
                                            <?php if ($app['cv_file']): ?>
                                                <a href="../<?php echo htmlspecialchars($app['cv_file']); ?>" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="fas fa-download"></i> Xem CV
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Ngày nộp"><?php echo date('d/m/Y H:i', strtotime($app['created_at'])); ?></td>
                                        <td data-label="Thao tác" class="text-end">
                                            <a href="view_application.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> Xem chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Chưa có đơn ứng tuyển nào</td>
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