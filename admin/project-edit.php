<?php
require_once 'auth_check.php'; // Includes db.php and session_start()

// Hàm xử lý upload file
function handle_upload($file_key, $current_path = '', $allowed_types = []) {
    global $message, $message_type;
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES[$file_key];

        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($file_info, $file['tmp_name']);
        finfo_close($file_info);

        if (!empty($allowed_types) && !in_array($mime_type, $allowed_types)) {
            $message = "Lỗi: Định dạng file '{$file['name']}' không được phép.";
            $message_type = 'danger';
            return null;
        }

        $upload_dir = '../uploads/projects/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Xóa file cũ nếu có (trừ khi là ảnh mặc định)
        if ($current_path && file_exists('../' . $current_path) && strpos($current_path, 'billboard.jpg') === false) {
            @unlink('../' . $current_path);
        }

        $safe_name = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $file_name = uniqid() . '-' . $safe_name . '.' . $ext;
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            return 'uploads/projects/' . $file_name;
        }
    }
    // Nếu không có file mới hoặc upload lỗi, giữ lại đường dẫn cũ
    return $current_path;
}

$project_id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : null;
$project = null;
$message = '';
$message_type = '';
$is_new_project = ($project_id === null);
$pageTitle = $is_new_project ? 'Thêm dự án mới' : 'Chỉnh sửa dự án';

