document.addEventListener('DOMContentLoaded', function() {
    const url = (typeof currentUrl !== 'undefined') ? currentUrl : window.location.href; // currentUrl được định nghĩa trong news_single.php
    const originalTitle = (typeof postTitle !== 'undefined') ? postTitle : document.title;
    let translatedTitle = originalTitle;

    const facebookBtn = document.getElementById('share-facebook');
    const zaloBtn = document.getElementById('share-zalo');
    const copyBtn = document.getElementById('copy-link');
    const copySuccess = document.getElementById('copy-success');

    function updateShareLinks() {
        if (facebookBtn) {
            facebookBtn.href = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
        }
        if (zaloBtn) {
            zaloBtn.href = `https://sp.zalo.me/share_inline?url=${encodeURIComponent(url)}&title=${encodeURIComponent(translatedTitle)}`;
        }
    }

    // Lắng nghe sự kiện thay đổi ngôn ngữ từ i18n.js
    document.addEventListener('languageChanged', async (e) => {
        const lang = e.detail.lang;
        if (lang === 'vi') {
            translatedTitle = originalTitle;
        } else {
            // Sử dụng hàm googleTranslate đã có trong i18n.js
            if (window.googleTranslate) {
                translatedTitle = await window.googleTranslate(originalTitle, 'vi', lang);
            }
        }
        updateShareLinks();
    });

    // Cập nhật link lần đầu khi tải trang
    updateShareLinks();

    // Xử lý nút sao chép liên kết
    if (copyBtn) {
        copyBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(url).then(() => {
                copySuccess.style.display = 'inline';
                setTimeout(() => { copySuccess.style.display = 'none'; }, 2000);
            });
        });
    }
});