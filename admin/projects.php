<?php
require_once 'auth_check.php';
$pageTitle = "Quản Lý Dự Án";

$message = '';
$message_type = '';

// Hàm ghi log hoạt động
function log_activity($conn, $action, $module) {
    if (isset($_SESSION['admin_id'])) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, module, ip) VALUES (?, ?, ?, ?)");
        $log_stmt->bind_param("isss", $_SESSION['admin_id'], $action, $module, $ip);
        $log_stmt->execute();
    }
}

// --- Thông báo ---
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'added') $message = 'Thêm dự án thành công!';
    if ($_GET['success'] === 'updated') $message = 'Cập nhật dự án thành công!';
    if ($_GET['success'] === 'deleted') $message = 'Xóa dự án thành công!';
    $message_type = 'success';
}
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'notfound') $message = 'Không tìm thấy dự án.';
    if ($_GET['error'] === 'delete_failed') $message = 'Lỗi khi xóa dự án.';
    $message_type = 'danger';
}

// --- Filter by Status ---
$filter = isset($_GET['filter']) && in_array($_GET['filter'], ['published', 'pending', 'draft']) ? $_GET['filter'] : 'all';

// Lấy số lượng theo từng trạng thái để hiển thị badge
$status_counts_result = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as count_published,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as count_pending,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as count_draft
    FROM projects
");
$status_counts = $status_counts_result ? $status_counts_result->fetch_assoc() : ['total' => 0, 'count_published' => 0, 'count_pending' => 0, 'count_draft' => 0];

// --- Pagination Logic ---
$limit = 10; // 10 dự án trên mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_sql = "SELECT COUNT(*) as total FROM projects";
if ($filter !== 'all') {
    $count_sql .= " WHERE status = '" . $conn->real_escape_string($filter) . "'";
}
$total_projects_result = $conn->query($count_sql);
$total_projects = $total_projects_result ? $total_projects_result->fetch_assoc()['total'] : 0;
$total_pages = max(1, ceil($total_projects / $limit));

// Lấy danh sách dự án
$projects_query = "SELECT p.*, u.fullname as author FROM projects p 
                         LEFT JOIN users u ON p.created_by = u.id";
