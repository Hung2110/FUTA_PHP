<?php
require_once 'auth_check.php';
$pageTitle = 'Quản Lý Tuyển Dụng';

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

function log_recruitment_activity($conn, $admin_id, $action)
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, module, ip) VALUES (?, ?, ?, ?)");
    $module = 'Recruitments';
    $stmt->bind_param("isss", $admin_id, $action, $module, $ip);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add' || $action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $branch = trim($_POST['work_location'] ?? '');
        $status = ($_POST['status'] ?? 'published') === 'published' ? 'open' : 'closed';

        // Ghép các trường mô tả thành một chuỗi duy nhất
        $description_parts = [];
        if (!empty($_POST['description'])) {
            $description_parts[] = "Mô tả công việc:\n" . trim($_POST['description']);
        }
        if (!empty($_POST['requirements'])) {
            $description_parts[] = "\nYêu cầu công việc:\n" . trim($_POST['requirements']);
        }
        if (!empty($_POST['benefits'])) {
            $description_parts[] = "\nQuyền lợi:\n" . trim($_POST['benefits']);
        }
        if (!empty($_POST['documents'])) {
            $description_parts[] = "\nDanh sách hồ sơ xin việc:\n" . trim($_POST['documents']);
        }
        // Thêm các thông tin khác vào description
        $description_parts[] = "\nNơi làm việc: " . trim($_POST['work_location'] ?? '');
        $description_parts[] = "Cấp bậc: " . trim($_POST['level'] ?? 'Nhân viên');
        $description_parts[] = "Số lượng: " . intval($_POST['quantity'] ?? 1);
        $description_parts[] = "Hình thức: " . trim($_POST['type'] ?? 'Toàn thời gian');
        $description_parts[] = "Kinh nghiệm: " . trim($_POST['experience'] ?? 'Không yêu cầu');
        $description_parts[] = "Mức lương: " . trim($_POST['salary'] ?? 'Thỏa thuận');
        $description_parts[] = "Ngành nghề: " . trim($_POST['industry'] ?? '');
        $description_parts[] = "Hạn chót nhận hồ sơ: " . trim($_POST['deadline'] ?? 'Không giới hạn');
        $description = implode("\n", $description_parts);
        
        if ($title === '') {
            $message = 'Vui lòng nhập chức danh tuyển dụng!';
            $message_type = 'danger';
        } else {
            // Luôn tự động tạo slug từ tiêu đề
            $slug = create_slug($title);

            if ($action === 'add') {
                // Bảng jobs không có industry, position. Có thể thêm sau nếu cần.
                $stmt = $conn->prepare("INSERT INTO jobs (title, branch, description, status) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $title, $branch, $description, $status);
                if ($stmt->execute()) {
                    $message = 'Thêm tin tuyển dụng thành công!';
                    $message_type = 'success';
                    log_recruitment_activity($conn, $_SESSION['admin_id'], "Thêm tin tuyển dụng: {$title}");
                    header('Location: recruitments.php?success=1');
                    exit;
                } else {
                    $message = 'Không thể thêm tin tuyển dụng: ' . $stmt->error;
                    $message_type = 'danger';
                }
                $stmt->close();
            } else {
                $stmt = $conn->prepare("UPDATE jobs SET title=?, branch=?, description=?, status=? WHERE id=?");
                $stmt->bind_param("ssssi", $title, $branch, $description, $status, $id);
                if ($stmt->execute()) {
                    $message = 'Cập nhật tin tuyển dụng thành công!';
                    $message_type = 'success';
                    log_recruitment_activity($conn, $_SESSION['admin_id'], "Cập nhật tin tuyển dụng ID: {$id}");
                    header('Location: recruitments.php?success=1');
                    exit;
                } else {
                    $message = 'Không thể cập nhật tin tuyển dụng: ' . $stmt->error;
                    $message_type = 'danger';
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            $message = 'Tin tuyển dụng không hợp lệ!';
            $message_type = 'danger';
        } else {
            $stmt = $conn->prepare("DELETE FROM jobs WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = 'Xóa tin tuyển dụng thành công!';
                $message_type = 'success';
                log_recruitment_activity($conn, $_SESSION['admin_id'], "Xóa tin tuyển dụng ID: {$id}");
                header('Location: recruitments.php?deleted=1');
                exit;
            } else {
                $message = 'Không thể xóa tin tuyển dụng: ' . $stmt->error;
                $message_type = 'danger';
            }
            $stmt->close();
        }
    }
}

if (isset($_GET['success'])) {
    $message = 'Thao tác thành công!';
    $message_type = 'success';
}

if (isset($_GET['deleted'])) {
    $message = 'Xóa tin tuyển dụng thành công!';
    $message_type = 'success';
}

$statsResult = $conn->query("
    SELECT
        COUNT(*) AS total_jobs,
        SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) AS open_jobs,
        SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) AS closed_jobs
    FROM jobs
