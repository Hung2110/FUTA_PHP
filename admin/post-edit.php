<?php
require_once 'auth_check.php'; // Sử dụng file kiểm tra đăng nhập và quyền chung
require_once '../db.php';

function create_slug($string) {
    // Chuyển đổi chuỗi thành chữ thường và sang bảng mã UTF-8
    $string = mb_strtolower($string, 'UTF-8');
    // Bảng chuyển đổi ký tự có dấu
    $char_map = [
        'à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ' => 'a', 'è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ' => 'e',
        'ì|í|ị|ỉ|ĩ' => 'i', 'ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ' => 'o',
        'ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ' => 'u', 'ỳ|ý|ỵ|ỷ|ỹ' => 'y', 'đ' => 'd',
    ];
    foreach ($char_map as $pattern => $replacement) {
        $string = preg_replace("/($pattern)/", $replacement, $string);
    }
    // Loại bỏ các ký tự không phải chữ, số, khoảng trắng hoặc gạch nối
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    // Thay thế khoảng trắng và các gạch nối liên tiếp bằng một gạch nối duy nhất
    $string = preg_replace('/([\s-]+)/', '-', $string);
    // Loại bỏ gạch nối ở đầu và cuối chuỗi
    return trim($string, '-');
}

function getUniqueSlug($conn, $slug, $id = 0) {
    $original_slug = $slug;
    $count = 1;
    
    while (true) {
        $query = "SELECT id FROM posts WHERE slug = ? AND id != ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $slug, $id);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 0) {
            $stmt->close();
            return $slug;
        }
        $stmt->close();
        $slug = $original_slug . '-' . $count;
        $count++;
    }
}

$post_id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : null;
$post = null;
$message = '';
$message_type = '';
$is_new_post = ($post_id === null);

// Xử lý khi form được gửi đi (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save'; // Mặc định là lưu (thêm/sửa)

    // Xử lý xóa bài viết
    if ($action === 'delete') {
        $post_id_to_delete = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        if ($post_id_to_delete > 0) {
            $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
            $stmt->bind_param('i', $post_id_to_delete);
            if ($stmt->execute()) {
                header('Location: news.php?success=deleted');
            } else {
                header('Location: news.php?error=delete_failed');
            }
            $stmt->close();
            exit;
        }
    }

    // Xử lý thêm/sửa bài viết
    $post_id_from_form = isset($_POST['post_id']) && is_numeric($_POST['post_id']) ? intval($_POST['post_id']) : null;

    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = trim($_POST['status'] ?? 'draft');
    $tags_input = trim($_POST['tags'] ?? '');
    $current_image = trim($_POST['current_image'] ?? '');

    // --- VALIDATION ---
    if (empty($title)) {
        $message = "Tiêu đề không được để trống.";
        $message_type = 'danger';
    }

    // Tự động tạo slug nếu để trống
    if (empty($slug)) {
        $slug = create_slug($title);
    } else {
        $slug = create_slug($slug);
    }

    if (empty($slug)) {
        $slug = 'post-' . time();
    }

    // Đảm bảo slug là duy nhất
    $slug = getUniqueSlug($conn, $slug, $post_id_from_form ? $post_id_from_form : 0);

    // Xử lý tags
    $tags = !empty($tags_input) ? array_map('trim', explode(',', $tags_input)) : [];
    $tags_json = json_encode($tags, JSON_UNESCAPED_UNICODE);

    // Xử lý upload ảnh
    $image_path = $current_image;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['image']['size'] > 6 * 1024 * 1024) { // Giới hạn 6MB
            $message = "Kích thước ảnh quá lớn. Vui lòng chọn ảnh dưới 6MB.";
            $message_type = 'danger';
        } else {
            $upload_dir = '../uploads/posts/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_name = time() . '_' . basename($_FILES['image']['name']);
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                // Chỉ xóa ảnh cũ khi có ảnh mới được tải lên thành công
                if (!empty($current_image) && file_exists('../' . $current_image) && strpos($current_image, 'billboard.jpg') === false) {
                    unlink('../' . $current_image);
                }
                $image_path = 'uploads/posts/' . $file_name;
            } else {
                $message = "Có lỗi xảy ra khi tải ảnh lên.";
                $message_type = 'danger';
            }
        }
    }

    if (empty($message)) {
        if ($post_id_from_form) { // Chế độ cập nhật
            $stmt = $conn->prepare(
                "UPDATE posts SET title=?, slug=?, excerpt=?, content=?, image=?, status=?, tags=? WHERE id=?"
            );
            $stmt->bind_param('sssssssi', $title, $slug, $excerpt, $content, $image_path, $status, $tags_json, $post_id_from_form);
            $success_message = "Cập nhật bài viết thành công!";
            $redirect_url = "news.php?success=updated";
        } else { // Chế độ thêm mới
            $created_by = $_SESSION['admin_id'];
            $stmt = $conn->prepare(
                "INSERT INTO posts (title, slug, excerpt, content, image, status, tags, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('sssssssi', $title, $slug, $excerpt, $content, $image_path, $status, $tags_json, $created_by);
            $success_message = "Thêm bài viết thành công!";
            $redirect_url = "news.php?success=added";
        }

        if ($stmt->execute()) {
            header("Location: " . $redirect_url);
            exit;
        } else {
            $message = "Lỗi khi lưu bài viết: " . $stmt->error;
            $message_type = 'danger';
        }
        $stmt->close();
    }
}