if ($filter !== 'all') {
    $projects_query .= " WHERE p.status = '" . $conn->real_escape_string($filter) . "'";
}
$projects_query .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$stmt_projects = $conn->prepare($projects_query);
$stmt_projects->bind_param("ii", $limit, $offset);
$stmt_projects->execute();
$projects = $stmt_projects->get_result();
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
    <!-- Quill.js CSS -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/projects.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-project-diagram text-primary me-2"></i>Quản Lý Dự Án</h1>
                <p class="mb-0">Theo dõi, quản lý và cập nhật các dự án quảng cáo.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="import.php?type=project" class="btn btn-outline-primary"><i class="fas fa-file-import me-1"></i> Import từ file</a>
                <a href="project-edit.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Thêm dự án mới
                </a>
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
                <a href="projects.php" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                    Tất cả <span class="badge <?php echo $filter === 'all' ? 'bg-white text-primary' : 'bg-secondary'; ?> ms-1"><?php echo (int)($status_counts['total'] ?? 0); ?></span>
                </a>
                <a href="projects.php?filter=published" class="btn <?php echo $filter === 'published' ? 'btn-success fw-semibold' : 'btn-outline-secondary'; ?>">
                    <i class="far fa-check-circle me-1"></i>Đã xuất bản <span class="badge <?php echo $filter === 'published' ? 'bg-white text-success' : 'bg-secondary'; ?> ms-1"><?php echo (int)($status_counts['count_published'] ?? 0); ?></span>
                </a>
                <a href="projects.php?filter=pending" class="btn <?php echo $filter === 'pending' ? 'btn-warning text-dark fw-semibold' : 'btn-outline-secondary'; ?>">
                    <i class="far fa-clock me-1"></i>Chờ duyệt <span class="badge <?php echo $filter === 'pending' ? 'bg-dark text-white' : 'bg-secondary'; ?> ms-1"><?php echo (int)($status_counts['count_pending'] ?? 0); ?></span>
                </a>
                <a href="projects.php?filter=draft" class="btn <?php echo $filter === 'draft' ? 'btn-secondary text-white fw-semibold' : 'btn-outline-secondary'; ?>">
                    <i class="far fa-file-alt me-1"></i>Bản nháp <span class="badge <?php echo $filter === 'draft' ? 'bg-dark text-white' : 'bg-secondary'; ?> ms-1"><?php echo (int)($status_counts['count_draft'] ?? 0); ?></span>
                </a>
            </div>
            <div class="text-muted small filter-count-info">
                <i class="fas fa-project-diagram me-1 text-primary"></i> Tổng số: <strong><?php echo number_format($status_counts['total'] ?? 0); ?></strong> dự án
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive table-responsive-horizontal">
                    <table class="table table-hover align-middle mb-0 table-horizontal">
                        <thead>
                            <tr>
                                <th class="text-center d-none d-md-table-cell" style="width: 14.285%;">ID</th>
                                <th class="text-center" style="width: 14.285%;">Hình ảnh</th>
                                <th class="text-start ps-2" style="width: 14.285%;">Dự án</th>
                                <th class="text-center d-none d-md-table-cell" style="width: 14.285%;">Tác giả</th>
                                <th class="text-center d-none d-md-table-cell" style="width: 14.285%;">Trạng thái</th>
                                <th class="text-center d-none d-md-table-cell" style="width: 14.285%;">Ngày tạo</th>
                                <th class="text-center pe-3" style="width: 14.285%;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($projects && $projects->num_rows > 0): while($project = $projects->fetch_assoc()): 
                                $badge_class = ['draft' => 'secondary', 'pending' => 'warning text-dark', 'published' => 'success'];
                                $status_text = ['draft' => 'Bản nháp', 'pending' => 'Chờ duyệt', 'published' => 'Đã xuất bản'];

                                // Xử lý ảnh đại diện: nếu có ảnh thì lấy đường dẫn, không có thì để trống
                                $project_img = '';
                                if (!empty($project['preview_image'])) {
                                    $img_val = trim($project['preview_image']);
                                    if (preg_match('/^https?:\/\//i', $img_val) || strpos($img_val, '../') === 0 || strpos($img_val, '/') === 0) {
                                        $project_img = $img_val;
                                    } else {
                                        $project_img = '../' . $img_val;
                                    }
                                }

                                // Xử lý video đại diện (nếu có)
                                $project_video = '';
                                if (!empty($project['preview_video'])) {
                                    $vid_val = trim($project['preview_video']);
                                    if (preg_match('/^https?:\/\//i', $vid_val) || strpos($vid_val, '../') === 0 || strpos($vid_val, '/') === 0) {
                                        $project_video = $vid_val;
                                    } else {
                                        $project_video = '../' . $vid_val;
                                    }
                                }

                                $summary_text = !empty($project['client']) ? trim(strip_tags($project['client'])) : trim(strip_tags($project['description'] ?? ''));
                                $summary_text = preg_replace('/\s+/', ' ', $summary_text);
                                $project_url = "../project-detail.php?id=" . $project['id'];
                            ?>
                            <tr>
                                <td class="text-center fw-bold text-secondary d-none d-md-table-cell">#<?php echo $project['id']; ?></td>
                                <td class="text-center" style="width: 14.285%;">
                                    <?php if (!empty($project_img)): ?>
                                        <a href="javascript:void(0)" class="btn-view d-inline-block"
                                            data-bs-toggle="modal" data-bs-target="#viewProjectModal"
                                            data-title="<?php echo htmlspecialchars($project['title']); ?>"
                                            data-author="<?php echo htmlspecialchars($project['author'] ?? 'Admin'); ?>"
                                            data-created_at="<?php echo date('d/m/Y H:i', strtotime($project['created_at'])); ?>"
                                            data-status="<?php echo htmlspecialchars($status_text[$project['status']] ?? $project['status']); ?>"
                                            data-status-class="<?php echo htmlspecialchars($badge_class[$project['status']] ?? 'secondary'); ?>"
                                            data-image="<?php echo htmlspecialchars($project_img); ?>"
                                            data-video="<?php echo htmlspecialchars($project_video); ?>"
                                            data-client="<?php echo htmlspecialchars($project['client'] ?? ''); ?>"
                                            data-content="<?php echo htmlspecialchars($project['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            title="Xem ảnh & dự án">
                                            <img src="<?php echo htmlspecialchars($project_img); ?>" alt="Ảnh dự án" class="post-table-img" onerror="this.parentElement.style.display='none';">
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="ps-2 text-start">
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark post-title-text" title="<?php echo htmlspecialchars($project['title']); ?>">
                                            <?php echo htmlspecialchars($project['title']); ?>
                                        </div>
                                        <div class="text-muted small mt-1 d-none d-md-block" style="word-break: break-all;">
                                            <a href="<?php echo $project_url; ?>" target="_blank" class="text-decoration-none text-muted" title="/project-detail.php?id=<?php echo $project['id']; ?>">
                                                <i class="fas fa-link fa-xs me-1"></i>/project/<?php echo $project['id']; ?>
                                                <i class="fas fa-external-link-alt fa-xs ms-1"></i>
                                            </a>
                                        </div>
                                        <?php if (!empty($summary_text)): ?>
                                            <div class="d-md-none mt-1 text-muted" style="font-size: 11.5px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="<?php echo htmlspecialchars($summary_text); ?>">
                                                <i class="fas fa-quote-left fa-xs me-1 text-primary opacity-50"></i><?php echo htmlspecialchars($summary_text); ?>
                                            </div>
                                        <?php endif; ?>
                                        <!-- Dòng thông tin dự án đầy đủ trên màn hình nhỏ (< 768px) -->
                                        <div class="d-md-none mt-1 d-flex align-items-center gap-2 flex-wrap post-mobile-meta">
                                            <span class="badge bg-light text-secondary border">#<?php echo $project['id']; ?></span>
                                            <span class="badge bg-<?php echo $badge_class[$project['status']] ?? 'secondary'; ?> rounded-pill">
                                                <?php echo $status_text[$project['status']] ?? $project['status']; ?>
                                            </span>
                                            <span class="text-secondary fw-medium">
                                                <i class="far fa-user me-1"></i><?php echo htmlspecialchars($project['author'] ?? 'Admin'); ?>
                                            </span>
                                            <span class="text-muted" style="white-space: nowrap;">
                                                <i class="far fa-calendar-alt me-1"></i><?php echo date('d/m/Y H:i', strtotime($project['created_at'])); ?>
                                            </span>
                                            <div class="w-100 mt-1">
                                                <a href="<?php echo $project_url; ?>" target="_blank" class="text-decoration-none text-muted" style="word-break: break-all; font-size: 11px;">
                                                    <i class="fas fa-link fa-xs me-1"></i>/project/<?php echo $project['id']; ?>
                                                    <i class="fas fa-external-link-alt fa-xs ms-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell text-center">
                                    <span class="text-secondary fw-medium" title="<?php echo htmlspecialchars($project['author'] ?? 'Admin'); ?>">
                                        <i class="far fa-user me-1 text-muted"></i><?php echo htmlspecialchars($project['author'] ?? 'Admin'); ?>
                                    </span>
                                </td>
                                <td class="d-none d-md-table-cell text-center">
                                    <span class="badge bg-<?php echo $badge_class[$project['status']] ?? 'secondary'; ?> rounded-pill px-2 py-1">
                                        <?php echo $status_text[$project['status']] ?? $project['status']; ?>
                                    </span>
                                </td>
                                <td class="d-none d-md-table-cell text-center text-muted" style="font-size: 13px; white-space: nowrap;">
                                    <div><i class="far fa-calendar-alt me-1 text-secondary"></i><?php echo date('d/m/Y', strtotime($project['created_at'])); ?></div>
                                    <div class="small text-secondary"><i class="far fa-clock me-1 text-muted"></i><?php echo date('H:i', strtotime($project['created_at'])); ?></div>
                                </td>
                                <td class="text-center pe-3">
                                    <div class="d-inline-flex gap-1 justify-content-center">
                                        <button type="button" class="btn btn-sm btn-outline-info btn-view action-btn" title="Xem trước"
                                            data-bs-toggle="modal" data-bs-target="#viewProjectModal"
                                            data-title="<?php echo htmlspecialchars($project['title']); ?>"
                                            data-author="<?php echo htmlspecialchars($project['author'] ?? 'Admin'); ?>"
                                            data-created_at="<?php echo date('d/m/Y H:i', strtotime($project['created_at'])); ?>"
                                            data-status="<?php echo htmlspecialchars($status_text[$project['status']] ?? $project['status']); ?>"
                                            data-status-class="<?php echo htmlspecialchars($badge_class[$project['status']] ?? 'secondary'); ?>"
                                            data-image="<?php echo !empty($project_img) ? htmlspecialchars($project_img) : ''; ?>"
                                            data-video="<?php echo !empty($project_video) ? htmlspecialchars($project_video) : ''; ?>"
                                            data-client="<?php echo htmlspecialchars($project['client'] ?? ''); ?>"
                                            data-content="<?php echo htmlspecialchars($project['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        ><i class="fas fa-eye"></i></button>
                                        <a href="project-edit.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline-warning action-btn" title="Sửa"><i class="fas fa-edit"></i></a>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Xóa dự án?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                            <button type="submit" formaction="project-edit.php" class="btn btn-sm btn-outline-danger action-btn" title="Xóa"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; else: ?> 
                            <tr><td colspan="7" class="text-center text-muted p-5">Chưa có dự án nào</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <?php if ($total_pages > 1): ?>
                <nav class="mt-4 d-flex justify-content-center mb-4">
                    <ul class="pagination">
                        <?php 
                        $filter_param = ($filter !== 'all') ? '&filter=' . urlencode($filter) : '';
                        if ($page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $filter_param; ?>">Trước</a></li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $filter_param; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $filter_param; ?>">Sau</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Xem trước dự án -->
    <div class="modal fade" id="viewProjectModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalTitle">Xem trước dự án</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h2 id="viewTitle" class="mb-3"></h2>
                    <p class="text-muted">
                        <span id="viewAuthor"></span> — <span id="viewCreatedAt"></span>
                        <span id="viewStatusBadge" class="ms-2"></span>
                    </p>
                    <p id="viewExcerpt" class="lead text-muted fst-italic mb-3" style="display: none;"></p>
                    <img id="viewImage" src="" alt="Ảnh dự án" class="img-fluid rounded mb-4" style="max-height: 400px; width: 100%; object-fit: cover; display: none;">
                    <video id="viewVideo" src="" controls class="img-fluid rounded mb-4" style="max-height: 400px; width: 100%; display: none;"></video>
                    <div id="viewContent" class="ql-editor" style="min-height: 300px; padding: 0;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/projects.js"></script>
</body>
</html>