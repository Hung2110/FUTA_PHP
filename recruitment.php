<?php
$pageTitle = 'Tuyển Dụng';
$pageStyles = ['css/recruitment.css'];
$bodyClass = 'recruitment-page';
include 'includes/header.php';
require_once 'db.php';

// Gọi file xử lý ứng tuyển để tái sử dụng logic và kích hoạt tính năng gửi Email
require_once __DIR__ . '/includes/process_application.php';

// Lấy dữ liệu tuyển dụng
$recruitment_posts = [];
// Lấy các tin tuyển dụng từ bảng 'jobs'
$sql = "SELECT id, title, industry, position, branch, description FROM jobs WHERE status = 'open' ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Tách dữ liệu từ description để hiển thị chính xác ra thẻ Card
        $desc_lines = explode("\n", $row['description']);
        $parsed_loc = $row['branch'];
        $parsed_sal = 'Thỏa thuận';
        $parsed_dead = 'Không giới hạn';
        $parsed_level = $row['position'];
        
        $desc_text = "";
        $in_desc = false;

        foreach($desc_lines as $line) {
            $tline = trim($line);
            if (empty($tline)) continue;

            if (stripos($tline, 'Nơi làm việc:') === 0) $parsed_loc = trim(substr($tline, strlen('Nơi làm việc:')));
            elseif (stripos($tline, 'Mức lương:') === 0) $parsed_sal = trim(substr($tline, strlen('Mức lương:')));
            elseif (stripos($tline, 'Hạn chót nhận hồ sơ:') === 0 || stripos($tline, 'Hạn nộp hồ sơ:') === 0) $parsed_dead = trim(preg_replace('/^Hạn (chót nhận|nộp) hồ sơ:/i', '', $tline));
            elseif (stripos($tline, 'Cấp bậc:') === 0) $parsed_level = trim(substr($tline, strlen('Cấp bậc:')));
            
            // Lấy một đoạn ngắn trong phần "Mô tả công việc" làm Excerpt
            if (stripos($tline, 'Mô tả công việc:') === 0) {
                $in_desc = true;
                $content = trim(substr($tline, strlen('Mô tả công việc:')));
                if (!empty($content)) $desc_text .= $content . " ";
                continue;
            }
            if ($in_desc) {
                if (preg_match('/^(Yêu cầu|Phúc lợi|Quyền lợi|Danh sách|Thông tin|Ghi chú|Nơi làm việc|Cấp bậc|Số lượng|Hình thức|Kinh nghiệm|Mức lương|Ngành nghề|Hạn)/i', $tline)) {
                    $in_desc = false;
                } else {
                    $desc_text .= $tline . " ";
                }
            }
        }

        $row['details'] = [
            'industry' => $row['industry'],
            'position' => $parsed_level,
            'work_location' => $parsed_loc,
            'salary' => $parsed_sal,
            'deadline' => $parsed_dead
        ];
        
        $clean_excerpt = trim(strip_tags($desc_text));
        $row['excerpt'] = empty($clean_excerpt) ? trim(strip_tags($row['description'])) : $clean_excerpt;
        
        $recruitment_posts[] = $row;
    }
}
?>



<section class="recruitment-banner">
    <h1 data-i18n="recruitment.banner_title">WELCOME TO FUTA ADVERTISING</h1>
    <p data-i18n="recruitment.banner_desc">Nơi cơ hội nghề nghiệp đang chờ đợi bạn!</p>
</section>

