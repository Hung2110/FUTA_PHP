document.addEventListener('DOMContentLoaded', function () {
    const viewProjectModal = document.getElementById('viewProjectModal');
    const viewContentDiv = document.getElementById('viewContent');
    const viewImage = document.getElementById('viewImage');
    const viewVideo = document.getElementById('viewVideo');
    const viewExcerpt = document.getElementById('viewExcerpt');
    const viewStatusBadge = document.getElementById('viewStatusBadge');

    if (viewProjectModal) {
        viewProjectModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (!button) return;

            document.getElementById('viewTitle').textContent = button.dataset.title || '';
            document.getElementById('viewAuthor').textContent = 'Tác giả: ' + (button.dataset.author || 'Admin');
            document.getElementById('viewCreatedAt').textContent = button.dataset.created_at || '';

            if (button.dataset.status) {
                viewStatusBadge.innerHTML = '<span class="badge bg-' + (button.dataset.statusClass || 'secondary') + ' rounded-pill">' + button.dataset.status + '</span>';
                viewStatusBadge.style.display = 'inline-block';
            } else {
                viewStatusBadge.innerHTML = '';
                viewStatusBadge.style.display = 'none';
            }

            if (button.dataset.client && button.dataset.client.trim() !== '') {
                viewExcerpt.textContent = button.dataset.client;
                viewExcerpt.style.display = 'block';
            } else {
                viewExcerpt.textContent = '';
                viewExcerpt.style.display = 'none';
            }

            if (button.dataset.image && button.dataset.image.trim() !== '') {
                viewImage.src = button.dataset.image;
                viewImage.style.display = 'block';
            } else {
                viewImage.src = '';
                viewImage.style.display = 'none';
            }

            if (button.dataset.video && button.dataset.video.trim() !== '') {
                viewVideo.src = button.dataset.video;
                viewVideo.style.display = 'block';
            } else {
                viewVideo.src = '';
                viewVideo.style.display = 'none';
            }

            viewContentDiv.innerHTML = button.dataset.content || '<p class="text-muted">Không có nội dung chi tiết.</p>';
        });

        viewProjectModal.addEventListener('hide.bs.modal', function() {
            const viewVideo = document.getElementById('viewVideo');
            if (viewVideo) viewVideo.pause();
        });
    }
});

