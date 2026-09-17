<?php
require_once 'auth_check.php';
$pageTitle = 'Quản Lý Tin Tức';

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
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | FUTA Advertising' : 'FUTA Advertising'; ?></title>
    <link rel="icon" href="../assets/images/logo/futa.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">

    <style>
        body { background: #f7f9fc; }
        .card { border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; background: #fff; }
        .form-control, .form-select, textarea { border-radius: 8px; }

        /* ======================================================================
           BẢNG QUẢN LÝ TIN TỨC - 1 BẢNG NGANG CHUẨN
           ====================================================================== */
        .table-responsive.table-responsive-horizontal {
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
        }
        .table-responsive.table-responsive-horizontal::-webkit-scrollbar {
            height: 6px;
        }
        .table-responsive.table-responsive-horizontal::-webkit-scrollbar-track {
            background: #f8fafc;
        }
        .table-responsive.table-responsive-horizontal::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .table.table-horizontal {
            display: table !important;
            width: 100% !important;
            border-collapse: collapse !important;
            vertical-align: middle;
            margin-bottom: 0;
            background: #fff;
        }
        .table.table-horizontal thead {
            display: table-header-group !important;
        }
        .table.table-horizontal thead tr {
            display: table-row !important;
            background: #f8fafc !important;
        }
        .table.table-horizontal thead th {
            display: table-cell !important;
            text-transform: uppercase !important;
            font-size: 0.78rem !important;
            font-weight: 700 !important;
            color: #64748b !important;
            background: #f8fafc !important;
            border-bottom: 2px solid #e2e8f0 !important;
            padding: 12px 14px !important;
            vertical-align: middle !important;
            white-space: nowrap !important;
            letter-spacing: 0.03em;
        }
        .table.table-horizontal tbody {
            display: table-row-group !important;
        }
        .table.table-horizontal tbody tr {
            display: table-row !important;
            background: transparent !important;
            border-bottom: 1px solid #f1f5f9 !important;
            transition: background-color 0.15s ease !important;
        }
        .table.table-horizontal tbody tr:hover {
            background-color: #f8fafc !important;
        }
        .table.table-horizontal tbody td {
            display: table-cell !important;
            padding: 11px 14px !important;
            border: none !important;
            border-bottom: 1px solid #f1f5f9 !important;
            font-size: 0.88rem !important;
            color: #1e293b !important;
            vertical-align: middle !important;
            white-space: normal !important;
            word-break: break-word !important;
        }
        .table.table-horizontal thead th:last-child,
        .table.table-horizontal tbody td:last-child {
            text-align: right !important;
            white-space: nowrap !important;
        }

        /* Chi tiết ô Hình ảnh */
        .post-table-img {
            width: 56px;
            height: 42px;
            object-fit: cover;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            display: inline-block;
            vertical-align: middle;
            transition: transform 0.15s ease;
        }
        .post-table-img:hover {
            transform: scale(1.06);
        }

        .post-title-text {
            line-height: 1.4;
            font-size: 0.92rem;
            font-weight: 600;
            color: #0f172a;
            word-break: break-word;
            white-space: normal;
        }

        .badge { padding: .35em .65em; font-size: 11px; }

        /* Nút thao tác */
        .action-btn {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.82rem;
            border-radius: 6px;
            padding: 0;
            transition: all 0.15s ease;
        }

        @media (max-width: 576px) {
            .table.table-horizontal tbody td {
                padding: 10px 6px !important;
            }
            .post-table-img {
                width: 44px;
                height: 33px;
                border-radius: 4px;
            }
            .action-btn {
                width: 28px;
                height: 28px;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 page-header-responsive">
        <h1><i class="fas fa-newspaper text-primary"></i> Quản lý Tin tức</h1>
        <div class="d-flex gap-2 flex-wrap btn-group-header">
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
            <div class="table-responsive table-responsive-horizontal">
                <table class="table table-hover align-middle mb-0 table-horizontal">
                    <thead>
                        <tr>
                            <th class="ps-3 d-none d-md-table-cell" style="width: 55px;">ID</th>
                            <th class="text-center" style="width: 80px;">Hình ảnh</th>
                            <th>Bài viết</th>
                            <th class="d-none d-md-table-cell" style="width: 130px;">Tác giả</th>
                            <th class="d-none d-md-table-cell text-center" style="width: 115px;">Trạng thái</th>
                            <th class="d-none d-md-table-cell" style="width: 140px;">Ngày tạo</th>
                            <th class="text-end pe-3" style="width: 110px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($posts && $posts->num_rows): while($post = $posts->fetch_assoc()): 
                            // Xử lý ảnh đại diện: nếu có ảnh thì lấy đường dẫn, không có thì để trống
                            $post_img = '';
                            if (!empty($post['image'])) {
                                $img_val = trim($post['image']);
                                if (preg_match('/^https?:\/\//i', $img_val) || strpos($img_val, '../') === 0 || strpos($img_val, '/') === 0) {
                                    $post_img = $img_val;
                                } else {
                                    $post_img = '../' . $img_val;
                                }
                            }
                            $summary_text = !empty($post['excerpt']) ? trim(strip_tags($post['excerpt'])) : trim(strip_tags($post['content'] ?? ''));
                            $summary_text = preg_replace('/\s+/', ' ', $summary_text);
                        ?>
                        <tr>
                            <td class="ps-3 fw-bold text-secondary d-none d-md-table-cell">#<?php echo $post['id']; ?></td>
                            <td class="text-center" style="width: 80px;">
                                <?php if (!empty($post_img)): ?>
                                    <a href="javascript:void(0)" class="btn-view d-inline-block"
                                        data-bs-toggle="modal" data-bs-target="#viewPostModal"
                                        data-title="<?php echo htmlspecialchars($post['title']); ?>"
                                        data-author="<?php echo htmlspecialchars($post['author'] ?? 'Admin'); ?>"
                                        data-created_at="<?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>"
                                        data-image="<?php echo htmlspecialchars($post_img); ?>"
                                        data-content="<?php echo htmlspecialchars($post['content'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        title="Xem ảnh & bài viết">
                                        <img src="<?php echo htmlspecialchars($post_img); ?>" alt="Ảnh bài viết" class="post-table-img" onerror="this.parentElement.style.display='none';">
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td class="ps-2">
                                <div class="min-w-0">
                                    <div class="fw-semibold text-dark post-title-text" title="<?php echo htmlspecialchars($post['title']); ?>"><?php echo htmlspecialchars($post['title']); ?></div>
                                    <?php $blog_url = "../news_single.php?slug=" . htmlspecialchars($post['slug']); ?>
                                    <div class="text-muted small mt-1 d-none d-md-block" style="word-break: break-all;">
                                        <a href="<?php echo $blog_url; ?>" target="_blank" class="text-decoration-none text-muted" title="/news/<?php echo htmlspecialchars($post['slug']); ?>">
                                            <i class="fas fa-link fa-xs me-1"></i>/news/<?php echo htmlspecialchars($post['slug']); ?>
                                            <i class="fas fa-external-link-alt fa-xs ms-1"></i>
                                        </a>
                                    </div>
                                    <?php if (!empty($summary_text)): ?>
                                        <div class="d-md-none mt-1 text-muted" style="font-size: 11.5px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="<?php echo htmlspecialchars($summary_text); ?>">
                                            <i class="fas fa-quote-left fa-xs me-1 text-primary opacity-50"></i><?php echo htmlspecialchars($summary_text); ?>
                                        </div>
                                    <?php endif; ?>
                                    <!-- Dòng thông tin bài post đầy đủ trên màn hình nhỏ (< 768px) -->
                                    <div class="d-md-none mt-1 d-flex align-items-center gap-2 flex-wrap post-mobile-meta">
                                        <span class="badge bg-light text-secondary border">#<?php echo $post['id']; ?></span>
                                        <span class="badge bg-<?php echo $post['status']=='published' ? 'success' : 'secondary'; ?> rounded-pill">
                                            <?php echo $post['status']=='published' ? 'Đã xuất bản' : 'Bản nháp'; ?>
                                        </span>
                                        <span class="text-secondary fw-medium">
                                            <i class="far fa-user me-1"></i><?php echo htmlspecialchars($post['author'] ?? 'Admin'); ?>
                                        </span>
                                        <span class="text-muted" style="white-space: nowrap;">
                                            <i class="far fa-calendar-alt me-1"></i><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>
                                        </span>
                                        <div class="w-100 mt-1">
                                            <a href="<?php echo $blog_url; ?>" target="_blank" class="text-decoration-none text-muted" style="word-break: break-all; font-size: 11px;">
                                                <i class="fas fa-link fa-xs me-1"></i>/news/<?php echo htmlspecialchars($post['slug']); ?>
                                                <i class="fas fa-external-link-alt fa-xs ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <span class="text-secondary fw-medium" title="<?php echo htmlspecialchars($post['author'] ?? 'Admin'); ?>">
                                    <i class="far fa-user me-1 text-muted"></i><?php echo htmlspecialchars($post['author'] ?? 'Admin'); ?>
                                </span>
                            </td>
                            <td class="d-none d-md-table-cell text-center">
                                <span class="badge bg-<?php echo $post['status']=='published' ? 'success' : 'secondary'; ?> rounded-pill px-2 py-1">
                                    <?php echo $post['status']=='published' ? 'Đã xuất bản' : 'Bản nháp'; ?>
                                </span>
                            </td>
                            <td class="d-none d-md-table-cell text-muted" style="font-size: 13px; white-space: nowrap;">
                                <div><i class="far fa-calendar-alt me-1 text-secondary"></i><?php echo date('d/m/Y', strtotime($post['created_at'])); ?></div>
                                <div class="small text-secondary ps-3"><i class="far fa-clock me-1 text-muted"></i><?php echo date('H:i', strtotime($post['created_at'])); ?></div>
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-inline-flex gap-1 justify-content-end">
                                    <button type="button" class="btn btn-sm btn-outline-info btn-view action-btn" title="Xem trước"
                                        data-bs-toggle="modal" data-bs-target="#viewPostModal"
                                        data-title="<?php echo htmlspecialchars($post['title']); ?>"
                                        data-author="<?php echo htmlspecialchars($post['author'] ?? 'Admin'); ?>"
                                        data-created_at="<?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>"
                                        data-image="<?php echo !empty($post_img) ? htmlspecialchars($post_img) : ''; ?>"
                                        data-content="<?php echo htmlspecialchars($post['content'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    ><i class="fas fa-eye"></i></button>
                                    <a href="post-edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-warning action-btn" title="Sửa"><i class="fas fa-edit"></i></a>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Xóa bài viết?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                        <button type="submit" formaction="post-edit.php" class="btn btn-sm btn-outline-danger action-btn" title="Xóa"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?> 
                        <tr><td colspan="7" class="text-center text-muted p-5">Chưa có bài viết</td></tr>
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
                    <img id="viewImage" src="" alt="Post Image" class="img-fluid rounded mb-4" style="max-height: 400px; width: 100%; object-fit: cover; display: none;">
                    <div id="viewContent" class="ql-editor" style="min-height: 300px; padding: 0;"></div>
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
    const viewContentDiv = document.getElementById('viewContent');
    const viewImage = document.getElementById('viewImage');

    viewPostModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;

        document.getElementById('viewTitle').textContent = button.dataset.title;
        document.getElementById('viewAuthor').textContent = 'Tác giả: ' + button.dataset.author;
        document.getElementById('viewCreatedAt').textContent = button.dataset.created_at;
        
        if (button.dataset.image && button.dataset.image.trim() !== '') {
            viewImage.src = button.dataset.image;
            viewImage.style.display = 'block';
        } else {
            viewImage.src = '';
            viewImage.style.display = 'none';
        }
        
        // Set content for the div
        viewContentDiv.innerHTML = button.dataset.content || '<p class="text-muted">Không có nội dung chi tiết.</p>';
    });
});
</script>
</body>
</html>