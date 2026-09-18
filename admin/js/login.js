function updateClock() {
    const clockTime = document.getElementById('clock-time');
    const clockDate = document.getElementById('clock-date');
    if (!clockTime || !clockDate) return;

    const now = new Date();
    const options = { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' };
    const timeString = now.toLocaleTimeString('vi-VN', { hour12: false });
    const dateString = now.toLocaleDateString('vi-VN', options);
    clockTime.textContent = timeString;
    clockDate.textContent = dateString.charAt(0).toUpperCase() + dateString.slice(1);
}

document.addEventListener('DOMContentLoaded', function() {
    updateClock();
    setInterval(updateClock, 1000);

    // Xử lý ẩn/hiện mật khẩu
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    if (togglePassword && password) {
        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    }
});