// Xử lý khi form được gửi đi (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';

    // Xử lý xóa
    if ($action === 'delete') {
        $project_id_to_delete = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;
        if ($project_id_to_delete > 0) {
            // Lấy thông tin file để xóa
            $stmt_get = $conn->prepare("SELECT preview_image, preview_video FROM projects WHERE id=?");
            $stmt_get->bind_param("i", $project_id_to_delete);
            $stmt_get->execute();
            $res = $stmt_get->get_result();
            if ($row = $res->fetch_assoc()) {
                if ($row['preview_image'] && file_exists('../' . $row['preview_image'])) @unlink('../' . $row['preview_image']);
                if ($row['preview_video'] && file_exists('../' . $row['preview_video'])) @unlink('../' . $row['preview_video']);
            }
            $stmt_get->close();

            $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->bind_param('i', $project_id_to_delete);
            if ($stmt->execute()) {
                header('Location: projects.php?success=deleted');
            } else {
                header('Location: projects.php?error=delete_failed');
            }
            $stmt->close();
            exit;
        }
    }

    // Xử lý thêm/sửa
    $project_id_from_form = isset($_POST['project_id']) && is_numeric($_POST['project_id']) ? intval($_POST['project_id']) : null;

    $title = trim($_POST['title'] ?? '');
    $client = trim($_POST['client'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = trim($_POST['status'] ?? 'draft');
    $current_image = trim($_POST['current_image'] ?? '');
    $current_video = trim($_POST['current_video'] ?? '');

    if (empty($title) || empty($client)) {
        $message = "Tiêu đề và Mô tả ngắn không được để trống.";
        $message_type = 'danger';
    }

    // Kiểm tra kích thước file (Giới hạn 50MB theo cấu hình IIS)
    $max_file_size = 50 * 1024 * 1024;
    if (
        (isset($_FILES['preview_image']) && $_FILES['preview_image']['error'] == UPLOAD_ERR_OK && $_FILES['preview_image']['size'] > $max_file_size) ||
        (isset($_FILES['preview_video']) && $_FILES['preview_video']['error'] == UPLOAD_ERR_OK && $_FILES['preview_video']['size'] > $max_file_size)
    ) {
        $message = "Kích thước file quá lớn. Vui lòng chọn file dưới 50MB.";
        $message_type = 'danger';
    }

    if (empty($message)) {
        // Xử lý upload chỉ khi không có lỗi
        $image_path = handle_upload('preview_image', $current_image, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
        $video_path = handle_upload('preview_video', $current_video, ['video/mp4', 'video/webm']);

        if ($image_path === null || (isset($_FILES['preview_video']) && $_FILES['preview_video']['error'] == UPLOAD_ERR_OK && $video_path === null)) {
            // Lỗi đã được ghi nhận trong $message bên trong hàm handle_upload
        } else {
            // Mới: Tự động chèn ảnh đại diện vào đầu mô tả nếu có
            if (!empty($image_path)) {
                $image_html = '<p><img src="../' . htmlspecialchars($image_path) . '" class="img-fluid rounded"></p>';
                $description = $image_html . $description;
            }

            if ($project_id_from_form) { // Chế độ cập nhật
            $stmt = $conn->prepare(
                "UPDATE projects SET title=?, client=?, description=?, preview_image=?, preview_video=?, status=? WHERE id=?"
            );
            $stmt->bind_param('ssssssi', $title, $client, $description, $image_path, $video_path, $status, $project_id_from_form);
            $redirect_url = "projects.php?success=updated";
        } else { // Chế độ thêm mới
            $created_by = $_SESSION['admin_id'];
            $stmt = $conn->prepare(
                "INSERT INTO projects (title, client, description, preview_image, preview_video, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('ssssssi', $title, $client, $description, $image_path, $video_path, $status, $created_by);
            $redirect_url = "projects.php?success=added";
        }

        if ($stmt->execute()) {
            header("Location: " . $redirect_url);
            exit;
        } else {
            $message = "Lỗi khi lưu dự án: " . $stmt->error;
            $message_type = 'danger';
        }
        $stmt->close();
        }
    }
}

// Nếu không phải là thêm mới, lấy dữ liệu dự án để hiển thị
if (!$is_new_project) {
    $stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $project = $result->fetch_assoc();
    } else {
        header('Location: projects.php?error=notfound');
        exit;
    }
    $stmt->close();
}
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
    <!-- 1. Thêm CSS của Quill.js -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <style>
        body { background: #f7f9fc; }
        .form-control, .form-select { border-radius: 8px; padding: 10px 15px; transition: all 0.2s ease; }
        .form-control:focus, .form-select:focus { box-shadow: 0 0 0 3px rgba(0,123,255,0.1); border-color: #007bff; }
        .card { border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: none; }
        /* Tùy chỉnh chiều cao cho editor */
        .ql-container {
            min-height: 400px;
            font-size: 16px;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-<?php echo $is_new_project ? 'plus-circle' : 'edit'; ?> text-primary"></i> <?php echo $is_new_project ? 'Thêm dự án mới' : 'Chỉnh sửa dự án'; ?>
        </h1>
        <a href="projects.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách
        </a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="project-edit.php<?php echo !$is_new_project ? '?id=' . $project_id : ''; ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($project['preview_image'] ?? ''); ?>">
        <input type="hidden" name="current_video" value="<?php echo htmlspecialchars($project['preview_video'] ?? ''); ?>">
        <?php if (!$is_new_project): ?>
            <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
        <?php endif; ?>

        <div class="row">
            <!-- Cột trái (Nội dung chính) -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-file-alt text-primary me-2"></i>Nội dung dự án</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label for="title" class="form-label fw-semibold">Tiêu đề dự án *</label>
                            <input type="text" class="form-control form-control-lg fw-bold" id="title" name="title" value="<?php echo htmlspecialchars($project['title'] ?? ''); ?>" required placeholder="Nhập tên dự án quảng cáo...">
                        </div>
                        <div class="mb-4">
                            <label for="client" class="form-label fw-semibold">Mô tả ngắn (Khách hàng/Chiến dịch) *</label>
                            <textarea class="form-control" id="client" name="client" rows="3" required placeholder="Ví dụ: Chiến dịch quảng cáo cho nhãn hàng X..."><?php echo htmlspecialchars($project['client'] ?? ''); ?></textarea>
                        </div>
                        <div>
                            <label class="form-label fw-semibold mb-2">Mô tả chi tiết</label>
                            <div id="description-editor">
                                <?php echo $project['description'] ?? ''; ?>
                            </div>
                            <input type="hidden" name="description" id="description-input">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột phải (Cài đặt) -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-cog text-primary me-2"></i>Cài đặt</h5>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> <?php echo $is_new_project ? 'Lưu dự án' : 'Cập nhật'; ?>
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="status" class="form-label fw-semibold">Trạng thái</label>
                            <select class="form-select fw-semibold" id="status" name="status">
                                <option value="published" <?php echo ($project['status'] ?? '') === 'published' ? 'selected' : ''; ?>>🟢 Đã xuất bản (Published)</option>
                                <option value="draft" <?php echo ($project['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>🟠 Bản nháp (Draft)</option>
                            </select>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <h6 class="fw-semibold mb-3"><i class="fas fa-image text-primary me-2"></i>Ảnh đại diện</h6>
                            <img id="image-preview" src="<?php echo !empty($project['preview_image']) ? '../' . htmlspecialchars($project['preview_image']) : ''; ?>" alt="Xem trước ảnh" class="img-fluid rounded shadow-sm mb-2" style="max-height: 200px; width: 100%; object-fit: cover; display: <?php echo !empty($project['preview_image']) ? 'block' : 'none'; ?>;">
                            <div id="image-placeholder" class="text-muted p-4 border rounded bg-light text-center" style="display: <?php echo empty($project['preview_image']) ? 'block' : 'none'; ?>;">
                                <i class="fas fa-image fa-3x mb-2 opacity-25"></i><br>Chưa có ảnh
                            </div>
                            <input type="file" class="form-control mt-2" id="preview_image" name="preview_image" accept="image/*" onchange="previewImage(event, 'image-preview')">
                        </div>
                        <hr>
                        <div>
                            <h6 class="fw-semibold mb-3"><i class="fas fa-video text-primary me-2"></i>Video dự án (Tùy chọn)</h6>
                            <video id="video-preview" src="<?php echo !empty($project['preview_video']) ? '../' . htmlspecialchars($project['preview_video']) : ''; ?>" class="img-fluid rounded shadow-sm mb-2" style="max-height: 200px; width: 100%; object-fit: cover; display: <?php echo !empty($project['preview_video']) ? 'block' : 'none'; ?>;" controls></video>
                            <div id="video-placeholder" class="text-muted p-4 border rounded bg-light text-center" style="display: <?php echo empty($project['preview_video']) ? 'block' : 'none'; ?>;">
                                <i class="fas fa-film fa-3x mb-2 opacity-25"></i><br>Chưa có video
                            </div>
                            <input type="file" class="form-control mt-2" id="preview_video" name="preview_video" accept="video/*" onchange="previewVideo(event, 'video-preview')">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- 3. Thêm JS của Quill.js -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
    function previewImage(event, previewId) {
        if (event.target.files && event.target.files[0]) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById(previewId);
                output.src = reader.result;
                output.style.display = 'block';
                var placeholder = document.getElementById('image-placeholder');
                if (placeholder) placeholder.style.display = 'none';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    }
    function previewVideo(event, previewId) {
        if (event.target.files && event.target.files[0]) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById(previewId);
                output.src = reader.result;
                output.style.display = 'block';
                var placeholder = document.getElementById('video-placeholder');
                if (placeholder) placeholder.style.display = 'none';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    }

    // 4. Khởi tạo Quill.js và đồng bộ dữ liệu
    const quillOptions = {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'script': 'sub'}, { 'script': 'super' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                ['link', 'image', 'video', 'blockquote', 'code-block'],
                ['clean']
            ]
        }
    };

    const quill = new Quill('#description-editor', quillOptions);

    const form = document.querySelector('form');
    const descriptionInput = document.getElementById('description-input');

    form.addEventListener('submit', function(e) {
        // Trước khi submit, lấy nội dung HTML từ Quill và gán vào input ẩn
        descriptionInput.value = quill.root.innerHTML;
    });
</script>
</body>
</html>