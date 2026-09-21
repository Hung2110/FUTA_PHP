<?php
/**
 * chat_notification_service.php
 * Xử lý kiểm tra và tự động gửi email cảnh báo khi khách hàng nhắn tin quá 5 phút mà chưa có nhân viên phản hồi.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Tự động nạp PHPMailer qua Composer
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

/**
 * Đảm bảo bảng chat_sessions có cột email_notified_at
 */
function ensureChatEmailNotificationColumn($conn) {
    static $checked = false;
    if ($checked || !$conn) return;

    $colCheck = $conn->query("SHOW COLUMNS FROM chat_sessions LIKE 'email_notified_at'");
    if ($colCheck && $colCheck->num_rows === 0) {
        $conn->query("ALTER TABLE chat_sessions ADD COLUMN email_notified_at DATETIME DEFAULT NULL");
    }

    // Đảm bảo bảng users có cột plain_password
    $userColCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'plain_password'");
    if ($userColCheck && $userColCheck->num_rows === 0) {
        $conn->query("ALTER TABLE users ADD COLUMN plain_password VARCHAR(255) DEFAULT NULL AFTER password");
        $conn->query("UPDATE users SET plain_password = '123456789' WHERE id = 2 AND password = '25f9e794323b453885f5181f1b624d0b'");
    }

    $checked = true;
}

/**
 * Tạo URL trỏ đến phòng chat của khách hàng trong trang Admin
 */
function getAdminChatUrl($sessionId = 0) {
    if (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = preg_replace('#/(includes|cron|api|admin)(/.*)?$#i', '', dirname($scriptPath));
        $baseDir = rtrim(str_replace('\\', '/', $baseDir), '/');
        $url = $protocol . $host . $baseDir . '/admin/chat.php';
    } else {
        $baseUrl = getenv('APP_URL') ?: 'http://localhost/FUTA_Advertising';
        $url = rtrim($baseUrl, '/') . '/admin/chat.php';
    }
    if ($sessionId > 0) {
        $url .= '?session_id=' . (int)$sessionId;
    }
    return $url;
}

/**
 * Lấy danh sách email và tên của nhân viên phụ trách trực Chat (chat_manager) hoặc Admin
 */
function getChatStaffRecipients($conn) {
    $recipients = [];

    // 1. Tìm các tài khoản được phân quyền 'chat_manager'
    $mgrStmt = $conn->query("SELECT id, fullname, email FROM users WHERE FIND_IN_SET('chat_manager', role) > 0 AND status = 'active'");
    if ($mgrStmt && $mgrStmt->num_rows > 0) {
        while ($row = $mgrStmt->fetch_assoc()) {
            if (!empty($row['email']) && filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $recipients[$row['email']] = [
                    'id' => (int)$row['id'],
                    'name' => $row['fullname'] ?: 'Quản Lý Chat'
                ];
            }
        }
    }

    // 2. Dự phòng: Nếu chưa có tài khoản chat_manager riêng, gửi cho các Admin
    if (empty($recipients)) {
        $admStmt = $conn->query("SELECT id, fullname, email FROM users WHERE FIND_IN_SET('admin', role) > 0 AND status = 'active'");
        if ($admStmt && $admStmt->num_rows > 0) {
            while ($row = $admStmt->fetch_assoc()) {
                if (!empty($row['email']) && filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                    $recipients[$row['email']] = [
                        'id' => (int)$row['id'],
                        'name' => $row['fullname'] ?: 'Quản Trị Viên'
                    ];
                }
            }
        }
    }

    // 3. Dự phòng qua biến môi trường hoặc email công ty mặc định
    if (empty($recipients)) {
        $envEmails = getenv('ADMIN_EMAILS');
        $defaultList = $envEmails ? array_map('trim', explode(',', $envEmails)) : ['futaadvertising@futa.vn'];
        foreach ($defaultList as $dEmail) {
            if (filter_var($dEmail, FILTER_VALIDATE_EMAIL)) {
                $recipients[$dEmail] = [
                    'id' => 0,
                    'name' => 'Quản Trị Viên FUTA'
                ];
            }
        }
    }

    return $recipients;
}

