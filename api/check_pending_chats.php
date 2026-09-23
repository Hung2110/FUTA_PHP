<?php
/**
 * api/check_pending_chats.php
 * Web endpoint cho phép kiểm tra định kỳ các phiên chat chưa có nhân viên phản hồi quá 5 phút.
 * Hoạt động ngay cả khi thư mục includes/ bị IIS/Apache chặn truy cập trực tiếp qua web.
 */
require_once __DIR__ . '/../includes/check_pending_chats.php';

