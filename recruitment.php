<?php
$pageStyles = ['css/recruitment.css'];
$bodyClass = 'recruitment-page';
include 'includes/header.php';
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
                    <input type="text" id="chucdanh" class="form-control border-start-0 bg-light" placeholder="Nhập chức danh...">
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <label for="nganhnghe" class="form-label fw-bold text-muted small" data-i18n="recruitment.filter_industry">Ngành nghề</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-briefcase text-muted"></i></span>
                    <select id="nganhnghe" class="form-select border-start-0 bg-light">
                        <option value="">Tất cả ngành nghề</option>
                        <option>Content Marketing</option>
                        <option>Thiết kế</option>
                        <option>Kinh doanh</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <label for="vitri" class="form-label fw-bold text-muted small" data-i18n="recruitment.filter_position">Vị trí</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user-tie text-muted"></i></span>
                    <select id="vitri" class="form-select border-start-0 bg-light">
                        <option value="">Tất cả</option>
                        <option>Nhân viên</option>
                        <option>Trưởng nhóm</option>
                        <option>Quản lý</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <label for="chinhanh" class="form-label fw-bold text-muted small" data-i18n="recruitment.filter_branch">Chi nhánh</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-map-marker-alt text-muted"></i></span>
                    <select id="chinhanh" class="form-select border-start-0 bg-light">
                        <option value="">Tất cả</option>
                        <option>Hồ Chí Minh</option>
                        <option>Hà Nội</option>
                        <option>Đà Nẵng</option>
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
            <label>Thông điệp</label>
            <textarea id="applicantMessage" name="message" rows="3" placeholder="Chia sẻ thêm về bản thân..."></textarea>
            <label data-i18n="recruitment.modal_cv">CV/Resume *</label>
            <input type="file" id="applicantCV" name="cv_file" accept=".pdf,.doc,.docx" required>
            <button type="submit" name="submit_application" value="1" data-i18n="recruitment.modal_submit">Gửi đơn</button>
        </form>
    </div>
</div>

<script>
    // Dữ liệu từ server
    let jobs = <?php echo json_encode($recruitment_posts, JSON_UNESCAPED_UNICODE); ?>;

    function escapeHtml(str = '') {
        return str.replace(/[&<>"']/g, (char) => {
            const entities = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            };
            return entities[char] || char;
        });
    }

    function searchJobs() {
        const chucdanh = document.getElementById("chucdanh").value.toLowerCase();
        const nganhnghe = document.getElementById("nganhnghe").value;
        const vitri = document.getElementById("vitri").value;
        const chinhanh = document.getElementById("chinhanh").value;

        const results = jobs.filter(job => {
            return (
                (chucdanh === "" || job.title.toLowerCase().includes(chucdanh)) &&
                (nganhnghe === "" || (job.details.industry || "") === nganhnghe) &&
                (vitri === "" || (job.details.position || "") === vitri) &&
                (chinhanh === "" || (job.details.work_location || "") === chinhanh)
            );
        });

        displayJobs(results);
    }

    function displayJobs(jobList) {
        const container = document.getElementById("jobList");
        container.innerHTML = "";

        if (jobList.length === 0) {
            container.innerHTML = `
                <div class="col-12">
                    <div class="no-result bg-white rounded-3 shadow-sm border p-5">
                        <i class="fas fa-search fa-3x mb-3 text-muted opacity-50"></i><br>
                        <span data-i18n="recruitment.no_result">Không tìm thấy tin tuyển dụng phù hợp</span>
                    </div>
                </div>`;
            return;
        }

        jobList.forEach(job => {
            const jobCard = document.createElement("div");
            jobCard.className = "col-lg-6 col-md-12";
            const jobDetails = job.details || {};
            const safeTitle = escapeHtml(job.title || '');
            const safeLocation = escapeHtml(jobDetails.work_location || 'Đang cập nhật');
            const safeSalary = escapeHtml(jobDetails.salary || 'Thỏa thuận');
            const safeDeadline = escapeHtml(jobDetails.deadline || 'Không giới hạn');
            const detailUrl = `recruitment-detail.php?id=${job.id}`;
            const excerpt = escapeHtml((job.excerpt || '').replace(/<[^>]*>?/gm, '')).substring(0, 140) + '...';

            jobCard.innerHTML = `
                <div class="card h-100 shadow-sm border-0 job-card-item" style="border-radius: 12px; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                    <div class="card-body p-4 pb-2">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h4 class="card-title fw-bold mb-0" style="font-size: 1.25rem; line-height: 1.4;">
                                <a href="${detailUrl}" class="text-decoration-none text-dark hover-primary" style="transition: color 0.3s;">${safeTitle}</a>
                            </h4>
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 border border-success border-opacity-25 ms-2" style="font-size: 0.75rem; white-space: nowrap;"><i class="fas fa-fire me-1"></i> Đang tuyển</span>
                        </div>
                        <div class="text-muted small mb-2 d-flex align-items-center gap-3">
                            <span><i class="fas fa-building me-1 opacity-75"></i> FUTA Group</span>
                        </div>
                        <p class="text-muted small mt-3 mb-4" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-size: 0.95rem; min-height: 42px;">${excerpt}</p>
                        <div class="d-flex flex-wrap gap-2 mt-auto text-muted" style="font-size: 0.85rem;">
                            <span class="d-flex align-items-center bg-light rounded px-2 py-1"><i class="fas fa-map-marker-alt text-danger me-2"></i> ${safeLocation}</span>
                            <span class="d-flex align-items-center bg-light rounded px-2 py-1"><i class="fas fa-money-bill-wave text-success me-2"></i> ${safeSalary}</span>
                            <span class="d-flex align-items-center bg-light rounded px-2 py-1"><i class="fas fa-calendar-times text-warning me-2"></i> ${safeDeadline}</span>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top-0 p-4 pt-3">
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary flex-grow-1 apply-btn" style="background: linear-gradient(135deg, #004aad 0%, #007bff 100%); border: none; font-weight: 600; padding: 10px 15px; border-radius: 8px;"><i class="fas fa-paper-plane me-2"></i>Ứng tuyển</button>
                            <a href="${detailUrl}" class="btn btn-outline-primary flex-grow-1" style="font-weight: 600; padding: 10px 15px; border-radius: 8px; border-color: #004aad; color: #004aad;">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
            `;
            jobCard.querySelector(".apply-btn").addEventListener("click", () => openApplyModal(job.title));
            container.appendChild(jobCard);
        });
        
        // Kích hoạt lại đa ngôn ngữ cho các phần tử vừa tạo
        if (window.i18n && typeof window.i18n.setLanguage === 'function') {
            window.i18n.setLanguage(window.i18n.getLanguage());
        }
    }

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

    window.onload = () => displayJobs(jobs);
</script>

<?php include 'includes/footer.php'; ?>
