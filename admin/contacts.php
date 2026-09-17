<?php
require_once 'auth_check.php';
$pageTitle = 'Liên Hệ';

// Xử lý cập nhật trạng thái tư vấn
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'], $_POST['id'])) {
    $id = intval($_POST['id']);
    $status = ($_POST['status'] === 'replied') ? 'replied' : 'new';
    try {
        $stmt = $conn->prepare("UPDATE contact SET status=? WHERE id=?");
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
        $stmt->close();
        $message = 'Cập nhật trạng thái thành công!';
    } catch (Exception $e) {
        $message = 'Lỗi: ' . $e->getMessage();
    }
}
$contacts = $conn->query("SELECT * FROM contact ORDER BY created_at DESC");
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
        <h1 class="mb-4"><i class="fas fa-envelope"></i> Tin Nhắn Liên Hệ</h1>
        <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
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
                                <th style="min-width: 110px;">Chủ đề</th>
                                <th style="min-width: 140px;">Tin nhắn</th>
                                <th style="min-width: 105px;">Ngày gửi</th>
                                <th style="min-width: 110px;">Trạng thái</th>
                                <th class="text-end pe-3" style="min-width: 70px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($contacts->num_rows > 0): ?>
                                <?php while($contact = $contacts->fetch_assoc()): ?>
                                    <tr>
                                        <td data-label="Mã ID"><strong>#<?php echo $contact['id']; ?></strong></td>
                                        <td data-label="Họ tên"><?php echo htmlspecialchars($contact['name']); ?></td>
                                        <td data-label="Email"><?php echo htmlspecialchars($contact['email']); ?></td>
                                        <td data-label="Điện thoại"><?php echo htmlspecialchars($contact['phone']); ?></td>
                                        <td data-label="Chủ đề"><div class="fw-semibold"><?php echo htmlspecialchars($contact['subject'] ?? ''); ?></div></td>
                                        <td data-label="Tin nhắn"><div class="text-secondary text-expandable" title="Bấm để xem đầy đủ"><?php echo htmlspecialchars($contact['message']); ?></div></td>
                                        <td data-label="Ngày gửi"><?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></td>
                                        <td data-label="Trạng thái">
                                            <form method="post" style="display:inline-block; margin:0;">
                                                <input type="hidden" name="id" value="<?php echo $contact['id']; ?>">
                                                <input type="hidden" name="update_status" value="1">
                                                <select name="status" class="form-select form-select-sm <?php echo (($contact['status'] ?? 'new')=='replied') ? 'border-success text-success fw-semibold' : 'text-secondary'; ?>" style="font-size: 0.85rem; padding: 4px 24px 4px 8px;" onchange="this.form.submit()">
                                                    <option value="new" <?php if(($contact['status'] ?? 'new')=='new') echo 'selected'; ?>>Chưa tư vấn</option>
                                                    <option value="replied" <?php if(($contact['status'] ?? '')=='replied') echo 'selected'; ?>>Đã tư vấn</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td data-label="Thao tác" class="text-end">
                                            <a href="view_contact.php?id=<?php echo $contact['id']; ?>" class="btn btn-sm btn-info" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Chưa có tin nhắn nào</td>
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