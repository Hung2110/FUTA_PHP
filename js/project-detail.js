document.addEventListener('DOMContentLoaded', function() {
    // Biến `currentUrl` và `projectTitle` được lấy từ thẻ script trong file PHP

    // Nút chia sẻ Facebook
    const facebookBtn = document.getElementById('share-facebook');
    if (facebookBtn) {
        facebookBtn.href = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentUrl)}`;
    }

    // Nút chia sẻ Zalo
    const zaloBtn = document.getElementById('share-zalo');
    if (zaloBtn) {
        zaloBtn.href = `https://sp.zalo.me/share_inline?url=${encodeURIComponent(currentUrl)}&title=${encodeURIComponent(projectTitle)}`;
    }

    // Nút sao chép liên kết
    const copyBtn = document.getElementById('copy-link');
    const copySuccess = document.getElementById('copy-success');
    if (copyBtn) {
        copyBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(currentUrl).then(() => {
                copySuccess.style.display = 'inline';
                setTimeout(() => { copySuccess.style.display = 'none'; }, 2000);
            });
        });
    }
});