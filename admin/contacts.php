<?php
require_once 'auth_check.php';

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
    <title>Liên Hệ </title>
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
        <h1 class="mb-4"><i class="fas fa-envelope"></i> Tin Nhắn Liên Hệ</h1>
        <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
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
                                <th>Website</th>
                                <th>Tin nhắn</th>
                                <th>Ngày gửi</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($contacts->num_rows > 0): ?>
                                <?php while($contact = $contacts->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $contact['id']; ?></td>
                                        <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                        <td><?php echo htmlspecialchars($contact['email']); ?></td>
                                        <td><?php echo htmlspecialchars($contact['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($contact['subject'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars(mb_substr($contact['message'], 0, 50)) . '...'; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></td>
                                        <td>
                                            <form method="post" style="display:inline-block">
                                                <input type="hidden" name="id" value="<?php echo $contact['id']; ?>">
                                                <input type="hidden" name="update_status" value="1">
                                                <select name="status" class="form-select form-select-sm" style="width:auto;display:inline-block;vertical-align:middle" onchange="this.form.submit()">
                                                    <option value="new" <?php if(($contact['status'] ?? 'new')=='new') echo 'selected'; ?>>Chưa tư vấn</option>
                                                    <option value="replied" <?php if(($contact['status'] ?? '')=='replied') echo 'selected'; ?>>Đã tư vấn</option>
                                                </select>
                                            </form>
                                            <?php if(($contact['status'] ?? 'new')=='replied'): ?>
                                                <span class="badge bg-success ms-1">Đã tư vấn</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary ms-1">Chưa tư vấn</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
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