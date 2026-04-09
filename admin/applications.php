<?php
require_once 'auth_check.php';

$applications = $conn->query("SELECT * FROM applications ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn Ứng Tuyển</title>
    <link rel="icon" href="/FUTA_PHP/assets/images/logo/futa.png" type="image/png">
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
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Điện thoại</th>
                                <th>Vị trí</th>
                                <th>CV File</th>
                                <th>Ngày nộp</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($applications->num_rows > 0): ?>
                                <?php while($app = $applications->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $app['id']; ?></td>
                                        <td><?php echo htmlspecialchars($app['fullname']); ?></td>
                                        <td><?php echo htmlspecialchars($app['email']); ?></td>
                                        <td><?php echo htmlspecialchars($app['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($app['position']); ?></td>
                                        <td>
                                            <?php if ($app['cv_file']): ?>
                                                <a href="../<?php echo htmlspecialchars($app['cv_file']); ?>" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="fas fa-download"></i> Xem CV
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($app['created_at'])); ?></td>
                                        <td>
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