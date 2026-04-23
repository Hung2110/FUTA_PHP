<?php
require_once 'db.php';

// Include logic xử lý form ứng tuyển
require_once 'includes/process_application.php';

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
    'documents' => '',
    'contact' => '',
    'notes' => '',
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
$documents_parts = [];
$contact_parts = [];
$notes_parts = [];

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
    } elseif (stripos($trimmed_line, 'Thông tin liên hệ:') === 0) {
        $current_section = 'contact';
        $content = trim(substr($trimmed_line, strlen('Thông tin liên hệ:')));
        if (!empty($content)) $contact_parts[] = $content;
    } elseif (stripos($trimmed_line, 'Ghi chú:') === 0) {
        $current_section = 'notes';
        $content = trim(substr($trimmed_line, strlen('Ghi chú:')));
        if (!empty($content)) $notes_parts[] = $content;
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
    } elseif (stripos($trimmed_line, 'Hạn chót nhận hồ sơ:') === 0 || stripos($trimmed_line, 'Hạn nộp hồ sơ:') === 0) {
        $current_section = null;
        $details['deadline'] = trim(preg_replace('/^Hạn (chót nhận|nộp) hồ sơ:/i', '', $trimmed_line));
    } else {
        if ($current_section === 'description') $description_parts[] = $trimmed_line;
        elseif ($current_section === 'requirements') $requirements_parts[] = $trimmed_line;
        elseif ($current_section === 'benefits') $benefits_parts[] = $trimmed_line;
        elseif ($current_section === 'documents') $documents_parts[] = $trimmed_line;
        elseif ($current_section === 'contact') $contact_parts[] = $trimmed_line;
        elseif ($current_section === 'notes') $notes_parts[] = $trimmed_line;
    }
}

