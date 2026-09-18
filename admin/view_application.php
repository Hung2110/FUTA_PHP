<?php
require_once 'auth_check.php';
$pageTitle = 'Chi Tiết Đơn Ứng Tuyển';

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
log_activity($conn, "Xem hồ sơ ứng viên #" . $app['id'] . " (" . $app['fullname'] . ")", 'applications');
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
    <link rel="stylesheet" href="css/view_application.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-user-tie text-primary me-2"></i>Hồ Sơ Ứng Viên</h1>
                <p class="mb-0">Xem thông tin chi tiết hồ sơ ứng viên và file CV đính kèm.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="applications.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
                </a>
            </div>
        </div>
        
        <div class="row">
            <!-- Cột thông tin -->
            <div class="col-lg-4 mb-4">
                <div class="card profile-card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-info-circle text-primary me-2"></i>Thông tin chung</h5>
                        <span class="badge bg-secondary">#<?php echo $app['id']; ?></span>
                    </div>
                    <div class="card-body">
                        <span class="info-label"><i class="fas fa-user me-2 text-muted"></i>Họ và tên</span>
                        <div class="info-value fw-bold"><?php echo htmlspecialchars($app['fullname']); ?></div>

                        <span class="info-label"><i class="fas fa-envelope me-2 text-muted"></i>Email</span>
                        <div class="info-value"><a href="mailto:<?php echo htmlspecialchars($app['email']); ?>"><?php echo htmlspecialchars($app['email']); ?></a></div>

                        <span class="info-label"><i class="fas fa-phone-alt me-2 text-muted"></i>Số điện thoại</span>
                        <div class="info-value"><a href="tel:<?php echo htmlspecialchars($app['phone']); ?>"><?php echo htmlspecialchars($app['phone']); ?></a></div>

                        <span class="info-label"><i class="fas fa-briefcase me-2 text-muted"></i>Vị trí ứng tuyển</span>
                        <div class="info-value text-primary fw-bold"><?php echo htmlspecialchars($app['position']); ?></div>

                        <span class="info-label"><i class="far fa-calendar-alt me-2 text-muted"></i>Ngày nộp</span>
                        <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($app['created_at'])); ?></div>

                        <?php if (!empty($app['message'])): ?>
                            <span class="info-label"><i class="fas fa-comment-dots me-2 text-muted"></i>Thông điệp bổ sung</span>
                            <div class="message-box"><?php echo nl2br(htmlspecialchars($app['message'])); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Cột hiển thị CV -->
            <div class="col-lg-8 mb-4">
                <div class="card profile-card h-100 d-flex flex-column">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-file-pdf text-danger me-2"></i>Chi tiết CV</h5>
                        <?php if ($app['cv_file']): ?>
                            <div>
                                <a href="../<?php echo htmlspecialchars($app['cv_file']); ?>" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                                    <i class="fas fa-external-link-alt me-1"></i>Mở tab mới
                                </a>
                                <a href="../<?php echo htmlspecialchars($app['cv_file']); ?>" download class="btn btn-sm btn-primary">
                                    <i class="fas fa-download me-1"></i>Tải xuống
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-0 flex-grow-1">
                        <?php if ($app['cv_file']): ?>
                            <?php $ext = strtolower(pathinfo($app['cv_file'], PATHINFO_EXTENSION)); ?>
                            <?php if ($ext === 'pdf'): ?>
                                <div class="cv-container">
                                    <iframe src="../<?php echo htmlspecialchars($app['cv_file']); ?>" class="cv-iframe"></iframe>
                                </div>
                            <?php elseif (in_array($ext, ['doc', 'docx'])): ?>
                                <div class="p-5 text-center d-flex flex-column justify-content-center h-100">
                                    <i class="fas fa-file-word fa-5x text-primary mb-4"></i>
                                    <h4 class="text-dark">File định dạng Word</h4>
                                    <p class="text-muted mb-4">Trình duyệt không hỗ trợ xem trực tiếp định dạng Word.<br>Vui lòng tải xuống thiết bị để xem chi tiết.</p>
                                    <div>
                                        <a href="../<?php echo htmlspecialchars($app['cv_file']); ?>" download class="btn btn-primary px-4 py-2">
                                            <i class="fas fa-download me-2"></i>Tải xuống CV
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="p-5 text-center d-flex flex-column justify-content-center h-100 text-muted">
                                <i class="fas fa-file-excel fa-5x mb-4 opacity-50"></i>
                                <h5>Không có file CV đính kèm</h5>
                                <p>Ứng viên không đính kèm file CV trong đơn ứng tuyển này.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>