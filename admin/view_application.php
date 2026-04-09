<?php
require_once 'auth_check.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: applications.php');
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM applications WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Không tìm thấy đơn ứng tuyển, có thể hiển thị trang lỗi hoặc quay về danh sách
    header('Location: applications.php');
    exit();
}

$app = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Đơn Ứng Tuyển</title>
    <link rel="icon" href="/FUTA_PHP/assets/images/logo/futa.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background: #f7f9fc; }
        .profile-card .card-body { padding: 2rem; }
        .profile-card .list-group-item { border: none; padding: .75rem 0; }
        .profile-card .list-group-item strong { min-width: 150px; display: inline-block; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0"><i class="fas fa-user-tie"></i> Hồ Sơ Ứng Viên</h1>
            <a href="applications.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại danh sách</a>
        </div>
        
        <div class="card profile-card">
            <div class="card-body">
                <h3 class="card-title mb-4">Thông tin chi tiết</h3>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>ID:</strong> <?php echo $app['id']; ?></li>
                    <li class="list-group-item"><strong>Họ và tên:</strong> <?php echo htmlspecialchars($app['fullname']); ?></li>
                    <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($app['email']); ?></li>
                    <li class="list-group-item"><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($app['phone']); ?></li>
                    <li class="list-group-item"><strong>Vị trí ứng tuyển:</strong> <?php echo htmlspecialchars($app['position']); ?></li>
                    <li class="list-group-item"><strong>Ngày nộp:</strong> <?php echo date('d/m/Y H:i', strtotime($app['created_at'])); ?></li>
                    <li class="list-group-item"><strong>File CV:</strong>
                        <?php if ($app['cv_file']): ?>
                            <a href="../<?php echo htmlspecialchars($app['cv_file']); ?>" target="_blank" class="btn btn-info">
                                <i class="fas fa-download"></i> Xem CV
                            </a>
                        <?php else: ?>
                            <span class="text-muted">Không có file CV</span>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>