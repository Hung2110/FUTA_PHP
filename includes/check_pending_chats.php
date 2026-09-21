<?php
/**
 * includes/check_pending_chats.php
 * Script kiểm tra định kỳ các phiên chat chưa có nhân viên phản hồi quá 5 phút.
 * Có thể chạy độc lập qua:
 * 1. CLI / Windows Task Scheduler: php c:\laragon\www\FUTA_Advertising\includes\check_pending_chats.php
 * 2. Trình duyệt / Web Cron: http://localhost/FUTA_Advertising/includes/check_pending_chats.php
 */

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/chat_notification_service.php';

$isCli = (php_sapi_name() === 'cli');

// Gọi hàm kiểm tra thông báo với force = true
$result = checkAndNotifyPendingChats($conn, true);

if ($isCli) {
    echo "[" . date('Y-m-d H:i:s') . "] Chay kiem tra chat qua han 5 phut:\n";
    if (!empty($result['success'])) {
        $count = $result['notified_count'] ?? 0;
        echo "- So phien da gui email thong bao: {$count}\n";
        if (!empty($result['sessions'])) {
            foreach ($result['sessions'] as $s) {
                $status = !empty($s['mail_sent']) ? 'Thanh cong' : 'Loi gui mail';
                echo "  + Phien #{$s['session_id']} ({$s['customer']}) - Cho {$s['wait_minutes']} phut -> {$status}\n";
            }
        }
    } else {
        echo "- Loi: " . ($result['error'] ?? 'Unknown error') . "\n";
    }
} else {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

