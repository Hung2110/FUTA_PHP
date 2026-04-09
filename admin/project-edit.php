<?php
require_once 'auth_check.php'; // Includes db.php and session_start()

// Hàm xử lý upload file
function handle_upload($file_key, $current_path = '') {
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/projects/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Xóa file cũ nếu có (trừ khi là ảnh mặc định)
        if ($current_path && file_exists('../' . $current_path) && strpos($current_path, 'billboard.jpg') === false) {
            @unlink('../' . $current_path);
        }

        $file_name = uniqid() . '-' . basename($_FILES[$file_key]['name']);
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $target_path)) {
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
        $image_path = handle_upload('preview_image', $current_image);
        $video_path = handle_upload('preview_video', $current_video);

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
    <title><?php echo $is_new_project ? 'Thêm dự án mới' : 'Chỉnh sửa dự án'; ?></title>
    <link rel="icon" href="/FUTA_PHP/assets/images/logo/futa.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background: #f7f9fc; }
        .card { border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: none; }
        .form-control, .form-select { border-radius: 8px; }
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

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-4">
            <form action="project-edit.php<?php echo !$is_new_project ? '?id=' . $project_id : ''; ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($project['preview_image'] ?? ''); ?>">
                <input type="hidden" name="current_video" value="<?php echo htmlspecialchars($project['preview_video'] ?? ''); ?>">
                <?php if (!$is_new_project): ?>
                    <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="title" class="form-label">Tiêu đề dự án *</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($project['title'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="client" class="form-label">Mô tả ngắn *</label>
                    <textarea class="form-control" id="client" name="client" rows="3" required><?php echo htmlspecialchars($project['client'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="description-editor" class="form-label">Mô tả chi tiết</label>
                    <textarea class="form-control" id="description-editor" name="description" rows="15"><?php echo htmlspecialchars($project['description'] ?? ''); ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="preview_image" class="form-label">Ảnh đại diện</label>
                            <input type="file" class="form-control" id="preview_image" name="preview_image" accept="image/*" onchange="previewImage(event, 'image-preview')">
                            <div class="mt-2">
                                <p class="mb-1 small text-muted">Xem trước:</p>
                                <img id="image-preview" src="<?php echo !empty($project['preview_image']) ? '../' . htmlspecialchars($project['preview_image']) : ''; ?>" alt="Xem trước ảnh" style="max-width: 200px; height: auto; display: <?php echo !empty($project['preview_image']) ? 'block' : 'none'; ?>;">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="preview_video" class="form-label">Video đại diện</label>
                            <input type="file" class="form-control" id="preview_video" name="preview_video" accept="video/*" onchange="previewVideo(event, 'video-preview')">
                            <div class="mt-2">
                                <p class="mb-1 small text-muted">Xem trước:</p>
                                <video id="video-preview" src="<?php echo !empty($project['preview_video']) ? '../' . htmlspecialchars($project['preview_video']) : ''; ?>" style="max-width: 200px; height: auto; display: <?php echo !empty($project['preview_video']) ? 'block' : 'none'; ?>;" controls></video>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status">
                                <option value="published" <?php echo ($project['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Xuất bản</option>
                                <option value="draft" <?php echo ($project['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Bản nháp</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> <?php echo $is_new_project ? 'Lưu dự án' : 'Cập nhật dự án'; ?>
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function previewImage(event, previewId) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById(previewId);
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }
    function previewVideo(event, previewId) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById(previewId);
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/l2q0znuxxaqs67g0oq57gq8hvxeewnh664ncw761l4psvcxg/tinymce/8/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  tinymce.init({
    selector: '#description-editor',
    plugins: 'anchor autolink charmap codesample emoticons link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
  });
</script>
</body>
</html>