<?php
$applicationMessage = '';
$applicationType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_application'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $cvFile = $_FILES['cv_file'] ?? null;

    if ($fullname === '' || $email === '' || $phone === '' || $position === '') {
        $applicationMessage = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
        $applicationType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $applicationMessage = 'Email không hợp lệ.';
        $applicationType = 'error';
    } elseif (!$cvFile || $cvFile['error'] !== UPLOAD_ERR_OK) {
        $applicationMessage = 'Vui lòng tải lên CV hợp lệ.';
        $applicationType = 'error';
    } elseif ($cvFile['size'] > 5 * 1024 * 1024) { // Giới hạn 5MB
        $applicationMessage = 'Dung lượng file CV không được vượt quá 5MB.';
        $applicationType = 'error';
    } else {
        $allowedExtensions = ['pdf', 'doc', 'docx'];
        $extension = strtolower(pathinfo($cvFile['name'], PATHINFO_EXTENSION));

        // Kiểm tra thêm MIME type thực tế để chống fake đuôi file
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $cvFile['tmp_name']);
        finfo_close($finfo);

        $allowedMimeTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        if (!in_array($extension, $allowedExtensions, true) || !in_array($mimeType, $allowedMimeTypes, true)) {
            $applicationMessage = 'Định dạng CV không được hỗ trợ. Chỉ chấp nhận PDF, DOC, DOCX chuẩn.';
            $applicationType = 'error';
        } else {
            $uploadDir = __DIR__ . '/../uploads/cv/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $safeFileName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', pathinfo($cvFile['name'], PATHINFO_FILENAME));
            $fileName = $safeFileName . '_' . time() . '.' . $extension;
            $uploadPath = $uploadDir . $fileName;
            $dbPath = 'uploads/cv/' . $fileName; // Đường dẫn lưu vào DB

            if (move_uploaded_file($cvFile['tmp_name'], $uploadPath)) {
                $stmt = $conn->prepare("INSERT INTO applications (fullname, email, phone, position, message, cv_file) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $fullname, $email, $phone, $position, $message, $dbPath);

                if ($stmt->execute()) {
                    $application_id = $conn->insert_id;
                    $applicationMessage = 'Ứng tuyển thành công! Chúng tôi sẽ liên hệ với bạn sớm.';
                    $applicationType = 'success';

                    // Thông báo admin
                    $admin_users_query = $conn->query("SELECT id FROM users WHERE role IN ('admin', 'manager', 'recruitment_manager')");
                    if ($admin_users_query && $admin_users_query->num_rows > 0) {
                        $notification_message = "Đơn ứng tuyển mới: " . htmlspecialchars($position) . " từ " . htmlspecialchars($fullname);
                        $notification_link = "view_application.php?id=" . $application_id;
                        $notification_type = 'application';

                        $notify_stmt = $conn->prepare("INSERT INTO notifications (user_id, type, message, link) VALUES (?, ?, ?, ?)");
                        while ($admin_user = $admin_users_query->fetch_assoc()) {
                            $notify_stmt->bind_param("isss", $admin_user['id'], $notification_type, $notification_message, $notification_link);
                            $notify_stmt->execute();
                        }
                        $notify_stmt->close();
                    }
                }
                $stmt->close();
            }
        }
    }
}
?>