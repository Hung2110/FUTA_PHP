<?php
require_once 'auth_check.php';
$pageTitle = 'Quản Lý Carousel';

// Hàm ghi log hoạt động (tái sử dụng từ các file admin khác)
function log_activity($conn, $action, $module) {
    if (isset($_SESSION['admin_id'])) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, module, ip) VALUES (?, ?, ?, ?)");
        $log_stmt->bind_param("isss", $_SESSION['admin_id'], $action, $module, $ip);
        $log_stmt->execute();
    }
}

/**
 * Xử lý upload file với kiểm tra bảo mật.
 */
function handle_upload($file_key, $current_path = '', $allowed_types = [], $max_size = 5000000) { // 5MB
    global $message, $message_type;

    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES[$file_key];

        if ($file['size'] > $max_size) {
            $message = "Lỗi: File '{$file['name']}' quá lớn. Kích thước tối đa là " . ($max_size / 1024 / 1024) . "MB.";
            $message_type = 'danger';
            return null;
        }

        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($file_info, $file['tmp_name']);
        finfo_close($file_info);    

        if (!empty($allowed_types) && !in_array($mime_type, $allowed_types)) {
            $message = "Lỗi: Định dạng file '{$file['name']}' không được phép.";
            $message_type = 'danger';
            return null;
        }

        $upload_dir = '../uploads/carousel/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        if ($current_path && file_exists('../' . $current_path) && strpos($current_path, 'billboard.jpg') === false) {
            unlink('../' . $current_path);
        }

        $file_name = uniqid() . '-' . basename($file['name']);
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            return 'uploads/carousel/' . $file_name;
        } else {
            $message = "Lỗi khi di chuyển file '{$file['name']}'.";
            $message_type = 'danger';
            return null;
        }
    }
    return $current_path;
}

// --- Xử lý POST request ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = intval($_POST['id'] ?? 0);
    $sort_order = intval($_POST['sort_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    $current_image_path = $_POST['current_image_path'] ?? '';

    $image_path = handle_upload('image_file', $current_image_path, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

    // Nếu upload file thất bại, gán thông báo lỗi và dừng lại
    if ($image_path === null) {
        $post_message = $message; // $message được gán từ hàm handle_upload
        $post_message_type = $message_type;
    } else {
        if ($action == 'add') {
            $stmt = $conn->prepare("INSERT INTO carousel_slides (image_path, sort_order, status) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $image_path, $sort_order, $status);
            $log_action = "Thêm slide carousel mới";
            $success_param = "added";
        } elseif ($action == 'edit') {
            $stmt = $conn->prepare("UPDATE carousel_slides SET image_path=?, sort_order=?, status=? WHERE id=?");
            $stmt->bind_param("sisi", $image_path, $sort_order, $status, $id);
            $log_action = "Cập nhật slide carousel ID: " . $id;
            $success_param = "updated";
        } elseif ($action == 'delete') {
            $stmt_select = $conn->prepare("SELECT image_path FROM carousel_slides WHERE id = ? LIMIT 1");
            $stmt_select->bind_param("i", $id);
            $stmt_select->execute();
            $result = $stmt_select->get_result();
            $slide = $result->fetch_assoc();
            if ($slide && !empty($slide['image_path']) && file_exists('../' . $slide['image_path'])) {
                @unlink('../' . $slide['image_path']);
            }
            $stmt_select->close();

            $stmt = $conn->prepare("DELETE FROM carousel_slides WHERE id=?");
            $stmt->bind_param("i", $id);
            $log_action = "Xóa slide carousel ID: " . $id;
            $success_param = "deleted";
        }

        if (isset($stmt) && $stmt->execute()) {
            if ($action === 'add') {
                $log_action .= " (ID: " . $conn->insert_id . ")";
            }
            if ($action === 'add') {
                $log_action .= " (ID: " . $conn->insert_id . ")";
            }
            log_activity($conn, $log_action, "Carousel Slides");
            // Chuyển hướng để tránh submit lại form
            $redirect_url = "carousel_slides.php?success=" . $success_param;
            if ($action === 'edit') {
                $redirect_url .= "&edit=" . $id; // Giữ lại modal edit sau khi cập nhật
            }
            header("Location: " . $redirect_url);
            exit();
        } else {
            $post_message = "Lỗi khi thực hiện thao tác: " . ($stmt->error ?? 'Unknown error');
            $post_message_type = 'danger';
        }
        if(isset($stmt)) $stmt->close();
    }
}

$message = '';
// --- Lấy thông báo từ session (sau khi redirect) ---
$message_type = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'added') $message = 'Thêm slide thành công!';
    if ($_GET['success'] === 'updated') $message = 'Cập nhật slide thành công!';
    if ($_GET['success'] === 'deleted') $message = 'Xóa slide thành công!';
    $message_type = 'success';
}
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'upload') $message = 'Lỗi khi tải file lên.';
    $message_type = 'danger';
}

// Lọc theo trạng thái
$filter = isset($_GET['filter']) && in_array($_GET['filter'], ['active', 'inactive']) ? $_GET['filter'] : 'all';

// Thống kê số lượng slide
$status_counts_result = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as count_active,
        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as count_inactive
    FROM carousel_slides
");
$status_counts = $status_counts_result ? $status_counts_result->fetch_assoc() : ['total' => 0, 'count_active' => 0, 'count_inactive' => 0];