$details['description'] = implode("\n", $description_parts);
$details['requirements'] = implode("\n", $requirements_parts);
$details['benefits'] = implode("\n", $benefits_parts);
$details['documents'] = implode("\n", $documents_parts);
$details['contact'] = implode("\n", $contact_parts);
$details['notes'] = implode("\n", $notes_parts);

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
        box-shadow: 0 5px 25px rgba(0, 74, 173, 0.08);
        margin-bottom: 2rem;
        border: none;
        position: sticky;
        top: 100px;
    }
    .job-title-main {
        font-size: 2rem;
        font-weight: 700;
        color: #007bff;
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }
    .job-post-date {
        color: #6c757d;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    .detail-subtitle {
        font-size: 1.25rem;
        font-weight: 700;
        color: #007bff;
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
        color: #007bff;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #eef2f7;
    }
    
    .summary-list li strong {
        color: #007bff;
        font-weight: 600;
        min-width: 120px;
        display: inline-block;
    }
    .btn-apply-now {
        width: 100%;
        padding: 14px;
        font-size: 1.1rem;
        font-weight: 700;
        text-transform: uppercase;
        border-radius: 10px;
        background: linear-gradient(135deg, #004aad 0%, #007bff 100%);
        border: none;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 74, 173, 0.3);
        margin-top: 1.5rem;
        letter-spacing: 0.5px;
    }
    .btn-apply-now:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 123, 255, 0.4);
    }
    .recruitment-banner {
        background: #f4f6f9;
        padding: 3rem 0;
        text-align: center;
        margin-bottom: 0;
    }
    .recruitment-banner h1 {
        color: #007bff;
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
        color: #007bff;
    }
    .pull-left {
        float: left !important;
    }
    .section-title-benefits {
        font-size: 1.25rem;
        font-weight: 700;
        color: #007bff;
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
        color: #007bff;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #eef2f7;
        margin-bottom: 1.5rem;
        margin-top: 2rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .section-title i {
        color: #007bff;
        font-size: 1.2rem;
    }
    .contact-info p {
        margin-bottom: 0.8rem;
        align-items: flex-start;
    }
    .contact-info p i {
        color: #007bff;
        margin-right: 10px;
        margin-top: 5px;
        width: 20px;
        text-align: center;
        flex-shrink: 0;
    }
    /* New Summary Card Layout */
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
        padding: 40px;
        border-radius: 16px;
        max-width: 650px;
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
        margin-bottom: 15px;
        color: #007bff;
        font-size: 26px;
        font-weight: 800;
        border-bottom: 2px solid #eef2f7;
        padding-bottom: 15px;
    }

    .modal-content #applyJobTitle {
        color: #007bff;
        margin-bottom: 25px;
        font-size: 16px;
        font-weight: 600;
    }

    .apply-form {
        display: flex;
        flex-direction: column;
    }

    .modal-content label {
        display: block;
        margin: 0 0 8px;
        font-weight: 600;
        color: #495057;
        font-size: 14.5px;
    }

    .apply-form .form-control {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 12px 15px;
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
        background: #ffe5e5;
        color: #dc3545;
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
    <h1 data-i18n="recruitment_detail.title">Chi Tiết Tin Tuyển Dụng</h1>
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
                <p class="job-post-date fw-medium">
                    Lượt xem: <?php echo rand(500, 5000); /* View tĩnh hiển thị minh họa */ ?> &nbsp;&nbsp;|&nbsp;&nbsp; 
                    Ngày cập nhật: <?php echo date('d/m/Y', strtotime($job['created_at'])); ?>
                </p>
                <h1 class="job-title-main mb-4"><?php echo htmlspecialchars($job['title']); ?></h1>
                
                <div class="job-detail-section">
                    <?php if (!empty($details['benefits'])): ?>
                        <label class="section-title-benefits"><i class="fa fa-grin pull-left ico-ttd"></i> <span data-i18n="recruitment_detail.benefits">Phúc lợi:</span></label>
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

                    <?php if (!empty($details['description'])): ?>
                        <h4 class="detail-subtitle"><i class="fas fa-briefcase"></i> <span data-i18n="recruitment_detail.job_desc">Mô tả công việc</span></h4>
                        <div class="job-full-description"><?php echo nl2br(htmlspecialchars($details['description'])); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($details['requirements'])): ?>
                        <h4 class="detail-subtitle"><i class="fas fa-user-check"></i> <span>Yêu cầu</span></h4>
                        <div class="job-full-description"><?php echo nl2br(htmlspecialchars($details['requirements'])); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($details['documents'])): ?>
                        <h4 class="detail-subtitle"><i class="fas fa-folder-open"></i> <span>Danh sách hồ sơ xin việc</span></h4>
                        <div class="job-full-description"><?php echo nl2br(htmlspecialchars($details['documents'])); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($details['contact'])): ?>
                        <h4 class="detail-subtitle"><i class="fas fa-address-book"></i> <span>Thông tin liên hệ</span></h4>
                        <div class="job-full-description fw-medium"><?php echo nl2br(htmlspecialchars($details['contact'])); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($details['notes'])): ?>
                        <h4 class="detail-subtitle"><i class="fas fa-exclamation-circle text-danger"></i> <span>Ghi chú</span></h4>
                        <div class="job-full-description fst-italic text-danger fw-bold"><?php echo nl2br(htmlspecialchars($details['notes'])); ?></div>
                    <?php endif; ?>
                </div>

                <div class="job-detail-section contact-info">
                    <h3 class="section-title"><i class="fas fa-paper-plane"></i> <span data-i18n="recruitment_detail.contact_apply">Thông tin liên hệ & Ứng tuyển</span></h3>
                    <p class="d-flex"><i class="fas fa-envelope mt-1 me-2 text-primary"></i> <span><span data-i18n="recruitment_detail.contact_desc1">Để ứng tuyển, vui lòng gửi CV và các giấy tờ liên quan về địa chỉ email:</span> <strong>futaadvertising@futa.vn</strong></span></p>
                    <p class="d-flex"><i class="fas fa-pen mt-1 me-2 text-primary"></i> <span><span data-i18n="recruitment_detail.contact_desc2">Tiêu đề email ghi rõ:</span> "Ứng tuyển vị trí [<?php echo htmlspecialchars($job['title']); ?>] - [Họ và tên]"</span></p>
                    <p class="d-flex"><i class="fas fa-phone-alt mt-1 me-2 text-primary"></i> <span><span data-i18n="recruitment_detail.contact_desc3">Hoặc liên hệ qua số điện thoại:</span> <strong>1900 6912 </strong> <span data-i18n="recruitment_detail.contact_desc4">để được hướng dẫn.</span></span></p>
                </div>
            </div>
        </div>

        <!-- Cột thông tin tóm tắt -->
        <div class="col-lg-4">
            <div class="job-summary-card">
                <h4 class="summary-title" data-i18n="recruitment_detail.general_info">Thông tin chung</h4>
                <?php
                    $summary_items = [
                        ['icon' => 'fa-map-marker-alt', 'label' => 'Nơi làm việc', 'value' => $details['work_location'], 'i18n' => 'recruitment_detail.summary_location'],
                        ['icon' => 'fa-users', 'label' => 'Cấp bậc', 'value' => $details['level'], 'i18n' => 'recruitment_detail.summary_level'],
                        ['icon' => 'fa-user-friends', 'label' => 'Số lượng', 'value' => $details['quantity'], 'i18n' => 'recruitment_detail.summary_quantity'],
                        ['icon' => 'fa-user-clock', 'label' => 'Hình thức', 'value' => $details['type'], 'i18n' => 'recruitment_detail.summary_type'],
                        ['icon' => 'fa-star', 'label' => 'Kinh nghiệm', 'value' => $details['experience'], 'i18n' => 'recruitment_detail.summary_experience'],
                        ['icon' => 'fa-dollar-sign', 'label' => 'Mức lương', 'value' => $details['salary'], 'i18n' => 'recruitment_detail.summary_salary'],
                        ['icon' => 'fa-briefcase', 'label' => 'Ngành nghề', 'value' => $details['industry'], 'i18n' => 'recruitment_detail.summary_industry'],
                        ['icon' => 'fa-calendar-times', 'label' => 'Hạn chót', 'value' => $details['deadline'], 'i18n' => 'recruitment_detail.summary_deadline'],
                    ];
                ?>
                <ul class="summary-list">
                    <?php foreach ($summary_items as $item): ?>
                        <li>
                            <i class="fa <?php echo $item['icon']; ?> ico-ttd"></i>
                            <div class="summary-item-content">
                                    <strong data-i18n="<?php echo $item['i18n']; ?>"><?php echo $item['label']; ?></strong>
                                <span><?php echo htmlspecialchars($item['value']); ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <button onclick="openApplyModal('<?php echo htmlspecialchars($job['title'], ENT_QUOTES); ?>')" class="btn-apply-now w-100" data-i18n="recruitment_detail.apply_now"><i class="fas fa-paper-plane me-2"></i>Ứng tuyển ngay</button>
            </div>
        </div>
    </div>
