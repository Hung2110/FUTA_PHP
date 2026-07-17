<?php
require_once 'auth_check.php'; // Kiểm tra đăng nhập và quyền
$pageTitle = 'Import Dữ Liệu';

// Lấy loại import mặc định từ URL, ví dụ: import.php?type=project
$message = '';
$message_type = '';
if (isset($_SESSION['import_message'])) {
    $message = $_SESSION['import_message'];
    $message_type = $_SESSION['import_message_type'];
    unset($_SESSION['import_message']);
    unset($_SESSION['import_message_type']);
}

$import_type_default = $_GET['type'] ?? 'project'; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | FUTA Advertising' : 'FUTA Advertising'; ?></title>
    <link rel="icon" href="../assets/images/logo/futa.png" type="image/png">
     <!-- Favicon (Logo trên tab trình duyệt) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: #f7f9fc; }
        .page-header {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        .page-header h1 {
            font-weight: 700;
            font-size: 1.75rem;
            margin: 0;
            color: #1f2a37;
        }
        .page-header p { color: #6b7280; margin: 5px 0 0; font-size: 14px; }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .card-body { padding: 2rem; }
        .form-label { font-weight: 600; color: #374151; }
        .form-control, .form-select { border-radius: 8px; padding: 10px 14px; }
        .btn-primary { font-weight: 600; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-file-import me-2 text-primary"></i>Import Dữ liệu</h1>
            <p class="mb-0">Tải lên file Word (.docx) hoặc PDF để tạo nhanh dự án hoặc bài viết mới.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo $message; // Cho phép HTML trong thông báo ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body p-lg-5">
                <h3 class="card-title mb-2">Tải lên tệp của bạn</h3>
                <div class="alert alert-info mb-4">
                    <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Quy ước định dạng file</h6>
                    <p class="mb-1">Sử dụng các thẻ sau để phân tách nội dung:</p>
                    <ul>
                        <li><strong>Dự án:</strong> <code>[TITLE]</code>, <code>[CLIENT]</code>, <code>[CONTENT]</code></li>
                        <li><strong>Tin tức:</strong> <code>[TITLE]</code>, <code>[EXCERPT]</code>, <code>[CONTENT]</code></li>
                    </ul>
                    <small>Nếu không có thẻ nào, hệ thống sẽ tự lấy dòng đầu làm tiêu đề.</small>
                </div>
                <form action="handle_import.php" method="post" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="import_type" class="form-label">1. Chọn loại nội dung</label>
                        <select name="import_type" id="import_type" class="form-select" required>
                            <option value="project" <?php echo ($import_type_default === 'project') ? 'selected' : ''; ?>>Dự án</option>
                            <option value="posts" <?php echo ($import_type_default === 'posts') ? 'selected' : ''; ?>>Tin tức (Blog)</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="fileToUpload" class="form-label">2. Chọn tệp từ máy tính</label>
                        <input type="file" class="form-control" name="fileToUpload" id="fileToUpload" accept=".docx,.pdf" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100" name="submit"><i class="fas fa-upload me-2"></i>Bắt đầu Import</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>