/**
 * Kiểm tra các phiên chat mà khách đã nhắn > 5 phút nhưng chưa có nhân viên phản hồi, và gửi email thông báo
 * 
 * @param mysqli $conn
 * @param bool $force Bỏ qua throttle thời gian
 * @return array Kết quả xử lý
 */
function checkAndNotifyPendingChats($conn, $force = false) {
    if (!$conn) {
        return ['success' => false, 'error' => 'Database connection missing'];
    }

    // Giới hạn tần suất kiểm tra (Throttle) để tránh spam query khi nhiều request tới cùng lúc
    $throttleFile = sys_get_temp_dir() . '/futa_chat_check_pending.tmp';
    $nowTime = time();
    if (!$force && file_exists($throttleFile)) {
        $lastCheck = (int)@file_get_contents($throttleFile);
        if (($nowTime - $lastCheck) < 20) { // Chỉ kiểm tra tối đa 1 lần mỗi 20 giây
            return ['success' => true, 'checked' => false, 'reason' => 'Throttled'];
        }
    }
    @file_put_contents($throttleFile, (string)$nowTime);

    // Đảm bảo cấu trúc cột tồn tại
    ensureChatEmailNotificationColumn($conn);

    // Tìm các phiên chat:
    // 1. status = 'open'
    // 2. last_sender = 'customer' (khách nhắn tin cuối cùng, chưa có admin trả lời)
    // 3. last_message_time cách hiện tại >= 300 giây (5 phút)
    // 4. email_notified_at IS NULL hoặc nhỏ hơn last_message_time (chưa gửi email cho đợt tin nhắn này)
    $query = "
        SELECT cs.id, cs.name, cs.phone, cs.email, cs.last_message_time, cs.last_sender,
               TIMESTAMPDIFF(SECOND, cs.last_message_time, NOW()) as wait_seconds
        FROM chat_sessions cs
        WHERE cs.status = 'open'
          AND cs.last_sender = 'customer'
          AND cs.last_message_time <= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
          AND (cs.email_notified_at IS NULL OR cs.email_notified_at < cs.last_message_time)
        ORDER BY cs.last_message_time ASC
        LIMIT 5
    ";

    $result = $conn->query($query);
    if (!$result || $result->num_rows === 0) {
        return ['success' => true, 'checked' => true, 'notified_count' => 0];
    }

    $staffRecipients = getChatStaffRecipients($conn);
    if (empty($staffRecipients)) {
        return ['success' => false, 'error' => 'Không tìm thấy danh sách email người nhận'];
    }

    $notifiedSessions = [];

    while ($session = $result->fetch_assoc()) {
        $sessionId = (int)$session['id'];
        $customerName = htmlspecialchars($session['name'] ?: 'Khách hàng');
        $customerPhone = htmlspecialchars($session['phone'] ?: 'Chưa cung cấp');
        $customerEmail = !empty($session['email']) ? htmlspecialchars($session['email']) : 'Chưa cung cấp';
        $waitMinutes = ceil((int)$session['wait_seconds'] / 60);
        $messageTime = date('d/m/Y H:i:s', strtotime($session['last_message_time']));

        // Lấy 3 tin nhắn gần nhất của khách hàng trong phiên này
        $msgList = [];
        $msgStmt = $conn->prepare("SELECT sender, message, created_at FROM chat_messages WHERE session_id = ? ORDER BY id DESC LIMIT 3");
        $msgStmt->bind_param("i", $sessionId);
        $msgStmt->execute();
        $msgRes = $msgStmt->get_result();
        while ($m = $msgRes->fetch_assoc()) {
            $msgList[] = $m;
        }
        $msgStmt->close();
        $msgList = array_reverse($msgList); // Đưa về thứ tự thời gian cũ trước, mới sau

        $messageContentHtml = '';
        if (!empty($msgList)) {
            foreach ($msgList as $m) {
                $rawText = $m['message'];
                if (strpos($rawText, 'FILE::') === 0) {
                    $parts = explode('::', $rawText);
                    $fileName = htmlspecialchars($parts[3] ?? 'Tập tin đính kèm');
                    $formatted = "<em>[Đính kèm file: {$fileName}]</em>";
                } else {
                    $formatted = nl2br(htmlspecialchars($rawText));
                }
                $senderLabel = ($m['sender'] === 'customer') ? 'Khách hàng' : 'Nhân viên';
                $timeLabel = date('H:i', strtotime($m['created_at']));
                $messageContentHtml .= "<div style='margin-bottom: 8px;'><strong>{$senderLabel} ({$timeLabel}):</strong> {$formatted}</div>";
            }
        } else {
            $messageContentHtml = '<em>Khách hàng đã mở phiên chat trực tuyến và đang chờ tư vấn.</em>';
        }

        $chatAdminUrl = getAdminChatUrl($sessionId);

        // 1. Thêm thông báo chuông (notifications) trong Admin Panel cho các nhân sự trực chat
        $notifText = "Khách hàng {$customerName} ({$customerPhone}) đang chờ phản hồi chat hơn {$waitMinutes} phút!";
        $notifLink = "chat.php?session_id={$sessionId}";
        $notifType = 'chat';

        $insertNotifStmt = $conn->prepare("INSERT INTO notifications (user_id, type, message, link) VALUES (?, ?, ?, ?)");
        foreach ($staffRecipients as $rEmail => $rInfo) {
            if (!empty($rInfo['id'])) {
                $insertNotifStmt->bind_param("isss", $rInfo['id'], $notifType, $notifText, $notifLink);
                $insertNotifStmt->execute();
            }
        }
        $insertNotifStmt->close();

        // 2. Gửi Email thông báo khẩn cấp qua PHPMailer
        $mail = new PHPMailer(true);
        $mailSent = false;
        try {
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host       = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('SMTP_USER') ?: 'futaadvertising@futa.vn';
            $mail->Password   = getenv('SMTP_PASS') ?: 'xtonupudcelpoixh';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = getenv('SMTP_PORT') ? (int)getenv('SMTP_PORT') : 587;
            $mail->CharSet    = 'UTF-8';

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $senderEmail = getenv('SMTP_USER') ?: 'futaadvertising@futa.vn';
            $mail->setFrom($senderEmail, 'FUTA Advertising - Live Chat');

            if (!empty($session['email']) && filter_var($session['email'], FILTER_VALIDATE_EMAIL)) {
                $mail->addReplyTo($session['email'], $customerName);
            }

            foreach ($staffRecipients as $rEmail => $rInfo) {
                $mail->addAddress($rEmail, $rInfo['name']);
            }

            $mail->isHTML(true);
            $mail->Subject = "[FUTA ADVERTISING] Cảnh báo: Khách hàng {$customerName} đang chờ phản hồi chat (> 5 phút)";

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
                    .badge-alert { display: inline-block; background-color: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; margin-bottom: 18px; }
                    .info-table { width: 100%; border-collapse: collapse; margin-bottom: 22px; }
                    .info-table td { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
                    .info-table td.label { width: 35%; font-weight: 600; color: #475569; background-color: #f8fafc; }
                    .info-table td.value { color: #1e293b; }
                    .message-box { background: #f8fafc; border-left: 4px solid #ff6600; border-radius: 6px; padding: 16px; margin-bottom: 25px; }
                    .message-title { font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 10px; }
                    .message-content { font-size: 14px; color: #1e293b; line-height: 1.6; }
                    .btn-action { display: inline-block; background: #ff6600; color: #ffffff !important; text-decoration: none; padding: 13px 30px; font-size: 14px; font-weight: 700; border-radius: 8px; text-align: center; box-shadow: 0 4px 10px rgba(255, 102, 0, 0.35); }
                    .email-footer { background: #f8fafc; padding: 18px 30px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
                </style>
            </head>
            <body>
                <div class="email-wrapper">
                    <div class="email-header">
                        <h2>FUTA ADVERTISING</h2>
                        <p>Hệ Thống Quản Lý Chat Trực Tuyến & Chăm Sóc Khách Hàng</p>
                    </div>
                    <div class="email-body">
                        <div class="badge-alert">
                            ⚠️ CẢNH BÁO: KHÁCH HÀNG CHỜ PHẢN HỒI QUÁ 5 PHÚT
                        </div>
                        <p style="margin-top:0; font-size: 15px;">Kính gửi <strong>Nhân viên trực web & Quản lý Chat</strong>,</p>
                        <p style="font-size: 14px; color: #475569;">Hệ thống phát hiện có một khách hàng đã gửi tin nhắn trên Live Chat Website nhưng <strong>đã qua hơn ' . $waitMinutes . ' phút chưa có nhân viên nhận hoặc phản hồi</strong>. Vui lòng kiểm tra và hỗ trợ ngay:</p>
                        
                        <table class="info-table">
                            <tr>
                                <td class="label">Khách hàng:</td>
                                <td class="value"><strong>' . $customerName . '</strong></td>
                            </tr>
                            <tr>
                                <td class="label">Số điện thoại:</td>
                                <td class="value"><a href="tel:' . $customerPhone . '" style="color:#003366; font-weight:700; text-decoration:none;">' . $customerPhone . '</a></td>
                            </tr>
                            <tr>
                                <td class="label">Email khách:</td>
                                <td class="value">' . $customerEmail . '</td>
                            </tr>
                            <tr>
                                <td class="label">Thời gian gửi tin:</td>
                                <td class="value">' . $messageTime . '</td>
                            </tr>
                            <tr>
                                <td class="label">Thời gian đang chờ:</td>
                                <td class="value"><span style="color:#dc2626; font-weight:700;">Hơn ' . $waitMinutes . ' phút</span></td>
                            </tr>
                        </table>

                        <div class="message-box">
                            <div class="message-title">💬 Nội dung tin nhắn gần nhất của khách:</div>
                            <div class="message-content">' . $messageContentHtml . '</div>
                        </div>

                        <div style="text-align: center; margin: 28px 0 12px 0;">
                            <a href="' . htmlspecialchars($chatAdminUrl) . '" class="btn-action" target="_blank">
                                Mở Cuộc Trò Chuyện & Phản Hồi Ngay &rarr;
                            </a>
                        </div>
                    </div>
                    <div class="email-footer">
                        <p style="margin: 0 0 4px 0;">Email cảnh báo tự động được gửi khi tin nhắn trực tuyến của khách không có phản hồi sau 5 phút.</p>
                        <p style="margin: 0;">FUTA Advertising - Hệ thống quản trị nội bộ.</p>
                    </div>
                </div>
            </body>
            </html>';

            $mail->Body = $mailBody;
            $mail->AltBody = "CẢNH BÁO CHAT: Khách hàng {$customerName} ({$customerPhone}) đã nhắn tin hơn {$waitMinutes} phút chưa có phản hồi. Mở chat tại: {$chatAdminUrl}";

            $mailSent = $mail->send();
        } catch (Exception $e) {
            error_log("Lỗi gửi email cảnh báo chat chờ 5 phút: " . $mail->ErrorInfo . " | " . $e->getMessage());
        }

        // 3. Cập nhật mốc thời gian đã gửi thông báo vào chat_sessions để chống gửi lặp lại
        $updateStmt = $conn->prepare("UPDATE chat_sessions SET email_notified_at = NOW() WHERE id = ?");
        $updateStmt->bind_param("i", $sessionId);
        $updateStmt->execute();
        $updateStmt->close();

        $notifiedSessions[] = [
            'session_id' => $sessionId,
            'customer' => $customerName,
            'wait_minutes' => $waitMinutes,
            'mail_sent' => $mailSent
        ];
    }

    return [
        'success' => true,
        'checked' => true,
        'notified_count' => count($notifiedSessions),
        'sessions' => $notifiedSessions
    ];
}

