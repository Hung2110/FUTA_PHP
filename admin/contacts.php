<?php
require_once 'auth_check.php';
$pageTitle = 'Liên Hệ';

// Xử lý cập nhật trạng thái tư vấn
$message = '';
$message_type = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'], $_POST['id'])) {
    $id = intval($_POST['id']);
    $status = ($_POST['status'] === 'replied') ? 'replied' : 'new';
    try {
        $stmt = $conn->prepare("UPDATE contact SET status=? WHERE id=?");
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
        $stmt->close();
        $status_label = ($status === 'replied') ? 'Đã phản hồi' : 'Mới (Chưa xử lý)';
        log_activity($conn, "Cập nhật liên hệ #$id sang trạng thái: $status_label", 'contacts');
        $message = 'Cập nhật trạng thái thành công!';
    } catch (Exception $e) {
        $message = 'Lỗi: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Lọc theo trạng thái
$filter = isset($_GET['filter']) && in_array($_GET['filter'], ['new', 'replied']) ? $_GET['filter'] : 'all';
if (isset($_POST['current_filter']) && in_array($_POST['current_filter'], ['new', 'replied'])) {
    $filter = $_POST['current_filter'];
}

$query = "SELECT * FROM contact";
if ($filter !== 'all') {
    $query .= " WHERE status = '" . $conn->real_escape_string($filter) . "'";
}
$query .= " ORDER BY created_at DESC";
$contacts = $conn->query($query);

// Đếm số lượng theo trạng thái
$counts_res = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as count_new,
    SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as count_replied
    FROM contact");
$counts = $counts_res ? $counts_res->fetch_assoc() : ['total' => 0, 'count_new' => 0, 'count_replied' => 0];
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
    <link rel="stylesheet" href="css/contacts.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-envelope text-primary me-2"></i>Tin Nhắn Liên Hệ</h1>
                <p class="mb-0">Quản lý và phản hồi các yêu cầu tư vấn quảng cáo từ khách hàng.</p>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 table-filter-toolbar">
            <div class="btn-group filter-btn-group" role="group">
                <a href="contacts.php" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                    Tất cả <span class="badge <?php echo $filter === 'all' ? 'bg-white text-primary' : 'bg-secondary'; ?> ms-1"><?php echo (int)($counts['total'] ?? 0); ?></span>
                </a>
                <a href="contacts.php?filter=new" class="btn <?php echo $filter === 'new' ? 'btn-warning text-dark fw-semibold' : 'btn-outline-secondary'; ?>">
                    <i class="far fa-clock me-1"></i>Chưa tư vấn <span class="badge <?php echo $filter === 'new' ? 'bg-dark text-white' : 'bg-secondary'; ?> ms-1"><?php echo (int)($counts['count_new'] ?? 0); ?></span>
                </a>
                <a href="contacts.php?filter=replied" class="btn <?php echo $filter === 'replied' ? 'btn-success fw-semibold' : 'btn-outline-secondary'; ?>">
                    <i class="far fa-check-circle me-1"></i>Đã tư vấn <span class="badge <?php echo $filter === 'replied' ? 'bg-white text-success' : 'bg-secondary'; ?> ms-1"><?php echo (int)($counts['count_replied'] ?? 0); ?></span>
                </a>
            </div>
            <div class="text-muted small filter-count-info">
                <i class="fas fa-envelope me-1 text-primary"></i> Tổng số: <strong><?php echo (int)($counts['total'] ?? 0); ?></strong> tin nhắn
            </div>
        </div>

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
                                <th class="text-center" style="min-width: 145px; width: 145px;">Trạng thái</th>
                                <th class="text-end pe-3" style="min-width: 70px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($contacts && $contacts->num_rows > 0): ?>
                                <?php while($contact = $contacts->fetch_assoc()): ?>
                                    <tr>
                                        <td data-label="Mã ID" class="ps-3"><strong>#<?php echo $contact['id']; ?></strong></td>
                                        <td data-label="Họ tên"><span class="fw-semibold text-dark"><?php echo htmlspecialchars($contact['name']); ?></span></td>
                                        <td data-label="Email"><a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" class="text-decoration-none"><?php echo htmlspecialchars($contact['email']); ?></a></td>
                                        <td data-label="Điện thoại"><a href="tel:<?php echo htmlspecialchars($contact['phone']); ?>" class="text-decoration-none text-secondary"><?php echo htmlspecialchars($contact['phone']); ?></a></td>
                                        <td data-label="Chủ đề"><div class="fw-medium text-dark"><?php echo htmlspecialchars($contact['subject'] ?? ''); ?></div></td>
                                        <td data-label="Tin nhắn"><div class="text-secondary text-expandable" title="Bấm để xem đầy đủ"><?php echo htmlspecialchars($contact['message']); ?></div></td>
                                        <td data-label="Ngày gửi" class="text-nowrap text-muted" style="font-size: 13px;"><?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></td>
                                        <td data-label="Trạng thái" class="text-center">
                                            <form method="post" class="d-inline-flex justify-content-center m-0">
                                                <input type="hidden" name="id" value="<?php echo $contact['id']; ?>">
                                                <input type="hidden" name="update_status" value="1">
                                                <?php if ($filter !== 'all'): ?>
                                                    <input type="hidden" name="current_filter" value="<?php echo htmlspecialchars($filter); ?>">
                                                <?php endif; ?>
                                                <select name="status" class="status-badge-select <?php echo (($contact['status'] ?? 'new') === 'replied') ? 'status-replied' : 'status-new'; ?>" onchange="this.form.submit()" title="Nhấp để cập nhật trạng thái">
                                                    <option value="new" <?php if(($contact['status'] ?? 'new') === 'new') echo 'selected'; ?>>Chưa tư vấn</option>
                                                    <option value="replied" <?php if(($contact['status'] ?? '') === 'replied') echo 'selected'; ?>>Đã tư vấn</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td data-label="Thao tác" class="text-end pe-3">
                                            <a href="view_contact.php?id=<?php echo $contact['id']; ?>" class="btn btn-sm btn-info text-white" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Chưa có tin nhắn nào<?php echo $filter !== 'all' ? ' phù hợp với bộ lọc' : ''; ?></td>
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