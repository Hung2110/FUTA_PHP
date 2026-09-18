document.addEventListener('DOMContentLoaded', function () {
    // Hàm mở modal và tải nội dung cho Trang chủ
    window.openContentModal = function(element) {
        const type = element.dataset.type;
        const id = element.dataset.id;
        const slug = element.dataset.slug;

        const modalEl = document.getElementById('contentModal');
        if (!modalEl) return;
        const modal = new bootstrap.Modal(modalEl);
        const modalTitle = document.getElementById('contentModalLabel');
        const modalBody = document.getElementById('contentModalBody');

        // Reset modal
        if (modalTitle) modalTitle.textContent = 'Đang tải...';
        if (modalBody) {
            modalBody.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>`;
        }
        modal.show();

        // Xác định endpoint API
        let apiUrl = 'api.php?resource=';
        if (type === 'post') {
            apiUrl += `posts&slug=${slug}`;
        } else {
            apiUrl += `projects&id=${id}`;
        }

        // Gọi API để lấy dữ liệu
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data && !data.error) {
                    if (modalTitle) modalTitle.textContent = data.title;
                    // Hiển thị nội dung, ưu tiên 'content' cho bài viết và 'description' cho dự án
                    if (modalBody) {
                        modalBody.innerHTML = `
                            <img src="${data.image || data.preview_image}" class="img-fluid rounded mb-3" alt="${data.title}">
                            <div>${data.content || data.description}</div>
                        `;
                    }
                }
            })
            .catch(error => {
                console.error('Error loading content:', error);
                if (modalBody) {
                    modalBody.innerHTML = `<div class="alert alert-danger">Không thể tải dữ liệu. Vui lòng thử lại sau.</div>`;
                }
            });
    };
});

