<?php
/**
 * includes/contact-chat-api.php
 * Chuyển tiếp tới api/contact-chat-api.php
 * Lưu ý: Thư mục includes/ được bảo vệ và chặn truy cập trực tiếp từ Web trên IIS (<hiddenSegments>) và Apache.
 * Các yêu cầu AJAX/Fetch từ trình duyệt vui lòng gọi tới: api/contact-chat-api.php
 */
require_once __DIR__ . '/../api/contact-chat-api.php';