");
$stats = $statsResult ? $statsResult->fetch_assoc() : ['total_jobs' => 0, 'open_jobs' => 0, 'closed_jobs' => 0];

$latestJobResult = $conn->query("SELECT title, created_at FROM jobs ORDER BY created_at DESC LIMIT 1");
$latestJob = $latestJobResult && $latestJobResult->num_rows > 0 ? $latestJobResult->fetch_assoc() : null;

$filter = isset($_GET['filter']) && in_array($_GET['filter'], ['open', 'closed']) ? $_GET['filter'] : 'all';
$jobs_query = "SELECT * FROM jobs";
if ($filter !== 'all') {
    $jobs_query .= " WHERE status = '" . $conn->real_escape_string($filter) . "'";
}
$jobs = $conn->query($jobs_query . " ORDER BY created_at DESC");

$edit_job = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM jobs WHERE id = {$edit_id} LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $edit_job = $result->fetch_assoc();

        // --- PARSE DESCRIPTION FOR EDIT FORM ---
        $description_content = $edit_job['description'] ?? '';
        $parsed_details = [
            'description' => '', 'requirements' => '', 'benefits' => '', 
            'documents' => '',
            'salary' => '', 'quantity' => '1', 'deadline' => '',
            'level' => '', 'type' => '', 'experience' => '', 'industry' => ''
        ];

        $lines = explode("\n", $description_content);
        $current_section = null;
        $description_parts = []; $requirements_parts = []; $benefits_parts = [];
        $documents_parts = [];

        foreach ($lines as $line) {
            $trimmed_line = trim($line);
            if (empty($trimmed_line)) continue;

            if (stripos($trimmed_line, 'Mô tả công việc:') === 0) {
                $current_section = 'description';
                $content = trim(substr($trimmed_line, strlen('Mô tả công việc:')));
                if (!empty($content)) $description_parts[] = $content;
            } elseif (stripos($trimmed_line, 'Yêu cầu:') === 0 || stripos($trimmed_line, 'Yêu cầu công việc:') === 0) {
                $current_section = 'requirements';
                $content = trim(preg_replace('/^Yêu cầu( công việc)?:/i', '', $trimmed_line));
                if (!empty($content)) $requirements_parts[] = $content;
            } elseif (stripos($trimmed_line, 'Phúc lợi:') === 0 || stripos($trimmed_line, 'Quyền lợi:') === 0) {
                $current_section = 'benefits';
                $content = trim(preg_replace('/^(Phúc lợi|Quyền lợi):/i', '', $trimmed_line));
                if (!empty($content)) $benefits_parts[] = $content;
            } elseif (stripos($trimmed_line, 'Danh sách hồ sơ xin việc:') === 0 || stripos($trimmed_line, 'Danh sách hồ sơ:') === 0) {
                $current_section = 'documents';
                $content = trim(preg_replace('/^Danh sách hồ sơ( xin việc)?:/i', '', $trimmed_line));
                if (!empty($content)) $documents_parts[] = $content;
            } elseif (stripos($trimmed_line, 'Nơi làm việc:') === 0) { $current_section = null;
            } elseif (stripos($trimmed_line, 'Mức lương:') === 0) { $current_section = null; $parsed_details['salary'] = trim(substr($trimmed_line, strlen('Mức lương:')));
            } elseif (stripos($trimmed_line, 'Số lượng:') === 0) { $current_section = null; $parsed_details['quantity'] = trim(substr($trimmed_line, strlen('Số lượng:')));
            } elseif (stripos($trimmed_line, 'Hạn nộp hồ sơ:') === 0 || stripos($trimmed_line, 'Hạn chót nhận hồ sơ:') === 0) { $current_section = null; $parsed_details['deadline'] = trim(preg_replace('/^Hạn (chót nhận|nộp) hồ sơ:/i', '', $trimmed_line));
            } elseif (stripos($trimmed_line, 'Cấp bậc:') === 0) { $current_section = null; $parsed_details['level'] = trim(substr($trimmed_line, strlen('Cấp bậc:')));
            } elseif (stripos($trimmed_line, 'Hình thức:') === 0 || stripos($trimmed_line, 'Hình thức làm việc:') === 0) { $current_section = null; $parsed_details['type'] = trim(str_replace(['Hình thức làm việc:', 'Hình thức:'], '', $trimmed_line));
            } elseif (stripos($trimmed_line, 'Kinh nghiệm:') === 0) { $current_section = null; $parsed_details['experience'] = trim(substr($trimmed_line, strlen('Kinh nghiệm:')));
            } elseif (stripos($trimmed_line, 'Ngành nghề:') === 0) { $current_section = null; $parsed_details['industry'] = trim(substr($trimmed_line, strlen('Ngành nghề:')));
            } elseif ($current_section === 'description') { $description_parts[] = $trimmed_line;
            } elseif ($current_section === 'requirements') { $requirements_parts[] = $trimmed_line;
            } elseif ($current_section === 'benefits') { $benefits_parts[] = $trimmed_line;
            } elseif ($current_section === 'documents') { $documents_parts[] = $trimmed_line;
            }
        }

        $parsed_details['description'] = implode("\n", $description_parts);
        $parsed_details['requirements'] = implode("\n", $requirements_parts);
        $parsed_details['benefits'] = implode("\n", $benefits_parts);
        $parsed_details['documents'] = implode("\n", $documents_parts);

        if (empty($parsed_details['description']) && empty($parsed_details['requirements']) && empty($parsed_details['benefits'])) {
            $parsed_details['description'] = $description_content;
        }
        
        $edit_job['parsed_details'] = $parsed_details;
    }
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
    <link rel="stylesheet" href="css/recruitments.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-briefcase text-primary me-2"></i>Quản Lý Tuyển Dụng</h1>
                <p class="mb-0">Kiểm soát toàn bộ tin đăng và hồ sơ ứng viên trên một giao diện duy nhất.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#jobModal">
                    <i class="fas fa-plus me-1"></i> Thêm tin tuyển dụng
                </button>
            </div>
        </div> 

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 table-filter-toolbar">
            <div class="btn-group filter-btn-group" role="group">
                <a href="recruitments.php" class="btn <?php echo ($filter ?? 'all') === 'all' ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                    Tất cả <span class="badge <?php echo ($filter ?? 'all') === 'all' ? 'bg-white text-primary' : 'bg-secondary'; ?> ms-1"><?php echo (int)($stats['total_jobs'] ?? 0); ?></span>
                </a>
                <a href="recruitments.php?filter=open" class="btn <?php echo ($filter ?? 'all') === 'open' ? 'btn-success fw-semibold' : 'btn-outline-secondary'; ?>">
                    <i class="far fa-check-circle me-1"></i>Đang tuyển <span class="badge <?php echo ($filter ?? 'all') === 'open' ? 'bg-white text-success' : 'bg-secondary'; ?> ms-1"><?php echo (int)($stats['open_jobs'] ?? 0); ?></span>
                </a>
                <a href="recruitments.php?filter=closed" class="btn <?php echo ($filter ?? 'all') === 'closed' ? 'btn-secondary text-white fw-semibold' : 'btn-outline-secondary'; ?>">
                    <i class="far fa-times-circle me-1"></i>Đã đóng <span class="badge <?php echo ($filter ?? 'all') === 'closed' ? 'bg-dark text-white' : 'bg-secondary'; ?> ms-1"><?php echo (int)($stats['closed_jobs'] ?? 0); ?></span>
                </a>
            </div>
            <div class="text-muted small filter-count-info">
                <i class="fas fa-briefcase me-1 text-primary"></i> Tổng số: <strong><?php echo (int)($stats['total_jobs'] ?? 0); ?></strong> tin tuyển dụng
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3" style="min-width: 50px;">ID</th>
                                <th style="min-width: 160px;">Công việc</th>
                                <th style="min-width: 110px;">Nơi làm việc</th>
                                <th style="min-width: 95px;">Trạng thái</th>
                                <th style="min-width: 140px;">Mô tả ngắn</th>
                                <th style="min-width: 90px;">Lương</th>
                                <th style="min-width: 100px;">Ngày tạo</th>
                                <th class="text-end pe-3" style="min-width: 90px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($jobs && $jobs->num_rows > 0): ?>
                                <?php while($job = $jobs->fetch_assoc()): ?>
                                    <tr>
                                        <td data-label="Mã ID"><strong>#<?php echo $job['id']; ?></strong></td>
                                        <td data-label="Công việc">
                                            <p class="job-title mb-1 fw-bold" style="line-height: 1.35;"><?php echo htmlspecialchars($job['title']); ?></p>
                                            <p class="job-meta mb-0">
                                                <a href="../recruitment-detail.php?id=<?php echo $job['id']; ?>&preview=1" target="_blank" class="text-decoration-none small"><i class="fas fa-external-link-alt fa-xs"></i> Xem tin</a>
                                            </p>
                                        </td>
                                        <td data-label="Nơi làm việc"><?php echo htmlspecialchars($job['branch'] ?? '—'); ?></td>
                                        <td data-label="Trạng thái">
                                            <span class="badge <?php echo $job['status'] === 'open' ? 'badge-open' : 'badge-closed'; ?>">
                                                <?php echo $job['status'] === 'open' ? 'Đang tuyển' : 'Đã đóng'; ?>
                                            </span>
                                        </td>
                                        <td data-label="Mô tả">
                                            <div class="description-snippet text-muted text-expandable" style="font-size: 0.88rem; line-height: 1.5;" title="Bấm để xem đầy đủ">
                                                <?php echo htmlspecialchars(strip_tags($job['description'])); ?>
                                            </div>
                                        </td>
                                        <td data-label="Lương"><span class="text-primary fw-bold">Thỏa thuận</span></td>
                                        <td data-label="Ngày tạo" style="font-size: 13px; color: #6b7280;"><?php echo date('d/m/Y H:i', strtotime($job['created_at'])); ?></td>
                                        <td data-label="Thao tác" class="text-end">
                                            <div class="table-actions">
                                                <a href="?edit=<?php echo $job['id']; ?>" class="btn btn-sm btn-warning" title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" title="Xóa" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php echo $job['id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="empty-state">
                                        <i class="fas fa-briefcase"></i>
                                        <p class="mb-0">Chưa có tin tuyển dụng nào</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="jobModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-<?php echo $edit_job ? 'edit' : 'plus-circle'; ?> me-2"></i>
                        <?php echo $edit_job ? 'Cập nhật tin tuyển dụng' : 'Thêm tin tuyển dụng mới'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                        <input type="hidden" name="action" value="<?php echo $edit_job ? 'edit' : 'add'; ?>">
                        <?php if ($edit_job): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_job['id']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Chức danh (Tiêu đề) *</label>
                            <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($edit_job['title'] ?? ''); ?>" required placeholder="VD: Nhân viên Marketing">
                        </div>

                        <h6 class="mt-4 mb-3 text-primary border-bottom pb-2">Thông tin chung</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nơi làm việc (Chi nhánh)</label>
                                <input type="text" class="form-control" name="work_location" value="<?php echo htmlspecialchars($edit_job['branch'] ?? ''); ?>" placeholder="VD: TP. Hồ Chí Minh">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cấp bậc</label>
                                <input type="text" class="form-control" name="level" value="<?php echo htmlspecialchars($edit_job['parsed_details']['level'] ?? ''); ?>" placeholder="VD: Nhân viên">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hình thức làm việc</label>
                                <input type="text" class="form-control" name="type" value="<?php echo htmlspecialchars($edit_job['parsed_details']['type'] ?? ''); ?>" placeholder="VD: Toàn thời gian">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kinh nghiệm</label>
                                <input type="text" class="form-control" name="experience" value="<?php echo htmlspecialchars($edit_job['parsed_details']['experience'] ?? ''); ?>" placeholder="VD: 1 năm">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngành nghề</label>
                                <input type="text" class="form-control" name="industry" value="<?php echo htmlspecialchars($edit_job['parsed_details']['industry'] ?? ''); ?>" placeholder="VD: Vận Tải, Kho Vận">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mức lương</label>
                                <input type="text" class="form-control" name="salary" value="<?php echo htmlspecialchars($edit_job['parsed_details']['salary'] ?? ''); ?>" placeholder="VD: 15 - 20 triệu hoặc Thỏa thuận">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số lượng</label>
                                <input type="number" class="form-control" name="quantity" value="<?php echo htmlspecialchars($edit_job['parsed_details']['quantity'] ?? '1'); ?>" min="1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hạn chót nhận hồ sơ</label>
                                <input type="date" class="form-control" name="deadline" value="<?php echo htmlspecialchars($edit_job['parsed_details']['deadline'] ?? ''); ?>">
                            </div>
                        </div>

                        <h6 class="mt-4 mb-3 text-primary border-bottom pb-2">Nội dung chi tiết</h6>
                        <?php
                        $default_req = "- Tốt nghiệp THPT;\n\n- Biết sử dụng máy tính;\n\n- 18 – 40 tuổi;\n\n- Sức khỏe tốt;";
                        $default_docs = "- Đơn xin việc;\n\n- Sơ yếu lý lịch;\n\n- Hình 3 x 4;\n\n- CMND/ CCCD/ Giấy Thông Báo Mã Định Danh Cá Nhân;\n\n- Bằng Cấp 3\n\n- Giấy Khám Sức Khoẻ;";
                        ?>
                        <div class="mb-3">
                            <label class="form-label">Mô tả công việc (chi tiết)</label>
                            <textarea class="form-control" name="description" rows="4" placeholder="- Gạch đầu dòng các mô tả..."><?php echo htmlspecialchars($edit_job['parsed_details']['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Yêu cầu công việc</label>
                            <textarea class="form-control" name="requirements" rows="6"><?php echo htmlspecialchars($edit_job ? ($edit_job['parsed_details']['requirements'] ?? '') : $default_req); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quyền lợi</label>
                            <textarea class="form-control" name="benefits" rows="4" placeholder="Mỗi dòng 1 quyền lợi (VD: Bảo hiểm theo quy định)"><?php echo htmlspecialchars($edit_job['parsed_details']['benefits'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Danh sách hồ sơ xin việc</label>
                            <textarea class="form-control" name="documents" rows="8"><?php echo htmlspecialchars($edit_job ? ($edit_job['parsed_details']['documents'] ?? '') : $default_docs); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Trạng thái</label>
                            <select class="form-select" name="status">
                                <option value="published" <?php echo ($edit_job['status'] ?? 'open') === 'open' ? 'selected' : ''; ?>>Đang tuyển (Published)</option>
                                <option value="draft" <?php echo ($edit_job['status'] ?? '') === 'closed' ? 'selected' : ''; ?>>Đã đóng (Draft)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #e5e7eb; padding: 16px 24px;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-<?php echo $edit_job ? 'save' : 'plus'; ?> me-1"></i>
                            <?php echo $edit_job ? 'Cập nhật' : 'Thêm mới'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Modal xác nhận xóa -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" id="deleteForm">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-trash text-danger me-2"></i>Xác nhận xóa tin tuyển dụng</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Bạn có chắc chắn muốn xóa tin tuyển dụng này? Thao tác này không thể hoàn tác.</p>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" id="deleteJobId">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-danger">Xóa</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="js/recruitments.js"></script>
</body>
</html>
