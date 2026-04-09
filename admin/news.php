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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tin Tức</title>
    <link rel="icon" href="/FUTA_PHP/assets/images/logo/futa.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background: #f7f9fc; }
        .card { border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: none; }
        .table { vertical-align: middle; }
        .table thead th {
            text-transform: uppercase;
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            padding: 16px;
        }
        .table tbody td { padding: 16px; border-bottom: 1px solid #f3f4f6; }
        .table tbody tr:hover {
            background-color: #f9fafb;
        }
        .post-title-cell {
            display: flex;
            align-items: center;
        }
        .post-title-cell .post-image {
            width: 80px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 16px;
        }
        .badge { padding: .4em .8em; font-size: 11px; }
        .form-control, .form-select, textarea { border-radius: 8px; }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-newspaper text-primary"></i> Quản lý Tin tức</h1>
        <div class="d-flex gap-2">
            <a href="import.php?type=posts" class="btn btn-outline-primary"><i class="fas fa-file-import"></i> Import từ file</a>
            <a href="post-edit.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm bài viết mới
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th style="width: 45%;">Tiêu đề</th>
                            <th>Tác giả</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th class="text-end" style="width: 15%;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($posts && $posts->num_rows): while($post = $posts->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?php echo $post['id']; ?></strong></td>
                            <td>
                                <div class="post-title-cell">
                                <img src="../<?php echo htmlspecialchars($post['image'] ?: 'assets/images/banners/back.jpeg'); ?>" alt="Post image" class="post-image">
                                <div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($post['title']); ?></div>
                                    <div class="text-muted small">
                                        <?php $blog_url = "../news_single.php?slug=" . htmlspecialchars($post['slug']); ?>
                                        <a href="<?php echo $blog_url; ?>" target="_blank" class="text-decoration-none">
                                            /news/<?php echo htmlspecialchars($post['slug']); ?>
                                            <i class="fas fa-external-link-alt fa-xs ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($post['author'] ?? ''); ?></td>
                            <td><span class="badge bg-<?php echo $post['status']=='published' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($post['status']); ?></span></td>
                            <td style="font-size: 13px; color: #6b7280;"><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-info btn-view" title="Xem trước"
                                    data-bs-toggle="modal" data-bs-target="#viewPostModal"
                                    data-title="<?php echo htmlspecialchars($post['title']); ?>"
                                    data-author="<?php echo htmlspecialchars($post['author'] ?? 'Admin'); ?>"
                                    data-created_at="<?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>"
                                    data-image="../<?php echo htmlspecialchars($post['image'] ?: 'assets/images/banners/back.jpeg'); ?>"
                                    data-content="<?php echo htmlspecialchars($post['content'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
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
                        <tr><td colspan="6" class="text-center text-muted p-5">Chưa có bài viết</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <?php if ($total_pages > 1): ?>
            <nav class="mt-4 d-flex justify-content-center mb-4">
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
        document.getElementById('viewCreatedAt').textContent = button.dataset.created_at;
        document.getElementById('viewImage').src = button.dataset.image;

        // Decode HTML entities from the data-content attribute before rendering
        const tempTextarea = document.createElement('textarea');
        tempTextarea.innerHTML = button.dataset.content;
        document.getElementById('viewContent').innerHTML = tempTextarea.value;
    });
});
</script>
</body>
</html>