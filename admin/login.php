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
    <link rel="stylesheet" href="css/login.css">
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
    <script src="js/login.js"></script>
</body>
</html>