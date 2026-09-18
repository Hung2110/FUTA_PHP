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

    // Khởi tạo tooltips
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
