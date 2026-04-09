<?php
require_once 'auth_check.php';

// --- Thông báo ---
$message = '';
$message_type = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'added') $message = 'Thêm bài viết thành công!';
    if ($_GET['success'] === 'updated') $message = 'Cập nhật bài viết thành công!';
    if ($_GET['success'] === 'deleted') $message = 'Xóa bài viết thành công!';
    $message_type = 'success';
}
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'notfound') $message = 'Không tìm thấy bài viết.';
    $message_type = 'danger';
}

// --- Pagination Logic ---
$limit = 10; // Số bài viết trên mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Lấy tổng số bài viết để tính tổng số trang
$total_posts_result = $conn->query("SELECT COUNT(*) as total FROM posts");
$total_posts = $total_posts_result->fetch_assoc()['total'];
$total_pages = ceil($total_posts / $limit);

// Lấy bài viết cho trang hiện tại
$posts_query = "SELECT p.*, u.fullname as author FROM posts p LEFT JOIN users u ON p.created_by = u.id ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$stmt_posts = $conn->prepare($posts_query);
$stmt_posts->bind_param("ii", $limit, $offset);
$stmt_posts->execute();
$posts = $stmt_posts->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý Blog</title>
    <link rel="icon" href="/FUTA_PHP/assets/images/logo/futa.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background: #f7f9fc; }
        .main-content { margin-left: 260px; padding: 30px; }
        .card { border-radius: 12px; }
        .form-control, .form-select, textarea { border-radius: 8px; }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-newspaper text-primary"></i> Quản lý Blog</h1>
        <a href="post-edit.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm bài mới
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th style="width: 40%;">Tiêu đề</th>
                            <th>Tác giả</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($posts && $posts->num_rows): while($post = $posts->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $post['id']; ?></td>
                            <td class="d-flex align-items-center">
                                <img src="../<?php echo htmlspecialchars($post['image'] ?: 'assets/images/service/billboard.jpg'); ?>" alt="Post image" class="rounded me-3" width="60" height="45" style="object-fit: cover;">
                                <div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($post['title']); ?></div>
                                    <div class="text-muted small">
                                        <?php $blog_url = "../blog/" . htmlspecialchars($post['slug']); ?>
                                        <a href="<?php echo $blog_url; ?>" target="_blank" class="text-decoration-none">
                                            /blog/<?php echo htmlspecialchars($post['slug']); ?>
                                            <i class="fas fa-external-link-alt fa-xs ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($post['author'] ?? ''); ?></td>
                            <td><span class="badge bg-<?php echo $post['status']=='published' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($post['status']); ?></span></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></td>
                            <td class="text-end d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-sm btn-info btn-view" title="Xem trước"
                                    data-bs-toggle="modal" data-bs-target="#viewPostModal"
                                    data-title="<?php echo htmlspecialchars($post['title']); ?>"
                                    data-author="<?php echo htmlspecialchars($post['author'] ?? 'Admin'); ?>"
                                    data-created_at="<?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>"
                                    data-image="../<?php echo htmlspecialchars($post['image'] ?: 'assets/images/service/billboard.jpg'); ?>"
                                    data-content="<?php echo htmlspecialchars($post['content']); ?>"
                                ><i class="fas fa-eye"></i></button>
                                <a href="post-edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-warning" title="Sửa"><i class="fas fa-edit"></i></a>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Xóa bài viết?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <button type="submit" formaction="post-edit.php" class="btn btn-sm btn-danger" title="Xóa"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="7" class="text-center text-muted">Chưa có bài viết</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <?php if ($total_pages > 1): ?>
            <nav class="mt-4 d-flex justify-content-center">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>">Trước</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>">Sau</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Xem trước bài viết -->
    <div class="modal fade" id="viewPostModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalTitle">Xem trước bài viết</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h2 id="viewTitle" class="mb-3"></h2>
                    <p class="text-muted">
                        <span id="viewAuthor"></span> — <span id="viewCreatedAt"></span>
                    </p>
                    <img id="viewImage" src="" alt="Post Image" class="img-fluid rounded mb-4" style="max-height: 400px; width: 100%; object-fit: cover;">
                    <div id="viewContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewPostModal = document.getElementById('viewPostModal');
    viewPostModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;

        document.getElementById('viewTitle').textContent = button.dataset.title;
        document.getElementById('viewAuthor').textContent = 'Tác giả: ' + button.dataset.author;
        document.getElementById('viewCreatedAt').textContent = button.dataset.createdAt;
        document.getElementById('viewImage').src = button.dataset.image;
        document.getElementById('viewContent').innerHTML = button.dataset.content;
    });
});
</script>
</body>
</html>
