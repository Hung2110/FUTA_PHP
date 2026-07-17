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

// --- Pagination Logic ---
$limit = 9; // Số dự án trên mỗi trang (3x3 grid)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Lấy tổng số dự án để tính tổng số trang
$total_projects_result = $conn->query("SELECT COUNT(*) as total FROM projects");
$total_projects = $total_projects_result->fetch_assoc()['total'];
$total_pages = ceil($total_projects / $limit);

// Lấy danh sách dự án
$projects_query = "SELECT p.*, u.fullname as created_by_name FROM projects p 
                         LEFT JOIN users u ON p.created_by = u.id 
                         ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
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
    <style>
        body { background: #f7f9fc; }
        .project-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }
        .project-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,74,173,0.08);
            overflow: hidden;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            display: flex;
            flex-direction: column;
        }
       
        .project-card-img {
            height: 180px;
            width: 100%;
            object-fit: cover;
            background-color: #e9ecef;
        }
        .project-card-body { 
            padding: 20px; 
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .project-card-footer { padding: 15px 20px; background-color: #f8f9fa; border-top: 1px solid #e9ecef; margin-top: auto; /* Đẩy footer xuống cuối cùng */ }
        .action-btn {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-project-diagram text-primary"></i> Quản Lý Dự Án</h1>
             <div class="d-flex gap-2">
            <a href="import.php?type=project" class="btn btn-outline-primary"><i class="fas fa-file-import"></i> Import từ file</a>
            <a href="project-edit.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm dự án mới
            </a>
        </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="project-grid">
            <?php if ($projects->num_rows > 0): ?>
                <?php while($project = $projects->fetch_assoc()): ?>
                    <?php 
                        $badge_class = ['draft' => 'warning', 'pending' => 'info', 'published' => 'success'];
                        $status_text = ['draft' => 'Nháp', 'pending' => 'Chờ duyệt', 'published' => 'Đã xuất bản'];
                    ?>
                    <div class="project-card">
                        <a href="../project-detail.php?id=<?php echo $project['id']; ?>" target="_blank">
                            <img src="../<?php echo htmlspecialchars(!empty($project['preview_image']) ? $project['preview_image'] : 'assets/images/service/billboard.jpg'); ?>" class="project-card-img" alt="<?php echo htmlspecialchars($project['title']); ?>">
                        </a>
                        <div class="project-card-body">
                            <h5 class="card-title fw-bold mb-2"><?php echo htmlspecialchars($project['title']); ?></h5>
                            <p class="card-text text-muted small mb-3"><i class="fas fa-file-alt me-2"></i><?php echo htmlspecialchars(mb_substr($project['client'] ?? '', 0, 100)) . (mb_strlen($project['client'] ?? '') > 100 ? '...' : ''); ?></p>
                            <p class="card-text text-muted small mb-1"><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($project['created_by_name'] ?? 'N/A'); ?></p>
                            <p class="card-text text-muted small mb-3"><i class="fas fa-calendar-alt me-2"></i><?php echo date('d/m/Y', strtotime($project['created_at'])); ?></p>
                            <div class="mt-auto">
                            <span class="badge bg-<?php echo $badge_class[$project['status']] ?? 'secondary'; ?>">
                                <?php echo $status_text[$project['status']] ?? $project['status']; ?>
                            </span>
                            </div>
                        </div>
                        <div class="project-card-footer">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-sm btn-info action-btn btn-view" title="Xem chi tiết"
                                    data-bs-toggle="modal" data-bs-target="#viewProjectModal"
                                    data-title="<?php echo htmlspecialchars($project['title']); ?>"
                                    data-client="<?php echo htmlspecialchars($project['client']); ?>"
                                    data-status-text="<?php echo htmlspecialchars($status_text[$project['status']] ?? $project['status']); ?>"
                                    data-status-class="<?php echo htmlspecialchars($badge_class[$project['status']] ?? 'secondary'); ?>"
                                    data-created-by="<?php echo htmlspecialchars($project['created_by_name'] ?? 'N/A'); ?>"
                                    data-created-at="<?php echo date('d/m/Y H:i', strtotime($project['created_at'])); ?>"
                                    data-description="<?php echo htmlspecialchars($project['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    data-image="<?php echo htmlspecialchars($project['preview_image']); ?>"
                                    data-video="<?php echo htmlspecialchars($project['preview_video']); ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="project-edit.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-warning action-btn" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa dự án này?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                    <button type="submit" formaction="project-edit.php" class="btn btn-sm btn-danger action-btn" title="Xóa" >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-center text-muted mt-5">Chưa có dự án nào</p>
                </div>
            <?php endif; ?>
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

    <!-- Modal Xem chi tiết -->
    <div class="modal fade" id="viewProjectModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalTitle">Chi tiết dự án</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-7">
                            <h2 id="viewTitle" class="mb-3"></h2>
                            <p><strong>Mô tả ngắn:</strong> <span id="viewClient" class="text-muted fst-italic"></span></p>
                            <hr>
                            <p><strong>Người tạo:</strong> <span id="viewCreatedBy"></span></p>
                            <p><strong>Ngày tạo:</strong> <span id="viewCreatedAt"></span></p>
                            <p><strong>Trạng thái:</strong> <span id="viewStatus" class="badge"></span></p>
                            <hr>
                            <p><strong>Mô tả chi tiết:</strong></p>
                            <div id="viewDescription" class="ql-editor" style="min-height: 400px; padding: 0;"></div>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <p><strong>Ảnh đại diện:</strong></p>
                                <img id="viewImage" class="img-fluid rounded" style="display:none; max-height: 300px; width: 100%; object-fit: cover;">
                            </div>
                            <div>
                                <p><strong>Video đại diện:</strong></p>
                                <video id="viewVideo" class="img-fluid rounded" style="display:none;" controls></video>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const viewProjectModal = document.getElementById('viewProjectModal');
        const viewDescriptionDiv = document.getElementById('viewDescription');

        viewProjectModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            document.getElementById('viewTitle').textContent = button.dataset.title;
            document.getElementById('viewClient').textContent = button.dataset.client;
            document.getElementById('viewCreatedBy').textContent = button.dataset.createdBy;
            document.getElementById('viewCreatedAt').textContent = button.dataset.createdAt;

            // Set content for the div
            viewDescriptionDiv.innerHTML = button.dataset.description || '<p class="text-muted">Không có mô tả chi tiết.</p>';

            const statusBadge = document.getElementById('viewStatus');
            statusBadge.textContent = button.dataset.statusText;
            statusBadge.className = 'badge bg-' + button.dataset.statusClass;
            const viewImage = document.getElementById('viewImage');
            viewImage.style.display = button.dataset.image ? 'block' : 'none';
            viewImage.src = button.dataset.image ? '../' + button.dataset.image : '';
            const viewVideo = document.getElementById('viewVideo');
            viewVideo.style.display = button.dataset.video ? 'block' : 'none';
            viewVideo.src = button.dataset.video ? '../' + button.dataset.video : '';
        });
        viewProjectModal.addEventListener('hide.bs.modal', function() {
            document.getElementById('viewVideo').pause();
        });
    });
    </script>
</body>
</html>