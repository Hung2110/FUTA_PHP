document.addEventListener('DOMContentLoaded', function() {
    const slideModal = document.getElementById('slideModal');
    const slideForm = document.getElementById('slideForm');
    const modalTitle = document.getElementById('slideModalLabel');
    const formAction = document.getElementById('formAction');
    const slideId = document.getElementById('slideId');
    const sortOrderInput = document.getElementById('sort_order');
    const statusSelect = document.getElementById('status');
    const imageFileInput = document.getElementById('image_file');
    const imagePreview = document.getElementById('image_preview');
    const currentImagePath = document.getElementById('currentImagePath');
    const submitBtn = document.getElementById('submitBtn');

    if (!slideModal || !slideForm) return;

    // Reset form khi modal đóng
    slideModal.addEventListener('hide.bs.modal', function () {
        // Nếu đang ở trang edit, khi đóng modal thì quay về trang danh sách
        if (window.location.search.includes('edit=')) {
            window.location.href = 'carousel_slides.php';
        }
    });
    slideModal.addEventListener('hidden.bs.modal', function () {
        slideForm.reset();
        formAction.value = 'add';
        modalTitle.textContent = 'Thêm slide mới';
        submitBtn.textContent = 'Lưu';
        imagePreview.style.display = 'none';
        imagePreview.src = '';
        currentImagePath.value = '';
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Lưu';
    });

    // Xử lý khi nhấn nút "Thêm slide mới"
    const addSlideBtn = document.getElementById('addSlideBtn');
    if (addSlideBtn) {
        addSlideBtn.addEventListener('click', function() {
            formAction.value = 'add';
            modalTitle.textContent = 'Thêm slide mới';
            submitBtn.textContent = 'Lưu';
        });
    }

    // Xử lý khi nhấn nút "Sửa"
    slideModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget; // Nút kích hoạt modal
        if (button && button.classList.contains('btn-edit')) {
            slideForm.action = `carousel_slides.php?edit=${button.dataset.id}`;
            modalTitle.textContent = 'Sửa slide';
            submitBtn.textContent = 'Cập nhật';
            formAction.value = 'edit';

            slideId.value = button.dataset.id;
            sortOrderInput.value = button.dataset.sort_order;
            statusSelect.value = button.dataset.status;
            currentImagePath.value = button.dataset.image_path;

            if (button.dataset.image_path) {
                imagePreview.src = '../' + button.dataset.image_path;
                imagePreview.style.display = 'block';
            } else {
                imagePreview.style.display = 'none';
            }
        } else {
            // Chế độ thêm mới
            slideForm.action = 'carousel_slides.php';
        }
    });

    // Preview ảnh khi chọn file
    if (imageFileInput) {
        imageFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    imagePreview.src = event.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
                imagePreview.src = '';
            }
        });
    }

    // Vô hiệu hóa nút submit để tránh gửi nhiều lần
    slideForm.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang xử lý...';
    });

    // Nếu có tham số edit trên URL, tự động mở modal
    const urlParams = new URLSearchParams(window.location.search);
    const editId = urlParams.get('edit');
    if (editId) {
        const modal = new bootstrap.Modal(slideModal);
        const editButton = document.querySelector(`.btn-edit[data-id='${editId}']`) || document.getElementById('addSlideBtn');
        modal.show(editButton);
    }
});