// Nếu không phải là thêm mới, lấy dữ liệu bài viết để hiển thị
if (!$is_new_post) {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
        // Chuyển đổi tags từ JSON thành chuỗi để hiển thị
        $post['tags_str'] = !empty($post['tags']) ? implode(', ', json_decode($post['tags'], true)) : '';
    } else {
        // Nếu không tìm thấy ID, chuyển hướng về trang danh sách
        header('Location: news.php?error=notfound');
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
    <title><?php echo $is_new_post ? 'Thêm bài viết mới' : 'Chỉnh sửa bài viết'; ?></title>
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
            <i class="fas fa-<?php echo $is_new_post ? 'plus-circle' : 'edit'; ?> text-primary"></i> <?php echo $is_new_post ? 'Thêm bài viết mới' : 'Chỉnh sửa bài viết'; ?>
        </h1>
        <a href="news.php" class="btn btn-secondary">
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
            <form action="post-edit.php<?php echo !$is_new_post ? '?id=' . $post_id : ''; ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($post['image'] ?? ''); ?>">
                <?php if (!$is_new_post): ?>
                    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="title" class="form-label">Tiêu đề *</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($post['title'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="slug" class="form-label">Đường dẫn (slug)</label>
                    <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($post['slug'] ?? ''); ?>" placeholder="Để trống để tạo tự động">
                </div>
                <div class="mb-3">
                    <label for="excerpt" class="form-label">Mô tả ngắn (Excerpt)</label>
                    <textarea class="form-control" id="excerpt" name="excerpt" rows="4"><?php echo htmlspecialchars($post['excerpt'] ?? ''); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="content-editor" class="form-label">Nội dung chi tiết</label>
                    <textarea class="form-control" id="content-editor" name="content" rows="15"><?php echo $post['content'] ?? ''; ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="image" class="form-label">Ảnh đại diện</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                            <div class="mt-2">
                                <p class="mb-1 small text-muted">Xem trước:</p>
                                <img id="image-preview" src="<?php echo !empty($post['image']) ? '../' . htmlspecialchars($post['image']) : ''; ?>" alt="Xem trước ảnh" style="max-width: 200px; height: auto; display: <?php echo !empty($post['image']) ? 'block' : 'none'; ?>;">
                            </div>
                                <div class="mt-2">
                                </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status">
                                <option value="published" <?php echo ($post['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Xuất bản</option>
                                <option value="draft" <?php echo ($post['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Bản nháp</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="tags" class="form-label">Thẻ (Tags)</label>
                    <input type="text" class="form-control" id="tags" name="tags" value="<?php echo htmlspecialchars($post['tags_str'] ?? ''); ?>" placeholder="Nhập các thẻ, cách nhau bằng dấu phẩy">
                    <div class="form-text">Ví dụ: quảng cáo, marketing, futa</div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> <?php echo $is_new_post ? 'Lưu bài viết' : 'Cập nhật bài viết'; ?>
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('image-preview');
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
<!-- Place the first <script> tag in your HTML's <head> -->
<script src="https://cdn.tiny.cloud/1/l2q0znuxxaqs67g0oq57gq8hvxeewnh664ncw761l4psvcxg/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>

<!-- Place the following <script> and <textarea> tags your HTML's <body> -->
<script>
  tinymce.init({
    selector: '#content-editor',
    plugins: [
      // Core editing features
      'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount',
      // Your account includes a free trial of TinyMCE premium features
      // Try the most popular premium features until Jan 5, 2026:
      'checklist', 'mediaembed', 'casechange', 'formatpainter', 'pageembed', 'a11ychecker', 'tinymcespellchecker', 'permanentpen', 'powerpaste', 'advtable', 'advcode', 'advtemplate', 'ai', 'uploadcare', 'mentions', 'tinycomments', 'tableofcontents', 'footnotes', 'mergetags', 'autocorrect', 'typography', 'inlinecss', 'markdown','importword', 'exportword', 'exportpdf'
    ],
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography uploadcare | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
    tinycomments_mode: 'embedded',
    tinycomments_author: 'Author name',
    mergetags_list: [
      { value: 'First.Name', title: 'First Name' },
      { value: 'Email', title: 'Email' },
    ],
    ai_request: (request, respondWith) => respondWith.string(() => Promise.reject('See docs to implement AI Assistant')),
    uploadcare_public_key: 'ab3664f99f73615f23f0',
  });
</script>
