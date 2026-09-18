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

$pageTitle = htmlspecialchars($job['title']);
$pageStyles = ['css/recruitment-detail.css'];
$bodyClass = 'recruitment-detail-page';
include 'includes/header.php';
?>



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
                    <span data-i18n="recruitment_detail.views">Lượt xem:</span> <?php echo rand(500, 5000); /* View tĩnh hiển thị minh họa */ ?> &nbsp;&nbsp;|&nbsp;&nbsp; 
                    <span data-i18n="recruitment_detail.updated_date">Ngày cập nhật:</span> <?php echo date('d/m/Y', strtotime($job['created_at'])); ?>
                </p>
                <h1 class="job-title-main mb-4" 
                    data-i18n-key="job_title_<?php echo $job['id']; ?>" 
                    data-i18n-text="<?php echo htmlspecialchars($job['title']); ?>"
                ><?php echo htmlspecialchars($job['title']); ?></h1>
                
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
                                     echo '<span><i class="fa ' . $icon . ' pull-left ico-ttd"></i> <span data-i18n-key="job_benefit_'.md5($line).'" data-i18n-text="'.htmlspecialchars($line).'">'.
                                          htmlspecialchars($line) . 
                                          '</span></span>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($details['description'])): ?>
                        <h4 class="detail-subtitle"><i class="fas fa-briefcase"></i> <span data-i18n="recruitment_detail.job_desc">Mô tả công việc</span></h4>
                         <div class="job-full-description" data-i18n-key="job_desc_<?php echo $job['id']; ?>" data-i18n-text="<?php echo htmlspecialchars(strip_tags($details['description'])); ?>"><?php echo nl2br(htmlspecialchars($details['description'])); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($details['requirements'])): ?>
                        <h4 class="detail-subtitle"><i class="fas fa-user-check"></i> <span data-i18n="recruitment_detail.job_req">Yêu cầu công việc</span></h4>
                        <div class="job-full-description" data-i18n-key="job_req_<?php echo $job['id']; ?>" data-i18n-text="<?php echo htmlspecialchars(strip_tags($details['requirements'])); ?>"><?php echo nl2br(htmlspecialchars($details['requirements'])); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($details['documents'])): ?>
                        <h4 class="detail-subtitle"><i class="fas fa-folder-open"></i> <span data-i18n="recruitment_detail.documents">Danh sách hồ sơ xin việc</span></h4>
                        <div class="job-full-description" data-i18n-key="job_docs_<?php echo $job['id']; ?>" data-i18n-text="<?php echo htmlspecialchars(strip_tags($details['documents'])); ?>"><?php echo nl2br(htmlspecialchars($details['documents'])); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($details['contact'])): ?>
                        <h4 class="detail-subtitle"><i class="fas fa-address-book"></i> <span data-i18n="recruitment_detail.contact_info">Thông tin liên hệ</span></h4>
                        <div class="job-full-description fw-medium" data-i18n-key="job_contact_<?php echo $job['id']; ?>" data-i18n-text="<?php echo htmlspecialchars(strip_tags($details['contact'])); ?>"><?php echo nl2br(htmlspecialchars($details['contact'])); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($details['notes'])): ?>
                        <h4 class="detail-subtitle"><i class="fas fa-exclamation-circle text-danger"></i> <span data-i18n="recruitment_detail.notes">Ghi chú</span></h4>
                        <div class="job-full-description fst-italic text-danger fw-bold" data-i18n-key="job_notes_<?php echo $job['id']; ?>" data-i18n-text="<?php echo htmlspecialchars(strip_tags($details['notes'])); ?>"><?php echo nl2br(htmlspecialchars($details['notes'])); ?></div>
                    <?php endif; ?>
                </div>

                <div class="job-detail-section contact-info">
                    <h3 class="section-title"><i class="fas fa-paper-plane"></i> <span data-i18n="recruitment_detail.contact_apply">Thông tin liên hệ & Ứng tuyển</span></h3>
                    <p class="d-flex"><i class="fas fa-envelope mt-1 me-2 text-primary"></i> <span><span data-i18n="recruitment_detail.contact_desc1">Để ứng tuyển, vui lòng gửi CV và các giấy tờ liên quan về địa chỉ email:</span> <strong>futaadvertising@futa.vn</strong></span></p>
                    <p class="d-flex"><i class="fas fa-pen mt-1 me-2 text-primary"></i> <span><span data-i18n="recruitment_detail.contact_desc2">Tiêu đề email ghi rõ:</span> "Ứng tuyển vị trí [<span data-i18n-key="job_title_<?php echo $job['id']; ?>" data-i18n-text="<?php echo htmlspecialchars($job['title']); ?>"><?php echo htmlspecialchars($job['title']); ?></span>] - [Họ và tên]"</span></p>
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
                                <span data-i18n-key="job_summary_<?php echo str_replace('recruitment_detail.summary_', '', $item['i18n']) . '_' . $job['id']; ?>" 
                                      data-i18n-text="<?php echo htmlspecialchars($item['value']); ?>"
                                ><?php echo htmlspecialchars($item['value']); ?></span>
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
        <h2 data-i18n="recruitment.modal_title">Nộp hồ sơ ứng tuyển</h2>
        <p id="applyJobTitle"></p>
        <form method="POST" enctype="multipart/form-data" class="apply-form">
            <div class="row g-3">
                <input type="hidden" name="position" id="applicationPosition">
                <div class="col-md-6">
                    <label data-i18n="recruitment.modal_name">Họ và tên</label>
                    <input type="text" id="applicantName" name="fullname" class="form-control" required placeholder="Nhập họ và tên của bạn">
                </div>
                <div class="col-md-6">
                    <label data-i18n="recruitment.modal_phone">Số điện thoại</label>
                    <input type="text" id="applicantPhone" name="phone" class="form-control" required placeholder="0123 456 789">
                </div>
                <div class="col-12">
                    <label data-i18n="recruitment.modal_email">Email</label>
                    <input type="email" id="applicantEmail" name="email" class="form-control" required placeholder="email@example.com">
                </div>
                <div class="col-12">
                    <label>Thông điệp bổ sung</label>
                    <textarea id="applicantMessage" name="message" class="form-control" rows="3" placeholder="Chia sẻ thêm về kỹ năng và kinh nghiệm làm việc của bạn..."></textarea>
                </div>
                <div class="col-12">
                    <label data-i18n="recruitment.modal_cv">CV/Resume (PDF, DOC, DOCX)</label>
                    <input type="file" id="applicantCV" name="cv_file" class="form-control bg-white" accept=".pdf,.doc,.docx" required>
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" name="submit_application" value="1" class="btn-apply-now w-100" data-i18n="recruitment_detail.apply_now">
                        <i class="fas fa-paper-plane me-2"></i>Gửi Đơn Ứng Tuyển
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="js/recruitment-detail.js"></script>
<?php include 'includes/footer.php'; ?>