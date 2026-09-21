/**
 * FUTA Advertising - Dashboard Script
 * Handles responsive filtering (desktop pills & mobile/tablet dropdown) and UI interactions.
 */

document.addEventListener('DOMContentLoaded', function() {
    const desktopButtons = document.querySelectorAll('.btn-section-filter');
    const dropdownItems = document.querySelectorAll('.btn-dropdown-filter');
    const sectionCards = document.querySelectorAll('.section-card-col');
    const activeFilterText = document.querySelector('.active-filter-text');

    /**
     * Đồng bộ lọc hiển thị giữa Desktop Pills và Mobile/Tablet Dropdown
     */
    function applySectionFilter(target, labelHtml) {
        // 1. Ẩn/Hiện các khối card trang con
        sectionCards.forEach(col => {
            const sec = col.getAttribute('data-section');
            if (target === 'all' || sec === target) {
                col.style.display = '';
            } else {
                col.style.display = 'none';
            }
        });

        // 2. Đồng bộ nút bấm Desktop
        desktopButtons.forEach(btn => {
            if (btn.getAttribute('data-target') === target) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // 3. Đồng bộ item trong Dropdown Mobile/Tablet
        dropdownItems.forEach(item => {
            const checkIcon = item.querySelector('.filter-check-icon');
            if (item.getAttribute('data-target') === target) {
                item.classList.add('active');
                if (checkIcon) checkIcon.classList.remove('d-none');
            } else {
                item.classList.remove('active');
                if (checkIcon) checkIcon.classList.add('d-none');
            }
        });

        // 4. Cập nhật nhãn hiển thị trên nút Dropdown
        if (activeFilterText && labelHtml) {
            activeFilterText.innerHTML = labelHtml;
        }

        // 5. Tự động cuộn thanh pills đến nút đang chọn nếu bị tràn
        const activeBtn = document.querySelector(`.btn-section-filter[data-target="${target}"]`);
        if (activeBtn && activeBtn.scrollIntoView) {
            activeBtn.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });
        }

        // 6. Cuộn khung danh sách thẻ card về đỉnh khi đổi bộ lọc
        const scrollContainer = document.querySelector('.dashboard-cards-scroll');
        if (scrollContainer) {
            scrollContainer.scrollTop = 0;
        }
    }

    // Sự kiện click nút bấm Desktop
    if (desktopButtons.length) {
        desktopButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const target = this.getAttribute('data-target');
                const label = this.innerHTML.trim();
                applySectionFilter(target, label);
            });
        });
    }

    // Sự kiện click mục trong Dropdown Mobile/Tablet
    if (dropdownItems.length) {
        dropdownItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const target = this.getAttribute('data-target');
                const spanContent = this.querySelector('span');
                const label = spanContent ? spanContent.innerHTML.trim() : this.innerText.trim();
                applySectionFilter(target, label);
            });
        });
    }

    // Hỗ trợ lăn chuột ngang mượt mà trên thanh filter pills
    const pillsTrack = document.querySelector('.dashboard-filter-pills');
    if (pillsTrack) {
        pillsTrack.addEventListener('wheel', function(e) {
            if (e.deltaY !== 0) {
                e.preventDefault();
                this.scrollLeft += e.deltaY;
            }
        }, { passive: false });
    }

    // Khởi tạo tooltips
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Chuyển tiếp cuộn chuột từ vùng header hoặc menu bar vào khung thẻ card (ngoại trừ thanh pills cuộn ngang)
    const cardsScroll = document.querySelector('.dashboard-cards-scroll');
    const mainContent = document.querySelector('.main-content');
    if (cardsScroll && mainContent) {
        mainContent.addEventListener('wheel', function(e) {
            if (!e.target.closest('.dashboard-cards-scroll') && !e.target.closest('.dashboard-filter-pills')) {
                cardsScroll.scrollTop += e.deltaY;
            }
        }, { passive: true });
    }
});
