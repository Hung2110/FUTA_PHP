<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'db.php';
require_once 'vendor/autoload.php'; // Đảm bảo đã cài đặt PHPMailer qua Composer

$success = isset($_GET['success']) && $_GET['success'] == '1';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($name && $email && $phone && $content) {
        $stmt = $conn->prepare("INSERT INTO contact (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $name, $email, $phone, $subject, $content);
        if ($stmt->execute()) {
            $contact_id = $conn->insert_id; // Lấy ID của liên hệ vừa tạo
            $stmt->close();

            // 1. Tạo thông báo trong hệ thống admin (chuông thông báo) cho admin & contact_manager
            $admin_users_query = $conn->query("SELECT id FROM users WHERE (FIND_IN_SET('admin', role) > 0 OR FIND_IN_SET('contact_manager', role) > 0) AND status = 'active'");
            if ($admin_users_query && $admin_users_query->num_rows > 0) {
                $notification_message = "Có liên hệ mới từ: " . htmlspecialchars($name);
                $notification_link = "view_contact.php?id=" . $contact_id;
                $notification_type = 'contact';

                $notify_stmt = $conn->prepare("INSERT INTO notifications (user_id, type, message, link) VALUES (?, ?, ?, ?)");
                while ($admin_user = $admin_users_query->fetch_assoc()) {
                    if (isset($admin_user['id'])) {
                        $notify_stmt->bind_param("isss", $admin_user['id'], $notification_type, $notification_message, $notification_link);
                        $notify_stmt->execute();
                    }
                }
                $notify_stmt->close();
            }

            // 2. Lấy danh sách email của người được Admin phân quyền Quản lý liên hệ (contact_manager)
            $managerEmails = [];
            $manager_query = $conn->query("SELECT id, fullname, email FROM users WHERE FIND_IN_SET('contact_manager', role) > 0 AND status = 'active'");
            if ($manager_query && $manager_query->num_rows > 0) {
                while ($mgr = $manager_query->fetch_assoc()) {
                    if (!empty($mgr['email']) && filter_var($mgr['email'], FILTER_VALIDATE_EMAIL)) {
                        $managerEmails[$mgr['email']] = $mgr['fullname'] ?? 'Quản Lý Liên Hệ';
                    }
                }
            }

            // Nếu chưa có tài khoản nào được phân quyền contact_manager riêng, gửi thông báo dự phòng tới Admin
            if (empty($managerEmails)) {
                $fallback_query = $conn->query("SELECT id, fullname, email FROM users WHERE FIND_IN_SET('admin', role) > 0 AND status = 'active'");
                if ($fallback_query && $fallback_query->num_rows > 0) {
                    while ($adm = $fallback_query->fetch_assoc()) {
                        if (!empty($adm['email']) && filter_var($adm['email'], FILTER_VALIDATE_EMAIL)) {
                            $managerEmails[$adm['email']] = $adm['fullname'] ?? 'Quản Trị Viên';
                        }
                    }
                }
            }

            // Dự phòng cuối cùng qua biến môi trường ADMIN_EMAILS hoặc email mặc định của công ty
            if (empty($managerEmails)) {
                $envEmails = getenv('ADMIN_EMAILS');
                $defaultList = $envEmails ? array_map('trim', explode(',', $envEmails)) : ['futaadvertising@futa.vn'];
                foreach ($defaultList as $dEmail) {
                    if (filter_var($dEmail, FILTER_VALIDATE_EMAIL)) {
                        $managerEmails[$dEmail] = 'Quản Lý Liên Hệ FUTA';
                    }
                }
            }

            // 3. Chuẩn bị đường dẫn xem chi tiết liên hệ trên trang Admin
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseDir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            $adminViewUrl = $protocol . $host . $baseDir . '/admin/view_contact.php?id=' . $contact_id;

            // 4. Nội dung Email HTML chuẩn nhận diện thương hiệu FUTA
            $displaySubject = $subject ?: 'Tư vấn dịch vụ quảng cáo';
            $currentTime = date('d/m/Y H:i:s');
            $mailSubject = "[FUTA ADVERTISING] Liên hệ mới từ $name - " . $displaySubject;
            
            $mailBody = '
            <!DOCTYPE html>
            <html lang="vi">
            <head>
                <meta charset="UTF-8">
                <style>
                    body { font-family: "Segoe UI", Arial, sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 0; background-color: #f4f6f9; }
                    .email-wrapper { max-width: 620px; margin: 25px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.07); border: 1px solid #e2e8f0; }
                    .email-header { background: linear-gradient(135deg, #003366 0%, #002244 100%); padding: 26px 30px; text-align: center; color: #ffffff; border-bottom: 4px solid #ff6600; }
                    .email-header h2 { margin: 0; font-size: 22px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; }
                    .email-header p { margin: 6px 0 0 0; font-size: 13px; color: #cbd5e1; }
                    .email-body { padding: 28px 30px; }
                    .badge-alert { display: inline-block; background-color: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-bottom: 18px; }
                    .info-table { width: 100%; border-collapse: collapse; margin-bottom: 22px; }
                    .info-table td { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
                    .info-table td.label { width: 35%; font-weight: 600; color: #475569; background-color: #f8fafc; }
                    .info-table td.value { color: #1e293b; }
                    .message-box { background: #f8fafc; border-left: 4px solid #ff6600; border-radius: 6px; padding: 16px; margin-bottom: 25px; }
                    .message-title { font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px; }
                    .message-content { font-size: 14px; color: #1e293b; white-space: pre-wrap; word-break: break-word; line-height: 1.6; }
                    .btn-action { display: inline-block; background: #ff6600; color: #ffffff !important; text-decoration: none; padding: 12px 28px; font-size: 14px; font-weight: 700; border-radius: 8px; text-align: center; box-shadow: 0 3px 8px rgba(255, 102, 0, 0.35); }
                    .email-footer { background: #f8fafc; padding: 18px 30px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
                </style>
            </head>
            <body>
                <div class="email-wrapper">
                    <div class="email-header">
                        <h2>FUTA ADVERTISING</h2>
                        <p>Hệ thống Quản lý Yêu cầu & Liên hệ Khách hàng</p>
                    </div>
                    <div class="email-body">
                        <div class="badge-alert">
                            📬 Thông báo liên hệ mới từ Website
                        </div>
                        <p style="margin-top:0; font-size: 15px;">Xin chào <strong>Quản lý liên hệ</strong>,</p>
                        <p style="font-size: 14px; color: #475569;">Website FUTA Advertising vừa tiếp nhận một yêu cầu liên hệ / tư vấn dịch vụ mới từ khách hàng. Dưới đây là thông tin chi tiết:</p>
                        
                        <table class="info-table">
                            <tr>
                                <td class="label">Họ và tên:</td>
                                <td class="value"><strong>' . htmlspecialchars($name) . '</strong></td>
                            </tr>
                            <tr>
                                <td class="label">Số điện thoại:</td>
                                <td class="value"><a href="tel:' . htmlspecialchars($phone) . '" style="color:#003366; font-weight:600; text-decoration:none;">' . htmlspecialchars($phone) . '</a></td>
                            </tr>
                            <tr>
                                <td class="label">Email khách hàng:</td>
                                <td class="value"><a href="mailto:' . htmlspecialchars($email) . '" style="color:#003366; text-decoration:none;">' . htmlspecialchars($email) . '</a></td>
                            </tr>
                            <tr>
                                <td class="label">Nhu cầu / Website:</td>
                                <td class="value">' . htmlspecialchars($displaySubject) . '</td>
                            </tr>
                            <tr>
                                <td class="label">Thời gian tiếp nhận:</td>
                                <td class="value">' . $currentTime . '</td>
                            </tr>
                        </table>

                        <div class="message-box">
                            <div class="message-title">Nội dung tin nhắn khách hàng:</div>
                            <div class="message-content">' . nl2br(htmlspecialchars($content)) . '</div>
                        </div>

                        <div style="text-align: center; margin: 25px 0 10px 0;">
                            <a href="' . htmlspecialchars($adminViewUrl) . '" class="btn-action" target="_blank">
                                Xem & Xử Lý Liên Hệ Trên Admin &rarr;
                            </a>
                        </div>
                    </div>
                    <div class="email-footer">
                        <p style="margin: 0 0 4px 0;">Email này được gửi tự động đến tài khoản được phân quyền <strong>Quản Lý Liên Hệ</strong> trên hệ thống FUTA Advertising.</p>
                        <p style="margin: 0;">Bạn có thể phản hồi trực tiếp cho khách hàng bằng cách bấm nút <strong>Reply</strong> email này.</p>
                    </div>
                </div>
            </body>
            </html>';

            // 5. Khởi tạo và cấu hình PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->SMTPDebug = 0; // Tắt debug ra màn hình để tránh làm hỏng HTTP headers
                $mail->isSMTP();
                $mail->Host       = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = getenv('SMTP_USER') ?: 'futaadvertising@futa.vn';
                $mail->Password   = getenv('SMTP_PASS') ?: 'xtonupudcelpoixh';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = getenv('SMTP_PORT') ? (int)getenv('SMTP_PORT') : 587;
                $mail->CharSet    = 'UTF-8';
                
                // Bỏ qua xác thực chứng chỉ SSL khi kiểm thử trên local / Laragon / XAMPP
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
                
                $senderEmail = getenv('SMTP_USER') ?: 'futaadvertising@futa.vn';
                $mail->setFrom($senderEmail, 'FUTA Advertising');
                $mail->addReplyTo($email, $name); // Bấm Reply sẽ gửi thẳng vào email của khách hàng
                $mail->isHTML(true);
                $mail->Subject = $mailSubject;
                $mail->Body    = $mailBody;
                $mail->AltBody = "Khách hàng: $name\nSố điện thoại: $phone\nEmail: $email\nWebsite/Nhu cầu: $displaySubject\nThời gian: $currentTime\nNội dung:\n$content";
                
                foreach ($managerEmails as $mgrEmail => $mgrName) {
                    $mail->addAddress($mgrEmail, $mgrName ?: 'Quản Lý Liên Hệ');
                }
                
                $mail->send();
            } catch (Exception $e) {
                // Ghi log lỗi gửi email vào server log nhưng không ngắt luồng thông báo thành công của khách hàng
                error_log("Lỗi gửi email liên hệ (PHPMailer): " . $mail->ErrorInfo . " | Exception: " . $e->getMessage());
            }

            header("Location: contact.php?success=1");
            exit;
        } else {
            $error = 'Có lỗi xảy ra khi lưu thông tin. Vui lòng thử lại!';
        }
    } else {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    }
}

$pageTitle = 'Liên Hệ';
$pageStyles = ['css/contact.css'];
$bodyClass = 'contact-page';
include 'includes/header.php';
?>

    <canvas id="background-canvas"></canvas>

    <div id="blue-shapes-container">
        <div class="blue-shape"></div>
        <div class="blue-shape"></div>
        <div class="blue-shape"></div>
        <div class="blue-shape"></div>
    </div>

    <div class="map-section">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.5691585731315!2d106.69154027586865!3d10.767650059353896!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f15ff909021%3A0x3db151a2dfeec426!2zMjE4IMSQ4buBIFRow6FtLCBQaMaw4budbmcgUGjhuqFtIE5nxakgTMOjbywgUXXhuq1uIDEsIEjhu5MgQ2jDrSBNaW5oLCBWaeG7h3QgTmFt!5e0!3m2!1svi!2s!4v1756373888967!5m2!1svi!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>    
    </div>

    <div class="page-container">
        <h2 class="contact-title" data-i18n="contact.title">Liên hệ FUTA Advertising</h2>

        <div class="contact-content">
            <div class="contact-info">
                <div class="info-group">
                    <h4 data-i18n="contact.headquarters">Trụ sở chính</h4>
                    <p><i class="bi bi-geo-alt-fill"></i> <span data-i18n="footer.address">Số 218 Đề Thám, Phường Bến Thành, TP. Hồ Chí Minh</span></p>
                </div>
                <div class="info-group">
                    <h4 data-i18n="contact.hotline">Tổng đài</h4>
                    <p><i class="bi bi-telephone-fill"></i> <a href="tel:19006912" style="color: inherit; text-decoration: none;">1900 6912</a></p>
                </div>
                <div class="info-group">
                    <h4 data-i18n="contact.email_website">Email & Website</h4>
                    <p><i class="bi bi-envelope-fill"></i> <a href="mailto:futaadvertising@futa.vn" style="color: inherit; text-decoration: none;">futaadvertising@futa.vn</a></p>
                    <p><i class="bi bi-globe2"></i> <a href="https://futaadvertising.vn" target="_blank" style="color: inherit; text-decoration: none;">futaadvertising.vn</a></p>
                </div>
                <div class="info-group">
                    <h4 data-i18n="contact.social_media">Mạng xã hội</h4>
                    <div class="social-iconss">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-chat-dots"></i></a>
                        <a href="#"><i class="bi bi-messenger"></i></a>
                    </div>
                </div>

                <div class="illustration-container">
                    <img src="assets/images/icon/icon.jpeg" alt="Hình minh họa" class="illustration" width="400" height="300" loading="lazy" decoding="async">
                </div>
            </div>

            <div class="contact-form">
                <?php if ($success): ?>
                    <div class="alert alert-success">Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.</div>
                <?php else: ?>
                <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                <form action="#" method="post" autocomplete="off">
                    <div class="form-group">
                        <input type="text" id="name" name="name" placeholder="Tên của bạn *" data-i18n-placeholder="contact.form_name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <input type="email" id="email" name="email" placeholder="Email *" data-i18n-placeholder="contact.form_email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <input type="tel" id="phone" name="phone" placeholder="Điện thoại *" data-i18n-placeholder="contact.form_phone" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <input type="text" id="subject" name="subject" placeholder="Website" data-i18n-placeholder="contact.form_subject" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <textarea id="content" name="content" placeholder="Nội Dung *" data-i18n-placeholder="contact.form_content" required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="submit-btn" data-i18n="contact.form_submit">Nhận tư vấn</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="js/contact.js"></script>


<!-- Nút chuyển trang trên Mobile -->
<a href="recruitment.php" class="mobile-page-nav-btn prev"><i class="fas fa-chevron-left"></i></a>
<a href="index.php" class="mobile-page-nav-btn next"><i class="fas fa-chevron-right"></i></a>
  
<?php include 'includes/footer.php'; ?>
