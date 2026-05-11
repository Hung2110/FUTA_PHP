// Chặn click chuột phải (Context Menu)
document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
});

// Chặn các phím tắt phổ biến để mở DevTools và xem source
document.addEventListener('keydown', function(e) {
    // Chặn F12
    if (e.key === 'F12' || e.keyCode === 123) {
        e.preventDefault();
        return false;
    }
    // Chặn Ctrl+Shift+I / Ctrl+Shift+J / Ctrl+Shift+C (Mở DevTools)
    if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C' || e.keyCode === 73 || e.keyCode === 74 || e.keyCode === 67)) {
        e.preventDefault();
        return false;
    }
    // Chặn Ctrl+U (Xem Source)
    if (e.ctrlKey && (e.key === 'U' || e.key === 'u' || e.keyCode === 85)) {
        e.preventDefault();
        return false;
    }
    // Chặn Ctrl+S (Lưu trang web)
    if (e.ctrlKey && (e.key === 'S' || e.key === 's' || e.keyCode === 83)) {
        e.preventDefault();
        return false;
    }
    // Chặn Ctrl+P (In trang web)
    if (e.ctrlKey && (e.key === 'P' || e.key === 'p' || e.keyCode === 80)) {
        e.preventDefault();
        return false;
    }
});