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
        // Tạo cấu trúc 'details' để tương thích với JavaScript hiện tại
        $row['details'] = [
            'industry' => $row['industry'],
            'position' => $row['position'],
            'work_location' => $row['branch'],
            'salary' => 'Thỏa thuận', // Bạn có thể thêm cột salary vào bảng jobs nếu cần
            'deadline' => null // Bạn có thể thêm cột deadline vào bảng jobs nếu cần
        ];
        $row['excerpt'] = $row['description']; // Sử dụng description làm excerpt
        $recruitment_posts[] = $row;
    }
}
?>

<section class="banner">
    <h1 data-i18n="recruitment.banner_title">WELCOME TO FUTA ADVERTISING</h1>
    <p data-i18n="recruitment.banner_desc">Nơi cơ hội nghề nghiệp đang chờ đợi bạn!</p>
</section>

<!-- Bộ lọc tuyển dụng -->
<section class="filter-box">
    <div>
        <label for="chucdanh" data-i18n="recruitment.filter_title">Tìm kiếm công việc</label>
        <input type="text" id="chucdanh" placeholder="Chức danh tuyển dụng">
    </div>
    <div>
        <label for="nganhnghe" data-i18n="recruitment.filter_industry">Ngành nghề</label>
        <select id="nganhnghe">
            <option value="">Tất cả ngành nghề</option>
            <option>Marketing</option>
            <option>Thiết kế</option>
            <option>Kinh doanh</option>
        </select>
    </div>
    <div>
        <label for="vitri" data-i18n="recruitment.filter_position">Vị trí</label>
        <select id="vitri">
            <option value="">Tất cả vị trí</option>
            <option>Nhân viên</option>
            <option>Trưởng nhóm</option>
            <option>Quản lý</option>
        </select>
    </div>
    <div>
        <label for="chinhanh" data-i18n="recruitment.filter_branch">Chi nhánh</label>
        <select id="chinhanh">
            <option value="">Tất cả chi nhánh</option>
            <option>Hồ Chí Minh</option>
            <option>Hà Nội</option>
            <option>Đà Nẵng</option>
        </select>
    </div>
    <div>
        <button onclick="searchJobs()" data-i18n="recruitment.filter_search">Tìm Kiếm</button>
    </div>
</section>

<!-- Thông báo ứng tuyển -->
<?php if ($applicationMessage): ?>
    <div class="application-alert <?php echo $applicationType === 'success' ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($applicationMessage, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<!-- Danh sách công việc -->
<section class="job-list" id="jobList"></section>

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
            container.innerHTML = `<p class="no-result">Không tìm thấy tin tuyển dụng phù hợp</p>`;
            return;
        }

        jobList.forEach(job => {
            const jobCard = document.createElement("div");
            jobCard.className = "job-card";
            const jobDetails = job.details || {};
            const safeTitle = escapeHtml(job.title || '');
            const safeLocation = escapeHtml(jobDetails.work_location || 'Đang cập nhật');
            const safeSalary = escapeHtml(jobDetails.salary || 'Thỏa thuận');
            const safeDeadline = jobDetails.deadline ? new Date(jobDetails.deadline).toLocaleDateString('vi-VN') : 'Không giới hạn';
            const detailUrl = `recruitment-detail.php?id=${job.id}`;

            jobCard.innerHTML = `
                <h3><a href="${detailUrl}" class="job-title-link">${safeTitle}</a></h3>
                <div class="job-meta">
                    <span><i class="fas fa-map-marker-alt"></i> ${safeLocation}</span>
                    <span><i class="fas fa-money-bill-wave"></i> ${safeSalary}</span>
                    <span><i class="fas fa-calendar-times"></i> ${safeDeadline}</span>
                </div>
                <div class="job-card-actions">
                    <a href="${detailUrl}" class="detail-btn">Xem chi tiết</a>
                    <button class="apply-btn">Ứng tuyển</button>
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
