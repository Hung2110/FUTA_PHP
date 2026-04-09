<?php
require_once 'db.php';

$applicationMessage = '';
$applicationType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_application'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $cvFile = $_FILES['cv_file'] ?? null;

    if ($fullname === '' || $email === '' || $phone === '' || $position === '') {
        $applicationMessage = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
        $applicationType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $applicationMessage = 'Email không hợp lệ.';
        $applicationType = 'error';
    } elseif (!$cvFile || $cvFile['error'] !== UPLOAD_ERR_OK) {
        $applicationMessage = 'Vui lòng tải lên CV hợp lệ.';
        $applicationType = 'error';
    } else {
        $allowedExtensions = ['pdf', 'doc', 'docx'];
        $extension = strtolower(pathinfo($cvFile['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions, true)) {
            $applicationMessage = 'Định dạng CV không được hỗ trợ. Chỉ chấp nhận PDF, DOC, DOCX.';
            $applicationType = 'error';
        } else {
            $uploadDir = 'uploads/cv/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $safeFileName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', pathinfo($cvFile['name'], PATHINFO_FILENAME));
            $fileName = $safeFileName . '_' . time() . '.' . $extension;
            $uploadPath = $uploadDir . $fileName;

            if (move_uploaded_file($cvFile['tmp_name'], $uploadPath)) {
                $stmt = $conn->prepare("INSERT INTO applications (fullname, email, phone, position, message, cv_file) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $fullname, $email, $phone, $position, $message, $uploadPath);

                if ($stmt->execute()) {
                    $application_id = $conn->insert_id;
                    $applicationMessage = 'Ứng tuyển thành công! Chúng tôi sẽ liên hệ với bạn sớm.';
                    $applicationType = 'success';

                    // --- Tạo thông báo cho admin/manager ---
                    $admin_users_query = $conn->query("SELECT id FROM users WHERE role IN ('admin', 'manager')");
                    if ($admin_users_query) {
                        $notification_message = "Đơn ứng tuyển mới cho vị trí '" . htmlspecialchars($position) . "' từ " . htmlspecialchars($fullname);
                        $notification_link = "view_application.php?id=" . $application_id;
                        $notification_type = 'application';

                        $notify_stmt = $conn->prepare("INSERT INTO notifications (user_id, type, message, link) VALUES (?, ?, ?, ?)");
                        while ($admin_user = $admin_users_query->fetch_assoc()) {
                            $notify_stmt->bind_param("isss", $admin_user['id'], $notification_type, $notification_message, $notification_link);
                            $notify_stmt->execute();
                        }
                        $notify_stmt->close();
                    }
                    // --- Kết thúc tạo thông báo ---
                } else {
                    $applicationMessage = 'Không thể gửi đơn ứng tuyển. Vui lòng thử lại.';
                    $applicationType = 'error';
                }
                $stmt->close();
            } else {
                $applicationMessage = 'Không thể tải lên tệp CV. Vui lòng thử lại.';
                $applicationType = 'error';
            }
        }
    }
}

// Giả sử slug được truyền qua URL, ví dụ: /FUTA_PHP/recruitment-detail.php?slug=ten-cong-viec
$job_id = intval($_GET['id'] ?? 0);

if ($job_id <= 0) {
    http_response_code(404);
    echo "Không tìm thấy tin tuyển dụng.";
    exit;
}

if (isset($_GET['preview']) && $_GET['preview'] == 1) {
    // Chế độ xem trước từ admin: không kiểm tra status
    $stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ? LIMIT 1");
} else {
    // Chế độ xem công khai: chỉ hiển thị tin 'open'
    $stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ? AND status = 'open' LIMIT 1");
}
$stmt->bind_param('i', $job_id);
$stmt->execute();
$result = $stmt->get_result();
$job = $result->fetch_assoc();
$stmt->close();
if (!$job) {
    http_response_code(404);
    // Bạn có thể chuyển hướng đến trang 404 tùy chỉnh
    echo "Tin tuyển dụng không tồn tại hoặc đã hết hạn.";
    exit;
}

// --- Parse description string for structured display ---
$description_content = $job['description'] ?? '';
$details = [
    'description' => '',
    'requirements' => '',
    'benefits' => '',
    'work_location' => $job['branch'], // Get from branch column to be sure
    'salary' => 'Thỏa thuận',
    'quantity' => '1',
    'deadline' => 'Không giới hạn',
    'level' => 'Nhân viên',
    'type' => 'Toàn thời gian',
    'experience' => 'Không yêu cầu',
    'industry' => 'Quảng cáo'
];

$lines = explode("\n", $description_content);
$current_section = null;
$description_parts = [];
$requirements_parts = [];
$benefits_parts = [];

foreach ($lines as $line) {
    $trimmed_line = trim($line);
    if (empty($trimmed_line)) continue;

    if (stripos($trimmed_line, 'Mô tả công việc:') === 0) {
        $current_section = 'description';
        $content = trim(substr($trimmed_line, strlen('Mô tả công việc:')));
        if (!empty($content)) $description_parts[] = $content;
    } elseif (stripos($trimmed_line, 'Yêu cầu công việc:') === 0) {
        $current_section = 'requirements';
        $content = trim(substr($trimmed_line, strlen('Yêu cầu công việc:')));
        if (!empty($content)) $requirements_parts[] = $content;
    } elseif (stripos($trimmed_line, 'Quyền lợi:') === 0) {
        $current_section = 'benefits';
        $content = trim(substr($trimmed_line, strlen('Quyền lợi:')));
        if (!empty($content)) $benefits_parts[] = $content;
    } elseif (stripos($trimmed_line, 'Nơi làm việc:') === 0) {
        $current_section = null;
        $details['work_location'] = trim(substr($trimmed_line, strlen('Nơi làm việc:')));
    } elseif (stripos($trimmed_line, 'Mức lương:') === 0) {
        $current_section = null;
        $details['salary'] = trim(substr($trimmed_line, strlen('Mức lương:')));
    } elseif (stripos($trimmed_line, 'Số lượng:') === 0) {
        $current_section = null;
        $details['quantity'] = trim(substr($trimmed_line, strlen('Số lượng:')));
    } elseif (stripos($trimmed_line, 'Cấp bậc:') === 0) {
        $current_section = null;
        $details['level'] = trim(substr($trimmed_line, strlen('Cấp bậc:')));
    } elseif (stripos($trimmed_line, 'Hình thức:') === 0 || stripos($trimmed_line, 'Hình thức làm việc:') === 0) {
        $current_section = null;
        $details['type'] = trim(str_replace(['Hình thức làm việc:', 'Hình thức:'], '', $trimmed_line));
    } elseif (stripos($trimmed_line, 'Kinh nghiệm:') === 0) {
        $current_section = null;
        $details['experience'] = trim(substr($trimmed_line, strlen('Kinh nghiệm:')));
    } elseif (stripos($trimmed_line, 'Ngành nghề:') === 0) {
        $current_section = null;
        $details['industry'] = trim(substr($trimmed_line, strlen('Ngành nghề:')));
    } elseif (stripos($trimmed_line, 'Hạn nộp hồ sơ:') === 0) {
        $current_section = null;
        $details['deadline'] = trim(substr($trimmed_line, strlen('Hạn nộp hồ sơ:')));
    } elseif ($current_section === 'description') {
        $description_parts[] = $trimmed_line;
    } elseif ($current_section === 'requirements') {
        $requirements_parts[] = $trimmed_line;
    } elseif ($current_section === 'benefits') {
        $benefits_parts[] = $trimmed_line;
    }
}

$details['description'] = implode("\n", $description_parts);
$details['requirements'] = implode("\n", $requirements_parts);
$details['benefits'] = implode("\n", $benefits_parts);

// If no section is parsed, display the entire original content
if (empty($details['description']) && empty($details['requirements']) && empty($details['benefits'])) {
    $details['description'] = $description_content;
}

$pageStyles = ['css/recruitment-detail.css'];
$bodyClass = 'recruitment-detail-page';
include 'includes/header.php';
?>

<style>
    .recruitment-detail-container {
        padding-top: 2rem;
        padding-bottom: 3rem;
    }
    .job-content-wrapper, .job-summary-card {
        background: #fff;
        padding: 2.5rem;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
        border: 1px solid #f0f0f0;
    }
    .job-title-main {
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }
    .job-post-date {
        color: #6c757d;
        margin-bottom: 2rem;
        font-size: 0.9rem;
        font-style: italic;
    }
    .detail-subtitle {
        font-size: 1.25rem;
        font-weight: 700;
        color: #004aad;
        margin-top: 2rem;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #eef2f7;
        display: flex;
        align-items: center;
        gap: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .detail-subtitle i {
        color: #007bff;
    }
    .job-full-description {
        line-height: 1.7;
        color: #4a4a4a;
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }
    .job-summary-card {
        position: sticky;
        top: 20px;
        background: #f8f9fa;
        border: none;
    }
    
    .summary-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #dee2e6;
    }
    
    .summary-list li strong {
        color: #333;
        font-weight: 600;
        min-width: 120px;
        display: inline-block;
    }
    .btn-apply-now {
        width: 100%;
        padding: 14px;
        font-size: 1rem;
        font-weight: 700;
        text-transform: uppercase;
        border-radius: 8px;
        background: linear-gradient(135deg, #004aad 0%, #007bff 100%);
        border: none;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 74, 173, 0.3);
        margin-top: 1rem;
    }
    .recruitment-banner {
        background: #f4f6f9;
        padding: 3rem 0;
        text-align: center;
        margin-bottom: 0;
    }
    .recruitment-banner h1 {
        color: #004aad;
        font-weight: 800;
        margin: 0;
    }
    /* Styles for Benefits Section */
    .benefits-wrapper {
        margin-top: 1rem;
        padding-bottom: 10px;
    }
    .ico-ttd {
        margin-right: 8px;
        color: #004aad;
    }
    .pull-left {
        float: left !important;
    }
    .section-title-benefits {
        font-size: 1.25rem;
        font-weight: 700;
        color: #004aad;
        margin-top: 2rem;
        margin-bottom: 0.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #eef2f7;
        display: flex;
        align-items: center;
    }
    /* Icons and Section Styles */
    .job-post-date i {
        margin-right: 8px;
    }
    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1a1a;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #eef2f7;
        margin-bottom: 1.5rem;
        margin-top: 2rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .section-title i {
        color: #004aad;
        font-size: 1.2rem;
    }
    .contact-info p {
        margin-bottom: 0.8rem;
        align-items: flex-start;
    }
    .contact-info p i {
        color: #004aad;
        margin-right: 10px;
        margin-top: 5px;
        width: 20px;
        text-align: center;
        flex-shrink: 0;
    }
    /* New Summary Card Layout */
    .job-summary-card {
        padding: 1.75rem;
        border: 1px solid #e9ecef;
    }
    .summary-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
    .summary-list li {
        display: flex;
        align-items: flex-start;
        margin-bottom: 0;
    }
    .summary-list .ico-ttd {
        margin-right: 12px;
        margin-top: 3px;
        font-size: 1.1rem;
    }
    .summary-item-content {
        display: flex;
        flex-direction: column;
    }
    .summary-item-content strong {
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 500;
        margin-bottom: 2px;
    }
    .summary-item-content span {
        font-weight: 600;
        color: #212529;
        font-size: 0.95rem;
    }
    @media (max-width: 992px) {
        .summary-list {
            grid-template-columns: 1fr;
        }
    }

    /* Modal Styles from recruitment.css */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        justify-content: center;
        align-items: center;
        z-index: 9999;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .modal-content {
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0px 10px 40px rgba(0,0,0,0.3);
        position: relative;
        animation: slideUp 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    @keyframes slideUp {
        from {
            transform: translateY(30px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-content h2 {
        margin-top: 0;
        margin-bottom: 8px;
        color: #007bff;
        font-size: 24px;
        font-weight: 700;
    }

    .modal-content #applyJobTitle {
        color: #666;
        margin-bottom: 20px;
        font-size: 15px;
        font-weight: 500;
    }

    .apply-form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .modal-content label {
        display: block;
        margin: 0 0 5px;
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .modal-content input, 
    .modal-content textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
        font-family: inherit;
    }

    .modal-content input:focus, 
    .modal-content textarea:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .modal-content button[type="submit"] {
        margin-top: 10px;
        background: #007bff;
        color: #fff;
        border: none;
        padding: 14px 25px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 123, 255, 0.2);
    }

    .modal-content button[type="submit"]:hover {
        background: #0056b3;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
    }

    .close-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 28px;
        color: #999;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.3s ease;
        background: #f1f1f1;
        cursor: pointer;
    }

    .close-btn:hover {
        background: #e0e0e0;
        color: #333;
        transform: rotate(90deg);
    }

    /* Thông báo ứng tuyển */
    .application-alert {
        max-width: 800px; /* Điều chỉnh lại cho phù hợp hơn */
        margin: 20px auto;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 1rem;
        animation: slideDown 0.3s ease-out;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border-left-width: 5px;
        border-left-style: solid;
    }

    .application-alert::before {
        font-family: "Font Awesome 5 Free";
        font-weight: 900; /* For solid icons */
        font-size: 1.2rem;
    }

    .application-alert.success {
        background-color: #f0fff4;
        color: #2f6f44;
        border-color: #48bb78;
    }
    .application-alert.success::before {
        content: "\f058"; /* fa-check-circle */
        color: #48bb78;
    }

    .application-alert.error {
        background-color: #fff5f5;
        color: #c53030;
        border-color: #f56565;
    }
    .application-alert.error::before {
        content: "\f071"; /* fa-exclamation-triangle */
        color: #f56565;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="recruitment-banner">
    <h1>Chi Tiết Tin Tuyển Dụng</h1>
</div>

<!-- Thông báo ứng tuyển -->
<?php if ($applicationMessage): ?>
    <div class="application-alert <?php echo $applicationType === 'success' ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($applicationMessage, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<main class="container recruitment-detail-container">
    <div class="row">
        <!-- Cột nội dung chính -->
        <div class="col-lg-8">
            <div class="job-content-wrapper">
                <h1 class="job-title-main"><?php echo htmlspecialchars($job['title']); ?></h1>
                <p class="job-post-date"><i class="far fa-calendar-alt"></i> Ngày đăng: <?php echo date('d/m/Y', strtotime($job['created_at'])); ?></p>
                
                <div class="job-detail-section">
                    <h3 class="section-title"><i class="fas fa-info-circle"></i> Chi tiết công việc</h3>
                    <?php if (!empty($details['description'])): ?>
                        <h4 class="detail-subtitle"><i class="fas fa-briefcase"></i> Mô tả công việc</h4>
                        <div class="job-full-description"><?php echo nl2br(htmlspecialchars($details['description'])); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($details['requirements'])): ?>
                        <h4 class="detail-subtitle"><i class="fas fa-user-check"></i> Yêu cầu công việc</h4>
                        <div class="job-full-description"><?php echo nl2br(htmlspecialchars($details['requirements'])); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($details['benefits'])): ?>
                        <label class="section-title-benefits"><i class="fa fa-grin pull-left ico-ttd"></i> Phúc lợi:</label>
                        <div class="benefits-wrapper">
                            <?php 
                            $benefit_lines = explode("\n", $details['benefits']);
                            $benefit_lines = array_filter(array_map('trim', $benefit_lines));
                            $chunks = array_chunk($benefit_lines, 3);
                            
                            foreach ($chunks as $chunk) {
                                echo '<div class="row" style="padding-top: 10px;">';
                                foreach ($chunk as $line) {
                                    // Map icons based on keywords to match the requested style
                                    $icon = 'fa-check-circle';
                                    $lower_line = mb_strtolower($line, 'UTF-8');
                                    
                                    if (strpos($lower_line, 'bảo hiểm') !== false) $icon = 'fa-first-aid';
                                    elseif (strpos($lower_line, 'du lịch') !== false) $icon = 'fa-plane';
                                    elseif (strpos($lower_line, 'phụ cấp') !== false) $icon = 'fa-money-bill-alt';
                                    elseif (strpos($lower_line, 'đồng phục') !== false) $icon = 'fa-tshirt';
                                    elseif (strpos($lower_line, 'thưởng') !== false) $icon = 'fa-dollar-sign';
                                    elseif (strpos($lower_line, 'sức khỏe') !== false || strpos($lower_line, 'khám') !== false) $icon = 'fa-user-md';
                                    elseif (strpos($lower_line, 'đào tạo') !== false) $icon = 'fa-graduation-cap';
                                    elseif (strpos($lower_line, 'tăng lương') !== false) $icon = 'fa-chart-line';
                                    elseif (strpos($lower_line, 'nghỉ phép') !== false) $icon = 'fa-briefcase';
                                    elseif (strpos($lower_line, 'lương') !== false) $icon = 'fa-money-bill-wave';

                                    echo '<div class="col-md-4">';
                                    echo '<span><i class="fa ' . $icon . ' pull-left ico-ttd"></i> ' . htmlspecialchars($line) . '</span>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="job-detail-section contact-info">
                    <h3 class="section-title"><i class="fas fa-paper-plane"></i> Thông tin liên hệ & Ứng tuyển</h3>
                    <p><i class="fas fa-envelope"></i> Để ứng tuyển, vui lòng gửi CV và các giấy tờ liên quan về địa chỉ email: <strong>futaadvertising@futa.vn</strong></p>
                    <p><i class="fas fa-pen"></i> Tiêu đề email ghi rõ: "Ứng tuyển vị trí [<?php echo htmlspecialchars($job['title']); ?>] - [Họ và tên]"</p>
                    <p><i class="fas fa-phone-alt"></i> Hoặc liên hệ qua số điện thoại: <strong>1900 6912 </strong> để được hướng dẫn.</p>
                </div>
            </div>
        </div>

        <!-- Cột thông tin tóm tắt -->
        <div class="col-lg-4">
            <div class="job-summary-card">
                <h4 class="summary-title">Thông tin chung</h4>
                <?php
                    $summary_items = [
                        ['icon' => 'fa-map-marker-alt', 'label' => 'Nơi làm việc', 'value' => $details['work_location']],
                        ['icon' => 'fa-dollar-sign', 'label' => 'Mức lương', 'value' => $details['salary']],
                        ['icon' => 'fa-users', 'label' => 'Cấp bậc', 'value' => $details['level']],
                        ['icon' => 'fa-user-friends', 'label' => 'Số lượng', 'value' => $details['quantity']],
                        ['icon' => 'fa-user-clock', 'label' => 'Hình thức', 'value' => $details['type']],
                        ['icon' => 'fa-star', 'label' => 'Kinh nghiệm', 'value' => $details['experience']],
                        ['icon' => 'fa-briefcase', 'label' => 'Ngành nghề', 'value' => $details['industry']],
                        ['icon' => 'fa-calendar-times', 'label' => 'Hạn chót', 'value' => $details['deadline']],
                    ];
                ?>
                <ul class="summary-list">
                    <?php foreach ($summary_items as $item): ?>
                        <li>
                            <i class="fa <?php echo $item['icon']; ?> ico-ttd"></i>
                            <div class="summary-item-content">
                                <strong><?php echo $item['label']; ?></strong>
                                <span><?php echo htmlspecialchars($item['value']); ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <button onclick="openApplyModal('<?php echo htmlspecialchars($job['title'], ENT_QUOTES); ?>')" class="btn-apply-now">Ứng tuyển ngay</button>
            </div>
        </div>
    </div>
</main>

<!-- Modal ứng tuyển -->
<div class="modal" id="applyModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('applyModal')">&times;</span>
        <h2 data-i18n="recruitment.modal_title">Nộp hồ sơ ứng tuyển</h2>
        <p id="applyJobTitle"></p>
        <form method="POST" enctype="multipart/form-data" class="apply-form">
            <input type="hidden" name="position" id="applicationPosition">
            <label data-i18n="recruitment.modal_name">Họ và tên *</label>
            <input type="text" id="applicantName" name="fullname" required>
            <label data-i18n="recruitment.modal_email">Email *</label>
            <input type="email" id="applicantEmail" name="email" required>
            <label data-i18n="recruitment.modal_phone">Số điện thoại *</label>
            <input type="text" id="applicantPhone" name="phone" required>
            <label>Thông điệp</label>
            <textarea id="applicantMessage" name="message" rows="3" placeholder="Chia sẻ thêm về bản thân..."></textarea>
            <label data-i18n="recruitment.modal_cv">CV/Resume (PDF, DOC, DOCX) *</label>
            <input type="file" id="applicantCV" name="cv_file" accept=".pdf,.doc,.docx" required>
            <button type="submit" name="submit_application" value="1" data-i18n="recruitment.modal_submit">Gửi đơn</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    function openModal(id) {
        document.getElementById(id).style.display = "flex";
    }

    function closeModal(id) {
        document.getElementById(id).style.display = "none";
    }

    function openApplyModal(jobTitle) {
        document.getElementById("applyJobTitle").innerText = "Công việc: " + jobTitle;
        document.getElementById("applicationPosition").value = jobTitle;
        openModal("applyModal");
    }
</script>