<!-- Bộ lọc tuyển dụng -->
<section class="container filter-container">
    <div class="card shadow-sm border-0 p-4" style="border-radius: 15px;">
        <div class="row g-3">
            <div class="col-lg-3 col-md-6">
                <label for="chucdanh" class="form-label fw-bold text-muted small" data-i18n="recruitment.filter_title">Tìm kiếm công việc</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="chucdanh" class="form-control border-start-0 bg-light" placeholder="Nhập chức danh..." data-i18n-placeholder="recruitment.filter_placeholder_title">
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <label for="nganhnghe" class="form-label fw-bold text-muted small" data-i18n="recruitment.filter_industry">Ngành nghề</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-briefcase text-muted"></i></span>
                    <select id="nganhnghe" class="form-select border-start-0 bg-light">
                        <option value="" data-i18n="recruitment.filter_option_all_industry">Tất cả ngành nghề</option>
                        <option data-i18n="recruitment.filter_option_content">Content Marketing</option>
                        <option data-i18n="recruitment.filter_option_design">Thiết kế</option>
                        <option data-i18n="recruitment.filter_option_business">Kinh doanh</option>
                        <option data-i18n="recruitment.filter_option_supervisor">Giám sát thi công</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <label for="vitri" class="form-label fw-bold text-muted small" data-i18n="recruitment.filter_position">Vị trí</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user-tie text-muted"></i></span>
                    <select id="vitri" class="form-select border-start-0 bg-light">
                        <option value="" data-i18n="recruitment.filter_option_all">Tất cả</option>
                        <option data-i18n="recruitment.filter_option_staff">Nhân viên</option>
                        <option data-i18n="recruitment.filter_option_leader">Trưởng nhóm</option>
                        <option data-i18n="recruitment.filter_option_manager">Quản lý</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <label for="chinhanh" class="form-label fw-bold text-muted small" data-i18n="recruitment.filter_branch">Chi nhánh</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-map-marker-alt text-muted"></i></span>
                    <select id="chinhanh" class="form-select border-start-0 bg-light">
                        <option value="" data-i18n="recruitment.filter_option_all">Tất cả</option>
                        <option data-i18n="recruitment.filter_option_hcm">Hồ Chí Minh</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-2 col-md-12 d-flex align-items-end">
                <button onclick="searchJobs()" class="btn btn-primary w-100" style="padding: 10px; font-weight: 600; background: linear-gradient(135deg, #004aad 0%, #007bff 100%); border: none; border-radius: 8px;" data-i18n="recruitment.filter_search">
                    <i class="fas fa-filter me-2"></i>Tìm Kiếm
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Thông báo ứng tuyển -->
<?php if ($applicationMessage): ?>
    <div class="application-alert <?php echo $applicationType === 'success' ? 'success' : 'error'; ?>">
        <i class="fas <?php echo $applicationType === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> fa-lg"></i>
        <?php echo htmlspecialchars($applicationMessage, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<!-- Danh sách công việc -->
<section class="container mb-5 mt-4">
    <div class="row g-4" id="jobList"></div>
</section>

<!-- Modal ứng tuyển -->
<div class="modal" id="applyModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('applyModal')">&times;</span>
        <h2 data-i18n="recruitment.modal_title">Đơn ứng tuyển</h2>
        <p id="applyJobTitle"></p>
        <form method="POST" enctype="multipart/form-data" class="apply-form">
            <input type="hidden" name="position" id="applicationPosition">
            <label data-i18n="recruitment.modal_name">Họ và tên *</label>
            <input type="text" id="applicantName" name="fullname" required>
            <label data-i18n="recruitment.modal_email">Email *</label>
            <input type="email" id="applicantEmail" name="email" required>
            <label data-i18n="recruitment.modal_phone">Số điện thoại *</label>
            <input type="text" id="applicantPhone" name="phone" required>
            <label data-i18n="recruitment.modal_message">Thông điệp</label>
            <textarea id="applicantMessage" name="message" rows="3" data-i18n-placeholder="recruitment.modal_message_placeholder" placeholder="Chia sẻ thêm về bản thân..."></textarea>
            <label data-i18n="recruitment.modal_cv">CV/Resume</label>
            <input type="file" id="applicantCV" name="cv_file" accept=".pdf,.doc,.docx" required data-i18n-placeholder="recruitment.modal_cv_placeholder" placeholder="Đính kèm CV/Resume (PDF, DOC, DOCX)">
            <button type="submit" name="submit_application" value="1" data-i18n="recruitment.modal_submit">Gửi đơn</button>
        </form>
    </div>
</div>

<script id="recruitment-data" type="application/json">
    <?php echo json_encode($recruitment_posts, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
</script>
<script src="js/recruitment.js"></script>

<!-- Nút chuyển trang trên Mobile -->
<a href="news.php" class="mobile-page-nav-btn prev"><i class="fas fa-chevron-left"></i></a>
<a href="contact.php" class="mobile-page-nav-btn next"><i class="fas fa-chevron-right"></i></a>

<?php include 'includes/footer.php'; ?>
