// Dữ liệu tuyển dụng từ server
let jobs = [];

// Khởi tạo lấy dữ liệu từ thẻ script JSON hoặc window.recruitmentJobs
function initRecruitmentData() {
    if (window.recruitmentJobs && Array.isArray(window.recruitmentJobs)) {
        jobs = window.recruitmentJobs;
    } else {
        const dataEl = document.getElementById('recruitment-data');
        if (dataEl) {
            try {
                jobs = JSON.parse(dataEl.textContent || '[]');
            } catch (e) {
                console.error('Error parsing recruitment data:', e);
                jobs = [];
            }
        }
    }
}

function escapeHtml(str = '') {
    return String(str).replace(/[&<>"']/g, (char) => {
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
    const chucdanhEl = document.getElementById("chucdanh");
    const nganhngheEl = document.getElementById("nganhnghe");
    const vitriEl = document.getElementById("vitri");
    const chinhanhEl = document.getElementById("chinhanh");

    const chucdanh = chucdanhEl ? chucdanhEl.value.toLowerCase() : "";
    const nganhnghe = nganhngheEl ? nganhngheEl.value : "";
    const vitri = vitriEl ? vitriEl.value : "";
    const chinhanh = chinhanhEl ? chinhanhEl.value : "";

    const results = jobs.filter(job => {
        const details = job.details || {};
        return (
            (chucdanh === "" || (job.title || "").toLowerCase().includes(chucdanh)) &&
            (nganhnghe === "" || (details.industry || "") === nganhnghe) &&
            (vitri === "" || (details.position || "") === vitri) &&
            (chinhanh === "" || (details.work_location || "") === chinhanh)
        );
    });

    displayJobs(results);
}

function displayJobs(jobList) {
    const container = document.getElementById("jobList");
    if (!container) return;
    container.innerHTML = "";

    if (!jobList || jobList.length === 0) {
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
        const originalExcerpt = escapeHtml((job.excerpt || '').replace(/<[^>]*>?/gm, ''));

        jobCard.innerHTML = `
            <div class="card h-100 shadow-sm border-0 job-card-item" style="border-radius: 12px; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                <div class="card-body p-4 pb-2">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h4 class="card-title fw-bold mb-0" style="font-size: 1.25rem; line-height: 1.4;">
                            <a href="${detailUrl}" 
                               class="text-decoration-none text-dark hover-primary" 
                               style="transition: color 0.3s;"
                               data-i18n-key="job_title_${job.id}"
                               data-i18n-text="${safeTitle}">${safeTitle}</a>
                        </h4>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 border border-success border-opacity-25 ms-2" style="font-size: 0.75rem; white-space: nowrap;"><i class="fas fa-fire me-1"></i> Đang tuyển</span>
                    </div>
                    <div class="text-muted small mb-2 d-flex align-items-center gap-3">
                        <span><i class="fas fa-building me-1 opacity-75"></i> FUTA Group</span>
                    </div>
                    <p class="text-muted small mt-3 mb-4" 
                       style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-size: 0.95rem; min-height: 42px;"
                       data-i18n-key="job_excerpt_${job.id}"
                       data-i18n-text="${originalExcerpt}">${excerpt}</p>
                    <div class="d-flex flex-wrap gap-2 mt-auto text-muted" style="font-size: 0.85rem;">
                        <span class="d-flex align-items-center bg-light rounded px-2 py-1" data-i18n-key="job_location_${job.id}" data-i18n-text="${safeLocation}"><i class="fas fa-map-marker-alt text-danger me-2"></i> ${safeLocation}</span>
                        <span class="d-flex align-items-center bg-light rounded px-2 py-1" data-i18n-key="job_salary_${job.id}" data-i18n-text="${safeSalary}"><i class="fas fa-money-bill-wave text-success me-2"></i> ${safeSalary}</span>
                        <span class="d-flex align-items-center bg-light rounded px-2 py-1" data-i18n-key="job_deadline_${job.id}" data-i18n-text="${safeDeadline}"><i class="fas fa-calendar-times text-warning me-2"></i> ${safeDeadline}</span>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top-0 p-4 pt-3">
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary flex-grow-1 apply-btn" style="background: linear-gradient(135deg, #004aad 0%, #007bff 100%); border: none; font-weight: 600; padding: 10px 15px; border-radius: 8px;" data-i18n="recruitment.apply_btn"><i class="fas fa-paper-plane me-2"></i>Ứng tuyển</button>
                        <a href="${detailUrl}" class="btn btn-outline-primary flex-grow-1" style="font-weight: 600; padding: 10px 15px; border-radius: 8px; border-color: #004aad; color: #004aad;" data-i18n="recruitment.view_detail_btn">Xem chi tiết</a>
                    </div>
                </div>
            </div>
        `;
        const applyBtn = jobCard.querySelector(".apply-btn");
        if (applyBtn) {
            applyBtn.addEventListener("click", () => openApplyModal(job.title));
        }
        container.appendChild(jobCard);
    });
}

function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = "flex";
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = "none";
    }
}

function openApplyModal(jobTitle) {
    const titleEl = document.getElementById("applyJobTitle");
    const posEl = document.getElementById("applicationPosition");
    if (titleEl) {
        titleEl.innerText = "Công việc: " + jobTitle;
    }
    if (posEl) {
        posEl.value = jobTitle;
    }
    openModal("applyModal");
}

// Gán hàm vào window để gọi được từ inline HTML onclick nếu có
window.searchJobs = searchJobs;
window.displayJobs = displayJobs;
window.openModal = openModal;
window.closeModal = closeModal;
window.openApplyModal = openApplyModal;

document.addEventListener('DOMContentLoaded', function() {
    initRecruitmentData();
    displayJobs(jobs);
});

