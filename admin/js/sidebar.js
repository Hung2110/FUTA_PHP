document.addEventListener('DOMContentLoaded', function() {
    // Xử lý đóng/mở Sidebar Drawer trên Mobile và Tablet (< 992px)
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');

    function openSidebar() {
        if (sidebar) sidebar.classList.add('show');
        if (backdrop) backdrop.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('show');
        if (backdrop) backdrop.classList.remove('show');
        document.body.style.overflow = '';
    }

    if (sidebarToggle) sidebarToggle.addEventListener('click', openSidebar);
    if (sidebarClose) sidebarClose.addEventListener('click', closeSidebar);
    if (backdrop) backdrop.addEventListener('click', closeSidebar);

    // Đóng sidebar khi nhấn phím ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar && sidebar.classList.contains('show')) {
            closeSidebar();
        }
    });

    // Xử lý thông báo (Notification)
    const markAllBtn = document.getElementById('markAllNotificationsRead');
    const badgeEl = document.getElementById('notificationBadge') || document.querySelector('.notification-badge');
    const countBadge = document.getElementById('unreadCountBadge');

    // 1. Đánh dấu tất cả thông báo là đã đọc
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Không đóng dropdown để người dùng nhìn thấy sự thay đổi

            // Cập nhật giao diện tức thì (Optimistic UI)
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
                item.classList.add('read');
                const dot = item.querySelector('.unread-dot');
                if (dot) dot.remove();
                const dotWrapper = item.querySelector('.unread-dot-wrapper');
                if (dotWrapper) dotWrapper.remove();
                const msg = item.querySelector('.notif-msg');
                if (msg) msg.classList.remove('fw-semibold');
            });

            const bellDot = document.getElementById('notificationBadge') || document.querySelector('.notification-badge');
            if (bellDot) bellDot.remove();
            if (badgeEl) badgeEl.remove();
            if (countBadge) {
                countBadge.style.display = 'none';
                countBadge.classList.add('d-none');
            }
            markAllBtn.style.display = 'none';

            // Gửi API lên server
            fetch('mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=mark_all'
            }).catch(error => {
                console.error('Lỗi khi đánh dấu đã đọc tất cả thông báo:', error);
            });
        });
    }

    // 2. Đánh dấu từng thông báo khi bấm vào
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function(e) {
            const notificationId = this.dataset.id;
            const isUnread = this.classList.contains('unread') || this.classList.contains('fw-bold');
            const href = this.getAttribute('href');

            if (isUnread && notificationId) {
                e.preventDefault(); // Chặn tạm thời để gửi request

                // Cập nhật giao diện tức thì
                this.classList.remove('unread', 'fw-bold');
                this.classList.add('read');
                const dot = this.querySelector('.unread-dot');
                if (dot) dot.remove();
                const dotWrapper = this.querySelector('.unread-dot-wrapper');
                if (dotWrapper) dotWrapper.remove();
                const msg = this.querySelector('.notif-msg');
                if (msg) msg.classList.remove('fw-semibold');

                const currentBadge = document.getElementById('notificationBadge') || document.querySelector('.notification-badge');
                if (currentBadge) {
                    let count = parseInt(currentBadge.getAttribute('data-count') || currentBadge.textContent || '1') - 1;
                    if (count > 0) {
                        currentBadge.setAttribute('data-count', count);
                        currentBadge.title = 'Có ' + count + ' thông báo mới';
                        if (countBadge) countBadge.textContent = count + ' mới';
                    } else {
                        currentBadge.remove();
                        if (countBadge) {
                            countBadge.style.display = 'none';
                            countBadge.classList.add('d-none');
                        }
                        if (markAllBtn) markAllBtn.style.display = 'none';
                    }
                }

                // Gửi API và chuyển trang
                fetch('mark_notification_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + notificationId
                }).catch(error => {
                    console.error('Lỗi khi đánh dấu đã đọc thông báo:', error);
                }).finally(() => {
                    if (href && href !== '#' && !href.startsWith('javascript:')) {
                        window.location.href = href;
                    }
                });
            }
        });
    });

    // Xử lý tóm tắt & bấm vào để mở rộng / thu gọn nội dung dài (.text-expandable)
    document.addEventListener('click', function(e) {
        if (e.target.closest('a') || e.target.closest('button') || e.target.closest('input') || e.target.closest('select')) {
            return;
        }
        const expandable = e.target.closest('.text-expandable');
        if (expandable) {
            expandable.classList.toggle('expanded');
        }
    });

    // Tự động chuyển đổi .filter-btn-group đã có sẵn thành Dropdown khi ở khung màn hình nhỏ (< 768px)
    function initResponsiveFilterDropdown() {
        document.querySelectorAll('.filter-btn-group').forEach(function(group) {
            if (group.dataset.dropdownReady) return;
            group.dataset.dropdownReady = 'true';

            // Tìm nút đang được chọn (active)
            var activeBtn = group.querySelector('.btn-primary') || 
                            group.querySelector('.btn.active') || 
                            group.querySelector('.btn:not(.btn-outline-secondary)');
            if (!activeBtn) {
                activeBtn = group.querySelector('.btn');
            }
            if (!activeBtn) return;

            // Đánh dấu group để CSS ẩn trên mobile, hiện trên desktop
            group.classList.add('has-mobile-dropdown');

            // Tạo Dropdown wrapper cho mobile
            var dropdownWrap = document.createElement('div');
            dropdownWrap.className = 'dropdown d-md-none w-100 filter-dropdown-mobile';

            // Tạo nút Dropdown Toggle
            var toggleBtn = document.createElement('button');
            toggleBtn.className = 'btn btn-outline-secondary dropdown-toggle w-100 d-flex justify-content-between align-items-center bg-white';
            toggleBtn.type = 'button';
            toggleBtn.setAttribute('data-bs-toggle', 'dropdown');
            toggleBtn.setAttribute('aria-expanded', 'false');

            toggleBtn.innerHTML = '<span class="d-flex align-items-center text-truncate"><i class="fas fa-filter text-primary me-2"></i><span class="text-muted small me-1">Lọc:</span><strong class="text-dark d-inline-flex align-items-center gap-1">' + activeBtn.innerHTML + '</strong></span>';

            // Tạo Dropdown Menu
            var menu = document.createElement('ul');
            menu.className = 'dropdown-menu dropdown-menu-end w-100 shadow-sm border py-1';
            menu.style.maxHeight = '320px';
            menu.style.overflowY = 'auto';

            // Duyệt qua tất cả các nút bấm đã có sẵn trong group để tạo items
            group.querySelectorAll('.btn').forEach(function(btn) {
                var li = document.createElement('li');
                var a = document.createElement('a');
                var isActive = (btn === activeBtn);
                a.className = 'dropdown-item d-flex justify-content-between align-items-center py-2 ' + (isActive ? 'active fw-semibold' : '');
                a.href = btn.href;
                a.innerHTML = btn.innerHTML;

                if (isActive && !a.querySelector('.fa-check')) {
                    var check = document.createElement('i');
                    check.className = 'fas fa-check small ms-2';
                    a.appendChild(check);
                }

                li.appendChild(a);
                menu.appendChild(li);
            });

            dropdownWrap.appendChild(toggleBtn);
            dropdownWrap.appendChild(menu);

            // Chèn dropdown vào ngay trước group đã có sẵn
            group.parentNode.insertBefore(dropdownWrap, group);

            // Khởi tạo Bootstrap Dropdown instance nếu có sẵn
            if (window.bootstrap && window.bootstrap.Dropdown) {
                new bootstrap.Dropdown(toggleBtn);
            }
        });
    }

    initResponsiveFilterDropdown();
});

