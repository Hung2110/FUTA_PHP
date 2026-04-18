<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

                    // --- Chỉ gửi email thông báo cho địa chỉ cố định ---
                    $adminEmails = ['hung.nguyen@futa.vn'];

                    // --- Gửi email thông báo qua PHPMailer kèm CV đính kèm ---
                    require_once __DIR__ . '/../vendor/autoload.php'; // Đường dẫn chính xác từ thư mục includes
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'nguyenquochung0509@gmail.com'; // Email gửi đi
                        $mail->Password   = 'omxuvvzaacrmnkyf'; // Mật khẩu ứng dụng (đã tạo)
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;
                        $mail->CharSet    = 'UTF-8';

                        $mail->setFrom('nguyenquochung0509@gmail.com', 'FUTA Tuyển Dụng');
                        
                        // Gửi cho tất cả quản trị viên tìm được
                        foreach ($adminEmails as $adminEmail) {
                            $mail->addAddress($adminEmail);
                        }
                        $mail->addReplyTo($email, $fullname); // Bấm Reply sẽ trả lời thẳng ứng viên

                        // Đính kèm file CV đã được lưu
                        $mail->addAttachment($uploadPath, $cvFile['name']);

                        $mail->isHTML(true);
                        $mail->Subject = "[FUTA Tuyển Dụng] Đơn ứng tuyển mới: " . $position;
                        $mail->Body    = "<h3>Có một ứng viên mới vừa nộp hồ sơ:</h3>" .
                                         "<p><strong>Vị trí ứng tuyển:</strong> " . htmlspecialchars($position) . "</p>" .
                                         "<p><strong>Họ tên:</strong> " . htmlspecialchars($fullname) . "</p>" .
                                         "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>" .
                                         "<p><strong>Số điện thoại:</strong> " . htmlspecialchars($phone) . "</p>" .
                                         "<p><strong>Thông điệp:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>";
                        $mail->send();
                    } catch (Exception $e) {
                        error_log("Lỗi gửi email tuyển dụng: {$mail->ErrorInfo}");
                    }
                }
                $stmt->close();
            }
        }
    }
}
?>