<?php
require_once 'auth_check.php';

function create_slug($string) {
    $string = mb_strtolower($string, 'UTF-8');
    $char_map = [
        'à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ' => 'a', 'è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ' => 'e',
        'ì|í|ị|ỉ|ĩ' => 'i', 'ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ' => 'o',
        'ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ' => 'u', 'ỳ|ý|ỵ|ỷ|ỹ' => 'y', 'đ' => 'd',
    ];
    foreach ($char_map as $pattern => $replacement) {
        $string = preg_replace("/($pattern)/", $replacement, $string);
    }
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/([\s-]+)/', '-', $string);
    return trim($string, '-');
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add' || $action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        $image = $_POST['existing_image'] ?? '';

        if (!empty($_FILES['image']['name'])) {
            $uploadDir = '../uploads/posts/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $uploadPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $image = 'uploads/posts/' . $fileName;
            }
        }

        if ($title === '') {
            $message = 'Vui lòng nhập tiêu đề bài viết!';
            $message_type = 'danger';
        } else {
            $slug = create_slug($title);
            $created_by = $_SESSION['admin_id'];

            if ($action === 'add') {
                $stmt = $conn->prepare("INSERT INTO posts (title, slug, excerpt, content, image, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssi", $title, $slug, $excerpt, $content, $image, $status, $created_by);
                if ($stmt->execute()) {
                    header('Location: posts.php?success=1');
                    exit;
                } else {
                    $message = 'Không thể thêm bài viết: ' . $stmt->error;
                    $message_type = 'danger';
                }
            } else {
                $stmt = $conn->prepare("UPDATE posts SET title=?, slug=?, excerpt=?, content=?, image=?, status=? WHERE id=?");
                $stmt->bind_param("ssssssi", $title, $slug, $excerpt, $content, $image, $status, $id);
                if ($stmt->execute()) {
                    header('Location: posts.php?success=1');
                    exit;
                } else {
                    $message = 'Không thể cập nhật bài viết: ' . $stmt->error;
                    $message_type = 'danger';
                }
            }
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM posts WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header('Location: posts.php?deleted=1');
            exit;
        } else {
            $message = 'Không thể xóa bài viết: ' . $stmt->error;
            $message_type = 'danger';
        }
        $stmt->close();
    }
}

if (isset($_GET['success'])) {
    $message = 'Thao tác thành công!';
    $message_type = 'success';
}
if (isset($_GET['deleted'])) {
    $message = 'Xóa bài viết thành công!';
    $message_type = 'success';
}

$posts = $conn->query("SELECT p.*, u.fullname as author_name FROM posts p LEFT JOIN users u ON p.created_by = u.id ORDER BY p.created_at DESC");

$edit_post = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM posts WHERE id = {$edit_id} LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $edit_post = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Tin Tức</title>
    <link rel="icon" href="/FUTA_PHP/assets/images/logo/futa.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css"> <!-- Sử dụng CSS chung của admin -->
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-newspaper me-2 text-primary"></i>Quản lý Tin tức & Blog</h1>
                <p class="mb-0">Tạo, chỉnh sửa và quản lý các bài viết trên website.</p>
            </div>
            <button class="cta-button" data-bs-toggle="modal" data-bs-target="#postModal">
                <i class="fas fa-plus"></i> Thêm bài viết mới
            </button>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Ảnh</th>
                                <th>Tiêu đề</th>
                                <th>Tác giả</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($posts && $posts->num_rows > 0): ?>
                                <?php while($post = $posts->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <img src="../<?php echo htmlspecialchars($post['image'] ?? 'assets/img/placeholder.png'); ?>" alt="Image" style="width: 80px; height: 50px; object-fit: cover; border-radius: 4px;">
                                        </td>
                                        <td>
                                            <p class="job-title mb-1"><?php echo htmlspecialchars($post['title']); ?></p>
                                            <p class="job-meta mb-0">
                                                <a href="../blog-detail.php?slug=<?php echo $post['slug']; ?>" target="_blank" class="text-decoration-none small"><i class="fas fa-external-link-alt fa-xs"></i> Xem bài viết</a>
                                            </p>
                                        </td>
                                        <td><?php echo htmlspecialchars($post['author_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge <?php echo $post['status'] === 'published' ? 'badge-open' : 'badge-closed'; ?>">
                                                <?php echo $post['status'] === 'published' ? 'Đã xuất bản' : 'Bản nháp'; ?>
                                            </span>
                                        </td>
                                        <td style="font-size: 13px; color: #6b7280;"><?php echo date('d/m/Y', strtotime($post['created_at'])); ?></td>
                                        <td>
                                            <div class="table-actions">
                                                <a href="?edit=<?php echo $post['id']; ?>" class="btn btn-sm btn-warning" title="Sửa"><i class="fas fa-edit"></i></a>
                                                <button type="button" class="btn btn-sm btn-danger" title="Xóa" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php echo $post['id']; ?>"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center p-5">Chưa có bài viết nào.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Thêm/Sửa -->
    <div class="modal fade" id="postModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php echo $edit_post ? 'Cập nhật bài viết' : 'Thêm bài viết mới'; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $edit_post ? 'edit' : 'add'; ?>">
                        <?php if ($edit_post): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_post['id']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Tiêu đề *</label>
                            <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($edit_post['title'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả ngắn (Excerpt)</label>
                            <textarea class="form-control" name="excerpt" rows="3"><?php echo htmlspecialchars($edit_post['excerpt'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nội dung chi tiết</label>
                            <textarea class="form-control" name="content" rows="10" id="editor"><?php echo htmlspecialchars($edit_post['content'] ?? ''); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ảnh đại diện</label>
                                <input type="file" class="form-control" name="image" accept="image/*">
                                <?php if (!empty($edit_post['image'])): ?>
                                    <input type="hidden" name="existing_image" value="<?php echo $edit_post['image']; ?>">
                                    <img src="../<?php echo $edit_post['image']; ?>" class="mt-2" style="max-width: 100px; border-radius: 4px;">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select class="form-select" name="status">
                                    <option value="published" <?php echo ($edit_post['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Xuất bản</option>
                                    <option value="draft" <?php echo ($edit_post['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Bản nháp</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary"><?php echo $edit_post ? 'Cập nhật' : 'Thêm mới'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Xóa -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="deleteForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Xác nhận xóa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bạn có chắc chắn muốn xóa bài viết này?</p>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deletePostId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger">Xóa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/35.1.0/classic/ckeditor.js"></script>
    <script>
        ClassicEditor.create(document.querySelector('#editor')).catch(error => console.error(error));

        var deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var postId = button.getAttribute('data-id');
            document.getElementById('deletePostId').value = postId;
        });

        <?php if ($edit_post): ?>
        var postModal = new bootstrap.Modal(document.getElementById('postModal'));
        postModal.show();
        <?php endif; ?>
    </script>
</body>
</html>