// Lấy danh sách slide carousel
$slides_sql = "SELECT * FROM carousel_slides";
if ($filter !== 'all') {
    $slides_sql .= " WHERE status = '" . $conn->real_escape_string($filter) . "'";
}
$slides_sql .= " ORDER BY sort_order ASC, created_at DESC";
$slides_query = $conn->query($slides_sql);
$slides = [];
if ($slides_query) {
    while($row = $slides_query->fetch_assoc()) {
        $slides[] = $row;
    }
}

// Lấy slide để chỉnh sửa nếu có tham số 'edit' trên URL
$edit_slide = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM carousel_slides WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_slide = $result->fetch_assoc();
    }
    $stmt->close();
}

// Nếu có thông báo lỗi từ quá trình xử lý POST (không redirect)
if (isset($post_message)) {
    $message = $post_message;
    $message_type = $post_message_type;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | FUTA ADVERTISING' : 'FUTA ADVERTISING'; ?></title>
    <link rel="icon" href="../assets/images/logo/futa.png" type="image/png">
     <!-- Favicon (Logo trên tab trình duyệt) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/carousel_slides.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-images text-primary me-2"></i>Quản Lý Carousel</h1>
                <p class="mb-0">Quản lý hình ảnh và thứ tự hiển thị banner/slide trên trang chủ.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#slideModal" id="addSlideBtn">
                    <i class="fas fa-plus me-1"></i> Thêm slide mới
                </button>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 table-filter-toolbar">
            <div class="btn-group filter-btn-group" role="group">
                <a href="carousel_slides.php" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                    Tất cả <span class="badge <?php echo $filter === 'all' ? 'bg-white text-primary' : 'bg-secondary'; ?> ms-1"><?php echo (int)($status_counts['total'] ?? 0); ?></span>
                </a>
                <a href="carousel_slides.php?filter=active" class="btn <?php echo $filter === 'active' ? 'btn-success fw-semibold' : 'btn-outline-secondary'; ?>">
                    <i class="far fa-check-circle me-1"></i>Hoạt động <span class="badge <?php echo $filter === 'active' ? 'bg-white text-success' : 'bg-secondary'; ?> ms-1"><?php echo (int)($status_counts['count_active'] ?? 0); ?></span>
                </a>
                <a href="carousel_slides.php?filter=inactive" class="btn <?php echo $filter === 'inactive' ? 'btn-secondary text-white fw-semibold' : 'btn-outline-secondary'; ?>">
                    <i class="far fa-eye-slash me-1"></i>Tạm ẩn <span class="badge <?php echo $filter === 'inactive' ? 'bg-dark text-white' : 'bg-secondary'; ?> ms-1"><?php echo (int)($status_counts['count_inactive'] ?? 0); ?></span>
                </a>
            </div>
            <div class="text-muted small filter-count-info">
                <i class="fas fa-images me-1 text-primary"></i> Tổng số: <strong><?php echo (int)($status_counts['total'] ?? 0); ?></strong> slide banner
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3" style="min-width: 50px;">ID</th>
                                <th style="min-width: 90px;">Ảnh</th>
                                <th style="min-width: 65px;">Thứ tự</th>
                                <th class="text-center" style="min-width: 95px;">Trạng thái</th>
                                <th style="min-width: 95px;">Ngày đăng</th>
                                <th class="text-end pe-3" style="min-width: 90px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($slides)): ?>
                                <?php foreach($slides as $slide): ?>
                                <tr>
                                    <td data-label="Mã ID"><?php echo $slide['id']; ?></td>
                                    <td data-label="Hình ảnh">
                                        <img src="../<?php echo htmlspecialchars($slide['image_path'] ?: 'assets/images/service/billboard.jpg'); ?>" alt="Slide image" class="slide-image">
                                    </td>
                                    <td data-label="Thứ tự"><?php echo $slide['sort_order']; ?></td>
                                    <td data-label="Trạng thái" class="text-center">
                                        <span class="badge bg-<?php echo $slide['status']=='active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($slide['status']); ?>
                                        </span>
                                    </td>
                                    <td data-label="Ngày đăng"><?php echo date('d/m/Y', strtotime($slide['created_at'])); ?></td>
                                    <td data-label="Thao tác" class="text-end" style="width: 120px;">
                                        <button type="button" class="btn btn-sm btn-warning action-btn btn-edit" title="Sửa"
                                            data-bs-toggle="modal" data-bs-target="#slideModal"
                                            data-id="<?php echo $slide['id']; ?>"
                                            data-sort_order="<?php echo $slide['sort_order']; ?>"
                                            data-status="<?php echo $slide['status']; ?>"
                                            data-image_path="<?php echo htmlspecialchars($slide['image_path']); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa slide này?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $slide['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger action-btn" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted p-5">Chưa có slide nào</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Thêm/Sửa Slide -->
    <div class="modal fade" id="slideModal" tabindex="-1" aria-labelledby="slideModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="slideForm" method="POST" enctype="multipart/form-data" action="carousel_slides.php<?php echo $edit_slide ? '?edit=' . $edit_slide['id'] : ''; ?>">
                    <div class="modal-header">
                        <h5 class="modal-title" id="slideModalLabel">Thêm slide mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <input type="hidden" name="id" id="slideId">
                        <input type="hidden" name="current_image_path" id="currentImagePath">

                        <div class="mb-3">
                            <label for="image_file" class="form-label">Ảnh Slide (JPG, PNG, GIF, WEBP) *</label>
                            <input type="file" class="form-control" id="image_file" name="image_file" accept="image/*">
                            <img id="image_preview" class="preview-img-modal mt-2" style="display: none;" alt="Image Preview">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">Thứ tự sắp xếp</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Trạng thái</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/carousel_slides.js"></script>