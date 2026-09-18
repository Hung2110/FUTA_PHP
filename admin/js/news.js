document.addEventListener('DOMContentLoaded', function() {
    const viewPostModal = document.getElementById('viewPostModal');
    const viewContentDiv = document.getElementById('viewContent');
    const viewImage = document.getElementById('viewImage');

    if (viewPostModal) {
        viewPostModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (!button) return;

            document.getElementById('viewTitle').textContent = button.dataset.title || '';
            document.getElementById('viewAuthor').textContent = 'Tác giả: ' + (button.dataset.author || 'Admin');
            document.getElementById('viewCreatedAt').textContent = button.dataset.created_at || '';
            
            if (button.dataset.image && button.dataset.image.trim() !== '') {
                viewImage.src = button.dataset.image;
                viewImage.style.display = 'block';
            } else {
                viewImage.src = '';
                viewImage.style.display = 'none';
            }
            
            // Set content for the div
            viewContentDiv.innerHTML = button.dataset.content || '<p class="text-muted">Không có nội dung chi tiết.</p>';
        });
    }
});

