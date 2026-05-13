<?php
session_start();

// Nếu người dùng đã đăng nhập, chuyển hướng đến dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once '../db.php';

$pageTitle = 'Đăng Nhập';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $password_md5 = md5($password);
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND status = 'active'");
        $stmt->bind_param("ss", $username, $password_md5);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            session_regenerate_id(true); // Tái tạo session ID sau khi đăng nhập thành công

            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_fullname'] = $user['fullname'];
            $_SESSION['admin_role'] = trim($user['role']); // Trim để loại bỏ khoảng trắng thừa từ DB nếu có
            
            // Log activity
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, module, ip) VALUES (?, ?, ?, ?)");
            $action = "Đăng nhập hệ thống";
            $module = "Authentication";
            $log_stmt->bind_param("isss", $user['id'], $action, $module, $ip);
            $log_stmt->execute();
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
        }
        $stmt->close();
    } else {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | FUTA Advertising' : 'FUTA Advertising'; ?></title>
    <link rel="icon" href="../assets/images/logo/futa.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <style>
        :root {
            --primary: #3674ff;
            --primary-dark: #0052d9;
            --text-muted: #6b7280;
            --bg: #f5f7fb;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: stretch;
            color: #0f172a;
        }
        .auth-wrapper {
            display: flex;
            width: 100%;
        }
        .auth-illustration {
            flex: 1.05;
            background: url('../assets/images/banners/back.jpeg') center/cover no-repeat;
            position: relative;
            min-height: 100vh;
            display: none;
        }
        .auth-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(15,23,42,0.68), rgba(54,116,255,0.55));
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            padding: 30px;
        }
        .clock-card {
            background: rgba(255,255,255,0.24);
            color: #fff;
            padding: 18px 26px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            min-width: 230px;
            box-shadow: 0 20px 50px rgba(15,23,42,0.35);
        }
        .clock-time {
            font-size: 2.1rem;
            font-weight: 600;
            letter-spacing: 2px;
            margin-bottom: 6px;
        }
        .clock-date {
            font-size: 0.95rem;
            opacity: 0.95;
        }
        .auth-form {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 5vw;
            background: #fff;
        }
        .form-card {
            width: 100%;
            max-width: 420px;
        }
       
        .page-logo {
            display: block;
            max-width: 300px;
            margin: 0 auto 20px auto;
        }
        .page-subtitle {
            color: var(--text-muted);
            margin-bottom: 30px;
        }
        .form-label {
            font-weight: 500;
            color: #1f2937;
        }
        .form-control {
            border-radius: 32px;
            padding: 12px 18px;
            border: 1.5px solid #e2e8f0;
            box-shadow: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(54,116,255,0.2);
        }
        .input-group-text {
            border-radius: 32px;
            border: 1.5px solid #e2e8f0;
            background: transparent;
        }
        .input-group-text:first-child {
            border-right: 0;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        .input-group-text.toggle-password {
            border-left: 0;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            cursor: pointer;
            color: var(--text-muted);
        }
        .input-group .form-control {
            border-left: 0;
        }
        .input-group .form-control:not(:last-child) {
            border-right: 0;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        .forgot-link {
            text-align: right;
            display: block;
            font-weight: 500;
            color: var(--primary);
            text-decoration: none;
            margin-bottom: 22px;
        }
        .forgot-link:hover { text-decoration: underline; }
        .btn-primary {
            border-radius: 999px;
            padding: 12px;
            font-size: 1.05rem;
            font-weight: 600;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 25px rgba(54,116,255,0.3);
        }
        .login-hint {
            margin-top: 26px;
            padding: 16px 20px;
            border-radius: 18px;
            background: #eef2ff;
            color: var(--text-muted);
            font-size: 0.95rem;
        }
        .login-hint strong { color: #111827; }
        @media (min-width: 992px) { .auth-illustration { display: block; } }
        @media (max-width: 575.98px) { .form-card { max-width: 100%; } }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-illustration">
            <div class="auth-overlay">
                <div class="clock-card">
                    <div class="clock-time" id="clock-time">00:00:00</div>
                    <div class="clock-date" id="clock-date">Thứ, ngày tháng năm</div>
                </div>
            </div>
        </div>
        <div class="auth-form">
            <div class="form-card">
                <img src="../assets/images/logo/Advertising.png" class="page-logo">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Tên đăng nhập</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Nhập tên đăng nhập..." required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-1">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Nhập mật khẩu..." required>
                            <span class="input-group-text toggle-password" id="togglePassword"><i class="fas fa-eye"></i></span>
                        </div>
                    </div>

                    <a href="#" class="forgot-link">Quên mật khẩu?</a>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        Đăng nhập
                    </button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateClock() {
            const now = new Date();
            const options = { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' };
            const timeString = now.toLocaleTimeString('vi-VN', { hour12: false });
            const dateString = now.toLocaleDateString('vi-VN', options);
            document.getElementById('clock-time').textContent = timeString;
            document.getElementById('clock-date').textContent = dateString.charAt(0).toUpperCase() + dateString.slice(1);
        }
        updateClock();
        setInterval(updateClock, 1000);

        // Xử lý ẩn/hiện mật khẩu
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    </script>
</body>
</html>