</main>

<!-- Modal ứng tuyển -->
<div class="modal" id="applyModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('applyModal')">&times;</span>
        <h2 data-i18n="recruitment_detail.modal_title">Nộp hồ sơ ứng tuyển</h2>
        <p id="applyJobTitle"></p>
        <form method="POST" enctype="multipart/form-data" class="apply-form">
            <div class="row g-3">
                <input type="hidden" name="position" id="applicationPosition">
                <div class="col-md-6">
                    <label data-i18n="recruitment.modal_name">Họ và tên *</label>
                    <input type="text" id="applicantName" name="fullname" class="form-control" required placeholder="Nhập họ và tên của bạn">
                </div>
                <div class="col-md-6">
                    <label data-i18n="recruitment.modal_phone">Số điện thoại *</label>
                    <input type="text" id="applicantPhone" name="phone" class="form-control" required placeholder="0123 456 789">
                </div>
                <div class="col-12">
                    <label data-i18n="recruitment.modal_email">Email *</label>
                    <input type="email" id="applicantEmail" name="email" class="form-control" required placeholder="email@example.com">
                </div>
                <div class="col-12">
                    <label>Thông điệp bổ sung</label>
                    <textarea id="applicantMessage" name="message" class="form-control" rows="3" placeholder="Chia sẻ thêm về kỹ năng và kinh nghiệm làm việc của bạn..."></textarea>
                </div>
                <div class="col-12">
                    <label data-i18n="recruitment.modal_cv">CV/Resume (PDF, DOC, DOCX) *</label>
                    <input type="file" id="applicantCV" name="cv_file" class="form-control bg-white" accept=".pdf,.doc,.docx" required>
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" name="submit_application" value="1" class="btn-apply-now w-100" data-i18n="recruitment.modal_submit">
                        <i class="fas fa-paper-plane me-2"></i>Gửi Đơn Ứng Tuyển
                    </button>
                </div>
            </div>
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