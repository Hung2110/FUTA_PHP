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

$jobs = $conn->query("SELECT * FROM jobs ORDER BY created_at DESC");

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
    <link rel="icon" href="/FUTA_PHP/assets/images/logo/futa.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --primary: #007bff;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --dark: #343a40;
            --light: #f8f9fa;
        }

        body {
            background: #f7f9fc;
            color: #1f2a37;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .page-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 30px;
            padding: 25px;
        }

        .page-header h1 {
            font-weight: 700;
            font-size: 1.75rem;
            margin: 0;
            color: #1f2a37;
        }

        .page-header p {
            color: #6b7280;
            margin: 5px 0 0;
            font-size: 14px;
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            border: none;
            color: #fff;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,123,255,0.3);
            text-decoration: none;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,123,255,0.4);
            color: #fff;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid var(--primary);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        }

        .stat-card:nth-child(2) {
            border-left-color: var(--success);
        }

        .stat-card:nth-child(3) {
            border-left-color: var(--danger);
        }

        .stat-card:nth-child(4) {
            border-left-color: var(--info);
        }

        .stat-card h6 {
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 1px;
            color: #6b7280;
            font-weight: 600;
            margin: 0 0 12px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin: 8px 0;
            color: #1f2a37;
        }

        .stat-trend {
            font-size: 13px;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
            background: #fff;
        }

        .card-body {
            padding: 0;
        }

        .table {
            margin: 0;
        }

        .table thead th {
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 11px;
            color: #6b7280;
            border-bottom: 2px solid #e5e7eb;
            background: #f9fafb;
            padding: 16px;
            font-weight: 600;
        }

        .table tbody td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
        }

        .table tbody tr:hover {
            background: #f9fafb;
        }

        .job-title {
            font-weight: 600;
            margin-bottom: 4px;
            color: #1f2a37;
            font-size: 15px;
        }

        .job-meta {
            font-size: 12px;
            color: #6b7280;
            margin: 0;
        }

        .badge-open {
            background: rgba(40,167,69,0.1);
            color: #28a745;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }

        .badge-closed {
            background: rgba(108,117,125,0.1);
            color: #6c757d;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }

        .description-snippet {
            max-width: 400px;
            color: #4b5563;
            font-size: 13px;
            line-height: 1.5;
        }

        .file-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            background: rgba(0,123,255,0.1);
            color: #007bff;
            font-size: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .file-pill:hover {
            background: #007bff;
            color: #fff;
            transform: translateY(-1px);
        }

        .table-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .table-actions .btn {
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 13px;
        }

        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 20px 24px;
            background: #f9fafb;
        }

        .modal-title {
            font-weight: 700;
            font-size: 1.25rem;
            color: #1f2a37;
        }

        .modal-body {
            padding: 24px;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 10px 14px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
            border-color: #007bff;
            outline: none;
        }

        .note-muted {
            font-size: 12px;
            color: #6b7280;
            margin-top: 8px;
        }

        .note-muted a {
            color: #007bff;
            text-decoration: none;
        }

        .note-muted a:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 14px 20px;
            margin-bottom: 24px;
        }

        @media (max-width: 992px) {
            .stat-grid {
                grid-template-columns: 1fr;
            }
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-briefcase me-2 text-primary"></i>Quản Lý Tuyển Dụng</h1>
                <p class="mb-0">Kiểm soát toàn bộ tin đăng và hồ sơ ứng viên trên một giao diện duy nhất.</p>
            </div>
            <button class="cta-button" data-bs-toggle="modal" data-bs-target="#jobModal">
                <i class="fas fa-plus"></i> Thêm tin tuyển dụng
            </button>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <h6>Tổng số tin</h6>
                <div class="stat-value"><?php echo number_format($stats['total_jobs'] ?? 0); ?></div>
                <div class="stat-trend"><i class="fas fa-chart-line"></i>Tất cả trạng thái</div>
            </div>
            <div class="stat-card">
                <h6>Đang tuyển</h6>
                <div class="stat-value text-success"><?php echo number_format($stats['open_jobs'] ?? 0); ?></div>
                <div class="stat-trend text-success"><i class="fas fa-check-circle"></i>Hiển thị trên website</div>
            </div>
            <div class="stat-card">
                <h6>Đã đóng</h6>
                <div class="stat-value text-secondary"><?php echo number_format($stats['closed_jobs'] ?? 0); ?></div>
                <div class="stat-trend text-secondary"><i class="fas fa-circle-xmark"></i>Ẩn với ứng viên</div>
            </div>
            <div class="stat-card">
                <h6>Cập nhật gần nhất</h6>
                <div class="stat-value" style="font-size:20px;">
                    <?php echo $latestJob ? date('d/m/Y', strtotime($latestJob['created_at'])) : '-'; ?>
                </div>
                <div class="stat-trend text-primary">
                    <?php echo $latestJob ? htmlspecialchars($latestJob['title']) : 'Chưa có dữ liệu'; ?>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
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
                                <th>ID</th>
                                <th>Công việc</th>
                                <th>Nơi làm việc</th>
                                <th>Trạng thái</th>
                                <th>Mô tả ngắn</th>
                                <th>Lương</th>
                                <th>Ngày tạo</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($jobs && $jobs->num_rows > 0): ?>
                                <?php while($job = $jobs->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>#<?php echo $job['id']; ?></strong></td>
                                        <td>
                                            <p class="job-title mb-1"><?php echo htmlspecialchars($job['title']); ?></p>
                                            <p class="job-meta mb-0">
                                                <a href="../recruitment-detail.php?id=<?php echo $job['id']; ?>&preview=1" target="_blank" class="text-decoration-none small"><i class="fas fa-external-link-alt fa-xs"></i> Xem tin</a>
                                            </p>
                                        </td>
                                        <td><?php echo htmlspecialchars($job['branch'] ?? '—'); ?></td>
                                        <td>
                                            <span class="badge <?php echo $job['status'] === 'open' ? 'badge-open' : 'badge-closed'; ?>">
                                                <?php echo $job['status'] === 'open' ? 'Đang tuyển' : 'Đã đóng'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="description-snippet">
                                                <?php echo htmlspecialchars(substr($job['description'], 0, 100)) . '...'; ?>
                                            </div>
                                        </td>
                                        <td><span class="text-primary fw-bold">Thỏa thuận</span></td>
                                        <td style="font-size: 13px; color: #6b7280;"><?php echo date('d/m/Y H:i', strtotime($job['created_at'])); ?></td>
                                        <td>
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

        <script>
        // Gán id vào modal xóa
        var deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var jobId = button.getAttribute('data-id');
            document.getElementById('deleteJobId').value = jobId;
        });

        // Focus vào input đầu tiên khi mở modal thêm/sửa
        <?php if ($edit_job): ?>
        var jobModal = new bootstrap.Modal(document.getElementById('jobModal'));
        jobModal.show();
        setTimeout(function() {
            document.querySelector('#jobModal input[name="title"]').focus();
            document.getElementById('jobModal').scrollTo(0,0);
        }, 400);
        <?php endif; ?>

        // Loading khi submit form thêm/sửa
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                var btn = form.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang xử lý...';
                }
            });
        });

        // Chặn submit lại khi nhấn Enter ngoài input
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && e.target.tagName !== 'INPUT') {
                e.preventDefault();
            }
        });
        </script>
</body>
</html>
