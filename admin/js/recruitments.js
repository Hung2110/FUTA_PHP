document.addEventListener('DOMContentLoaded', function () {
    // Gán id vào modal xóa
    var deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var jobId = button ? button.getAttribute('data-id') : '';
            var deleteInput = document.getElementById('deleteJobId');
            if (deleteInput) deleteInput.value = jobId;
        });
    }

    // Focus vào input đầu tiên khi mở modal thêm/sửa
    var jobModalEl = document.getElementById('jobModal');
    if (jobModalEl && (jobModalEl.dataset.autoOpen === 'true' || window.location.search.includes('edit='))) {
        var jobModal = new bootstrap.Modal(jobModalEl);
        jobModal.show();
        setTimeout(function() {
            var titleInput = document.querySelector('#jobModal input[name="title"]');
            if (titleInput) titleInput.focus();
            jobModalEl.scrollTo(0, 0);
        }, 400);
    }

    // Loading khi submit form thêm/sửa
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            var btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang xử lý...';
            }
        });
    });

    // Chặn submit lại khi nhấn Enter ngoài input
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && e.target.tagName !== 'INPUT') {
            e.preventDefault();
        }
    });
});

