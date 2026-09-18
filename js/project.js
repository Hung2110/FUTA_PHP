// Thay thế sự kiện scroll liên tục bằng IntersectionObserver (tối ưu hiệu suất cực lớn)
document.addEventListener('DOMContentLoaded', () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('show');
                observer.unobserve(entry.target); // Ngừng theo dõi khi đã hiện, giảm tải bộ nhớ
            }
        });
    }, { threshold: 0.1 }); // Hiển thị khi xuất hiện 10% màn hình

    document.querySelectorAll('.futa-project-page .fade-in').forEach(el => {
        observer.observe(el);
    });

    // Hàm mở modal và tải nội dung
    window.openContentModal = function(element) {
        const type = element.dataset.type;
        const id = element.dataset.id;

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
        let apiUrl = `api.php?resource=projects&id=${id}`;

        // Gọi API để lấy dữ liệu
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data && !data.error) {
                    if (modalTitle) modalTitle.textContent = data.title;
                    // Hiển thị nội dung
                    if (modalBody) {
                        modalBody.innerHTML = `
                            <img src="${data.preview_image}" class="img-fluid rounded mb-3" alt="${data.title}">
                            <div>${data.description}</div>
                        `;
                    }
                }
            })
            .catch(error => {
                console.error('Error loading project content:', error);
                if (modalBody) {
                    modalBody.innerHTML = `<div class="alert alert-danger">Không thể tải dữ liệu dự án. Vui lòng thử lại sau.</div>`;
                }
            });
    };
});

