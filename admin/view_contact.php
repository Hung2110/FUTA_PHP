<?php
require_once 'auth_check.php';
$pageTitle = 'Chi Tiết Liên Hệ';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: contacts.php');
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM contact WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: contacts.php?error=notfound');
    exit();
}

$contact = $result->fetch_assoc();
$stmt->close();

$status_text = ['pending' => 'Chưa tư vấn', 'done' => 'Đã tư vấn'];
$status_colors = ['pending' => 'secondary', 'done' => 'success'];
// Xử lý cập nhật trạng thái tư vấn nếu có
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'], $_POST['status'])) {
    $new_status = ($_POST['status'] === 'replied') ? 'replied' : 'new';
    $update_stmt = $conn->prepare("UPDATE contact SET status=? WHERE id=?");
    $update_stmt->bind_param('si', $new_status, $id);
    $update_stmt->execute();
    $update_stmt->close();
    $contact['status'] = $new_status;
    $status_label = ($new_status === 'replied') ? 'Đã tư vấn' : 'Chưa tư vấn';
    log_activity($conn, "Cập nhật trạng thái liên hệ #$id sang $status_label", 'contacts');
    $message = 'Cập nhật trạng thái thành công!';
}

$status_text = ['new' => 'Chưa tư vấn', 'replied' => 'Đã tư vấn'];
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
    <link rel="stylesheet" href="css/view_contact.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-envelope-open-text text-primary me-2"></i>Chi Tiết Liên Hệ</h1>
                <p class="mb-0">Xem thông tin chi tiết và phản hồi yêu cầu tư vấn của khách hàng.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="contacts.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
                </a>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div class="text-muted small">
                <i class="fas fa-info-circle me-1 text-primary"></i> Chi tiết liên hệ <strong>#<?php echo $contact['id']; ?></strong> &bull; Gửi lúc <?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?>
            </div>
            <div>
                <span class="badge bg-<?php echo ($contact['status'] ?? 'new') === 'replied' ? 'success' : 'warning text-dark'; ?>">
                    <i class="fas fa-<?php echo ($contact['status'] ?? 'new') === 'replied' ? 'check-circle' : 'clock'; ?> me-1"></i>
                    <?php echo ($contact['status'] ?? 'new') === 'replied' ? 'Đã tư vấn' : 'Chưa tư vấn'; ?>
                </span>
            </div>
        </div>
        
        <div class="card profile-card">
            <div class="card-body">
                <h3 class="card-title mb-4">Thông tin từ: <?php echo htmlspecialchars($contact['name']); ?></h3>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>ID:</strong> <span class="value">#<?php echo $contact['id']; ?></span></li>
                    <li class="list-group-item"><strong>Họ và tên:</strong> <span class="value"><?php echo htmlspecialchars($contact['name']); ?></span></li>
                    <li class="list-group-item"><strong>Email:</strong> <span class="value"><a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>"><?php echo htmlspecialchars($contact['email']); ?></a></span></li>
                    <li class="list-group-item"><strong>Số điện thoại:</strong> <span class="value"><a href="tel:<?php echo htmlspecialchars($contact['phone']); ?>"><?php echo htmlspecialchars($contact['phone']); ?></a></span></li>
                    <li class="list-group-item"><strong>Chủ đề:</strong> <span class="value"><?php echo htmlspecialchars($contact['subject'] ?: 'Không có chủ đề'); ?></span></li>
                    <li class="list-group-item"><strong>Ngày gửi:</strong> <span class="value"><?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></span></li>
                    <li class="list-group-item"><strong>Trạng thái:</strong> <span class="value"><span class="badge bg-<?php echo $status_colors[$contact['status'] ?? 'new'] ?? 'secondary'; ?>"><?php echo $status_text[$contact['status'] ?? 'new'] ?? ucfirst($contact['status'] ?? 'new'); ?></span></span></li>
                    <li class="list-group-item">
                        <strong>Trạng thái:</strong> 
                        <span class="value">
                            <form method="post" class="d-inline-flex align-items-center gap-2 m-0">
                                <input type="hidden" name="update_status" value="1">
                                <select name="status" class="status-badge-select <?php echo (($contact['status'] ?? 'new') === 'replied') ? 'status-replied' : 'status-new'; ?>" onchange="this.form.submit()" title="Nhấp để cập nhật trạng thái">
                                    <option value="new" <?php if(($contact['status'] ?? 'new') === 'new') echo 'selected'; ?>>Chưa tư vấn</option>
                                    <option value="replied" <?php if(($contact['status'] ?? '') === 'replied') echo 'selected'; ?>>Đã tư vấn</option>
                                </select>
                                <span class="text-muted small"><i class="fas fa-sync-alt ms-1"></i> (Nhấp để đổi nhanh)</span>
                            </form>
                        </span>
                    </li>
                    <li class="list-group-item d-block"><strong>Nội dung tin nhắn:</strong> <div class="value mt-2 message-box"><?php echo nl2br(htmlspecialchars($contact['message'])); ?></div></li>
                </ul>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>