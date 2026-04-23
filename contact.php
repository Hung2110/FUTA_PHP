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
    if ($name && $email && $phone && $subject && $content) {
        $stmt = $conn->prepare("INSERT INTO contact (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $name, $email, $phone, $subject, $content);
        if ($stmt->execute()) {
            $contact_id = $conn->insert_id; // Lấy ID của liên hệ vừa tạo
            $stmt->close();

            // --- Tạo thông báo cho admin/contact_manager ---
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
            // --- Kết thúc tạo thông báo ---
        }
        
        // --- Chỉ gửi email thông báo cho địa chỉ cố định ---
        $adminEmails = ['hung.nguyen@futa.vn'];
        
        $mailSubject = "[FUTA ADVERTISING] Liên hệ mới từ $name";
        $mailBody = "<h3>Bạn nhận được một liên hệ mới từ website FUTA:</h3>" .
            "<p><strong>Tên Khách Hàng:</strong> " . htmlspecialchars($name) . "</p>" .
            "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>" .
            "<p><strong>Điện thoại:</strong> " . htmlspecialchars($phone) . "</p>" .
            "<p><strong>Website:</strong> " . htmlspecialchars($subject) . "</p>" .
            "<p><strong>Nội dung:</strong><br>" . nl2br(htmlspecialchars($content)) . "</p>";

        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = 0; // Tắt hiển thị chi tiết quá trình kết nối với Google
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Thay bằng SMTP server của bạn (VD: smtp.gmail.com)
            $mail->SMTPAuth   = true;
            $mail->Username   = 'nguyenquochung0509@gmail.com'; // Thay bằng email gửi đi của bạn
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Password   = 'pqbxhhjdkrhqwhux';    // Mật khẩu ứng dụng viết liền không khoảng trắng
            $mail->Port       = 587; // Sử dụng cổng 587 cho STARTTLS của Gmail
            $mail->CharSet    = 'UTF-8'; // Bổ sung để hỗ trợ tiếng Việt không bị lỗi font
            $mail->setFrom('nguyenquochung0509@gmail.com', 'FUTA Website');
            $mail->addReplyTo($email, $name);
            $mail->isHTML(true);
            $mail->Subject = $mailSubject;
            $mail->Body    = $mailBody;
            
            foreach ($adminEmails as $adminEmail) {
                $mail->addAddress($adminEmail);
            }
            
            $mail->send();
            
            header("Location: contact.php?success=1");
            exit;
        } catch (Exception $e) {
            // Bắt lỗi và thông báo cho người dùng, đồng thời ghi log
            error_log("Gửi email thất bại. Lỗi: {$mail->ErrorInfo}");
            $error = "Đã lưu thông tin liên hệ nhưng hệ thống gặp lỗi khi gửi email thông báo. Vui lòng thử lại sau.";
            $success = false;
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
                    <p><i class="bi bi-telephone-fill"></i> 1900 6912</p>
                </div>
                <div class="info-group">
                    <h4 data-i18n="contact.email_website">Email & Website</h4>
                    <p><i class="bi bi-envelope-fill"></i> futaadvertising@futa.vn</p>
                    <p><i class="bi bi-globe2"></i> futaads.vn</p>
                </div>
                <div class="info-group">
                    <h4 data-i18n="contact.social_media">Mạng xã hội</h4>
                    <div class="social-icons">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-chat-dots"></i></a>
                        <a href="#"><i class="bi bi-messenger"></i></a>
                    </div>
                </div>

                <div class="illustration-container">
                    <img src="assets/images/icon/icon.jpeg" alt="Hình minh họa" class="illustration">
                </div>
            </div>

            <div class="contact-form">
                <?php if ($success): ?>
                    <div class="alert alert-success">Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.</div>
                    <script>
                        // Tự động tải lại trang sạch sau 3 giây
                        setTimeout(function() {
                            window.location.href = 'contact.php';
                        }, 3000);
                    </script>
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
                        <input type="text" id="subject" name="subject" placeholder="Website *" data-i18n-placeholder="contact.form_subject" required value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
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
    <script>
        const canvas = document.getElementById('background-canvas');
        const ctx = canvas.getContext('2d');
        
        let width = canvas.width = window.innerWidth;
        let height = canvas.height = window.innerHeight;
        
        const particles = [];
        const maxParticles = 100;
        
        function Particle(x, y, radius, color) {
            this.x = x;
            this.y = y;
            this.radius = radius;
            this.color = color;
            this.velocity = {
                x: (Math.random() - 0.5) * 0.5,
                y: (Math.random() - 0.5) * 0.5
            };
            
            this.draw = function() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2, false);
                ctx.fillStyle = this.color;
                ctx.fill();
            };
            
            this.update = function() {
                this.x += this.velocity.x;
                this.y += this.velocity.y;
                
                if (this.x < 0 || this.x > width) this.velocity.x = -this.velocity.x;
                if (this.y < 0 || this.y > height) this.velocity.y = -this.velocity.y;
                
                this.draw();
            };
        }
        
        function init() {
            particles.length = 0;
            for (let i = 0; i < maxParticles; i++) {
                const radius = Math.random() * 2 + 1;
                const x = Math.random() * (width - radius * 2) + radius;
                const y = Math.random() * (height - radius * 2) + radius;
                const color = 'rgba(190, 190, 190, 0.5)';
                particles.push(new Particle(x, y, radius, color));
            }
        }
        
        function connectParticles() {
            for (let i = 0; i < particles.length; i++) {
                for (let j = i; j < particles.length; j++) {
                    const p1 = particles[i];
                    const p2 = particles[j];
                    const distance = Math.sqrt((p1.x - p2.x)**2 + (p1.y - p2.y)**2);
                    
                    const maxDistance = 120;
                    if (distance < maxDistance) {
                        ctx.beginPath();
                        ctx.moveTo(p1.x, p1.y);
                        ctx.lineTo(p2.x, p2.y);
                        ctx.strokeStyle = `rgba(190, 190, 190, ${1 - (distance / maxDistance)})`;
                        ctx.lineWidth = 0.5;
                        ctx.stroke();
                    }
                }
            }
        }
        
        function animate() {
            requestAnimationFrame(animate);
            ctx.clearRect(0, 0, width, height);
            
            connectParticles();
            
            for (let i = 0; i < particles.length; i++) {
                particles[i].update();
            }
        }
        
        window.addEventListener('resize', () => {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
            init();
        });
        
        init();
        animate();
    </script>
  
<?php include 'includes/footer.php'; ?>
